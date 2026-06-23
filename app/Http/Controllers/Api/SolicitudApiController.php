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

    /** Catálogo de tipos de trámite disponibles. */
    public function tipos()
    {
        $tipos = SolicitudTipo::where('activo', true)->orderBy('orden')
            ->get(['id', 'codigo', 'nombre', 'descripcion', 'requiere_pago', 'permite_adjuntos', 'requiere_adjunto', 'campos', 'genera_documento']);

        return $this->sendResponse($tipos, 'Tipos de trámite.');
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
