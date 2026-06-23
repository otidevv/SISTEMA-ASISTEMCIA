<?php

namespace App\Http\Controllers\Api;

use App\Models\Solicitud;
use App\Models\SolicitudTipo;
use App\Models\SolicitudAdjunto;
use App\Models\Ciclo;
use App\Services\SolicitudService;
use Illuminate\Http\Request;

/**
 * API de Mesa de Partes / Solicitudes para la app móvil (Sanctum).
 */
class SolicitudApiController extends BaseController
{
    protected SolicitudService $service;

    public function __construct(SolicitudService $service)
    {
        $this->service = $service;
    }

    /** Catálogo de tipos de trámite disponibles (incluye código TUSNE para detección de pago). */
    public function tipos()
    {
        $tipos = SolicitudTipo::with('concepto:id,codigo')->where('activo', true)->orderBy('orden')->get();

        $data = $tipos->map(fn ($t) => [
            'id' => $t->id,
            'codigo' => $t->codigo,
            'nombre' => $t->nombre,
            'descripcion' => $t->descripcion,
            'requiere_pago' => (bool) $t->requiere_pago,
            'permite_adjuntos' => (bool) $t->permite_adjuntos,
            'requiere_adjunto' => (bool) $t->requiere_adjunto,
            'genera_documento' => $t->genera_documento,
            'campos' => $t->campos,
            'codigo_tusne' => optional($t->concepto)->codigo,
        ]);

        return $this->sendResponse($data, 'Tipos de trámite.');
    }

    /** Opciones del formulario: carreras, turnos y carrera/turno actual del usuario. */
    public function opcionesForm(Request $request)
    {
        $user = $request->user();
        $carreras = \App\Models\Carrera::orderBy('nombre')->get(['id', 'nombre']);
        $turnos = \App\Models\Turno::orderBy('orden')->get(['id', 'nombre']);

        $ciclo = Ciclo::where('es_activo', true)->orderBy('programa_id')->first();
        $insc = \App\Models\Inscripcion::where('estudiante_id', $user->id)
            ->when($ciclo, fn ($q) => $q->where('ciclo_id', $ciclo->id))
            ->latest()
            ->first();

        $carrera = ($insc && $insc->carrera_id) ? \App\Models\Carrera::find($insc->carrera_id) : null;
        $turno = ($insc && $insc->turno_id) ? \App\Models\Turno::find($insc->turno_id) : null;

        return $this->sendResponse([
            'carreras' => $carreras,
            'turnos' => $turnos,
            'estudiante' => [
                'carrera_id' => $insc->carrera_id ?? null,
                'carrera_nombre' => $carrera->nombre ?? null,
                'turno_id' => $insc->turno_id ?? null,
                'turno_nombre' => $turno->nombre ?? null,
            ],
        ], 'Opciones del formulario.');
    }

    /** Pagos disponibles del usuario para un código TUSNE (no usados). */
    public function pagos(Request $request)
    {
        $user = $request->user();
        $codigo = trim($request->input('codigo', ''));
        if ($codigo === '') {
            return $this->sendResponse(['ok' => true, 'pagos' => []], 'Sin código.');
        }

        try {
            $vouchers = app(\App\Services\PaymentValidationService::class)->validateVoucher($user->numero_documento, '') ?? [];
        } catch (\Throwable $e) {
            return $this->sendResponse(['ok' => false, 'pagos' => []], 'No se pudo consultar el sistema de pagos.');
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
                    ];
                    break;
                }
            }
        }

        return $this->sendResponse(['ok' => true, 'pagos' => $disponibles], 'Pagos disponibles.');
    }

    /** Mis trámites. */
    public function index(Request $request)
    {
        $solicitudes = Solicitud::with('tipo:id,nombre,codigo')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->sendResponse($solicitudes, 'Mis trámites.');
    }

    /** Detalle/seguimiento de un expediente. */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $solicitud = Solicitud::with([
            'tipo', 'historial.usuario:id,nombre,apellido_paterno', 'adjuntos', 'inasistencias',
            'derivaciones.rolDestino:id,nombre', 'derivaciones.usuarioDestino:id,nombre,apellido_paterno',
            'usuarioActual:id,nombre,apellido_paterno', 'rolActual:id,nombre',
        ])->findOrFail($id);

        if ($solicitud->user_id !== $user->id && !$user->hasPermission('solicitudes.manage')) {
            return $this->sendError('No autorizado.', [], 403);
        }

        return $this->sendResponse($solicitud, 'Expediente.');
    }

    /** Crear solicitud. */
    public function store(Request $request)
    {
        $user = $request->user();
        $tipo = SolicitudTipo::where('activo', true)->find($request->input('solicitud_tipo_id'));
        if (!$tipo) {
            return $this->sendError('Tipo de trámite inválido.', [], 422);
        }

        $datos = $request->input('datos', []);
        if (is_string($datos)) {
            $datos = json_decode($datos, true) ?: [];
        }

        foreach ((array) ($tipo->campos ?? []) as $campo) {
            $name = $campo['name'] ?? null;
            if (!$name || $name === 'fechas') {
                continue;
            }
            if (!empty($campo['required']) && empty($datos[$name])) {
                return $this->sendError('Falta el campo: ' . ($campo['label'] ?? $name), [], 422);
            }
        }

        if ($tipo->requiere_adjunto && !$request->hasFile('adjuntos')) {
            return $this->sendError('Este trámite requiere adjuntar al menos un documento.', [], 422);
        }

        $opts = ['canal' => 'app'];

        if ($request->filled('fechas')) {
            $f = $request->input('fechas');
            $opts['fechas'] = is_array($f) ? $f : array_values(array_filter(array_map('trim', explode(',', $f))));
        }

        $ciclo = Ciclo::where('es_activo', true)->orderBy('programa_id')->first();
        if ($ciclo) {
            $opts['ciclo_id'] = $ciclo->id;
            $opts['term_name'] = $ciclo->codigo ?? null;
        }

        if ($request->filled('serial_voucher')) {
            $serial = trim($request->input('serial_voucher'));
            if (Solicitud::where('serial_voucher', $serial)->exists()) {
                return $this->sendError('Ese voucher ya está registrado en otro trámite.', [], 422);
            }
            $opts['serial_voucher'] = $serial;
            $res = $this->service->validarVoucher($user->numero_documento, $serial, optional($tipo->concepto)->codigo);
            if ($res['ok']) {
                $opts['pago_validado'] = true;
                $opts['monto'] = $res['monto'];
                $opts['fecha_pago'] = $res['fecha'];
            }
        }

        $solicitud = $this->service->crear($tipo, $user, $datos, $opts);

        if ($request->hasFile('adjuntos')) {
            $this->guardarAdjuntos($solicitud, $request->file('adjuntos'));
        }

        return $this->sendResponse($solicitud->fresh('tipo'), 'Solicitud registrada: ' . $solicitud->codigo, 201);
    }

    /** Registrar/validar el pago (voucher). */
    public function registrarPago(Request $request, $id)
    {
        $user = $request->user();
        $solicitud = Solicitud::with('tipo.concepto')->findOrFail($id);
        if ($solicitud->user_id !== $user->id) {
            return $this->sendError('No autorizado.', [], 403);
        }

        $request->validate(['serial_voucher' => 'required|string|max:50']);
        $serial = trim($request->input('serial_voucher'));

        if (Solicitud::where('serial_voucher', $serial)->where('id', '!=', $solicitud->id)->exists()) {
            return $this->sendError('Ese voucher ya está registrado en otro trámite.', [], 422);
        }

        $codigo = optional(optional($solicitud->tipo)->concepto)->codigo;
        $res = $this->service->validarVoucher($solicitud->numero_documento, $serial, $codigo);

        if (!$res['ok']) {
            $solicitud->update(['serial_voucher' => $serial, 'pago_validado' => false]);
            return $this->sendError($res['mensaje'] . ' Quedó por validar.', [], 422);
        }

        $this->service->registrarPago($solicitud, $serial, $res['monto'], $res['fecha']);

        return $this->sendResponse($solicitud->fresh(), 'Pago validado.');
    }

    /** Subir evidencia(s). */
    public function subirAdjunto(Request $request, $id)
    {
        $user = $request->user();
        $solicitud = Solicitud::findOrFail($id);
        if ($solicitud->user_id !== $user->id && !$user->hasPermission('solicitudes.manage')) {
            return $this->sendError('No autorizado.', [], 403);
        }

        $request->validate([
            'adjuntos' => 'required',
            'adjuntos.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $this->guardarAdjuntos($solicitud, $request->file('adjuntos'));

        return $this->sendResponse($solicitud->fresh('adjuntos'), 'Evidencia(s) adjuntada(s).');
    }

    private function guardarAdjuntos(Solicitud $solicitud, $files): void
    {
        foreach ((is_array($files) ? $files : [$files]) as $file) {
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
}
