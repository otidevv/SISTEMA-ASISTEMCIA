<?php

namespace App\Exports\Sheets; // Asegúrate de que el namespace sea este

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherDailyAgendaSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    private $docenteData;
    private $docenteName;
    private $filterPeriodHeader;
    private $filterCicloHeader;
    private $currentRow = 1; // Track current row for styling

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
        // Use the teacher's name as the sheet title
        // Excel sheet names have a max length of 31 characters and cannot contain certain characters.
        // We'll sanitize it to be safe.
        $title = substr(preg_replace('/[\\\\\/:\*\?\[\]]/', '', $this->docenteName), 0, 31);
        return $title ?: 'Docente'; // Fallback if name is empty after sanitization
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $dataRows = new Collection();
        $this->currentRow = 1; // Reset row counter for each sheet

        // Header section for each sheet (similar to main report)
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

        // Teacher's name row
        $dataRows->push([$this->docenteName, '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $this->currentRow++;

        // Table Headers
        $dataRows->push([
            'MES', 'SEMANA', 'FECHA', 'CURSO', 'TEMA DESARROLLADO', 'AULA', 'TURNO', 
            'HORA ENTRADA', 'HORA SALIDA', 'TARDANZA (min)', 'HORAS DICTADAS', 'PAGO', 'ESTADO', 'NOTA DE SALIDA' 
        ]);
        $this->currentRow++; 

        $docenteTotalHoras = 0;
        $docenteTotalPago = 0;

        ksort($this->docenteData['months']); 
        foreach ($this->docenteData['months'] as $monthKey => $monthData) {
            $monthName = strtoupper($monthData['month_name']); 
            
            // Month row
            $dataRows->push([$monthName, '', '', '', '', '', '', '', '', '', '', '', '', '']);
            $this->currentRow++;

            $monthTotalHoras = 0;
            $monthTotalPago = 0;
            
            ksort($monthData['weeks']); 
            foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                // Week row
                $dataRows->push(['', 'SEMANA ' . $weekNumber, '', '', '', '', '', '', '', '', '', '', '', '']);
                $this->currentRow++;

                $weekTotalHoras = 0;
                $weekTotalPago = 0;

                // Detail rows
                foreach ($weekData['details'] as $detail) {
                    $dataRows->push([
                        '', '', // Grouping columns empty
                        Carbon::parse($detail['fecha'])->format('d/m/Y'), 
                        $detail['curso'],
                        $detail['tema_desarrollado'],
                        $detail['aula'],
                        $detail['turno'],
                        $detail['hora_entrada'],
                        $detail['hora_salida'],
                        $detail['minutos_tardanza'] > 0 ? $detail['minutos_tardanza'] : '', 
                        number_format($detail['horas_dictadas'], 2),
                        'S/ ' . number_format($detail['pago'], 2, '.', ','),
                        $detail['estado_sesion'], 
                        $detail['salida_source'], 
                    ]);
                    $this->currentRow++;
                    $weekTotalHoras += $detail['horas_dictadas'];
                    $weekTotalPago += $detail['pago'];
                }
                // Weekly Total row
                $dataRows->push([
                    '', '', '', '', '', '', '', '', 'TOTAL SEMANA ' . $weekNumber,
                    '', // Empty for Tardanza
                    number_format($weekTotalHoras, 2),
                    'S/ ' . number_format($weekTotalPago, 2, '.', ','),
                    '', // Empty for Estado
                    '', // Empty for Nota de Salida
                ]);
                $this->currentRow++;
                $monthTotalHoras += $weekTotalHoras;
                $monthTotalPago += $weekTotalPago;
            }
            // Monthly Total row
            $dataRows->push([
                '', '', '', '', '', '', '', '', 'TOTAL MES ' . $monthName,
                '', // Empty for Tardanza
                number_format($monthTotalHoras, 2),
                'S/ ' . number_format($monthTotalPago, 2, '.', ','),
                '', // Empty for Estado
                '', // Empty for Nota de Salida
            ]);
            $this->currentRow++;
            $docenteTotalHoras += $monthTotalHoras;
            $docenteTotalPago += $monthTotalPago; 
        }
        // Teacher's Total row
        $dataRows->push([
            '', '', '', '', '', '', '', '', 'TOTAL ' . $this->docenteName,
            '', // Empty for Tardanza
            number_format($docenteTotalHoras, 2),
            'S/ ' . number_format($docenteTotalPago, 2, '.', ','),
            '', // Empty for Estado
            '', // Empty for Nota de Salida
        ]);
        $this->currentRow++;

        return $dataRows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return []; // Headings are generated within the collection method
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return $row; // Rows are already formatted
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $startHeaderRow = 1; 
                $endHeaderRow = 7; 
                $totalColumns = 'O'; // 15 columns (A-O)

                // Global Headers (Rows 1-6)
                $sheet->mergeCells('A1:'.$totalColumns.'1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A2:'.$totalColumns.'2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A4:'.$totalColumns.'4');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A5:'.$totalColumns.'5');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells('A6:'.$totalColumns.'6');
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);

                // Teacher Name Header (Row 8)
                $teacherNameRow = 8;
                $sheet->mergeCells('A'.$teacherNameRow.':'.$totalColumns.$teacherNameRow);
                $sheet->getStyle('A'.$teacherNameRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDEBF7']], // Light Blue for teacher
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                ]);

                // Table Headers (Row 9)
                $tableHeaderRow = 9;
                $sheet->getStyle('A'.$tableHeaderRow.':'.$totalColumns.$tableHeaderRow)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF366092']], // Dark Blue
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);
                $sheet->getRowDimension($tableHeaderRow)->setRowHeight(20); 

                $actualRowIndex = $tableHeaderRow + 1; // Start data rows from row 10

                // Apply styles and merges for grouped data
                $docenteData = $this->docenteData; // Access the processed data for this sheet
                
                ksort($docenteData['months']); 
                foreach ($docenteData['months'] as $monthKey => $monthData) {
                    $monthStartRow = $actualRowIndex;
                    $actualRowIndex++; 

                    // Month row style
                    $sheet->getStyle('A'.$monthStartRow.':'.$totalColumns.$monthStartRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']], // Lighter blue for month
                    ]);
                    $sheet->mergeCells('A'.$monthStartRow.':B'.$monthStartRow); // Merge 'MES' and 'SEMANA' for month title

                    ksort($monthData['weeks']);
                    foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                        $weekStartRow = $actualRowIndex;
                        $actualRowIndex++; 

                        // Week row style
                        $sheet->getStyle('B'.$weekStartRow.':'.$totalColumns.$weekStartRow)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEBF1DE']], // Light green for week
                        ]);
                        $sheet->mergeCells('B'.$weekStartRow.':C'.$weekStartRow); // Merge 'SEMANA' with 'FECHA' for week title

                        // Count detail rows for the week
                        foreach ($weekData['details'] as $detail) {
                            $actualRowIndex++; 
                        }
                        
                        // Weekly Total row style
                        $sheet->getStyle('I'.$actualRowIndex.':'.$totalColumns.$actualRowIndex)->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2EFDA']], // Green for weekly total
                        ]);
                        $sheet->mergeCells('I'.$actualRowIndex.':J'.$actualRowIndex); // Merge "TOTAL SEMANA" with "TARDANZA"
                        $actualRowIndex++; 

                        // Merge 'SEMANA' column for the group
                        $sheet->mergeCells('B'.$weekStartRow.':B'.($actualRowIndex - 2));
                        $sheet->getStyle('B'.$weekStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                    // Monthly Total row style
                    $sheet->getStyle('I'.$actualRowIndex.':'.$totalColumns.$actualRowIndex)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6E0B4']], // Darker green for monthly total
                    ]);
                    $sheet->mergeCells('I'.$actualRowIndex.':J'.$actualRowIndex); 
                    $actualRowIndex++;
                    
                    // Merge 'MES' column for the group
                    $sheet->mergeCells('A'.$monthStartRow.':A'.($actualRowIndex - 2));
                    $sheet->getStyle('A'.$monthStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
                // Teacher's Total row style - This is the last row for the teacher's section
                $sheet->getStyle('I'.$actualRowIndex.':'.$totalColumns.$actualRowIndex)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFA9D18E']], // Even darker green for teacher total
                ]);
                $sheet->mergeCells('I'.$actualRowIndex.':J'.$actualRowIndex); 
                $actualRowIndex++;
                
                // Apply borders to all data cells for this sheet
                // Note: This range needs to be dynamic based on the actual content of the sheet
                $sheet->getStyle('A'.$tableHeaderRow.':'.$totalColumns.($actualRowIndex - 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                ]);

                // Auto-size columns for this sheet
                foreach (range('A', $totalColumns) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
