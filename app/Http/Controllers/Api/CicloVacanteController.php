<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\CicloCarreraVacante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CicloVacanteController extends Controller
{
    /**
     * Obtener vacantes de un ciclo
     */
    public function getVacantesByCiclo($cicloId)
    {
        try {
            $ciclo = Ciclo::findOrFail($cicloId);
            
            // Obtener todas las carreras activas
            $todasLasCarreras = Carrera::activas()
                ->select('id', 'codigo', 'nombre')
                ->orderBy('nombre')
                ->get();
            
            // Obtener vacantes existentes
            $vacantesExistentes = CicloCarreraVacante::where('ciclo_id', $cicloId)
                ->get()
                ->keyBy('carrera_id');
            
            // Construir lista completa de vacantes (incluye carreras sin vacantes configuradas)
            $vacantes = $todasLasCarreras->map(function ($carrera) use ($cicloId, $vacantesExistentes) {
                $vacanteExistente = $vacantesExistentes->get($carrera->id);
                
                if ($vacanteExistente) {
                    return [
                        'id' => $vacanteExistente->id,
                        'carrera_id' => $carrera->id,
                        'carrera_nombre' => $carrera->nombre,
                        'carrera_codigo' => $carrera->codigo,
                        'vacantes_total' => $vacanteExistente->vacantes_total,
                        'vacantes_ocupadas' => $vacanteExistente->vacantes_ocupadas,
                        'vacantes_reservadas' => $vacanteExistente->vacantes_reservadas,
                        'vacantes_disponibles' => $vacanteExistente->vacantes_disponibles,
                        'observaciones' => $vacanteExistente->observaciones,
                        'estado' => $vacanteExistente->estado,
                        'porcentaje_ocupacion' => $vacanteExistente->porcentaje_ocupacion,
                        'estado_vacantes' => $vacanteExistente->estado_vacantes
                    ];
                } else {
                    // Carrera sin vacantes configuradas aún
                    return [
                        'id' => null,
                        'carrera_id' => $carrera->id,
                        'carrera_nombre' => $carrera->nombre,
                        'carrera_codigo' => $carrera->codigo,
                        'vacantes_total' => 0,
                        'vacantes_ocupadas' => 0,
                        'vacantes_reservadas' => 0,
                        'vacantes_disponibles' => 0,
                        'observaciones' => '',
                        'estado' => true,
                        'porcentaje_ocupacion' => 0,
                        'estado_vacantes' => 'sin-configurar'
                    ];
                }
            });

            // Ya no necesitamos carreras disponibles porque las mostramos todas
            $carrerasDisponibles = [];

            return response()->json([
                'success' => true,
                'ciclo' => [
                    'id' => $ciclo->id,
                    'nombre' => $ciclo->nombre,
                    'codigo' => $ciclo->codigo
                ],
                'vacantes' => $vacantes,
                'carreras_disponibles' => $carrerasDisponibles,
                'resumen' => [
                    'total_carreras' => $vacantes->count(),
                    'total_vacantes' => $vacantes->sum('vacantes_total'),
                    'vacantes_ocupadas' => $vacantes->sum('vacantes_ocupadas'),
                    'vacantes_disponibles' => $vacantes->sum('vacantes_disponibles')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener vacantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar o actualizar vacantes para un ciclo
     */
    public function saveVacantes(Request $request, $cicloId)
    {
        $validator = Validator::make($request->all(), [
            'vacantes' => 'required|array',
            'vacantes.*.carrera_id' => 'required|exists:carreras,id',
            'vacantes.*.vacantes_total' => 'required|integer|min:0',
            'vacantes.*.observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $ciclo = Ciclo::findOrFail($cicloId);
            $savedVacantes = [];

            foreach ($request->vacantes as $vacanteData) {
                // Solo crear/actualizar si hay cambios significativos
                $vacante = CicloCarreraVacante::updateOrCreate(
                    [
                        'ciclo_id' => $cicloId,
                        'carrera_id' => $vacanteData['carrera_id']
                    ],
                    [
                        'vacantes_total' => $vacanteData['vacantes_total'],
                        'observaciones' => $vacanteData['observaciones'] ?? null,
                        'estado' => true
                    ]
                );

                $savedVacantes[] = $vacante->load('carrera');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vacantes guardadas exitosamente',
                'vacantes' => $savedVacantes
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar vacantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar vacantes para una carrera específica
     */
    public function addVacanteCarrera(Request $request, $cicloId)
    {
        $validator = Validator::make($request->all(), [
            'carrera_id' => 'required|exists:carreras,id',
            'vacantes_total' => 'required|integer|min:0',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar que no exista ya
            $existe = CicloCarreraVacante::where('ciclo_id', $cicloId)
                ->where('carrera_id', $request->carrera_id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existen vacantes configuradas para esta carrera en este ciclo'
                ], 400);
            }

            $vacante = CicloCarreraVacante::create([
                'ciclo_id' => $cicloId,
                'carrera_id' => $request->carrera_id,
                'vacantes_total' => $request->vacantes_total,
                'observaciones' => $request->observaciones,
                'estado' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vacantes agregadas exitosamente',
                'vacante' => $vacante->load('carrera')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar vacantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar vacantes específicas
     */
    public function updateVacante(Request $request, $vacanteId)
    {
        $validator = Validator::make($request->all(), [
            'vacantes_total' => 'integer|min:0',
            'observaciones' => 'nullable|string',
            'estado' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $vacante = CicloCarreraVacante::findOrFail($vacanteId);

            // Validar que no se reduzcan las vacantes por debajo de las ocupadas
            if (isset($request->vacantes_total) && 
                $request->vacantes_total < ($vacante->vacantes_ocupadas + $vacante->vacantes_reservadas)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede reducir las vacantes por debajo de las ya ocupadas o reservadas'
                ], 400);
            }

            $vacante->update($request->only([
                'vacantes_total',
                'observaciones',
                'estado'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Vacantes actualizadas exitosamente',
                'vacante' => $vacante->load('carrera')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar vacantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar vacantes de una carrera
     */
    public function deleteVacante($vacanteId)
    {
        try {
            $vacante = CicloCarreraVacante::findOrFail($vacanteId);

            // Verificar que no haya vacantes ocupadas
            if ($vacante->vacantes_ocupadas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque hay vacantes ocupadas'
                ], 400);
            }

            $vacante->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vacantes eliminadas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar vacantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de vacantes por carrera
     */
    public function getResumenVacantes()
    {
        try {
            $cicloActivo = Ciclo::activo()->first();
            
            if (!$cicloActivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay ciclo activo'
                ], 404);
            }

            $resumen = CicloCarreraVacante::with('carrera')
                ->where('ciclo_id', $cicloActivo->id)
                ->where('estado', true)
                ->get()
                ->map(function ($vacante) {
                    return [
                        'carrera' => $vacante->carrera->nombre,
                        'codigo' => $vacante->carrera->codigo,
                        'total' => $vacante->vacantes_total,
                        'ocupadas' => $vacante->vacantes_ocupadas,
                        'disponibles' => $vacante->vacantes_disponibles,
                        'porcentaje' => $vacante->porcentaje_ocupacion,
                        'estado' => $vacante->estado_vacantes,
                    ];
                });

            return response()->json([
                'success' => true,
                'ciclo' => $cicloActivo->nombre,
                'resumen' => $resumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ], 500);
        }
    }
}