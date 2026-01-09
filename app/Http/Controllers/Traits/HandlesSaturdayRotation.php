<?php

namespace App\Http\Controllers\Traits;

use App\Models\Ciclo;
use Carbon\Carbon;

trait HandlesSaturdayRotation
{
    /**
     * Obtener día de horario considerando rotación de sábado
     * 
     * @param mixed $fecha Fecha a consultar
     * @param Ciclo|null $ciclo Ciclo activo (opcional, se obtiene automáticamente si no se proporciona)
     * @return string Día de la semana a usar para buscar horario
     */
    protected function getDiaHorarioParaFecha($fecha, $ciclo = null)
    {
        if (!$ciclo) {
            $ciclo = Ciclo::where('es_activo', true)->first();
        }
        
        if (!$ciclo) {
            // Si no hay ciclo activo, retornar el día real
            return strtolower(Carbon::parse($fecha)->translatedFormat('l'));
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
        $diaReal = strtolower($fechaCarbon->translatedFormat('l'));
        $esSabado = $diaReal === 'sábado';
        
        $info = [
            'dia_real' => $diaReal,
            'dia_horario' => $diaReal,
            'es_sabado' => $esSabado,
            'semana' => null
        ];
        
        if ($esSabado && $ciclo) {
            $info['dia_horario'] = $ciclo->getDiaEquivalenteSabado($fecha);
            $info['semana'] = $ciclo->getNumeroSemana($fecha);
        }
        
        return $info;
    }
}
