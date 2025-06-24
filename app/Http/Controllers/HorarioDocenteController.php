<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HorarioDocente;
use App\Models\User;
use App\Models\Ciclo;
use App\Models\Aula;
use App\Models\Curso;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class HorarioDocenteController extends Controller
{
    public function index()
    {
        // Carga las relaciones necesarias para mostrar en la vista
        $horarios = HorarioDocente::with('docente', 'aula', 'ciclo', 'curso')->paginate(10);
        return view('horarios_docentes.index', compact('horarios'));
    }

    public function create()
    {
        // Obtener todos los usuarios con rol 'profesor'
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->get();

        $aulas = Aula::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::where('estado', true)->get();
        
        // Definir turnos disponibles
        $turnos = [
            'MAÑANA' => 'MAÑANA',
            'TARDE' => 'TARDE',
            'NOCHE' => 'NOCHE'
        ];
        
        return view('horarios_docentes.create', compact('docentes', 'aulas', 'ciclos', 'cursos', 'turnos'));
    }

    public function store(Request $request)
    {
        // Validaciones básicas - ACTUALIZADO para incluir turno
        $validatedData = $request->validate([
            'docente_id'   => 'required|exists:users,id',
            'aula_id'      => 'required|exists:aulas,id',
            'ciclo_id'     => 'required|exists:ciclos,id',
            'curso_id'     => 'required|exists:cursos,id',
            'dia_semana'   => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'hora_inicio'  => 'required|date_format:H:i',
            'hora_fin'     => 'required|date_format:H:i|after:hora_inicio',
            'turno'        => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);

        // Validación de conflictos de horarios
        $this->validateScheduleConflicts($request);

        HorarioDocente::create($validatedData);

        return redirect()->route('horarios-docentes.index')
            ->with('success', 'Horario asignado correctamente.');
    }

    public function edit($id)
    {
        $horario = HorarioDocente::findOrFail($id);

        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->get();

        $aulas = Aula::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::all();
        
        $turnos = [
            'MAÑANA' => 'MAÑANA',
            'TARDE' => 'TARDE',
            'NOCHE' => 'NOCHE'
        ];

        return view('horarios_docentes.edit', compact('horario', 'docentes', 'aulas', 'ciclos', 'cursos', 'turnos'));
    }

    public function update(Request $request, $id)
    {
        // Validaciones básicas - ACTUALIZADO para incluir turno
        $validatedData = $request->validate([
            'docente_id'   => 'required|exists:users,id',
            'aula_id'      => 'required|exists:aulas,id',
            'ciclo_id'     => 'required|exists:ciclos,id',
            'curso_id'     => 'required|exists:cursos,id',
            'dia_semana'   => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'hora_inicio'  => 'required|date_format:H:i',
            'hora_fin'     => 'required|date_format:H:i|after:hora_inicio',
            'turno'        => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);

        // Validación de conflictos de horarios (excluyendo el horario actual)
        $this->validateScheduleConflicts($request, $id);

        $horario = HorarioDocente::findOrFail($id);
        $horario->update($validatedData);

        return redirect()->route('horarios-docentes.index')
            ->with('success', 'Horario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $horario = HorarioDocente::findOrFail($id);
        $horario->delete();

        return back()->with('success', 'Horario eliminado exitosamente.');
    }

    /**
     * Vista de tabla semanal como calendario - MEJORADO con filtros dinámicos
     */
   public function calendario()
{
    $aulas = Aula::all(); // Trae todas las aulas (A1, B1, etc.)
    $turnos = ['MAÑANA', 'TARDE', 'NOCHE']; // Turnos fijos
    $semana = 1; // Semana por defecto (puedes modificar si necesitas dinámica)

    $calendarios = [];

    foreach ($aulas as $aula) {
        foreach ($turnos as $turno) {
            // Buscar horarios por aula y turno
            $horarios = HorarioDocente::with('docente', 'curso')
                ->where('aula_id', $aula->id)
                ->where('turno', $turno)
                ->get();

            if ($horarios->isNotEmpty()) {
                $calendarios[] = [
                    'aula' => $aula,
                    'turno' => $turno,
                    'semana' => $semana,
                    'horariosSemana' => $this->organizarHorariosPorSemana($horarios)
                ];
            }
        }
    }

    return view('horarios_docentes.calendario', compact('calendarios'));
}


    /**
     * Generar múltiples calendarios para comparación
     */
    public function calendarioMultiple(Request $request)
    {
        $aulas = Aula::all();
        $ciclos = Ciclo::all();
        $turnos = ['MAÑANA', 'TARDE', 'NOCHE'];
        
        $calendarios = [];
        
        // Si se solicitan calendarios específicos
        $aulasSeleccionadas = $request->get('aulas', []);
        $turnosSeleccionados = $request->get('turnos', []);
        $cicloId = $request->get('ciclo_id');
        
        if (!empty($aulasSeleccionadas) && !empty($turnosSeleccionados)) {
            foreach ($aulasSeleccionadas as $aulaId) {
                foreach ($turnosSeleccionados as $turno) {
                    $query = HorarioDocente::with('docente', 'aula', 'ciclo', 'curso')
                        ->where('aula_id', $aulaId)
                        ->where('turno', $turno);
                    
                    if ($cicloId) {
                        $query->where('ciclo_id', $cicloId);
                    }
                    
                    $horarios = $query->get();
                    $aula = Aula::find($aulaId);
                    
                    if ($horarios->isNotEmpty() || $request->get('mostrar_vacios')) {
                        $calendarios[] = [
                            'aula' => $aula,
                            'turno' => $turno,
                            'horarios' => $this->organizarHorariosPorSemana($horarios),
                            'total_clases' => $horarios->count()
                        ];
                    }
                }
            }
        }
        
        return view('horarios_docentes.calendario_multiple', compact(
            'calendarios', 
            'aulas', 
            'ciclos', 
            'turnos',
            'aulasSeleccionadas',
            'turnosSeleccionados',
            'cicloId'
        ));
    }

    /**
     * Validar conflictos de horarios para docentes y aulas - ACTUALIZADO con turno
     */
    private function validateScheduleConflicts(Request $request, $excludeId = null)
    {
        $horaInicio = Carbon::createFromFormat('H:i', $request->hora_inicio);
        $horaFin = Carbon::createFromFormat('H:i', $request->hora_fin);
        
        // 1. Verificar conflicto de DOCENTE en el mismo día/hora/turno
        $conflictoDocente = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('dia_semana', $request->dia_semana)
            ->where('turno', $request->turno)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->where('hora_inicio', '<', $horaFin->format('H:i'))
                      ->where('hora_fin', '>', $horaInicio->format('H:i'));
                });
            })
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists();

        if ($conflictoDocente) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'docente_id' => 'Este docente ya tiene un horario asignado en este día, turno y horario.',
            ]);
        }

        // 2. Verificar conflicto de AULA en el mismo día/hora/turno
        $conflictoAula = HorarioDocente::where('aula_id', $request->aula_id)
            ->where('dia_semana', $request->dia_semana)
            ->where('turno', $request->turno)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->where('hora_inicio', '<', $horaFin->format('H:i'))
                      ->where('hora_fin', '>', $horaInicio->format('H:i'));
                });
            })
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists();

        if ($conflictoAula) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'aula_id' => 'Esta aula ya está ocupada en este día, turno y horario.',
            ]);
        }

        // 3. Verificar límite máximo de horas por docente por día y turno
        $horasTotalesDocente = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('dia_semana', $request->dia_semana)
            ->where('turno', $request->turno)
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->get()
            ->sum(function ($horario) {
                $horaInicioStr = substr($horario->hora_inicio, 0, 5);
                $horaFinStr = substr($horario->hora_fin, 0, 5);
                
                $inicio = Carbon::createFromFormat('H:i', $horaInicioStr);
                $fin = Carbon::createFromFormat('H:i', $horaFinStr);
                return $inicio->diffInMinutes($fin);
            });

        $minutosNuevoHorario = $horaInicio->diffInMinutes($horaFin);
        $totalMinutos = $horasTotalesDocente + $minutosNuevoHorario;

        // Límite: máximo 6 horas (360 minutos) por turno
        if ($totalMinutos > 360) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hora_fin' => 'El docente excedería el límite máximo de 6 horas por turno.',
            ]);
        }
    }

    /**
     * Organizar horarios por semana para la vista calendario
     */
    private function organizarHorariosPorSemana($horarios)
    {
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        
        // Obtener TODOS los bloques de horario únicos
        $bloquesHorario = [];
        foreach ($horarios as $horario) {
            $horaInicio = substr($horario->hora_inicio, 0, 5);
            $horaFin = substr($horario->hora_fin, 0, 5);
            $bloque = $horaInicio . '-' . $horaFin;
            
            if (!isset($bloquesHorario[$bloque])) {
                $bloquesHorario[$bloque] = [
                    'inicio' => $horaInicio,
                    'fin' => $horaFin
                ];
            }
        }
        
        // Ordenar los bloques por hora de inicio
        uksort($bloquesHorario, function($a, $b) {
            $horaA = explode('-', $a)[0];
            $horaB = explode('-', $b)[0];
            return strcmp($horaA, $horaB);
        });

        $horariosSemana = [];
        
        // Inicializar la estructura
        foreach ($bloquesHorario as $bloque => $horario) {
            foreach ($diasSemana as $dia) {
                $horariosSemana[$bloque][$dia] = [];
            }
        }

        // Llenar con los horarios existentes
        foreach ($horarios as $horario) {
            $horaInicio = substr($horario->hora_inicio, 0, 5);
            $horaFin = substr($horario->hora_fin, 0, 5);
            $bloque = $horaInicio . '-' . $horaFin;
            
            $diaHorario = $horario->dia_semana;
            
            if (isset($horariosSemana[$bloque][$diaHorario])) {
                $horariosSemana[$bloque][$diaHorario][] = $horario;
            }
        }

        return [
            'dias' => $diasSemana,
            'bloques' => array_keys($bloquesHorario),
            'horarios' => $horariosSemana
        ];
    }

    /**
     * API para verificar disponibilidad en tiempo real
     */
    public function verificarDisponibilidad(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'aula_id' => 'required|exists:aulas,id',
            'dia_semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);

        try {
            $this->validateScheduleConflicts($request, $request->get('exclude_id'));
            return response()->json(['disponible' => true, 'mensaje' => 'Horario disponible']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'disponible' => false, 
                'errores' => $e->errors()
            ], 422);
        }
    }

    /**
     * Obtener estadísticas de ocupación por aula y turno
     */
    public function estadisticas()
    {
        $estadisticas = [];
        
        $aulas = Aula::all();
        $turnos = ['MAÑANA', 'TARDE', 'NOCHE'];
        
        foreach ($aulas as $aula) {
            $estadisticas[$aula->id] = [
                'aula' => $aula,
                'turnos' => []
            ];
            
            foreach ($turnos as $turno) {
                $totalHoras = HorarioDocente::where('aula_id', $aula->id)
                    ->where('turno', $turno)
                    ->get()
                    ->sum(function ($horario) {
                        $inicio = Carbon::createFromFormat('H:i', substr($horario->hora_inicio, 0, 5));
                        $fin = Carbon::createFromFormat('H:i', substr($horario->hora_fin, 0, 5));
                        return $inicio->diffInHours($fin);
                    });
                
                $totalClases = HorarioDocente::where('aula_id', $aula->id)
                    ->where('turno', $turno)
                    ->count();
                
                $estadisticas[$aula->id]['turnos'][$turno] = [
                    'total_horas' => $totalHoras,
                    'total_clases' => $totalClases,
                    'ocupacion_porcentual' => ($totalHoras / 30) * 100 // Asumiendo 30 horas máximas por semana
                ];
            }
        }
        
        return view('horarios_docentes.estadisticas', compact('estadisticas'));
    }
}