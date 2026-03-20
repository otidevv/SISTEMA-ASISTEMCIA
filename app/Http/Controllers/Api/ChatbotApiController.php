<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ciclo;
use App\Models\CicloCarreraVacante;
use App\Models\Carrera;
use App\Models\Curso;
use App\Models\ResultadoExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatbotApiController extends Controller
{
    /**
     * Procesar una pregunta del usuario usando Google Gemini AI.
     */
    public function ask(Request $request)
    {
        $message = $request->input('message');
        
        if (!$message) {
            return response()->json(['error' => 'No message provided'], 400);
        }

        // Obtener contexto del asistente
        $assistantData = $this->getAssistantData()->getData();

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error("Chatbot: GEMINI_API_KEY no encontrada en .env");
            return response()->json(['error' => 'Gemini API Key not configured'], 500);
        }

        // Construir el prompt del sistema
        $systemPrompt = "Eres 'Boni-Bot', el asistente virtual inteligente del CEPRE UNAMAD.\n";
        $systemPrompt .= "Identidad: Sé amable y servicial. Preséntate como Boni-Bot solo al INICIO del diálogo. En mensajes posteriores de la misma conversación, sé directo y evita repetir saludos largos o tu identidad para no ser redundante.\n";
        $systemPrompt .= "Tu objetivo es ayudar con información precisa y realizar sugerencias proactivas.\n\n";
        
        $systemPrompt .= "DATOS DEL CICLO ACTUAL:\n";
        $systemPrompt .= "- Nombre: " . $assistantData->ciclo->nombre . "\n";
        $systemPrompt .= "- Inscripciones: " . $assistantData->ciclo->inscripciones . "\n";
        $systemPrompt .= "- Inicio de clases: " . $assistantData->ciclo->inicio . "\n";
        $examenes = json_decode(json_encode($assistantData->ciclo->examenes), true);
        $systemPrompt .= "- Exámenes: " . implode(", ", array_map(fn($e) => "{$e['n']}: {$e['f']}", $examenes)) . "\n";
        $systemPrompt .= "- Costos: Matrícula S/. 100, Enseñanza S/. 1,050. Total: S/. 1,150.\n";
        $systemPrompt .= "- Resultados Oficiales: [Ver Resultados Aquí](https://portalcepre.unamad.edu.pe/resultados-examenes)\n\n";

        $systemPrompt .= "CARRERAS Y LOGOS:\n";
        foreach ($assistantData->vacantes as $v) {
            $logo = $v->img ? "\nLogo/Carrera: ![logo]({$v->img})\n" : "";
            $systemPrompt .= "- {$v->c}: {$v->v} vacantes. Grupo: {$v->g}. {$logo}";
        }
        
        $systemPrompt .= "\nCURSOS Y PREPARACIÓN:\n";
        foreach ($assistantData->cursos as $c) {
            $systemPrompt .= "- {$c->n}: {$c->d}\n";
        }
        $systemPrompt .= "- Ofrecemos **Ingreso Directo** a la UNAMAD (únicos en la región).\n\n";

        $systemPrompt .= "PROCESO DE INSCRIPCIÓN (MODAL DE POSTULACIÓN):\n";
        $systemPrompt .= "Para inscribirse, el usuario debe abrir el modal de postulación y seguir estos 5 pasos:\n";
        $systemPrompt .= "1. **Paso Personal**: Ingresar DNI y verificar datos (Nombres, Apellidos, Nacimiento, Teléfono, Dirección, Email). La contraseña por defecto será su DNI.\n";
        $systemPrompt .= "2. **Paso Padres**: Registrar datos de al menos uno de los padres (Padre o Madre).\n";
        $systemPrompt .= "3. **Paso Académico**: Elegir Carrera, Turno, Ubicación del Colegio y Nombre del Colegio.\n";
        $systemPrompt .= "4. **Paso Docs/Pago**: El sistema busca el pago con el DNI automáticamente. Debe subir: Foto del estudiante (JPG/PNG), DNI escaneado (PDF), Certificado de estudios (PDF) y Voucher escaneado.\n";
        $systemPrompt .= "5. **Paso Confirmar**: Revisar el resumen, marcar la declaración jurada y enviar.\n\n";

        $systemPrompt .= "INSTRUCCIONES DE RESPUESTA:\n";
        $systemPrompt .= "1. SIEMPRE que pregunten por una carrera específica, MUESTRA su logo usando ![logo](url) al inicio de la respuesta.\n";
        $systemPrompt .= "2. Si preguntan como inscribirse, explica los 5 pasos resumidos.\n";
        $systemPrompt .= "3. Usa emojis y un tono amable.\n";
        $systemPrompt .= "4. Usa Markdown (negritas, listas).\n";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nPregunta del usuario: " . $message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Lo siento, tuve un problema al procesar tu respuesta.";
                return response()->json(['response' => $aiResponse]);
            }

            return response()->json(['error' => 'Error from Gemini API'], 502);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

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
            'A' => $vacantes->where('g', 'A')->pluck('c')->unique()->implode(', '),
            'B' => $vacantes->where('g', 'B')->pluck('c')->unique()->implode(', '),
            'C' => $vacantes->where('g', 'C')->pluck('c')->unique()->implode(', '),
        ];

        // 4. Cursos (Módulos)
        $cursos = Curso::where('estado', 1)->get()->map(function($c) {
            return [
                'n' => $c->nombre,
                'd' => $c->descripcion
            ];
        });

        return response()->json([
            'ciclo' => $cicloData,
            'vacantes' => $vacantes,
            'grupos' => $grupos,
            'cursos' => $cursos,
            'resultados_url' => 'https://portalcepre.unamad.edu.pe/resultados-examenes',
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
