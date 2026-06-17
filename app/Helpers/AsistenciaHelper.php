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
            ->latest()
            ->first();

            if ($inscripcion) {
                $cicloActivo = $inscripcion->ciclo;
            } else {
                // Si no hay inscripción encontrada, buscar el ciclo más reciente marcado como activo
                // Priorizando CEPRE si no se sabe nada
                $cicloActivo = Ciclo::where('es_activo', true)
                    ->orderBy('programa_id', 'asc') // Programa 1 (CEPRE) primero
                    ->orderBy('fecha_inicio', 'desc')
                    ->first();
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

        $tieneAsistenciaHoy = false;
        if ($nro_documento) {
            $tieneAsistenciaHoy = RegistroAsistencia::where('nro_documento', $nro_documento)
                ->whereDate('fecha_registro', $hoy->toDateString())
                ->exists();
        }

        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy->endOfDay() : $fechaExamenCarbon;
        if ($hoy < $fechaExamenCarbon && !$tieneAsistenciaHoy) {
            $fechaFinCalculo = $hoy->copy()->subDay()->endOfDay();
        }

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
    public static function calcularInfoAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo, $returnHistory = false)
    {
        $hoy = Carbon::now()->startOfDay();
        
        // El inicio de la evaluación para este método es lo que se pase (fecha inicio periodo o primer registro)
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->startOfDay();

        $docLimpio = trim(strval($numeroDocumento));
        $tieneAsistenciaHoy = false;
        if ($numeroDocumento) {
            $tieneAsistenciaHoy = RegistroAsistencia::where('nro_documento', $docLimpio)
                ->whereDate('fecha_registro', $hoy->toDateString())
                ->exists();
        }

        // La fecha final para el cálculo no puede ser futura
        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy->copy()->endOfDay() : $fechaExamenCarbon->copy()->endOfDay();
        if ($hoy < $fechaExamenCarbon && !$tieneAsistenciaHoy) {
            $fechaFinCalculo = $hoy->copy()->subDay()->endOfDay();
        }

        // Solo procesar si el inicio no es en el futuro
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
                'es_proyeccion' => true,
                'historial' => []
            ];
        }

        // Obtener el primer registro de asistencia del estudiante dentro de este ciclo
        $docLimpio = trim(strval($numeroDocumento));
        $primerRegistroGlobal = RegistroAsistencia::where('nro_documento', $docLimpio)
            ->where('fecha_registro', '>=', Carbon::parse($ciclo->fecha_inicio)->startOfDay())
            ->where('fecha_registro', '<=', Carbon::parse($ciclo->fecha_fin)->endOfDay())
            ->orderBy('fecha_registro')
            ->first();

        // SI NO HAY REGISTRO, NO HAY CÁLCULO (Igual que en la Web)
        if (!$primerRegistroGlobal) {
            return [
                'dias_habiles' => self::contarDiasHabiles($fechaInicio, $fechaExamen, $ciclo),
                'dias_asistidos' => 0,
                'dias_falta' => 0,
                'porcentaje_asistencia' => 100,
                'estado' => 'sin_registros',
                'mensaje' => 'Aún no tienes registros de asistencia en este ciclo académico. Tu control de asistencia comenzará a partir de tu primer registro en el sistema biométrico de la institución.',
                'puede_rendir' => true,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaExamen,
                'es_proyeccion' => true,
                'historial' => []
            ];
        }

        // En la web, si es el primer periodo, se cuenta desde el primer registro
        $inicioEfectivo = $fechaInicioCarbon;
        $inicioCicloStr = Carbon::parse($ciclo->fecha_inicio)->toDateString();
        if ($fechaInicioCarbon->toDateString() === $inicioCicloStr) {
            $inicioEfectivo = Carbon::parse($primerRegistroGlobal->fecha_registro)->startOfDay();
        }

        $diasHabilesTotales = self::contarDiasHabiles($inicioEfectivo, $fechaExamenCarbon, $ciclo);
        $diasHabilesTranscurridos = self::contarDiasHabiles($inicioEfectivo, $fechaFinCalculo, $ciclo);

        // Obtener registros en el periodo
        $registrosMap = RegistroAsistencia::where('nro_documento', $docLimpio)
            ->whereBetween('fecha_registro', [
                $inicioEfectivo->copy()->startOfDay(),
                $fechaFinCalculo->copy()->endOfDay()
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'), 'fecha_registro')
            ->get()
            ->groupBy('fecha');

        $diasConAsistencia = 0;
        $historial = [];
        
        // Siempre calculamos el conteo, el historial es opcional para la respuesta
        $tempFecha = $inicioEfectivo->copy();
        while ($tempFecha <= $fechaFinCalculo) {
            if ($ciclo->esDiaHabil($tempFecha)) {
                $fechaStr = $tempFecha->toDateString();
                if (isset($registrosMap[$fechaStr])) {
                    $diasConAsistencia++;
                    if ($returnHistory) {
                        $historial[] = [
                            'fecha' => $fechaStr,
                            'dia_semana' => $tempFecha->translatedFormat('l'),
                            'estado' => 'asistio',
                            'hora' => Carbon::parse($registrosMap[$fechaStr][0]->fecha_registro)->format('H:i:s')
                        ];
                    }
                } elseif ($returnHistory) {
                    $historial[] = [
                        'fecha' => $fechaStr,
                        'dia_semana' => $tempFecha->translatedFormat('l'),
                        'estado' => 'falta',
                        'hora' => null
                    ];
                }
            }
            $tempFecha->addDay();
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
            // Lógica de examen pasado (Idéntica a la Web)
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
            // Lógica de periodo en curso (Idéntica a la Web)
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

            // El mensaje de días restantes se concatena al final como en la web
            $mensaje .= " Quedan {$diasRestantes} día" . ($diasRestantes > 1 ? 's' : '') . " hábil" . ($diasRestantes > 1 ? 'es' : '') . " hasta el examen.";
        }

        return [
            'dias_habiles' => $diasHabilesTotales,
            'dias_asistidos' => $diasConAsistencia,
            'dias_falta' => $diasFaltaActuales,
            'porcentaje_asistencia' => $porcentajeAsistenciaProyectado,
            'porcentaje_inasistencia' => $porcentajeInasistenciaProyectado,
            'limite_amonestacion' => $limiteAmonestacion,
            'limite_inhabilitacion' => $limiteInhabilitacion,
            'estado' => $estado,
            'mensaje' => $mensaje,
            'puede_rendir' => $puedeRendir,
            'fecha_inicio' => $inicioEfectivo->toDateString(),
            'fecha_fin' => $fechaExamenCarbon->toDateString(),
            'es_proyeccion' => $hoy < $fechaExamenCarbon,
            'historial' => $historial
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
        $fechaExamen = $ciclo->fecha_primer_examen ?: $ciclo->fecha_fin;
        if (!$fechaExamen) {
            return null;
        }
        return [
            'id' => 1,
            'nombre' => 'Primer Examen',
            'fecha_inicio' => $ciclo->fecha_inicio,
            'fecha_examen' => $fechaExamen
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

        // 2. Obtener inscripciones según programa
        if ($ciclo->programa_id == 2) {
            $inscripciones = \App\Models\InscripcionReforzamiento::where('ciclo_id', $ciclo->id)
                ->where('estado_inscripcion', 'validado')
                ->with('estudiante:id,numero_documento')
                ->get();
        } else {
            $inscripciones = \App\Models\Inscripcion::where('ciclo_id', $ciclo->id)
                ->where('estado_inscripcion', 'activo')
                ->with('estudiante:id,numero_documento')
                ->get();
        }
        
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

    /**
     * Obtener detalle de asistencias por mes incluyendo faltas (Movido desde InscripcionController)
     */
    public static function obtenerDetalleAsistenciasPorMes($numeroDocumento, $fechaInicio, $fechaFin, $ciclo = null, $turno_id = 1)
    {
        // Primero, obtener todos los registros de asistencia
        $registros = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->orderBy('fecha_registro')
            ->get();

        // Organizar registros por fecha
        $registrosPorFecha = [];
        foreach ($registros as $registro) {
            $fecha = Carbon::parse($registro->fecha_registro)->format('Y-m-d');
            if (!isset($registrosPorFecha[$fecha])) {
                $registrosPorFecha[$fecha] = [];
            }
            $registrosPorFecha[$fecha][] = $registro;
        }

        // Generar todos los días hábiles del período
        $detallesPorMes = [];
        $fechaActual = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();

        // Obtener configuración del turno para validar las horas
        $turnoConfig = \App\Models\Turno::find($turno_id);

        while ($fechaActual <= $fechaFinCarbon) {
            // Solo procesar días hábiles según configuración del ciclo
            $esDiaHabil = $ciclo ? $ciclo->esDiaHabil($fechaActual) : $fechaActual->isWeekday();
            
            if ($esDiaHabil) {
                $mes = $fechaActual->format('Y-m');
                $nombreMes = $fechaActual->locale('es')->monthName;
                $anio = $fechaActual->year;
                $fechaStr = $fechaActual->format('Y-m-d');

                // Inicializar el mes si no existe
                if (!isset($detallesPorMes[$mes])) {
                    $detallesPorMes[$mes] = [
                        'mes' => ucfirst($nombreMes),
                        'anio' => $anio,
                        'dias_asistidos' => 0,
                        'dias_falta' => 0,
                        'registros' => []
                    ];
                }

                // Datos básicos del día
                $datosDelDia = [
                    'fecha' => $fechaActual->format('d/m/Y'),
                    'dia_semana' => ucfirst($fechaActual->locale('es')->dayName),
                    'hora_entrada' => null,
                    'hora_salida' => null,
                    'asistio' => false
                ];

                // Verificar si hay registros para este día
                if (isset($registrosPorFecha[$fechaStr])) {
                    $datosDelDia['asistio'] = true;
                    $detallesPorMes[$mes]['dias_asistidos']++;

                    // Procesar los registros del día
                    $registrosDelDia = [];
                    foreach ($registrosPorFecha[$fechaStr] as $reg) {
                        $dt = Carbon::parse($reg->fecha_registro);
                        $registrosDelDia[] = [
                            'hora' => $dt->hour,
                            'minutos' => $dt->minute,
                            'hora_formateada' => $dt->format('H:i'),
                            'total_minutos' => $dt->hour * 60 + $dt->minute
                        ];
                    }

                    // Ordenar registros por hora
                    usort($registrosDelDia, function ($a, $b) {
                        return $a['total_minutos'] - $b['total_minutos'];
                    });

                    $entrada = null;
                    $salida = null;
                    $esTarde = false;

                    // Aplicar lógica según el turno oficial del estudiante y su configuración en BD
                    foreach ($registrosDelDia as $reg) {
                        $cat = self::categorizarAsistencia($reg['total_minutos'], $turnoConfig);
                        
                        if ($cat === 'Entrada' && !$entrada) {
                            $entrada = $reg['hora_formateada'];
                        } elseif ($cat === 'Tarde' && !$entrada) {
                            $entrada = $reg['hora_formateada'];
                            $esTarde = true;
                        } elseif ($cat === 'Salida') {
                            $salida = $reg['hora_formateada']; // El último registro de salida gana
                        }
                    }

                    // Fallbacks si no se detectó nada específico por categorías pero hay registros
                    if (!$entrada && count($registrosDelDia) > 0) {
                        $entrada = $registrosDelDia[0]['hora_formateada'];
                        // Determinar si fue tarde comparando con los límites del turno dinámicamente
                        $minEntrada = $registrosDelDia[0]['total_minutos'];
                        
                        // Usar hora_entrada_fin como límite de tolerancia si existe
                        $limiteEntrada = $turnoConfig ? self::timeStringToMinutes($turnoConfig->hora_entrada_fin) : null;
                        
                        if ($limiteEntrada && $minEntrada > $limiteEntrada) {
                            $esTarde = true;
                        }
                    }
                    
                    if (!$salida && count($registrosDelDia) > 1) {
                        // Si hay más de un registro, el último es la salida (si no se marcó por categoría)
                        if (!$salida || $salida === $entrada) {
                             $salida = $registrosDelDia[count($registrosDelDia) - 1]['hora_formateada'];
                        }
                    }

                    $datosDelDia['hora_entrada'] = $entrada ?: 'Sin registro';
                    $datosDelDia['hora_salida'] = ($salida && $salida !== $entrada) ? $salida : '-';
                    $datosDelDia['es_tarde'] = $esTarde;
                } else {
                    // NO ASISTIÓ
                    $datosDelDia['hora_entrada'] = 'FALTA';
                    $datosDelDia['hora_salida'] = 'FALTA';
                    $datosDelDia['es_tarde'] = false;
                    $detallesPorMes[$mes]['dias_falta']++;
                }

                // Agregar el día al mes correspondiente
                $detallesPorMes[$mes]['registros'][] = $datosDelDia;
            }

            // Avanzar al siguiente día
            $fechaActual->addDay();
        }

        // Convertir asociativo a numérico para respuesta JSON limpia
        return array_values($detallesPorMes);
    }

    /**
     * Helper para convertir hora HH:MM a minutos
     */
    public static function timeStringToMinutes($timeStr)
    {
        if (!$timeStr) return null;
        $parts = explode(':', $timeStr);
        return intval($parts[0]) * 60 + intval($parts[1]);
    }

    /**
     * Categorizar asistencia según los rangos configurados en la BD
     */
    public static function categorizarAsistencia($minutos, $turno)
    {
        if (!$turno) return 'Otro';

        $entradaInicio = self::timeStringToMinutes($turno->hora_entrada_inicio);
        $entradaFin = self::timeStringToMinutes($turno->hora_entrada_fin);
        $tardeInicio = self::timeStringToMinutes($turno->hora_tarde_inicio);
        $tardeFin = self::timeStringToMinutes($turno->hora_tarde_fin);
        $salidaInicio = self::timeStringToMinutes($turno->hora_salida_inicio);
        $salidaFin = self::timeStringToMinutes($turno->hora_salida_fin);

        // Si no hay configuración en BD, retornar Otro
        if ($entradaInicio === null) {
            return 'Otro';
        }

        if ($minutos >= $entradaInicio && $minutos <= $entradaFin) return 'Entrada';
        if ($minutos >= $tardeInicio && $minutos <= $tardeFin) return 'Tarde';
        if ($minutos >= $salidaInicio && $minutos <= $salidaFin) return 'Salida';

        return 'Otro';
    }

    /**
     * Describe un marcaje para mostrarlo en los clientes (web y app móvil).
     *
     * Fuente ÚNICA de clasificación de marcajes: reutiliza categorizarAsistencia(),
     * que se basa en la configuración real del turno (BD). Los clientes solo deben
     * MOSTRAR estos valores, nunca recalcular la situación por su cuenta.
     *
     * @param mixed $fechaHora Fecha/hora del marcaje
     * @param \App\Models\Turno|null $turno Turno del alumno (de su inscripción)
     * @return array{tipo: string, situacion: string}
     */
    public static function describirMarcaje($fechaHora, $turno): array
    {
        $carbon = $fechaHora instanceof Carbon ? $fechaHora : Carbon::parse($fechaHora);
        $minutos = $carbon->hour * 60 + $carbon->minute;
        $categoria = self::categorizarAsistencia($minutos, $turno); // Entrada/Tarde/Salida/Otro

        $mapa = [
            'Entrada' => ['tipo' => 'entrada', 'situacion' => 'ENTRADA NORMAL'],
            'Tarde'   => ['tipo' => 'tarde',   'situacion' => 'ENTRADA TARDE'],
            'Salida'  => ['tipo' => 'salida',  'situacion' => 'SALIDA NORMAL'],
            'Otro'    => ['tipo' => 'otro',    'situacion' => 'REGISTRO'],
        ];

        return $mapa[$categoria] ?? $mapa['Otro'];
    }

    /**
     * Obtener documentos de alumnos inhabilitados para un examen y ciclo específico.
     */
    public static function obtenerDocumentosInhabilitados($ciclo, $examenNumero)
    {
        $examenActivo = self::getExamenPeriodoPorId($ciclo, $examenNumero);
        if (!$examenActivo) return [];

        $periodoInicio = Carbon::parse($examenActivo['fecha_inicio'])->startOfDay();
        $periodoExamen = Carbon::parse($examenActivo['fecha_examen'])->endOfDay();
        $ahora = Carbon::now();
        $fechaFinCalculo = $ahora < $periodoExamen ? $ahora->copy()->endOfDay() : $periodoExamen;

        // Obtener todas las inscripciones del ciclo
        $inscripciones = \App\Models\Inscripcion::where('ciclo_id', $ciclo->id)
            ->where('estado_inscripcion', 'activo')
            ->with('estudiante:id,numero_documento')
            ->get();

        $totalEstudiantes = $inscripciones->count();
        if ($totalEstudiantes === 0) return [];

        $documentosInscritos = $inscripciones->pluck('estudiante.numero_documento')->filter()->toArray();

        // Obtener primeras asistencias
        $primerasAsistencias = DB::table('registros_asistencia')
            ->whereIn('nro_documento', $documentosInscritos)
            ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
            ->where('fecha_registro', '<=', $ciclo->fecha_fin)
            ->select('nro_documento', DB::raw('MIN(fecha_registro) as first_reg'))
            ->groupBy('nro_documento')
            ->get()
            ->pluck('first_reg', 'nro_documento')
            ->toArray();

        // Obtener asistencias del periodo
        $asistenciasCount = DB::table('registros_asistencia')
            ->whereIn('nro_documento', $documentosInscritos)
            ->where('fecha_registro', '>=', $periodoInicio)
            ->where('fecha_registro', '<=', $fechaFinCalculo)
            ->select('nro_documento', DB::raw('COUNT(DISTINCT DATE(fecha_registro)) as total'))
            ->groupBy('nro_documento')
            ->get()
            ->pluck('total', 'nro_documento')
            ->toArray();

        // Pre-calcular mapa de días hábiles
        $cycleStart = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
        $cycleEnd = Carbon::parse($ciclo->fecha_fin)->endOfDay();
        $cumulativeBusinessDays = [];
        $count = 0;
        $temp = $cycleStart->copy();
        while ($temp <= $cycleEnd) {
            if ($ciclo->esDiaHabil($temp)) $count++;
            $cumulativeBusinessDays[$temp->toDateString()] = $count;
            $temp->addDay();
        }

        $inhabilitados = [];
        $isPrimerExamen = ($examenActivo['nombre'] === 'Primer Examen');
        $porcentajeInhabilitacion = $ciclo->porcentaje_inhabilitacion ?? 30;

        foreach ($inscripciones as $inscripcion) {
            $doc = $inscripcion->estudiante->numero_documento ?? '';
            if (empty($doc) || !isset($primerasAsistencias[$doc])) {
                continue;
            }

            $currentInicioConteo = $periodoInicio->copy();
            if ($isPrimerExamen) {
                $fechaPrimerRegistro = Carbon::parse($primerasAsistencias[$doc])->startOfDay();
                if ($fechaPrimerRegistro->gt($currentInicioConteo)) {
                    $currentInicioConteo = $fechaPrimerRegistro;
                }
            }

            if ($currentInicioConteo > $ahora) continue;

            $diasHabilesTotales = self::getBusinessDaysCountStatic($currentInicioConteo, $periodoExamen, $cumulativeBusinessDays, $cycleStart, $cycleEnd);
            $diasHabilesTranscurridos = self::getBusinessDaysCountStatic($currentInicioConteo, $fechaFinCalculo, $cumulativeBusinessDays, $cycleStart, $cycleEnd);
            
            $diasConAsistencia = $asistenciasCount[$doc] ?? 0;
            $totalFaltas = max(0, $diasHabilesTranscurridos - $diasConAsistencia);
            
            $limiteInhabilitacion = ceil($diasHabilesTotales * ($porcentajeInhabilitacion / 100));

            if ($totalFaltas >= $limiteInhabilitacion) {
                $inhabilitados[] = $doc;
            }
        }

        return $inhabilitados;
    }
}
