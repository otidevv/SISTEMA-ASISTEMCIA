<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ciclo;
use App\Models\CicloCarreraVacante;
use App\Models\Carrera;
use App\Models\ResultadoExamen;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ChatbotApiController extends Controller
{
    /**
     * Obtener toda la información relevante para el Chatbot.
     * Esta ruta es pública para permitir el acceso desde la landing page.
     */
    public function getAssistantData()
    {
        $cicloActivo = Ciclo::where('es_activo', true)->first();
        
        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay ciclo activo'], 404);
        }

        // 1. Datos del Ciclo
        $cicloData = [
            'id' => $cicloActivo->id,
            'nombre' => $cicloActivo->nombre,
            'inicio' => $cicloActivo->fecha_inicio ? $cicloActivo->fecha_inicio->translatedFormat('d \d\e F') : 'Pronto',
            'fin' => $cicloActivo->fecha_fin ? $cicloActivo->fecha_fin->translatedFormat('d \d\e F') : 'Pronto',
            'inscripciones' => "Abiertas hasta el " . ($cicloActivo->fecha_inicio ? $cicloActivo->fecha_inicio->subDays(2)->translatedFormat('d \d\e F') : 'próximamente'),
            'examenes' => [
                ['n' => '1° Examen', 'f' => $cicloActivo->fecha_primer_examen ? $cicloActivo->fecha_primer_examen->translatedFormat('d \d\e F') : 'Por definir'],
                ['n' => '2° Examen', 'f' => $cicloActivo->fecha_segundo_examen ? $cicloActivo->fecha_segundo_examen->translatedFormat('d \d\e F') : 'Por definir'],
                ['n' => '3° Examen (Final)', 'f' => $cicloActivo->fecha_tercer_examen ? $cicloActivo->fecha_tercer_examen->translatedFormat('d \d\e F') : 'Por definir'],
            ]
        ];

        // 2. Vacantes por Carrera (Si el activo no tiene, buscar el último con vacantes)
        $idCicloVacantes = $cicloActivo->id;
        if (CicloCarreraVacante::where('ciclo_id', $cicloActivo->id)->count() == 0) {
            $ultimoConVacantes = Ciclo::whereHas('vacantesCarreras')->orderBy('id', 'desc')->first();
            if ($ultimoConVacantes) {
                $idCicloVacantes = $ultimoConVacantes->id;
                // Si el nombre del ciclo activo es genérico, podemos usar el del ciclo con vacantes
                // Pero por ahora solo actualizamos el ID para el fetch
            }
        }

        $vacantes = CicloCarreraVacante::where('ciclo_id', $idCicloVacantes)
            ->with('carrera')
            ->get()
            ->map(function($v) {
                return [
                    'c' => $v->carrera->nombre,
                    'v' => $v->vacantes_total,
                    'd' => $v->vacantes_disponibles,
                    'g' => $v->carrera->grupo,
                    'img' => $v->carrera->imagen_url ? asset($v->carrera->imagen_url) : null,
                    'slug' => $v->carrera->slug,
                    'desc' => $v->carrera->descripcion
                ];
            });

        // 3. Grupos de Carreras (Agrupados)
        $grupos = [
            'A' => Carrera::where('grupo', 'LIKE', '%A%')->pluck('nombre')->implode(', '),
            'B' => Carrera::where('grupo', 'LIKE', '%B%')->pluck('nombre')->implode(', '),
            'C' => Carrera::where('grupo', 'LIKE', '%C%')->pluck('nombre')->implode(', '),
        ];

        // 4. Últimos Resultados
        $ultimosResultados = ResultadoExamen::where('ciclo_id', $cicloActivo->id)
            ->orderBy('fecha_examen', 'desc')
            ->first();

        return response()->json([
            'ciclo' => $cicloData,
            'vacantes' => $vacantes,
            'grupos' => [
                'A' => "💻 **Ingenierías**: " . ($grupos['A'] ?: "Sistemas, Forestal."),
                'B' => "🏥 **Salud**: " . ($grupos['B'] ?: "Enfermería, Veterinaria."),
                'C' => "⚖️ **Letras/Negocios**: " . ($grupos['C'] ?: "Derecho, Contabilidad, Educación."),
            ],
            'resultados_url' => route('resultados-examenes.public'),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
