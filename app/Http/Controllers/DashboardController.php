<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\RegistroAsistencia;
use App\Models\Ciclo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

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
            
            // Obtener horarios del profesor para hoy
            $hoy = Carbon::now()->format('Y-m-d');
            $diaSemana = Carbon::now()->locale('es')->dayName;
            
            $horariosHoy = \App\Models\HorarioDocente::where('docente_id', $user->id)
                ->where('dia_semana', $diaSemana)
                ->with(['aula', 'curso', 'ciclo'])
                ->get();
            
            $data['horariosHoy'] = $horariosHoy;
            $data['sesionesHoy'] = $horariosHoy->count();
            
           // Obtener registros del biométrico de hoy
$registrosHoy = \App\Models\RegistroAsistencia::where('nro_documento', $user->numero_documento)
->whereDate('fecha_registro', $hoy)
->orderBy('fecha_registro')
->get();

$horasHoy = 0;

$horariosHoyConHoras = $horariosHoy->map(function ($horario) use ($registrosHoy, &$horasHoy, $user) {
$horaInicio = Carbon::parse($horario->hora_inicio);
$horaFin = Carbon::parse($horario->hora_fin);

// Entrada válida: entre 15 minutos antes y 30 después del inicio
$entrada = $registrosHoy
    ->filter(fn($r) =>
        Carbon::parse($r->fecha_registro)->format('H:i:s') >= $horaInicio->copy()->subMinutes(15)->format('H:i:s') &&
        Carbon::parse($r->fecha_registro)->format('H:i:s') <= $horaInicio->copy()->addMinutes(30)->format('H:i:s')
    )
    ->sortBy('fecha_registro')
    ->first();

// Salida válida: entre 15 minutos antes y 60 después del final
$salida = $registrosHoy
    ->filter(fn($r) =>
        Carbon::parse($r->fecha_registro)->format('H:i:s') >= $horaFin->copy()->subMinutes(15)->format('H:i:s') &&
        Carbon::parse($r->fecha_registro)->format('H:i:s') <= $horaFin->copy()->addMinutes(60)->format('H:i:s')
    )
    ->sortByDesc('fecha_registro')
    ->first();

// Sumar horas si ambas existen
if ($entrada && $salida) {
    $hEntrada = Carbon::parse($entrada->fecha_registro);
    $hSalida = Carbon::parse($salida->fecha_registro);
    if ($hSalida->greaterThan($hEntrada)) {
        $horasHoy += $hSalida->diffInMinutes($hEntrada) / 60;
    }
}

// Buscar asistencia docente solo para tema desarrollado
$asistencia = \App\Models\AsistenciaDocente::where('docente_id', $user->id)
    ->where('horario_id', $horario->id)
    ->whereDate('fecha_hora', Carbon::today())
    ->first();

return [
    'horario' => $horario,
    'hora_entrada_registrada' => $entrada ? Carbon::parse($entrada->fecha_registro)->format('H:i A') : null,
    'hora_salida_registrada' => $salida ? Carbon::parse($salida->fecha_registro)->format('H:i A') : null,
    'asistencia' => $asistencia
];
});

$data['horasHoy'] = round($horasHoy, 2);
$data['horariosHoyConHoras'] = $horariosHoyConHoras;

            
            // Calcular pago estimado del día
            $pagoEstimadoHoy = \App\Models\AsistenciaDocente::where('docente_id', $user->id)
            ->whereDate('fecha_hora', $hoy)
            ->sum('monto_total');
        
        $data['pagoEstimadoHoy'] = $pagoEstimadoHoy;
        
            
            // Obtener sesiones pendientes (horarios sin asistencia registrada)
            $sesionesPendientes = 0;
foreach ($horariosHoy as $horario) {
    $horaInicio = Carbon::parse($horario->hora_inicio);
    $horaFin = Carbon::parse($horario->hora_fin);

    $hayRegistro = $registrosHoy->filter(function ($r) use ($horaInicio, $horaFin) {
        $hora = Carbon::parse($r->fecha_registro)->format('H:i:s');
        return $hora >= $horaInicio->copy()->subMinutes(15)->format('H:i:s') &&
               $hora <= $horaFin->copy()->addMinutes(60)->format('H:i:s');
    })->count() > 0;

    if (!$hayRegistro && Carbon::now()->greaterThan($horaInicio)) {
        $sesionesPendientes++;
    }
}
$data['sesionesPendientes'] = $sesionesPendientes;

            
            // Resumen semanal (últimos 7 días)
            $fechaInicio = Carbon::now()->subDays(6)->startOfDay();
            $fechaFin = Carbon::now()->endOfDay();
            
            $resumenSemanal = \App\Models\AsistenciaDocente::where('docente_id', $user->id)
                ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
                ->selectRaw('
                    COUNT(*) as total_sesiones,
                    SUM(horas_dictadas) as total_horas,
                    SUM(monto_total) as total_ingresos,
                    AVG(CASE WHEN estado = "completada" THEN 1 ELSE 0 END) * 100 as porcentaje_asistencia
                ')
                ->first();
            
            $data['resumenSemanal'] = [
                'sesiones' => $resumenSemanal->total_sesiones ?? 0,
                'horas' => $resumenSemanal->total_horas ?? 0,
                'ingresos' => $resumenSemanal->total_ingresos ?? 0,
                'asistencia' => round($resumenSemanal->porcentaje_asistencia ?? 0)
            ];
            
            // Próxima clase
            $proximaClase = \App\Models\HorarioDocente::where('docente_id', $user->id)
                ->where(function($query) use ($hoy, $diaSemana) {
                    // Clases de hoy que aún no han comenzado
                    $query->where('dia_semana', $diaSemana)
                          ->where('hora_inicio', '>', Carbon::now()->format('H:i:s'));
                })
                ->orWhere(function($query) use ($diaSemana) {
                    // Clases de días siguientes
                    $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
                    $diaActualIndex = array_search(strtolower($diaSemana), $diasSemana);
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
            
            // Recordatorios
            $recordatorios = [];
            if ($sesionesPendientes > 0) {
                $recordatorios[] = [
                    'tipo' => 'warning',
                    'mensaje' => "{$sesionesPendientes} sesión" . ($sesionesPendientes > 1 ? 'es' : '') . " pendiente" . ($sesionesPendientes > 1 ? 's' : '') . " de completar"
                ];
            }
            
            if ($proximaClase) {
                $horasHastaProxima = Carbon::now()->diffInHours(Carbon::parse($proximaClase->hora_inicio));
                if ($horasHastaProxima <= 5) {
                    $recordatorios[] = [
                        'tipo' => 'info',
                        'mensaje' => "Próxima clase en {$horasHastaProxima} horas"
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
     * Calcular asistencia para un examen específico
     */
    private function calcularAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now()->startOfDay();
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->startOfDay();

        // Si el examen aún no ha llegado, calcular hasta hoy
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
                'es_proyeccion' => $hoy < $fechaExamenCarbon
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
                $mensaje = 'Has superado el 30% de inasistencias. No pudiste rendir este examen.';
                $puedeRendir = false;
            } elseif ($diasFaltaActuales >= $limiteAmonestacion) {
                $estado = 'amonestado';
                $mensaje = 'Superaste el 20% de inasistencias pero pudiste rendir el examen.';
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
                    $mensaje = "Tienes {$diasFaltaActuales} faltas. ¡Cuidado! Solo puedes faltar {$faltasParaInhabilitacion} días más antes de ser inhabilitado.";
                } else {
                    $mensaje = "Tienes {$diasFaltaActuales} faltas. ¡No puedes faltar más o serás inhabilitado!";
                }
            } else {
                $faltasParaAmonestacion = $limiteAmonestacion - $diasFaltaActuales;
                $mensaje = "Tu asistencia va bien. Tienes {$diasFaltaActuales} faltas. Puedes faltar hasta {$faltasParaAmonestacion} días más sin ser amonestado.";
            }

            // Agregar información de días restantes
            $mensaje .= " Quedan {$diasRestantes} días hábiles hasta el examen.";
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
     * Contar días hábiles entre dos fechas (Lunes a Viernes)
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
     * Obtener el siguiente día hábil (Lunes a Viernes)
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
     * Obtener estadísticas generales de asistencia (para administradores)
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
