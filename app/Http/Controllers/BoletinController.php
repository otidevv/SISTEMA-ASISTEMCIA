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
        $validatedData = $request->validate([
            'tipo_examen' => 'required|string|max:255',
            'entregas_json' => 'required|json',
        ]);

        $tipo_examen = $validatedData['tipo_examen'];
        $entregas = json_decode($validatedData['entregas_json'], true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($entregas)) {
            return response()->json(['success' => false, 'message' => 'El formato de las entregas es inválido.'], 422);
        }

        // To use Laravel's validator, we need to wrap our array
        $payload = ['entregas' => $entregas];
        $rules = [
            'entregas' => 'present|array|min:1',
            'entregas.*.inscripcion_id' => 'required|exists:inscripciones,id',
            'entregas.*.curso_id' => 'required|exists:cursos,id',
            'entregas.*.entregado' => 'required|boolean',
        ];

        $validator = validator($payload, $rules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos de entrega inválidos.', 'errors' => $validator->errors()], 422);
        }

        $validatedEntregas = $validator->validated()['entregas'];

        DB::beginTransaction();
        try {
            foreach ($validatedEntregas as $entrega) {
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
            
            $message = count($validatedEntregas) > 1 ? 'Cambios guardados correctamente.' : 'Cambio guardado correctamente.';
            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar entregas de boletín: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado al guardar los cambios.'], 500);
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