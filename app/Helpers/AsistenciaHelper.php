<?php

namespace App\Helpers;

use App\Models\RegistroAsistencia;
use App\Models\Ciclo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsistenciaHelper
{
    /**
     * Determina el estado de habilitación de un estudiante.
     *
     * @param string $nro_documento
     * @return array
     */
    public static function obtenerEstadoHabilitacion($nro_documento)
    {
        $cicloActivo = Ciclo::where('es_activo', true)->first();

        if (!$cicloActivo) {
            return [
                'estado' => 'desconocido',
                'detalle' => 'Sin ciclo activo',
                'puede_rendir' => true,
                'faltas' => 0,
                'asistencias' => 0,
                'examen' => 'N/A'
            ];
        }

        $examenActivo = self::determinarExamenActivo($cicloActivo);

        if (!$examenActivo) {
            return [
                'estado' => 'regular',
                'detalle' => 'Fuera de periodo de examen',
                'puede_rendir' => true,
                'faltas' => 0,
                'asistencias' => 0,
                'examen' => 'N/A'
            ];
        }

        // Contar asistencias
        $diasAsistidos = RegistroAsistencia::where('nro_documento', $nro_documento)
            ->whereBetween('fecha_registro', [
                $examenActivo['fecha_inicio']->startOfDay(),
                min(now(), $examenActivo['fecha_examen'])->endOfDay()
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->filter(function($item) use ($cicloActivo) {
                return $cicloActivo->esDiaHabil($item->fecha);
            })
            ->count();

        // Calcular días hábiles transcurridos
        $fechaFin = now() < $examenActivo['fecha_examen'] ? now() : $examenActivo['fecha_examen'];
        $diasHabilesTranscurridos = self::contarDiasHabiles($examenActivo['fecha_inicio'], $fechaFin, $cicloActivo);
        
        // Faltas
        $totalFaltas = max(0, $diasHabilesTranscurridos - $diasAsistidos);

        // Límites
        $diasHabilesTotales = self::contarDiasHabiles($examenActivo['fecha_inicio'], $examenActivo['fecha_examen'], $cicloActivo);
        $porcentajeAmonestacion = $cicloActivo->porcentaje_amonestacion ?? 20;
        $porcentajeInhabilitacion = $cicloActivo->porcentaje_inhabilitacion ?? 30;
        
        $limiteAmonestacion = ceil($diasHabilesTotales * ($porcentajeAmonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($porcentajeInhabilitacion / 100));

        $estado = 'regular';
        $puede_rendir = true;
        $detalle = 'HABILITADO';

        if ($totalFaltas >= $limiteInhabilitacion) {
            $estado = 'inhabilitado';
            $puede_rendir = false;
            $detalle = 'INHABILITADO';
        } elseif ($totalFaltas >= $limiteAmonestacion) {
            $estado = 'amonestado';
            $puede_rendir = true;
            $detalle = 'AMONESTADO (Habilitado para Examen)';
        } else {
            $estado = 'regular';
            $puede_rendir = true;
            $detalle = 'HABILITADO PARA EXAMEN';
        }

        return [
            'estado' => $estado,
            'detalle' => $detalle,
            'puede_rendir' => $puede_rendir,
            'faltas' => $totalFaltas,
            'asistencias' => $diasAsistidos,
            'examen' => $examenActivo['nombre'],
            'limite_amonestacion' => $limiteAmonestacion,
            'limite_inhabilitacion' => $limiteInhabilitacion,
            'dias_habiles_totales' => $diasHabilesTotales,
            'faltas_para_amonestacion' => max(0, $limiteAmonestacion - $totalFaltas),
            'faltas_para_inhabilitacion' => max(0, $limiteInhabilitacion - $totalFaltas),
        ];
    }

    private static function determinarExamenActivo($ciclo)
    {
        $hoy = Carbon::now();
        
        if ($ciclo->fecha_primer_examen && $hoy <= $ciclo->fecha_primer_examen) {
            return [
                'nombre' => 'Primer Examen',
                'fecha_inicio' => $ciclo->fecha_inicio,
                'fecha_examen' => $ciclo->fecha_primer_examen
            ];
        }
        
        if ($ciclo->fecha_segundo_examen && $hoy <= $ciclo->fecha_segundo_examen) {
            $inicioSegundo = self::getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo);
            return [
                'nombre' => 'Segundo Examen',
                'fecha_inicio' => $inicioSegundo,
                'fecha_examen' => $ciclo->fecha_segundo_examen
            ];
        }
        
        if ($ciclo->fecha_tercer_examen && $hoy <= $ciclo->fecha_tercer_examen) {
            $inicioTercero = self::getSiguienteDiaHabil($ciclo->fecha_segundo_examen, $ciclo);
            return [
                'nombre' => 'Tercer Examen',
                'fecha_inicio' => $inicioTercero,
                'fecha_examen' => $ciclo->fecha_tercer_examen
            ];
        }
        
        return null;
    }

    private static function getSiguienteDiaHabil($fecha, $ciclo)
    {
        $siguiente = Carbon::parse($fecha)->addDay();
        while (!$ciclo->esDiaHabil($siguiente)) {
            $siguiente->addDay();
        }
        return $siguiente;
    }

    private static function contarDiasHabiles($fechaInicio, $fechaFin, $ciclo)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $diasHabiles = 0;

        while ($inicio <= $fin) {
            if ($ciclo->esDiaHabil($inicio)) {
                $diasHabiles++;
            }
            $inicio->addDay();
        }

        return $diasHabiles;
    }
}
