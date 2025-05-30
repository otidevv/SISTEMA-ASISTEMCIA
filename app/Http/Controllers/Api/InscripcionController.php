<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\User;
use App\Models\Aula;
use App\Models\RegistroAsistencia;
use App\Exports\AsistenciasPorCicloExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Exports\InscripcionesExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InscripcionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inscripcion::with(['estudiante', 'carrera', 'ciclo', 'turno', 'aula', 'registradoPor', 'actualizadoPor']);

        // Aplicar filtros
        if ($request->has('ciclo_id')) {
            $query->where('ciclo_id', $request->ciclo_id);
        }

        if ($request->has('carrera_id')) {
            $query->where('carrera_id', $request->carrera_id);
        }

        if ($request->has('turno_id')) {
            $query->where('turno_id', $request->turno_id);
        }

        if ($request->has('aula_id')) {
            $query->where('aula_id', $request->aula_id);
        }

        if ($request->has('estado')) {
            $query->where('estado_inscripcion', $request->estado);
        }

        $inscripciones = $query->orderBy('created_at', 'desc')->get();

        $data = $inscripciones->map(function ($inscripcion) {
            return [
                'id' => $inscripcion->id,
                'codigo_inscripcion' => $inscripcion->codigo_inscripcion,
                'estudiante' => [
                    'id' => $inscripcion->estudiante->id,
                    'nombre_completo' => $inscripcion->estudiante->nombre . ' ' .
                        $inscripcion->estudiante->apellido_paterno . ' ' .
                        $inscripcion->estudiante->apellido_materno,
                    'codigo' => $inscripcion->estudiante->numero_documento, // Usando numero_documento como código
                    'email' => $inscripcion->estudiante->email
                ],
                'carrera' => [
                    'id' => $inscripcion->carrera->id,
                    'nombre' => $inscripcion->carrera->nombre,
                    'codigo' => $inscripcion->carrera->codigo
                ],
                'ciclo' => [
                    'id' => $inscripcion->ciclo->id,
                    'nombre' => $inscripcion->ciclo->nombre,
                    'codigo' => $inscripcion->ciclo->codigo
                ],
                'turno' => [
                    'id' => $inscripcion->turno->id,
                    'nombre' => $inscripcion->turno->nombre
                ],
                'aula' => [
                    'id' => $inscripcion->aula->id,
                    'codigo' => $inscripcion->aula->codigo,
                    'nombre' => $inscripcion->aula->nombre,
                    'capacidad' => $inscripcion->aula->capacidad,
                    'disponible' => $inscripcion->aula->getCapacidadDisponible(
                        $inscripcion->ciclo_id,
                        $inscripcion->carrera_id,
                        $inscripcion->turno_id
                    )
                ],
                'fecha_inscripcion' => $inscripcion->fecha_inscripcion->format('Y-m-d'),
                'estado_inscripcion' => $inscripcion->estado_inscripcion,
                'fecha_retiro' => $inscripcion->fecha_retiro ? $inscripcion->fecha_retiro->format('Y-m-d') : null,
                'motivo_retiro' => $inscripcion->motivo_retiro,
                'observaciones' => $inscripcion->observaciones,
                'actions' => $this->getActionButtons($inscripcion)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    private function getActionButtons($inscripcion)
    {
        $buttons = '';

        // Ver detalles
        $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-info view-inscripcion" data-id="' . $inscripcion->id . '" title="Ver detalles"><i class="uil uil-eye"></i></a> ';

        // ⭐ AGREGAR ESTE BOTÓN PARA EL PDF ⭐
        // Descargar reporte de asistencia
        if (auth()->user()->hasPermission('inscripciones.view') || auth()->user()->hasPermission('attendance.reports')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-success download-asistencia" data-id="' . $inscripcion->id . '" data-estudiante-id="' . $inscripcion->estudiante_id . '" data-ciclo-id="' . $inscripcion->ciclo_id . '" title="Descargar reporte de asistencia"><i class="uil uil-file-download"></i></a> ';
        }

        if (auth()->user()->hasPermission('inscripciones.edit')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit-inscripcion" data-id="' . $inscripcion->id . '" title="Editar"><i class="uil uil-edit"></i></a> ';
        }

        if (auth()->user()->hasPermission('inscripciones.delete')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-inscripcion" data-id="' . $inscripcion->id . '" title="Eliminar"><i class="uil uil-trash-alt"></i></a>';
        }

        return $buttons;
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'estudiante_id' => 'required|exists:users,id',
            'carrera_id' => 'required|exists:carreras,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'turno_id' => 'required|exists:turnos,id',
            'aula_id' => 'required|exists:aulas,id',
            'fecha_inscripcion' => 'required|date',
            'estado_inscripcion' => 'required|in:activo,inactivo,retirado,egresado,trasladado',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el estudiante no tenga una inscripción activa
        $inscripcionActiva = Inscripcion::where('estudiante_id', $request->estudiante_id)
            ->where('estado_inscripcion', 'activo')
            ->first();

        if ($inscripcionActiva) {
            return response()->json([
                'success' => false,
                'message' => 'El estudiante ya tiene una inscripción activa'
            ], 422);
        }

        // Verificar capacidad del aula para el grupo específico
        $aula = Aula::find($request->aula_id);
        if ($aula->estaLlena($request->ciclo_id, $request->carrera_id, $request->turno_id)) {
            return response()->json([
                'success' => false,
                'message' => 'El aula seleccionada ha alcanzado su capacidad máxima para esta carrera y turno'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['registrado_por'] = auth()->id();

            $inscripcion = Inscripcion::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inscripción creada exitosamente',
                'data' => $inscripcion
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la inscripción'
            ], 500);
        }
    }

    public function show($id)
    {
        $inscripcion = Inscripcion::with([
            'estudiante',
            'carrera',
            'ciclo',
            'turno',
            'aula',
            'registradoPor',
            'actualizadoPor'
        ])->find($id);

        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'Inscripción no encontrada'
            ], 404);
        }

        $data = [
            'id' => $inscripcion->id,
            'codigo_inscripcion' => $inscripcion->codigo_inscripcion,
            'estudiante' => [
                'id' => $inscripcion->estudiante->id,
                'nombre_completo' => $inscripcion->estudiante->nombre . ' ' .
                    $inscripcion->estudiante->apellido_paterno . ' ' .
                    $inscripcion->estudiante->apellido_materno,
                'codigo' => $inscripcion->estudiante->numero_documento, // Usando numero_documento como código
                'email' => $inscripcion->estudiante->email
            ],
            'carrera' => [
                'id' => $inscripcion->carrera->id,
                'nombre' => $inscripcion->carrera->nombre,
                'codigo' => $inscripcion->carrera->codigo
            ],
            'ciclo' => [
                'id' => $inscripcion->ciclo->id,
                'nombre' => $inscripcion->ciclo->nombre,
                'codigo' => $inscripcion->ciclo->codigo
            ],
            'turno' => [
                'id' => $inscripcion->turno->id,
                'nombre' => $inscripcion->turno->nombre
            ],
            'aula' => [
                'id' => $inscripcion->aula->id,
                'codigo' => $inscripcion->aula->codigo,
                'nombre' => $inscripcion->aula->nombre,
                'capacidad' => $inscripcion->aula->capacidad,
                'tipo' => $inscripcion->aula->tipo,
                'edificio' => $inscripcion->aula->edificio,
                'piso' => $inscripcion->aula->piso
            ],
            'carrera_id' => $inscripcion->carrera_id,
            'ciclo_id' => $inscripcion->ciclo_id,
            'turno_id' => $inscripcion->turno_id,
            'aula_id' => $inscripcion->aula_id,
            'fecha_inscripcion' => $inscripcion->fecha_inscripcion->format('Y-m-d'),
            'estado_inscripcion' => $inscripcion->estado_inscripcion,
            'fecha_retiro' => $inscripcion->fecha_retiro ? $inscripcion->fecha_retiro->format('Y-m-d') : null,
            'motivo_retiro' => $inscripcion->motivo_retiro,
            'observaciones' => $inscripcion->observaciones,
            'registrado_por' => $inscripcion->registradoPor ? [
                'id' => $inscripcion->registradoPor->id,
                'nombre_completo' => $inscripcion->registradoPor->nombre . ' ' . $inscripcion->registradoPor->apellido_paterno
            ] : null,
            'actualizado_por' => $inscripcion->actualizadoPor ? [
                'id' => $inscripcion->actualizadoPor->id,
                'nombre_completo' => $inscripcion->actualizadoPor->nombre . ' ' . $inscripcion->actualizadoPor->apellido_paterno
            ] : null,
            'created_at' => $inscripcion->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $inscripcion->updated_at->format('Y-m-d H:i:s')
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $inscripcion = Inscripcion::find($id);

        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'Inscripción no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'carrera_id' => 'required|exists:carreras,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'turno_id' => 'required|exists:turnos,id',
            'aula_id' => 'required|exists:aulas,id',
            'fecha_inscripcion' => 'required|date',
            'estado_inscripcion' => 'required|in:activo,inactivo,retirado,egresado,trasladado',
            'fecha_retiro' => 'nullable|date|required_if:estado_inscripcion,retirado,trasladado',
            'motivo_retiro' => 'nullable|string|required_if:estado_inscripcion,retirado,trasladado',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Si se está cambiando el aula, verificar capacidad
        if ($request->aula_id != $inscripcion->aula_id) {
            $nuevaAula = Aula::find($request->aula_id);
            if ($nuevaAula->estaLlena($request->ciclo_id, $request->carrera_id, $request->turno_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El aula seleccionada ha alcanzado su capacidad máxima para esta carrera y turno'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['actualizado_por'] = auth()->id();

            // Si el estado cambió a retirado o trasladado, establecer fecha de retiro
            if (in_array($request->estado_inscripcion, ['retirado', 'trasladado']) && !$request->fecha_retiro) {
                $data['fecha_retiro'] = now();
            }

            // Si el estado no es retirado ni trasladado, limpiar campos de retiro
            if (!in_array($request->estado_inscripcion, ['retirado', 'trasladado'])) {
                $data['fecha_retiro'] = null;
                $data['motivo_retiro'] = null;
            }

            $inscripcion->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inscripción actualizada exitosamente',
                'data' => $inscripcion
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la inscripción'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $inscripcion = Inscripcion::find($id);

        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'Inscripción no encontrada'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $inscripcion->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inscripción eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la inscripción'
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $inscripcion = Inscripcion::find($id);

        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'Inscripción no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:activo,inactivo,retirado,egresado,trasladado',
            'motivo' => 'nullable|string|required_if:estado,retirado,trasladado'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $inscripcion->estado_inscripcion = $request->estado;
            $inscripcion->actualizado_por = auth()->id();

            if (in_array($request->estado, ['retirado', 'trasladado'])) {
                $inscripcion->fecha_retiro = now();
                $inscripcion->motivo_retiro = $request->motivo;
            }

            $inscripcion->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado de inscripción actualizado exitosamente',
                'data' => $inscripcion
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado'
            ], 500);
        }
    }

    public function porEstudiante($estudianteId)
    {
        $inscripciones = Inscripcion::with(['carrera', 'ciclo', 'turno', 'aula'])
            ->where('estudiante_id', $estudianteId)
            ->orderBy('fecha_inscripcion', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $inscripciones
        ]);
    }

    public function porCiclo($cicloId)
    {
        $inscripciones = Inscripcion::with(['estudiante', 'carrera', 'turno', 'aula'])
            ->where('ciclo_id', $cicloId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $inscripciones
        ]);
    }

    public function porCarrera($carreraId)
    {
        $inscripciones = Inscripcion::with(['estudiante', 'ciclo', 'turno', 'aula'])
            ->where('carrera_id', $carreraId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $inscripciones
        ]);
    }

    public function estadisticas()
    {
        $estadisticas = [
            'total_inscripciones' => Inscripcion::count(),
            'inscripciones_activas' => Inscripcion::where('estado_inscripcion', 'activo')->count(),
            'inscripciones_inactivas' => Inscripcion::where('estado_inscripcion', 'inactivo')->count(),
            'inscripciones_retiradas' => Inscripcion::where('estado_inscripcion', 'retirado')->count(),
            'egresados' => Inscripcion::where('estado_inscripcion', 'egresado')->count(),
            'trasladados' => Inscripcion::where('estado_inscripcion', 'trasladado')->count(),
            'por_carrera' => Inscripcion::with('carrera')
                ->select('carrera_id', DB::raw('count(*) as total'))
                ->groupBy('carrera_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'carrera' => $item->carrera->nombre,
                        'total' => $item->total
                    ];
                }),
            'por_ciclo' => Inscripcion::with('ciclo')
                ->select('ciclo_id', DB::raw('count(*) as total'))
                ->groupBy('ciclo_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'ciclo' => $item->ciclo->nombre,
                        'total' => $item->total
                    ];
                }),
            'por_turno' => Inscripcion::with('turno')
                ->select('turno_id', DB::raw('count(*) as total'))
                ->groupBy('turno_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'turno' => $item->turno->nombre,
                        'total' => $item->total
                    ];
                }),
            'por_aula' => DB::table('inscripciones as i')
                ->join('aulas as a', 'i.aula_id', '=', 'a.id')
                ->join('ciclos as c', 'i.ciclo_id', '=', 'c.id')
                ->join('carreras as ca', 'i.carrera_id', '=', 'ca.id')
                ->join('turnos as t', 'i.turno_id', '=', 't.id')
                ->where('i.estado_inscripcion', 'activo')
                ->where('c.es_activo', true)
                ->select(
                    'a.id',
                    'a.codigo',
                    'a.nombre',
                    'a.capacidad',
                    'ca.nombre as carrera',
                    't.nombre as turno',
                    DB::raw('COUNT(i.id) as total_inscripciones'),
                    DB::raw('ROUND((COUNT(i.id) / a.capacidad) * 100, 2) as porcentaje_ocupacion')
                )
                ->groupBy('a.id', 'a.codigo', 'a.nombre', 'a.capacidad', 'ca.nombre', 't.nombre')
                ->orderBy('porcentaje_ocupacion', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'aula' => $item->codigo . ' - ' . $item->nombre,
                        'carrera' => $item->carrera,
                        'turno' => $item->turno,
                        'total' => $item->total_inscripciones,
                        'capacidad' => $item->capacidad,
                        'porcentaje_ocupacion' => $item->porcentaje_ocupacion
                    ];
                })
        ];

        return response()->json([
            'success' => true,
            'data' => $estadisticas
        ]);
    }

    public function exportarExcel(Request $request)
    {
        $query = Inscripcion::with(['estudiante', 'carrera', 'ciclo', 'turno', 'aula']);

        // Aplicar filtros
        if ($request->has('ciclo_id')) {
            $query->where('ciclo_id', $request->ciclo_id);
        }

        if ($request->has('carrera_id')) {
            $query->where('carrera_id', $request->carrera_id);
        }

        if ($request->has('turno_id')) {
            $query->where('turno_id', $request->turno_id);
        }

        if ($request->has('aula_id')) {
            $query->where('aula_id', $request->aula_id);
        }

        if ($request->has('estado')) {
            $query->where('estado_inscripcion', $request->estado);
        }

        $inscripciones = $query->get();

        return Excel::download(new InscripcionesExport($inscripciones), 'inscripciones_' . date('Y-m-d') . '.xlsx');
    }

    public function estudiantesSinInscripcion()
    {
        // Obtener IDs de estudiantes con inscripciones activas
        $estudiantesConInscripcion = Inscripcion::where('estado_inscripcion', 'activo')
            ->pluck('estudiante_id');

        // Obtener estudiantes sin inscripción activa
        $estudiantes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'estudiante'); // Cambiado de 'name' a 'nombre' y 'Estudiante' a 'estudiante'
        })
            ->whereNotIn('id', $estudiantesConInscripcion)
            ->where('estado', 1) // Cambiado a 1 porque estado es boolean
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get()
            ->map(function ($estudiante) {
                return [
                    'id' => $estudiante->id,
                    'nombre_completo' => $estudiante->nombre . ' ' .
                        $estudiante->apellido_paterno . ' ' .
                        $estudiante->apellido_materno,
                    'codigo' => $estudiante->numero_documento, // Usando numero_documento como código
                    'email' => $estudiante->email
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $estudiantes
        ]);
    }

    public function aulasDisponibles(Request $request)
    {
        $query = Aula::where('estado', 1);

        // Si se especifica un tipo
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Obtener parámetros para filtrar capacidad
        $cicloId = $request->get('ciclo_id');
        $carreraId = $request->get('carrera_id');
        $turnoId = $request->get('turno_id');

        $aulas = $query->get()->map(function ($aula) use ($cicloId, $carreraId, $turnoId) {
            $capacidadDisponible = $aula->getCapacidadDisponible($cicloId, $carreraId, $turnoId);
            $porcentajeOcupacion = $aula->getPorcentajeOcupacion($cicloId, $carreraId, $turnoId);

            return [
                'id' => $aula->id,
                'codigo' => $aula->codigo,
                'nombre' => $aula->nombre,
                'tipo' => $aula->tipo,
                'capacidad' => $aula->capacidad,
                'disponible' => $capacidadDisponible,
                'porcentaje_ocupacion' => $porcentajeOcupacion,
                'edificio' => $aula->edificio,
                'piso' => $aula->piso,
                'tiene_proyector' => $aula->tiene_proyector,
                'tiene_aire_acondicionado' => $aula->tiene_aire_acondicionado,
                'inscripciones_grupo' => $cicloId && $carreraId && $turnoId ?
                    $aula->getInscripcionesActivasPorGrupo($cicloId, $carreraId, $turnoId) : null
            ];
        });

        // Ordenar por disponibilidad (las que tienen más espacio primero)
        $aulas = $aulas->sortByDesc('disponible')->values();

        return response()->json([
            'success' => true,
            'data' => $aulas
        ]);
    }
    public function reporteAsistenciaPdf($id)
    {
        // Obtener la inscripción con todas las relaciones necesarias
        $inscripcion = Inscripcion::with(['estudiante', 'ciclo', 'carrera', 'turno', 'aula'])
            ->find($id);

        if (!$inscripcion) {
            abort(404, 'Inscripción no encontrada');
        }

        $ciclo = $inscripcion->ciclo;
        $estudiante = $inscripcion->estudiante;

        // Obtener el primer registro de asistencia del estudiante
        $primerRegistro = RegistroAsistencia::where('nro_documento', $estudiante->numero_documento)
            ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
            ->where('fecha_registro', '<=', $ciclo->fecha_fin)
            ->orderBy('fecha_registro')
            ->first();

        // Array para almacenar toda la información
        $data = [
            'inscripcion' => $inscripcion,
            'estudiante' => $estudiante,
            'ciclo' => $ciclo,
            'carrera' => $inscripcion->carrera,
            'turno' => $inscripcion->turno,
            'aula' => $inscripcion->aula,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'primer_registro' => $primerRegistro
        ];

        // Información de asistencia
        $infoAsistencia = [];

        if ($primerRegistro) {
            // Primer Examen
            $infoAsistencia['primer_examen'] = $this->calcularAsistenciaExamenPdf(
                $estudiante->numero_documento,
                $primerRegistro->fecha_registro,
                $ciclo->fecha_primer_examen,
                $ciclo
            );

            // Segundo Examen
            if ($ciclo->fecha_segundo_examen) {
                $inicioSegundo = $this->getSiguienteDiaHabilPdf($ciclo->fecha_primer_examen);
                $infoAsistencia['segundo_examen'] = $this->calcularAsistenciaExamenPdf(
                    $estudiante->numero_documento,
                    $inicioSegundo,
                    $ciclo->fecha_segundo_examen,
                    $ciclo
                );
            }

            // Tercer Examen
            if ($ciclo->fecha_tercer_examen && $ciclo->fecha_segundo_examen) {
                $inicioTercero = $this->getSiguienteDiaHabilPdf($ciclo->fecha_segundo_examen);
                $infoAsistencia['tercer_examen'] = $this->calcularAsistenciaExamenPdf(
                    $estudiante->numero_documento,
                    $inicioTercero,
                    $ciclo->fecha_tercer_examen,
                    $ciclo
                );
            }

            // Asistencia total del ciclo
            $infoAsistencia['total_ciclo'] = $this->calcularAsistenciaExamenPdf(
                $estudiante->numero_documento,
                $primerRegistro->fecha_registro,
                min(Carbon::now(), Carbon::parse($ciclo->fecha_fin)),
                $ciclo
            );

            // Obtener detalle de asistencias por mes
            $detalleAsistencias = $this->obtenerDetalleAsistenciasPorMes(
                $estudiante->numero_documento,
                $primerRegistro->fecha_registro,
                min(Carbon::now(), Carbon::parse($ciclo->fecha_fin))
            );

            $data['detalle_asistencias'] = $detalleAsistencias;
        }

        $data['info_asistencia'] = $infoAsistencia;

        // Generar el PDF
        $pdf = PDF::loadView('reportes.asistencia-estudiante', $data);
        $pdf->setPaper('a4', 'portrait');

        // Nombre del archivo
        $filename = 'reporte_asistencia_' . $estudiante->numero_documento . '_' . $ciclo->codigo . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Calcular asistencia para el reporte PDF
     */
    /**
     * Calcular asistencia para el reporte PDF
     * REEMPLAZA TODO EL MÉTODO calcularAsistenciaExamenPdf CON ESTE CÓDIGO
     */
    private function calcularAsistenciaExamenPdf($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now();
        // IMPORTANTE: Usar startOfDay para la fecha de inicio
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->endOfDay();

        // Si el examen aún no ha llegado, calcular hasta el final del día de hoy
        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy->endOfDay() : $fechaExamenCarbon;

        // Si la fecha de inicio es futura, no calcular aún
        if ($fechaInicioCarbon > $hoy) {
            return [
                'dias_habiles' => 0,
                'dias_asistidos' => 0,
                'dias_falta' => 0,
                'porcentaje_asistencia' => 0,
                'porcentaje_falta' => 0,
                'condicion' => 'Pendiente',
                'puede_rendir' => '-'
            ];
        }

        // Calcular días hábiles - IMPORTANTE: usar endOfDay para incluir el día completo
        $diasHabilesTotales = $this->contarDiasHabilesPdf(
            $fechaInicioCarbon->format('Y-m-d'),
            $fechaExamenCarbon->format('Y-m-d')
        );

        $diasHabilesTranscurridos = $this->contarDiasHabilesPdf(
            $fechaInicioCarbon->format('Y-m-d'),
            $fechaFinCalculo->format('Y-m-d')
        );

        // Obtener días con asistencia - usar DATE para agrupar por día
        $registros = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                $fechaInicioCarbon,
                $fechaFinCalculo
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        // Contar asistencias en días hábiles
        $diasConAsistencia = 0;
        $fechasContadas = [];

        foreach ($registros as $fecha) {
            $fechaCarbon = Carbon::parse($fecha);

            // Solo contar días hábiles (lunes a viernes)
            if ($fechaCarbon->isWeekday()) {
                $diasConAsistencia++;
                $fechasContadas[] = $fecha;
            }
        }

        // IMPORTANTE: Asegurarse de que los días con asistencia no excedan los días transcurridos
        if ($diasConAsistencia > $diasHabilesTranscurridos) {
            // Log para debugging
            \Log::warning("Asistencias exceden días transcurridos en PDF", [
                'documento' => $numeroDocumento,
                'dias_asistidos' => $diasConAsistencia,
                'dias_transcurridos' => $diasHabilesTranscurridos,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin_calculo' => $fechaFinCalculo->format('Y-m-d'),
                'fechas_contadas' => $fechasContadas
            ]);

            // Ajustar al máximo posible
            $diasConAsistencia = $diasHabilesTranscurridos;
        }

        // Calcular faltas solo de los días transcurridos
        $diasFalta = max(0, $diasHabilesTranscurridos - $diasConAsistencia);

        // Calcular porcentajes sobre el total de días del período
        $porcentajeAsistencia = $diasHabilesTotales > 0 ?
            round(($diasConAsistencia / $diasHabilesTotales) * 100, 2) : 0;

        $porcentajeFalta = $diasHabilesTotales > 0 ?
            round(($diasFalta / $diasHabilesTotales) * 100, 2) : 0;

        // Asegurar que los porcentajes no excedan 100% ni sean negativos
        $porcentajeAsistencia = min(100, max(0, $porcentajeAsistencia));
        $porcentajeFalta = min(100, max(0, $porcentajeFalta));

        // Calcular límites basados en el total de días del período
        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

        // Determinar condición basada en las faltas actuales
        $condicion = 'Regular';
        $puedeRendir = 'SÍ';

        if ($diasFalta >= $limiteInhabilitacion) {
            $condicion = 'Inhabilitado';
            $puedeRendir = 'NO';
        } elseif ($diasFalta >= $limiteAmonestacion) {
            $condicion = 'Amonestado';
        }

        // Preparar resultado
        $resultado = [
            'dias_habiles' => $diasHabilesTotales,
            'dias_asistidos' => $diasConAsistencia,
            'dias_falta' => $diasFalta,
            'porcentaje_asistencia' => $porcentajeAsistencia,
            'porcentaje_falta' => $porcentajeFalta,
            'condicion' => $condicion,
            'puede_rendir' => $puedeRendir
        ];

        // Agregar información de días transcurridos si el período aún no termina
        if ($diasHabilesTranscurridos < $diasHabilesTotales) {
            $resultado['dias_habiles_transcurridos'] = $diasHabilesTranscurridos;

            // Calcular porcentajes actuales (sobre días transcurridos)
            $porcentajeAsistenciaActual = $diasHabilesTranscurridos > 0 ?
                round(($diasConAsistencia / $diasHabilesTranscurridos) * 100, 2) : 0;
            $porcentajeFaltaActual = $diasHabilesTranscurridos > 0 ?
                round(($diasFalta / $diasHabilesTranscurridos) * 100, 2) : 0;

            $resultado['porcentaje_asistencia_actual'] = $porcentajeAsistenciaActual;
            $resultado['porcentaje_falta_actual'] = $porcentajeFaltaActual;
            $resultado['es_proyeccion'] = true;
        }

        return $resultado;
    }

    /**
     * Contar días hábiles para el PDF
     */
    private function contarDiasHabilesPdf($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $diasHabiles = 0;

        // IMPORTANTE: usar <= para incluir ambos días (inicio y fin)
        while ($inicio <= $fin) {
            if ($inicio->isWeekday()) {
                $diasHabiles++;
            }
            $inicio->addDay();
        }

        return $diasHabiles;
    }

    /**
     * Obtener siguiente día hábil para el PDF
     */
    private function getSiguienteDiaHabilPdf($fecha)
    {
        $dia = Carbon::parse($fecha)->addDay();

        while (!$dia->isWeekday()) {
            $dia->addDay();
        }

        return $dia;
    }

    /**
     * Obtener detalle de asistencias por mes
     */
    private function obtenerDetalleAsistenciasPorMes($numeroDocumento, $fechaInicio, $fechaFin)
    {
        $registros = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->orderBy('fecha_registro')
            ->get();

        $detallesPorMes = [];

        foreach ($registros as $registro) {
            $mes = Carbon::parse($registro->fecha_registro)->format('Y-m');
            $nombreMes = Carbon::parse($registro->fecha_registro)->locale('es')->monthName;
            $anio = Carbon::parse($registro->fecha_registro)->year;

            if (!isset($detallesPorMes[$mes])) {
                $detallesPorMes[$mes] = [
                    'mes' => ucfirst($nombreMes),
                    'anio' => $anio,
                    'dias_asistidos' => 0,
                    'registros' => []
                ];
            }

            $fecha = Carbon::parse($registro->fecha_registro)->format('Y-m-d');
            if (!isset($detallesPorMes[$mes]['registros'][$fecha])) {
                $detallesPorMes[$mes]['registros'][$fecha] = [
                    'fecha' => Carbon::parse($registro->fecha_registro)->format('d/m/Y'),
                    'dia_semana' => Carbon::parse($registro->fecha_registro)->locale('es')->dayName,
                    'hora_entrada' => null,
                    'hora_salida' => null,
                    'registros_del_dia' => [] // Para almacenar todos los registros del día
                ];

                if (Carbon::parse($registro->fecha_registro)->isWeekday()) {
                    $detallesPorMes[$mes]['dias_asistidos']++;
                }
            }

            // Guardar todos los registros del día
            $hora = Carbon::parse($registro->fecha_registro)->hour;
            $horaFormateada = Carbon::parse($registro->fecha_registro)->format('H:i');
            $detallesPorMes[$mes]['registros'][$fecha]['registros_del_dia'][] = [
                'hora' => $hora,
                'hora_formateada' => $horaFormateada
            ];
        }

        // Procesar los registros de cada día para determinar entrada y salida
        foreach ($detallesPorMes as $mes => $datosMes) {
            foreach ($datosMes['registros'] as $fecha => $datosdia) {
                $registrosDelDia = $datosdia['registros_del_dia'];

                if (count($registrosDelDia) > 0) {
                    // Ordenar registros por hora
                    usort($registrosDelDia, function ($a, $b) {
                        return $a['hora'] - $b['hora'];
                    });

                    // Verificar si todos los registros son después de las 18:00
                    $todosRegistrosTarde = true;
                    foreach ($registrosDelDia as $reg) {
                        if ($reg['hora'] < 18) {
                            $todosRegistrosTarde = false;
                            break;
                        }
                    }

                    // Si todos los registros son tarde (después de 18:00), solo hay salida
                    if ($todosRegistrosTarde) {
                        // Tomar el último registro como salida
                        $ultimoIndice = count($registrosDelDia) - 1;
                        $detallesPorMes[$mes]['registros'][$fecha]['hora_entrada'] = 'Sin registro';
                        $detallesPorMes[$mes]['registros'][$fecha]['hora_salida'] = $registrosDelDia[$ultimoIndice]['hora_formateada'];
                    } else {
                        // Lógica normal para días con registros variados
                        $entradaEncontrada = false;
                        $salidaEncontrada = false;

                        // Buscar entrada (primer registro antes de las 17:00)
                        foreach ($registrosDelDia as $reg) {
                            if ($reg['hora'] < 17 && !$entradaEncontrada) {
                                $detallesPorMes[$mes]['registros'][$fecha]['hora_entrada'] = $reg['hora_formateada'];
                                $entradaEncontrada = true;
                                break;
                            }
                        }

                        // Buscar salida (último registro después de las 18:00)
                        for ($i = count($registrosDelDia) - 1; $i >= 0; $i--) {
                            if ($registrosDelDia[$i]['hora'] >= 18) {
                                $detallesPorMes[$mes]['registros'][$fecha]['hora_salida'] = $registrosDelDia[$i]['hora_formateada'];
                                $salidaEncontrada = true;
                                break;
                            }
                        }

                        // Si no hay entrada clara pero hay registros tempranos
                        if (!$entradaEncontrada && count($registrosDelDia) > 0) {
                            // Si el primer registro es entre 17:00 y 18:00, podría ser entrada tardía
                            if ($registrosDelDia[0]['hora'] >= 17 && $registrosDelDia[0]['hora'] < 18) {
                                $detallesPorMes[$mes]['registros'][$fecha]['hora_entrada'] = $registrosDelDia[0]['hora_formateada'] . ' (tardía)';
                            } else if ($registrosDelDia[0]['hora'] < 17) {
                                $detallesPorMes[$mes]['registros'][$fecha]['hora_entrada'] = $registrosDelDia[0]['hora_formateada'];
                            } else {
                                $detallesPorMes[$mes]['registros'][$fecha]['hora_entrada'] = 'Sin registro';
                            }
                        }

                        // Si no hay salida clara pero hay múltiples registros
                        if (!$salidaEncontrada && count($registrosDelDia) > 1) {
                            $ultimoIndice = count($registrosDelDia) - 1;
                            // Si el último registro es después de las 17:00, considerarlo salida
                            if ($registrosDelDia[$ultimoIndice]['hora'] >= 17) {
                                $detallesPorMes[$mes]['registros'][$fecha]['hora_salida'] = $registrosDelDia[$ultimoIndice]['hora_formateada'];
                            }
                        }
                    }

                    // Manejar casos donde no hay entrada o salida
                    if (
                        !isset($detallesPorMes[$mes]['registros'][$fecha]['hora_entrada']) ||
                        $detallesPorMes[$mes]['registros'][$fecha]['hora_entrada'] === null
                    ) {
                        $detallesPorMes[$mes]['registros'][$fecha]['hora_entrada'] = 'Sin registro';
                    }

                    if (
                        !isset($detallesPorMes[$mes]['registros'][$fecha]['hora_salida']) ||
                        $detallesPorMes[$mes]['registros'][$fecha]['hora_salida'] === null
                    ) {
                        $detallesPorMes[$mes]['registros'][$fecha]['hora_salida'] = '-';
                    }
                }

                // Limpiar el array temporal
                unset($detallesPorMes[$mes]['registros'][$fecha]['registros_del_dia']);
            }
        }

        return $detallesPorMes;
    }

    public function exportarAsistenciasPorCiclo(Request $request): BinaryFileResponse
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id'
        ]);

        $cicloId = $request->ciclo_id;

        // Verificar si hay inscripciones activas
        $tieneInscripciones = Inscripcion::where('ciclo_id', $cicloId)
            ->where('estado_inscripcion', 'activo')
            ->exists();

        if (!$tieneInscripciones) {
            // Si se usa desde un frontend con JS, este bloque se podría adaptar para respuesta 200 con tipo application/json
            abort(404, 'No existen inscripciones activas para este ciclo.');
        }

        $filename = 'asistencias_ciclo_' . $cicloId . '.xlsx';
        return Excel::download(new AsistenciasPorCicloExport($cicloId), $filename);
    }
}
