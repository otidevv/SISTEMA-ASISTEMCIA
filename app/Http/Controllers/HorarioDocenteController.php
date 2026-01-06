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
    public function index(Request $request)
    {
        // 1. Obtener todos los ciclos para el selector
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();

        // 2. Determinar el ciclo a mostrar
        $cicloSeleccionadoId = $request->input('ciclo_id');
        $cicloActivo = $ciclos->firstWhere('es_activo', true);

        if ($cicloSeleccionadoId) {
            $cicloSeleccionado = $ciclos->find($cicloSeleccionadoId);
        } else {
            $cicloSeleccionado = $cicloActivo;
        }

        // 3. Construir la consulta de horarios
        $query = HorarioDocente::with('docente', 'aula', 'ciclo', 'curso');

        // 4. Filtrar por el ciclo seleccionado (si existe)
        if ($cicloSeleccionado) {
            $query->where('ciclo_id', $cicloSeleccionado->id);
        }

        $horarios = $query->paginate(10);

        // 5. Pasar los datos a la vista
        return view('horarios_docentes.index', compact('horarios', 'ciclos', 'cicloSeleccionado'));
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

        $horario = HorarioDocente::create($validatedData);

        return redirect()->route('horarios-docentes.index', ['ciclo_id' => $horario->ciclo_id])
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

        return redirect()->route('horarios-docentes.index', ['ciclo_id' => $horario->ciclo_id])
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
    // Obtener el ciclo activo
    $cicloActivo = Ciclo::where('es_activo', true)->first();

    if (!$cicloActivo) {
        // Si no hay ciclo activo, puedes redirigir o mostrar un mensaje
        return redirect()->route('horarios-docentes.index')->with('error', 'No hay un ciclo activo para mostrar el calendario.');
    }

    $aulas = Aula::all();
    $turnos = ['MAÑANA', 'TARDE', 'NOCHE'];
    $semana = 1; // Semana por defecto

    $calendarios = [];

    foreach ($aulas as $aula) {
        foreach ($turnos as $turno) {
            // Buscar horarios por aula, turno y ciclo activo
            $horarios = HorarioDocente::with('docente', 'curso')
                ->where('ciclo_id', $cicloActivo->id)
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

    // Pasar también el ciclo activo a la vista para mostrar su nombre
    return view('horarios_docentes.calendario', compact('calendarios', 'cicloActivo'));
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

        // 1. Verificar conflicto de DOCENTE en el mismo día/hora/turno y ciclo
        $conflictoDocente = HorarioDocente::with('aula', 'curso')
            ->where('docente_id', $request->docente_id)
            ->where('ciclo_id', $request->ciclo_id)
            ->where('dia_semana', $request->dia_semana)
            ->where('turno', $request->turno)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->whereTime('hora_inicio', '<', $horaFin)
                      ->whereTime('hora_fin', '>', $horaInicio);
                });
            })
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->first();

        if ($conflictoDocente) {
            $message = sprintf(
                'Este docente ya tiene un horario asignado en el Aula %s con el curso %s de %s a %s (ID de horario: %d).',
                $conflictoDocente->aula->nombre ?? 'N/A',
                $conflictoDocente->curso->nombre ?? 'N/A',
                Carbon::parse($conflictoDocente->hora_inicio)->format('H:i'),
                Carbon::parse($conflictoDocente->hora_fin)->format('H:i'),
                $conflictoDocente->id
            );
            throw \Illuminate\Validation\ValidationException::withMessages([
                'docente_id' => $message,
            ]);
        }

        // 2. Verificar conflicto de AULA en el mismo día/hora/turno y ciclo
        $conflictoAula = HorarioDocente::with('docente', 'curso')
            ->where('aula_id', $request->aula_id)
            ->where('ciclo_id', $request->ciclo_id)
            ->where('dia_semana', $request->dia_semana)
            ->where('turno', $request->turno)
            ->where(function ($query) use ($horaInicio, $horaFin) {
                $query->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->whereTime('hora_inicio', '<', $horaFin)
                      ->whereTime('hora_fin', '>', $horaInicio);
                });
            })
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->first();

        if ($conflictoAula) {
            $message = sprintf(
                'El aula ya está ocupada por el docente %s con el curso %s en el horario de %s a %s (ID de horario: %d).',
                $conflictoAula->docente->name ?? 'N/A',
                $conflictoAula->curso->nombre ?? 'N/A',
                Carbon::parse($conflictoAula->hora_inicio)->format('H:i'),
                Carbon::parse($conflictoAula->hora_fin)->format('H:i'),
                $conflictoAula->id
            );
            throw \Illuminate\Validation\ValidationException::withMessages([
                'aula_id' => $message,
            ]);
        }

        // 3. Verificar límite máximo de horas por docente por día y turno en el mismo ciclo
        $horasTotalesDocente = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('ciclo_id', $request->ciclo_id)
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
                'hora_fin' => 'El docente excedería el límite máximo de 6 horas por turno en este ciclo.',
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

    /**
     * Vista de grilla visual interactiva para crear horarios masivamente
     */
    public function grid(Request $request)
    {
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        $cicloActivo = $ciclos->firstWhere('es_activo', true);
        
        $cicloSeleccionadoId = $request->input('ciclo_id', $cicloActivo?->id);
        $cicloSeleccionado = $ciclos->find($cicloSeleccionadoId) ?? $cicloActivo;
        
        $aulas = Aula::all();
        $turnos = ['MAÑANA', 'TARDE', 'NOCHE'];
        $cursos = Curso::where('estado', true)->get();
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->get();
        
        // Obtener aula y turno seleccionados
        $aulaSeleccionadaId = $request->input('aula_id', $aulas->first()?->id);
        $turnoSeleccionado = $request->input('turno', 'MAÑANA');
        
        return view('horarios_docentes.grid', compact(
            'ciclos',
            'cicloSeleccionado',
            'aulas',
            'turnos',
            'cursos',
            'docentes',
            'aulaSeleccionadaId',
            'turnoSeleccionado'
        ));
    }

    /**
     * API: Obtener horarios para la grilla visual
     */
    public function getSchedules(Request $request)
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);

        $horarios = HorarioDocente::with(['docente', 'curso', 'aula'])
            ->where('ciclo_id', $request->ciclo_id)
            ->where('aula_id', $request->aula_id)
            ->where('turno', $request->turno)
            ->get()
            ->map(function ($horario) {
                return [
                    'id' => $horario->id,
                    'docente_id' => $horario->docente_id,
                    'docente_nombre' => $horario->docente?->nombre_completo ?? 'Sin asignar',
                    'curso_id' => $horario->curso_id,
                    'curso_nombre' => $horario->curso?->nombre ?? 'Sin curso',
                    'curso_color' => $horario->curso?->color ?? '#7367f0',
                    'dia_semana' => $horario->dia_semana,
                    'hora_inicio' => substr($horario->hora_inicio, 0, 5),
                    'hora_fin' => substr($horario->hora_fin, 0, 5),
                    'grupo' => $horario->grupo,
                ];
            });

        return response()->json($horarios);
    }

    /**
     * Guardar múltiples horarios de forma masiva
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'horarios' => 'required|array',
            'horarios.*.docente_id' => 'nullable|exists:users,id', // Cambiado a nullable para recesos
            'horarios.*.curso_id' => 'nullable|exists:cursos,id', // Cambiado a nullable para recesos
            'horarios.*.aula_id' => 'required|exists:aulas,id',
            'horarios.*.ciclo_id' => 'required|exists:ciclos,id',
            'horarios.*.dia_semana' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'horarios.*.hora_inicio' => 'required|date_format:H:i',
            'horarios.*.hora_fin' => 'required|date_format:H:i',
            'horarios.*.turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);

        $horariosCreados = [];
        $errores = [];

        \DB::beginTransaction();
        try {
            foreach ($request->horarios as $index => $horarioData) {
                try {
                    // Solo validar conflictos si tiene docente (no es receso)
                    if (!empty($horarioData['docente_id'])) {
                        $tempRequest = new Request($horarioData);
                        $this->validateScheduleConflicts($tempRequest);
                    }
                    
                    $horario = HorarioDocente::create([
                        'docente_id' => $horarioData['docente_id'] ?? null,
                        'curso_id' => $horarioData['curso_id'],
                        'aula_id' => $horarioData['aula_id'],
                        'ciclo_id' => $horarioData['ciclo_id'],
                        'dia_semana' => $horarioData['dia_semana'],
                        'hora_inicio' => $horarioData['hora_inicio'],
                        'hora_fin' => $horarioData['hora_fin'],
                        'turno' => $horarioData['turno'],
                        'grupo' => $horarioData['grupo'] ?? null,
                    ]);
                    
                    $horariosCreados[] = $horario->id;
                } catch (\Illuminate\Validation\ValidationException $e) {
                    $errores[] = [
                        'index' => $index,
                        'errores' => $e->errors()
                    ];
                }
            }

            if (!empty($errores)) {
                \DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Se encontraron conflictos en algunos horarios',
                    'errores' => $errores
                ], 422);
            }

            \DB::commit();
            return response()->json([
                'success' => true,
                'message' => count($horariosCreados) . ' horarios creados exitosamente',
                'horarios_creados' => $horariosCreados
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los horarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar horario a PDF
     */
    public function exportPDF(Request $request)
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
            'turno' => 'required|in:MAÑANA,TARDE,NOCHE',
        ]);

        $ciclo = Ciclo::find($request->ciclo_id);
        $aula = Aula::find($request->aula_id);
        $turno = $request->turno;

        $horarios = HorarioDocente::with(['docente', 'curso'])
            ->where('ciclo_id', $request->ciclo_id)
            ->where('aula_id', $request->aula_id)
            ->where('turno', $request->turno)
            ->get();

        // Organizar horarios por día y hora
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $horasRango = $this->obtenerRangoHoras($horarios);
        
        $grilla = [];
        foreach ($horasRango as $hora) {
            $fila = ['hora' => $hora];
            foreach ($dias as $dia) {
                $fila[$dia] = $this->obtenerHorarioEnCelda($horarios, $dia, $hora);
            }
            $grilla[] = $fila;
        }

        $pdf = \PDF::loadView('horarios_docentes.pdf', [
            'ciclo' => $ciclo,
            'aula' => $aula,
            'turno' => $turno,
            'grilla' => $grilla,
            'dias' => $dias
        ]);

        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download("Horario_{$aula->nombre}_{$turno}_{$ciclo->nombre}.pdf");
    }

    /**
     * Obtener rango de horas de los horarios
     */
    private function obtenerRangoHoras($horarios)
    {
        $horas = [];
        foreach ($horarios as $horario) {
            $inicio = Carbon::createFromFormat('H:i', substr($horario->hora_inicio, 0, 5));
            $fin = Carbon::createFromFormat('H:i', substr($horario->hora_fin, 0, 5));
            
            while ($inicio < $fin) {
                $horaStr = $inicio->format('H:i');
                if (!in_array($horaStr, $horas)) {
                    $horas[] = $horaStr;
                }
                $inicio->addHour();
            }
        }
        
        sort($horas);
        return $horas;
    }

    /**
     * Obtener horario en una celda específica
     */
    private function obtenerHorarioEnCelda($horarios, $dia, $hora)
    {
        foreach ($horarios as $horario) {
            if ($horario->dia_semana === $dia) {
                $inicio = Carbon::createFromFormat('H:i', substr($horario->hora_inicio, 0, 5));
                $fin = Carbon::createFromFormat('H:i', substr($horario->hora_fin, 0, 5));
                $horaActual = Carbon::createFromFormat('H:i', $hora);
                
                if ($horaActual >= $inicio && $horaActual < $fin) {
                    return $horario;
                }
            }
        }
        return null;
    }
}