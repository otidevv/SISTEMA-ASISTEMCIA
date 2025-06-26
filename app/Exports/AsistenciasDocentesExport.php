<?php

namespace App\Exports;

use App\Models\AsistenciaDocente;
use App\Models\User; 
use App\Models\HorarioDocente;
use AppModels\PagoDocente; // Importa el modelo PagoDocente
use AppModels\Ciclo; // Importa el modelo Ciclo para la relación

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Illuminate\Support\Collection; 

class AsistenciasDocentesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    // La tarifa por minuto fija se remueve si es dinámica por docente.
    // const TARIFA_POR_MINUTO = 3.00; 
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
        $this->selectedMonth = (int)($selectedMonth ?? Carbon::now()->month);
        $this->selectedYear = (int)($selectedYear ?? Carbon::now()->year);
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
        
        // ¡¡¡CORRECCIÓN CLAVE AQUÍ!!!
        // Filtra por ciclo académico a través de la relación con HorarioDocente y luego con Ciclo
        // Asume que la relación 'ciclo' en HorarioDocente apunta a la columna 'codigo' en la tabla 'ciclos'
        if ($this->selectedCicloAcademico) {
            $asistenciasRawQuery->whereHas('horario.ciclo', function ($query) use ($selectedCicloAcademico) {
                // Aquí 'codigo' se refiere a la columna 'codigo' en tu tabla 'ciclos'
                $query->where('codigo', $selectedCicloAcademico); 
            });
        }

        $asistenciasRaw = $asistenciasRawQuery->get();

        $processedData = [];
        $groupedByDocenteDateHorario = $asistenciasRaw->groupBy(function ($item) {
            return $item->docente_id . '_' . Carbon::parse($item->fecha_hora)->format('Y-m-d') . '_' . $item->horario_id;
        });

        foreach ($groupedByDocenteDateHorario as $groupKey => $records) {
            $docenteId = $records->first()->docente_id;
            $fecha = Carbon::parse($records->first()->fecha_hora)->format('Y-m-d');
            $horarioId = $records->first()->horario_id;

            $entrada = $records->where('estado', 'entrada')->sortBy('fecha_hora')->first();
            $salida = $records->where('estado', 'salida')->sortByDesc('fecha_hora')->first();

            $horaEntrada = $entrada ? Carbon::parse($entrada->fecha_hora) : null;
            $horaSalida = $salida ? Carbon::parse($salida->fecha_hora) : null;
            $temaDesarrollado = $salida->tema_desarrollado ?? ($entrada->tema_desarrollado ?? 'N/A');

            $horasDictadas = 0;
            $montoTotal = 0;

            // Si 'horas_dictadas' ya están en la DB, úsalas.
            if ($salida && $salida->horas_dictadas !== null) { 
                $horasDictadas = $salida->horas_dictadas;
            } elseif ($entrada && $entrada->horas_dictadas !== null) {
                $horasDictadas = $entrada->horas_dictadas;
            } else { // Recalcula si no están en DB
                if ($horaEntrada && $horaSalida && Carbon::parse($salida->fecha_hora)->greaterThan(Carbon::parse($entrada->fecha_hora))) {
                    $minutosDictados = Carbon::parse($salida->fecha_hora)->diffInMinutes($horaEntrada);
                    $horasDictadas = round($minutosDictados / 60, 2);
                }
            }
            
            // Obtener la tarifa dinámica desde PagoDocente
            $tarifaPorHoraAplicable = 0;
            if ($horasDictadas > 0 && $entrada) { // Solo si hay horas y un punto de referencia de fecha
                $pagoDocente = PagoDocente::where('docente_id', $docenteId)
                    ->whereDate('fecha_inicio', '<=', $entrada->fecha_hora) // Fecha del registro de asistencia
                    ->whereDate('fecha_fin', '>=', $entrada->fecha_hora)
                    ->first();
                if ($pagoDocente) {
                    $tarifaPorHoraAplicable = $pagoDocente->tarifa_por_hora;
                }
            }
            // ¡¡¡CORRECCIÓN DE FÓRMULA DE PAGO AQUÍ!!!
            // Si tarifa_por_hora es por HORA, se multiplica directamente por las horas dictadas (que ya están en horas).
            $montoTotal = $horasDictadas * $tarifaPorHoraAplicable; 

            $processedData[$docenteId]['docente_info'] = $records->first()->docente;
            $monthKey = Carbon::parse($fecha)->format('Y-m');
            $processedData[$docenteId]['months'][$monthKey]['month_name'] = Carbon::parse($fecha)->locale('es')->monthName;
            $weekKey = Carbon::parse($fecha)->weekOfYear;
            $processedData[$docenteId]['months'][$monthKey]['weeks'][$weekKey]['week_number'] = $weekKey;
            
            $processedData[$docenteId]['months'][$monthKey]['weeks'][$weekKey]['details'][] = [
                'fecha' => $fecha,
                'curso' => $records->first()->horario->curso->nombre ?? 'N/A',
                'tema_desarrollado' => $temaDesarrollado,
                'aula' => $records->first()->horario->aula->nombre ?? 'N/A',
                'turno' => $records->first()->horario->turno ?? 'N/A',
                'hora_entrada' => $horaEntrada ? $horaEntrada->format('H:i a') : 'N/A',
                'hora_salida' => $horaSalida ? $horaSalida->format('H:i a') : 'N/A',
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

        // Rango de fechas dinámico para el encabezado del reporte
        $rangoFechasHeader = 'PERIODO: ';
        if ($this->fechaInicio && $this->fechaFin) {
            $rangoFechasHeader .= Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' - ' . Carbon::parse($this->fechaFin)->format('d/m/Y');
        } elseif ($this->selectedMonth && $this->selectedYear) {
            $rangoFechasHeader .= Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->locale('es')->monthName . ' ' . $this->selectedYear;
        } else {
            $rangoFechasHeader .= 'Todo el Historial';
        }

        // Si se seleccionó un ciclo académico, agregarlo al encabezado
        if ($this->selectedCicloAcademico) {
            $rangoFechasHeader .= ' - CICLO: ' . $this->selectedCicloAcademico;
        }


        // Agregar encabezados generales
        $this->dataRows->push([
            'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS', 
            '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push([
            'CENTRO PRE UNIVERSITARIO', 
            '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push(['']); 
        $this->currentRow++;
        $this->dataRows->push([
            'REPORTE DE ASISTENCIA DOCENTE', 
            '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push([
            'INFORME DE AVANCE ACADÉMICO', 
            '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push([
            $rangoFechasHeader, 
            '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        $this->dataRows->push(['']); 
        $this->currentRow++;

        // Encabezados de la tabla principal
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
                
                // === Estilos y combinaciones de celdas para el encabezado ===
                $startHeaderRow = 1; 
                $endHeaderRow = 7; 

                // UNAMAD (A1:L1)
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // CENTRO PRE UNIVERSITARIO (A2:L2)
                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', 'CENTRO PRE UNIVERSITARIO');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // REPORTE DE ASISTENCIA DOCENTE (A4:L4)
                $sheet->mergeCells('A4:L4');
                $sheet->setCellValue('A4', 'REPORTE DE ASISTENCIA DOCENTE');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // INFORME DE AVANCE ACADÉMICO (A5:L5)
                $sheet->mergeCells('A5:L5');
                $sheet->setCellValue('A5', 'INFORME DE AVANCE ACADÉMICO');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Encabezado de Periodo (A6:L6)
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

                // Encabezados de la tabla (fila 8)
                $sheet->getStyle('A8:L8')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF366092']], 
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);
                $sheet->getRowDimension(8)->setRowHeight(20); 

                // Lógica para aplicar estilos y combinaciones de celdas a los datos procesados
                $actualRowIndex = $endHeaderRow + 2; // La primera fila de datos después de los encabezados (fila 9)

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
                            $sheet->mergeCells('C'.$weekStartRow.':C'.($actualRowIndex - 1));
                            $sheet->getStyle('C'.$weekStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                        }
                        $sheet->getStyle('J'.$actualRowIndex.':L'.$actualRowIndex)->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6E0B4']], 
                        ]);
                        $sheet->mergeCells('J'.$actualRowIndex.':K'.$actualRowIndex); 
                        $actualRowIndex++;
                        $sheet->mergeCells('B'.$monthStartRow.':B'.($actualRowIndex - 1));
                        $sheet->getStyle('B'.$monthStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                    $sheet->getStyle('J'.$actualRowIndex.':L'.$actualRowIndex)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFA9D18E']], 
                    ]);
                    $sheet->mergeCells('J'.$actualRowIndex.':K'.$actualRowIndex); 
                    $actualRowIndex++;
                    $sheet->mergeCells('A'.$docenteStartRow.':A'.($actualRowIndex - 1));
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