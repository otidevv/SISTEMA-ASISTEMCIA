<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\User;
use App\Models\Inscripcion;
use App\Models\Aula;
use App\Models\CentroEducativo;
use App\Models\Parentesco;
use App\Models\RegistroAsistencia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exports\PostulacionesCompletoExport;
use App\Exports\PostulacionesResumenExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Imports\PostulantesImport;
use App\Exports\PostulantesTemplateExport;

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

        $cicloActivo = Ciclo::where('es_activo', true)->first();

        // Obtener ciclos y carreras activos para precargar los filtros
        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        $carreras = Carrera::where('estado', true)->orderBy('nombre')->get();

        return view('postulaciones.index', compact('cicloActivo', 'ciclos', 'carreras'));
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
            $query = Postulacion::with(['estudiante', 'ciclo', 'carrera', 'turno', 'centroEducativo'])
                ->select('postulaciones.*');

            // Filtro de ciclo: si no se especifica un ciclo, usar el ciclo activo por defecto.
            $cicloId = $request->input('ciclo_id');
            if (empty($cicloId)) {
                $cicloActivo = Ciclo::where('es_activo', true)->first();
                // Si hay un ciclo activo, usamos su ID. Si no, usamos un ID inválido para no devolver nada. 
                $cicloId = $cicloActivo ? $cicloActivo->id : -1; 
            }

            $query->where('ciclo_id', $cicloId);

            // Otros filtros
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }
            if ($request->filled('carrera_id')) {
                $query->where('carrera_id', $request->carrera_id);
            }

            return DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($search = $request->input('search.value')) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('estudiante', function ($q2) use ($search) {
                                $q2->where('nombre', 'like', "%{$search}%")
                                   ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                   ->orWhere('apellido_materno', 'like', "%{$search}%")
                                   ->orWhere('numero_documento', 'like', "%{$search}%");
                            })
                            ->orWhereHas('carrera', function ($q3) use ($search) {
                                $q3->where('nombre', 'like', "%{$search}%");
                            })
                            ->orWhereHas('turno', function ($q4) use ($search) {
                                $q4->where('nombre', 'like', "%{$search}%");
                            })
                            ->orWhereHas('centroEducativo', function ($q5) use ($search) {
                                $q5->where('cen_edu', 'like', "%{$search}%");
                            })
                            ->orWhere('codigo_postulante', 'like', "%{$search}%")
                            ->orWhere('estado', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('actions', function ($postulacion) {
                    return $this->generarAcciones($postulacion);
                })
                ->addColumn('estudiante_nombre', function ($postulacion) {
                    return $postulacion->estudiante?->nombre . ' ' . 
                           $postulacion->estudiante?->apellido_paterno . ' ' . 
                           $postulacion->estudiante?->apellido_materno;
                })
                ->addColumn('dni', function ($postulacion) {
                    return $postulacion->estudiante?->numero_documento ?? 'N/A';
                })
                ->addColumn('email', function ($postulacion) {
                    return $postulacion->estudiante?->email;
                })
                ->addColumn('ciclo_nombre', function ($postulacion) {
                    return $postulacion->ciclo?->nombre;
                })
                ->addColumn('carrera_nombre', function ($postulacion) {
                    return $postulacion->carrera?->nombre;
                })
                ->addColumn('turno_nombre', function ($postulacion) {
                    return $postulacion->turno?->nombre;
                })
                ->editColumn('fecha_postulacion', function ($postulacion) {
                    return $postulacion->fecha_postulacion ? $postulacion->fecha_postulacion->format('d/m/Y H:i') : '';
                })
                ->addColumn('constancia_estado_html', function ($postulacion) {
                    return $this->generarEstadoConstancia($postulacion);
                })
                ->rawColumns(['actions', 'constancia_estado_html'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error en PostulacionController@listar: ' . $e->getMessage() . ' Archivo: ' . $e->getFile() . ' Línea: ' . $e->getLine());
            return response()->json([
                'error' => 'Error al cargar postulaciones. Por favor, revise los logs del sistema.'
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
            $postulacion = Postulacion::with([
                'estudiante.parentescos.padre', // Eager load parentescos and their associated 'padre' (User) 
                'ciclo',
                'carrera',
                'turno',
                'centroEducativo'
            ])->findOrFail($id);

            // Buscar la inscripción asociada si existe y cargar el aula
            $inscripcion = null;
            if ($postulacion->estado === 'aprobado') {
                $inscripcion = Inscripcion::where('estudiante_id', $postulacion->estudiante_id)
                    ->where('ciclo_id', $postulacion->ciclo_id)
                    ->with('aula') // Cargar la relación con el aula
                    ->first();
            }

            // Extraer datos del padre y la madre
            $padre = $postulacion->estudiante->parentescos->where('tipo_parentesco', 'Padre')->first();
            $madre = $postulacion->estudiante->parentescos->where('tipo_parentesco', 'Madre')->first();

            $padreData = $padre && $padre->padre ? $padre->padre->toArray() : null;
            $madreData = $madre && $madre->padre ? $madre->padre->toArray() : null;

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

            // Agregar la URL de la foto de perfil del estudiante si existe
            $fotoPerfilUrl = $postulacion->estudiante->foto_perfil ? Storage::url($postulacion->estudiante->foto_perfil) : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'postulacion' => array_merge($postulacion->toArray(), [
                        'centro_educativo' => $postulacion->centroEducativo ? [
                            'nombre' => $postulacion->centroEducativo->cen_edu
                        ] : null
                    ]),
                    'documentos' => $documentos,
                    'inscripcion' => $inscripcion, // Incluir la inscripción en la respuesta
                    'foto_perfil_url' => $fotoPerfilUrl, // Incluir la URL de la foto de perfil
                    'padre' => $padreData, // Incluir datos del padre
                    'madre' => $madreData // Incluir datos de la madre
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
                $grupoInfo = $postulacion->carrera->grupo ? ' del Grupo ' . $postulacion->carrera->grupo : '';
                return response()->json([
                    'success' => false,
                    'message' => 'No hay aulas disponibles con capacidad suficiente' . $grupoInfo . ' para el turno ' . $postulacion->turno->nombre . ' y carrera ' . $postulacion->carrera->nombre
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
            if ($postulacion->tipo_inscripcion === 'postulante' || $postulacion->tipo_inscripcion === 'reforzamiento') {
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
                'message' => 'Postulación aprobada exitosamente. Se ha creado la inscripción y asignado al aula ' . $aula->nombre . ' (Grupo ' . $postulacion->carrera->grupo . ')',
                'data' => [
                    'inscripcion_id' => $inscripcion->id,
                    'codigo_inscripcion' => $inscripcion->codigo_inscripcion,
                    'aula' => $aula->nombre,
                    'aula_capacidad_disponible' => $aula->getCapacidadDisponible() - 1,
                    'grupo_carrera' => $postulacion->carrera->grupo
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
     * Asignar aula disponible según turno, carrera y capacidad - LÓGICA ROBUSTA
     */
    private function asignarAulaDisponible($turnoId, $carreraId, $tipoInscripcion = 'postulante')
    {
        $carrera = Carrera::find($carreraId);
        $turno = Turno::find($turnoId);
        
        // 1. Obtener el grupo de la carrera desde la base de datos
        $grupoCarrera = $carrera->grupo;
        $turnoNombre = strtoupper($turno->nombre); // MAÑANA o TARDE
        
        // 2. Construir la consulta completa ANTES de withCount
        // Esto asegura que inscripciones_count se calcule correctamente solo para las aulas filtradas
        $queryAulas = Aula::activas()
            ->where('nombre', 'like', '%' . $turnoNombre . '%');
        
        // 3. Aplicar filtro de grupo si la carrera tiene uno asignado
        // Filtrar SOLO por nombre (el codigo puede tener valores inconsistentes)
        if ($grupoCarrera) {
            $queryAulas->where('nombre', 'like', $grupoCarrera . '-%');
        }
        
        // 4. AHORA aplicar withCount DESPUÉS de todos los filtros
        // Esto garantiza que inscripciones_count solo cuente para aulas del grupo correcto
        $queryAulas->withCount(['inscripciones' => function ($query) { 
            $query->where('estado_inscripcion', 'activo')
                  ->whereHas('ciclo', function ($subQuery) {
                      $subQuery->where('es_activo', true);
                  });
        }]);
        
        // 5. Obtener aulas y filtrar por capacidad disponible
        $aulas = $queryAulas->get()->filter(function ($aula) {
            return ($aula->capacidad - $aula->inscripciones_count) > 0;
        });
        
        // 6. Si no hay aulas disponibles, retornar null
        if ($aulas->isEmpty()) {
            return null;
        }
        
        // 7. Ordenar para llenado progresivo (menos ocupadas primero)
        $aulas = $aulas->sortBy(function ($aula) { 
            // Primero, por el número de inscritos activos (ascendente)
            // Segundo, por el nombre del aula (alfabético)
            return [$aula->inscripciones_count, $aula->nombre];
        });
        
        return $aulas->first();
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

        // Editar postulación aprobada
        if ($user->hasPermission('postulaciones.edit') && $postulacion->estado == 'aprobado') {
            $actions[] = '<button class="btn btn-sm btn-primary edit-approved" data-id="' . $postulacion->id . '" title="Editar postulación aprobada">
                <i class="uil uil-edit"></i>
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
     * Obtener datos de postulación aprobada para editar
     */
    public function editarAprobada($id)
    {
        if (!Auth::user()->hasPermission('postulaciones.edit')) {
            return response()->json(['error' => 'Sin permisos para editar'], 403);
        }

        try {
            $postulacion = Postulacion::with(['estudiante.parentescos.padre', 'ciclo', 'carrera', 'turno'])
                ->findOrFail($id);
            
            // Buscar la inscripción asociada si existe
            $inscripcion = Inscripcion::where('estudiante_id', $postulacion->estudiante_id)
                ->where('ciclo_id', $postulacion->ciclo_id)
                ->first();

            // Extraer datos del padre y la madre
            $padre = $postulacion->estudiante->parentescos->where('tipo_parentesco', 'Padre')->first();
            $madre = $postulacion->estudiante->parentescos->where('tipo_parentesco', 'Madre')->first();

            $padreData = $padre && $padre->padre ? $padre->padre->toArray() : null;
            $madreData = $madre && $madre->padre ? $madre->padre->toArray() : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'estudiante' => $postulacion->estudiante,
                    'postulacion' => array_merge($postulacion->toArray(), [
                        'centro_educativo' => $postulacion->centroEducativo ? [
                            'nombre' => $postulacion->centroEducativo->cen_edu
                        ] : null
                    ]),
                    'inscripcion' => $inscripcion,
                    'padre' => $padreData,
                    'madre' => $madreData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar postulación aprobada
     */
    public function actualizarAprobada(Request $request, $id)
    {
        Log::info('actualizarAprobada method called');
        // dd($request->all()); // Removed dd as it's not needed for the final output
        if (!Auth::user()->hasPermission('postulaciones.edit')) {
            return response()->json(['error' => 'Sin permisos para editar'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'required|email',
            'ciclo_id' => 'required|exists:ciclos,id',
            'carrera_id' => 'required|exists:carreras,id',
            'turno_id' => 'required|exists:turnos,id',
            'aula_id' => 'nullable|exists:aulas,id',
            'tipo_inscripcion' => 'required|in:postulante,reforzamiento',
            'codigo_postulante' => 'nullable|string|max:255|unique:postulaciones,codigo_postulante,' . $id,
            'numero_recibo' => 'nullable|string|max:50',
            'monto_matricula' => 'nullable|numeric|min:0',
            'monto_ensenanza' => 'nullable|numeric|min:0',
            'observacion_cambio' => 'required|string|min:10',

            // Datos del padre (pueden ser opcionales si ya existen)
            'padre_nombre' => 'nullable|string|max:100',
            'padre_apellido_paterno' => 'nullable|string|max:100',
            'padre_apellido_materno' => 'nullable|string|max:100',
            'padre_dni' => 'nullable|string|size:8',
            'padre_telefono' => 'nullable|string|max:15',

            // Datos de la madre (pueden ser opcionales si ya existen)
            'madre_nombre' => 'nullable|string|max:100',
            'madre_apellido_paterno' => 'nullable|string|max:100',
            'madre_apellido_materno' => 'nullable|string|max:100',
            'madre_dni' => 'nullable|string|size:8',
            'madre_telefono' => 'nullable|string|max:15',
        ]);

        DB::beginTransaction();
        
        try {
            $postulacion = Postulacion::findOrFail($id);
            $estudiante = $postulacion->estudiante;
            
            // Guardar el ciclo_id original antes de actualizar la postulación
            $original_ciclo_id = $postulacion->ciclo_id;

            // Actualizar datos del estudiante
            $estudiante->nombre = $request->nombre;
            $estudiante->apellido_paterno = $request->apellido_paterno;
            $estudiante->apellido_materno = $request->apellido_materno;
            $estudiante->telefono = $request->telefono;
            $estudiante->email = $request->email;
            $estudiante->save();

            // Procesar y guardar padres
            if ($request->filled('padre_dni')) {
                $padre = User::updateOrCreate(
                    ['numero_documento' => $request->padre_dni],
                    [
                        'username' => $request->padre_dni,
                        'email' => $request->padre_dni . '@cepre.unamad.edu.pe',
                        'password_hash' => bcrypt($request->padre_dni),
                        'nombre' => $request->padre_nombre,
                        'apellido_paterno' => $request->padre_apellido_paterno,
                        'apellido_materno' => $request->padre_apellido_materno,
                        'telefono' => $request->padre_telefono,
                        'tipo_documento' => 'DNI',
                        'estado' => true
                    ]
                );
                
                if ($padre->wasRecentlyCreated) {
                    $padre->assignRole('padre');
                }

                // Limpiar y actualizar parentesco para 'Padre'
                $parentescosPadre = Parentesco::where('estudiante_id', $estudiante->id)
                                              ->where('tipo_parentesco', 'Padre')
                                              ->get();

                if ($parentescosPadre->isNotEmpty()) {
                    $parentescoPrincipal = $parentescosPadre->first();
                    $idsToDelete = $parentescosPadre->slice(1)->pluck('id');
                    if ($idsToDelete->isNotEmpty()) {
                        Parentesco::destroy($idsToDelete);
                    }
                    $parentescoPrincipal->padre_id = $padre->id;
                    $parentescoPrincipal->save();
                } else {
                    Parentesco::create([
                        'estudiante_id' => $estudiante->id,
                        'tipo_parentesco' => 'Padre',
                        'padre_id' => $padre->id
                    ]);
                }
            }

            if ($request->filled('madre_dni')) {
                $madre = User::updateOrCreate(
                    ['numero_documento' => $request->madre_dni],
                    [
                        'username' => $request->madre_dni,
                        'email' => $request->madre_dni . '@cepre.unamad.edu.pe',
                        'password_hash' => bcrypt($request->madre_dni),
                        'nombre' => $request->madre_nombre,
                        'apellido_paterno' => $request->madre_apellido_paterno,
                        'apellido_materno' => $request->madre_apellido_materno,
                        'telefono' => $request->madre_telefono,
                        'tipo_documento' => 'DNI',
                        'estado' => true
                    ]
                );

                if ($madre->wasRecentlyCreated) {
                    $madre->assignRole('padre');
                }

                // Limpiar y actualizar parentesco para 'Madre'
                $parentescosMadre = Parentesco::where('estudiante_id', $estudiante->id)
                                              ->where('tipo_parentesco', 'Madre')
                                              ->get();

                if ($parentescosMadre->isNotEmpty()) {
                    $parentescoPrincipal = $parentescosMadre->first();
                    $idsToDelete = $parentescosMadre->slice(1)->pluck('id');
                    if ($idsToDelete->isNotEmpty()) {
                        Parentesco::destroy($idsToDelete);
                    }
                    $parentescoPrincipal->padre_id = $madre->id;
                    $parentescoPrincipal->save();
                } else {
                    Parentesco::create([
                        'estudiante_id' => $estudiante->id,
                        'tipo_parentesco' => 'Madre',
                        'padre_id' => $madre->id
                    ]);
                }
            }
            
            // Actualizar datos de la postulación
            $postulacion->codigo_postulante = $request->codigo_postulante;
            $postulacion->ciclo_id = $request->ciclo_id;
            $postulacion->carrera_id = $request->carrera_id;
            $postulacion->turno_id = $request->turno_id;
            $postulacion->tipo_inscripcion = $request->tipo_inscripcion;
            $postulacion->numero_recibo = $request->numero_recibo;
            $postulacion->monto_matricula = $request->monto_matricula;
            $postulacion->monto_ensenanza = $request->monto_ensenanza;
            $postulacion->monto_total_pagado = ($request->monto_matricula ?? 0) + ($request->monto_ensenanza ?? 0);
            
            // Agregar observación al historial
            $observacionActual = $postulacion->observaciones ?? '';
            $nuevaObservacion = date('d/m/Y H:i') . ' - Modificación: ' . $request->observacion_cambio . ' (Por: ' . Auth::user()->nombre . ')';
            $postulacion->observaciones = $observacionActual ? $observacionActual . "\n" . $nuevaObservacion : $nuevaObservacion;
            
            $postulacion->save();
            
            // Si hay inscripción asociada, actualizarla también
            $inscripcion = Inscripcion::where('estudiante_id', $estudiante->id)
                ->where('ciclo_id', $original_ciclo_id) // Usar el ciclo_id original para la búsqueda
                ->first();
                
            if ($inscripcion) {
                $inscripcion->codigo_inscripcion = $request->codigo_postulante;
                $inscripcion->ciclo_id = $request->ciclo_id; // Actualizar al nuevo ciclo
                $inscripcion->carrera_id = $request->carrera_id;
                $inscripcion->turno_id = $request->turno_id;
                $inscripcion->tipo_inscripcion = $request->tipo_inscripcion;
                
                // Si se especifica un aula, actualizarla
                if ($request->aula_id) {
                    $inscripcion->aula_id = $request->aula_id;
                }
                
                $inscripcion->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Postulación actualizada correctamente' . ($inscripcion ? '. La inscripción asociada también fue actualizada.' : '')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar postulación aprobada: ' . $e->getMessage() . ' Archivo: ' . $e->getFile() . ' Línea: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
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
    
    /**
     * Crear postulación desde el panel de administración
     * Este método permite que un admin cree una postulación para un estudiante existente
     */
    public function crearDesdeAdmin(Request $request)
    {
        // Verificar permisos de admin
        if (!Auth::user()->hasPermission('postulaciones.create')) {
            return response()->json(['error' => 'Sin permisos para crear postulaciones'], 403);
        }

        // Validar datos
        $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'tipo_inscripcion' => 'required|in:postulante,reforzamiento',
            'carrera_id' => 'required|exists:carreras,id',
            'turno_id' => 'required|exists:turnos,id',
            'centro_educativo_id' => 'required|exists:mysql_centros.centros_educativos,id',
            // Validación de archivos
            'voucher_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'certificado_estudios' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'carta_compromiso' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'constancia_estudios' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'dni_documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'foto_carnet' => 'required|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        
        try {
            // Obtener el ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            if (!$cicloActivo) {
                throw new \Exception('No hay un ciclo activo para inscripciones');
            }

            // Verificar que el estudiante existe
            $estudiante = User::findOrFail($request->estudiante_id);
            
            // Verificar si ya tiene una postulación en este ciclo
            $postulacionExistente = Postulacion::where('estudiante_id', $estudiante->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();
                
            if ($postulacionExistente) {
                throw new \Exception('El estudiante ya tiene una postulación en este ciclo');
            }

            // Generar código postulante correlativo único basado en el ciclo
            $correlativoInicial = $cicloActivo->correlativo_inicial ?? 100001;

            // Buscar el máximo código de postulante solo para el ciclo actual
            $ultimoCodigo = Postulacion::where('ciclo_id', $cicloActivo->id)->max('codigo_postulante');

            // Determinar el siguiente código, asegurando que sea mayor que el correlativo inicial
            $nuevoCodigo = max((int)$ultimoCodigo + 1, $correlativoInicial);

            // Asegurar que sea único (por si hay concurrencia)
            while (Postulacion::where('codigo_postulante', $nuevoCodigo)->exists()) {
                $nuevoCodigo++;
            }

            // Crear la postulación con el ID del estudiante correcto
            $postulacion = new Postulacion();
            $postulacion->estudiante_id = $estudiante->id; // ID del estudiante, NO del admin
            $postulacion->ciclo_id = $cicloActivo->id;
            $postulacion->carrera_id = $request->carrera_id;
            $postulacion->turno_id = $request->turno_id;
            $postulacion->tipo_inscripcion = $request->tipo_inscripcion;
            $postulacion->codigo_postulante = $nuevoCodigo;
            $postulacion->estado = 'pendiente';
            $postulacion->fecha_postulacion = now();
            $postulacion->centro_educativo_id = $request->centro_educativo_id;
            
            // Datos del voucher
            $postulacion->numero_recibo = $request->numero_recibo;
            $postulacion->fecha_emision_voucher = $request->fecha_emision_voucher;
            $postulacion->monto_matricula = $request->monto_matricula ?? 0;
            $postulacion->monto_ensenanza = $request->monto_ensenanza ?? 0;
            $postulacion->monto_total_pagado = ($request->monto_matricula ?? 0) + ($request->monto_ensenanza ?? 0);
            
            // Registrar que fue creado por un admin usando campos existentes
            $postulacion->observaciones = 'Postulación creada por administrador: ' . Auth::user()->name;
            
            $postulacion->save();

            // Subir documentos
            $documentosPath = 'postulaciones/' . $postulacion->id . '/documentos';
            
            if ($request->hasFile('voucher_pago')) {
                $postulacion->voucher_path = $request->file('voucher_pago')
                    ->store($documentosPath, 'public');
            }
            
            if ($request->hasFile('certificado_estudios')) {
                $postulacion->certificado_estudios_path = $request->file('certificado_estudios')
                    ->store($documentosPath, 'public');
            }
            
            if ($request->hasFile('carta_compromiso')) {
                $postulacion->carta_compromiso_path = $request->file('carta_compromiso')
                    ->store($documentosPath, 'public');
            }
            
            if ($request->hasFile('constancia_estudios')) {
                $postulacion->constancia_estudios_path = $request->file('constancia_estudios')
                    ->store($documentosPath, 'public');
            }
            
            if ($request->hasFile('dni_documento')) {
                $postulacion->dni_path = $request->file('dni_documento')
                    ->store($documentosPath, 'public');
            }
            
            if ($request->hasFile('foto_carnet')) {
                $postulacion->foto_path = $request->file('foto_carnet')
                    ->store($documentosPath, 'public');
            }
            
            $postulacion->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Postulación creada exitosamente para el estudiante',
                'postulacion' => true,
                'data' => [
                    'id' => $postulacion->id,
                    'codigo' => $postulacion->codigo_postulante,
                    'estudiante' => $estudiante->nombre . ' ' . $estudiante->apellido_paterno
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar la vista de reportes completos de postulaciones
     */
    public function reportesCompletos()
    {
        if (!Auth::user()->hasPermission('postulaciones.reports')) {
            abort(403, 'No tienes permisos para ver reportes de postulaciones');
        }

        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        $carreras = Carrera::where('estado', true)->orderBy('nombre')->get();
        $turnos = Turno::where('estado', true)->orderBy('nombre')->get();

        return view('postulaciones.reportes.completos', compact('ciclos', 'carreras', 'turnos'));
    }

    /**
     * Mostrar la vista de reportes resumen de postulaciones
     */
    public function reportesResumen()
    {
        if (!Auth::user()->hasPermission('postulaciones.reports')) {
            abort(403, 'No tienes permisos para ver reportes de postulaciones');
        }

        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        $carreras = Carrera::where('estado', true)->orderBy('nombre')->get();
        $turnos = Turno::where('estado', true)->orderBy('nombre')->get();
        $aulas = Aula::where('estado', true)->orderBy('nombre')->get();

        return view('postulaciones.reportes.resumen', compact('ciclos', 'carreras', 'turnos', 'aulas'));
    }

    public function exportarReporteCompleto(Request $request)
    {
        $ciclo_id = $request->input('ciclo_id');
        $carrera_id = $request->input('carrera_id');
        $turno_id = $request->input('turno_id');

        return Excel::download(new PostulacionesCompletoExport($ciclo_id, $carrera_id, $turno_id), 'postulaciones.xlsx');
    }

    public function exportarReporteResumen(Request $request)
    {
        $ciclo_id = $request->input('ciclo_id');
        $carrera_id = $request->input('carrera_id');
        $turno_id = $request->input('turno_id');
        $aula_id = $request->input('aula_id');

        return Excel::download(new PostulacionesResumenExport($ciclo_id, $carrera_id, $turno_id, $aula_id), 'postulaciones_resumen.xlsx');
    }

    public function getStats(Request $request)
    {
        if (!Auth::user()->hasPermission('postulaciones.view')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $query = Postulacion::query();

            // Filtro de ciclo: si no se especifica un ciclo, usar el ciclo activo por defecto.
            $cicloId = $request->input('ciclo_id');
            if (empty($cicloId)) {
                $cicloActivo = Ciclo::where('es_activo', true)->first();
                $cicloId = $cicloActivo ? $cicloActivo->id : -1;
            }

            $query->where('ciclo_id', $cicloId);

            // Otros filtros
            if ($request->filled('carrera_id')) {
                $query->where('carrera_id', $request->carrera_id);
            }

            $stats = $query->select('estado', DB::raw('count(*) as total'))
                        ->groupBy('estado')
                        ->pluck('total', 'estado')
                        ->all();

            $stats['pendiente'] = $stats['pendiente'] ?? 0;
            $stats['aprobado'] = $stats['aprobado'] ?? 0;
            $stats['rechazado'] = $stats['rechazado'] ?? 0;
            $stats['observado'] = $stats['observado'] ?? 0;

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error en PostulacionController@getStats: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar estadísticas.'], 500);
        }
    }

    /**
     * Descargar plantilla para importación masiva
     */
    public function descargarPlantilla()
    {
        return Excel::download(new PostulantesTemplateExport, 'plantilla_postulantes.xlsx');
    }

    /**
     * Importar postulantes desde Excel
     */
    public function importar(Request $request)
    {
        if (!Auth::user()->hasPermission('postulaciones.create')) {
             if ($request->expectsJson()) {
                 return response()->json(['error' => 'No tienes permisos para importar postulaciones'], 403);
             }
             return back()->with('error', 'No tienes permisos para importar postulaciones');
        }

        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $simulacro = $request->boolean('simulacro');
            $import = new PostulantesImport($simulacro);
            Excel::import($import, $request->file('archivo_excel'));
            
            $msg = ($simulacro ? "[SIMULACRO] " : "") . "Proceso finalizado. Total procesados: " . $import->resultados['procesados'] . 
                   ". " . ($simulacro ? "Se registrarían: " : "Nuevos registrados: ") . $import->resultados['creados'] . ".";

            $hayErrores = count($import->resultados['errores']) > 0;
            
            // Si es petición AJAX, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg . ($hayErrores ? ' Se encontraron algunos errores.' : ''),
                    'procesados' => $import->resultados['procesados'],
                    'creados' => $import->resultados['creados'],
                    'errores' => $import->resultados['errores'],
                    'simulacro' => $simulacro
                ]);
            }

            if ($hayErrores) {
                // Guardar errores en sesión para mostrarlos
                return back()->with('warning', $msg . ' Se encontraron algunos errores.')
                             ->with('import_errors', $import->resultados['errores']);
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error crítico en importación: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error crítico en importación: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar la vista del reporte de inhabilitados con filtros.
     */
    public function reporteInhabilitadosView()
    {
        if (!Auth::user()->hasPermission('postulaciones.reportes.inhabilitados')) {
            abort(403, 'No tienes permisos para ver reportes de inhabilitados');
        }

        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        $cicloActivo = Ciclo::where('es_activo', true)->first();

        return view('postulaciones.reportes.inhabilitados', compact('ciclos', 'cicloActivo'));
    }

    /**
     * Genera un reporte PDF detallado de estudiantes inhabilitados con filtros.
     */
    public function reporteInhabilitadosPdf(Request $request)
    {
        if (!Auth::user()->hasPermission('postulaciones.reportes.inhabilitados')) {
            abort(403, 'No tienes permisos para descargar este reporte');
        }
        
        $cicloId = $request->input('ciclo_id');
        $periodo = $request->input('periodo_examen', 'hoy');
        
        $ciclo = Ciclo::findOrFail($cicloId);
        
        $hoy = Carbon::now();
        $labelPeriodo = "Estado Actual (Snapshot)";
        $periodoTipo = $periodo; // 'hoy', '1', '2', '3'
        
        // Determinar periodo de examen
        $examenPeriodo = null;
        if ($periodoTipo == 'hoy') {
            $examenPeriodo = \App\Helpers\AsistenciaHelper::determinarExamenActivo($ciclo);
            if ($examenPeriodo) {
                $labelPeriodo = $examenPeriodo['nombre'] . " (Snapshot Actual)";
            }
        } elseif ($periodoTipo == '1') {
            $examenPeriodo = ['fecha_inicio' => $ciclo->fecha_inicio, 'fecha_examen' => $ciclo->fecha_primer_examen, 'nombre' => 'Primer Examen'];
            $labelPeriodo = "Primer Examen (Corte)";
        } elseif ($periodoTipo == '2') {
            $inicio2 = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo);
            $examenPeriodo = ['fecha_inicio' => $inicio2, 'fecha_examen' => $ciclo->fecha_segundo_examen, 'nombre' => 'Segundo Examen'];
            $labelPeriodo = "Segundo Examen (Periodo Independiente)";
        } elseif ($periodoTipo == '3') {
            $inicio3 = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen, $ciclo);
            $examenPeriodo = ['fecha_inicio' => $inicio3, 'fecha_examen' => $ciclo->fecha_tercer_examen, 'nombre' => 'Tercer Examen'];
            $labelPeriodo = "Tercer Examen (Periodo Independiente)";
        }

        $fechaFin = $examenPeriodo ? Carbon::parse($examenPeriodo['fecha_examen']) : $hoy;
        if ($hoy < $fechaFin) $fechaFin = $hoy;

        $inscripciones = Inscripcion::where('ciclo_id', $ciclo->id)
            ->where('estado_inscripcion', 'activo')
            ->with(['estudiante', 'carrera', 'aula', 'turno'])
            ->get();

        $inhabilitados = [];
        $totalEstudiantes = $inscripciones->count();
        $estudiantesRegulares = 0;
        $estudiantesAmonestados = 0;
        $estudiantesInhabilitadosCount = 0;

        foreach ($inscripciones as $inscripcion) {
            $estudiante = $inscripcion->estudiante;
            if (!$estudiante) continue;

            // Usar AsistenciaHelper que ya tiene la lógica del Excel unificada
            // Nota: obtenerEstadoHabilitacion ya maneja la lógica de periodos si le pasamos el ciclo
            $infoHabilitacion = \App\Helpers\AsistenciaHelper::obtenerEstadoHabilitacion($estudiante->numero_documento, $ciclo);

            // Sin embargo, el reporte PDF permite filtrar por periodo específico
            // Así que si no es 'hoy', debemos forzar el periodo
            if ($periodoTipo !== 'hoy') {
                $info = $this->calcularAsistenciaExamen(
                    $estudiante->numero_documento,
                    $fechaInicioCalculo,
                    $fechaFin,
                    $ciclo
                );
            } else {
                // Si es 'hoy', usamos el helper que es más robusto y coincide con el Excel
                $info = [
                    'estado' => $infoHabilitacion['estado'],
                    'dias_falta' => $infoHabilitacion['faltas'],
                    'dias_asistidos' => $infoHabilitacion['asistencias'],
                    'dias_habiles_transcurridos' => $infoHabilitacion['dias_habiles_totales'], // Usamos totales para el limite
                    'porcentaje_asistencia_actual' => $infoHabilitacion['dias_habiles_totales'] > 0 ? round(($infoHabilitacion['asistencias'] / $infoHabilitacion['dias_habiles_totales']) * 100, 2) : 100,
                    'limite_inhabilitacion' => $infoHabilitacion['limite_inhabilitacion']
                ];
            }

            if ($info['estado'] === 'inhabilitado') {
                $inhabilitados[] = [
                    'nombres' => $estudiante->nombre . ' ' . $estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno,
                    'dni' => $estudiante->numero_documento,
                    'carrera' => $inscripcion->carrera ? $inscripcion->carrera->nombre : 'N/A',
                    'aula' => $inscripcion->aula ? $inscripcion->aula->nombre : 'N/A',
                    'turno' => $inscripcion->turno ? $inscripcion->turno->nombre : 'N/A',
                    'faltas' => $info['dias_falta'],
                    'asistencias' => $info['dias_asistidos'],
                    'total_dias' => $info['dias_habiles_transcurridos'],
                    'porcentaje' => $info['porcentaje_asistencia_actual'],
                    'limite' => $info['limite_inhabilitacion']
                ];
                $estudiantesInhabilitadosCount++;
            } elseif ($info['estado'] === 'amonestado') {
                $estudiantesAmonestados++;
            } else {
                $estudiantesRegulares++;
            }
        }

        // Ordenar inhabilitados
        usort($inhabilitados, function($a, $b) {
            return strcmp($a['nombres'], $b['nombres']);
        });

        $data = [
            'ciclo' => $ciclo,
            'periodo_label' => $labelPeriodo,
            'inhabilitados' => $inhabilitados,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i A'),
            'total_general' => $totalEstudiantes,
            'total_inhabilitados' => $estudiantesInhabilitadosCount,
            'total_regulares' => $estudiantesRegulares,
            'total_amonestados' => $estudiantesAmonestados,
            'resumen' => [
                'porcentaje_inhabilitados' => $totalEstudiantes > 0 ? round(($estudiantesInhabilitadosCount / $totalEstudiantes) * 100, 2) : 0,
            ]
        ];

        $pdf = Pdf::loadView('reportes.inhabilitados-pdf', $data);
        return $pdf->download('reporte_inhabilitados_' . $ciclo->codigo . '_' . date('Ymd') . '.pdf');
    }

    private function calcularAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now()->startOfDay();
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->startOfDay();

        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy : $fechaExamenCarbon;

        if ($fechaInicioCarbon > $hoy) {
            return ['estado' => 'regular', 'dias_falta' => 0, 'dias_asistidos' => 0, 'dias_habiles_transcurridos' => 0, 'porcentaje_asistencia_actual' => 100, 'limite_inhabilitacion' => 0];
        }

        $diasHabilesTotales = $this->contarDiasHabiles($fechaInicio, $fechaExamen, $ciclo);
        $diasHabilesTranscurridos = $this->contarDiasHabiles($fechaInicio, $fechaFinCalculo, $ciclo);

        $registros = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [$fechaInicioCarbon->startOfDay(), $fechaFinCalculo->endOfDay()])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        $registrosAsistencia = 0;
        foreach ($registros as $fecha) {
            $carbonFecha = Carbon::parse($fecha);
            if ($ciclo->esDiaHabil($carbonFecha)) {
                $registrosAsistencia++;
            }
        }

        $diasFaltaActuales = $diasHabilesTranscurridos - $registrosAsistencia;
        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

        $estado = 'regular';
        if ($diasFaltaActuales >= $limiteInhabilitacion) {
            $estado = 'inhabilitado';
        } elseif ($diasFaltaActuales >= $limiteAmonestacion) {
            $estado = 'amonestado';
        }

        return [
            'dias_habiles_transcurridos' => $diasHabilesTranscurridos,
            'dias_asistidos' => $registrosAsistencia,
            'dias_falta' => $diasFaltaActuales,
            'porcentaje_asistencia_actual' => $diasHabilesTranscurridos > 0 ? round(($registrosAsistencia / $diasHabilesTranscurridos) * 100, 2) : 100,
            'limite_inhabilitacion' => $limiteInhabilitacion,
            'estado' => $estado
        ];
    }

    private function contarDiasHabiles($fechaInicio, $fechaFin, $ciclo)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $diasHabiles = 0;
        while ($inicio <= $fin) {
            if ($ciclo->esDiaHabil($inicio)) $diasHabiles++;
            $inicio->addDay();
        }
        return $diasHabiles;
    }

    private function getSiguienteDiaHabil($fecha, $ciclo)
    {
        $dia = Carbon::parse($fecha)->addDay();
        while (!$ciclo->esDiaHabil($dia)) {
            $dia->addDay();
        }
        return $dia;
    }
}