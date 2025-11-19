<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\Ciclo;
use App\Models\Postulacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TarjetasController extends Controller
{
    public function obtenerPostulantes(Request $request)
    {
        try {
            $cicloActivo = Ciclo::where('es_activo', true)->first();

            if (!$cicloActivo) {
                return response()->json(['error' => 'No hay un ciclo activo configurado.'], 404);
            }

            $inscripciones = Inscripcion::with(['estudiante', 'carrera', 'aula'])
                ->where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('estudiante.postulaciones', function ($query) use ($cicloActivo) {
                    $query->where('ciclo_id', $cicloActivo->id)->where('estado', 'aprobado');
                })
                ->get();

            if ($inscripciones->isEmpty()) {
                $postulantesAprobados = Postulacion::with(['estudiante', 'carrera', 'ciclo'])
                    ->where('ciclo_id', $cicloActivo->id)
                    ->where('estado', 'aprobado')
                    ->get();

                if ($postulantesAprobados->isEmpty()) {
                    return response()->json([]);
                }

                $temas = ['P', 'Q', 'R'];
                $data = $postulantesAprobados->map(function ($postulacion) use ($temas) {
                    $tema = $temas[array_rand($temas)];
                    return [
                        'nombres' => $postulacion->estudiante->nombre . ' ' . $postulacion->estudiante->apellido_paterno,
                        'carrera' => $postulacion->carrera->nombre,
                        'aula' => 'POR ASIGNAR',
                        'codigo' => $postulacion->codigo_postulante,
                        'grupo' => 'POR ASIGNAR',
                        'tema' => $tema,
                        'foto' => $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                        'foto_path' => $postulacion->foto_path,
                    ];
                });
                return response()->json($data);
            }

            $temas = ['P', 'Q', 'R'];
            $postulantes = $inscripciones->map(function ($inscripcion) use ($temas) {
                $tema = $temas[array_rand($temas)];
                $postulacion = Postulacion::where('estudiante_id', $inscripcion->estudiante_id)
                                          ->where('ciclo_id', $inscripcion->ciclo_id)
                                          ->first();

                return [
                    'nombres' => $inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido_paterno,
                    'carrera' => $inscripcion->carrera->nombre,
                    'aula' => $inscripcion->aula ? $inscripcion->aula->nombre : 'N/A',
                    'codigo' => $postulacion ? $postulacion->codigo_postulante : $inscripcion->codigo_inscripcion,
                    'grupo' => $inscripcion->aula ? $inscripcion->aula->nombre : 'N/A',
                    'tema' => $tema,
                    'foto' => $postulacion && $postulacion->foto_path ? asset('storage/' . $postulacion->foto_path) : null,
                    'foto_path' => $postulacion ? $postulacion->foto_path : null,
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
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'postulantes' => 'required|array',
            'postulantes.*.nombres' => 'required|string',
            'postulantes.*.carrera' => 'required|string',
            'postulantes.*.aula' => 'required|string',
            'postulantes.*.codigo' => 'required|string',
            'postulantes.*.grupo' => 'required|string',
            'postulantes.*.tema' => 'required|string',
            'postulantes.*.foto' => 'nullable|string',
            'postulantes.*.foto_path' => 'nullable|string',
        ]);

        $postulantes = $request->postulantes;
        $tarjetasData = [];

        \Log::info('Procesando ' . count($postulantes) . ' postulantes para PDF');

        // Usar un placeholder de 1x1 pixel transparente como fallback definitivo.
        // Esto evita cualquier dependencia del sistema de archivos para el placeholder.
        $placeholderBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        foreach ($postulantes as $postulante) {
            $postulanteData = (array) $postulante;
            $base64Image = null;
            $fotoPath = $postulanteData['foto_path'] ?? null;

            // 1. Intentar cargar la foto desde el almacenamiento local
            if ($fotoPath) {
                $fullPath = storage_path('app/public/' . $fotoPath);
                if (file_exists($fullPath)) {
                    try {
                        $imageData = file_get_contents($fullPath);
                        $mime = mime_content_type($fullPath);
                        $base64Image = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                    } catch (\Exception $e) {
                        \Log::warning('Fallo al leer foto desde storage: ' . $fullPath . '. Error: ' . $e->getMessage());
                    }
                } else {
                    \Log::warning('Archivo de foto no encontrado en: ' . $fullPath . ' para: ' . $postulanteData['nombres']);
                }
            }

            // 2. Si no se pudo cargar la foto, usar el placeholder
            if (!$base64Image) {
                $base64Image = $placeholderBase64;
                \Log::info('Usando placeholder para: ' . $postulanteData['nombres']);
            }

            $tarjetasData[] = [
                'nombres' => $postulanteData['nombres'],
                'carrera' => $postulanteData['carrera'],
                'aula' => $postulanteData['aula'],
                'codigo' => $postulanteData['codigo'],
                'grupo' => $postulanteData['grupo'],
                'tema' => $postulanteData['tema'],
                'foto' => $base64Image,
            ];
        }

        \Log::info('Datos preparados para PDF: ' . count($tarjetasData) . ' tarjetas');
        $viewPath = 'tarjetas.pdf';
        \Log::info('Generando PDF con vista: ' . $viewPath);

        try {
            $pdf = Pdf::loadView($viewPath, ['tarjetas' => $tarjetasData])
                ->setPaper('a4', 'portrait');
            \Log::info('PDF generado exitosamente');
            return $pdf->download('etiquetas_examen_preuni_' . date('YmdHis') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar PDF: ' . $e->getMessage()], 500);
        }
    }
}