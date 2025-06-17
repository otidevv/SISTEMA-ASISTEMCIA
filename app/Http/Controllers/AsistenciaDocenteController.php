<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsistenciaDocente;
use App\Models\AsistenciaEvento;
use App\Models\User;
use App\Models\HorarioDocente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class AsistenciaDocenteController extends Controller
{
    public function __construct()
    {
        // Procesar eventos pendientes cada vez que se carga la página
        Artisan::call('asistencia:procesar-eventos');
    }
    /**
     * Mostrar la lista de registros de asistencia docente.
     */
    public function index(Request $request)
    {
        // Obtener parámetros de filtrado
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));
        $documento = $request->get('documento');

        // Crear la consulta base
        $query = AsistenciaDocente::with(['docente', 'horario.curso']);

        // Aplicar filtros
        if ($fecha) {
            $query->whereDate('fecha_hora', $fecha);
        }

        if ($documento) {
            $query->whereHas('docente', function ($q) use ($documento) {
                $q->where('numero_documento', 'like', '%' . $documento . '%');
            });
        }

        // Obtener registros paginados
        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        // Obtener docentes para el filtro
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'numero_documento', 'nombre', 'apellido_paterno')->get();

        return view('asistencia-docente.index', compact('asistencias', 'docentes', 'fecha', 'documento'));
    }

    /**
     * Muestra la vista de monitoreo en tiempo real de asistencia docente.
     */
    public function monitor()
    {
        // Obtenemos los últimos 10 registros para mostrarlos inicialmente
        $ultimasAsistencias = AsistenciaDocente::with(['docente', 'horario.curso'])
            ->orderBy('fecha_hora', 'desc')
            ->take(10)
            ->get();

        return view('asistencia-docente.monitor', compact('ultimasAsistencias'));
    }

    /**
     * Mostrar el formulario para registrar asistencia docente manualmente.
     */
    public function create()
    {
        // Obtener docentes para el select
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->get();

        return view('asistencia-docente.create', compact('docentes'));
    }

    /**
     * Guardar un nuevo registro de asistencia docente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'fecha_hora' => 'required|date',
            'estado' => 'required|in:entrada,salida',
            'tipo_verificacion' => 'nullable|string',
            'terminal_id' => 'nullable|string',
            'codigo_trabajo' => 'nullable|string',
        ]);

        $fecha = Carbon::parse($request->fecha_hora);
        $diaSemana = strtolower($fecha->locale('es')->dayName);
        $hora = $fecha->format('H:i:s');

        // Buscar horario correspondiente
        $horario = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('dia_semana', $diaSemana)
            ->whereTime('hora_inicio', '<=', $hora)
            ->whereTime('hora_fin', '>=', $hora)
            ->first();

        if (!$horario) {
            return redirect()->back()->withInput()->withErrors(['horario_id' => 'No existe un horario programado para la fecha y hora seleccionadas.']);
        }

        AsistenciaDocente::create([
            'docente_id' => $request->docente_id,
            'horario_id' => $horario->id,
            'fecha_hora' => $fecha,
            'estado' => $request->estado,
            'tipo_verificacion' => $request->tipo_verificacion ?? 'manual',
            'tema_desarrollado' => null,
            'curso_id' => $horario->curso_id,
            'aula_id' => $horario->aula_id,
            'turno' => $horario->turno,
            'hora_entrada' => $horario->hora_inicio,
            'hora_salida' => $horario->hora_fin
        ]);

        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia docente registrada correctamente.');
    }

    /**
     * Mostrar el formulario para editar registros de asistencia docente.
     */
    public function editar(Request $request)
    {
        // Verificar si se han enviado parámetros de búsqueda
        if ($request->has('fecha_desde') || $request->has('fecha_hasta') || $request->has('documento')) {
            // Crear la consulta base
            $query = AsistenciaDocente::with(['docente', 'horario.curso']);

            // Aplicar filtros
            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
            }

            if ($request->has('documento')) {
                $query->whereHas('docente', function ($q) use ($request) {
                    $q->where('numero_documento', 'like', '%' . $request->documento . '%');
                });
            }

            // Obtener registros paginados
            $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);

            return view('asistencia-docente.editar', compact('asistencias'));
        }

        // Si no hay parámetros, mostrar solo el formulario de búsqueda
        return view('asistencia-docente.editar');
    }

    /**
     * Mostrar el formulario para editar un registro específico.
     */
    public function edit($id)
    {
        $asistencia = AsistenciaDocente::with(['docente', 'horario.curso'])->findOrFail($id);
        
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->get();

        return view('asistencia-docente.edit', compact('asistencia', 'docentes'));
    }

    /**
     * Actualizar un registro de asistencia docente.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'fecha_hora' => 'required|date',
            'estado' => 'required|in:entrada,salida',
            'tipo_verificacion' => 'nullable|string',
            'terminal_id' => 'nullable|string',
            'codigo_trabajo' => 'nullable|string',
        ]);

        $asistencia = AsistenciaDocente::findOrFail($id);

        $fecha = Carbon::parse($request->fecha_hora);
        $diaSemana = strtolower($fecha->locale('es')->dayName);
        $hora = $fecha->format('H:i:s');

        // Buscar horario correspondiente
        $horario = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('dia_semana', $diaSemana)
            ->whereTime('hora_inicio', '<=', $hora)
            ->whereTime('hora_fin', '>=', $hora)
            ->first();

        $asistencia->update([
            'docente_id' => $request->docente_id,
            'horario_id' => $horario?->id,
            'fecha_hora' => $fecha,
            'estado' => $request->estado,
            'tipo_verificacion' => $request->tipo_verificacion ?? 'manual',
            'curso_id' => $horario?->curso_id,
            'aula_id' => $horario?->aula_id,
            'turno' => $horario?->turno,
            'hora_entrada' => $horario?->hora_inicio,
            'hora_salida' => $horario?->hora_fin
        ]);

        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia docente actualizada correctamente.');
    }

    /**
     * Eliminar un registro de asistencia docente.
     */
    public function destroy($id)
    {
        $asistencia = AsistenciaDocente::findOrFail($id);
        $asistencia->delete();

        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia docente eliminada correctamente.');
    }

    /**
     * Mostrar el formulario para exportar registros de asistencia docente.
     */
    public function exportar()
    {
        // Obtener docentes para el filtro
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'numero_documento', 'nombre', 'apellido_paterno')->get();

        return view('asistencia-docente.exportar', compact('docentes'));
    }

    /**
     * Exportar registros de asistencia docente según los filtros.
     */
    public function exportarAction(Request $request)
    {
        // Lógica para exportar registros a Excel o CSV
        // Aquí puedes usar paquetes como maatwebsite/excel

        return redirect()->route('asistencia-docente.exportar')->with('success', 'Registros de asistencia docente exportados exitosamente.');
    }

    /**
     * Mostrar los reportes y estadísticas de asistencia docente.
     */
    public function reports()
    {
        // Obtener datos para estadísticas
        $totalHoy = AsistenciaDocente::whereDate('fecha_hora', Carbon::today())->count();
        $totalSemana = AsistenciaDocente::whereBetween('fecha_hora', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $totalMes = AsistenciaDocente::whereMonth('fecha_hora', Carbon::now()->month)->count();

        // Gráfico de asistencia por día de la semana actual
        $asistenciaSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $fecha = Carbon::now()->startOfWeek()->addDays($i);
            $asistenciaSemana[$fecha->format('Y-m-d')] = AsistenciaDocente::whereDate('fecha_hora', $fecha)->count();
        }

        // Estadísticas por docente
        $asistenciaPorDocente = AsistenciaDocente::with('docente')
            ->selectRaw('docente_id, COUNT(*) as total_asistencias, SUM(horas_dictadas) as total_horas, SUM(monto_total) as total_pagos')
            ->whereMonth('fecha_hora', Carbon::now()->month)
            ->groupBy('docente_id')
            ->get();

        return view('asistencia-docente.reportes', compact('totalHoy', 'totalSemana', 'totalMes', 'asistenciaSemana', 'asistenciaPorDocente'));
    }

    /**
     * API para obtener últimas asistencias procesadas (para tiempo real)
     */
    public function ultimasProcesadas(Request $request)
    {
        // Obtener la marca de tiempo de la última consulta
        $ultimaConsulta = $request->input('ultima_consulta', 0);

        // Buscar asistencias recientes
        $asistenciasRecientes = AsistenciaDocente::with(['docente', 'horario.curso'])
            ->where('updated_at', '>', Carbon::createFromTimestamp($ultimaConsulta))
            ->orderBy('id', 'asc')
            ->take(5)
            ->get();

        // Si no hay asistencias recientes, retornar una respuesta vacía
        if ($asistenciasRecientes->isEmpty()) {
            return response()->json([
                'success' => true,
                'tiene_nuevos' => false,
                'hora_actual' => now()->timestamp,
                'registros' => []
            ]);
        }

        // Formatear los registros
        $registros = [];
        foreach ($asistenciasRecientes as $asistencia) {
            $registros[] = [
                'id' => $asistencia->id,
                'docente_nombre' => $asistencia->docente ? 
                    $asistencia->docente->nombre . ' ' . $asistencia->docente->apellido_paterno : 
                    'N/A',
                'numero_documento' => $asistencia->docente ? $asistencia->docente->numero_documento : 'N/A',
                'fecha_hora_formateada' => $asistencia->fecha_hora->format('d/m/Y H:i:s'),
                'estado' => $asistencia->estado,
                'curso' => $asistencia->horario && $asistencia->horario->curso ? 
                    $asistencia->horario->curso->nombre : 'N/A',
                'tipo_verificacion' => $asistencia->tipo_verificacion,
                'foto_url' => $asistencia->docente && $asistencia->docente->foto_perfil ?
                    asset('storage/' . $asistencia->docente->foto_perfil) : null,
                'iniciales' => $asistencia->docente ?
                    strtoupper(substr($asistencia->docente->nombre, 0, 1)) : 'N/A',
            ];
        }

        return response()->json([
            'success' => true,
            'tiene_nuevos' => true,
            'hora_actual' => now()->timestamp,
            'registros' => $registros
        ]);
    }

    /**
     * Procesar eventos del biométrico específicos para docentes
     */
    public function procesarEventosBiometrico()
    {
        // Procesar eventos pendientes del biométrico para docentes
        $eventos = AsistenciaEvento::where('procesado', false)
            ->whereHas('usuario', function($query) {
                $query->whereHas('roles', function($q) {
                    $q->where('nombre', 'profesor');
                });
            })
            ->orderBy('id', 'asc')
            ->get();

        $eventosProcessados = 0;

        foreach ($eventos as $evento) {
            // Buscar el docente por número de documento
            $docente = User::whereHas('roles', function($query) {
                $query->where('nombre', 'profesor');
            })->where('numero_documento', $evento->nro_documento)->first();

            if ($docente) {
                $fecha = Carbon::parse($evento->fecha_hora);
                $diaSemana = strtolower($fecha->locale('es')->dayName);
                $hora = $fecha->format('H:i:s');

                // Buscar horario correspondiente
                $horario = HorarioDocente::where('docente_id', $docente->id)
                    ->where('dia_semana', $diaSemana)
                    ->whereTime('hora_inicio', '<=', $hora)
                    ->whereTime('hora_fin', '>=', $hora)
                    ->first();

                // Crear registro de asistencia docente
                $fechaEvento = Carbon::parse($evento->fecha_hora);
                AsistenciaDocente::create([
                    'docente_id' => $docente->id,
                    'horario_id' => $horario?->id,
                    'fecha_hora' => $fechaEvento,
                    'estado' => $this->determinarEstado($evento->tipo_verificacion),
                    'tipo_verificacion' => 'biometrico',
                    'terminal_id' => $evento->terminal_id ?? 'BIOMETRICO',
                    'codigo_trabajo' => $evento->codigo_trabajo,
                    'curso_id' => $horario?->curso_id,
                    'aula_id' => $horario?->aula_id,
                    'turno' => $horario?->turno,
                    'hora_entrada' => $horario?->hora_inicio,
                    'hora_salida' => $horario?->hora_fin
                ]);

                // Marcar evento como procesado
                $evento->update(['procesado' => true]);
                $eventosProcessados++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Eventos de docentes procesados exitosamente',
            'eventos_procesados' => $eventosProcessados
        ]);
    }

    /**
     * Determinar el estado (entrada/salida) basado en el tipo de verificación
     */
    private function determinarEstado($tipoVerificacion)
    {
        // Lógica para determinar si es entrada o salida
        // Esto puede variar según la configuración del biométrico
        // Por defecto, asumimos que es entrada
        return 'entrada';
    }
}
