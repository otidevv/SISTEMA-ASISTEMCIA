<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\Inscripcion;
use App\Models\RegistroAsistencia;
use App\Models\Ciclo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Models\PagoDocente;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Turno;
use App\Models\Curso;
use App\Models\Carrera;
use App\Models\Aula;

class DashboardController extends Controller
{
    /**
     * Obtener datos generales del dashboard
     */
    public function getDatosGenerales()
    {
        try {
            $user = Auth::user();

            // Cache datos estáticos por 10 minutos
            $data = Cache::remember('dashboard.datos_generales', 600, function () {
                return [
                    'totalUsuarios' => User::count(),
                    'totalEstudiantes' => User::whereHas('roles', function ($query) {
                        $query->where('nombre', 'estudiante');
                    })->count(),
                    'totalProfesores' => User::whereHas('roles', function ($query) {
                        $query->where('nombre', 'profesor');
                    })->count(),
                    'totalPadres' => User::whereHas('roles', function ($query) {
                        $query->where('nombre', 'padre');
                    })->count()
                ];
            });

            // Datos del usuario (no cacheados)
            $data['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'numero_documento' => $user->numero_documento
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener datos generales'], 500);
        }
    }

    /**
     * Obtener anuncios activos
     */
    public function getAnuncios()
    {
        try {
            $anuncios = Anuncio::select('id', 'titulo', 'contenido', 'fecha_publicacion')
                ->where('es_activo', true)
                ->where(function ($query) {
                    $query->whereNull('fecha_expiracion')
                          ->orWhere('fecha_expiracion', '>', now());
                })
                ->where('fecha_publicacion', '<=', now())
                ->orderBy('fecha_publicacion', 'desc')
                ->take(3) // Reducido a 3 para cargar más rápido
                ->get();

            return response()->json($anuncios);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener anuncios'], 500);
        }
    }

    /**
     * Obtener últimos registros de asistencia
     */
    public function getUltimosRegistros()
    {
        try {
            $registros = RegistroAsistencia::with('usuario')
                ->orderBy('fecha_registro', 'desc')
                ->take(10)
                ->get()
                ->map(function ($registro) {
                    return [
                        'id' => $registro->id,
                        'nro_documento' => $registro->nro_documento,
                        'fecha_registro' => $registro->fecha_registro,
                        'estado' => $registro->estado,
                        'usuario' => $registro->usuario ? [
                            'name' => $registro->usuario->name,
                            'email' => $registro->usuario->email
                        ] : null
                    ];
                });

            return response()->json($registros);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener registros'], 500);
        }
    }

    /**
     * Obtener datos del dashboard administrativo
     */
    public function getDatosAdmin()
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('admin') && !$user->hasPermission('dashboard.admin')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $cicloActivo = Ciclo::where('es_activo', true)->first();

            // Cache datos administrativos por 5 minutos (menos tiempo ya que son más críticos)
            $cacheKey = 'dashboard.admin.' . ($cicloActivo ? $cicloActivo->id : 'no_ciclo');
            $data = Cache::remember($cacheKey, 300, function () use ($cicloActivo) {
                return [
                    'cicloActivo' => $cicloActivo,
                    'totalInscripciones' => $cicloActivo ?
                        Inscripcion::where('ciclo_id', $cicloActivo->id)
                            ->where('estado_inscripcion', 'activo')
                            ->count() : 0,
                    'totalCarreras' => Carrera::where('estado', true)->count(),
                    'totalAulas' => Aula::where('estado', true)->count(),
                    'totalAdministradores' => User::whereHas('roles', function ($query) {
                        $query->where('nombre', 'administrador');
                    })->count(),
                    'totalRoles' => Role::count(),
                    'totalPermisos' => Permission::count(),
                    'totalCiclos' => Ciclo::count(),
                    'totalTurnos' => Turno::where('estado', true)->count(),
                    'totalCursos' => Curso::where('estado', true)->count(),
                    'totalAnuncios' => Anuncio::count(),
                    'totalHorariosDocentes' => HorarioDocente::count(),
                    'totalPagosDocentes' => PagoDocente::count(),
                    'totalAsistenciaDocente' => AsistenciaDocente::count(),
                    'totalInscripcionesGeneral' => Inscripcion::count()
                ];
            });

            // Asistencia de hoy (no cacheada, datos en tiempo real)
            $today = Carbon::today();
            $data['asistenciaHoy'] = [
                'total_registros' => RegistroAsistencia::whereDate('fecha_registro', $today)->count(),
                'presentes' => RegistroAsistencia::whereDate('fecha_registro', $today)
                                ->where('estado', 'presente')
                                ->count(),
                'ausentes' => RegistroAsistencia::whereDate('fecha_registro', $today)
                                ->where('estado', 'ausente')
                                ->count(),
            ];

            // Estadísticas del ciclo activo (cacheadas por 10 minutos)
            if ($cicloActivo) {
                $statsCacheKey = 'dashboard.admin.stats.' . $cicloActivo->id;
                $data['estadisticasAsistencia'] = Cache::remember($statsCacheKey, 600, function () use ($cicloActivo) {
                    return $this->obtenerEstadisticasGenerales($cicloActivo);
                });
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener datos administrativos'], 500);
        }
    }

    /**
     * Obtener datos del dashboard de estudiante
     */
    public function getDatosEstudiante()
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('estudiante')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $inscripcionActiva = Inscripcion::where('estudiante_id', $user->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('ciclo', function ($query) {
                    $query->where('es_activo', true);
                })
                ->with(['ciclo', 'carrera', 'aula', 'turno'])
                ->first();

            $data = [
                'inscripcionActiva' => $inscripcionActiva,
                'infoAsistencia' => []
            ];

            if ($inscripcionActiva) {
                $ciclo = $inscripcionActiva->ciclo;
                
                $primerRegistro = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                    ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
                    ->where('fecha_registro', '<=', $ciclo->fecha_fin)
                    ->orderBy('fecha_registro')
                    ->first();

                if ($primerRegistro) {
                    // Calcular asistencia para cada examen
                    $data['infoAsistencia']['primer_examen'] = $this->calcularAsistenciaExamen(
                        $user->numero_documento,
                        $primerRegistro->fecha_registro,
                        $ciclo->fecha_primer_examen,
                        $ciclo
                    );

                    if ($ciclo->fecha_segundo_examen) {
                        $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen);
                        $data['infoAsistencia']['segundo_examen'] = $this->calcularAsistenciaExamen(
                            $user->numero_documento,
                            $inicioSegundo,
                            $ciclo->fecha_segundo_examen,
                            $ciclo
                        );
                    }

                    if ($ciclo->fecha_tercer_examen && $ciclo->fecha_segundo_examen) {
                        $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen);
                        $data['infoAsistencia']['tercer_examen'] = $this->calcularAsistenciaExamen(
                            $user->numero_documento,
                            $inicioTercero,
                            $ciclo->fecha_tercer_examen,
                            $ciclo
                        );
                    }

                    // Asistencia total del ciclo
                    $data['infoAsistencia']['total_ciclo'] = $this->calcularAsistenciaExamen(
                        $user->numero_documento,
                        $primerRegistro->fecha_registro,
                        min(Carbon::now(), Carbon::parse($ciclo->fecha_fin)),
                        $ciclo
                    );
                }

                $data['primerRegistro'] = $primerRegistro;
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener datos del estudiante'], 500);
        }
    }

    /**
     * Obtener datos del dashboard de profesor
     */
    public function getDatosProfesor(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('profesor')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $fechaSeleccionada = $request->input('fecha') ? 
                Carbon::parse($request->input('fecha')) : Carbon::today();
            
            $diaSemanaSeleccionada = $fechaSeleccionada->locale('es')->dayName;
            
            // Obtener ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            // Obtener horarios del día
            $horariosDelDia = HorarioDocente::where('docente_id', $user->id)
                ->where('dia_semana', $diaSemanaSeleccionada)
                ->with(['aula', 'curso', 'ciclo'])
                ->orderBy('hora_inicio')
                ->get();
            
            // Obtener registros del biométrico
            $registrosDelDia = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaSeleccionada->format('Y-m-d'))
                ->orderBy('fecha_registro')
                ->get();

            // Procesar horarios con detalles
            $horasDelDia = 0;
            $sesionesPendientes = 0;
            $tarifaPorHora = $this->obtenerTarifaDocente($user->id, $cicloActivo);
            
            $horariosConDetalles = $horariosDelDia->map(function ($horario) use ($registrosDelDia, &$horasDelDia, $user, &$sesionesPendientes, $fechaSeleccionada) {
                // Procesar cada horario (simplificado)
                $horaInicio = Carbon::parse($horario->hora_inicio);
                $horaFin = Carbon::parse($horario->hora_fin);
                
                $horarioInicioHoy = $fechaSeleccionada->copy()->setTime($horaInicio->hour, $horaInicio->minute);
                $horarioFinHoy = $fechaSeleccionada->copy()->setTime($horaFin->hour, $horaFin->minute);
                
                // Buscar entrada y salida
                $entrada = $registrosDelDia->filter(function($r) use ($horarioInicioHoy) {
                    $horaRegistro = Carbon::parse($r->fecha_registro);
                    return $horaRegistro->between(
                        $horarioInicioHoy->copy()->subMinutes(15),
                        $horarioInicioHoy->copy()->addMinutes(30)
                    );
                })->first();
                
                $salida = $registrosDelDia->filter(function($r) use ($horarioFinHoy) {
                    $horaRegistro = Carbon::parse($r->fecha_registro);
                    return $horaRegistro->between(
                        $horarioFinHoy->copy()->subMinutes(15),
                        $horarioFinHoy->copy()->addMinutes(60)
                    );
                })->sortByDesc('fecha_registro')->first();
                
                // Calcular horas trabajadas
                if ($entrada && $salida) {
                    $hEntrada = Carbon::parse($entrada->fecha_registro);
                    $hSalida = Carbon::parse($salida->fecha_registro);
                    if ($hSalida->greaterThan($hEntrada)) {
                        $horasDelDia += $hSalida->diffInMinutes($hEntrada) / 60;
                    }
                }
                
                // Buscar asistencia docente
                $asistencia = AsistenciaDocente::where('docente_id', $user->id)
                    ->where('horario_id', $horario->id)
                    ->whereDate('fecha_hora', $fechaSeleccionada->format('Y-m-d'))
                    ->first();
                
                $claseTerminada = Carbon::now()->greaterThan($horarioFinHoy);
                if ($claseTerminada && !$asistencia && $entrada && $salida) {
                    $sesionesPendientes++;
                }
                
                return [
                    'horario' => $horario,
                    'hora_entrada_registrada' => $entrada ? Carbon::parse($entrada->fecha_registro)->format('H:i A') : null,
                    'hora_salida_registrada' => $salida ? Carbon::parse($salida->fecha_registro)->format('H:i A') : null,
                    'asistencia' => $asistencia,
                    'tiene_registros' => $entrada && $salida,
                    'clase_terminada' => $claseTerminada
                ];
            });

            // Calcular métricas
            $pagoEstimadoHoy = $this->calcularPagoEstimado($user->id, $fechaSeleccionada, $horariosDelDia, $tarifaPorHora);
            
            // Resumen semanal
            $resumenSemanal = AsistenciaDocente::where('docente_id', $user->id)
                ->whereBetween('fecha_hora', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
                ->selectRaw('
                    COUNT(*) as total_sesiones,
                    SUM(horas_dictadas) as total_horas,
                    SUM(monto_total) as total_ingresos,
                    AVG(CASE WHEN estado = "completada" THEN 1 ELSE 0 END) * 100 as porcentaje_asistencia
                ')
                ->first();
            
            $data = [
                'fechaSeleccionada' => $fechaSeleccionada->format('Y-m-d'),
                'horariosDelDia' => $horariosConDetalles,
                'horasHoy' => round($horasDelDia, 2),
                'sesionesHoy' => $horariosDelDia->count(),
                'sesionesPendientes' => $sesionesPendientes,
                'tarifaPorHora' => $tarifaPorHora,
                'pagoEstimadoHoy' => $pagoEstimadoHoy,
                'resumenSemanal' => [
                    'sesiones' => $resumenSemanal->total_sesiones ?? 0,
                    'horas' => round($resumenSemanal->total_horas ?? 0, 2),
                    'ingresos' => $resumenSemanal->total_ingresos ?? 0,
                    'asistencia' => round($resumenSemanal->porcentaje_asistencia ?? 0)
                ],
                'proximaClase' => $this->obtenerProximaClase($user->id)
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener datos del profesor: ' . $e->getMessage()], 500);
        }
    }

    // Métodos auxiliares privados
    
    private function obtenerTarifaDocente($docenteId, $cicloActivo = null)
    {
        if (!$cicloActivo) {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
        }

        if ($cicloActivo) {
            $pagoDocente = PagoDocente::where('docente_id', $docenteId)
                ->where('fecha_inicio', '<=', $cicloActivo->fecha_fin)
                ->where(function ($query) use ($cicloActivo) {
                    $query->where('fecha_fin', '>=', $cicloActivo->fecha_inicio)
                          ->orWhereNull('fecha_fin');
                })
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            if ($pagoDocente) {
                return $pagoDocente->tarifa_por_hora;
            }
        }

        $user = User::find($docenteId);
        return $user->tarifa_por_hora ?? 25.00;
    }

    private function calcularPagoEstimado($docenteId, $fechaSeleccionada, $horariosDelDia, $tarifaPorHora)
    {
        $pagoReal = AsistenciaDocente::where('docente_id', $docenteId)
            ->whereDate('fecha_hora', $fechaSeleccionada->format('Y-m-d'))
            ->sum('monto_total');

        if ($pagoReal > 0) {
            return $pagoReal;
        }

        $totalHorasEstimadas = 0;
        foreach ($horariosDelDia as $horario) {
            $horaInicio = Carbon::parse($horario->hora_inicio);
            $horaFin = Carbon::parse($horario->hora_fin);
            $totalHorasEstimadas += $horaInicio->diffInMinutes($horaFin) / 60;
        }

        return $totalHorasEstimadas * $tarifaPorHora;
    }

    private function obtenerProximaClase($docenteId)
    {
        $ahora = Carbon::now();
        $diaActualSemana = $ahora->locale('es')->dayName;

        // Buscar clases de hoy que aún no han comenzado
        $proximaClaseHoy = HorarioDocente::where('docente_id', $docenteId)
            ->where('dia_semana', $diaActualSemana)
            ->where('hora_inicio', '>', $ahora->format('H:i:s'))
            ->with(['aula', 'curso'])
            ->orderBy('hora_inicio')
            ->first();

        if ($proximaClaseHoy) {
            return $proximaClaseHoy;
        }

        // Buscar en los próximos días
        $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        $diaActualIndex = array_search(strtolower($diaActualSemana), $diasSemana);
        
        for ($i = 1; $i <= 7; $i++) {
            $indexDia = ($diaActualIndex + $i) % 7;
            $diaBuscado = $diasSemana[$indexDia];
            
            $claseEncontrada = HorarioDocente::where('docente_id', $docenteId)
                ->where('dia_semana', $diaBuscado)
                ->with(['aula', 'curso'])
                ->orderBy('hora_inicio')
                ->first();
            
            if ($claseEncontrada) {
                return $claseEncontrada;
            }
        }

        return null;
    }

    private function calcularAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now()->startOfDay();
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->startOfDay();

        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy : $fechaExamenCarbon;

        if ($fechaInicioCarbon > $hoy) {
            return [
                'dias_habiles' => 0,
                'dias_asistidos' => 0,
                'dias_falta' => 0,
                'porcentaje_asistencia' => 0,
                'porcentaje_inasistencia' => 0,
                'estado' => 'pendiente',
                'mensaje' => 'Este período aún no ha comenzado.',
                'puede_rendir' => true
            ];
        }

        $diasHabilesTotales = $this->contarDiasHabiles($fechaInicio, $fechaExamen);
        $diasHabilesTranscurridos = $this->contarDiasHabiles($fechaInicio, $fechaFinCalculo);

        $registrosAsistencia = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                $fechaInicioCarbon->startOfDay(),
                $fechaFinCalculo->endOfDay()
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        $diasConAsistencia = 0;
        foreach ($registrosAsistencia as $fecha) {
            $carbonFecha = Carbon::parse($fecha);
            if ($carbonFecha->isWeekday()) {
                $diasConAsistencia++;
            }
        }

        $diasFaltaActuales = $diasHabilesTranscurridos - $diasConAsistencia;
        $porcentajeAsistenciaProyectado = $diasHabilesTotales > 0 ?
            round(($diasConAsistencia / $diasHabilesTotales) * 100, 2) : 0;
        $porcentajeInasistenciaProyectado = 100 - $porcentajeAsistenciaProyectado;

        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

        $estado = 'regular';
        $mensaje = '';
        $puedeRendir = true;

        if ($diasFaltaActuales >= $limiteInhabilitacion) {
            $estado = 'inhabilitado';
            $mensaje = 'Has superado el límite de inasistencias.';
            $puedeRendir = false;
        } elseif ($diasFaltaActuales >= $limiteAmonestacion) {
            $estado = 'amonestado';
            $mensaje = 'Has sido amonestado por inasistencias.';
        } else {
            $mensaje = 'Tu asistencia es regular.';
        }

        return [
            'dias_habiles' => $diasHabilesTotales,
            'dias_habiles_transcurridos' => $diasHabilesTranscurridos,
            'dias_asistidos' => $diasConAsistencia,
            'dias_falta' => $diasFaltaActuales,
            'porcentaje_asistencia' => $porcentajeAsistenciaProyectado,
            'porcentaje_inasistencia' => $porcentajeInasistenciaProyectado,
            'limite_amonestacion' => $limiteAmonestacion,
            'limite_inhabilitacion' => $limiteInhabilitacion,
            'estado' => $estado,
            'mensaje' => $mensaje,
            'puede_rendir' => $puedeRendir
        ];
    }

    private function contarDiasHabiles($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $diasHabiles = 0;

        while ($inicio <= $fin) {
            if ($inicio->isWeekday()) {
                $diasHabiles++;
            }
            $inicio->addDay();
        }

        return $diasHabiles;
    }

    private function getSiguienteDiaHabil($fecha)
    {
        $dia = Carbon::parse($fecha)->addDay();
        while (!$dia->isWeekday()) {
            $dia->addDay();
        }
        return $dia;
    }

    private function obtenerEstadisticasGenerales($ciclo)
    {
        $inscripciones = Inscripcion::where('ciclo_id', $ciclo->id)
            ->where('estado_inscripcion', 'activo')
            ->with('estudiante')
            ->get();

        $totalEstudiantes = $inscripciones->count();
        $estudiantesRegulares = 0;
        $estudiantesAmonestados = 0;
        $estudiantesInhabilitados = 0;

        foreach ($inscripciones as $inscripcion) {
            $estudiante = $inscripcion->estudiante;
            
            $primerRegistro = RegistroAsistencia::where('nro_documento', $estudiante->numero_documento)
                ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
                ->orderBy('fecha_registro')
                ->first();

            if ($primerRegistro) {
                $fechaFin = Carbon::now() < $ciclo->fecha_fin ? Carbon::now() : $ciclo->fecha_fin;
                
                $info = $this->calcularAsistenciaExamen(
                    $estudiante->numero_documento,
                    $primerRegistro->fecha_registro,
                    $fechaFin,
                    $ciclo
                );

                switch ($info['estado']) {
                    case 'regular':
                        $estudiantesRegulares++;
                        break;
                    case 'amonestado':
                        $estudiantesAmonestados++;
                        break;
                    case 'inhabilitado':
                        $estudiantesInhabilitados++;
                        break;
                }
            }
        }

        return [
            'total_estudiantes' => $totalEstudiantes,
            'regulares' => $estudiantesRegulares,
            'amonestados' => $estudiantesAmonestados,
            'inhabilitados' => $estudiantesInhabilitados,
            'porcentaje_regulares' => $totalEstudiantes > 0 ? round(($estudiantesRegulares / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_amonestados' => $totalEstudiantes > 0 ? round(($estudiantesAmonestados / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_inhabilitados' => $totalEstudiantes > 0 ? round(($estudiantesInhabilitados / $totalEstudiantes) * 100, 2) : 0
        ];
    }
}