<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\RegistroAsistencia;
use App\Models\Ciclo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AsistenciaDiaExport;

class ReporteController extends Controller
{
    /**
     * Generar reporte de asistencia por día
     */
    public function asistenciaDia(Request $request)
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'fecha_reporte' => 'required|date',
            'tipo_reporte' => 'required|in:asistencia_dia,faltas_dia,resumen_examen',
            'formato' => 'required|in:pdf,xlsx',
            'carrera_id' => 'nullable|exists:carreras,id',
            'turno_id' => 'nullable|exists:turnos,id',
            'aula_id' => 'nullable|exists:aulas,id'
        ]);

        // Obtener datos del reporte
        $data = $this->obtenerDatosReporte($request);

        // Generar reporte según el formato
        if ($request->formato === 'pdf') {
            return $this->generarPdf($data, $request->tipo_reporte);
        } else {
            return $this->generarExcel($data, $request->tipo_reporte);
        }
    }

    /**
     * Vista previa del reporte
     */
    public function asistenciaDiaPreview(Request $request)
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'fecha_reporte' => 'required|date',
            'tipo_reporte' => 'required|in:asistencia_dia,faltas_dia,resumen_examen',
            'carrera_id' => 'nullable|exists:carreras,id',
            'turno_id' => 'nullable|exists:turnos,id',
            'aula_id' => 'nullable|exists:aulas,id'
        ]);

        $data = $this->obtenerDatosReporte($request);

        // Generar HTML para la vista previa
        $html = view('reportes.asistencia-dia-preview', $data)->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Obtener datos para el reporte
     */
    private function obtenerDatosReporte(Request $request)
    {
        $ciclo = Ciclo::find($request->ciclo_id);
        $fechaReporte = Carbon::parse($request->fecha_reporte);

        // Verificar si es día de examen
        $esExamen = false;
        $numeroExamen = null;

        if ($fechaReporte->isSameDay(Carbon::parse($ciclo->fecha_primer_examen))) {
            $esExamen = true;
            $numeroExamen = 1;
        } elseif ($ciclo->fecha_segundo_examen && $fechaReporte->isSameDay(Carbon::parse($ciclo->fecha_segundo_examen))) {
            $esExamen = true;
            $numeroExamen = 2;
        } elseif ($ciclo->fecha_tercer_examen && $fechaReporte->isSameDay(Carbon::parse($ciclo->fecha_tercer_examen))) {
            $esExamen = true;
            $numeroExamen = 3;
        }

        // Query base de inscripciones
        $query = Inscripcion::with(['estudiante', 'carrera', 'turno', 'aula'])
            ->where('ciclo_id', $request->ciclo_id)
            ->where('estado_inscripcion', 'activo');

        // Aplicar filtros
        if ($request->carrera_id) {
            $query->where('carrera_id', $request->carrera_id);
        }
        if ($request->turno_id) {
            $query->where('turno_id', $request->turno_id);
        }
        if ($request->aula_id) {
            $query->where('aula_id', $request->aula_id);
        }

        $inscripciones = $query->get();

        // Obtener registros de asistencia del día
        $registrosDelDia = RegistroAsistencia::whereIn('nro_documento', $inscripciones->pluck('estudiante.numero_documento'))
            ->whereDate('fecha_registro', $fechaReporte)
            ->get()
            ->groupBy('nro_documento');

        // Procesar datos según el tipo de reporte
        $estudiantes = [];
        $totalEstudiantes = $inscripciones->count();
        $totalAsistencias = 0;
        $totalFaltas = 0;

        foreach ($inscripciones as $inscripcion) {
            $estudiante = $inscripcion->estudiante;
            $registros = $registrosDelDia->get($estudiante->numero_documento, collect());

            // Determinar asistencia
            $asistio = $registros->isNotEmpty();
            $horaEntrada = null;
            $horaSalida = null;

            if ($asistio) {
                $totalAsistencias++;
                // Procesar registros para obtener entrada y salida
                $horasRegistradas = $registros->map(function ($r) {
                    return Carbon::parse($r->fecha_registro);
                })->sort();

                // Detectar turno y asignar entrada/salida
                $primerRegistro = $horasRegistradas->first();
                $esTurnoManana = $primerRegistro->hour < 14;

                if ($esTurnoManana) {
                    // Turno mañana
                    $horaEntrada = $horasRegistradas->first(function ($hora) {
                        return $hora->hour < 10;
                    }) ?? $horasRegistradas->first();

                    $horaSalida = $horasRegistradas->last(function ($hora) {
                        return $hora->hour >= 12 && $hora->hour <= 14;
                    });
                } else {
                    // Turno tarde
                    $horaEntrada = $horasRegistradas->first(function ($hora) {
                        return $hora->hour < 18;
                    });

                    $horaSalida = $horasRegistradas->last(function ($hora) {
                        return $hora->hour >= 18;
                    });

                    if (!$horaEntrada && $horasRegistradas->count() > 0) {
                        $horaEntrada = 'Sin registro';
                    }
                }
            } else {
                $totalFaltas++;
            }

            // Si es examen, obtener información adicional
            $puedeRendirExamen = true;
            $porcentajeAsistencia = null;

            if ($esExamen && $request->tipo_reporte === 'resumen_examen') {
                // Calcular asistencia hasta la fecha del examen
                $asistenciaInfo = $this->calcularAsistenciaHastaFecha(
                    $estudiante->numero_documento,
                    $ciclo,
                    $fechaReporte
                );

                $porcentajeAsistencia = $asistenciaInfo['porcentaje_asistencia'];
                $puedeRendirExamen = $asistenciaInfo['puede_rendir'];
            }

            // Agregar estudiante según el tipo de reporte
            if ($request->tipo_reporte === 'faltas_dia' && !$asistio) {
                $estudiantes[] = [
                    'inscripcion' => $inscripcion,
                    'estudiante' => $estudiante,
                    'asistio' => false,
                    'hora_entrada' => null,
                    'hora_salida' => null
                ];
            } elseif ($request->tipo_reporte !== 'faltas_dia') {
                $estudiantes[] = [
                    'inscripcion' => $inscripcion,
                    'estudiante' => $estudiante,
                    'asistio' => $asistio,
                    'hora_entrada' => $horaEntrada ? ($horaEntrada === 'Sin registro' ? $horaEntrada : $horaEntrada->format('H:i')) : null,
                    'hora_salida' => $horaSalida ? $horaSalida->format('H:i') : null,
                    'puede_rendir_examen' => $puedeRendirExamen,
                    'porcentaje_asistencia' => $porcentajeAsistencia
                ];
            }
        }

        // Ordenar estudiantes por apellido
        usort($estudiantes, function ($a, $b) {
            return strcmp(
                $a['estudiante']->apellido_paterno . ' ' . $a['estudiante']->apellido_materno,
                $b['estudiante']->apellido_paterno . ' ' . $b['estudiante']->apellido_materno
            );
        });

        return [
            'ciclo' => $ciclo,
            'fecha_reporte' => $fechaReporte,
            'fecha_reporte_formato' => $fechaReporte->format('d/m/Y'),
            'dia_semana' => ucfirst($fechaReporte->locale('es')->dayName),
            'es_examen' => $esExamen,
            'numero_examen' => $numeroExamen,
            'tipo_reporte' => $request->tipo_reporte,
            'estudiantes' => $estudiantes,
            'total_estudiantes' => $totalEstudiantes,
            'total_asistencias' => $totalAsistencias,
            'total_faltas' => $totalFaltas,
            'porcentaje_asistencia' => $totalEstudiantes > 0 ? round(($totalAsistencias / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_faltas' => $totalEstudiantes > 0 ? round(($totalFaltas / $totalEstudiantes) * 100, 2) : 0,
            'filtros' => [
                'carrera' => $request->carrera_id ? \App\Models\Carrera::find($request->carrera_id) : null,
                'turno' => $request->turno_id ? \App\Models\Turno::find($request->turno_id) : null,
                'aula' => $request->aula_id ? \App\Models\Aula::find($request->aula_id) : null
            ],
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s')
        ];
    }

    /**
     * Calcular asistencia hasta una fecha específica
     */
    private function calcularAsistenciaHastaFecha($numeroDocumento, $ciclo, $fechaHasta)
    {
        // Obtener primer registro del estudiante
        $primerRegistro = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [$ciclo->fecha_inicio, $ciclo->fecha_fin])
            ->orderBy('fecha_registro')
            ->first();

        if (!$primerRegistro) {
            return [
                'porcentaje_asistencia' => 0,
                'puede_rendir' => false
            ];
        }

        $fechaInicio = Carbon::parse($primerRegistro->fecha_registro)->startOfDay();
        $fechaFin = Carbon::parse($fechaHasta)->endOfDay();

        // Contar días hábiles según configuración del ciclo
        $diasHabiles = 0;
        $fecha = $fechaInicio->copy();
        while ($fecha <= $fechaFin) {
            if ($ciclo->esDiaHabil($fecha)) {
                $diasHabiles++;
            }
            $fecha->addDay();
        }

        // Contar días con asistencia
        $diasConAsistencia = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [$fechaInicio, $fechaFin])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->filter(function ($registro) use ($ciclo) {
                return $ciclo->esDiaHabil(Carbon::parse($registro->fecha));
            })
            ->count();

        $porcentajeAsistencia = $diasHabiles > 0 ? round(($diasConAsistencia / $diasHabiles) * 100, 2) : 0;
        $porcentajeFalta = 100 - $porcentajeAsistencia;

        // Verificar si puede rendir examen
        $limiteInhabilitacion = $ciclo->porcentaje_inhabilitacion;
        $puedeRendir = $porcentajeFalta < $limiteInhabilitacion;

        return [
            'dias_habiles' => $diasHabiles,
            'dias_asistidos' => $diasConAsistencia,
            'porcentaje_asistencia' => $porcentajeAsistencia,
            'puede_rendir' => $puedeRendir
        ];
    }

    /**
     * Generar PDF
     */
    private function generarPdf($data, $tipoReporte)
    {
        $vista = 'reportes.asistencia-dia-pdf';

        $pdf = PDF::loadView($vista, $data);
        $pdf->setPaper('a4', $tipoReporte === 'resumen_examen' ? 'landscape' : 'portrait');

        $filename = 'reporte_asistencia_' . $data['fecha_reporte']->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generar Excel
     */
    private function generarExcel($data, $tipoReporte)
    {
        $filename = 'reporte_asistencia_' . $data['fecha_reporte']->format('Y-m-d') . '.xlsx';

        return Excel::download(new AsistenciaDiaExport($data), $filename);
    }
}
