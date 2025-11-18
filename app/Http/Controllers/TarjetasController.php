<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\Ciclo;
use App\Models\Postulacion;
use Barryvdh\DomPDF\Facade\Pdf;

class TarjetasController extends Controller
{
    public function obtenerPostulantes(Request $request)
    {
        try {
            // Obtener el ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)->first();

            if (!$cicloActivo) {
                return response()->json(['error' => 'No hay un ciclo activo configurado.'], 404);
            }

            // Obtener las inscripciones del ciclo activo que también tienen una postulación aprobada
            $inscripciones = Inscripcion::with(['estudiante', 'carrera', 'aula'])
                ->where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.postulaciones', function ($query) use ($cicloActivo) {
                    $query->where('ciclo_id', $cicloActivo->id)->where('estado', 'aprobado');
                })
                ->get();

            if ($inscripciones->isEmpty()) {
                // Si no hay inscritos, podría ser un ciclo de postulantes, buscar postulantes aprobados
                $postulantesAprobados = Postulacion::with(['estudiante', 'carrera', 'ciclo'])
                    ->where('ciclo_id', $cicloActivo->id)
                    ->where('estado', 'aprobado')
                    ->get();

                if($postulantesAprobados->isEmpty()){
                    return response()->json([]);
                }

                $temas = ['P', 'Q', 'R'];
                $data = $postulantesAprobados->map(function ($postulacion) use ($temas) {
                    $tema = $temas[array_rand($temas)];
                    return [
                        'nombres' => $postulacion->estudiante->nombre . ' ' . $postulacion->estudiante->apellido_paterno,
                        'carrera' => $postulacion->carrera->nombre,
                        'aula' => 'POR ASIGNAR', // Los postulantes puros no tienen aula asignada aún
                        'codigo' => $postulacion->codigo_postulante,
                        'grupo' => 'POR ASIGNAR',
                        'tema' => $tema,
                        'foto' => $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                    ];
                });
                return response()->json($data);
            }

            $temas = ['P', 'Q', 'R'];

            $postulantes = $inscripciones->map(function ($inscripcion) use ($temas) {
                // Asignar un tema aleatorio
                $tema = $temas[array_rand($temas)];

                $postulacion = Postulacion::where('estudiante_id', $inscripcion->estudiante_id)
                                          ->where('ciclo_id', $inscripcion->ciclo_id)
                                          ->first();

                return [
                    'nombres' => $inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido_paterno,
                    'carrera' => $inscripcion->carrera->nombre,
                    'aula' => $inscripcion->aula ? $inscripcion->aula->nombre : 'N/A',
                    'codigo' => $postulacion ? $postulacion->codigo_postulante : $inscripcion->codigo_inscripcion,
                    'grupo' => $inscripcion->aula ? $inscripcion->aula->nombre : 'N/A', // Asumiendo que grupo es el aula
                    'tema' => $tema,
                    'foto' => $postulacion && $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                ];
            });

            return response()->json($postulantes);

        } catch (\Exception $e) {
            \Log::error('Error en TarjetasController@obtenerPostulantes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos. Revise el log del sistema.'], 500);
        }
    }

    public function exportarPDF(Request $request)
    {
        $request->validate([
            'postulantes' => 'required|array',
            'postulantes.*.nombres' => 'required|string',
            'postulantes.*.carrera' => 'required|string',
            'postulantes.*.aula' => 'required|string',
            'postulantes.*.codigo' => 'required|string',
            'postulantes.*.grupo' => 'required|string',
            'postulantes.*.tema' => 'required|string',
            'postulantes.*.foto' => 'nullable|string'
        ]);

        $postulantes = $request->postulantes;

        // Preparar datos para la vista PDF
        $tarjetasData = collect($postulantes)->map(function ($postulante) {
            return [
                'nombres' => $postulante['nombres'],
                'carrera' => $postulante['carrera'],
                'aula' => $postulante['aula'],
                'codigo' => $postulante['codigo'],
                'grupo' => $postulante['grupo'],
                'tema' => $postulante['tema'],
                'foto' => $postulante['foto'] ?? null,
            ];
        });

        // Generar PDF
        $pdf = Pdf::loadView('tarjetas.pdf', ['tarjetas' => $tarjetasData])
            ->setPaper('a4', 'portrait');

        return $pdf->download('tarjetas_preuni_' . date('YmdHis') . '.pdf');
    }
}
