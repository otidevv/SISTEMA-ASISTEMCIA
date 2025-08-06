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
use App\Models\HorarioDocente; // Asegúrate de que esta línea esté presente

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
        $data['totalUsuarios'] = \App\Models\User::count();
        $data['totalEstudiantes'] = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('nombre', 'estudiante');
        })->count();
        $data['totalPadres'] = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('nombre', 'padre');
        })->count();

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
            $data['esProfesor'] = true;
            
            // Obtener la fecha seleccionada del request, o usar la fecha actual por defecto
            $fechaSeleccionada = $request->input('fecha') ? Carbon::parse($request->input('fecha')) : Carbon::today();
            $data['fechaSeleccionada'] = $fechaSeleccionada; // Pasar al Blade
            
            $diaSemanaSeleccionada = $fechaSeleccionada->locale('es')->dayName;
            
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

            $horariosDelDiaConDetalles = $horariosDelDia->map(function ($horario) use ($registrosDelDia, &$horasDelDia, $user, &$sesionesPendientes, $fechaSeleccionada) {
                $horaInicio = Carbon::parse($horario->hora_inicio);
                $horaFin = Carbon::parse($horario->hora_fin);

                // Importante: Para el cálculo de `dentroDelHorario` y `claseTerminada` en la vista,
                // si la fecha seleccionada NO ES HOY, siempre se considerará 'clase terminada'.
                // Si la fecha seleccionada ES HOY, se usa `Carbon::now()` para la comparación.
                $momentoActualComparacion = $fechaSeleccionada->isToday() ? Carbon::now() : $fechaSeleccionada->copy()->endOfDay(); // Si no es hoy, asumimos que ya pasó el día completo

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
                    ->whereDate('fecha_hora', $fechaSeleccionada->format('Y-m-d')) // IMPORTANTE: Filtrar por la fecha seleccionada
                    ->first();

                // Determinar estados para la UI (usando $momentoActualComparacion)
                $dentroDelHorario = $momentoActualComparacion->between($horarioInicioHoy, $horarioFinHoy);
                $claseTerminada = $momentoActualComparacion->greaterThan($horarioFinHoy);
                $tieneRegistros = $entrada && $salida;
                
                // Lógica para determinar si se puede registrar tema (Ajustada para fechas anteriores)
                $puedeRegistrarTema = false;
                if ($asistencia) {
                    // Ya tiene tema registrado, puede editar
                    $puedeRegistrarTema = true;
                } elseif ($claseTerminada && $tieneRegistros) {
                    // Clase terminada (hoy o en el pasado) y tiene ambos registros
                    $puedeRegistrarTema = true;
                } elseif ($fechaSeleccionada->isToday() && $dentroDelHorario && $entrada) {
                    // Está dentro del horario HOY y tiene entrada
                    $puedeRegistrarTema = true;
                }

                // Contar sesiones pendientes (solo para la fecha seleccionada si ya terminó y no tiene tema)
                if ($claseTerminada && !$asistencia && $tieneRegistros) {
                    $sesionesPendientes++;
                }

                return [
                    'horario' => $horario,
                    'hora_entrada_registrada' => $entrada ? Carbon::parse($entrada->fecha_registro)->format('H:i A') : null,
                    'hora_salida_registrada' => $salida ? Carbon::parse($salida->fecha_registro)->format('H:i A') : null,
                    'asistencia' => $asistencia,
                    'puede_registrar_tema' => $puedeRegistrarTema,
                    'dentro_horario' => $dentroDelHorario,
                    'clase_terminada' => $claseTerminada,
                    'tiene_registros' => $tieneRegistros
                ];
            });

            $data['horasHoy'] = round($horasDelDia, 2); // Ahora es horasDelDia
            $data['horariosDelDia'] = $horariosDelDiaConDetalles; // Renombrado para mayor claridad
            $data['sesionesHoy'] = $horariosDelDia->count(); // Total de sesiones para la fecha seleccionada
            $data['sesionesPendientes'] = $sesionesPendientes; // Sesiones pendientes para la fecha seleccionada

            // Calcular pago estimado del día (para la fecha seleccionada)
            $pagoEstimadoHoy = AsistenciaDocente::where('docente_id', $user->id)
            ->whereDate('fecha_hora', $fechaSeleccionada->format('Y-m-d'))
            ->sum('monto_total');
            
            $data['pagoEstimadoHoy'] = $pagoEstimadoHoy;
            
            // Resumen semanal (últimos 7 días desde hoy, no desde la fecha seleccionada)
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
                'asistencia' => round($resumenSemanal->porcentaje_asistencia ?? 0)
            ];
            
            // Próxima clase (siempre desde la fecha y hora actuales)
            $ahora = Carbon::now();
            $diaActualSemana = $ahora->locale('es')->dayName;

            $proximaClase = HorarioDocente::where('docente_id', $user->id)
                ->where(function($query) use ($ahora, $diaActualSemana) {
                    // Clases de hoy que aún no han comenzado
                    $query->where('dia_semana', $diaActualSemana)
                                ->where('hora_inicio', '>', $ahora->format('H:i:s'));
                })
                ->orWhere(function($query) use ($diaActualSemana) {
                    // Clases de días siguientes
                    $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
                    $diaActualIndex = array_search(strtolower($diaActualSemana), $diasSemana);
                    $diasSiguientes = array_slice($diasSemana, $diaActualIndex + 1);
                    
                    if (!empty($diasSiguientes)) {
                        $query->whereIn('dia_semana', $diasSiguientes);
                    }
                })
                ->with(['aula', 'curso'])
                ->orderByRaw("
                    CASE dia_semana
                        WHEN 'lunes' THEN 1
                        WHEN 'martes' THEN 2
                        WHEN 'miércoles' THEN 3
                        WHEN 'jueves' THEN 4
                        WHEN 'viernes' THEN 5
                        WHEN 'sábado' THEN 6
                        WHEN 'domingo' THEN 7
                    END
                ")
                ->orderBy('hora_inicio')
                ->first();
            
            $data['proximaClase'] = $proximaClase;
            
            // Recordatorios (siempre basados en la situación actual)
            $recordatorios = [];
            if ($sesionesPendientes > 0) { // Sesiones pendientes de la fecha seleccionada
                $recordatorios[] = [
                    'tipo' => 'warning',
                    'mensaje' => "{$sesionesPendientes} sesión" . ($sesionesPendientes > 1 ? 'es' : '') . " pendiente" . ($sesionesPendientes > 1 ? 's' : '') . " de completar el tema para el " . $fechaSeleccionada->locale('es')->isoFormat('D [de] MMMM') . "."
                ];
            }
            
            if ($proximaClase) {
                // Calcular horas hasta la próxima clase solo si es hoy o en el futuro
                $horaProximaClase = Carbon::parse($proximaClase->hora_inicio);
                $diaProximaClase = $this->getFechaParaDiaSemana($proximaClase->dia_semana);

                // Si la próxima clase es hoy y aún no ha pasado su hora
                if ($diaProximaClase->isToday() && $ahora->lessThan($horaProximaClase)) {
                    $horasHastaProxima = $ahora->diffInHours($horaProximaClase, false); // false para obtener negativo si ya pasó
                    if ($horasHastaProxima >= 0 && $horasHastaProxima <= 5) {
                        $recordatorios[] = [
                            'tipo' => 'info',
                            'mensaje' => "Tu próxima clase de {$proximaClase->curso->nombre} es hoy en {$horasHastaProxima} horas."
                        ];
                    }
                } elseif ($diaProximaClase->greaterThan($ahora->startOfDay())) {
                    // Si es en un día futuro, solo un recordatorio general
                    $recordatorios[] = [
                        'tipo' => 'info',
                        'mensaje' => "Tu próxima clase de {$proximaClase->curso->nombre} es el {$diaProximaClase->locale('es')->isoFormat('dddd')} a las {$horaProximaClase->format('h:i A')}."
                    ];
                }
            }
            
            $data['recordatorios'] = $recordatorios;
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

        // Estadísticas generales (para administradores)
        if ($user->hasRole('administrador') || $user->hasPermission('dashboard.admin')) {
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
            $data['totalCarreras'] = \App\Models\Carrera::where('estado', true)->count();

            // Aulas
            $data['totalAulas'] = \App\Models\Aula::where('estado', true)->count();
        }

        // Determinar qué vista mostrar según el rol
        if ($user->hasRole('profesor')) {
            return view('admin.dashboard-profesor', $data);
        } elseif ($user->hasRole('estudiante')) {
            return view('admin.dashboard-estudiante', $data);
        } elseif ($user->hasRole('padre')) {
            return view('admin.dashboard-padre', $data);
        } else {
            return view('admin.dashboard', $data);
        }
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
                'fecha_seleccionada' => 'required|date', // Nueva validación para la fecha
                'tema_desarrollado' => 'required|string|min:10|max:1000'
            ], [
                'tema_desarrollado.required' => 'El tema desarrollado es obligatorio',
                'tema_desarrollado.min' => 'El tema debe tener al menos 10 caracteres',
                'tema_desarrollado.max' => 'El tema no puede exceder 1000 caracteres',
                'fecha_seleccionada.required' => 'La fecha es obligatoria',
                'fecha_seleccionada.date' => 'La fecha debe tener un formato válido'
            ]);

            $user = Auth::user();
            $fechaSeleccionada = Carbon::parse($request->fecha_seleccionada)->startOfDay(); // Obtener y limpiar la fecha
            $diaSemanaSeleccionada = $fechaSeleccionada->locale('es')->dayName;
            
            // Verificar que el horario pertenece al docente y corresponde al día de la semana de la fecha seleccionada
            $horario = HorarioDocente::where('id', $request->horario_id)
                ->where('docente_id', $user->id)
                ->where('dia_semana', $diaSemanaSeleccionada) // Usamos el día de la semana de la fecha seleccionada
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
            
            // Obtener registros biométricos para la FECHA SELECCIONADA
            $registrosDiaSeleccionado = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaSeleccionada->format('Y-m-d')) // Filtrar por la fecha seleccionada
                ->orderBy('fecha_registro') // Ordenar para encontrar la primera entrada y última salida
                ->get();

            // Buscar entrada válida (15 min antes hasta 30 min después del inicio del horario)
            $entrada = $registrosDiaSeleccionado->filter(function($r) use ($horarioInicioClase) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioInicioClase->copy()->subMinutes(15),
                    $horarioInicioClase->copy()->addMinutes(30)
                );
            })->first();

            // Buscar salida válida (15 min antes hasta 60 min después del final del horario)
            $salida = $registrosDiaSeleccionado->filter(function($r) use ($horarioFinClase) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioFinClase->copy()->subMinutes(15),
                    $horarioFinClase->copy()->addMinutes(60)
                );
            })->sortByDesc('fecha_registro')->first(); // Tomar la última salida que cumpla el criterio

            // Validar que existan registros de entrada y salida para poder registrar el tema
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
                $tarifaHora = $horario->tarifa_hora ?? $user->tarifa_hora ?? 25; // Usar tarifa del horario o del usuario, o un valor por defecto
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
                    'fecha_hora' => $fechaSeleccionada->toDateString() // Usamos la fecha seleccionada
                ],
                [
                    'tema_desarrollado' => $request->tema_desarrollado,
                    'hora_entrada' => $horaEntrada->toDateTimeString(), // Guardar como datetime
                    'hora_salida' => $horaSalida->toDateTimeString(),   // Guardar como datetime
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
                'message' => 'Error al registrar: ' . $e->getMessage() . '. Línea: ' . $e->getLine() . '. Archivo: ' . $e->getFile()
            ], 500);
        }
    }

    /**
     * Calcula la asistencia de un estudiante para un período específico (ej. hasta un examen).
     *
     * @param string $numeroDocumento El número de documento del estudiante.
     * @param string $fechaInicio La fecha de inicio del período de cálculo.
     * @param string $fechaExamen La fecha del examen o fin del período de cálculo.
     * @param \App\Models\Ciclo $ciclo El objeto Ciclo para obtener porcentajes de amonestación/inhabilitación.
     * @return array Un array con los detalles de la asistencia.
     */
    private function calcularAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now()->startOfDay();
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->startOfDay();

        // Si el examen aún no ha llegado, calcular hasta hoy para una proyección
        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy : $fechaExamenCarbon;

        // Si la fecha de inicio es futura (para segundo y tercer examen), no calcular aún
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
                'es_proyeccion' => true // Siempre es proyección si el inicio es futuro
            ];
        }

        // Calcular días hábiles totales hasta el examen
        $diasHabilesTotales = $this->contarDiasHabiles($fechaInicio, $fechaExamen);

        // Calcular días hábiles transcurridos hasta hoy o fecha del examen
        $diasHabilesTranscurridos = $this->contarDiasHabiles($fechaInicio, $fechaFinCalculo);

        // Obtener registros de asistencia
        $registrosAsistencia = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                $fechaInicioCarbon->startOfDay(),
                $fechaFinCalculo->endOfDay()
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        // Contar días con asistencia (solo días hábiles)
        $diasConAsistencia = 0;
        foreach ($registrosAsistencia as $fecha) {
            $carbonFecha = Carbon::parse($fecha);
            if ($carbonFecha->isWeekday()) { // Lunes a Viernes
                $diasConAsistencia++;
            }
        }

        $diasFaltaActuales = $diasHabilesTranscurridos - $diasConAsistencia;

        // Para proyección: calcular el porcentaje sobre el total de días hasta el examen
        $porcentajeAsistenciaProyectado = $diasHabilesTotales > 0 ?
            round(($diasConAsistencia / $diasHabilesTotales) * 100, 2) : 0;
        $porcentajeInasistenciaProyectado = 100 - $porcentajeAsistenciaProyectado;

        // Para estado actual: calcular sobre días transcurridos
        $porcentajeAsistenciaActual = $diasHabilesTranscurridos > 0 ?
            round(($diasConAsistencia / $diasHabilesTranscurridos) * 100, 2) : 0;

        // Calcular límites basados en el total de días hasta el examen
        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

        // Determinar estado y mensaje
        $estado = 'regular';
        $mensaje = '';
        $puedeRendir = true;

        // Si el examen ya pasó, usar estado definitivo
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
            // El examen aún no llega, mostrar proyección
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

            // Agregar información de días restantes
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
     * Contar días hábiles entre dos fechas (Lunes a Viernes).
     *
     * @param string $fechaInicio La fecha de inicio.
     * @param string $fechaFin La fecha de fin.
     * @return int El número de días hábiles.
     */
    private function contarDiasHabiles($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $diasHabiles = 0;

        while ($inicio <= $fin) {
            if ($inicio->isWeekday()) { // Lunes a Viernes
                $diasHabiles++;
            }
            $inicio->addDay();
        }

        return $diasHabiles;
    }

    /**
     * Obtener el siguiente día hábil (Lunes a Viernes) a partir de una fecha dada.
     *
     * @param string $fecha La fecha de referencia.
     * @return \Carbon\Carbon La fecha del siguiente día hábil.
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
     * Obtiene la fecha para un día de la semana dado, a partir de hoy o el día más cercano en el futuro.
     * Útil para calcular la fecha real de la próxima clase.
     *
     * @param string $diaSemana El día de la semana en español (ej. 'lunes').
     * @return \Carbon\Carbon La fecha calculada.
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

        // Si el día de la semana objetivo es hoy o en el futuro esta semana
        if ($fecha->dayOfWeek <= $targetDayOfWeek) {
            return $fecha->next($targetDayOfWeek);
        } else {
            // Si el día de la semana objetivo ya pasó esta semana, buscar la próxima semana
            return $fecha->addWeek()->startOfWeek()->next($targetDayOfWeek);
        }
    }

    /**
     * Obtener estadísticas generales de asistencia (para administradores).
     *
     * @param \App\Models\Ciclo $ciclo El ciclo activo.
     * @return array Un array con las estadísticas de asistencia de estudiantes.
     */
    private function obtenerEstadisticasGenerales($ciclo)
    {
        // Obtener todas las inscripciones activas del ciclo
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

            // Obtener primer registro
            $primerRegistro = RegistroAsistencia::where('nro_documento', $estudiante->numero_documento)
                ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
                ->orderBy('fecha_registro')
                ->first();

            if ($primerRegistro) {
                // Calcular asistencia hasta la fecha actual o fin del ciclo
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
