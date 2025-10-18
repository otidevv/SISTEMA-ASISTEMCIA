<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Aula;
use App\Models\Inscripcion;
use App\Models\Curso;
use App\Models\BoletinEntrega;
use App\Models\HorarioDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                $rowData['courses'][] = [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'entregado' => $entregas->has($curso->id) ? $entregas[$curso->id]->entregado : false,
                ];
            }
            $data[] = $rowData;
        }

        return response()->json(['data' => $data, 'cursos' => $cursos]);
    }

    public function marcarEntrega(Request $request)
    {
        $request->validate([
            'inscripcion_id' => 'required|exists:inscripciones,id',
            'curso_id' => 'required|exists:cursos,id',
            'tipo_examen' => 'required|string|max:255',
            'entregado' => 'required|boolean',
        ]);

        BoletinEntrega::updateOrCreate(
            [
                'inscripcion_id' => $request->inscripcion_id,
                'curso_id' => $request->curso_id,
                'tipo_examen' => $request->tipo_examen,
            ],
            [
                'entregado' => $request->entregado,
                'fecha_entrega' => $request->entregado ? now() : null,
            ]
        );

        return response()->json(['success' => true]);
    }
}