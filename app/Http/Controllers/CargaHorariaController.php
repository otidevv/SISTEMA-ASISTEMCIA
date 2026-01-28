<?php

namespace App\Http\Controllers;

use App\Models\HorarioDocente;
use App\Models\PagoDocente;
use App\Models\Ciclo;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CargaHorariaResumenExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CargaHorariaController extends Controller
{
    /**
     * Exportar el resumen de carga horaria de todos los docentes para un ciclo
     */
    public function exportarExcelResumen($cicloId)
    {
        $ciclo = Ciclo::findOrFail($cicloId);
        $nombreArchivo = 'Resumen_Carga_Horaria_' . str_replace(' ', '_', $ciclo->nombre) . '.xlsx';
        
        return Excel::download(new CargaHorariaResumenExport($cicloId), $nombreArchivo);
    }

    public function index()
    {
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        $docentes = User::whereHas('roles', function($q){
            $q->where('nombre', 'profesor');
        })->orderBy('nombre')->get();
        
        $cicloActivo = $ciclos->firstWhere('es_activo', true);
        
        return view('carga-horaria.index', compact('ciclos', 'docentes', 'cicloActivo'));
    }

    /**
     * Vista de Mi Horario para el Docente
     */
    public function miHorario()
    {
        $docente = auth()->user();
        $cicloActivo = Ciclo::where('es_activo', true)->first();
        
        if (!$cicloActivo) {
            return redirect()->back()->with('error', 'No hay un ciclo académico activo.');
        }

        $data = $this->obtenerDatosCargaHoraria($docente->id, $cicloActivo->id);
        
        return view('carga-horaria.docente-dashboard', compact('docente', 'cicloActivo', 'data'));
    }
    
    public function obtenerDatosCargaHoraria($docenteId, $cicloId)
    {
        // Obtener horarios del docente en el ciclo
        $horarios = HorarioDocente::with(['curso', 'aula', 'ciclo'])
            ->where('docente_id', $docenteId)
            ->where('ciclo_id', $cicloId)
            ->get();
        
        // Ordenamiento personalizado: Lunes a Domingo cronológicamente
        $ordenDias = [
            'Lunes' => 1,
            'Martes' => 2,
            'Miércoles' => 3,
            'Miercoles' => 3,
            'Jueves' => 4,
            'Viernes' => 5,
            'Sábado' => 6,
            'Sabado' => 6,
            'Domingo' => 0
        ];

        $horarios = $horarios->sort(function($a, $b) use ($ordenDias) {
            $diaA = $ordenDias[$a->dia_semana] ?? 99;
            $diaB = $ordenDias[$b->dia_semana] ?? 99;
            
            if ($diaA === $diaB) {
                return strcmp($a->hora_inicio, $b->hora_inicio);
            }
            return $diaA <=> $diaB;
        });
        
        // Calcular horas por horario
        $horariosConHoras = $horarios->map(function($horario) {
            $inicio = Carbon::parse($horario->hora_inicio);
            $fin = Carbon::parse($horario->hora_fin);
            
            // Asegurar que inicio sea antes que fin para evitar errores
            if ($fin < $inicio) {
                $fin->addDay();
            }
            
            // 🏷️ Identificar si el bloque ya es un receso explícito
            $esRecesoExplicito = !$horario->curso_id || 
                                ($horario->curso && (
                                    str_contains(strtolower($horario->curso->nombre), 'receso') || 
                                    str_contains(strtolower($horario->curso->nombre), 'sin curso')
                                ));

            $minutosBrutos = abs($fin->diffInMinutes($inicio));
            $minutosRecesoSubtraer = 0;

            if (!$esRecesoExplicito) {
                // Cálculo de sustracción automática (Alineado con Reporte de Planillas Excel)
                // Se resta el tiempo de receso si la clase cruza los rangos fijos
                $baseDate = Carbon::today();
                $startH = $baseDate->copy()->setTime($inicio->hour, $inicio->minute);
                $endH = $baseDate->copy()->setTime($fin->hour, $fin->minute);
                if ($endH < $startH) $endH->addDay();

                // Receso Mañana: 10:00 - 10:30
                $r1S = $baseDate->copy()->setTime(10, 0);
                $r1E = $baseDate->copy()->setTime(10, 30);
                if ($startH < $r1E && $endH > $r1S) {
                    $overlapS = $startH->max($r1S);
                    $overlapE = $endH->min($r1E);
                    if ($overlapE > $overlapS) {
                        $minutosRecesoSubtraer += $overlapS->diffInMinutes($overlapE);
                    }
                }

                // Receso Tarde: 18:00 - 18:30
                $r2S = $baseDate->copy()->setTime(18, 0);
                $r2E = $baseDate->copy()->setTime(18, 30);
                if ($startH < $r2E && $endH > $r2S) {
                    $overlapS = $startH->max($r2S);
                    $overlapE = $endH->min($r2E);
                    if ($overlapE > $overlapS) {
                        $minutosRecesoSubtraer += $overlapS->diffInMinutes($overlapE);
                    }
                }
            }

            $minutosNetos = $minutosBrutos - $minutosRecesoSubtraer;
            $decimal = $minutosNetos / 60;
            
            $horario->horas_decimal = $decimal;
            $horario->horas_formateado = self::formatearHorasHumanas($decimal);
            $horario->es_receso = $esRecesoExplicito;
            $horario->minutos_receso_sustraidos = $minutosRecesoSubtraer;
            
            return $horario;
        });
        
        // Filtrar horarios reales (no recesos) para cálculos estadísticos y económicos 
        $horariosReales = $horariosConHoras->filter(function($h) {
            return !$h->es_receso;
        });

        // Obtener ciclo y tarifa
        $ciclo = Ciclo::find($cicloId);
        $pago = PagoDocente::where('docente_id', $docenteId)
            ->where('fecha_inicio', '<=', $ciclo->fecha_fin)
            ->where(function ($query) use ($ciclo) {
                $query->where('fecha_fin', '>=', $ciclo->fecha_inicio)
                      ->orWhereNull('fecha_fin');
            })
            ->orderBy('fecha_inicio', 'desc')
            ->first();
        
        $tarifaPorHora = $pago ? $pago->tarifa_por_hora : 0;

        // Calcular semanas del ciclo
        $inicio = Carbon::parse($ciclo->fecha_inicio);
        $fin = Carbon::parse($ciclo->fecha_fin);
        $diasCiclo = abs($inicio->diffInDays($fin));
        $semanasCiclo = round($diasCiclo / 7, 1) ?: 1;

        // 📅 NUEVO: Horas Base Semanal (Suma simple de Lun-Vie sin repeticiones)
        $horasBaseSemanal = $horariosReales->whereNotIn('dia_semana', ['Sábado', 'Sabado', 'Domingo'])->sum('horas_decimal');

        // Calcular ocurrencias de cada día en el ciclo (considerando rotación de sábados)
        $ocurrenciasDias = $this->contarOcurrenciasDias($ciclo);
        
        $totalHorasCiclo = 0;
        foreach ($horariosReales as $h) {
            $cantDias = $ocurrenciasDias[$h->dia_semana] ?? 0;
            $totalHorasCiclo += ($h->horas_decimal * $cantDias);
        }

        // Promedio semanal real considerando sábados rotativos
        $totalHorasSemana = $totalHorasCiclo / ($semanasCiclo ?: 1);
        
        // Horas por turno (excluyendo recesos)
        $horasPorTurno = $horariosReales->groupBy('turno')->map(function($grupo) {
            return $grupo->sum('horas_decimal');
        });
        
        // Horas por curso (excluyendo recesos)
        $horasPorCurso = $horariosReales->groupBy('curso_id')->map(function($grupo) {
            return [
                'curso' => $grupo->first()->curso ? $grupo->first()->curso->nombre : 'Sin curso',
                'horas' => $grupo->sum('horas_decimal')
            ];
        });

        $pagoSemanal = $totalHorasSemana * $tarifaPorHora;
        $pagoMensual = $pagoSemanal * 4;
        $pagoTotalCiclo = $totalHorasCiclo * $tarifaPorHora;
        
        return [
            'horarios' => $horariosConHoras->values(),
            'horas_base_semanal' => $horasBaseSemanal,
            'horas_base_formateado' => self::formatearHorasHumanas($horasBaseSemanal),
            'horas_totales_ciclo' => round($totalHorasCiclo, 1),
            'horas_totales_ciclo_formateado' => self::formatearHorasHumanas($totalHorasCiclo),
            'total_horas_semana' => round($totalHorasSemana, 1),
            'total_horas_formateado' => self::formatearHorasHumanas($totalHorasSemana),
            'horas_por_turno' => $horasPorTurno,
            'horas_por_curso' => $horasPorCurso,
            'tarifa_por_hora' => $tarifaPorHora,
            'semanas_ciclo' => $semanasCiclo,
            'pago_semanal' => $pagoSemanal,
            'pago_mensual' => $pagoMensual,
            'pago_total_ciclo' => $pagoTotalCiclo,
            'docente' => User::find($docenteId),
            'ciclo' => $ciclo
        ];
    }

    /**
     * Cuenta cuántas veces ocurre cada día de la semana en el ciclo,
     * considerando la rotación de los sábados.
     */
    private function contarOcurrenciasDias($ciclo)
    {
        $inicio = Carbon::parse($ciclo->fecha_inicio);
        $fin = Carbon::parse($ciclo->fecha_fin);
        
        $conteo = [
            'Lunes' => 0,
            'Martes' => 0,
            'Miércoles' => 0,
            'Jueves' => 0,
            'Viernes' => 0,
        ];
        
        $actual = $inicio->copy();
        while ($actual <= $fin) {
            $diaHorario = $ciclo->getDiaHorarioParaFecha($actual);
            if ($diaHorario && isset($conteo[$diaHorario])) {
                $conteo[$diaHorario]++;
            }
            $actual->addDay();
        }
        
        return $conteo;
    }

    /**
     * Convierte horas decimales a formato legible (Ej: 10.5 -> 10h 30m)
     */
    public static function formatearHorasHumanas($horasDecimal)
    {
        $horas = floor($horasDecimal);
        $minutos = round(($horasDecimal - $horas) * 60);
        
        if ($horas == 0 && $minutos == 0) return "0h 0m";
        if ($horas == 0) return "{$minutos}m";
        if ($minutos == 0) return "{$horas}h";
        
        return "{$horas}h {$minutos}m";
    }
    
    public function pdfVisual($docenteId, $cicloId)
    {
        $data = $this->obtenerDatosCargaHoraria($docenteId, $cicloId);
        
        $pdf = Pdf::loadView('reportes.carga-horaria-visual', [
            'docente' => $data['docente'],
            'ciclo' => $data['ciclo'],
            'data' => $data,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s')
        ]);
        
        $pdf->setPaper('a4', 'landscape'); // Horizontal para el horario visual
        
        $filename = 'horario_' . $data['docente']->numero_documento . '_' . $data['ciclo']->codigo . '.pdf';
        return $pdf->download($filename);
    }

    public function pdfDetallado($docenteId, $cicloId)
    {
        $data = $this->obtenerDatosCargaHoraria($docenteId, $cicloId);
        
        $pdf = Pdf::loadView('reportes.carga-horaria-detallado', [
            'docente' => $data['docente'],
            'ciclo' => $data['ciclo'],
            'data' => $data,
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s')
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'carga_horaria_' . $data['docente']->numero_documento . '_' . $data['ciclo']->codigo . '.pdf';
        return $pdf->download($filename);
    }
}
