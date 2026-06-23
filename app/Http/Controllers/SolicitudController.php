<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\SolicitudTipo;
use App\Models\SolicitudAdjunto;
use App\Models\User;
use App\Models\Role;
use App\Models\Ciclo;
use App\Services\SolicitudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SolicitudController extends Controller
{
    protected SolicitudService $service;

    public function __construct(SolicitudService $service)
    {
        $this->service = $service;
    }

    /** Mis trámites (del usuario autenticado). */
    public function index()
    {
        $solicitudes = Solicitud::with('tipo')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('solicitudes.index', compact('solicitudes'));
    }

    /** Vista de control para la contadora: trámites + pagos (API) + conciliación de un estudiante. */
    public function estudiante(Request $request)
    {
        $documento = trim($request->input('documento', ''));

        $estudiante = null;
        $solicitudes = collect();
        $pagos = [];
        $pagosError = null;
        $vouchersUsados = [];

        if ($documento !== '') {
            $estudiante = User::where('numero_documento', $documento)->first();
            $solicitudes = Solicitud::with('tipo')->where('numero_documento', $documento)->latest()->get();

            try {
                $pagos = app(\App\Services\PaymentValidationService::class)->validateVoucher($documento, '') ?? [];
            } catch (\Throwable $e) {
                $pagosError = 'No se pudo consultar el sistema de pagos.';
            }

            $vouchersUsados = $solicitudes->pluck('serial_voucher')->filter()->values()->all();
        }

        return view('solicitudes.estudiante', compact('documento', 'estudiante', 'solicitudes', 'pagos', 'pagosError', 'vouchersUsados'));
    }

    /** Devuelve (JSON) los pagos disponibles de un DNI para un código TUSNE (no usados). */
    public function pagosDisponibles(Request $request)
    {
        $dni = trim($request->input('dni', ''));
        $codigo = trim($request->input('codigo', ''));
        $u = Auth::user();

        if ($dni !== '' && $dni !== $u->numero_documento && !$u->hasPermission('solicitudes.manage')) {
            return response()->json(['ok' => false, 'mensaje' => 'No autorizado'], 403);
        }
        if ($dni === '' || $codigo === '') {
            return response()->json(['ok' => true, 'pagos' => []]);
        }

        try {
            $vouchers = app(\App\Services\PaymentValidationService::class)->validateVoucher($dni, '') ?? [];
        } catch (\Throwable $e) {
            // API caída → no bloquear (el usuario podrá registrar el voucher manualmente)
            return response()->json(['ok' => false, 'mensaje' => 'No se pudo consultar el sistema de pagos.', 'pagos' => []]);
        }

        $usados = Solicitud::whereNotNull('serial_voucher')->pluck('serial_voucher')->all();

        $disponibles = [];
        foreach ($vouchers as $v) {
            $serial = $v['serial_voucher'] ?? $v['serial'] ?? null;
            if (!$serial || in_array($serial, $usados)) {
                continue;
            }
            foreach (($v['items'] ?? []) as $it) {
                $desc = $it['descripcion'] ?? $it['description'] ?? '';
                if (str_contains($desc, $codigo) && (int) ($it['status'] ?? 0) === 2) {
                    $disponibles[] = [
                        'serial' => $serial,
                        'monto' => $it['total'] ?? $v['monto_total'] ?? null,
                        'fecha' => $it['paymentDate'] ?? $v['fecha'] ?? null,
                        'descripcion' => $desc,
                    ];
                    break;
                }
            }
        }

        return response()->json(['ok' => true, 'pagos' => $disponibles]);
    }

    /** Formulario para crear una nueva solicitud. */
    public function create()
    {
        $tipos = SolicitudTipo::with('concepto')->where('activo', true)->orderBy('orden')->get();
        $carreras = \App\Models\Carrera::orderBy('nombre')->get(['id', 'nombre']);
        $turnos = \App\Models\Turno::orderBy('orden')->get(['id', 'nombre']);
        $puedeOtros = Auth::user()->hasPermission('solicitudes.manage');

        return view('solicitudes.create', compact('tipos', 'carreras', 'turnos', 'puedeOtros'));
    }

    /** Devuelve (JSON) datos del estudiante por DNI: nombre y su carrera/turno actual. */
    public function estudianteInfo(Request $request)
    {
        $dni = trim($request->input('dni', ''));
        $u = Auth::user();

        // Solo puede consultar otro DNI quien gestiona; el propio siempre.
        if ($dni !== '' && $dni !== $u->numero_documento && !$u->hasPermission('solicitudes.manage')) {
            return response()->json(['encontrado' => false, 'mensaje' => 'No autorizado'], 403);
        }

        $user = User::where('numero_documento', $dni)->first();
        if (!$user) {
            return response()->json(['encontrado' => false]);
        }

        $ciclo = Ciclo::where('es_activo', true)->orderBy('programa_id')->first();
        $insc = \App\Models\Inscripcion::where('estudiante_id', $user->id)
            ->when($ciclo, fn ($q) => $q->where('ciclo_id', $ciclo->id))
            ->latest()
            ->first();

        $carrera = ($insc && $insc->carrera_id) ? \App\Models\Carrera::find($insc->carrera_id) : null;
        $turno = ($insc && $insc->turno_id) ? \App\Models\Turno::find($insc->turno_id) : null;

        return response()->json([
            'encontrado' => true,
            'nombre' => trim(($user->nombre ?? '') . ' ' . ($user->apellido_paterno ?? '') . ' ' . ($user->apellido_materno ?? '')),
            'carrera_id' => $insc->carrera_id ?? null,
            'carrera_nombre' => $carrera->nombre ?? null,
            'turno_id' => $insc->turno_id ?? null,
            'turno_nombre' => $turno->nombre ?? null,
        ]);
    }

    /** Registrar la solicitud. */
    public function store(Request $request)
    {
        $tipo = SolicitudTipo::where('activo', true)->findOrFail($request->input('solicitud_tipo_id'));

        // Resolver al solicitante: por defecto el usuario autenticado; un gestor puede crear por DNI.
        $solicitante = Auth::user();
        $dni = trim($request->input('numero_documento', ''));
        if ($dni !== '' && $dni !== $solicitante->numero_documento) {
            abort_unless($solicitante->hasPermission('solicitudes.manage'), 403);
            $target = User::where('numero_documento', $dni)->first();
            if (!$target) {
                return back()->withErrors(['numero_documento' => 'No existe un usuario registrado con ese DNI.'])->withInput();
            }
            $solicitante = $target;
        }

        // Adjunto obligatorio para ciertos trámites (sílabo, CV, justificación, etc.)
        if ($tipo->requiere_adjunto && !$request->hasFile('adjuntos')) {
            return back()->withErrors(['adjuntos' => 'Este trámite requiere adjuntar al menos un documento.'])->withInput();
        }
        if ($request->hasFile('adjuntos')) {
            $request->validate(['adjuntos.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png']);
        }

        // Validar y recolectar los campos dinámicos
        $datos = [];
        foreach ((array) ($tipo->campos ?? []) as $campo) {
            $name = $campo['name'] ?? null;
            if (!$name || $name === 'fechas') {
                continue; // las fechas se manejan aparte
            }
            $val = $request->input('campo_' . $name);
            if (!empty($campo['required']) && ($val === null || $val === '')) {
                return back()
                    ->withErrors(['campo_' . $name => 'El campo "' . ($campo['label'] ?? $name) . '" es obligatorio.'])
                    ->withInput();
            }
            $datos[$name] = $val;
        }

        $opts = ['canal' => 'web'];

        // Fechas a justificar (justificación de inasistencias)
        if ($request->filled('fechas')) {
            $opts['fechas'] = array_values(array_filter(array_map('trim', explode(',', $request->input('fechas')))));
        }

        // Ligar al ciclo activo (CEPRE)
        $ciclo = Ciclo::where('es_activo', true)->orderBy('programa_id')->first();
        if ($ciclo) {
            $opts['ciclo_id'] = $ciclo->id;
            $opts['term_name'] = $ciclo->codigo ?? null;
        }

        // Voucher (si lo ingresó al crear): validar contra la API de pagos
        if ($request->filled('serial_voucher')) {
            $serial = trim($request->input('serial_voucher'));
            if (Solicitud::where('serial_voucher', $serial)->exists()) {
                return back()->withErrors(['serial_voucher' => 'Ese voucher ya está registrado en otro trámite.'])->withInput();
            }
            $opts['serial_voucher'] = $serial;
            $res = $this->service->validarVoucher($solicitante->numero_documento, $serial, optional($tipo->concepto)->codigo);
            if ($res['ok']) {
                $opts['pago_validado'] = true;
                $opts['monto'] = $res['monto'];
                $opts['fecha_pago'] = $res['fecha'];
            }
        }

        $solicitud = $this->service->crear($tipo, $solicitante, $datos, $opts);

        // Adjuntar evidencias enviadas en el formulario de creación
        if ($request->hasFile('adjuntos')) {
            $this->guardarAdjuntos($solicitud, $request->file('adjuntos'));
        }

        return redirect()
            ->route('solicitudes.show', $solicitud->id)
            ->with('success', 'Solicitud registrada: ' . $solicitud->codigo)
            ->with('comprobante', $solicitud->id);
    }

    /** Ver expediente (seguimiento). */
    public function show($id)
    {
        $solicitud = Solicitud::with([
            'tipo', 'estudiante', 'historial.usuario', 'adjuntos', 'inasistencias',
            'derivaciones.rolDestino', 'derivaciones.usuarioDestino', 'derivaciones.deUsuario',
            'rolActual', 'usuarioActual', 'vbDirector', 'atendidoPor',
        ])->findOrFail($id);

        $u = Auth::user();
        $puedeGestionar = $u->hasPermission('solicitudes.manage')
            || $u->hasPermission('solicitudes.approve')
            || $u->hasPermission('solicitudes.atender');

        abort_unless($solicitud->user_id === $u->id || $puedeGestionar, 403);

        $roles = Role::orderBy('nombre')->get();

        return view('solicitudes.show', compact('solicitud', 'roles'));
    }

    /** Constancia de seguimiento / Hoja de Trámite (PDF con QR). */
    public function pdfSeguimiento($id)
    {
        $solicitud = Solicitud::with([
            'tipo', 'estudiante', 'historial.usuario', 'inasistencias',
            'derivaciones.rolDestino', 'derivaciones.usuarioDestino', 'derivaciones.deUsuario',
            'vbDirector',
        ])->findOrFail($id);

        $u = Auth::user();
        $puede = $solicitud->user_id === $u->id
            || $u->hasPermission('solicitudes.manage')
            || $u->hasPermission('solicitudes.approve')
            || $u->hasPermission('solicitudes.atender');
        abort_unless($puede, 403);

        $qrData = "CONSTANCIA DE SEGUIMIENTO CEPRE-UNAMAD\nExpediente: {$solicitud->codigo}\nEstado: {$solicitud->estado}\nValidacion: " . uniqid();
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(110)->generate($qrData));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('solicitudes.pdf.seguimiento', [
            'solicitud' => $solicitud,
            'qrCode' => $qrCode,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Seguimiento_' . $solicitud->codigo . '.pdf');
    }

    /** Comprobante / cargo de recepción del trámite (PDF con QR). */
    public function pdfComprobante($id)
    {
        $solicitud = Solicitud::with(['tipo', 'estudiante'])->findOrFail($id);

        $u = Auth::user();
        $puede = $solicitud->user_id === $u->id
            || $u->hasPermission('solicitudes.manage')
            || $u->hasPermission('solicitudes.approve')
            || $u->hasPermission('solicitudes.atender');
        abort_unless($puede, 403);

        $qrData = "COMPROBANTE DE RECEPCION CEPRE-UNAMAD\nExpediente: {$solicitud->codigo}\n"
            . 'Fecha: ' . $solicitud->created_at?->format('d/m/Y H:i') . "\nValidacion: " . uniqid();
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(110)->generate($qrData));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('solicitudes.pdf.comprobante', [
            'solicitud' => $solicitud,
            'qrCode' => $qrCode,
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
        ]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Comprobante_' . $solicitud->codigo . '.pdf');
    }

    /** Bandeja del Director: pendientes de Visto Bueno. */
    public function bandejaVistoBueno()
    {
        $solicitudes = Solicitud::with(['tipo', 'estudiante'])
            ->where('estado', Solicitud::ESTADO_ENVIADA)
            ->latest()
            ->paginate(15);

        $roles = Role::orderBy('nombre')->get();

        return view('solicitudes.bandeja', compact('solicitudes', 'roles'));
    }

    /** Otorgar V°B° y derivar. */
    public function vistoBueno(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);
        $this->service->darVistoBueno($solicitud, Auth::user(), $this->datosDerivacion($request));

        return back()->with('success', 'V°B° otorgado y trámite derivado.');
    }

    public function observar(Request $request, $id)
    {
        $request->validate(['observacion' => 'required|string|max:1000']);
        $this->service->observar(Solicitud::findOrFail($id), Auth::user(), $request->input('observacion'));

        return back()->with('success', 'Trámite observado.');
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate(['observacion' => 'required|string|max:1000']);
        $this->service->rechazar(Solicitud::findOrFail($id), Auth::user(), $request->input('observacion'));

        return back()->with('success', 'Trámite rechazado.');
    }

    /** Bandeja "Por atender": solicitudes derivadas a mí o a mis roles. */
    public function porAtender()
    {
        $u = Auth::user();
        $misRoles = DB::table('user_roles')->where('usuario_id', $u->id)->pluck('rol_id');

        $solicitudes = Solicitud::with(['tipo', 'estudiante'])
            ->where('estado', Solicitud::ESTADO_DERIVADA)
            ->where(function ($q) use ($u, $misRoles) {
                $q->where('user_actual_id', $u->id)
                  ->orWhereIn('rol_actual_id', $misRoles);
            })
            ->latest()
            ->paginate(15);

        return view('solicitudes.por-atender', compact('solicitudes'));
    }

    public function atender(Request $request, $id)
    {
        $this->service->atender(Solicitud::findOrFail($id), Auth::user(), $request->input('comentario'));

        return back()->with('success', 'Trámite atendido.');
    }

    public function derivar(Request $request, $id)
    {
        $this->service->derivar(Solicitud::findOrFail($id), Auth::user(), $this->datosDerivacion($request));

        return back()->with('success', 'Trámite derivado.');
    }

    /** Búsqueda de administrativos para el selector de derivación (JSON). */
    public function buscarAdministrativos(Request $request)
    {
        $u = Auth::user();
        abort_unless(
            $u->hasPermission('solicitudes.approve') || $u->hasPermission('solicitudes.atender') || $u->hasPermission('solicitudes.manage'),
            403
        );

        $q = trim($request->input('q', ''));

        $users = User::when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                  ->orWhere('apellido_paterno', 'like', "%{$q}%")
                  ->orWhere('apellido_materno', 'like', "%{$q}%")
                  ->orWhere('numero_documento', 'like', "%{$q}%");
            });
        })
            ->orderBy('apellido_paterno')
            ->limit(15)
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'numero_documento']);

        return response()->json($users);
    }

    /** Registrar/validar el pago (voucher) de un trámite pendiente de pago. */
    public function registrarPago(Request $request, $id)
    {
        $solicitud = Solicitud::with('tipo.concepto')->findOrFail($id);
        $u = Auth::user();
        abort_unless(
            $solicitud->user_id === $u->id || $u->hasPermission('solicitudes.manage') || $u->hasPermission('solicitudes.approve'),
            403
        );

        $request->validate(['serial_voucher' => 'required|string|max:50']);
        $serial = trim($request->input('serial_voucher'));

        if (Solicitud::where('serial_voucher', $serial)->where('id', '!=', $solicitud->id)->exists()) {
            return back()->withErrors(['serial_voucher' => 'Ese voucher ya está registrado en otro trámite.']);
        }

        $codigo = optional(optional($solicitud->tipo)->concepto)->codigo;
        $res = $this->service->validarVoucher($solicitud->numero_documento, $serial, $codigo);

        if (!$res['ok']) {
            $solicitud->update(['serial_voucher' => $serial, 'pago_validado' => false]);
            return back()->with('warning', $res['mensaje'] . ' Se registró el voucher, pero quedará por validar.');
        }

        $this->service->registrarPago($solicitud, $serial, $res['monto'], $res['fecha']);

        return back()->with('success', 'Pago validado. El trámite avanzó para Visto Bueno del Director.');
    }

    /** Subir evidencia(s) a un expediente existente. */
    public function subirAdjunto(Request $request, $id)
    {
        $solicitud = Solicitud::findOrFail($id);
        $u = Auth::user();
        $puede = $solicitud->user_id === $u->id
            || $u->hasPermission('solicitudes.manage')
            || $u->hasPermission('solicitudes.approve')
            || $u->hasPermission('solicitudes.atender');
        abort_unless($puede, 403);

        $request->validate([
            'adjuntos' => 'required',
            'adjuntos.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $this->guardarAdjuntos($solicitud, $request->file('adjuntos'));

        return back()->with('success', 'Evidencia(s) adjuntada(s).');
    }

    /** Eliminar una evidencia (dueño o gestor). */
    public function eliminarAdjunto($id, $adjuntoId)
    {
        $solicitud = Solicitud::findOrFail($id);
        $adj = SolicitudAdjunto::where('solicitud_id', $solicitud->id)->findOrFail($adjuntoId);

        $u = Auth::user();
        abort_unless($solicitud->user_id === $u->id || $u->hasPermission('solicitudes.manage'), 403);

        Storage::disk('public')->delete($adj->path);
        $adj->delete();

        return back()->with('success', 'Evidencia eliminada.');
    }

    /** Guarda archivos subidos como adjuntos de la solicitud (disco public). */
    private function guardarAdjuntos(Solicitud $solicitud, $files): void
    {
        $files = is_array($files) ? $files : [$files];

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            $path = $file->store('solicitudes/' . $solicitud->id, 'public');
            SolicitudAdjunto::create([
                'solicitud_id' => $solicitud->id,
                'tipo' => 'evidencia',
                'nombre_original' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    private function datosDerivacion(Request $request): array
    {
        return [
            'user_destino_id' => $request->input('user_destino_id') ?: null,
            'rol_destino_id' => $request->input('rol_destino_id') ?: null,
            'accion' => $request->input('accion', 'atencion'),
            'observacion' => $request->input('observacion'),
        ];
    }
}
