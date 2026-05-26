<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\ExamenGrupoConfig;
use App\Models\ExamenPreguntaDistribucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamenEstructuraApiController extends Controller
{
    /**
     * Obtiene la estructura de exámenes para un ciclo.
     */
    public function getEstructura($cicloId)
    {
        $ciclo = Ciclo::find($cicloId);

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'Ciclo académico no encontrado'
            ], 404);
        }

        // Obtener configuraciones de los grupos
        $configsRaw = ExamenGrupoConfig::where('ciclo_id', $cicloId)->get();
        
        $configs = [];
        $defaultTemas = ['A' => 'P', 'B' => 'Q', 'C' => 'R'];
        
        foreach (['A', 'B', 'C'] as $grupo) {
            $config = $configsRaw->where('grupo', $grupo)->first();
            if ($config) {
                $configs[$grupo] = [
                    'tema' => $config->tema,
                    'duracion_minutos' => $config->duracion_minutos,
                    'puntaje_maximo' => $config->puntaje_maximo,
                    'puntaje_minimo_aprobatorio' => $config->puntaje_minimo_aprobatorio
                ];
            } else {
                $configs[$grupo] = [
                    'tema' => $defaultTemas[$grupo],
                    'duracion_minutos' => 150, // 2.5 horas por defecto
                    'puntaje_maximo' => 400,
                    'puntaje_minimo_aprobatorio' => 160
                ];
            }
        }

        // Obtener asignaturas (cursos activos)
        $cursos = Curso::where('estado', 1)->orderBy('nombre')->get();
        
        // Obtener distribución de preguntas actual
        $distribucionRaw = ExamenPreguntaDistribucion::where('ciclo_id', $cicloId)->get();

        $cursosData = $cursos->map(function ($curso) use ($distribucionRaw) {
            $preguntas = [];
            foreach (['A', 'B', 'C'] as $grupo) {
                $dist = $distribucionRaw->where('grupo', $grupo)->where('curso_id', $curso->id)->first();
                $preguntas[$grupo] = $dist ? $dist->cantidad_preguntas : 0;
            }

            return [
                'id' => $curso->id,
                'nombre' => $curso->nombre,
                'codigo' => $curso->codigo,
                'preguntas' => $preguntas
            ];
        });

        return response()->json([
            'success' => true,
            'ciclo' => [
                'id' => $ciclo->id,
                'nombre' => $ciclo->nombre
            ],
            'configs' => $configs,
            'cursos' => $cursosData
        ]);
    }

    /**
     * Guarda la estructura de exámenes para un ciclo.
     */
    public function saveEstructura(Request $request, $cicloId)
    {
        $ciclo = Ciclo::find($cicloId);

        if (!$ciclo) {
            return response()->json([
                'success' => false,
                'message' => 'Ciclo académico no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'configs' => 'required|array',
            'configs.*.tema' => 'nullable|string|max:50',
            'configs.*.duracion_minutos' => 'required|integer|min:1',
            'configs.*.puntaje_maximo' => 'required|integer|min:0',
            'configs.*.puntaje_minimo_aprobatorio' => 'required|integer|min:0',
            'preguntas' => 'required|array',
            'preguntas.*.curso_id' => 'required|exists:cursos,id',
            'preguntas.*.preguntas_A' => 'required|integer|min:0',
            'preguntas.*.preguntas_B' => 'required|integer|min:0',
            'preguntas.*.preguntas_C' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Guardar configuraciones por grupo
            foreach ($request->input('configs') as $grupo => $configData) {
                if (in_array($grupo, ['A', 'B', 'C'])) {
                    ExamenGrupoConfig::updateOrCreate(
                        [
                            'ciclo_id' => $cicloId,
                            'grupo' => $grupo
                        ],
                        [
                            'tema' => $configData['tema'] ?? null,
                            'duracion_minutos' => $configData['duracion_minutos'],
                            'puntaje_maximo' => $configData['puntaje_maximo'],
                            'puntaje_minimo_aprobatorio' => $configData['puntaje_minimo_aprobatorio'],
                        ]
                    );
                }
            }

            // 2. Guardar distribuciones de preguntas
            foreach ($request->input('preguntas') as $pregData) {
                $cursoId = $pregData['curso_id'];
                
                foreach (['A', 'B', 'C'] as $grupo) {
                    $cantPreguntas = (int)($pregData['preguntas_' . $grupo] ?? 0);

                    // Si es 0 o mayor, actualizamos/creamos
                    ExamenPreguntaDistribucion::updateOrCreate(
                        [
                            'ciclo_id' => $cicloId,
                            'grupo' => $grupo,
                            'curso_id' => $cursoId
                        ],
                        [
                            'cantidad_preguntas' => $cantPreguntas
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estructura de examen guardada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la estructura de examen: ' . $e->getMessage()
            ], 500);
        }
    }
}
