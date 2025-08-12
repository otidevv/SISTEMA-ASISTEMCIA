<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ReniecController extends Controller
{
    /**
     * Consultar datos de RENIEC por DNI
     */
    public function consultarDni(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|size:8|regex:/^[0-9]+$/'
        ]);

        $dni = $request->dni;

        try {
            // Verificar si los datos están en caché (para evitar consultas repetidas)
            $cacheKey = 'reniec_dni_' . $dni;
            $datosCache = Cache::get($cacheKey);
            
            if ($datosCache) {
                return response()->json([
                    'success' => true,
                    'data' => $datosCache,
                    'source' => 'cache'
                ]);
            }

            // Consultar la API externa
            $response = Http::timeout(10)->get('https://apidatos.unamad.edu.pe/api/consulta/' . $dni);

            if ($response->successful()) {
                $datos = $response->json();
                
                // Verificar si se encontraron datos
                if (empty($datos) || !isset($datos['DNI'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se encontraron datos para el DNI proporcionado'
                    ], 404);
                }

                // Formatear los datos para el formulario
                $datosFormateados = $this->formatearDatos($datos);
                
                // Guardar en caché por 24 horas
                Cache::put($cacheKey, $datosFormateados, 86400);

                return response()->json([
                    'success' => true,
                    'data' => $datosFormateados,
                    'source' => 'api'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al consultar el servicio de RENIEC'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la consulta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatear datos de RENIEC para el formulario
     */
    private function formatearDatos($datos)
    {
        // Determinar género basado en el código
        $genero = 'M'; // Por defecto
        if (isset($datos['SEXO'])) {
            $genero = $datos['SEXO'] == '2' ? 'F' : 'M';
        }

        // Formatear fecha de nacimiento
        $fechaNacimiento = null;
        if (isset($datos['FECHA_NAC'])) {
            try {
                $fechaNacimiento = \Carbon\Carbon::parse($datos['FECHA_NAC'])->format('Y-m-d');
            } catch (\Exception $e) {
                $fechaNacimiento = null;
            }
        }

        return [
            'dni' => $datos['DNI'] ?? '',
            'nombres' => $datos['NOMBRES'] ?? '',
            'apellido_paterno' => $datos['AP_PAT'] ?? '',
            'apellido_materno' => $datos['AP_MAT'] ?? '',
            'fecha_nacimiento' => $fechaNacimiento,
            'genero' => $genero,
            'direccion' => $datos['DIRECCION'] ?? '',
            'ubigeo' => $datos['UBIGEO_DIR'] ?? '',
            'estado_civil' => $datos['EST_CIVIL'] ?? '',
            'nombre_madre' => $datos['MADRE'] ?? '',
            'nombre_padre' => $datos['PADRE'] ?? '',
            'datos_completos' => [
                'fecha_inscripcion' => $datos['FCH_INSCRIPCION'] ?? null,
                'fecha_emision' => $datos['FCH_EMISION'] ?? null,
                'fecha_caducidad' => $datos['FCH_CADUCIDAD'] ?? null,
                'ubigeo_nacimiento' => $datos['UBIGEO_NAC'] ?? null
            ]
        ];
    }

    /**
     * Consultar múltiples DNIs (para padres)
     */
    public function consultarMultiple(Request $request)
    {
        $request->validate([
            'dnis' => 'required|array|max:3',
            'dnis.*' => 'required|string|size:8|regex:/^[0-9]+$/'
        ]);

        $resultados = [];
        
        foreach ($request->dnis as $tipo => $dni) {
            try {
                // Verificar caché
                $cacheKey = 'reniec_dni_' . $dni;
                $datosCache = Cache::get($cacheKey);
                
                if ($datosCache) {
                    $resultados[$tipo] = [
                        'success' => true,
                        'data' => $datosCache
                    ];
                    continue;
                }

                // Consultar API
                $response = Http::timeout(10)->get('https://apidatos.unamad.edu.pe/api/consulta/' . $dni);
                
                if ($response->successful()) {
                    $datos = $response->json();
                    
                    if (!empty($datos) && isset($datos['DNI'])) {
                        $datosFormateados = $this->formatearDatos($datos);
                        Cache::put($cacheKey, $datosFormateados, 86400);
                        
                        $resultados[$tipo] = [
                            'success' => true,
                            'data' => $datosFormateados
                        ];
                    } else {
                        $resultados[$tipo] = [
                            'success' => false,
                            'message' => 'DNI no encontrado'
                        ];
                    }
                } else {
                    $resultados[$tipo] = [
                        'success' => false,
                        'message' => 'Error en la consulta'
                    ];
                }
            } catch (\Exception $e) {
                $resultados[$tipo] = [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'resultados' => $resultados
        ]);
    }
}