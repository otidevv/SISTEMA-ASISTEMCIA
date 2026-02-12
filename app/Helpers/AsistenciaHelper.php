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
            $inscripcion = \App\Models\Inscripcion::whereHas('estudiante', function ($q) use ($nro_documento) {
                $q->where('numero_documento', $nro_documento);
            })
            ->whereHas('ciclo', function ($q) {
                $q->where('es_activo', true);
            })
            ->with(['ciclo', 'estudiante'])
            ->first();

            if ($inscripcion) {
                $cicloActivo = $inscripcion->ciclo;
            } else {
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

        $hoy = now();
        $fechaInicioConteo = Carbon::parse($examenActivo['fecha_inicio'])->startOfDay();
        $fechaExamenCarbon = Carbon::parse($examenActivo['fecha_examen'])->endOfDay();

        // Obtener el primer registro de asistencia del estudiante dentro de este ciclo
        $primerRegistro = RegistroAsistencia::where('nro_documento', $nro_documento)
            ->where('fecha_registro', '>=', $cicloActivo->fecha_inicio)
            ->where('fecha_registro', '<=', $cicloActivo->fecha_fin)
            ->orderBy('fecha_registro')
            ->first();

        // Si es el primer examen, ajustamos el inicio a su primera asistencia si fue tardía
        if ($examenActivo['nombre'] === 'Primer Examen' && $primerRegistro) {
            $fechaPrimerRegistro = Carbon::parse($primerRegistro->fecha_registro)->startOfDay();
            if ($fechaPrimerRegistro->gt($fechaInicioConteo)) {
                $fechaInicioConteo = $fechaPrimerRegistro;
            }
        }

        if ($fechaInicioConteo > $hoy) {
            return [
                'estado' => 'regular',
                'detalle' => 'Pendiente',
                'puede_rendir' => true,
                'faltas' => 0,
                'asistencias' => 0,
                'examen' => $examenActivo['nombre'],
                'dias_habiles_totales' => self::contarDiasHabiles($fechaInicioConteo, $fechaExamenCarbon, $cicloActivo),
                'fecha_inicio_periodo' => $fechaInicioConteo->toDateString(),
            ];
        }

        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy->endOfDay() : $fechaExamenCarbon;

        $diasHabilesTotales = self::contarDiasHabiles($fechaInicioConteo, $fechaExamenCarbon, $cicloActivo);
        $diasHabilesTranscurridos = self::contarDiasHabiles($fechaInicioConteo, $fechaFinCalculo, $cicloActivo);

        $registros = RegistroAsistencia::where('nro_documento', $nro_documento)
            ->whereBetween('fecha_registro', [$fechaInicioConteo->copy()->startOfDay(), $fechaFinCalculo])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        $diasConAsistencia = 0;
        foreach ($registros as $fecha) {
            if ($cicloActivo->esDiaHabil(Carbon::parse($fecha))) {
                $diasConAsistencia++;
            }
        }

        $totalFaltas = max(0, $diasHabilesTranscurridos - $diasConAsistencia);
        
        $limiteAmonestacion = ceil($diasHabilesTotales * (($cicloActivo->porcentaje_amonestacion ?? 20) / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * (($cicloActivo->porcentaje_inhabilitacion ?? 30) / 100));

        $estado = 'regular';
        $puede_rendir = true;
        $detalle = 'REGULAR';

        if ($totalFaltas >= $limiteInhabilitacion) {
            $estado = 'inhabilitado';
            $puede_rendir = false;
            $detalle = 'INHABILITADO';
        } elseif ($totalFaltas >= $limiteAmonestacion) {
            $estado = 'amonestado';
            $puede_rendir = true;
            $detalle = 'AMONESTADO';
        }

        return [
            'estado' => $estado,
            'detalle' => $detalle,
            'puede_rendir' => $puede_rendir,
            'faltas' => $totalFaltas,
            'asistencias' => $diasConAsistencia,
            'examen' => $examenActivo['nombre'],
            'limite_amonestacion' => $limiteAmonestacion,
            'limite_inhabilitacion' => $limiteInhabilitacion,
            'dias_habiles_totales' => $diasHabilesTotales,
            'faltas_para_amonestacion' => max(0, $limiteAmonestacion - $totalFaltas),
            'faltas_para_inhabilitacion' => max(0, $limiteInhabilitacion - $totalFaltas),
            'fecha_inicio_periodo' => $fechaInicioConteo->toDateString(),
            'es_proyeccion' => $diasHabilesTranscurridos < $diasHabilesTotales
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
