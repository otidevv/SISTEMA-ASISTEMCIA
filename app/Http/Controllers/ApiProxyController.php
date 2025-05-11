<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ApiProxyController extends Controller
{
    public function consultaDNI($dni)
    {
        // Validación de formato
        if (!preg_match('/^[0-9]{8}$/', $dni)) {
            return response()->json(['error' => 'Formato de DNI inválido'], 400);
        }

        try {
            // Usar cache para peticiones repetidas (ahorra tiempo y recursos)
            return Cache::remember('dni_' . $dni, now()->addDays(30), function () use ($dni) {
                $response = Http::timeout(5)->get("https://apidatos.unamad.edu.pe/api/consulta/{$dni}");

                if ($response->successful()) {
                    return $response->json();
                }

                return response()->json(['error' => 'Error al consultar DNI: ' . $response->status()], $response->status());
            });
        } catch (\Exception $e) {
            Log::error("Error consultando DNI: {$dni}", ['error' => $e->getMessage()]);
            return response()->json(['error' => 'No se pudo conectar con el servicio de consulta'], 500);
        }
    }
}
