<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsistenciaDocente;
use App\Models\AsistenciaEvento;
use App\Models\User;
use App\Models\HorarioDocente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Models\RegistroAsistencia; // Asegúrate de que esta línea esté presente

class AsistenciaDocenteController extends Controller
{
    public function __construct()
    {
        // Procesar eventos pendientes cada vez que se carga la página
        Artisan::call('asistencia:procesar-eventos');
    }

    public function reports()
    {
        $totalHoy = AsistenciaDocente::whereDate('fecha_hora', Carbon::today())->count();
        $totalSemana = AsistenciaDocente::whereBetween('fecha_hora', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $totalMes = AsistenciaDocente::whereMonth('fecha_hora', Carbon::now()->month)->count();

        $asistenciaSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $fecha = Carbon::now()->startOfWeek()->addDays($i);
            $asistenciaSemana[$fecha->format('Y-m-d')] = AsistenciaDocente::whereDate('fecha_hora', $fecha)->count();
        }

        $asistenciaPorDocente = AsistenciaDocente::with('docente')
            ->selectRaw('docente_id, COUNT(*) as total_asistencias, SUM(horas_dictadas) as total_horas, SUM(monto_total) as total_pagos')
            ->whereMonth('fecha_hora', Carbon::now()->month)
            ->groupBy('docente_id')
            ->get();

        return view('asistencia-docente.reportes', compact('totalHoy', 'totalSemana', 'totalMes', 'asistenciaSemana', 'asistenciaPorDocente'));
    }

    public function index(Request $request)
    {
        // Obtener parámetros de filtrado
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));
        $documento = $request->get('documento');

        // Obtener números de documento de docentes
        $docentesDocumentos = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->pluck('numero_documento')->toArray();

        // Consulta base filtrando registros de docentes
        $query = RegistroAsistencia::with(['usuario.roles'])
            ->whereIn('nro_documento', $docentesDocumentos);

        // Aplicar filtros
        if ($fecha) {
            $query->where(function ($q) use ($fecha) {
                $q->whereDate('fecha_registro', $fecha)
                    ->orWhere(function ($q2) use ($fecha) {
                        $q2->where('tipo_verificacion', 4) // Manual
                            ->whereDate('fecha_hora', $fecha);
                    });
            });
        }

        if ($documento) {
            $query->where('nro_documento', 'like', '%' . $documento . '%');
        }

        // Obtener registros paginados
        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        // Obtener docentes para filtro
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'numero_documento', 'nombre', 'apellido_paterno')->get();

        // Agregar información adicional a cada registro
        $asistencias->getCollection()->transform(function ($asistencia) {
            if ($asistencia->usuario) {
                $fechaAsistencia = Carbon::parse($asistencia->fecha_hora);
                $diaSemana = $fechaAsistencia->dayOfWeek;

                $diasSemana = [
                    0 => 'Domingo',
                    1 => 'Lunes',
                    2 => 'Martes',
                    3 => 'Miércoles',
                    4 => 'Jueves',
                    5 => 'Viernes',
                    6 => 'Sábado'
                ];

                $nombreDia = $diasSemana[$diaSemana];

                $horario = HorarioDocente::where('docente_id', $asistencia->usuario->id)
                    ->where('dia_semana', $nombreDia)
                    ->whereTime('hora_inicio', '<=', $fechaAsistencia->format('H:i:s'))
                    ->whereTime('hora_fin', '>=', $fechaAsistencia->format('H:i:s'))
                    ->with('curso')
                    ->first();

                $asistencia->horario = $horario;

                if ($horario) {
                    $horaAsistencia = $fechaAsistencia->format('H:i:s');
                    $horaInicio = Carbon::parse($horario->hora_inicio);
                    $horaFin = Carbon::parse($horario->hora_fin);

                    $diffInicio = abs($fechaAsistencia->diffInMinutes($horaInicio));
                    $diffFin = abs($fechaAsistencia->diffInMinutes($horaFin));

                    $asistencia->tipo_asistencia = $diffInicio < $diffFin ? 'entrada' : 'salida';
                } else {
                    $asistencia->tipo_asistencia = $fechaAsistencia->hour < 12 ? 'entrada' : 'salida';
                }
            }

            return $asistencia;
        });

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
        if ($request->has('tema_desarrollado') && !$request->has('estado')) {
            $request->validate([
                'asistencia_id' => 'required|exists:asistencias_docentes,id',
                'tema_desarrollado' => 'required|string|max:500',
            ]);

            $asistencia = AsistenciaDocente::findOrFail($request->asistencia_id);
            $asistencia->update(['tema_desarrollado' => $request->tema_desarrollado]);

            return redirect()->back()->with('success', 'Tema desarrollado actualizado correctamente.');
        }

        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'fecha_hora' => 'required|date',
            'estado' => 'required|in:entrada,salida',
            'tipo_verificacion' => 'nullable|string',
            'terminal_id' => 'nullable|string',
            'codigo_trabajo' => 'nullable|string',
            'tema_desarrollado' => 'required|string',
        ]);

        $fecha = Carbon::parse($request->fecha_hora);
        $diaSemana = strtolower($fecha->locale('es')->dayName);
        $hora = $fecha->format('H:i:s');

        $horario = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('dia_semana', $diaSemana)
            ->whereTime('hora_inicio', '<=', $hora)
            ->whereTime('hora_fin', '>=', $hora)
            ->first();

        if (!$horario) {
            return redirect()->back()->withInput()->withErrors(['horario_id' => 'No existe un horario programado para la fecha y hora seleccionadas.']);
        }

        AsistenciaDocente::updateOrInsert(
            [
                'docente_id' => $request->docente_id,
                'horario_id' => $horario->id,
                'fecha_hora' => $fecha,
                'estado' => $request->estado,
            ],
            [
                'tipo_verificacion' => $request->tipo_verificacion ?? 'manual',
                'tema_desarrollado' => $request->tema_desarrollado,
                'curso_id' => $horario->curso_id,
                'aula_id' => $horario->aula_id,
                'turno' => $horario->turno,
            ]
        );

        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia docente registrada correctamente.');
    }

    private function determinarEstado($tipoVerificacion)
    {
        return 'entrada';
    }

    /**
     * Actualizar solo el tema desarrollado de una asistencia
     */
    public function actualizarTemaDesarrollado(Request $request)
    {
        $request->validate([
            'asistencia_id' => 'required|exists:asistencias_docentes,id',
            'tema_desarrollado' => 'required|string|max:500',
        ]);

        $asistencia = AsistenciaDocente::findOrFail($request->asistencia_id);
        $asistencia->update([
            'tema_desarrollado' => $request->tema_desarrollado,
            'fecha_hora' => now(),
            'estado' => 'entrada',
        ]);

        return redirect()->back()->with('success', 'Tema desarrollado y hora actualizada correctamente.');
    }

    public function editar($id)
    {
        $asistencia = AsistenciaDocente::findOrFail($id);
        return view('asistencia-docente.editar', compact('asistencia'));
    }
}
