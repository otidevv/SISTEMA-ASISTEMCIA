<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\Inscripcion;
use App\Models\RegistroAsistencia;
use App\Models\Ciclo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Models\PagoDocente; // NUEVO: Importar modelo PagoDocente
use App\Models\User; // Importar modelo User
use App\Models\Role; // Importar modelo Role
use App\Models\Permission; // Importar modelo Permission
use App\Models\Turno; // Importar modelo Turno
use App\Models\Curso; // Importar modelo Curso
use App\Models\Carrera; // ← AGREGAR ESTA LÍNEA
use App\Models\Aula; 

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal basado en el rol del usuario.
     * Permite a los profesores seleccionar una fecha para ver sus horarios y temas.
     *
     * @param Request $request La solicitud HTTP.
     * @return \Illuminate\View\View La vista del dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = [];

        // OBTENER ANUNCIOS ACTIVOS PARA TODOS
        $data['anuncios'] = Anuncio::where('es_activo', true)
            ->where(function ($query) {
                $query->whereNull('fecha_expiracion')
                      ->orWhere('fecha_expiracion', '>', now());
            })
            ->where('fecha_publicacion', '<=', now())
            ->orderBy('fecha_publicacion', 'desc')
            ->take(5) // Limitar a los 5 más recientes
            ->get();

        // Información común para todos los usuarios
        $data['user'] = $user;
        $data['totalUsuarios'] = User::count();
        $data['totalEstudiantes'] = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'estudiante');
        })->count();
        $data['totalProfesores'] = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->count();
        $data['totalPadres'] = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'padre');
        })->count();

        $data['ultimosRegistrosAsistencia'] = RegistroAsistencia::with('usuario')
        ->orderBy('fecha_registro', 'desc')
        ->take(10)
        ->get();

        // Si el usuario es un estudiante
        if ($user->hasRole('estudiante')) {
            // Obtener inscripción activa del estudiante
            $inscripcionActiva = Inscripcion::where('estudiante_id', $user->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('ciclo', function ($query) {
                    $query->where('es_activo', true);
                })
                ->with(['ciclo', 'carrera', 'aula', 'turno'])
                ->first();

            if ($inscripcionActiva) {
                $ciclo = $inscripcionActiva->ciclo;

                // Obtener el primer registro de asistencia del estudiante dentro del ciclo
                $primerRegistro = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                    ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
                    ->where('fecha_registro', '<=', $ciclo->fecha_fin)
                    ->orderBy('fecha_registro')
                    ->first();

                // Información de asistencia para cada examen
                $infoAsistencia = [];

                if ($primerRegistro) {
                    // Primer Examen
                    $infoAsistencia['primer_examen'] = $this->calcularAsistenciaExamen(
                        $user->numero_documento,
                        $primerRegistro->fecha_registro,
                        $ciclo->fecha_primer_examen,
                        $ciclo
                    );

                    // Segundo Examen (si existe fecha)
                    if ($ciclo->fecha_segundo_examen) {
                        // El día siguiente al primer examen que sea lunes a viernes
                        $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen);

                        $infoAsistencia['segundo_examen'] = $this->calcularAsistenciaExamen(
                            $user->numero_documento,
                            $inicioSegundo,
                            $ciclo->fecha_segundo_examen,
                            $ciclo
                        );
                    }

                    // Tercer Examen (si existe fecha)
                    if ($ciclo->fecha_tercer_examen && $ciclo->fecha_segundo_examen) {
                        // El día siguiente al segundo examen que sea lunes a viernes
                        $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen);

                        $infoAsistencia['tercer_examen'] = $this->calcularAsistenciaExamen(
                            $user->numero_documento,
                            $inicioTercero,
                            $ciclo->fecha_tercer_examen,
                            $ciclo
                        );
                    }

                    // Asistencia total del ciclo
                    $infoAsistencia['total_ciclo'] = $this->calcularAsistenciaExamen(
                        $user->numero_documento,
                        $primerRegistro->fecha_registro,
                        min(Carbon::now(), Carbon::parse($ciclo->fecha_fin)), // Usar fecha actual si el ciclo no ha terminado
                        $ciclo
                    );
                }

                $data['inscripcionActiva'] = $inscripcionActiva;
                $data['infoAsistencia'] = $infoAsistencia;
                $data['primerRegistro'] = $primerRegistro;
            }

            // Información del estudiante
            $data['esEstudiante'] = true;
        }

        // Si el usuario es un profesor
        if ($user->hasRole('profesor')) {
            try {
                $data['esProfesor'] = true;
                
                // Obtener la fecha seleccionada del request, o usar la fecha actual por defecto
                $fechaSeleccionada = $request->input('fecha') ? Carbon::parse($request->input('fecha')) : Carbon::today();
                $data['fechaSeleccionada'] = $fechaSeleccionada;
                
                $diaSemanaSeleccionada = $fechaSeleccionada->locale('es')->dayName;
                
                // NUEVO: Obtener ciclo activo para calcular tarifa
                $cicloActivo = Ciclo::where('es_activo', true)->first();
                
                // Obtener horarios del profesor para la fecha seleccionada
                $horariosDelDia = HorarioDocente::where('docente_id', $user->id)
                    ->where('dia_semana', $diaSemanaSeleccionada)
                    ->with(['aula', 'curso', 'ciclo'])
                    ->orderBy('hora_inicio')
                    ->get();
                
                // Obtener registros del biométrico para la fecha seleccionada
                $registrosDelDia = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                    ->whereDate('fecha_registro', $fechaSeleccionada->format('Y-m-d'))
                    ->orderBy('fecha_registro')
                    ->get();

                $horasDelDia = 0;
                $sesionesPendientes = 0;

                // NUEVO: Obtener tarifa por hora desde la base de datos
                $tarifaPorHora = $this->obtenerTarifaDocente($user->id, $cicloActivo);
                $data['tarifa_por_hora'] = $tarifaPorHora;

                $horariosDelDiaConDetalles = $horariosDelDia->map(function ($horario) use ($registrosDelDia, &$horasDelDia, $user, &$sesionesPendientes, $fechaSeleccionada) {
                    $horaInicio = Carbon::parse($horario->hora_inicio);
                    $horaFin = Carbon::parse($horario->hora_fin);

                    // Para el cálculo de estados en la UI
                    $momentoActualComparacion = $fechaSeleccionada->isToday() ? Carbon::now() : $fechaSeleccionada->copy()->endOfDay();

                    // Combinar la fecha seleccionada con las horas del horario
                    $horarioInicioHoy = $fechaSeleccionada->copy()->setTime($horaInicio->hour, $horaInicio->minute, $horaInicio->second);
                    $horarioFinHoy = $fechaSeleccionada->copy()->setTime($horaFin->hour, $horaFin->minute, $horaFin->second);

                    // Buscar entrada válida (15 min antes hasta 30 min después del inicio)
                    $entrada = $registrosDelDia
                    ->filter(function($r) use ($horarioInicioHoy) {
                        $horaRegistro = Carbon::parse($r->fecha_registro);
                        return $horaRegistro->between(
                            $horarioInicioHoy->copy()->subMinutes(15),
                            $horarioInicioHoy->copy()->addMinutes(30)
                        );
                    })
                    ->sortBy('fecha_registro')
                    ->first();

                    // Buscar salida válida (15 min antes hasta 60 min después del final)
                    $salida = $registrosDelDia
                    ->filter(function($r) use ($horarioFinHoy) {
                        $horaRegistro = Carbon::parse($r->fecha_registro);
                        return $horaRegistro->between(
                            $horarioFinHoy->copy()->subMinutes(15),
                            $horarioFinHoy->copy()->addMinutes(60)
                        );
                    })
                    ->sortByDesc('fecha_registro')
                    ->first();
                    
                    // Calcular horas trabajadas para la fecha seleccionada
                    if ($entrada && $salida) {
                        $hEntrada = Carbon::parse($entrada->fecha_registro);
                        $hSalida = Carbon::parse($salida->fecha_registro);
                        if ($hSalida->greaterThan($hEntrada)) {
                            $horasDelDia += $hSalida->diffInMinutes($hEntrada) / 60;
                        }
                    }

                    // Buscar asistencia docente (tema desarrollado) para la fecha seleccionada
                    $asistencia = AsistenciaDocente::where('docente_id', $user->id)
                        ->where('horario_id', $horario->id)
                        ->whereDate('fecha_hora', $fechaSeleccionada->format('Y-m-d'))
                        ->first();

                    // Determinar estados para la UI
                    $dentroDelHorario = $momentoActualComparacion->between($horarioInicioHoy, $horarioFinHoy);
                    $claseTerminada = $momentoActualComparacion->greaterThan($horarioFinHoy);
                    $tieneRegistros = $entrada && $salida;
                    
                    // Lógica para determinar si se puede registrar tema
                    $puedeRegistrarTema = false;
                    if ($asistencia) {
                        // Ya tiene tema registrado, puede editar
                        $puedeRegistrarTema = true;
                    } elseif ($claseTerminada && $tieneRegistros) {
                        // Clase terminada y tiene ambos registros
                        $puedeRegistrarTema = true;
                    } elseif ($fechaSeleccionada->isToday() && $dentroDelHorario && $entrada) {
                        // Está dentro del horario HOY y tiene entrada
                        $puedeRegistrarTema = true;
                    }

                    // Contar sesiones pendientes
                    if ($claseTerminada && !$asistencia && $tieneRegistros) {
                        $sesionesPendientes++;
                    }

                    // NUEVO: Información avanzada de la sesión
                    $tiempoInfo = $this->calcularInfoTiempo($horarioInicioHoy, $horarioFinHoy, $momentoActualComparacion, $fechaSeleccionada);
                    
                    // Progreso de clase si está en curso
                    $progresoClase = 0;
                    if ($dentroDelHorario && $fechaSeleccionada->isToday()) {
                        $totalMinutos = $horarioInicioHoy->diffInMinutes($horarioFinHoy);
                        $minutosTranscurridos = $horarioInicioHoy->diffInMinutes($momentoActualComparacion);
                        $progresoClase = round(($minutosTranscurridos / $totalMinutos) * 100);
                    }
                    
                    // Duración y eficiencia
                    $duracionProgramada = $horarioInicioHoy->diffInMinutes($horarioFinHoy);
                    $duracionReal = null;
                    $eficiencia = null;
                    
                    if ($entrada && $salida) {
                        $entradaCarbon = Carbon::parse($entrada->fecha_registro);
                        $salidaCarbon = Carbon::parse($salida->fecha_registro);
                        $duracionReal = $entradaCarbon->diffInMinutes($salidaCarbon);
                        $eficiencia = round(($duracionReal / $duracionProgramada) * 100);
                    }

                    // Calcular tardanza
                    $minutosTardanza = 0;
                    $dentroTolerancia = true;
                    if ($entrada) {
                        $horaEntradaReal = Carbon::parse($entrada->fecha_registro);
                        $tolerancia = $horarioInicioHoy->copy()->addMinutes(5);
                        if ($horaEntradaReal->gt($tolerancia)) {
                            $minutosTardanza = $horaEntradaReal->diffInMinutes($tolerancia);
                            $dentroTolerancia = false;
                        }
                    }

                    return [
                        'horario' => $horario,
                        'hora_entrada_registrada' => $entrada ? Carbon::parse($entrada->fecha_registro)->format('H:i A') : null,
                        'hora_salida_registrada' => $salida ? Carbon::parse($salida->fecha_registro)->format('H:i A') : null,
                        'asistencia' => $asistencia,
                        'puede_registrar_tema' => $puedeRegistrarTema,
                        'dentro_horario' => $dentroDelHorario,
                        'clase_terminada' => $claseTerminada,
                        'tiene_registros' => $tieneRegistros,
                        'minutos_tardanza' => $minutosTardanza > 0 ? $minutosTardanza : null,
                        'dentro_tolerancia' => $dentroTolerancia,
                        'tiempo_info' => $tiempoInfo,
                        'progreso_clase' => $progresoClase,
                        'duracion_programada' => $duracionProgramada,
                        'duracion_real' => $duracionReal,
                        'eficiencia' => $eficiencia
                    ];
                });

                // ASEGURAR QUE TODAS LAS VARIABLES SE ASIGNEN CORRECTAMENTE
                $data['horasHoy'] = round($horasDelDia, 2);
                $data['horariosDelDia'] = $horariosDelDiaConDetalles;
                $data['sesionesHoy'] = $horariosDelDia->count(); // ←← ESTA ES LA VARIABLE QUE FALTABA
                $data['sesionesPendientes'] = $sesionesPendientes;

                // CORREGIDO: Calcular pago estimado del día mejorado con tarifa real
                $pagoEstimadoHoy = $this->calcularPagoEstimadoMejorado($user->id, $fechaSeleccionada, $horariosDelDia, $tarifaPorHora);
                $data['pagoEstimadoHoy'] = $pagoEstimadoHoy;
                
                // Calcular horas reales vs programadas
                $horasReales = 0;
                $horasProgramadas = 0;
                
                foreach ($horariosDelDiaConDetalles as $item) {
                    $horasProgramadas += $item['duracion_programada'] / 60;
                    if ($item['duracion_real']) {
                        $horasReales += $item['duracion_real'] / 60;
                    }
                }
                
                $data['horasReales'] = round($horasReales, 1);
                $data['horasProgramadas'] = round($horasProgramadas, 1);
                
                // Calcular métricas de rendimiento
                $data['eficiencia'] = $this->calcularEficienciaDocente($user->id, $fechaSeleccionada);
                $data['puntualidad'] = $this->calcularPuntualidadDocente($user->id);
                
                // Resumen semanal
                $fechaInicioSemana = Carbon::now()->subDays(6)->startOfDay();
                $fechaFinSemana = Carbon::now()->endOfDay();
                
                $resumenSemanal = AsistenciaDocente::where('docente_id', $user->id)
                    ->whereBetween('fecha_hora', [$fechaInicioSemana, $fechaFinSemana])
                    ->selectRaw('
                        COUNT(*) as total_sesiones,
                        SUM(horas_dictadas) as total_horas,
                        SUM(monto_total) as total_ingresos,
                        AVG(CASE WHEN estado = "completada" THEN 1 ELSE 0 END) * 100 as porcentaje_asistencia
                    ')
                    ->first();
                
                $data['resumenSemanal'] = [
                    'sesiones' => $resumenSemanal->total_sesiones ?? 0,
                    'horas' => round($resumenSemanal->total_horas ?? 0, 2),
                    'ingresos' => $resumenSemanal->total_ingresos ?? 0,
                    'asistencia' => round($resumenSemanal->porcentaje_asistencia ?? 0),
                    'tendencia' => $this->calcularTendenciaSemanal($user->id)
                ];
                
                // CORREGIDO: Próxima clase - asegurar que solo sea del docente actual
                $proximaClase = $this->obtenerProximaClaseCorregida($user->id);
                $data['proximaClase'] = $proximaClase;
                
                // NUEVO: Sistema de notificaciones mejorado
                $notificaciones = $this->generarNotificacionesDocente($user->id, $fechaSeleccionada, $sesionesPendientes, $proximaClase);
                $data['notificaciones'] = $notificaciones;
                
                // Recordatorios (mantener compatibilidad)
                $recordatorios = $this->generarRecordatorios($sesionesPendientes, $proximaClase, $fechaSeleccionada);
                $data['recordatorios'] = $recordatorios;

            } catch (\Exception $e) {
                // ←← NUEVO: Manejo de errores para evitar fallos silenciosos
                \Log::error('Error en dashboard de profesor: ' . $e->getMessage());
                
                // Asignar valores por defecto si hay error
                $data['esProfesor'] = true;
                $data['fechaSeleccionada'] = Carbon::today();
                $data['horasHoy'] = 0;
                $data['horariosDelDia'] = collect([]);
                $data['sesionesHoy'] = 0;
                $data['sesionesPendientes'] = 0;
                $data['pagoEstimadoHoy'] = 0;
                $data['horasReales'] = 0;
                $data['horasProgramadas'] = 0;
                $data['eficiencia'] = 85;
                $data['puntualidad'] = 95;
                $data['resumenSemanal'] = [
                    'sesiones' => 0,
                    'horas' => 0,
                    'ingresos' => 0,
                    'asistencia' => 0,
                    'tendencia' => 'up'
                ];
                $data['proximaClase'] = null;
                $data['recordatorios'] = [];
                $data['notificaciones'] = [];
                
                // Mostrar error al usuario
                $data['error_dashboard'] = 'Hubo un problema al cargar la información. Por favor, intenta nuevamente.';
            }
        }

        // Si el usuario es un padre
        if ($user->hasRole('padre')) {
            // Obtener los hijos del padre
            $hijos = \App\Models\Parentesco::where('padre_id', $user->id)
                ->where('estado', true)
                ->with('estudiante')
                ->get();

            $data['hijosCount'] = $hijos->count();
            $data['esPadre'] = true;

            // Array para almacenar la información de asistencia de cada hijo
            $hijosAsistencia = [];

            foreach ($hijos as $parentesco) {
                $hijo = $parentesco->estudiante;

                // Obtener inscripción activa del hijo
                $inscripcionActiva = Inscripcion::where('estudiante_id', $hijo->id)
                    ->where('estado_inscripcion', 'activo')
                    ->whereHas('ciclo', function ($query) {
                        $query->where('es_activo', true);
                    })
                    ->with(['ciclo', 'carrera', 'aula', 'turno'])
                    ->first();

                if ($inscripcionActiva) {
                    $ciclo = $inscripcionActiva->ciclo;

                    // Obtener el primer registro de asistencia del estudiante
                    $primerRegistro = RegistroAsistencia::where('nro_documento', $hijo->numero_documento)
                        ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
                        ->where('fecha_registro', '<=', $ciclo->fecha_fin)
                        ->orderBy('fecha_registro')
                        ->first();

                    // Información de asistencia para cada examen
                    $infoAsistencia = [];

                    if ($primerRegistro) {
                        // Calcular asistencia para cada examen
                        $infoAsistencia['primer_examen'] = $this->calcularAsistenciaExamen(
                            $hijo->numero_documento,
                            $primerRegistro->fecha_registro,
                            $ciclo->fecha_primer_examen,
                            $ciclo
                        );

                        if ($ciclo->fecha_segundo_examen) {
                            $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen);
                            $infoAsistencia['segundo_examen'] = $this->calcularAsistenciaExamen(
                                $hijo->numero_documento,
                                $inicioSegundo,
                                $ciclo->fecha_segundo_examen,
                                $ciclo
                            );
                        }

                        if ($ciclo->fecha_tercer_examen && $ciclo->fecha_segundo_examen) {
                            $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen);
                            $infoAsistencia['tercer_examen'] = $this->calcularAsistenciaExamen(
                                $hijo->numero_documento,
                                $inicioTercero,
                                $ciclo->fecha_tercer_examen,
                                $ciclo
                            );
                        }

                        // Asistencia total del ciclo
                        $infoAsistencia['total_ciclo'] = $this->calcularAsistenciaExamen(
                            $hijo->numero_documento,
                            $primerRegistro->fecha_registro,
                            min(Carbon::now(), Carbon::parse($ciclo->fecha_fin)),
                            $ciclo
                        );
                    }

                    $hijosAsistencia[] = [
                        'hijo' => $hijo,
                        'parentesco' => $parentesco,
                        'inscripcionActiva' => $inscripcionActiva,
                        'infoAsistencia' => $infoAsistencia,
                        'primerRegistro' => $primerRegistro
                    ];
                }
            }

            $data['hijosAsistencia'] = $hijosAsistencia;
        }

        // Determinar qué vista mostrar según el rol
        if ($user->hasRole('profesor')) {
            return view('admin.dashboard-profesor', $data);
        } elseif ($user->hasRole('estudiante')) {
            return view('admin.dashboard-estudiante', $data);
        } elseif ($user->hasRole('padre')) {
            return view('admin.dashboard-padre', $data);
        } else {
            // Estadísticas generales (para administradores)
            if ($user->hasRole('admin') || $user->hasPermission('dashboard.admin')) {
                // Ciclo activo
                $cicloActivo = Ciclo::where('es_activo', true)->first();

                if ($cicloActivo) {
                    // Estadísticas del ciclo activo
                    $data['cicloActivo'] = $cicloActivo;
                    $data['totalInscripciones'] = Inscripcion::where('ciclo_id', $cicloActivo->id)
                        ->where('estado_inscripcion', 'activo')
                        ->count();

                    // Estadísticas de asistencia general
                    $data['estadisticasAsistencia'] = $this->obtenerEstadisticasGenerales($cicloActivo);
                }

                // Carreras activas
                $data['totalCarreras'] = Carrera::where('estado', true)->count();

                // Aulas
                $data['totalAulas'] = Aula::where('estado', true)->count();

                $data['totalAdministradores'] = User::whereHas('roles', function ($query) {
                    $query->where('nombre', 'administrador');
                })->count();
                $data['totalRoles'] = Role::count();
                $data['totalPermisos'] = Permission::count();
                $data['totalCiclos'] = Ciclo::count();
                $data['totalTurnos'] = Turno::where('estado', true)->count();
                $data['totalCursos'] = Curso::where('estado', true)->count();
                $data['totalAnuncios'] = Anuncio::count();
                $data['totalHorariosDocentes'] = HorarioDocente::count();
                $data['totalPagosDocentes'] = PagoDocente::count();
                $data['totalAsistenciaDocente'] = AsistenciaDocente::count();
                $data['totalInscripcionesGeneral'] = Inscripcion::count();

                // Asistencia de estudiantes para hoy
                $today = Carbon::today();
                $data['asistenciaHoy'] = [
                    'total_registros' => RegistroAsistencia::whereDate('fecha_registro', $today)->count(),
                    // Asumiendo que 'estado' en RegistroAsistencia puede ser 'presente' o 'ausente'
                    'presentes' => RegistroAsistencia::whereDate('fecha_registro', $today)
                                    ->where('estado', 'presente')
                                    ->count(),
                    'ausentes' => RegistroAsistencia::whereDate('fecha_registro', $today)
                                    ->where('estado', 'ausente')
                                    ->count(),
                ];

            }
            return view('admin.dashboard', $data);
        }
    }

    /**
     * NUEVO: Método para obtener la tarifa del docente desde la base de datos
     */
    private function obtenerTarifaDocente($docenteId, $cicloActivo = null)
    {
        if (!$cicloActivo) {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
        }

        if ($cicloActivo) {
            // Buscar tarifa en la tabla pagos_docentes para el ciclo activo
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

        // Si no hay registro en pagos_docentes, usar tarifa del usuario o valor por defecto
        $user = \App\Models\User::find($docenteId);
        return $user->tarifa_por_hora ?? 25.00;
    }

    /**
     * NUEVO: Sistema de notificaciones para docentes
     */
    private function generarNotificacionesDocente($docenteId, $fechaSeleccionada, $sesionesPendientes, $proximaClase)
    {
        $notificaciones = [];

        // Notificación para sesiones pendientes
        if ($sesionesPendientes > 0) {
            $notificaciones[] = [
                'id' => 'sesiones_pendientes',
                'tipo' => 'warning',
                'titulo' => 'Sesiones Pendientes',
                'mensaje' => "Tienes {$sesionesPendientes} sesión" . ($sesionesPendientes > 1 ? 'es' : '') . " pendiente" . ($sesionesPendientes > 1 ? 's' : '') . " de completar el tema para el " . $fechaSeleccionada->locale('es')->isoFormat('D [de] MMMM') . ".",
                'icono' => 'mdi-alert-circle',
                'accion' => 'completar_temas',
                'fecha_creacion' => Carbon::now(),
                'prioridad' => 'alta'
            ];
        }

        // Notificaciones para próxima clase
        if ($proximaClase) {
            $horaProximaClase = Carbon::parse($proximaClase->hora_inicio);
            $diaProximaClase = $this->calcularFechaProximaClase($proximaClase->dia_semana);
            $proximaClase->fecha_proxima = $diaProximaClase;

            if ($diaProximaClase->isToday()) {
                $ahora = Carbon::now();
                $horaCompleta = $diaProximaClase->copy()->setTime($horaProximaClase->hour, $horaProximaClase->minute);
                $minutosHastaProxima = $ahora->diffInMinutes($horaCompleta, false);
                
                if ($minutosHastaProxima >= 0 && $minutosHastaProxima <= 300) { // 5 horas
                    $tipoNotificacion = 'info';
                    $prioridad = 'media';
                    
                    if ($minutosHastaProxima <= 30) {
                        $tipoNotificacion = 'danger';
                        $prioridad = 'critica';
                        $mensaje = "¡Tu clase de {$proximaClase->curso->nombre} comienza en {$minutosHastaProxima} minutos!";
                    } elseif ($minutosHastaProxima <= 60) {
                        $tipoNotificacion = 'warning';
                        $prioridad = 'alta';
                        $mensaje = "Tu clase de {$proximaClase->curso->nombre} comienza en 1 hora.";
                    } else {
                        $horas = round($minutosHastaProxima / 60, 1);
                        $mensaje = "Tu próxima clase de {$proximaClase->curso->nombre} es hoy en {$horas} horas.";
                    }

                    $notificaciones[] = [
                        'id' => 'proxima_clase_hoy',
                        'tipo' => $tipoNotificacion,
                        'titulo' => 'Próxima Clase',
                        'mensaje' => $mensaje,
                        'icono' => 'mdi-clock-fast',
                        'accion' => 'ver_agenda',
                        'fecha_creacion' => Carbon::now(),
                        'prioridad' => $prioridad,
                        'datos' => [
                            'horario_id' => $proximaClase->id,
                            'curso' => $proximaClase->curso->nombre,
                            'aula' => $proximaClase->aula->nombre ?? 'Sin aula',
                            'hora' => $horaProximaClase->format('H:i')
                        ]
                    ];
                }
            } elseif ($diaProximaClase->isTomorrow()) {
                $notificaciones[] = [
                    'id' => 'proxima_clase_manana',
                    'tipo' => 'info',
                    'titulo' => 'Clase de Mañana',
                    'mensaje' => "Tu próxima clase de {$proximaClase->curso->nombre} es mañana a las {$horaProximaClase->format('h:i A')}.",
                    'icono' => 'mdi-calendar-clock',
                    'accion' => 'preparar_clase',
                    'fecha_creacion' => Carbon::now(),
                    'prioridad' => 'baja',
                    'datos' => [
                        'horario_id' => $proximaClase->id,
                        'curso' => $proximaClase->curso->nombre,
                        'aula' => $proximaClase->aula->nombre ?? 'Sin aula',
                        'hora' => $horaProximaClase->format('H:i')
                    ]
                ];
            }
        }

        // Notificación de rendimiento semanal
        $eficiencia = $this->calcularEficienciaDocente($docenteId, $fechaSeleccionada);
        $puntualidad = $this->calcularPuntualidadDocente($docenteId);

        if ($eficiencia < 70 || $puntualidad < 80) {
            $mensaje = "Tu rendimiento semanal: Eficiencia {$eficiencia}%, Puntualidad {$puntualidad}%.";
            if ($eficiencia < 70) $mensaje .= " Considera revisar tu gestión del tiempo en clase.";
            if ($puntualidad < 80) $mensaje .= " Recuerda llegar puntual a tus sesiones.";

            $notificaciones[] = [
                'id' => 'rendimiento_semanal',
                'tipo' => 'warning',
                'titulo' => 'Rendimiento Semanal',
                'mensaje' => $mensaje,
                'icono' => 'mdi-chart-line',
                'accion' => 'ver_estadisticas',
                'fecha_creacion' => Carbon::now(),
                'prioridad' => 'media'
            ];
        }

        // Ordenar notificaciones por prioridad
        $prioridadOrden = ['critica' => 1, 'alta' => 2, 'media' => 3, 'baja' => 4];
        usort($notificaciones, function($a, $b) use ($prioridadOrden) {
            return $prioridadOrden[$a['prioridad']] <=> $prioridadOrden[$b['prioridad']];
        });

        return $notificaciones;
    }

    /**
     * NUEVO: Método para calcular pago estimado mejorado con tarifa real
     */
    private function calcularPagoEstimadoMejorado($docenteId, $fechaSeleccionada, $horariosDelDia, $tarifaPorHora)
    {
        // Primero intentar obtener el monto real de AsistenciaDocente
        $pagoReal = AsistenciaDocente::where('docente_id', $docenteId)
            ->whereDate('fecha_hora', $fechaSeleccionada->format('Y-m-d'))
            ->sum('monto_total');

        // Si hay registros reales, usar ese monto
        if ($pagoReal > 0) {
            return $pagoReal;
        }

        // Si no hay registros reales, calcular estimado basado en horarios programados
        $totalHorasEstimadas = 0;
        
        foreach ($horariosDelDia as $horario) {
            $horaInicio = Carbon::parse($horario->hora_inicio);
            $horaFin = Carbon::parse($horario->hora_fin);
            $totalHorasEstimadas += $horaInicio->diffInMinutes($horaFin) / 60;
        }

        return $totalHorasEstimadas * $tarifaPorHora;
    }

    /**
     * Mantener método de recordatorios para compatibilidad
     */
    private function generarRecordatorios($sesionesPendientes, $proximaClase, $fechaSeleccionada)
    {
        $recordatorios = [];
        
        if ($sesionesPendientes > 0) {
            $recordatorios[] = [
                'tipo' => 'warning',
                'mensaje' => "{$sesionesPendientes} sesión" . ($sesionesPendientes > 1 ? 'es' : '') . " pendiente" . ($sesionesPendientes > 1 ? 's' : '') . " de completar el tema para el " . $fechaSeleccionada->locale('es')->isoFormat('D [de] MMMM') . "."
            ];
        }
        
        if ($proximaClase) {
            $horaProximaClase = Carbon::parse($proximaClase->hora_inicio);
            $diaProximaClase = $this->calcularFechaProximaClase($proximaClase->dia_semana);
            $proximaClase->fecha_proxima = $diaProximaClase;

            if ($diaProximaClase->isToday()) {
                $ahora = Carbon::now();
                $horaCompleta = $diaProximaClase->copy()->setTime($horaProximaClase->hour, $horaProximaClase->minute);
                $horasHastaProxima = $ahora->diffInHours($horaCompleta, false);
                
                if ($horasHastaProxima >= 0 && $horasHastaProxima <= 5) {
                    $recordatorios[] = [
                        'tipo' => 'info',
                        'mensaje' => "Tu próxima clase de {$proximaClase->curso->nombre} es hoy en {$horasHastaProxima} horas."
                    ];
                }
            } elseif ($diaProximaClase->isTomorrow()) {
                $recordatorios[] = [
                    'tipo' => 'info',
                    'mensaje' => "Tu próxima clase de {$proximaClase->curso->nombre} es mañana a las {$horaProximaClase->format('h:i A')}."
                ];
            }
        }
        
        return $recordatorios;
    }

    /**
     * CORREGIDO: Método para obtener próxima clase solo del docente actual
     */
    private function obtenerProximaClaseCorregida($docenteId)
    {
        $ahora = Carbon::now();
        $diaActualSemana = $ahora->locale('es')->dayName;

        // Primero buscar clases de hoy que aún no han comenzado
        $proximaClaseHoy = HorarioDocente::where('docente_id', $docenteId)
            ->where('dia_semana', $diaActualSemana)
            ->where('hora_inicio', '>', $ahora->format('H:i:s'))
            ->with(['aula', 'curso'])
            ->orderBy('hora_inicio')
            ->first();

        if ($proximaClaseHoy) {
            return $proximaClaseHoy;
        }

        // Si no hay clases hoy, buscar en los próximos días
        $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        $diaActualIndex = array_search(strtolower($diaActualSemana), $diasSemana);
        
        // Buscar en los próximos 7 días
        for ($i = 1; $i <= 7; $i++) {
            $indexDia = ($diaActualIndex + $i) % 7;
            $diaBuscado = $diasSemana[$indexDia];
            
            $claseEncontrada = HorarioDocente::where('docente_id', $docenteId) // CORREGIDO: Filtro por docente
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

    /**
     * NUEVO: Calcular información de tiempo
     */
    private function calcularInfoTiempo($horaInicio, $horaFin, $momentoActual, $fechaSeleccionada)
    {
        if (!$fechaSeleccionada->isToday()) {
            if ($fechaSeleccionada->isPast()) {
                return [
                    'estado' => 'terminada',
                    'texto' => 'Clase finalizada'
                ];
            } else {
                return [
                    'estado' => 'por_empezar',
                    'texto' => 'Clase programada'
                ];
            }
        }
        
        if ($momentoActual->lt($horaInicio)) {
            $minutosParaInicio = $momentoActual->diffInMinutes($horaInicio);
            return [
                'estado' => 'por_empezar',
                'texto' => $minutosParaInicio < 60 ? 
                    "En {$minutosParaInicio} min" : 
                    "En " . $momentoActual->diffForHumans($horaInicio, true)
            ];
        } elseif ($momentoActual->between($horaInicio, $horaFin)) {
            $minutosRestantes = $momentoActual->diffInMinutes($horaFin);
            return [
                'estado' => 'en_curso',
                'texto' => "Termina en {$minutosRestantes} min"
            ];
        } else {
            return [
                'estado' => 'terminada',
                'texto' => "Terminó " . $horaFin->diffForHumans()
            ];
        }
    }

    /**
     * NUEVO: Calcular eficiencia del docente
     */
    private function calcularEficienciaDocente($docenteId, $fecha)
    {
        $sesiones = AsistenciaDocente::where('docente_id', $docenteId)
            ->where('fecha_hora', '>=', Carbon::now()->subDays(30))
            ->with('horario')
            ->get();
        
        if ($sesiones->isEmpty()) {
            return 85; // Valor por defecto
        }
        
        $eficienciaPromedio = $sesiones->avg(function ($sesion) {
            // Calcular eficiencia basada en tiempo trabajado vs programado
            if (!$sesion->horario) {
                return 85; // Valor por defecto si no hay horario
            }
            
            $programado = Carbon::parse($sesion->horario->hora_fin)->diffInMinutes(Carbon::parse($sesion->horario->hora_inicio));
            $real = $sesion->horas_dictadas * 60; // Convertir a minutos
            
            return $real > 0 ? min(($real / $programado) * 100, 100) : 0;
        });
        
        return round($eficienciaPromedio);
    }

    /**
     * NUEVO: Calcular puntualidad del docente
     */
    private function calcularPuntualidadDocente($docenteId)
    {
        $registros = DB::table('asistencias_docentes as ad')
            ->join('horarios_docentes as hd', 'ad.horario_id', '=', 'hd.id')
            ->where('ad.docente_id', $docenteId)
            ->where('ad.fecha_hora', '>=', Carbon::now()->subDays(30))
            ->whereNotNull('ad.hora_entrada')
            ->select('ad.*', 'hd.hora_inicio')
            ->get();
        
        if ($registros->isEmpty()) {
            return 95; // Valor por defecto
        }
        
        $sesionesAPunto = 0;
        $totalSesiones = $registros->count();
        
        foreach ($registros as $registro) {
            $horaInicioProgramada = Carbon::parse($registro->hora_inicio);
            $horaEntradaReal = Carbon::parse($registro->hora_entrada);
            $tolerancia = $horaInicioProgramada->copy()->addMinutes(5);
            
            if ($horaEntradaReal->lte($tolerancia)) {
                $sesionesAPunto++;
            }
        }
        
        return round(($sesionesAPunto / $totalSesiones) * 100);
    }

    /**
     * NUEVO: Calcular fecha de próxima clase
     */
    private function calcularFechaProximaClase($diaSemana)
    {
        $diasMap = [
            'lunes' => 1, 'martes' => 2, 'miércoles' => 3, 'jueves' => 4,
            'viernes' => 5, 'sábado' => 6, 'domingo' => 0
        ];
        
        $targetDay = $diasMap[strtolower($diaSemana)];
        $ahora = Carbon::now();
        $daysUntilTarget = ($targetDay - $ahora->dayOfWeek + 7) % 7;
        
        if ($daysUntilTarget == 0) {
            $daysUntilTarget = 7; // Próxima semana si es el mismo día
        }
        
        return $ahora->addDays($daysUntilTarget);
    }

    /**
     * NUEVO: Calcular tendencia semanal
     */
    private function calcularTendenciaSemanal($docenteId)
    {
        $semanaActual = AsistenciaDocente::where('docente_id', $docenteId)
            ->whereBetween('fecha_hora', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('horas_dictadas');
        
        $semanaAnterior = AsistenciaDocente::where('docente_id', $docenteId)
            ->whereBetween('fecha_hora', [
                Carbon::now()->subWeek()->startOfWeek(), 
                Carbon::now()->subWeek()->endOfWeek()
            ])
            ->sum('horas_dictadas');
        
        return $semanaActual >= $semanaAnterior ? 'up' : 'down';
    }

    /**
     * Registra o actualiza el tema desarrollado por un docente para un horario y fecha específicos.
     *
     * @param Request $request La solicitud HTTP que contiene horario_id, fecha_seleccionada y tema_desarrollado.
     * @return \Illuminate\Http\JsonResponse La respuesta JSON con el resultado de la operación.
     */
    public function registrarTemaDesarrollado(Request $request)
    {
        try {
            $request->validate([
                'horario_id' => 'required|exists:horarios_docentes,id',
                'fecha_seleccionada' => 'required|date',
                'tema_desarrollado' => 'required|string|min:10|max:1000'
            ], [
                'tema_desarrollado.required' => 'El tema desarrollado es obligatorio',
                'tema_desarrollado.min' => 'El tema debe tener al menos 10 caracteres',
                'tema_desarrollado.max' => 'El tema no puede exceder 1000 caracteres',
                'fecha_seleccionada.required' => 'La fecha es obligatoria',
                'fecha_seleccionada.date' => 'La fecha debe tener un formato válido'
            ]);

            $user = Auth::user();
            $fechaSeleccionada = Carbon::parse($request->fecha_seleccionada)->startOfDay();
            $diaSemanaSeleccionada = $fechaSeleccionada->locale('es')->dayName;
            
            // Verificar que el horario pertenece al docente
            $horario = HorarioDocente::where('id', $request->horario_id)
                ->where('docente_id', $user->id)
                ->where('dia_semana', $diaSemanaSeleccionada)
                ->first();
                
            if (!$horario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horario no válido o no corresponde al día de la semana de la fecha seleccionada.'
                ], 400);
            }

            // Combinar la fecha seleccionada con las horas del horario
            $horarioInicioClase = $fechaSeleccionada->copy()->setTime(Carbon::parse($horario->hora_inicio)->hour, Carbon::parse($horario->hora_inicio)->minute);
            $horarioFinClase = $fechaSeleccionada->copy()->setTime(Carbon::parse($horario->hora_fin)->hour, Carbon::parse($horario->hora_fin)->minute);
            
            // Obtener registros biométricos para la fecha seleccionada
            $registrosDiaSeleccionado = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaSeleccionada->format('Y-m-d'))
                ->orderBy('fecha_registro')
                ->get();

            // Buscar entrada válida
            $entrada = $registrosDiaSeleccionado->filter(function($r) use ($horarioInicioClase) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioInicioClase->copy()->subMinutes(15),
                    $horarioInicioClase->copy()->addMinutes(30)
                );
            })->first();

            // Buscar salida válida
            $salida = $registrosDiaSeleccionado->filter(function($r) use ($horarioFinClase) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioFinClase->copy()->subMinutes(15),
                    $horarioFinClase->copy()->addMinutes(60)
                );
            })->sortByDesc('fecha_registro')->first();

            // Validar que existan registros de entrada y salida
            if (!$entrada || !$salida) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron registros biométricos de entrada y/o salida válidos para esta sesión en la fecha seleccionada. No se puede registrar el tema.'
                ], 400);
            }

            // Calcular datos de asistencia
            $horaEntrada = Carbon::parse($entrada->fecha_registro);
            $horaSalida = Carbon::parse($salida->fecha_registro);
            
            $horasTrabajadas = 0;
            $montoTotal = 0;
            
            if ($horaSalida->greaterThan($horaEntrada)) {
                $horasTrabajadas = $horaSalida->diffInMinutes($horaEntrada) / 60;
                
                // CORREGIDO: Usar tarifa desde base de datos
                $cicloActivo = Ciclo::where('es_activo', true)->first();
                $tarifaHora = $this->obtenerTarifaDocente($user->id, $cicloActivo);
                $montoTotal = $horasTrabajadas * $tarifaHora;
            } else {
                 return response()->json([
                    'success' => false,
                    'message' => 'Los registros biométricos son inválidos (hora de salida no es mayor que la de entrada).'
                ], 400);
            }

            // Crear o actualizar registro de AsistenciaDocente
            $asistencia = AsistenciaDocente::updateOrCreate(
                [
                    'docente_id' => $user->id,
                    'horario_id' => $request->horario_id,
                    'fecha_hora' => $fechaSeleccionada->toDateString()
                ],
                [
                    'tema_desarrollado' => $request->tema_desarrollado,
                    'hora_entrada' => $horaEntrada->toDateTimeString(),
                    'hora_salida' => $horaSalida->toDateTimeString(),
                    'horas_dictadas' => round($horasTrabajadas, 2),
                    'monto_total' => round($montoTotal, 2),
                    'estado' => 'completada'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Tema desarrollado registrado exitosamente',
                'data' => [
                    'tema' => $asistencia->tema_desarrollado,
                    'horas' => $asistencia->horas_dictadas,
                    'monto' => $asistencia->monto_total
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcula la asistencia de un estudiante para un período específico.
     */
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
                'limite_amonestacion' => 0,
                'limite_inhabilitacion' => 0,
                'estado' => 'pendiente',
                'mensaje' => 'Este período aún no ha comenzado.',
                'puede_rendir' => true,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaExamen,
                'es_proyeccion' => true
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

        $porcentajeAsistenciaActual = $diasHabilesTranscurridos > 0 ?
            round(($diasConAsistencia / $diasHabilesTranscurridos) * 100, 2) : 0;

        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

        $estado = 'regular';
        $mensaje = '';
        $puedeRendir = true;

        if ($hoy >= $fechaExamenCarbon) {
            if ($diasFaltaActuales >= $limiteInhabilitacion) {
                $estado = 'inhabilitado';
                $mensaje = 'Has superado el ' . $ciclo->porcentaje_inhabilitacion . '% de inasistencias. No pudiste rendir este examen.';
                $puedeRendir = false;
            } elseif ($diasFaltaActuales >= $limiteAmonestacion) {
                $estado = 'amonestado';
                $mensaje = 'Superaste el ' . $ciclo->porcentaje_amonestacion . '% de inasistencias pero pudiste rendir el examen.';
            } else {
                $mensaje = 'Tu asistencia fue adecuada para este examen.';
            }
        } else {
            $diasRestantes = $diasHabilesTotales - $diasHabilesTranscurridos;
            $faltasMaximasPermitidas = $limiteInhabilitacion - 1;
            $faltasParaInhabilitacion = $faltasMaximasPermitidas - $diasFaltaActuales;

            if ($diasFaltaActuales >= $limiteInhabilitacion) {
                $estado = 'inhabilitado';
                $mensaje = "Ya has acumulado {$diasFaltaActuales} faltas. Has superado el límite de {$limiteInhabilitacion} faltas permitidas.";
                $puedeRendir = false;
            } elseif ($diasFaltaActuales >= $limiteAmonestacion) {
                $estado = 'amonestado';
                if ($faltasParaInhabilitacion > 0) {
                    $mensaje = "Tienes {$diasFaltaActuales} faltas. ¡Cuidado! Solo puedes faltar {$faltasParaInhabilitacion} día" . ($faltasParaInhabilitacion > 1 ? 's' : '') . " más antes de ser inhabilitado.";
                } else {
                    $mensaje = "Tienes {$diasFaltaActuales} faltas. ¡No puedes faltar más o serás inhabilitado!";
                }
            } else {
                $faltasParaAmonestacion = $limiteAmonestacion - $diasFaltaActuales;
                $mensaje = "Tu asistencia va bien. Tienes {$diasFaltaActuales} faltas. Puedes faltar hasta {$faltasParaAmonestacion} día" . ($faltasParaAmonestacion > 1 ? 's' : '') . " más sin ser amonestado.";
            }

            $mensaje .= " Quedan {$diasRestantes} día" . ($diasRestantes > 1 ? 's' : '') . " hábil" . ($diasRestantes > 1 ? 'es' : '') . " hasta el examen.";
        }

        return [
            'dias_habiles' => $diasHabilesTotales,
            'dias_habiles_transcurridos' => $diasHabilesTranscurridos,
            'dias_asistidos' => $diasConAsistencia,
            'dias_falta' => $diasFaltaActuales,
            'porcentaje_asistencia' => $porcentajeAsistenciaProyectado,
            'porcentaje_asistencia_actual' => $porcentajeAsistenciaActual,
            'porcentaje_inasistencia' => $porcentajeInasistenciaProyectado,
            'limite_amonestacion' => $limiteAmonestacion,
            'limite_inhabilitacion' => $limiteInhabilitacion,
            'estado' => $estado,
            'mensaje' => $mensaje,
            'puede_rendir' => $puedeRendir,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaExamen,
            'es_proyeccion' => $hoy < $fechaExamenCarbon,
            'dias_restantes' => $hoy < $fechaExamenCarbon ? $diasHabilesTotales - $diasHabilesTranscurridos : 0
        ];
    }

    /**
     * Contar días hábiles entre dos fechas.
     */
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

    /**
     * Obtener el siguiente día hábil.
     */
    private function getSiguienteDiaHabil($fecha)
    {
        $dia = Carbon::parse($fecha)->addDay();

        while (!$dia->isWeekday()) {
            $dia->addDay();
        }

        return $dia;
    }

    /**
     * Obtiene la fecha para un día de la semana dado.
     */
    private function getFechaParaDiaSemana($diaSemana)
    {
        $diasSemanaMap = [
            'lunes' => Carbon::MONDAY,
            'martes' => Carbon::TUESDAY,
            'miércoles' => Carbon::WEDNESDAY,
            'jueves' => Carbon::THURSDAY,
            'viernes' => Carbon::FRIDAY,
            'sábado' => Carbon::SATURDAY,
            'domingo' => Carbon::SUNDAY,
        ];

        $targetDayOfWeek = $diasSemanaMap[strtolower($diaSemana)];
        $fecha = Carbon::now();

        if ($fecha->dayOfWeek <= $targetDayOfWeek) {
            return $fecha->next($targetDayOfWeek);
        } else {
            return $fecha->addWeek()->startOfWeek()->next($targetDayOfWeek);
        }
    }

    /**
     * Obtener estadísticas generales de asistencia.
     */
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