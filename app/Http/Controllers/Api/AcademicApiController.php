<?php

namespace App\Http\Controllers\Api;

use App\Models\MaterialAcademico;
use App\Models\ResultadoExamen;
use App\Models\Ciclo;
use App\Models\Inscripcion;
use App\Models\BoletinEntrega;
use App\Models\HorarioDocente;
use App\Helpers\AsistenciaHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AcademicApiController extends BaseController
{
    /**
     * Get academic materials for the logged in student.
     */
    public function getMaterials(Request $request)
    {
        $user = $request->user();
        
        // 1. Obtener inscripción activa con fallback (igual que en Dashboard)
        $inscripcion = Inscripcion::where('estudiante_id', $user->id)
            ->whereIn('estado_inscripcion', ['activo', 'aprobada', 'validado'])
            ->whereHas('ciclo', function ($query) {
                $query->where('es_activo', true);
            })
            ->with(['ciclo', 'carrera', 'aula', 'turno'])
            ->first();

        if (!$inscripcion) {
            $inscripcion = Inscripcion::whereHas('estudiante', function($q) use ($user) {
                $q->where('numero_documento', $user->numero_documento);
            })
            ->whereIn('estado_inscripcion', ['activo', 'aprobada', 'validado'])
            ->whereHas('ciclo', function ($query) {
                $query->where('es_activo', true);
            })
            ->with(['ciclo', 'carrera', 'aula', 'turno'])
            ->first();
        }

        if (!$inscripcion) {
            $inscripcion = Inscripcion::where('estudiante_id', $user->id)
                ->with(['ciclo', 'carrera', 'aula', 'turno'])
                ->latest()
                ->first();
        }

        if (!$inscripcion) {
            return $this->sendError('No se encontró una inscripción activa para tu usuario.', [], 404);
        }

        $materiales = MaterialAcademico::with('curso', 'profesor')
            ->where('ciclo_id', $inscripcion->ciclo_id)
            ->where('aula_id', $inscripcion->aula_id)
            ->orderBy('semana', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($material) {
                return [
                    'id' => $material->id,
                    'titulo' => $material->titulo,
                    'descripcion' => $material->descripcion,
                    'semana' => $material->semana,
                    'tipo' => $material->tipo,
                    'curso' => $material->curso->nombre ?? 'N/A',
                    'profesor' => $material->profesor->nombre_completo ?? 'N/A',
                    'url' => $material->tipo === 'link' 
                             ? $material->archivo 
                             : asset(Storage::url($material->archivo)),
                    'fecha' => $material->created_at->format('d/m/Y'),
                ];
            });

        return $this->sendResponse($materiales, 'Materiales recuperados con éxito');
    }

    /**
     * List available exam results.
     */
    public function getExamResults(Request $request)
    {
        $user = $request->user();
        
        // Visible results ordered
        $resultados = ResultadoExamen::with('ciclo')
            ->visible()
            ->ordenado()
            ->get()
            ->map(function($res) {
                return [
                    'id' => $res->id,
                    'nombre' => $res->nombre_examen,
                    'descripcion' => $res->descripcion,
                    'ciclo' => $res->ciclo->nombre ?? 'N/A',
                    'tipo' => $res->tipo_resultado,
                    'url_pdf' => $res->archivo_pdf ? asset(Storage::url($res->archivo_pdf)) : null,
                    'url_externa' => $res->link_externo,
                    'fecha_examen' => $res->fecha_examen->format('d/m/Y'),
                ];
            });

        return $this->sendResponse($resultados, 'Resultados de exámenes recuperados con éxito');
    }

    /**
     * Get the student's eligibility status for all exam periods.
     */
    public function getEligibility(Request $request)
    {
        $user = $request->user();
        
        $inscripcion = Inscripcion::where('estudiante_id', $user->id)
            ->whereIn('estado_inscripcion', ['activo', 'aprobada'])
            ->whereHas('ciclo', function ($q) {
                $q->where('es_activo', true);
            })
            ->with(['ciclo', 'carrera', 'turno', 'aula'])
            ->first();

        if (!$inscripcion) {
            return $this->sendError('No se encontró una inscripción activa para el ciclo actual.', [], 404);
        }

        $ciclo = $inscripcion->ciclo;
        $numeroDocumento = $user->numero_documento;

        $resumen = [
            'ciclo' => $ciclo->nombre,
            'inscripcion' => [
                'carrera' => $inscripcion->carrera->nombre ?? 'N/A',
                'turno' => $inscripcion->turno->nombre ?? 'N/A',
                'aula' => $inscripcion->aula->nombre ?? 'N/A',
                'aula_detalle' => $inscripcion->aula->descripcion ?? '',
            ],
            'examenes' => []
        ];

        try {
            $resumen['examenes'] = [
                'total_ciclo' => AsistenciaHelper::calcularInfoAsistenciaExamen(
                    $numeroDocumento, 
                    $ciclo->fecha_inicio, 
                    $ciclo->fecha_fin ?? ($ciclo->fecha_tercer_examen ?? ($ciclo->fecha_segundo_examen ?? now())), 
                    $ciclo
                ),
                'primer_examen' => $ciclo->fecha_primer_examen ? AsistenciaHelper::calcularInfoAsistenciaExamen(
                    $numeroDocumento, 
                    $ciclo->fecha_inicio, 
                    $ciclo->fecha_primer_examen, 
                    $ciclo
                ) : null,
                'segundo_examen' => ($ciclo->fecha_primer_examen && $ciclo->fecha_segundo_examen) ? AsistenciaHelper::calcularInfoAsistenciaExamen(
                    $numeroDocumento, 
                    AsistenciaHelper::getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo), 
                    $ciclo->fecha_segundo_examen, 
                    $ciclo
                ) : null,
                'tercer_examen' => ($ciclo->fecha_tercer_examen && $ciclo->fecha_segundo_examen) ? AsistenciaHelper::calcularInfoAsistenciaExamen(
                    $numeroDocumento, 
                    AsistenciaHelper::getSiguienteDiaHabil($ciclo->fecha_segundo_examen, $ciclo), 
                    $ciclo->fecha_tercer_examen, 
                    $ciclo
                ) : null,
            ];
        } catch (\Exception $e) {
            // Silently fail to allow the rest of the data to load
        }

        return $this->sendResponse($resumen, 'Estado de habilitación detallado recuperado.');
    }

    /**
     * List report cards and their delivery status.
     */
    public function getReportCards(Request $request)
    {
        $user = $request->user();
        
        $inscripciones = Inscripcion::where('estudiante_id', $user->id)
            ->with(['ciclo'])
            ->get();

        $datos = $inscripciones->map(function($ins) {
            $entregas = BoletinEntrega::where('inscripcion_id', $ins->id)
                ->with('curso')
                ->get()
                ->map(function($e) {
                    return [
                        'curso' => $e->curso->nombre ?? 'N/A',
                        'tipo_examen' => $e->tipo_examen,
                        'entregado' => (bool)$e->entregado,
                        'fecha_entrega' => $e->fecha_entrega ? (is_string($e->fecha_entrega) ? $e->fecha_entrega : $e->fecha_entrega->format('d/m/Y')) : null,
                    ];
                });

            return [
                'ciclo' => $ins->ciclo->nombre ?? 'N/A',
                'entregas' => $entregas
            ];
        });

        return $this->sendResponse($datos, 'Boletines de notas recuperados.');
    }

    /**
     * Get the class schedule for the student based on their assigned classroom.
     */
    public function getSchedule(Request $request)
    {
        $user = $request->user();
        
        // 1. Obtener inscripción activa
        $inscripcion = Inscripcion::where('estudiante_id', $user->id)
            ->whereIn('estado_inscripcion', ['activo', 'aprobada', 'validado'])
            ->whereHas('ciclo', function ($query) {
                $query->where('es_activo', true);
            })
            ->with(['ciclo', 'aula'])
            ->first();

        if (!$inscripcion) {
            $inscripcion = Inscripcion::whereHas('estudiante', function($q) use ($user) {
                $q->where('numero_documento', $user->numero_documento);
            })
            ->whereIn('estado_inscripcion', ['activo', 'aprobada', 'validado'])
            ->whereHas('ciclo', function ($query) {
                $query->where('es_activo', true);
            })
            ->with(['ciclo', 'aula'])
            ->first();
        }

        if (!$inscripcion) {
            $inscripcion = Inscripcion::where('estudiante_id', $user->id)
                ->with(['ciclo', 'aula'])
                ->latest()
                ->first();
        }

        if (!$inscripcion || !$inscripcion->aula_id) {
            return $this->sendResponse([
                'aula' => 'N/A',
                'hoy' => $this->getDiaSemanaSpanish(now()->dayOfWeek),
                'clases_hoy' => [],
                'semana_completa' => []
            ], 'No tienes un aula asignada actualmente.');
        }

        $hoy = now();
        $diaSemana = $this->getDiaSemanaSpanish($hoy->dayOfWeek);

        // 2. Obtener horarios del aula
        $horarios = HorarioDocente::with(['curso', 'docente', 'aula'])
            ->where('aula_id', $inscripcion->aula_id)
            ->where('ciclo_id', $inscripcion->ciclo_id)
            ->orderByRaw("FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')")
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy('dia_semana');

        // 3. Formatear datos
        $datos = [
            'aula' => $inscripcion->aula->nombre ?? 'N/A',
            'hoy' => $diaSemana,
            'clases_hoy' => $horarios[$diaSemana] ?? [],
            'semana_completa' => $horarios
        ];

        return $this->sendResponse($datos, 'Horario recuperado con éxito.');
    }

    private function getDiaSemanaSpanish($dayIndex)
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];
        return $dias[$dayIndex] ?? 'Lunes';
    }
}
