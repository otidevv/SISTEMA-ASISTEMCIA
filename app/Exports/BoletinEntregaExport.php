<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class BoletinEntregaExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles,
    WithColumnWidths,
    WithEvents
{
    protected $data;
    protected $cursos;
    protected $mes;
    protected $anio;

    public function __construct($data, $cursos, $mes = null, $anio = null)
    {
        $this->data = collect($data);
        $this->cursos = $cursos;
        $this->mes = $mes ?? date('F');
        $this->anio = $anio ?? date('Y');
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $headings = ['Código', 'Estudiante'];
        foreach ($this->cursos as $curso) {
            $headings[] = $curso->nombre;
        }
        // Agregar columnas de resumen
        $headings[] = 'Total Entregas';
        $headings[] = '% Entrega';
        $headings[] = 'Faltas';
        return $headings;
    }

    public function map($row): array
    {
        $totalCursos = count($row['courses']);
        $entregados = 0;
        
        $mappedRow = [
            $row['codigo'] ?? 'N/A',
            $row['student']
        ];
        
        foreach ($row['courses'] as $course) {
            if ($course['entregado']) {
                $entregados++;
                $fecha = $course['fecha_entrega'] 
                    ? \Carbon\Carbon::parse($course['fecha_entrega'])->format('d/m/Y') 
                    : '';
                $mappedRow[] = '✓ ' . $fecha;
            } else {
                $mappedRow[] = 'FALTA';
            }
        }
        
        // Columnas de resumen
        $mappedRow[] = $entregados;
        $mappedRow[] = $totalCursos > 0 ? round(($entregados / $totalCursos) * 100) . '%' : '0%';
        $mappedRow[] = $totalCursos - $entregados;
        
        return $mappedRow;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // Código
            'B' => 25, // Estudiante
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        
        // Estilo del título principal (si se agrega con eventos)
        // Estilo de encabezados
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Columna de código y estudiante
        $sheet->getStyle('A2:B' . $lastRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6']
            ],
            'font' => [
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Bordes para toda la tabla
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Alineación central para las celdas de contenido
        $sheet->getStyle('C2:' . $lastColumn . $lastRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();
                
                // Insertar fila de título
                $sheet->insertNewRowBefore(1, 1);
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->setCellValue('A1', 'CONTROL DE ENTREGA DE BOLETINES - ' . strtoupper($this->mes) . ' ' . $this->anio);
                
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '203864']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // Aplicar colores condicionales a las celdas de entrega
                $numCursos = count($this->cursos);
                $endColumn = chr(ord('C') + $numCursos - 1);
                
                for ($row = 3; $row <= $lastRow; $row++) {
                    for ($col = 'C'; $col <= $endColumn; $col++) {
                        $cellValue = $sheet->getCell($col . $row)->getValue();
                        
                        if (strpos($cellValue, '✓') !== false) {
                            // Entregado - Verde
                            $sheet->getStyle($col . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => '92D050']
                                ],
                                'font' => [
                                    'color' => ['rgb' => '006100'],
                                    'bold' => true
                                ]
                            ]);
                        } elseif ($cellValue === 'FALTA') {
                            // Falta - Rojo
                            $sheet->getStyle($col . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FF6B6B']
                                ],
                                'font' => [
                                    'color' => ['rgb' => 'FFFFFF'],
                                    'bold' => true
                                ]
                            ]);
                        }
                    }
                }
                
                // Estilo para columnas de resumen (últimas 3 columnas)
                $resumeStart = chr(ord($endColumn) + 1);
                $sheet->getStyle($resumeStart . '2:' . $lastColumn . '2')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ]
                ]);
                
                $sheet->getStyle($resumeStart . '3:' . $lastColumn . $lastRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E2F3']
                    ],
                    'font' => [
                        'bold' => true
                    ]
                ]);
                
                // Auto-ajustar altura de filas
                foreach (range(2, $lastRow) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(25);
                }
            }
        ];
    }
}