<?php

namespace App\Http\Controllers;

use App\Exports\BoletinEntregaExport;
use App\Models\Ciclo;
use App\Models\Aula;
use App\Models\Inscripcion;
use App\Models\Curso;
use App\Models\BoletinEntrega;
use App\Models\HorarioDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class BoletinController extends Controller
{

    public function index()
    {
        $ciclos = Ciclo::all();
        $aulas = Aula::all();
        return view('boletines.index', compact('ciclos', 'aulas'));
    }

    public function getData(Request $request)
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
            'tipo_examen' => 'required|string|max:255',
        ]);

        $inscripciones = Inscripcion::with('estudiante')
            ->where('ciclo_id', $request->ciclo_id)
            ->where('aula_id', $request->aula_id)
            ->get();

        $cursoIds = HorarioDocente::where('ciclo_id', $request->ciclo_id)->distinct()->pluck('curso_id');
        $cursos = Curso::whereIn('id', $cursoIds)->get();

        $data = [];
        foreach ($inscripciones as $inscripcion) {
            $entregas = BoletinEntrega::where('inscripcion_id', $inscripcion->id)
                ->where('tipo_examen', $request->tipo_examen)
                ->get()
                ->keyBy('curso_id');

            $rowData = [
                'student' => $inscripcion->estudiante->apellido_paterno . ' ' . $inscripcion->estudiante->apellido_materno . ', ' . $inscripcion->estudiante->nombre,
                'inscripcion_id' => $inscripcion->id,
                'courses' => []
            ];

            foreach ($cursos as $curso) {
                $entregado = $entregas->has($curso->id) ? $entregas[$curso->id]->entregado : false;
                $fecha_entrega = $entregas->has($curso->id) ? $entregas[$curso->id]->fecha_entrega : null;

                $rowData['courses'][] = [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'entregado' => $entregado,
                    'fecha_entrega' => $fecha_entrega,
                ];
            }
            $data[] = $rowData;
        }

        return response()->json(['data' => $data, 'cursos' => $cursos]);
    }

    public function getAsistentes(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
        ]);

        try {
            $fecha = $request->input('fecha');
            $ciclo_id = $request->input('ciclo_id');
            $aula_id = $request->input('aula_id');

            $asistentes = Inscripcion::where('ciclo_id', $ciclo_id)
                ->where('aula_id', $aula_id)
                ->whereHas('estudiante.asistencias', function ($query) use ($fecha) {
                    $query->whereDate('fecha_registro', $fecha);
                })
                ->pluck('id');

            return response()->json(['success' => true, 'asistentes' => $asistentes]);

        } catch (\Exception $e) {
            Log::error('Error al obtener asistentes para boletines: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al obtener los asistentes.'], 500);
        }
    }

    public function marcarEntrega(Request $request)
    {
        // Validar que tipo_examen siempre esté presente
        $request->validate(['tipo_examen' => 'required|string|max:255']);
        $tipo_examen = $request->input('tipo_examen');

        // Lógica para manejar entregas en lote
        if ($request->has('entregas') && is_array($request->input('entregas'))) {

            $rules = [
                'entregas' => 'present|array',
                'entregas.*.inscripcion_id' => 'required|exists:inscripciones,id',
                'entregas.*.curso_id' => 'required|exists:cursos,id',
                'entregas.*.entregado' => 'required|boolean',
            ];
    
            $validator = validator($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Datos de entrada no válidos.', 'errors' => $validator->errors()], 422);
            }

            $entregas = $request->input('entregas');

            DB::beginTransaction();
            try {
                foreach ($entregas as $entrega) {
                    BoletinEntrega::updateOrCreate(
                        [
                            'inscripcion_id' => $entrega['inscripcion_id'],
                            'curso_id' => $entrega['curso_id'],
                            'tipo_examen' => $tipo_examen,
                        ],
                        [
                            'entregado' => $entrega['entregado'],
                            'fecha_entrega' => $entrega['entregado'] ? now() : null,
                        ]
                    );
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Cambios guardados correctamente.']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error al marcar entregas de boletín en lote: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado al guardar los cambios.'], 500);
            }
        } else {
            // Lógica para una sola entrega
            try {
                $validatedData = $request->validate([
                    'inscripcion_id' => 'required|exists:inscripciones,id',
                    'curso_id' => 'required|exists:cursos,id',
                    'entregado' => 'required|boolean',
                ]);

                BoletinEntrega::updateOrCreate(
                    [
                        'inscripcion_id' => $validatedData['inscripcion_id'],
                        'curso_id' => $validatedData['curso_id'],
                        'tipo_examen' => $tipo_examen,
                    ],
                    [
                        'entregado' => $validatedData['entregado'],
                        'fecha_entrega' => $validatedData['entregado'] ? now() : null,
                    ]
                );

                return response()->json(['success' => true]);
            } catch (ValidationException $e) {
                return response()->json(['success' => false, 'message' => 'Datos de entrada no válidos.', 'errors' => $e->errors()], 422);
            } catch (\Exception $e) {
                Log::error('Error al marcar entrega de boletín: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado en el servidor.'], 500);
            }
        }
    }

    public function exportar(Request $request)
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
            'tipo_examen' => 'required|string|max:255',
        ]);

        // Re-use the logic from getData to fetch the data
        $inscripciones = Inscripcion::with('estudiante')
            ->where('ciclo_id', $request->ciclo_id)
            ->where('aula_id', $request->aula_id)
            ->get();

        $cursoIds = HorarioDocente::where('ciclo_id', $request->ciclo_id)->distinct()->pluck('curso_id');
        $cursos = Curso::whereIn('id', $cursoIds)->get();

        $data = [];
        foreach ($inscripciones as $inscripcion) {
            $entregas = BoletinEntrega::where('inscripcion_id', $inscripcion->id)
                ->where('tipo_examen', $request->tipo_examen)
                ->get()
                ->keyBy('curso_id');

            $rowData = [
                'student' => $inscripcion->estudiante->apellido_paterno . ' ' . $inscripcion->estudiante->apellido_materno . ', ' . $inscripcion->estudiante->nombre,
                'inscripcion_id' => $inscripcion->id,
                'courses' => []
            ];

            foreach ($cursos as $curso) {
                $entregado = $entregas->has($curso->id) ? $entregas[$curso->id]->entregado : false;
                $fecha_entrega = $entregas->has($curso->id) ? $entregas[$curso->id]->fecha_entrega : null;

                $rowData['courses'][] = [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'entregado' => $entregado,
                    'fecha_entrega' => $fecha_entrega,
                ];
            }
            $data[] = $rowData;
        }
        
        $ciclo = Ciclo::find($request->ciclo_id);
        $aula = Aula::find($request->aula_id);
        $fileName = 'Reporte_Boletines_' . str_replace(' ', '_', $ciclo->nombre) . '_' . str_replace(' ', '_', $aula->nombre) . '_' . $request->tipo_examen . '.xlsx';

        return Excel::download(new BoletinEntregaExport($data, $cursos), $fileName);
    }
}