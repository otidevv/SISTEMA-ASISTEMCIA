<?php

namespace App\Exports;

use App\Models\AsistenciaDocente;
use App\Models\User; 
use App\Models\HorarioDocente;
use App\Models\PagoDocente; // Importa el modelo PagoDocente
use App\Models\Ciclo; // Importa el modelo Ciclo para la relación

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Illuminate\Support\Collection; 

/**
 * Esta clase genera un REPORTE DETALLADO de asistencia de docentes,
 * mostrando cada sesión y agrupando los resultados por semana y mes.
 */
class AsistenciasDocentesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    private $dataRows = []; 
    private $currentRow = 1; 
    private $processedData; 

    private $selectedDocenteId;
    private $selectedMonth;
    private $selectedYear;
    private $fechaInicio; 
    private $fechaFin;      
    private $selectedCicloAcademico; 


    public function __construct($selectedDocenteId = null, $selectedMonth = null, $selectedYear = null, $fechaInicio = null, $fechaFin = null, $selectedCicloAcademico = null)
    {
        $this->selectedDocenteId = $selectedDocenteId;
        $this->selectedMonth = $selectedMonth ? (int)$selectedMonth : null;
        $this->selectedYear = $selectedYear ? (int)$selectedYear : null;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->selectedCicloAcademico = $selectedCicloAcademico;

        $asistenciasRawQuery = AsistenciaDocente::with(['docente', 'horario.curso', 'horario.aula'])
            ->orderBy('fecha_hora', 'asc');

        if ($this->selectedDocenteId) {
            $asistenciasRawQuery->where('docente_id', $this->selectedDocenteId);
        }

        if ($this->fechaInicio && $this->fechaFin) {
            $asistenciasRawQuery->whereBetween('fecha_hora', [Carbon::parse($this->fechaInicio)->startOfDay(), Carbon::parse($this->fechaFin)->endOfDay()]);
        } elseif ($this->selectedMonth && $this->selectedYear) {
            $asistenciasRawQuery->whereMonth('fecha_hora', $this->selectedMonth)
                                ->whereYear('fecha_hora', $this->selectedYear);
        }
        
        if ($this->selectedCicloAcademico) {
            $asistenciasRawQuery->whereHas('horario.ciclo', function ($query) use ($selectedCicloAcademico) {
                $query->where('codigo', $selectedCicloAcademico); 
            });
        }

        $asistenciasRaw = $asistenciasRawQuery->get();

        $processedData = [];
        // Agrupamos por docente y por día
        $groupedByDocenteDate = $asistenciasRaw->groupBy(function ($item) {
            if ($item && $item->fecha_hora) {
                return $item->docente_id . '_' . Carbon::parse($item->fecha_hora)->format('Y-m-d');
            }
            return null;
        })->filter();

        foreach ($groupedByDocenteDate as $groupKey => $recordsDelDia) {
            if ($recordsDelDia->isEmpty()) {
                continue;
            }

            $docenteId = $recordsDelDia->first()->docente_id;
            $fecha = Carbon::parse($recordsDelDia->first()->fecha_hora)->format('Y-m-d');
            
            // LÓGICA SIMPLIFICADA: Encontrar la primera entrada y la última salida del DÍA.
            $earliestEntryRecord = $recordsDelDia->where('estado', 'entrada')->sortBy('fecha_hora')->first();
            $latestExitRecord = $recordsDelDia->where('estado', 'salida')->sortByDesc('created_at')->first();

            $horasDictadas = 0;
            if ($earliestEntryRecord && $latestExitRecord) {
                $hEntrada = Carbon::parse($earliestEntryRecord->fecha_hora);
                $hSalida = Carbon::parse($latestExitRecord->created_at);
                if ($hSalida->greaterThan($hEntrada)) {
                    $horasDictadas = round($hSalida->diffInMinutes($hEntrada) / 60, 2);
                }
            }
            
            // Para mostrar la información, tomamos como referencia el primer registro del día
            $referenceRecord = $recordsDelDia->first();
            $horario = $referenceRecord->horario;
            
            $curso = $horario->curso->nombre ?? 'N/A';
            $aula = $horario->aula->nombre ?? 'N/A';
            $turno = $horario->turno ?? 'N/A';
            
            // Tomar el último tema desarrollado del día
            $temaRecord = $recordsDelDia->where('tema_desarrollado', '!=', null)->sortByDesc('fecha_hora')->first();
            $temaDesarrollado = $temaRecord->tema_desarrollado ?? 'N/A';

            // Horas para mostrar en el reporte
            $horaEntradaDisplay = $earliestEntryRecord ? Carbon::parse($earliestEntryRecord->fecha_hora)->format('H:i a') : 'N/A';
            $horaSalidaDisplay = $latestExitRecord ? Carbon::parse($latestExitRecord->created_at)->format('H:i a') : 'N/A';

            // CÁLCULO DE PAGO
            $tarifaPorHoraAplicable = 0;
            $referenceDateForPayment = $earliestEntryRecord ? $earliestEntryRecord->fecha_hora : null;

            if ($horasDictadas > 0 && $referenceDateForPayment) { 
                $pagoDocente = PagoDocente::where('docente_id', $docenteId)
                    ->whereDate('fecha_inicio', '<=', $referenceDateForPayment) 
                    ->whereDate('fecha_fin', '>=', $referenceDateForPayment)
                    ->first();
                if ($pagoDocente) {
                    $tarifaPorHoraAplicable = $pagoDocente->tarifa_por_hora;
                }
            }
            $montoTotal = $horasDictadas * $tarifaPorHoraAplicable; 

            // Asignar los datos procesados para el reporte
            $processedData[$docenteId]['docente_info'] = $recordsDelDia->first()->docente;
            $monthKey = Carbon::parse($fecha)->format('Y-m');
            $processedData[$docenteId]['months'][$monthKey]['month_name'] = Carbon::parse($fecha)->locale('es')->monthName;
            $weekKey = Carbon::parse($fecha)->weekOfYear;
            $processedData[$docenteId]['months'][$monthKey]['weeks'][$weekKey]['week_number'] = $weekKey;
            
            $processedData[$docenteId]['months'][$monthKey]['weeks'][$weekKey]['details'][] = [
                'fecha' => $fecha,
                'curso' => $curso,
                'tema_desarrollado' => $temaDesarrollado,
                'aula' => $aula,
                'turno' => $turno,
                'hora_entrada' => $horaEntradaDisplay,
                'hora_salida' => $horaSalidaDisplay,
                'horas_dictadas' => $horasDictadas,
                'pago' => $montoTotal,
            ];
        }
        $this->processedData = $processedData;
    }

    public function collection()
    {
        $this->dataRows = new Collection();
        $this->currentRow = 1; 

        $rangoFechasHeader = 'PERIODO: ';
        if ($this->fechaInicio && $this->fechaFin) {
            $rangoFechasHeader .= Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($this->fechaFin)->format('d/m/Y');
        } elseif ($this->selectedMonth && $this->selectedYear) {
            $rangoFechasHeader .= Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->locale('es')->monthName . ' ' . $this->selectedYear;
        } else {
            $rangoFechasHeader .= 'Todo el Historial';
        }

        if ($this->selectedCicloAcademico) {
            $rangoFechasHeader .= ' - CICLO: ' . $this->selectedCicloAcademico;
        }

        $this->dataRows->push([
            'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS', 
            '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push([
            'CENTRO PRE UNIVERSITARIO', 
            '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push(['']); 
        $this->currentRow++;
        $this->dataRows->push([
            'REPORTE DE ASISTENCIA DOCENTE', 
            '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push([
            'INFORME DE AVANCE ACADÉMICO', 
            '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push([
            $rangoFechasHeader, 
            '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push(['']); 
        $this->currentRow++;

        $this->dataRows->push([
            'DOCENTE', 'MES', 'SEMANA', 'FECHA', 'CURSO', 'TEMA DESARROLLADO', 'AULA', 'TURNO', 'HORA ENTRADA', 'HORA SALIDA', 'HORAS DICTADAS', 'PAGO'
        ]);
        $this->currentRow++; 

        $grandTotalHoras = 0;
        $grandTotalPago = 0;

        foreach ($this->processedData as $docenteId => $docenteData) { 
            $docente = $docenteData['docente_info'];
            $nombreDocente = $docente->nombre . ' ' . $docente->apellido_paterno;
            
            $this->dataRows->push([$nombreDocente, '', '', '', '', '', '', '', '', '', '', '']);
            $this->currentRow++;

            $docenteTotalHoras = 0;
            $docenteTotalPago = 0;

            ksort($docenteData['months']); 

            foreach ($docenteData['months'] as $monthKey => $monthData) {
                $monthName = strtoupper($monthData['month_name']); 
                
                $this->dataRows->push(['', $monthName, '', '', '', '', '', '', '', '', '', '']);
                $this->currentRow++;

                $monthTotalHoras = 0;
                $monthTotalPago = 0;
                
                ksort($monthData['weeks']);

                foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                    $this->dataRows->push(['', '', 'SEMANA ' . $weekNumber, '', '', '', '', '', '', '', '', '']);
                    $this->currentRow++;

                    $weekTotalHoras = 0;
                    $weekTotalPago = 0;

                    foreach ($weekData['details'] as $detail) {
                        $this->dataRows->push([
                            '', '', '', 
                            Carbon::parse($detail['fecha'])->format('d/m/Y'), 
                            $detail['curso'],
                            $detail['tema_desarrollado'],
                            $detail['aula'],
                            $detail['turno'],
                            $detail['hora_entrada'],
                            $detail['hora_salida'],
                            number_format($detail['horas_dictadas'], 2),
                            'S/ ' . number_format($detail['pago'], 2, '.', ','),
                        ]);
                        $this->currentRow++;
                        $weekTotalHoras += $detail['horas_dictadas'];
                        $weekTotalPago += $detail['pago'];
                    }
                    $this->dataRows->push([
                        '', '', '', '', '', '', '', '', '', 'TOTAL SEMANA ' . $weekNumber,
                        number_format($weekTotalHoras, 2),
                        'S/ ' . number_format($weekTotalPago, 2, '.', ','),
                    ]);
                    $this->currentRow++;
                    $monthTotalHoras += $weekTotalHoras;
                    $monthTotalPago += $weekTotalPago;
                }
                $this->dataRows->push([
                    '', '', '', '', '', '', '', '', '', 'TOTAL MES ' . $monthName,
                    number_format($monthTotalHoras, 2),
                    'S/ ' . number_format($monthTotalPago, 2, '.', ','),
                ]);
                $this->currentRow++;
                $docenteTotalHoras += $monthTotalHoras;
                $docenteTotalPago += $monthTotalPago; 
            }
            $this->dataRows->push([
                '', '', '', '', '', '', '', '', '', 'TOTAL ' . $nombreDocente,
                number_format($docenteTotalHoras, 2),
                'S/ ' . number_format($docenteTotalPago, 2, '.', ','),
            ]);
            $this->currentRow++;

            $grandTotalHoras += $docenteTotalHoras;
            $grandTotalPago += $docenteTotalPago;

            $this->dataRows->push(['', '', '', '', '', '', '', '', '', '', '', '']);
            $this->currentRow++;
        }

        $this->dataRows->push([
            '', '', '', '', '', '', '', '', '', 'TOTAL GENERAL',
            number_format($grandTotalHoras, 2),
            'S/ ' . number_format($grandTotalPago, 2, '.', ','),
        ]);
        $this->currentRow++;

        return $this->dataRows;
    }

    public function headings(): array
    {
        return [];
    }

    public function map($row): array
    {
        return $row;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $startHeaderRow = 1; 
                $endHeaderRow = 7; 

                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', 'CENTRO PRE UNIVERSITARIO');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A4:L4');
                $sheet->setCellValue('A4', 'REPORTE DE ASISTENCIA DOCENTE');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A5:L5');
                $sheet->setCellValue('A5', 'INFORME DE AVANCE ACADÉMICO');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A6:L6');
                $rangoFechasHeader = 'PERIODO: ';
                if ($this->fechaInicio && $this->fechaFin) {
                    $rangoFechasHeader .= Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($this->fechaFin)->format('d/m/Y');
                } elseif ($this->selectedMonth && $this->selectedYear) {
                    $rangoFechasHeader .= Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->locale('es')->monthName . ' ' . $this->selectedYear;
                } else {
                    $rangoFechasHeader .= 'Todo el Historial';
                }
                if ($this->selectedCicloAcademico) {
                    $rangoFechasHeader .= ' - CICLO: ' . $this->selectedCicloAcademico;
                }
                $sheet->setCellValue('A6', $rangoFechasHeader);
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A8:L8')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF366092']], 
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);
                $sheet->getRowDimension(8)->setRowHeight(20); 

                $actualRowIndex = $endHeaderRow + 2;

                foreach ($this->processedData as $docenteId => $docenteData) {
                    $docenteStartRow = $actualRowIndex;
                    $actualRowIndex++; 

                    $sheet->getStyle('A'.$docenteStartRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                    ]);

                    ksort($docenteData['months']); 
                    foreach ($docenteData['months'] as $monthKey => $monthData) {
                        $monthStartRow = $actualRowIndex;
                        $actualRowIndex++; 

                        $sheet->getStyle('B'.$monthStartRow)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11],
                        ]);
                        $sheet->getStyle('A'.$monthStartRow.':L'.$monthStartRow)->applyFromArray([
                            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']], 
                        ]);

                        ksort($monthData['weeks']);
                        foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                            $weekStartRow = $actualRowIndex;
                            $actualRowIndex++; 

                            $sheet->getStyle('C'.$weekStartRow)->applyFromArray([
                                'font' => ['bold' => true, 'size' => 10],
                            ]);
                            foreach ($weekData['details'] as $detail) {
                                $actualRowIndex++; 
                            }
                            $sheet->getStyle('J'.$actualRowIndex.':L'.$actualRowIndex)->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2EFDA']], 
                            ]);
                            $sheet->mergeCells('J'.$actualRowIndex.':K'.$actualRowIndex); 
                            $actualRowIndex++;
                            $sheet->mergeCells('C'.$weekStartRow.':C'.($actualRowIndex - 2));
                            $sheet->getStyle('C'.$weekStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                        }
                        $sheet->getStyle('J'.$actualRowIndex.':L'.$actualRowIndex)->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6E0B4']], 
                        ]);
                        $sheet->mergeCells('J'.$actualRowIndex.':K'.$actualRowIndex); 
                        $actualRowIndex++;
                        $sheet->mergeCells('B'.$monthStartRow.':B'.($actualRowIndex - 2));
                        $sheet->getStyle('B'.$monthStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                    $sheet->getStyle('J'.$actualRowIndex.':L'.$actualRowIndex)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFA9D18E']], 
                    ]);
                    $sheet->mergeCells('J'.$actualRowIndex.':K'.$actualRowIndex); 
                    $actualRowIndex++;
                    $sheet->mergeCells('A'.$docenteStartRow.':A'.($actualRowIndex - 2));
                    $sheet->getStyle('A'.$docenteStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    $actualRowIndex++;
                }

                $sheet->getStyle('J'.$actualRowIndex.':L'.$actualRowIndex)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2F75B5']], 
                ]);
                $sheet->mergeCells('J'.$actualRowIndex.':K'.$actualRowIndex); 
                $actualRowIndex++;

                $sheet->getStyle('A8:L'.($actualRowIndex - 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);

                foreach (range('A', 'L') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
