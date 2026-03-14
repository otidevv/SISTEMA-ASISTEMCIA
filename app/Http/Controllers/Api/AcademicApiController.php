<?php

namespace App\Http\Controllers\Api;

use App\Models\MaterialAcademico;
use App\Models\ResultadoExamen;
use App\Models\Ciclo;
use App\Models\Inscripcion;
use App\Models\BoletinEntrega;
use App\Helpers\AsistenciaHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Storage;

class AcademicApiController extends BaseController
{
    /**
     * Get academic materials for the logged in student.
     */
    public function getMaterials(Request $request)
    {
        $user = $request->user();
        
        // Get active inscription
        $inscripcion = Inscripcion::where('estudiante_id', $user->id)
            ->where('estado_inscripcion', 'activo')
            ->first();

        if (!$inscripcion) {
            return $this->sendError('El estudiante no tiene inscripciones activas.', [], 404);
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
            ->with('ciclo')
            ->first();

        if (!$inscripcion) {
            return $this->sendError('No se encontró una inscripción activa para el ciclo actual.', [], 404);
        }

        $ciclo = $inscripcion->ciclo;
        $numeroDocumento = $user->numero_documento;

        // Calcular datos para cada periodo
        $resumen = [
            'ciclo' => $ciclo->nombre,
            'examenes' => [
                'primer_examen' => AsistenciaHelper::calcularInfoAsistenciaExamen(
                    $numeroDocumento, 
                    $ciclo->fecha_inicio, 
                    $ciclo->fecha_primer_examen, 
                    $ciclo
                ),
                'segundo_examen' => $ciclo->fecha_segundo_examen ? AsistenciaHelper::calcularInfoAsistenciaExamen(
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
            ]
        ];
        
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
}
