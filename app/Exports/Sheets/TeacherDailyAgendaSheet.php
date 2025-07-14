<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherDailyAgendaSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithStyles
{
    private $docenteData;
    private $docenteName;
    private $filterPeriodHeader;
    private $filterCicloHeader;
    private $currentRow = 1;

    public function __construct(array $docenteData, string $docenteName, string $filterPeriodHeader, ?string $filterCicloHeader)
    {
        $this->docenteData = $docenteData;
        $this->docenteName = $docenteName;
        $this->filterPeriodHeader = $filterPeriodHeader;
        $this->filterCicloHeader = $filterCicloHeader;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        $title = substr(preg_replace('/[\\\\\/:\*\?\[\]]/', '', $this->docenteName), 0, 31);
        return $title ?: 'Docente';
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $dataRows = new Collection();
        $this->currentRow = 1;

        // ENCABEZADO INSTITUCIONAL - igual que la imagen original
        $dataRows->push([
            'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS', 
            '', '', '', '', '', '', '', '', '', '', '', '', '', '' 
        ]);
        $this->currentRow++;
        
        $dataRows->push([
            'CENTRO PRE UNIVERSITARIO', 
            '', '', '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        
        $dataRows->push(['']); 
        $this->currentRow++;
        
        $dataRows->push([
            'REPORTE DE ASISTENCIA DOCENTE', 
            '', '', '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        
        $dataRows->push([
            'INFORME DE AVANCE ACADÉMICO', 
            '', '', '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        
        $dataRows->push([
            $this->filterPeriodHeader . ($this->filterCicloHeader ? ' ' . $this->filterCicloHeader : ''), 
            '', '', '', '', '', '', '', '', '', '', '', '', '', ''
        ]);
        $this->currentRow++;
        
        $dataRows->push(['']); 
        $this->currentRow++;

        // Fila del nombre del Docente
        $dataRows->push([$this->docenteName, '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $this->currentRow++;

        // Encabezados de la tabla
        $dataRows->push([
            'MES', 'SEMANA', 'FECHA', 'CURSO', 'TEMA DESARROLLADO', 'AULA', 'TURNO', 
            'HORA ENTRADA', 'HORA SALIDA', 'TARDANZA (min)', 'HORAS DICTADAS', 'PAGO', 'ESTADO', 'NOTA DE SALIDA' 
        ]);
        $this->currentRow++;

        // Procesar datos usando la estructura original
        $docenteTotalHoras = 0;
        $docenteTotalPago = 0;

        if (isset($this->docenteData['months'])) {
            ksort($this->docenteData['months']); 
            foreach ($this->docenteData['months'] as $monthKey => $monthData) {
                $monthName = strtoupper($monthData['month_name']); 
                
                // Fila del Mes
                $dataRows->push([$monthName, '', '', '', '', '', '', '', '', '', '', '', '', '']);
                $this->currentRow++;

                $monthTotalHoras = 0;
                $monthTotalPago = 0;
                
                if (isset($monthData['weeks'])) {
                    ksort($monthData['weeks']); 
                    foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                        // Fila de la Semana
                        $dataRows->push(['', 'SEMANA ' . $weekNumber, '', '', '', '', '', '', '', '', '', '', '', '']);
                        $this->currentRow++;

                        $weekTotalHoras = 0;
                        $weekTotalPago = 0;

                        // Filas de Detalles
                        if (isset($weekData['details'])) {
                            foreach ($weekData['details'] as $detail) {
                                $dataRows->push([
                                    '', '', // Columnas de agrupación vacías
                                    Carbon::parse($detail['fecha'])->format('d/m/Y'), 
                                    $detail['curso'],
                                    $detail['tema_desarrollado'],
                                    $detail['aula'],
                                    $detail['turno'],
                                    $detail['hora_entrada'],
                                    $detail['hora_salida'],
                                    $detail['minutos_tardanza'] > 0 ? $detail['minutos_tardanza'] : '', 
                                    number_format($detail['horas_dictadas'], 2) . ' Horas/Min', 
                                    'S/ ' . number_format($detail['pago'], 2, '.', ','), 
                                    $detail['estado_sesion'], 
                                    $detail['salida_source'], 
                                ]);
                                $this->currentRow++;
                                
                                $weekTotalHoras += $detail['horas_dictadas'];
                                $weekTotalPago += $detail['pago'];
                            }
                        }
                        
                        // Fila de Total Semanal
                        $dataRows->push([
                            '', '', '', '', '', '', '', '', 'TOTAL SEMANA ' . $weekNumber,
                            '', 
                            number_format($weekTotalHoras, 2),
                            'S/ ' . number_format($weekTotalPago, 2, '.', ','),
                            '', 
                            '', 
                        ]);
                        $this->currentRow++;
                        $monthTotalHoras += $weekTotalHoras;
                        $monthTotalPago += $weekTotalPago;
                    }
                }
                
                // Fila de Total Mensual
                $dataRows->push([
                    '', '', '', '', '', '', '', '', 'TOTAL MES ' . $monthName,
                    '', 
                    number_format($monthTotalHoras, 2),
                    'S/ ' . number_format($monthTotalPago, 2, '.', ','),
                    '', 
                    '', 
                ]);
                $this->currentRow++;
                $docenteTotalHoras += $monthTotalHoras;
                $docenteTotalPago += $monthTotalPago; 
            }
        }

        // Fila de Total por Docente
        $dataRows->push([
            '', '', '', '', '', '', '', '', 'TOTAL ' . $this->docenteName,
            '', 
            number_format($docenteTotalHoras, 2) . ' HORAS',
            'S/ ' . number_format($docenteTotalPago, 2, '.', ','),
            '', 
            '', 
        ]);
        $this->currentRow++;

        return $dataRows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return $row;
    }

    /**
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            4 => ['font' => ['bold' => true, 'size' => 12]],
            5 => ['font' => ['bold' => true, 'size' => 12]],
            6 => ['font' => ['bold' => true, 'size' => 12]],
            9 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDEBF7']]]
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $totalColumns = 'O'; 

                // ENCABEZADOS PRINCIPALES
                $sheet->mergeCells('A1:'.$totalColumns.'1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:'.$totalColumns.'2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A4:'.$totalColumns.'4');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A5:'.$totalColumns.'5');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A6:'.$totalColumns.'6');
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // NOMBRE DEL DOCENTE (Fila 8)
                $teacherNameRow = 8;
                $sheet->mergeCells('A'.$teacherNameRow.':'.$totalColumns.$teacherNameRow);
                $sheet->getStyle('A'.$teacherNameRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDEBF7']], 
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // ENCABEZADOS DE TABLA (Fila 9)
                $tableHeaderRow = 9;
                $sheet->getStyle('A'.$tableHeaderRow.':'.$totalColumns.$tableHeaderRow)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF366092']], 
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);
                $sheet->getRowDimension($tableHeaderRow)->setRowHeight(20); 

                // APLICAR BORDES A TODA LA TABLA
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A'.$tableHeaderRow.':'.$totalColumns.$lastRow)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);

                // CONFIGURAR ANCHOS DE COLUMNA
                $sheet->getColumnDimension('A')->setWidth(15); // MES
                $sheet->getColumnDimension('B')->setWidth(15); // SEMANA
                $sheet->getColumnDimension('C')->setWidth(12); // FECHA
                $sheet->getColumnDimension('D')->setWidth(20); // CURSO
                $sheet->getColumnDimension('E')->setWidth(30); // TEMA DESARROLLADO
                $sheet->getColumnDimension('F')->setWidth(10); // AULA
                $sheet->getColumnDimension('G')->setWidth(12); // TURNO
                $sheet->getColumnDimension('H')->setWidth(15); // HORA ENTRADA
                $sheet->getColumnDimension('I')->setWidth(15); // HORA SALIDA
                $sheet->getColumnDimension('J')->setWidth(12); // TARDANZA
                $sheet->getColumnDimension('K')->setWidth(15); // HORAS DICTADAS
                $sheet->getColumnDimension('L')->setWidth(12); // PAGO
                $sheet->getColumnDimension('M')->setWidth(15); // ESTADO
                $sheet->getColumnDimension('N')->setWidth(15); // NOTA DE SALIDA

                // FUSIONAR CELDAS PARA AGRUPACIONES
                $this->mergeCellsForGroupedData($sheet);
                
                // APLICAR FORMATO CONDICIONAL
                $this->applyConditionalFormatting($sheet, $lastRow);
                
                // AGREGAR PIE DE PÁGINA
                $this->addFooter($sheet, $lastRow);
            }
        ];
    }

    private function mergeCellsForGroupedData($sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
        $currentGroups = ['mes' => '', 'semana' => ''];
        $startRows = ['mes' => 10, 'semana' => 10];
        
        for ($row = 10; $row <= $lastRow; $row++) {
            $mes = $sheet->getCell('A' . $row)->getValue();
            $semana = $sheet->getCell('B' . $row)->getValue();
            
            // Fusión para columna MES
            if ($mes !== '' && $mes !== $currentGroups['mes']) {
                if ($currentGroups['mes'] !== '' && $startRows['mes'] < $row - 1) {
                    $sheet->mergeCells('A' . $startRows['mes'] . ':A' . ($row - 1));
                    $sheet->getStyle('A' . $startRows['mes'])->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }
                $startRows['mes'] = $row;
                $currentGroups['mes'] = $mes;
            }
            
            // Fusión para columna SEMANA
            if ($semana !== '' && $semana !== $currentGroups['semana']) {
                if ($currentGroups['semana'] !== '' && $startRows['semana'] < $row - 1) {
                    $sheet->mergeCells('B' . $startRows['semana'] . ':B' . ($row - 1));
                    $sheet->getStyle('B' . $startRows['semana'])->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }
                $startRows['semana'] = $row;
                $currentGroups['semana'] = $semana;
            }
        }
        
        // Fusionar los grupos finales
        if ($startRows['mes'] < $lastRow) {
            $sheet->mergeCells('A' . $startRows['mes'] . ':A' . $lastRow);
            $sheet->getStyle('A' . $startRows['mes'])->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
        if ($startRows['semana'] < $lastRow) {
            $sheet->mergeCells('B' . $startRows['semana'] . ':B' . $lastRow);
            $sheet->getStyle('B' . $startRows['semana'])->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);
        }
    }

    /**
     * Aplica formato condicional con colores
     */
    private function applyConditionalFormatting($sheet, $lastRow)
    {
        // Aplicar colores según el estado
        for ($row = 10; $row <= $lastRow; $row++) {
            $estadoCell = $sheet->getCell('M' . $row)->getValue();
            
            if (strpos($estadoCell, 'PENDIENTE') !== false) {
                // Fondo amarillo claro para pendientes
                $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF99']]
                ]);
            } elseif (strpos($estadoCell, 'SIN REGISTRO') !== false) {
                // Fondo rojo claro para sin registro
                $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE6E6']]
                ]);
            } elseif (strpos($estadoCell, 'COMPLETADA') !== false) {
                // Fondo verde claro para completadas
                $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE6F7E6']]
                ]);
            }
        }
    }

    /**
     * Agregar pie de página profesional
     */
    private function addFooter($sheet, $lastRow)
    {
        // Saltar líneas después de la tabla
        $footerRow = $lastRow + 3;
        
        // Línea horizontal
        $sheet->setCellValue('A' . $footerRow, 'Vº Bº');
        $sheet->mergeCells('A' . $footerRow . ':C' . $footerRow);
        
        // Información del responsable
        $footerRow += 3;
        $sheet->setCellValue('K' . $footerRow, 'Bach. Roy Kevin Bonifacio Fernandez');
        $sheet->mergeCells('K' . $footerRow . ':O' . $footerRow);
        
        $footerRow++;
        $sheet->setCellValue('I' . $footerRow, 'SERVICIO ESPECIALIZADO EN GESTIÓN DE SERVICIOS INFORMÁTICOS DEL CEPRE UNAMAD');
        $sheet->mergeCells('I' . $footerRow . ':O' . $footerRow);
        
        // Estilo para el pie de página
        $sheet->getStyle('A' . ($lastRow + 3) . ':O' . $footerRow)->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => ['argb' => 'FF666666']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        
        // Estilo especial para la firma
        $sheet->getStyle('K' . ($footerRow - 1))->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['argb' => 'FF000000']
            ]
        ]);
    }
}