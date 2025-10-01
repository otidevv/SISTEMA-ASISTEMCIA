<?php

namespace App\Http\Controllers\Traits;

use App\Models\AsistenciaDocente;
use App\Models\PagoDocente;
use Carbon\Carbon;

trait ProcessesTeacherSessions
{
    // Tolerancias (unificadas)
    const TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS = 15;
    const TOLERANCIA_ENTRADA_TARDE_MINUTOS = 5;
    const TOLERANCIA_VENTANA_ENTRADA_MINUTOS = 120;
    const TOLERANCIA_VENTANA_SALIDA_MINUTOS = 60;
    const TOLERANCIA_SALIDA_ANTICIPADA_MINUTOS = 15;

    /**
     * Procesa una sesión de un docente para calcular estado, duración y pago.
     *
     * @param \App\Models\HorarioDocente $horario
     * @param \Carbon\Carbon $currentDate
     * @param \Illuminate\Support\Collection $registrosBiometricosDelDia
     * @param \App\Models\User $docente
     * @return array|null
     */
    public function processTeacherSessionLogic($horario, $currentDate, $registrosBiometricosDelDia, $docente)
    {
        if (!$horario || !$horario->hora_inicio || !$horario->hora_fin) {
            return null;
        }

        $horaInicioProgramada = Carbon::parse($horario->hora_inicio);
        $horaFinProgramada = Carbon::parse($horario->hora_fin);

        $horarioInicioHoy = $currentDate->copy()->setTime($horaInicioProgramada->hour, $horaInicioProgramada->minute, $horaInicioProgramada->second);
        $horarioFinHoy = $currentDate->copy()->setTime($horaFinProgramada->hour, $horaFinProgramada->minute, $horaFinProgramada->second);

        // Búsqueda de registros biométricos
        $entradaBiometrica = $registrosBiometricosDelDia
            ->filter(function($r) use ($horarioInicioHoy) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioInicioHoy->copy()->subMinutes(self::TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS),
                    $horarioInicioHoy->copy()->addMinutes(self::TOLERANCIA_VENTANA_ENTRADA_MINUTOS)
                );
            })
            ->sortBy('fecha_registro')
            ->first();

        $salidaBiometrica = $registrosBiometricosDelDia
            ->filter(function($r) use ($horarioFinHoy) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioFinHoy->copy()->subMinutes(self::TOLERANCIA_SALIDA_ANTICIPADA_MINUTOS),
                    $horarioFinHoy->copy()->addMinutes(self::TOLERANCIA_VENTANA_SALIDA_MINUTOS)
                );
            })
            ->sortByDesc('fecha_registro')
            ->first();

        // Tema desarrollado
        $asistenciaDocenteProcesada = AsistenciaDocente::where('docente_id', $docente->id)
            ->where('horario_id', $horario->id)
            ->whereDate('fecha_hora', $currentDate->toDateString())
            ->first();
        $temaDesarrollado = $asistenciaDocenteProcesada->tema_desarrollado ?? 'Pendiente';

        // Inicialización de variables
        $horasDictadas = 0;
        $estadoTexto = 'PENDIENTE';
        $minutosTardanza = 0;

        // Lógica de estado y cálculo de horas
        if ($entradaBiometrica && $salidaBiometrica) {
            $estadoTexto = 'COMPLETADA';
            $entradaCarbon = Carbon::parse($entradaBiometrica->fecha_registro);
            $salidaCarbon = Carbon::parse($salidaBiometrica->fecha_registro);

            // Determinar la hora de inicio efectiva para el cálculo, respetando la tolerancia de tardanza.
            $tardinessThreshold = $horarioInicioHoy->copy()->addMinutes(self::TOLERANCIA_ENTRADA_TARDE_MINUTOS);
            
            $effectiveStartTime;
            // Si la entrada es ANTES o DENTRO del umbral de tardanza, se usa la hora de inicio programada.
            if ($entradaCarbon->lessThanOrEqualTo($tardinessThreshold)) {
                $effectiveStartTime = $horarioInicioHoy;
            } else {
                // Si la entrada es DESPUÉS del umbral, se usa la hora de entrada real (se aplica descuento).
                $effectiveStartTime = $entradaCarbon;
            }

            // El fin efectivo es el más temprano entre la hora programada y la hora de salida.
            $finEfectivo = $salidaCarbon->min($horarioFinHoy);

            if ($finEfectivo > $effectiveStartTime) {
                $duracionBruta = $effectiveStartTime->diffInMinutes($finEfectivo);

                // Descuento de recesos
                $recesoMananaInicio = $currentDate->copy()->setTime(10, 0, 0);
                $recesoMananaFin = $currentDate->copy()->setTime(10, 30, 0);
                $minutosRecesoManana = 0;
                if ($effectiveStartTime < $recesoMananaFin && $finEfectivo > $recesoMananaInicio) {
                    $superposicionInicio = $effectiveStartTime->max($recesoMananaInicio);
                    $superposicionFin = $finEfectivo->min($recesoMananaFin);
                    if ($superposicionFin > $superposicionInicio) {
                        $minutosRecesoManana = $superposicionInicio->diffInMinutes($superposicionFin);
                    }
                }

                $recesoTardeInicio = $currentDate->copy()->setTime(18, 0, 0);
                $recesoTardeFin = $currentDate->copy()->setTime(18, 30, 0);
                $minutosRecesoTarde = 0;
                if ($effectiveStartTime < $recesoTardeFin && $finEfectivo > $recesoTardeInicio) {
                    $superposicionInicio = $effectiveStartTime->max($recesoTardeInicio);
                    $superposicionFin = $finEfectivo->min($recesoTardeFin);
                    if ($superposicionFin > $superposicionInicio) {
                        $minutosRecesoTarde = $superposicionInicio->diffInMinutes($superposicionFin);
                    }
                }

                $minutosNetos = $duracionBruta - $minutosRecesoManana - $minutosRecesoTarde;
                $horasDictadas = round(max(0, $minutosNetos) / 60, 2);
            }
        } elseif ($entradaBiometrica && !$salidaBiometrica) {
            if ($currentDate->isPast() || ($currentDate->isToday() && Carbon::now()->greaterThan($horarioFinHoy))) {
                $estadoTexto = 'INCOMPLETA';
            } else {
                $estadoTexto = 'EN CURSO';
            }
        } elseif (!$entradaBiometrica && !$salidaBiometrica) {
            if ($currentDate->isPast() || ($currentDate->isToday() && Carbon::now()->greaterThan($horarioFinHoy))) {
                $estadoTexto = 'FALTA';
            } else {
                $estadoTexto = 'PROGRAMADA';
            }
        }

        // Cálculo de tardanza
        if ($entradaBiometrica) {
            $horaEntradaReal = Carbon::parse($entradaBiometrica->fecha_registro);
            $tolerancia = $horarioInicioHoy->copy()->addMinutes(self::TOLERANCIA_ENTRADA_TARDE_MINUTOS);
            if ($horaEntradaReal->gt($tolerancia)) {
                $minutosTardanza = $horaEntradaReal->diffInMinutes($tolerancia);
            }
        }

        // Cálculo de pago
        $montoTotal = 0;
        $pagoDocente = PagoDocente::where('docente_id', $docente->id)
            ->whereDate('fecha_inicio', '<=', $currentDate)
            ->whereDate('fecha_fin', '>=', $currentDate)
            ->first();
        
        if ($pagoDocente) {
            $montoTotal = $horasDictadas * $pagoDocente->tarifa_por_hora;
        }

        return [
            // Datos básicos
            'horario' => $horario,
            'fecha' => $currentDate->toDateString(),
            'curso' => $horario->curso->nombre ?? 'N/A',
            'aula' => $horario->aula->nombre ?? 'N/A',
            'turno' => $horario->turno ?? 'N/A',
            'tema_desarrollado' => $temaDesarrollado,
            
            // Estado y registros
            'estado_sesion' => $estadoTexto,
            'hora_entrada' => $entradaBiometrica ? Carbon::parse($entradaBiometrica->fecha_registro)->format('g:i A') : '--',
            'hora_salida' => $salidaBiometrica ? Carbon::parse($salidaBiometrica->fecha_registro)->format('g:i A') : '--',
            'tiene_registros' => $entradaBiometrica && $salidaBiometrica,

            // Cálculos de tiempo y pago
            'horas_dictadas' => $horasDictadas,
            'pago' => $montoTotal,
            'minutos_tardanza' => $minutosTardanza,

            // Datos para ordenamiento y agrupación
            'year' => $currentDate->year,
            'month_number' => $currentDate->month,
            'day_number' => $currentDate->day,
            'mes' => $currentDate->locale('es')->monthName,
            'semana' => $currentDate->weekOfYear,
        ];
    }
}
