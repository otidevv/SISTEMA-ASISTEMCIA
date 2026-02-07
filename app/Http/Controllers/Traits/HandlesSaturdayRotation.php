<?php

namespace App\Http\Controllers\Traits;

use App\Models\Ciclo;
use Carbon\Carbon;

trait HandlesSaturdayRotation
{
    /**
     * Mapeo de días de la semana (Carbon dayOfWeek) a nombres con primera letra mayúscula
     * para coincidir con la base de datos
     */
    private static $diasSemana = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado'
    ];

    /**
     * Obtener día de horario considerando rotación de sábado
     * 
     * @param mixed $fecha Fecha a consultar
     * @param Ciclo|null $ciclo Ciclo activo (opcional, se obtiene automáticamente si no se proporciona)
     * @return string Día de la semana a usar para buscar horario (con primera mayúscula)
     */
    protected function getDiaHorarioParaFecha($fecha, $ciclo = null)
    {
        if (!$ciclo) {
            $ciclo = Ciclo::where('es_activo', true)->first();
        }
        
        if (!$ciclo) {
            // Si no hay ciclo activo, retornar el día real con primera mayúscula
            $fechaCarbon = Carbon::parse($fecha);
            return self::$diasSemana[$fechaCarbon->dayOfWeek];
        }
        
        return $ciclo->getDiaHorarioParaFecha($fecha);
    }

    /**
     * Obtener información de rotación para mostrar en vistas
     * 
     * @param mixed $fecha Fecha a consultar
     * @param Ciclo|null $ciclo Ciclo activo (opcional)
     * @return array ['dia_real' => string, 'dia_horario' => string, 'es_sabado' => bool, 'semana' => int|null]
     */
    protected function getInfoRotacion($fecha, $ciclo = null)
    {
        if (!$ciclo) {
            $ciclo = Ciclo::where('es_activo', true)->first();
        }
        
        $fechaCarbon = Carbon::parse($fecha);
        $dayOfWeek = $fechaCarbon->dayOfWeek;
        $diaReal = self::$diasSemana[$dayOfWeek]; // Con primera mayúscula
        $esSabado = $dayOfWeek === 6; // Carbon: 6 = Sábado
        
        $info = [
            'dia_real' => $diaReal,
            'dia_horario' => $diaReal, // Por defecto igual al día real
            'es_sabado' => $esSabado,
            'semana' => null
        ];
        
        // Si es sábado y el ciclo incluye sábados rotativos
        if ($esSabado && $ciclo && $ciclo->incluye_sabados) {
            $diaEquivalente = $ciclo->getDiaEquivalenteSabado($fecha);
            if ($diaEquivalente) {
                $info['dia_horario'] = $diaEquivalente; // Día equivalente (Lunes, Martes, etc.)
                $info['semana'] = $ciclo->getNumeroSemana($fecha);
            }
        }
        
        return $info;
    }

    /**
     * Determinar si le toca trabajar al docente en un sábado específico
     * Los docentes trabajan en sábados rotativos si tienen horarios para el día equivalente
     * 
     * @param int $docenteId ID del docente
     * @param mixed $fecha Fecha del sábado a verificar
     * @param Ciclo|null $ciclo Ciclo activo (opcional)
     * @return bool True si le toca trabajar ese sábado
     */
    protected function leTocaSabadoAlDocente($docenteId, $fecha, $ciclo = null)
    {
        if (!$ciclo) {
            $ciclo = Ciclo::where('es_activo', true)->first();
        }
        
        if (!$ciclo || !$ciclo->incluye_sabados) {
            return false; // Sin ciclo activo o sin sábados rotativos habilitados
        }
        
        $fechaCarbon = Carbon::parse($fecha);
        
        // Si no es sábado, retornar true (no aplica rotación de sábados)
        if ($fechaCarbon->dayOfWeek !== 6) {
            return true;
        }
        
        // Obtener el día equivalente según la rotación (Lunes, Martes, etc.)
        $diaEquivalente = $ciclo->getDiaEquivalenteSabado($fecha);
        
        if (!$diaEquivalente) {
            return false; // No hay día equivalente definido
        }
        
        // Verificar si el docente tiene horarios para ese día equivalente
        // Los docentes trabajan el sábado si tienen clases programadas para el día equivalente
        $tieneHorarioEquivalente = \App\Models\HorarioDocente::where('docente_id', $docenteId)
            ->where('dia_semana', $diaEquivalente)
            ->where('ciclo_id', $ciclo->id)
            ->exists();
        
        return $tieneHorarioEquivalente;
    }
}

