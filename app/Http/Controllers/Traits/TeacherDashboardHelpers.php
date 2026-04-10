<?php

namespace App\Http\Controllers\Traits;

use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Models\PagoDocente;
use App\Models\Ciclo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait TeacherDashboardHelpers
{
    /**
     * Obtiene la tarifa del docente desde la base de datos
     */
    protected function obtenerTarifaDocente($docenteId, $cicloActivo = null, $fechaReferencia = null)
    {
        $fechaReferencia = $fechaReferencia ? Carbon::parse($fechaReferencia) : Carbon::now();

        if (!$cicloActivo) {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
        }

        if ($cicloActivo) {
            // Priorizar tarifa asociada explícitamente al ciclo
            $pagoDocente = PagoDocente::where('docente_id', $docenteId)
                ->where('ciclo_id', $cicloActivo->id)
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            // Fallback por rango de fechas
            if (!$pagoDocente) {
                $pagoDocente = PagoDocente::where('docente_id', $docenteId)
                    ->whereDate('fecha_inicio', '<=', $fechaReferencia)
                    ->where(function ($query) use ($fechaReferencia) {
                        $query->whereDate('fecha_fin', '>=', $fechaReferencia)
                              ->orWhereNull('fecha_fin');
                    })
                    ->orderBy('fecha_inicio', 'desc')
                    ->first();
            }

            if ($pagoDocente) {
                return $pagoDocente->tarifa_por_hora;
            }
        }

        $user = User::find($docenteId);
        return $user->tarifa_por_hora ?? 25.00;
    }

    /**
     * Calcula información de tiempo para una clase
     */
    protected function calcularInfoTiempo($horaInicio, $horaFin, $momentoActual, $fechaSeleccionada)
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
     * Calcular eficiencia y puntualidad del docente
     */
    protected function calcularEficienciaYPuntualidad($docenteId, $cicloActivo)
    {
        $asistencias = AsistenciaDocente::where('docente_id', $docenteId)
            ->when($cicloActivo, function ($query, $ciclo) {
                return $query->whereHas('horario', function ($q) use ($ciclo) {
                    $q->where('ciclo_id', $ciclo->id);
                });
            })
            ->where('estado', 'completada')
            ->get();

        if ($asistencias->isEmpty()) {
            return ['eficiencia' => 0, 'puntualidad' => 0];
        }

        $totalHorasProgramadas = $asistencias->sum(function ($asistencia) {
            $horario = $asistencia->horario;
            if (!$horario) return 0;
            $inicio = Carbon::parse($horario->hora_inicio);
            $fin = Carbon::parse($horario->hora_fin);
            return $inicio->diffInMinutes($fin) / 60;
        });

        $totalHorasReales = $asistencias->sum('horas_dictadas');
        $eficiencia = $totalHorasProgramadas > 0 ? round(($totalHorasReales / $totalHorasProgramadas) * 100) : 0;

        $sesionesPuntuales = $asistencias->filter(function ($asistencia) {
            return ($asistencia->minutos_tardanza_entrada ?? 0) == 0;
        })->count();

        $puntualidad = $asistencias->count() > 0 ? round(($sesionesPuntuales / $asistencias->count()) * 100) : 0;

        return [
            'eficiencia' => min($eficiencia, 100),
            'puntualidad' => $puntualidad
        ];
    }

    /**
     * Calcular tendencia semanal
     */
    protected function calcularTendenciaSemanal($docenteId)
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
     * Obtener próxima clase considerando rotación de sábados
     */
    protected function obtenerProximaClaseCorregida($docenteId, $cicloActivo)
    {
        $ahora = Carbon::now();
        $diaActualSemana = $ahora->locale('es')->dayName;

        $proximaClaseHoy = HorarioDocente::where('docente_id', $docenteId)
            ->where('dia_semana', $diaActualSemana)
            ->where('hora_inicio', '>', $ahora->format('H:i:s'))
            ->when($cicloActivo, function ($query, $ciclo) {
                return $query->where('ciclo_id', $ciclo->id);
            })
            ->with(['aula', 'curso'])
            ->orderBy('hora_inicio')
            ->first();

        if ($proximaClaseHoy) {
            return $proximaClaseHoy;
        }

        $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        $diaActualIndex = array_search(strtolower($diaActualSemana), $diasSemana);
        
        for ($i = 1; $i <= 14; $i++) {
            $indexDia = ($diaActualIndex + $i) % 7;
            $diaBuscado = $diasSemana[$indexDia];
            $fechaBuscada = $ahora->copy()->addDays($i);
            
            if ($diaBuscado === 'sábado') {
                if (method_exists($this, 'leTocaSabadoAlDocente')) {
                    $leTocaSabado = $this->leTocaSabadoAlDocente($docenteId, $fechaBuscada, $cicloActivo);
                    if (!$leTocaSabado) continue;
                }
            }
            
            $claseEncontrada = HorarioDocente::where('docente_id', $docenteId)
                ->where('dia_semana', $diaBuscado)
                ->when($cicloActivo, function ($query, $ciclo) {
                    return $query->where('ciclo_id', $ciclo->id);
                })
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
     * Sistema de notificaciones para docentes
     */
    protected function generarNotificacionesDocente($docenteId, $fechaSeleccionada, $sesionesPendientes, $proximaClase, $cicloActivo)
    {
        $notificaciones = [];

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

        if ($proximaClase) {
            $horaProximaClase = Carbon::parse($proximaClase->hora_inicio);
            $diaProximaClase = $this->calcularFechaProximaClase($proximaClase->dia_semana);

            if ($diaProximaClase->isToday()) {
                $ahora = Carbon::now();
                $horaCompleta = $diaProximaClase->copy()->setTime($horaProximaClase->hour, $horaProximaClase->minute);
                $minutosHastaProxima = $ahora->diffInMinutes($horaCompleta, false);
                
                if ($minutosHastaProxima >= 0 && $minutosHastaProxima <= 300) {
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

        $prioridadOrden = ['critica' => 1, 'alta' => 2, 'media' => 3, 'baja' => 4];
        usort($notificaciones, function($a, $b) use ($prioridadOrden) {
            return $prioridadOrden[$a['prioridad']] <=> $prioridadOrden[$b['prioridad']];
        });

        return $notificaciones;
    }

    /**
     * Generar recordatorios
     */
    protected function generarRecordatorios($sesionesPendientes, $proximaClase, $fechaSeleccionada)
    {
        $recordatorios = [];
        
        if ($sesionesPendientes > 0) {
            $recordatorios[] = [
                'tipo' => 'warning',
                'mensaje' => "{$sesionesPendientes} sesión" . ($sesionesPendientes > 1 ? 'es' : '') . " pendiente" . ($sesionesPendientes > 1 ? 's' : '') . " de completar el tema para el " . $fechaSeleccionada->locale('es')->isoFormat('D [de] MMMM') . ".",
            ];
        }
        
        if ($proximaClase) {
            $diaProximaClase = $this->calcularFechaProximaClase($proximaClase->dia_semana);
            $horaProximaClase = Carbon::parse($proximaClase->hora_inicio);

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
     * Calcular fecha de próxima clase
     */
    protected function calcularFechaProximaClase($diaSemana)
    {
        $diasMap = [
            'lunes' => 1, 'martes' => 2, 'miércoles' => 3, 'jueves' => 4,
            'viernes' => 5, 'sábado' => 6, 'domingo' => 0
        ];
        
        $targetDay = $diasMap[strtolower($diaSemana)];
        $ahora = Carbon::now();
        $daysUntilTarget = ($targetDay - $ahora->dayOfWeek + 7) % 7;
        
        if ($daysUntilTarget == 0) {
            $daysUntilTarget = 7;
        }
        
        return $ahora->addDays($daysUntilTarget);
    }
}
