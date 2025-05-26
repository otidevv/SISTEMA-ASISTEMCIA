<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\User;
use App\Models\Aula;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InscripcionesExport;

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
}
