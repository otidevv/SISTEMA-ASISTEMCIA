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

        // Obtener el primer registro de asistencia del estudiante dentro de este ciclo
        // Esto es crucial para no penalizar a los que se inscribieron tarde
        $primerRegistro = RegistroAsistencia::where('nro_documento', $nro_documento)
            ->where('fecha_registro', '>=', $cicloActivo->fecha_inicio)
            ->where('fecha_registro', '<=', $cicloActivo->fecha_fin)
            ->orderBy('fecha_registro')
            ->first();

        $fechaInicioConteo = Carbon::parse($examenActivo['fecha_inicio'])->startOfDay();

        // Si es el primer examen, empezamos a contar desde su primer registro si este es después del inicio del ciclo
        if ($examenActivo['nombre'] === 'Primer Examen' && $primerRegistro) {
            $fechaPrimerRegistro = Carbon::parse($primerRegistro->fecha_registro)->startOfDay();
            if ($fechaPrimerRegistro->gt($fechaInicioConteo)) {
                $fechaInicioConteo = $fechaPrimerRegistro;
            }
        }

        // Contar asistencias en el periodo activo
        $diasAsistidos = RegistroAsistencia::where('nro_documento', $nro_documento)
            ->whereBetween('fecha_registro', [
                $fechaInicioConteo->copy()->startOfDay(),
                min(now(), Carbon::parse($examenActivo['fecha_examen']))->endOfDay()
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->filter(function($item) use ($cicloActivo) {
                return $cicloActivo->esDiaHabil($item->fecha);
            })
            ->count();

        // Calcular días hábiles transcurridos desde el inicio del periodo (o primer registro) hasta hoy
        $fechaExamenCarbon = Carbon::parse($examenActivo['fecha_examen'])->startOfDay();
        $fechaFinCalculo = now() < $fechaExamenCarbon ? now() : $fechaExamenCarbon;
        
        // Si la fecha de inicio es futura, no hay faltas aún
        if ($fechaInicioConteo->gt(now())) {
            $totalFaltas = 0;
            $diasHabilesTranscurridos = 0;
        } else {
            $diasHabilesTranscurridos = self::contarDiasHabiles($fechaInicioConteo, $fechaFinCalculo, $cicloActivo);
            $totalFaltas = max(0, $diasHabilesTranscurridos - $diasAsistidos);
        }

        // Límites basados en los días hábiles TOTALES del periodo del examen
        // Los límites siempre se calculan sobre el total del periodo para mantener coherencia
        $diasHabilesTotalesPeriodo = self::contarDiasHabiles($fechaInicioConteo, $fechaExamenCarbon, $cicloActivo);
        
        $porcentajeAmonestacion = $cicloActivo->porcentaje_amonestacion ?? 20;
        $porcentajeInhabilitacion = $cicloActivo->porcentaje_inhabilitacion ?? 30;
        
        $limiteAmonestacion = ceil($diasHabilesTotalesPeriodo * ($porcentajeAmonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotalesPeriodo * ($porcentajeInhabilitacion / 100));

        // Asegurar que si hay 0 faltas, el estado es siempre regular
        if ($totalFaltas == 0) {
            $estado = 'regular';
            $puede_rendir = true;
            $detalle = 'HABILITADO PARA EXAMEN';
        } elseif ($limiteInhabilitacion > 0 && $totalFaltas >= $limiteInhabilitacion) {
            $estado = 'inhabilitado';
            $puede_rendir = false;
            $detalle = 'INHABILITADO';
        } elseif ($limiteAmonestacion > 0 && $totalFaltas >= $limiteAmonestacion) {
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
            'dias_habiles_totales' => $diasHabilesTotalesPeriodo,
            'faltas_para_amonestacion' => max(0, $limiteAmonestacion - $totalFaltas),
            'faltas_para_inhabilitacion' => max(0, $limiteInhabilitacion - $totalFaltas),
            'fecha_inicio_periodo' => $fechaInicioConteo->toDateString(),
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
