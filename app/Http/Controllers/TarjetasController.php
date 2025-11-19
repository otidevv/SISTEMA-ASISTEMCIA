<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\Ciclo;
use App\Models\Postulacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http; // NECESARIO para descargar la foto

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

    /**
     * Exporta las tarjetas a PDF, incluyendo la conversión de fotos a Base64.
     */
    public function exportarPDF(Request $request)
    {
        // SOLUCIÓN 1: Aumentar el tiempo máximo de ejecución de PHP
        set_time_limit(300); // 5 minutos
        // SOLUCIÓN 2: Aumentar el límite de memoria para evitar fallos durante el renderizado de CSS/imágenes
        ini_set('memory_limit', '512M'); // CRÍTICO: 512MB de memoria

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
        $tarjetasData = [];

        // PASO DE PRUEBA: Desactivar la descarga de imágenes para verificar el renderizado del texto.
        foreach ($postulantes as $postulante) {
            $postulanteData = (array) $postulante; 
            
            // *** MANTENER ESTO COMO NULL PARA LA PRUEBA DE TEXTO ***
            $base64Image = null; 
            
            /*
            // CÓDIGO ORIGINAL (DESACTIVADO PARA PRUEBA DE TEXTO)
            $fotoUrl = $postulanteData['foto'] ?? null;
            if ($fotoUrl) {
                try {
                    $response = Http::timeout(15)->get($fotoUrl); 
                    if ($response->successful()) {
                        $imageData = $response->body();
                        $mime = $response->header('Content-Type') ?? 'image/jpeg'; 
                        $base64Image = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Fallo al procesar foto para PDF: ' . $fotoUrl . ' Error: ' . $e->getMessage());
                }
            }
            */

            $tarjetasData[] = [
                'nombres' => $postulanteData['nombres'],
                'carrera' => $postulanteData['carrera'],
                'aula' => $postulanteData['aula'],
                'codigo' => $postulanteData['codigo'],
                'grupo' => $postulanteData['grupo'],
                'tema' => $postulanteData['tema'],
                // Se pasa NULL para la foto en la vista
                'foto' => $base64Image, 
            ];
        }


        // Generar PDF
        // La vista es 'tarjetas.pdf' (resources/views/tarjetas/pdf.blade.php)
        $viewPath = 'tarjetas.pdf'; 

        $pdf = Pdf::loadView($viewPath, ['tarjetas' => $tarjetasData])
            ->setPaper('a4', 'portrait');

        return $pdf->download('etiquetas_examen_preuni_' . date('YmdHis') . '.pdf');
    }
}