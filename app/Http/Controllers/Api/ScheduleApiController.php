<?php

namespace App\Http\Controllers\Api;

use App\Models\Aula;
use App\Models\Ciclo;
use App\Models\HorarioDocente;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Carbon\Carbon;

class ScheduleApiController extends BaseController
{
    /**
     * List all classrooms.
     */
    public function getAulas()
    {
        $aulas = Aula::orderBy('nombre')->get();
        return $this->sendResponse($aulas, 'Aulas recuperadas con éxito.');
    }

    /**
     * List all cycles.
     */
    public function getCiclos()
    {
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        return $this->sendResponse($ciclos, 'Ciclos recuperados con éxito.');
    }

    /**
     * Get the schedule for a specific classroom, cycle and shift.
     */
    public function getClassroomSchedule(Request $request)
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);

        $horarios = HorarioDocente::with(['docente', 'curso', 'aula', 'ciclo'])
            ->where('ciclo_id', $request->ciclo_id)
            ->where('aula_id', $request->aula_id)
            ->where('turno', $request->turno)
            ->orderByRaw("FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')")
            ->orderBy('hora_inicio')
            ->get();

        // Organizar por día para facilitar el consumo en la App
        $organizado = $horarios->groupBy('dia_semana')->map(function($dia) {
            return $dia->map(function($h) {
                return [
                    'id' => $h->id,
                    'curso' => $h->curso->nombre ?? 'Receso',
                    'curso_color' => $h->curso->color ?? '#94a3b8',
                    'docente' => $h->docente->nombre_completo ?? 'N/A',
                    'hora_inicio' => substr($h->hora_inicio, 0, 5),
                    'hora_fin' => substr($h->hora_fin, 0, 5),
                    'grupo' => $h->grupo,
                ];
            });
        });

        // Obtener slots de tiempo dinámicos si el usuario los necesita para la grilla
        // (Similar a la lógica del web)
        $ciclo = Ciclo::find($request->ciclo_id);
        $slots = $this->obtenerSlotsDinámicos($horarios, $ciclo, $request->turno);

        return $this->sendResponse([
            'horarios' => $organizado,
            'slots' => $slots,
            'aula' => Aula::find($request->aula_id)->nombre,
            'turno' => $request->turno
        ], 'Horario de aula recuperado con éxito.');
    }

    private function obtenerSlotsDinámicos($horarios, $ciclo, $turno)
    {
        $points = [];
        foreach ($horarios as $h) {
            $points[] = substr($h->hora_inicio, 0, 5);
            $points[] = substr($h->hora_fin, 0, 5);
        }

        // Agregar recesos del ciclo
        if ($turno === 'MAÑANA') {
            if ($ciclo->receso_manana_inicio) $points[] = substr($ciclo->receso_manana_inicio, 0, 5);
            if ($ciclo->receso_manana_fin) $points[] = substr($ciclo->receso_manana_fin, 0, 5);
        } else {
            if ($ciclo->receso_tarde_inicio) $points[] = substr($ciclo->receso_tarde_inicio, 0, 5);
            if ($ciclo->receso_tarde_fin) $points[] = substr($ciclo->receso_tarde_fin, 0, 5);
        }

        $points = array_unique(array_filter($points));
        sort($points);

        $slots = [];
        for ($i = 0; $i < count($points) - 1; $i++) {
            $slots[] = [
                'inicio' => $points[$i],
                'fin' => $points[$i+1]
            ];
        }

        return $slots;
    }
}
