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
    public static function obtenerEstadoHabilitacion($nro_documento, $ciclo = null, $periodoId = null)
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

        if ($periodoId) {
            $examenActivo = self::getExamenPeriodoPorId($cicloActivo, $periodoId);
        } else {
            $examenActivo = self::determinarExamenActivo($cicloActivo);
        }

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

        // Si no tiene ningún registro en todo el ciclo, lo marcamos como desconocido (Sin datos en Excel)
        if (!$primerRegistro) {
            return [
                'estado' => 'desconocido',
                'detalle' => 'Sin registros biométricos',
                'puede_rendir' => true,
                'faltas' => 0,
                'asistencias' => 0,
                'examen' => $examenActivo['nombre'] ?? 'N/A',
                'dias_habiles_totales' => self::contarDiasHabiles($fechaInicioConteo, $fechaExamenCarbon, $cicloActivo),
                'limite_amonestacion' => 0,
                'limite_inhabilitacion' => 0
            ];
        }

        // Si es el primer examen, ajustamos el inicio a su primera asistencia si fue tardía
        if ($examenActivo['nombre'] === 'Primer Examen') {
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

    /**
     * Calcula la asistencia de un estudiante para un período específico (Reutilizado por Web y API).
     */
    public static function calcularInfoAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now()->startOfDay();
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->startOfDay();

        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy->copy()->endOfDay() : $fechaExamenCarbon->copy()->endOfDay();

        if ($fechaInicioCarbon > $hoy) {
            return [
                'dias_habiles' => self::contarDiasHabiles($fechaInicio, $fechaExamen, $ciclo),
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
                'es_proyeccion' => true
            ];
        }

        $diasHabilesTotales = self::contarDiasHabiles($fechaInicio, $fechaExamen, $ciclo);
        $diasHabilesTranscurridos = self::contarDiasHabiles($fechaInicio, $fechaFinCalculo, $ciclo);

        // Obtener el primer registro de asistencia del estudiante dentro de este ciclo para el primer examen
        $primerRegistroGlobal = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
            ->where('fecha_registro', '<=', $ciclo->fecha_fin)
            ->orderBy('fecha_registro')
            ->first();

        $adjInicio = $fechaInicioCarbon->copy();
        $cicloInicioStr = is_string($ciclo->fecha_inicio) ? $ciclo->fecha_inicio : $ciclo->fecha_inicio->toDateString();
        
        if ($fechaInicioCarbon->toDateString() === $cicloInicioStr && $primerRegistroGlobal) {
            $fechaPrimerReg = Carbon::parse($primerRegistroGlobal->fecha_registro)->startOfDay();
            if ($fechaPrimerReg->gt($adjInicio)) {
                $adjInicio = $fechaPrimerReg;
                // Recalcular días si el ajuste aplica
                $diasHabilesTotales = self::contarDiasHabiles($adjInicio, $fechaExamenCarbon, $ciclo);
                $diasHabilesTranscurridos = self::contarDiasHabiles($adjInicio, $fechaFinCalculo, $ciclo);
            }
        }

        $registrosAsistencia = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                $adjInicio->copy()->startOfDay(),
                $fechaFinCalculo->copy()->endOfDay()
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        $diasConAsistencia = 0;
        foreach ($registrosAsistencia as $fecha) {
            if ($ciclo->esDiaHabil(Carbon::parse($fecha))) {
                $diasConAsistencia++;
            }
        }

        $diasFaltaActuales = max(0, $diasHabilesTranscurridos - $diasConAsistencia);

        $porcentajeAsistenciaProyectado = $diasHabilesTotales > 0 ?
            round(($diasConAsistencia / $diasHabilesTotales) * 100, 2) : 0;
        $porcentajeInasistenciaProyectado = 100 - $porcentajeAsistenciaProyectado;

        $limiteAmonestacion = ceil($diasHabilesTotales * (($ciclo->porcentaje_amonestacion ?? 20) / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * (($ciclo->porcentaje_inhabilitacion ?? 30) / 100));

        $estado = 'regular';
        $mensaje = '';
        $puedeRendir = true;

        if ($hoy >= $fechaExamenCarbon) {
            if ($diasFaltaActuales >= $limiteInhabilitacion) {
                $estado = 'inhabilitado';
                $mensaje = 'Has superado el ' . ($ciclo->porcentaje_inhabilitacion ?? 30) . '% de inasistencias. No pudiste rendir este examen.';
                $puedeRendir = false;
            } elseif ($diasFaltaActuales >= $limiteAmonestacion) {
                $estado = 'amonestado';
                $mensaje = 'Superaste el ' . ($ciclo->porcentaje_amonestacion ?? 20) . '% de inasistencias pero pudiste rendir el examen.';
            } else {
                $mensaje = 'Tu asistencia fue adecuada para este examen.';
            }
        } else {
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

            $mensaje .= " Quedan {$diasRestantes} día" . ($diasRestantes > 1 ? 's' : '') . " hábil" . ($diasRestantes > 1 ? 'es' : '') . " hasta el examen.";
        }

        return [
            'dias_habiles' => $diasHabilesTotales,
            'dias_habiles_transcurridos' => $diasHabilesTranscurridos,
            'dias_asistidos' => $diasConAsistencia,
            'dias_falta' => $diasFaltaActuales,
            'porcentaje_asistencia' => $porcentajeAsistenciaProyectado,
            'limite_amonestacion' => $limiteAmonestacion,
            'limite_inhabilitacion' => $limiteInhabilitacion,
            'estado' => $estado,
            'mensaje' => $mensaje,
            'puede_rendir' => $puedeRendir,
            'es_proyeccion' => $hoy < $fechaExamenCarbon,
            'dias_restantes' => max(0, $diasHabilesTotales - $diasHabilesTranscurridos)
        ];
    }

    public static function getExamenPeriodoPorId($ciclo, $periodoId)
    {
        if ($periodoId == 1) {
            return [
                'id' => 1,
                'nombre' => 'Primer Examen',
                'fecha_inicio' => $ciclo->fecha_inicio,
                'fecha_examen' => $ciclo->fecha_primer_examen
            ];
        }

        if ($periodoId == 2) {
            $inicioSegundo = self::getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo);
            return [
                'id' => 2,
                'nombre' => 'Segundo Examen',
                'fecha_inicio' => $inicioSegundo,
                'fecha_examen' => $ciclo->fecha_segundo_examen
            ];
        }

        if ($periodoId == 3) {
            $inicioTercero = self::getSiguienteDiaHabil($ciclo->fecha_segundo_examen, $ciclo);
            return [
                'id' => 3,
                'nombre' => 'Tercer Examen',
                'fecha_inicio' => $inicioTercero,
                'fecha_examen' => $ciclo->fecha_tercer_examen
            ];
        }

        return self::determinarExamenActivo($ciclo);
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
    /**
     * Obtener estadísticas generales de asistencia de forma optimizada (Batch processing)
     */
    public static function obtenerEstadisticasCiclo($ciclo)
    {
        // 1. Obtener el examen activo
        $examenActivo = self::determinarExamenActivo($ciclo);
        if (!$examenActivo) {
            return self::emptyStats();
        }

        $periodoInicio = Carbon::parse($examenActivo['fecha_inicio'])->startOfDay();
        $periodoExamen = Carbon::parse($examenActivo['fecha_examen'])->endOfDay();
        $ahora = Carbon::now();
        $fechaFinCalculo = $ahora < $periodoExamen ? $ahora->copy()->endOfDay() : $periodoExamen;

        // 2. Obtener inscripciones
        $inscripciones = \App\Models\Inscripcion::where('ciclo_id', $ciclo->id)
            ->where('estado_inscripcion', 'activo')
            ->with('estudiante:id,numero_documento')
            ->get();
        
        $totalEstudiantes = $inscripciones->count();
        if ($totalEstudiantes === 0) {
            return self::emptyStats();
        }

        $documentosInscritos = $inscripciones->pluck('estudiante.numero_documento')->filter()->toArray();

        // 3. Batch fetch first registration in cycle
        $primerasAsistencias = DB::table('registros_asistencia')
            ->whereIn('nro_documento', $documentosInscritos)
            ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
            ->where('fecha_registro', '<=', $ciclo->fecha_fin)
            ->select('nro_documento', DB::raw('MIN(fecha_registro) as first_reg'))
            ->groupBy('nro_documento')
            ->get()
            ->pluck('first_reg', 'nro_documento')
            ->toArray();

        // 4. Batch fetch attendance counts in current period
        $asistenciasCount = DB::table('registros_asistencia')
            ->whereIn('nro_documento', $documentosInscritos)
            ->where('fecha_registro', '>=', $periodoInicio)
            ->where('fecha_registro', '<=', $fechaFinCalculo)
            ->select('nro_documento', DB::raw('COUNT(DISTINCT DATE(fecha_registro)) as total'))
            ->groupBy('nro_documento')
            ->get()
            ->pluck('total', 'nro_documento')
            ->toArray();

        // 5. Pre-calcular mapa de días hábiles acumulativos para el ciclo
        $cycleStart = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
        $cycleEnd = Carbon::parse($ciclo->fecha_fin)->endOfDay();
        $cumulativeBusinessDays = [];
        $count = 0;
        $temp = $cycleStart->copy();
        while ($temp <= $cycleEnd) {
            if ($ciclo->esDiaHabil($temp)) {
                $count++;
            }
            $cumulativeBusinessDays[$temp->toDateString()] = $count;
            $temp->addDay();
        }

        // 6. Calcular estadísticas en memoria
        $estudiantesRegulares = 0;
        $estudiantesAmonestados = 0;
        $estudiantesInhabilitados = 0;
        $estudiantesSinRegistros = 0;

        $isPrimerExamen = ($examenActivo['nombre'] === 'Primer Examen');
        $porcentajeAmonestacion = $ciclo->porcentaje_amonestacion ?? 20;
        $porcentajeInhabilitacion = $ciclo->porcentaje_inhabilitacion ?? 30;

        foreach ($inscripciones as $inscripcion) {
            $doc = $inscripcion->estudiante->numero_documento ?? '';
            
            if (empty($doc) || !isset($primerasAsistencias[$doc])) {
                $estudiantesSinRegistros++;
                continue;
            }

            $currentInicioConteo = $periodoInicio->copy();
            if ($isPrimerExamen) {
                $fechaPrimerRegistro = Carbon::parse($primerasAsistencias[$doc])->startOfDay();
                if ($fechaPrimerRegistro->gt($currentInicioConteo)) {
                    $currentInicioConteo = $fechaPrimerRegistro;
                }
            }

            if ($currentInicioConteo > $ahora) {
                $estudiantesRegulares++;
                continue;
            }

            $diasHabilesTotales = self::getBusinessDaysCountStatic($currentInicioConteo, $periodoExamen, $cumulativeBusinessDays, $cycleStart, $cycleEnd);
            $diasHabilesTranscurridos = self::getBusinessDaysCountStatic($currentInicioConteo, $fechaFinCalculo, $cumulativeBusinessDays, $cycleStart, $cycleEnd);
            
            $diasConAsistencia = $asistenciasCount[$doc] ?? 0;
            $totalFaltas = max(0, $diasHabilesTranscurridos - $diasConAsistencia);
            
            $limiteAmonestacion = ceil($diasHabilesTotales * ($porcentajeAmonestacion / 100));
            $limiteInhabilitacion = ceil($diasHabilesTotales * ($porcentajeInhabilitacion / 100));

            if ($totalFaltas >= $limiteInhabilitacion) {
                $estudiantesInhabilitados++;
            } elseif ($totalFaltas >= $limiteAmonestacion) {
                $estudiantesAmonestados++;
            } else {
                $estudiantesRegulares++;
            }
        }
        
        return [
            'total_estudiantes' => $totalEstudiantes,
            'regulares' => $estudiantesRegulares,
            'amonestados' => $estudiantesAmonestados,
            'inhabilitados' => $estudiantesInhabilitados,
            'sin_asistencia' => $estudiantesSinRegistros,
            'porcentaje_regulares' => $totalEstudiantes > 0 ? round(($estudiantesRegulares / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_amonestados' => $totalEstudiantes > 0 ? round(($estudiantesAmonestados / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_inhabilitados' => $totalEstudiantes > 0 ? round(($estudiantesInhabilitados / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_sin_asistencia' => $totalEstudiantes > 0 ? round(($estudiantesSinRegistros / $totalEstudiantes) * 100, 2) : 0
        ];
    }

    private static function getBusinessDaysCountStatic($from, $to, $cumulativeMap, $cycleStart, $cycleEnd)
    {
        $f = $from->copy()->startOfDay();
        $t = $to->copy()->startOfDay();
        
        if ($f < $cycleStart) $f = $cycleStart->copy();
        if ($t > $cycleEnd) $t = $cycleEnd->copy();
        if ($f > $t) return 0;

        $toStr = $t->toDateString();
        $countTo = $cumulativeMap[$toStr] ?? 0;
        
        $countFromBefore = 0;
        $fromBefore = $f->copy()->subDay();
        if ($fromBefore >= $cycleStart) {
            $countFromBefore = $cumulativeMap[$fromBefore->toDateString()] ?? 0;
        }
        
        return max(0, $countTo - $countFromBefore);
    }

    private static function emptyStats()
    {
        return [
            'total_estudiantes' => 0,
            'regulares' => 0,
            'amonestados' => 0,
            'inhabilitados' => 0,
            'sin_asistencia' => 0,
            'porcentaje_regulares' => 0,
            'porcentaje_amonestados' => 0,
            'porcentaje_inhabilitados' => 0,
            'porcentaje_sin_asistencia' => 0
        ];
    }

    public static function getSiguienteDiaHabil($fecha, $ciclo)
    {
        if (!$fecha) return null;
        $siguiente = Carbon::parse($fecha)->addDay();
        while (!$ciclo->esDiaHabil($siguiente)) {
            $siguiente->addDay();
        }
        return $siguiente;
    }

    public static function contarDiasHabiles($fechaInicio, $fechaFin, $ciclo)
    {
        if (!$fechaInicio || !$fechaFin) return 0;
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
