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
    public static function obtenerEstadoHabilitacion($nro_documento, $ciclo = null)
    {
        if (!$ciclo) {
            // Intentar encontrar el ciclo de la inscripción activa del estudiante
            $inscripcion = \App\Models\Inscripcion::whereHas('estudiante', function ($q) use ($nro_documento) {
                $q->where('numero_documento', $nro_documento);
            })
            ->whereHas('ciclo', function ($q) {
                $q->where('es_activo', true);
            })
            ->with('ciclo')
            ->first();

            if ($inscripcion) {
                $cicloActivo = $inscripcion->ciclo;
            } else {
                // Fallback al último ciclo activo si no hay inscripción encontrada
                $cicloActivo = Ciclo::where('es_activo', true)->orderBy('fecha_inicio', 'desc')->first();
            }
        } else {
            $cicloActivo = $ciclo;
        }

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

    public static function determinarExamenActivo($ciclo)
    {
        $hoy = Carbon::now();
        
        // 1. Si estamos antes o en la fecha del primer examen
        if ($ciclo->fecha_primer_examen && $hoy <= Carbon::parse($ciclo->fecha_primer_examen)->endOfDay()) {
            return [
                'id' => 1,
                'nombre' => 'Primer Examen',
                'fecha_inicio' => $ciclo->fecha_inicio,
                'fecha_examen' => $ciclo->fecha_primer_examen
            ];
        }
        
        // 2. Si estamos en el periodo del segundo examen
        if ($ciclo->fecha_segundo_examen && $hoy <= Carbon::parse($ciclo->fecha_segundo_examen)->endOfDay()) {
            $inicioSegundo = self::getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo);
            return [
                'id' => 2,
                'nombre' => 'Segundo Examen',
                'fecha_inicio' => $inicioSegundo,
                'fecha_examen' => $ciclo->fecha_segundo_examen
            ];
        }
        
        // 3. Fallback: Si hay tercer examen, evaluamos ese periodo (o lo fijamos como último si ya pasaron las fechas)
        if ($ciclo->fecha_tercer_examen) {
            $inicioTercero = self::getSiguienteDiaHabil($ciclo->fecha_segundo_examen, $ciclo);
            return [
                'id' => 3,
                'nombre' => 'Tercer Examen',
                'fecha_inicio' => $inicioTercero,
                'fecha_examen' => $ciclo->fecha_tercer_examen
            ];
        }

        // Si solo hay dos exámenes y ya pasó el segundo
        if ($ciclo->fecha_segundo_examen) {
            $inicioSegundo = self::getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo);
            return [
                'id' => 2,
                'nombre' => 'Segundo Examen',
                'fecha_inicio' => $inicioSegundo,
                'fecha_examen' => $ciclo->fecha_segundo_examen
            ];
        }
        
        // Último caso: Primer examen
        return [
            'id' => 1,
            'nombre' => 'Primer Examen',
            'fecha_inicio' => $ciclo->fecha_inicio,
            'fecha_examen' => $ciclo->fecha_primer_examen
        ];
    }

    /**
     * Obtener estadísticas generales de inhabilitados para un ciclo.
     */
    public static function obtenerEstadisticasCiclo($ciclo)
    {
        $inscripciones = \App\Models\Inscripcion::where('ciclo_id', $ciclo->id)
            ->where('estado_inscripcion', 'activo')
            ->get();

        $stats = [
            'total_estudiantes' => $inscripciones->count(),
            'regulares' => 0,
            'amonestados' => 0,
            'inhabilitados' => 0,
            'sin_asistencia' => 0
        ];

        foreach ($inscripciones as $inscripcion) {
            $info = self::obtenerEstadoHabilitacion($inscripcion->estudiante->numero_documento ?? '');
            
            if ($info['estado'] == 'desconocido') {
                $stats['sin_asistencia']++;
            } else {
                // Mapear estado a contador (regular -> regulares, amonestado -> amonestados, inhabilitado -> inhabilitados)
                $key = $info['estado'] == 'regular' ? 'regulares' : ($info['estado'] == 'amonestado' ? 'amonestados' : 'inhabilitados');
                $stats[$key]++;
            }
        }
        
        return [
            'total_estudiantes' => $stats['total_estudiantes'],
            'regulares' => $stats['regulares'],
            'amonestados' => $stats['amonestados'],
            'inhabilitados' => $stats['inhabilitados'],
            'sin_asistencia' => $stats['sin_asistencia'],
            'porcentaje_regulares' => $stats['total_estudiantes'] > 0 ? round(($stats['regulares'] / $stats['total_estudiantes']) * 100, 2) : 0,
            'porcentaje_amonestados' => $stats['total_estudiantes'] > 0 ? round(($stats['amonestados'] / $stats['total_estudiantes']) * 100, 2) : 0,
            'porcentaje_inhabilitados' => $stats['total_estudiantes'] > 0 ? round(($stats['inhabilitados'] / $stats['total_estudiantes']) * 100, 2) : 0
        ];
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
