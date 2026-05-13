<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HorarioDocente;
use App\Models\User;
use App\Models\Ciclo;
use App\Models\Aula;
use App\Models\Curso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class HorarioDocenteController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener todos los ciclos para el selector
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();

        // 2. Determinar el ciclo a mostrar
        $cicloSeleccionadoId = $request->input('ciclo_id');
        $cicloActivo = $ciclos->where('es_activo', true)->where('programa_id', 1)->first() 
            ?? $ciclos->firstWhere('es_activo', true);

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

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Horario actualizado correctamente.',
                'horario_id' => $horario->id
            ]);
        }

        return redirect()->route('horarios-docentes.index', ['ciclo_id' => $horario->ciclo_id])
            ->with('success', 'Horario actualizado correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        $horario = HorarioDocente::findOrFail($id);
        $horario->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Horario eliminado exitosamente.']);
        }

        return back()->with('success', 'Horario eliminado exitosamente.');
    }

    /**
     * Vista de tabla semanal como calendario - MEJORADO con filtros dinámicos
     */
   public function calendario()
{
    // Obtener el ciclo activo (Priorizamos CEPRE por defecto para el calendario general)
    $cicloActivo = Ciclo::where('es_activo', true)->where('programa_id', 1)->first()
        ?? Ciclo::where('es_activo', true)->first();

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
            $horasActuales = round($horasTotalesDocente / 60, 1);
            $horasNuevas = round($minutosNuevoHorario / 60, 1);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'hora_fin' => "El docente ya tiene {$horasActuales} horas en este turno. Sumando las {$horasNuevas} horas nuevas, excedería el límite máximo de 6 horas por turno en este ciclo.",
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
     * Obtener estadísticas de ocupación por aula y turno (Optimizado para Performance N+1)
     */
    public function estadisticas()
    {
        $estadisticas = [];
        
        $aulas = Aula::all();
        $turnos = ['MAÑANA', 'TARDE', 'NOCHE'];
        
        // 1. Cargar la matriz entera en 1 sola consulta (Adiós N+1)
        $horarios = HorarioDocente::select('aula_id', 'turno', 'hora_inicio', 'hora_fin')->get();
        
        // 2. Pre-agrupar en memoria RAM en tiempo O(1)
        $agrupados = [];
        foreach ($horarios as $h) {
            $inicio = Carbon::createFromFormat('H:i', substr($h->hora_inicio, 0, 5));
            $fin = Carbon::createFromFormat('H:i', substr($h->hora_fin, 0, 5));
            // Cálculo decimal de fracción temporal (ej. 1.5 horas = 1hr 30m)
            $horasDif = $inicio->diffInMinutes($fin) / 60.0;
            
            if (!isset($agrupados[$h->aula_id])) {
                $agrupados[$h->aula_id] = [];
            }
            if (!isset($agrupados[$h->aula_id][$h->turno])) {
                $agrupados[$h->aula_id][$h->turno] = ['total_horas' => 0, 'total_clases' => 0];
            }
            
            $agrupados[$h->aula_id][$h->turno]['total_horas'] += $horasDif;
            $agrupados[$h->aula_id][$h->turno]['total_clases'] += 1;
        }
        
        // 3. Cruzar los datos instantáneamente sin tocar la Base de Datos
        foreach ($aulas as $aula) {
            $estadisticas[$aula->id] = [
                'aula' => $aula,
                'turnos' => []
            ];
            
            foreach ($turnos as $turno) {
                // Recuperación en microsegundos
                $datos = isset($agrupados[$aula->id][$turno]) ? $agrupados[$aula->id][$turno] : ['total_horas' => 0, 'total_clases' => 0];
                
                $totalHoras = $datos['total_horas'];
                $totalClases = $datos['total_clases'];
                
                $estadisticas[$aula->id]['turnos'][$turno] = [
                    'total_horas' => $totalHoras,
                    'total_clases' => $totalClases,
                    'ocupacion_porcentual' => ($totalHoras / 30) * 100 // Asumiendo 30 horas académicas semanales
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
        $cicloActivo = $ciclos->where('es_activo', true)->where('programa_id', 1)->first()
            ?? $ciclos->firstWhere('es_activo', true);
        
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

        // Obtener horarios para determinar los slots dinámicos
        $horarios = HorarioDocente::where('ciclo_id', $cicloSeleccionadoId)
            ->where('aula_id', $aulaSeleccionadaId)
            ->where('turno', $turnoSeleccionado)
            ->get();
        
        $slotsRaw = $this->obtenerRangoHoras($horarios, $cicloSeleccionado, $turnoSeleccionado);
        
        // Convertir slotsRaw a formato estructurado para la vista
        $slots = [];
        $recesoMananaInicio = $cicloSeleccionado ? $this->parseToMinutes($cicloSeleccionado->receso_manana_inicio) : -1;
        $recesoTardeInicio = $cicloSeleccionado ? $this->parseToMinutes($cicloSeleccionado->receso_tarde_inicio) : -1;

        foreach ($slotsRaw as $s) {
            $partes = explode(' - ', $s);
            $inicioMin = $this->parseToMinutes($partes[0]);
            $finMin = $this->parseToMinutes($partes[1]);
            $slots[] = [
                'inicio' => $partes[0],
                'fin' => $partes[1],
                'minutos' => ($finMin - $inicioMin),
                'es_receso' => ($inicioMin === $recesoMananaInicio || $inicioMin === $recesoTardeInicio)
            ];
        }
        
        return view('horarios_docentes.grid', compact(
            'ciclos',
            'cicloSeleccionado',
            'aulas',
            'turnos',
            'cursos',
            'docentes',
            'aulaSeleccionadaId',
            'turnoSeleccionado',
            'slots'
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
                    'aula_id' => $horario->aula_id,
                    'ciclo_id' => $horario->ciclo_id,
                    'turno' => $horario->turno,
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
        $diasBase = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        
        // Verificar si hay clases el sábado
        $hayClasesSabado = $horarios->contains('dia_semana', 'Sábado');
        $dias = $hayClasesSabado ? $diasBase : ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        
        $horasRango = $this->obtenerRangoHoras($horarios, $ciclo, $turno);
        
        $grilla = [];
        foreach ($horasRango as $index => $hora) {
            $fila = ['hora' => $hora];
            foreach ($dias as $dia) {
                $fila[$dia] = $this->obtenerHorarioEnCelda($horarios, $dia, $hora);
            }
            $grilla[] = $fila;
        }

        // Lógica de Rowspan para el PDF
        $rowspans = [];
        foreach ($dias as $dia) {
            $skip = 0;
            foreach ($grilla as $index => $fila) {
                if ($skip > 0) {
                    $rowspans[$index][$dia] = 0; // Celda a saltar
                    $skip--;
                    continue;
                }
                
                $horario = $fila[$dia];
                if ($horario) {
                    // Contar cuántas filas ocupa este horario
                    $count = 1;
                    $currentIdx = $index + 1;
                    while ($currentIdx < count($grilla)) {
                        $nextHorario = $grilla[$currentIdx][$dia];
                        if ($nextHorario && $nextHorario->id === $horario->id) {
                            $count++;
                            $currentIdx++;
                        } else {
                            break;
                        }
                    }
                    $rowspans[$index][$dia] = $count;
                    $skip = $count - 1;
                } else {
                    $rowspans[$index][$dia] = 1;
                }
            }
        }

        $urlValidacion = route('publico.validar_horario', ['id' => $aula->id, 'ciclo' => $ciclo->id, 'tipo' => 'aula']);
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($urlValidacion));

        return Pdf::loadView('horarios_docentes.pdf', [
            'ciclo' => $ciclo,
            'aula' => $aula,
            'turno' => $turno,
            'grilla' => $grilla,
            'rowspans' => $rowspans,
            'dias' => $dias,
            'qrCode' => $qrCode,
            'hayClasesSabado' => $hayClasesSabado
        ])->setPaper('A4', 'landscape')
          ->download("Horario_{$aula->nombre}_{$turno}_{$ciclo->nombre}.pdf");
    }

    /**
     * Obtener rango de horas de los horarios Dinámicamente
     */
    private function obtenerRangoHoras($horarios, $ciclo = null, $turno = 'MAÑANA')
    {
        $timePoints = [];
        
        $pointsInteres = [];
        
        // 1. Recolectar puntos de interés (Horarios existentes)
        foreach ($horarios as $horario) {
            $pointsInteres[] = $this->parseToMinutes($horario->hora_inicio);
            $pointsInteres[] = $this->parseToMinutes($horario->hora_fin);
        }

        // 2. Puntos de recesos del ciclo (Solo si corresponden al turno)
        $turnoActual = strtoupper(trim((string)$turno));
        if ($ciclo) {
            if ($turnoActual === 'MAÑANA') {
                if ($ciclo->receso_manana_inicio) $pointsInteres[] = $this->parseToMinutes($ciclo->receso_manana_inicio);
                if ($ciclo->receso_manana_fin)    $pointsInteres[] = $this->parseToMinutes($ciclo->receso_manana_fin);
            } else {
                if ($ciclo->receso_tarde_inicio)  $pointsInteres[] = $this->parseToMinutes($ciclo->receso_tarde_inicio);
                if ($ciclo->receso_tarde_fin)     $pointsInteres[] = $this->parseToMinutes($ciclo->receso_tarde_fin);
            }
        }

        // 3. Determinar Límites (Dinamismo solicitado: si hay clases, recortar al primer y último evento)
        if (!empty($pointsInteres)) {
            $timePoints[] = min($pointsInteres);
            $timePoints[] = max($pointsInteres);
        } else {
            // Si no hay clases, usar límites por defecto del turno
            if ($turnoActual === 'MAÑANA') {
                $timePoints[] = 7 * 60;   // 07:00
                $timePoints[] = 13.5 * 60; // 13:30
            } elseif ($turnoActual === 'TARDE') {
                $timePoints[] = 15 * 60;   // 15:00
                $timePoints[] = 22 * 60;
            } else {
                $timePoints[] = 18 * 60;
                $timePoints[] = 22 * 60;
            }
        }

        // 4. Agregar todos los puntos intermedios
        foreach ($pointsInteres as $p) {
            $timePoints[] = $p;
        }

        // 4. Limpiar y filtrar
        $timePoints = array_unique(array_filter($timePoints, fn($p) => $p !== null && $p >= 0));
        sort($timePoints);

        // 4b. SNAPPING: Unificamos puntos muy cercanos para que la grilla sea limpia y alineada
        $snappedPoints = [];
        if (count($timePoints) > 0) {
            $snappedPoints[] = $timePoints[0];
            for ($i = 1; $i < count($timePoints); $i++) {
                $last = end($snappedPoints);
                // Si la diferencia es menor a 10 min, unificamos al punto anterior
                if ($timePoints[$i] - $last < 10) {
                    continue;
                }
                $snappedPoints[] = $timePoints[$i];
            }
        }
        $timePoints = $snappedPoints;

        // 5. Generar slots (con subdivisiones de máximo 1 hora para estética)
        $slots = [];
        for ($i = 0; $i < count($timePoints) - 1; $i++) {
            $start = $timePoints[$i];
            $end = $timePoints[$i + 1];
            
            // Si la brecha es de más de 60 mins, subdividir
            $curr = $start;
            while ($curr < $end) {
                $next = min($curr + 60, $end);
                
                // Si el remanente es muy pequeño (menos de 15 min), lo absorbemos
                if ($end - $next < 15) {
                    $next = $end;
                }
                
                // Evitar micro-slots (ej. de 1 minuto) si es posible, pero mantener exactitud para boundaries
                // Si la diferencia es de 1-5 mins y estamos subdividiendo una brecha grande, ajustamos.
                // Pero si 'end' es un punto oficial (horario), DEBEMOS respetarlo.
                
                $slots[] = sprintf('%02d:%02d', floor($curr/60), $curr % 60) . ' - ' . sprintf('%02d:%02d', floor($next/60), $next % 60);
                $curr = $next;
            }
        }
        
        return array_unique($slots);
    }

    /**
     * Helper para convertir HH:mm:ss a minutos
     */
    private function parseToMinutes($time)
    {
        if (!$time) return null;
        $parts = explode(':', $time);
        return (int)$parts[0] * 60 + (int)$parts[1];
    }

    /**
     * Obtener horario en una celda específica
     */
    private function obtenerHorarioEnCelda($horarios, $dia, $horaRango)
    {
        // Parsear el rango de hora (ej. "10:00 - 10:30")
        $partes = explode(' - ', $horaRango);
        $horaSlotInicio = $this->parseToMinutes(trim($partes[0]));
        
        foreach ($horarios as $horario) {
            if ($horario->dia_semana === $dia) {
                $inicio = $this->parseToMinutes($horario->hora_inicio);
                $fin = $this->parseToMinutes($horario->hora_fin);
                
                // Verificar si el slot de la grilla empieza justo o dentro de este horario
                if ($horaSlotInicio >= $inicio && $horaSlotInicio < $fin) {
                    return $horario;
                }
            }
        }
        return null;
    }
}
