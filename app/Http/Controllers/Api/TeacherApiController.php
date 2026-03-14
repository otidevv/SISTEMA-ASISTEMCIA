<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\HorarioDocente;
use App\Models\AsistenciaDocente;
use Carbon\Carbon;

class TeacherApiController extends BaseController
{
    /**
     * Get the logged in teacher's schedule
     */
    public function getMySchedule(Request $request)
    {
        $user = $request->user();
        
        $schedule = HorarioDocente::with(['curso', 'aula', 'ciclo'])
            ->where('docente_id', $user->id)
            ->whereHas('ciclo', fn($q) => $q->where('es_activo', true))
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get();

        return $this->sendResponse($schedule, 'Horario recuperado con éxito.');
    }

    /**
     * Get students enrolled in a specific class/schedule.
     */
    public function getClassStudents($horario_id)
    {
        try {
            $horario = HorarioDocente::findOrFail($horario_id);
            
            // Get active incription students for this ciclo, aula, turno
            $estudiantes = \App\Models\User::whereHas('roles', fn($q) => $q->where('nombre', 'estudiante'))
                ->whereHas('inscripciones', function ($q) use ($horario) {
                    $q->where('ciclo_id', $horario->ciclo_id)
                      ->where('aula_id', $horario->aula_id)
                      ->where('turno_id', $horario->turno_id)
                      ->where('estado_inscripcion', 'activo');
                })
                ->select('id', 'numero_documento', 'nombre', 'apellido_paterno', 'apellido_materno')
                ->orderBy('apellido_paterno')
                ->get();

            return $this->sendResponse($estudiantes, 'Lista de alumnos recuperada.');
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar alumnos: ' . $e->getMessage());
        }
    }

    /**
     * Upload academic material via API.
     */
    public function uploadMaterial(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'semana' => 'required|integer|min:1|max:20',
            'curso_id' => 'required|exists:cursos,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
            'tipo' => 'required|in:archivo,link',
            'archivo' => 'required_if:tipo,archivo|file|max:5120', // 5MB limit
            'url' => 'required_if:tipo,link|url',
        ]);

        try {
            $user = $request->user();
            $path = null;

            if ($request->tipo === 'archivo') {
                $path = $request->file('archivo')->store('materiales', 'public');
            } else {
                $path = $request->url;
            }

            $material = \App\Models\MaterialAcademico::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion ?? '',
                'semana' => $request->semana,
                'tipo' => $request->tipo,
                'archivo' => $path,
                'curso_id' => $request->curso_id,
                'ciclo_id' => $request->ciclo_id,
                'aula_id' => $request->aula_id,
                'profesor_id' => $user->id,
            ]);

            return $this->sendResponse($material, 'Material subido con éxito.');
        } catch (\Exception $e) {
            return $this->sendError('Error al subir material: ' . $e->getMessage());
        }
    }
}
