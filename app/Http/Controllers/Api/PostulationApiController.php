<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class PostulationApiController extends BaseController
{
    /**
     * Store a new postulation from mobile app
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_documento' => 'required|exists:users,numero_documento',
            'email' => 'required|email',
            'carrera_id' => 'required|exists:carreras,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error de validación.', $validator->errors());
        }

        // Obtener el ciclo activo para CEPRE (programa_id = 1)
        $cicloActivo = \App\Models\Ciclo::where('es_activo', true)->where('programa_id', 1)->first();
        if (!$cicloActivo || !$cicloActivo->estaPeriodoInscripcionAbierto()) {
            return $this->sendError('El proceso de inscripciones para el CEPRE ha culminado.', [], 400);
        }

        // Logic to create or update postulation
        return $this->sendResponse([], 'Postulación registrada correctamente.');
    }

    /**
     * Get the current status of a postulation
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $postulation = Postulacion::where('estudiante_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$postulation) {
            return $this->sendError('No se encontró postulación.', [], 404);
        }

        return $this->sendResponse($postulation, 'Estado de postulación recuperado.');
    }

    /**
     * ADMIN: List all postulations for the active cycle
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('postulaciones.view')) {
            return $this->sendError('Sin permisos.', [], 403);
        }

        $cicloId = $request->input('ciclo_id');
        if (empty($cicloId)) {
            $cicloActivo = \App\Models\Ciclo::where('es_activo', true)->where('programa_id', 1)->first();
            $cicloId = $cicloActivo ? $cicloActivo->id : null;
        }

        $query = Postulacion::with(['estudiante', 'carrera', 'turno'])
            ->orderBy('created_at', 'desc');

        if ($cicloId) {
            $query->where('ciclo_id', $cicloId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('estudiante', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        $postulaciones = $query->paginate(20);

        return $this->sendResponse($postulaciones, 'Postulaciones recuperadas.');
    }

    /**
     * ADMIN: Show postulation detail
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->hasPermission('postulaciones.show')) {
            return $this->sendError('Sin permisos.', [], 403);
        }

        try {
            $postulacion = Postulacion::with([
                'estudiante.parentescos.padre',
                'ciclo',
                'carrera',
                'turno',
                'centroEducativo'
            ])->findOrFail($id);

            // Buscar la inscripción asociada si existe y cargar el aula
            $inscripcion = null;
            if ($postulacion->estado === 'aprobado' || $postulacion->constancia_generada) {
                $inscripcion = \App\Models\Inscripcion::where('estudiante_id', $postulacion->estudiante_id)
                    ->where('ciclo_id', $postulacion->ciclo_id)
                    ->with('aula')
                    ->first();
            }

            // Extraer datos del padre y la madre
            $padre = $postulacion->estudiante->parentescos->filter(function($p) {
                return strtolower($p->tipo_parentesco) === 'padre';
            })->first();
            
            $madre = $postulacion->estudiante->parentescos->filter(function($p) {
                return strtolower($p->tipo_parentesco) === 'madre';
            })->first();

            $padreData = $padre && $padre->padre ? [
                'nombre' => trim("{$padre->padre->nombre} {$padre->padre->apellido_paterno} {$padre->padre->apellido_materno}"),
                'numero_documento' => (string) $padre->padre->numero_documento,
                'celular' => (string) ($padre->padre->telefono ?: ($padre->padre->celular ?: 'N/A')),
                'parentesco' => 'PADRE'
            ] : null;

            $madreData = $madre && $madre->padre ? [
                'nombre' => trim("{$madre->padre->nombre} {$madre->padre->apellido_paterno} {$madre->padre->apellido_materno}"),
                'numero_documento' => (string) $madre->padre->numero_documento,
                'celular' => (string) ($madre->padre->telefono ?: ($madre->padre->celular ?: 'N/A')),
                'parentesco' => 'MADRE'
            ] : null;

            // Obtener pagos desde la API de UNAMAD
            $paymentService = app(\App\Services\PaymentValidationService::class);
            $pagos = $paymentService->validateVoucher($postulacion->estudiante->numero_documento, null, false) ?? [];

            // Calcular monto total pagado desde los pagos reales
            $totalPagado = collect($pagos)->sum(function($p) {
                return (float) ($p['total'] ?? 0);
            });

            // Map documents
            $documentos = [
                'dni' => ['nombre' => 'DNI del Postulante', 'existe' => !empty($postulacion->dni_path), 'url' => $postulacion->dni_path ? \Illuminate\Support\Facades\Storage::url($postulacion->dni_path) : null],
                'certificado' => ['nombre' => 'Certificado de Estudios', 'existe' => !empty($postulacion->certificado_estudios_path), 'url' => $postulacion->certificado_estudios_path ? \Illuminate\Support\Facades\Storage::url($postulacion->certificado_estudios_path) : null],
                'foto' => ['nombre' => 'Fotografía', 'existe' => !empty($postulacion->foto_path), 'url' => $postulacion->foto_path ? \Illuminate\Support\Facades\Storage::url($postulacion->foto_path) : null],
                'voucher' => ['nombre' => 'Voucher de Pago', 'existe' => !empty($postulacion->voucher_path), 'url' => $postulacion->voucher_path ? \Illuminate\Support\Facades\Storage::url($postulacion->voucher_path) : null],
                'compromiso' => ['nombre' => 'Carta de Compromiso', 'existe' => !empty($postulacion->carta_compromiso_path), 'url' => $postulacion->carta_compromiso_path ? \Illuminate\Support\Facades\Storage::url($postulacion->carta_compromiso_path) : null],
                'constancia' => ['nombre' => 'Constancia de Estudios', 'existe' => !empty($postulacion->constancia_estudios_path), 'url' => $postulacion->constancia_estudios_path ? \Illuminate\Support\Facades\Storage::url($postulacion->constancia_estudios_path) : null],
                'firmada' => ['nombre' => 'Constancia Firmada', 'existe' => !empty($postulacion->constancia_firmada_path), 'url' => $postulacion->constancia_firmada_path ? \Illuminate\Support\Facades\Storage::url($postulacion->constancia_firmada_path) : null],
            ];

            return $this->sendResponse([
                'postulacion' => array_merge($postulacion->toArray(), [
                    'monto_total_pagado' => $totalPagado
                ]),
                'estudiante' => [
                    'nombre_completo' => "{$postulacion->estudiante->nombre} {$postulacion->estudiante->apellido_paterno} {$postulacion->estudiante->apellido_materno}",
                    'numero_documento' => $postulacion->estudiante->numero_documento,
                    'email' => $postulacion->estudiante->email,
                    'celular' => (string) ($postulacion->estudiante->telefono ?: 'N/A'),
                    'direccion' => $postulacion->estudiante->direccion,
                    'foto_perfil' => $postulacion->estudiante->foto_perfil ? asset(\Illuminate\Support\Facades\Storage::url($postulacion->estudiante->foto_perfil)) : null,
                ],
                'inscripcion' => $inscripcion ? [
                    'aula' => $inscripcion->aula ? $inscripcion->aula->nombre : 'Asignando...',
                    'codigo' => $inscripcion->codigo_inscripcion,
                    'estado' => $inscripcion->estado_inscripcion,
                ] : null,
                'padre' => $padreData,
                'madre' => $madreData,
                'documentos' => $documentos,
                'pagos' => $pagos,
                'academic_info' => [
                    'anio_egreso' => $postulacion->anio_egreso,
                    'centro_educativo' => $postulacion->centroEducativo ? $postulacion->centroEducativo->cen_edu : ($postulacion->colegio_nombre_manual ?? 'N/A'),
                ],
            ], 'Detalle de postulación recuperado.');
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar detalle.', ['error' => $e->getMessage()]);
        }
    }
}
