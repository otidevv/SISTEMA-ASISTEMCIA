<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\User;
use App\Models\Inscripcion;
use App\Models\Aula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostulacionController extends Controller
{
    /**
     * Mostrar la vista principal de postulaciones
     */
    public function index()
    {
        // Verificar permisos
        if (!Auth::user()->hasPermission('postulaciones.view')) {
            abort(403, 'No tienes permisos para ver postulaciones');
        }

        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        $carreras = Carrera::where('estado', true)->orderBy('nombre')->get();
        $turnos = Turno::where('estado', true)->orderBy('nombre')->get();

        return view('postulaciones.index', compact('ciclos', 'carreras', 'turnos'));
    }

    /**
     * Obtener lista de postulaciones en formato JSON para DataTables
     */
    public function listar(Request $request)
    {
        if (!Auth::user()->hasPermission('postulaciones.view')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $query = Postulacion::with(['estudiante', 'ciclo', 'carrera', 'turno']);

            // Filtros opcionales
            if ($request->ciclo_id) {
                $query->where('ciclo_id', $request->ciclo_id);
            }
            if ($request->estado) {
                $query->where('estado', $request->estado);
            }
            if ($request->carrera_id) {
                $query->where('carrera_id', $request->carrera_id);
            }

            $postulaciones = $query->orderBy('created_at', 'desc')->get();

            $data = $postulaciones->map(function ($postulacion) {
                $actions = $this->generarAcciones($postulacion);
                
                return [
                    'id' => $postulacion->id,
                    'codigo' => $postulacion->codigo_postulante,
                    'estudiante' => $postulacion->estudiante->nombre . ' ' . 
                                   $postulacion->estudiante->apellido_paterno . ' ' . 
                                   $postulacion->estudiante->apellido_materno,
                    'dni' => $postulacion->estudiante->numero_documento ?? 'N/A',
                    'email' => $postulacion->estudiante->email,
                    'ciclo' => $postulacion->ciclo->nombre,
                    'carrera' => $postulacion->carrera->nombre,
                    'turno' => $postulacion->turno->nombre,
                    'tipo_inscripcion' => $postulacion->tipo_inscripcion,
                    'fecha_postulacion' => $postulacion->fecha_postulacion->format('d/m/Y H:i'),
                    'estado' => $postulacion->estado,
                    'documentos_verificados' => $postulacion->documentos_verificados,
                    'pago_verificado' => $postulacion->pago_verificado,
                    'numero_recibo' => $postulacion->numero_recibo,
                    'monto_total' => $postulacion->monto_total_pagado,
                    'constancia_generada' => $postulacion->constancia_generada,
                    'constancia_firmada' => $postulacion->constancia_firmada,
                    'constancia_estado' => $this->generarEstadoConstancia($postulacion),
                    'actions' => $actions
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar postulaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de una postulación
     */
    public function show($id)
    {
        if (!Auth::user()->hasPermission('postulaciones.show')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $postulacion = Postulacion::with(['estudiante', 'ciclo', 'carrera', 'turno', 'centroEducativo'])
                ->findOrFail($id);

            // Preparar información de documentos
            $documentos = [
                'dni' => [
                    'nombre' => 'DNI del Postulante',
                    'existe' => !empty($postulacion->dni_path),
                    'url' => $postulacion->dni_path ? Storage::url($postulacion->dni_path) : null
                ],
                'certificado_estudios' => [
                    'nombre' => 'Certificado de Estudios',
                    'existe' => !empty($postulacion->certificado_estudios_path),
                    'url' => $postulacion->certificado_estudios_path ? Storage::url($postulacion->certificado_estudios_path) : null
                ],
                'foto' => [
                    'nombre' => 'Fotografía',
                    'existe' => !empty($postulacion->foto_path),
                    'url' => $postulacion->foto_path ? Storage::url($postulacion->foto_path) : null
                ],
                'voucher' => [
                    'nombre' => 'Voucher de Pago',
                    'existe' => !empty($postulacion->voucher_path),
                    'url' => $postulacion->voucher_path ? Storage::url($postulacion->voucher_path) : null
                ],
                'carta_compromiso' => [
                    'nombre' => 'Carta de Compromiso',
                    'existe' => !empty($postulacion->carta_compromiso_path),
                    'url' => $postulacion->carta_compromiso_path ? Storage::url($postulacion->carta_compromiso_path) : null
                ],
                'constancia_estudios' => [
                    'nombre' => 'Constancia de Estudios',
                    'existe' => !empty($postulacion->constancia_estudios_path),
                    'url' => $postulacion->constancia_estudios_path ? Storage::url($postulacion->constancia_estudios_path) : null
                ],
                'constancia_firmada' => [
                    'nombre' => 'Constancia Firmada',
                    'existe' => !empty($postulacion->constancia_firmada_path),
                    'url' => $postulacion->constancia_firmada_path ? Storage::url($postulacion->constancia_firmada_path) : null
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'postulacion' => $postulacion,
                    'documentos' => $documentos
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener postulación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar documentos
     */
    public function verificarDocumentos(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.verify_documents')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $postulacion = Postulacion::findOrFail($id);
            $postulacion->documentos_verificados = $request->verificado ? 1 : 0;
            $postulacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Documentos ' . ($request->verificado ? 'verificados' : 'marcados como no verificados')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar pago
     */
    public function verificarPago(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.verify_payment')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $postulacion = Postulacion::findOrFail($id);
            $postulacion->pago_verificado = $request->verificado ? 1 : 0;
            $postulacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Pago ' . ($request->verificado ? 'verificado' : 'marcado como no verificado')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar postulación
     */
    public function rechazar(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.reject')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'motivo' => 'required|string|min:10'
        ]);

        try {
            $postulacion = Postulacion::findOrFail($id);
            $postulacion->rechazar(Auth::id(), $request->motivo);

            return response()->json([
                'success' => true,
                'message' => 'Postulación rechazada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Observar postulación
     */
    public function observar(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.observe')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'observaciones' => 'required|string|min:10'
        ]);

        try {
            $postulacion = Postulacion::findOrFail($id);
            $postulacion->observar(Auth::id(), $request->observaciones);

            return response()->json([
                'success' => true,
                'message' => 'Postulación marcada con observaciones'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Aprobar postulación y crear inscripción
     */
    public function aprobar(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.approve')) {
            return response()->json(['error' => 'Sin permisos para aprobar postulaciones'], 403);
        }

        DB::beginTransaction();
        
        try {
            // Obtener la postulación con sus relaciones
            $postulacion = Postulacion::with(['estudiante', 'ciclo', 'carrera', 'turno', 'centroEducativo'])
                ->findOrFail($id);
            
            // Verificar que la postulación esté pendiente
            if ($postulacion->estado !== 'pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar postulaciones pendientes'
                ], 400);
            }
            
            // Verificar que los documentos y pago estén verificados
            if (!$postulacion->documentos_verificados || !$postulacion->pago_verificado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los documentos y pago deben estar verificados antes de aprobar'
                ], 400);
            }
            
            // Buscar un aula disponible usando la lógica mejorada
            $aula = $this->asignarAulaDisponible($postulacion->turno_id, $postulacion->carrera_id, $postulacion->tipo_inscripcion);
            
            if (!$aula) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay aulas disponibles con capacidad suficiente para el turno y carrera seleccionados'
                ], 400);
            }
            
            // Crear la inscripción
            $inscripcion = new Inscripcion();
            $inscripcion->codigo_inscripcion = $postulacion->codigo_postulante;
            $inscripcion->estudiante_id = $postulacion->estudiante_id;
            $inscripcion->carrera_id = $postulacion->carrera_id;
            $inscripcion->tipo_inscripcion = $postulacion->tipo_inscripcion;
            $inscripcion->ciclo_id = $postulacion->ciclo_id;
            $inscripcion->turno_id = $postulacion->turno_id;
            $inscripcion->aula_id = $aula->id;
            $inscripcion->centro_educativo_id = $postulacion->centro_educativo_id;
            $inscripcion->fecha_inscripcion = now();
            $inscripcion->estado_inscripcion = 'activo';
            $inscripcion->observaciones = 'Inscripción generada desde postulación aprobada #' . $postulacion->id;
            $inscripcion->registrado_por = Auth::id();
            $inscripcion->save();
            
            // Actualizar el estado de la postulación
            $postulacion->aprobar(Auth::id());
            
            // Cambiar rol de postulante a estudiante
            if ($postulacion->tipo_inscripcion === 'postulante') {
                $estudiante = User::find($postulacion->estudiante_id);
                if ($estudiante) {
                    // Remover rol de postulante si lo tiene
                    if ($estudiante->hasRole('postulante')) {
                        $estudiante->removeRole('postulante');
                    }
                    // Asignar rol de estudiante si no lo tiene
                    if (!$estudiante->hasRole('estudiante')) {
                        $estudiante->assignRole('estudiante');
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Postulación aprobada exitosamente. Se ha creado la inscripción y asignado al aula ' . $aula->nombre . ' (Grupo ' . $this->determinarGrupoCarrera($postulacion->carrera->nombre) . ')',
                'data' => [
                    'inscripcion_id' => $inscripcion->id,
                    'codigo_inscripcion' => $inscripcion->codigo_inscripcion,
                    'aula' => $aula->nombre,
                    'aula_capacidad_disponible' => $aula->getCapacidadDisponible() - 1,
                    'grupo_carrera' => $this->determinarGrupoCarrera($postulacion->carrera->nombre)
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar postulación: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Asignar aula disponible según turno, carrera y capacidad - LÓGICA MEJORADA
     */
    private function asignarAulaDisponible($turnoId, $carreraId, $tipoInscripcion = 'postulante')
    {
        $carrera = Carrera::find($carreraId);
        $turno = Turno::find($turnoId);
        
        // 1. Determinar el grupo de la carrera
        $grupoCarrera = $this->determinarGrupoCarrera($carrera->nombre);
        $turnoNombre = strtoupper($turno->nombre); // MAÑANA o TARDE
        
        // 2. Buscar aulas específicas del grupo y turno
        $query = Aula::activas()->where('estado', 1);
        
        // Filtrar por turno específico
        $query->where('nombre', 'like', '%' . $turnoNombre . '%');
        
        $aulas = collect();
        
        // Priorizar aulas del grupo específico
        if ($grupoCarrera) {
            $aulasGrupoEspecifico = clone $query;
            $aulasGrupoEspecifico->where(function($q) use ($grupoCarrera) {
                $q->where('codigo', 'like', $grupoCarrera . '%')
                  ->orWhere('codigo', 'like', '%' . $grupoCarrera . '%'); // Para casos como AB, ABC
            });
            
            $aulas = $aulasGrupoEspecifico->get()->filter(function ($aula) {
                return $aula->getCapacidadDisponible() > 0;
            });
        }
        
        // 3. Si no hay aulas del grupo específico, buscar aulas mixtas (AB, ABC) del mismo turno
        if ($aulas->isEmpty()) {
            $aulasMixtas = clone $query;
            $aulasMixtas->where(function($q) {
                $q->where('codigo', 'like', 'AB%')
                  ->orWhere('codigo', 'like', 'ABC%');
            });
            
            $aulas = $aulasMixtas->get()->filter(function ($aula) {
                return $aula->getCapacidadDisponible() > 0;
            });
        }
        
        // 4. Como último recurso, cualquier aula del turno
        if ($aulas->isEmpty()) {
            $aulas = $query->get()->filter(function ($aula) {
                return $aula->getCapacidadDisponible() > 0;
            });
        }
        
        // 5. Ordenar por prioridad
        $aulas = $aulas->sortBy(function ($aula) use ($grupoCarrera) {
            $score = 0;
            
            // Alta prioridad para aulas del grupo específico
            if ($grupoCarrera && str_starts_with($aula->codigo, $grupoCarrera)) {
                $score -= 100;
            }
            
            // Media prioridad para aulas mixtas
            if (str_starts_with($aula->codigo, 'AB') || str_starts_with($aula->codigo, 'ABC')) {
                $score -= 50;
            }
            
            // Equilibrar ocupación (llenar las más ocupadas primero)
            $score += (100 - $aula->getPorcentajeOcupacion());
            
            // Preferir capacidades apropiadas
            $capacidadIdeal = $this->getCapacidadIdealPorGrupo($grupoCarrera);
            $score += abs($aula->capacidad - $capacidadIdeal);
            
            return $score;
        });
        
        return $aulas->first();
    }

    /**
     * Determinar el grupo de carrera según CEPRE UNAMAD
     */
    private function determinarGrupoCarrera($nombreCarrera)
    {
        $nombreLower = strtolower($nombreCarrera);
        
        // Grupo A - Ingenierías
        if (str_contains($nombreLower, 'ingenieria') || 
            str_contains($nombreLower, 'sistemas') ||
            str_contains($nombreLower, 'informatica') ||
            str_contains($nombreLower, 'forestal') ||
            str_contains($nombreLower, 'medio ambiente') ||
            str_contains($nombreLower, 'agroindustrial')) {
            return 'A';
        }
        
        // Grupo B - Ciencias de la Salud
        if (str_contains($nombreLower, 'medicina') ||
            str_contains($nombreLower, 'veterinaria') ||
            str_contains($nombreLower, 'zootecnia') ||
            str_contains($nombreLower, 'enfermeria')) {
            return 'B';
        }
        
        // Grupo C - Ciencias Sociales y Educación
        if (str_contains($nombreLower, 'administracion') ||
            str_contains($nombreLower, 'negocios') ||
            str_contains($nombreLower, 'contabilidad') ||
            str_contains($nombreLower, 'finanzas') ||
            str_contains($nombreLower, 'derecho') ||
            str_contains($nombreLower, 'ciencias politicas') ||
            str_contains($nombreLower, 'ecoturismo') ||
            str_contains($nombreLower, 'educacion') ||
            str_contains($nombreLower, 'primaria') ||
            str_contains($nombreLower, 'inicial') ||
            str_contains($nombreLower, 'especial') ||
            str_contains($nombreLower, 'matematica') ||
            str_contains($nombreLower, 'computacion')) {
            return 'C';
        }
        
        return null; // Si no coincide con ningún grupo
    }

    /**
     * Obtener capacidad ideal por grupo de carreras
     */
    private function getCapacidadIdealPorGrupo($grupo)
    {
        switch ($grupo) {
            case 'A': return 30; // Ingenierías - grupos medianos
            case 'B': return 25; // Ciencias de salud - grupos más pequeños
            case 'C': return 35; // Ciencias sociales - grupos más grandes
            default: return 30;
        }
    }

    /**
     * Eliminar postulación
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasPermission('postulaciones.delete')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $postulacion = Postulacion::findOrFail($id);
            
            // Eliminar archivos asociados
            if ($postulacion->voucher_pago_path) Storage::delete($postulacion->voucher_pago_path);
            if ($postulacion->certificado_estudios_path) Storage::delete($postulacion->certificado_estudios_path);
            if ($postulacion->carta_compromiso_path) Storage::delete($postulacion->carta_compromiso_path);
            if ($postulacion->constancia_estudios_path) Storage::delete($postulacion->constancia_estudios_path);
            if ($postulacion->dni_path) Storage::delete($postulacion->dni_path);
            if ($postulacion->foto_carnet_path) Storage::delete($postulacion->foto_carnet_path);
            
            $postulacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Postulación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar acciones disponibles según permisos
     */
    private function generarAcciones($postulacion)
    {
        $actions = [];
        $user = Auth::user();

        // Ver detalle
        if ($user->hasPermission('postulaciones.show')) {
            $actions[] = '<button class="btn btn-sm btn-info view-postulacion" data-id="' . $postulacion->id . '" title="Ver detalle">
                <i class="uil uil-eye"></i>
            </button>';
        }

        // Verificar documentos
        if ($user->hasPermission('postulaciones.verify_documents') && $postulacion->estado == 'pendiente') {
            $checkIcon = $postulacion->documentos_verificados ? 'uil-check-circle' : 'uil-file-check-alt';
            $checkClass = $postulacion->documentos_verificados ? 'btn-success' : 'btn-warning';
            $actions[] = '<button class="btn btn-sm ' . $checkClass . ' verify-docs" data-id="' . $postulacion->id . '" 
                data-verified="' . $postulacion->documentos_verificados . '" title="Verificar documentos">
                <i class="uil ' . $checkIcon . '"></i>
            </button>';
        }

        // Verificar pago
        if ($user->hasPermission('postulaciones.verify_payment') && $postulacion->estado == 'pendiente') {
            $payIcon = $postulacion->pago_verificado ? 'uil-money-bill' : 'uil-money-withdraw';
            $payClass = $postulacion->pago_verificado ? 'btn-success' : 'btn-warning';
            $actions[] = '<button class="btn btn-sm ' . $payClass . ' verify-payment" data-id="' . $postulacion->id . '" 
                data-verified="' . $postulacion->pago_verificado . '" title="Verificar pago">
                <i class="uil ' . $payIcon . '"></i>
            </button>';
        }

        // Observar
        if ($user->hasPermission('postulaciones.observe') && $postulacion->estado == 'pendiente') {
            $actions[] = '<button class="btn btn-sm btn-warning observe-postulacion" data-id="' . $postulacion->id . '" title="Observar">
                <i class="uil uil-comment-exclamation"></i>
            </button>';
        }

        // Rechazar
        if ($user->hasPermission('postulaciones.reject') && $postulacion->estado == 'pendiente') {
            $actions[] = '<button class="btn btn-sm btn-danger reject-postulacion" data-id="' . $postulacion->id . '" title="Rechazar">
                <i class="uil uil-times-circle"></i>
            </button>';
        }

        // Aprobar
        if ($user->hasPermission('postulaciones.approve') && 
            $postulacion->estado == 'pendiente' && 
            $postulacion->documentos_verificados && 
            $postulacion->pago_verificado) {
            $actions[] = '<button class="btn btn-sm btn-success approve-postulacion" data-id="' . $postulacion->id . '" title="Aprobar">
                <i class="uil uil-check-circle"></i>
            </button>';
        }

        // Eliminar
        if ($user->hasPermission('postulaciones.delete')) {
            $actions[] = '<button class="btn btn-sm btn-danger delete-postulacion" data-id="' . $postulacion->id . '" title="Eliminar">
                <i class="uil uil-trash-alt"></i>
            </button>';
        }

        return '<div class="btn-group">' . implode(' ', $actions) . '</div>';
    }
    
    /**
     * Generar estado HTML de la constancia
     */
    private function generarEstadoConstancia($postulacion)
    {
        if ($postulacion->constancia_firmada) {
            return '<span class="badge bg-success">
                <i class="uil uil-check-circle"></i> Completa
            </span>';
        } elseif ($postulacion->constancia_generada) {
            return '<span class="badge bg-warning">
                <i class="uil uil-file-download-alt"></i> Generada
            </span>';
        } else {
            return '<span class="badge bg-secondary">
                <i class="uil uil-times-circle"></i> Pendiente
            </span>';
        }
    }
    
    /**
     * Obtener la postulación actual del estudiante autenticado
     */
    public function miPostulacionActual()
    {
        try {
            $user = Auth::user();
            
            // Obtener el ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            if (!$cicloActivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay ciclo activo',
                    'postulacion' => null
                ]);
            }
            
            // Buscar postulación del usuario en el ciclo activo
            $postulacion = Postulacion::where('estudiante_id', $user->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->with(['carrera', 'turno', 'ciclo'])
                ->first();
            
            if ($postulacion) {
                return response()->json([
                    'success' => true,
                    'postulacion' => [
                        'id' => $postulacion->id,
                        'codigo_postulante' => $postulacion->codigo_postulante,
                        'estado' => $postulacion->estado,
                        'fecha_postulacion' => $postulacion->fecha_postulacion,
                        'carrera_nombre' => $postulacion->carrera->nombre ?? '',
                        'turno_nombre' => $postulacion->turno->nombre ?? '',
                        'constancia_generada' => $postulacion->constancia_generada,
                        'constancia_firmada' => $postulacion->constancia_firmada,
                        'fecha_constancia_generada' => $postulacion->fecha_constancia_generada,
                        'fecha_constancia_subida' => $postulacion->fecha_constancia_subida,
                        'documentos_verificados' => $postulacion->documentos_verificados,
                        'pago_verificado' => $postulacion->pago_verificado,
                        'observaciones' => $postulacion->observaciones,
                        'motivo_rechazo' => $postulacion->motivo_rechazo
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'postulacion' => null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener postulación: ' . $e->getMessage(),
                'postulacion' => null
            ], 500);
        }
    }

    /**
     * Actualizar documentos de una postulación
     */
    public function actualizarDocumentos(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.edit')) {
            return response()->json(['error' => 'Sin permisos para editar documentos'], 403);
        }

        try {
            $postulacion = Postulacion::findOrFail($id);
            $estudiante = $postulacion->estudiante;
            
            // Validar archivos
            $request->validate([
                'dni' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'certificado_estudios' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'foto' => 'nullable|image|max:5120',
                'voucher' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'carta_compromiso' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'constancia_estudios' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'constancia_firmada' => 'nullable|file|mimes:pdf|max:5120',
                'observacion' => 'nullable|string|max:500'
            ]);

            $documentosActualizados = [];

            // Procesar DNI
            if ($request->hasFile('dni')) {
                // Eliminar archivo anterior si existe
                if ($postulacion->dni_path && Storage::exists($postulacion->dni_path)) {
                    Storage::delete($postulacion->dni_path);
                }
                
                $path = $request->file('dni')->store('documentos/dni', 'public');
                $postulacion->dni_path = $path;
                $documentosActualizados[] = 'DNI';
            }

            // Procesar Certificado de Estudios
            if ($request->hasFile('certificado_estudios')) {
                if ($postulacion->certificado_estudios_path && Storage::exists($postulacion->certificado_estudios_path)) {
                    Storage::delete($postulacion->certificado_estudios_path);
                }
                
                $path = $request->file('certificado_estudios')->store('documentos/certificados', 'public');
                $postulacion->certificado_estudios_path = $path;
                $documentosActualizados[] = 'Certificado de Estudios';
            }

            // Procesar Foto
            if ($request->hasFile('foto')) {
                if ($postulacion->foto_path && Storage::exists($postulacion->foto_path)) {
                    Storage::delete($postulacion->foto_path);
                }
                
                $path = $request->file('foto')->store('documentos/fotos', 'public');
                $postulacion->foto_path = $path;
                
                // También actualizar la foto de perfil del estudiante
                if ($estudiante->foto_perfil && Storage::exists($estudiante->foto_perfil)) {
                    Storage::delete($estudiante->foto_perfil);
                }
                $estudiante->foto_perfil = $path;
                $estudiante->save();
                
                $documentosActualizados[] = 'Fotografía';
            }

            // Procesar Voucher
            if ($request->hasFile('voucher')) {
                if ($postulacion->voucher_path && Storage::exists($postulacion->voucher_path)) {
                    Storage::delete($postulacion->voucher_path);
                }
                
                $path = $request->file('voucher')->store('documentos/vouchers', 'public');
                $postulacion->voucher_path = $path;
                $documentosActualizados[] = 'Voucher de Pago';
            }

            // Procesar Carta de Compromiso
            if ($request->hasFile('carta_compromiso')) {
                if ($postulacion->carta_compromiso_path && Storage::exists($postulacion->carta_compromiso_path)) {
                    Storage::delete($postulacion->carta_compromiso_path);
                }
                
                $path = $request->file('carta_compromiso')->store('documentos/carta_compromiso', 'public');
                $postulacion->carta_compromiso_path = $path;
                $documentosActualizados[] = 'Carta de Compromiso';
            }

            // Procesar Constancia de Estudios
            if ($request->hasFile('constancia_estudios')) {
                if ($postulacion->constancia_estudios_path && Storage::exists($postulacion->constancia_estudios_path)) {
                    Storage::delete($postulacion->constancia_estudios_path);
                }
                
                $path = $request->file('constancia_estudios')->store('documentos/constancia_estudios', 'public');
                $postulacion->constancia_estudios_path = $path;
                $documentosActualizados[] = 'Constancia de Estudios';
            }

            // Procesar Constancia Firmada
            if ($request->hasFile('constancia_firmada')) {
                if ($postulacion->constancia_firmada_path && Storage::exists($postulacion->constancia_firmada_path)) {
                    Storage::delete($postulacion->constancia_firmada_path);
                }
                
                $path = $request->file('constancia_firmada')->store('documentos/constancias_firmadas', 'public');
                $postulacion->constancia_firmada_path = $path;
                $postulacion->constancia_firmada = true;
                $postulacion->fecha_constancia_subida = now();
                $documentosActualizados[] = 'Constancia Firmada';
            }

            // Guardar observación si existe
            if ($request->filled('observacion')) {
                $observacionAnterior = $postulacion->observaciones ?? '';
                $nuevaObservacion = '[' . now()->format('d/m/Y H:i') . ' - Actualización de documentos] ' . 
                                   $request->observacion . "\n" . $observacionAnterior;
                $postulacion->observaciones = $nuevaObservacion;
            }

            // Registrar quién y cuándo actualizó
            $postulacion->actualizado_por = Auth::id();
            $postulacion->fecha_actualizacion = now();
            
            $postulacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Documentos actualizados correctamente',
                'documentos_actualizados' => $documentosActualizados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar documentos: ' . $e->getMessage()
            ], 500);
        }
    }
}