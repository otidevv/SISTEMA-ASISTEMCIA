<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CarnetsEntregaSheet implements FromCollection, WithEvents, WithColumnWidths, WithTitle
{
    protected $carnets;
    protected $nombreAula;

    public function __construct($carnets, $nombreAula)
    {
        $this->carnets = $carnets;
        $this->nombreAula = $nombreAula;
    }

    public function collection()
    {
        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // ========== ENCABEZADO PRINCIPAL ==========
                // Fila 1: Título principal
                $sheet->setCellValue('A1', 'CENTRO PREUNIVERSITARIO - UNAMAD');
                $sheet->mergeCells('A1:M1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1F4E78']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                $sheet->getRowDimension(1)->setRowHeight(25);
                
                // Fila 2: Subtítulo
                $sheet->setCellValue('A2', 'CONTROL DE ENTREGA DE CARNETS');
                $sheet->mergeCells('A2:M2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);
                
                // Fila 3: Información del aula y resumen
                $sheet->setCellValue('A3', 'AULA:');
                $sheet->setCellValue('B3', strtoupper($this->nombreAula));
                $sheet->mergeCells('B3:D3');
                
                // Calcular estadísticas
                $total = $this->carnets->count();
                $entregados = $this->carnets->where('entregado', true)->count();
                $pendientes = $total - $entregados;
                $porcentaje = $total > 0 ? round(($entregados / $total) * 100, 1) : 0;
                
                $sheet->setCellValue('F3', 'Total Carnets:');
                $sheet->setCellValue('G3', $total);
                $sheet->setCellValue('I3', 'Entregados:');
                $sheet->setCellValue('J3', $entregados);
                $sheet->setCellValue('K3', '(' . $porcentaje . '%)');
                
                // Estilos de la fila 3
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);
                $sheet->getStyle('B3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '4472C4']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);
                $sheet->getStyle('F3')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);
                $sheet->getStyle('G3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E7E6E6']
                    ]
                ]);
                $sheet->getStyle('I3')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);
                $sheet->getStyle('J3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C6EFCE']
                    ]
                ]);
                $sheet->getStyle('K3')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '006100']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);
                
                $sheet->getRowDimension(3)->setRowHeight(20);
                
                // Borde inferior del encabezado
                $sheet->getStyle('A3:M3')->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '4472C4']
                        ]
                    ]
                ]);
                
                // ========== ENCABEZADOS DE TABLA (fila 5) ==========
                $headers = [
                    'A5' => 'N°',
                    'B5' => 'CÓDIGO',
                    'C5' => 'DNI',
                    'D5' => 'APELLIDOS Y NOMBRES',
                    'E5' => 'CARRERA',
                    'F5' => 'TURNO',
                    'G5' => 'IMPRESO',
                    'H5' => 'FECHA IMP.',
                    'I5' => 'ESTADO ENTREGA',
                    'J5' => 'FECHA ENTREGA',
                    'K5' => 'ENTREGADO POR',
                    'L5' => 'FIRMA',
                    'M5' => 'OBSERVACIONES'
                ];
                
                foreach ($headers as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }
                
                $sheet->getStyle('A5:M5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 10
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF']
                        ]
                    ]
                ]);
                
                $sheet->getRowDimension(5)->setRowHeight(30);
                
                // ========== DATOS (desde fila 6) ==========
                $row = 6;
                $numero = 1;
                foreach ($this->carnets as $carnet) {
                    $sheet->setCellValue('A' . $row, $numero++);
                    $sheet->setCellValue('B' . $row, $carnet->codigo_carnet);
                    $sheet->setCellValue('C' . $row, $carnet->estudiante->numero_documento ?? '');
                    $sheet->setCellValue('D' . $row, strtoupper($carnet->nombre_completo));
                    $sheet->setCellValue('E' . $row, strtoupper($carnet->carrera->nombre ?? ''));
                    $sheet->setCellValue('F' . $row, strtoupper($carnet->turno->nombre ?? ''));
                    $sheet->setCellValue('G' . $row, $carnet->impreso ? 'SÍ' : 'NO');
                    $sheet->setCellValue('H' . $row, $carnet->fecha_impresion ? $carnet->fecha_impresion->format('d/m/Y') : '');
                    $sheet->setCellValue('I' . $row, $carnet->estado_entrega);
                    $sheet->setCellValue('J' . $row, $carnet->fecha_entrega ? $carnet->fecha_entrega->format('d/m/Y H:i') : '');
                    $sheet->setCellValue('K' . $row, $carnet->entregador ? $carnet->entregador->nombre . ' ' . $carnet->entregador->apellido_paterno : '');
                    $sheet->setCellValue('L' . $row, '');
                    $sheet->setCellValue('M' . $row, $carnet->observaciones ?? '');
                    
                    $row++;
                }
                
                $lastRow = $row - 1;
                
                // ========== ESTILOS DE DATOS ==========
                if ($lastRow >= 6) {
                    $sheet->getStyle('A6:M' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ]);
                    
                    // Centrar columnas
                    $sheet->getStyle('A6:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('G6:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Altura de filas
                    for ($i = 6; $i <= $lastRow; $i++) {
                        $sheet->getRowDimension($i)->setRowHeight(25);
                    }
                    
                    // Colorear según estado
                    for ($i = 6; $i <= $lastRow; $i++) {
                        $estadoEntrega = $sheet->getCell('I' . $i)->getValue();
                        if ($estadoEntrega === 'Pendiente entrega') {
                            $sheet->getStyle('I' . $i)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFF2CC']
                                ],
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => 'FF6B00']
                                ]
                            ]);
                        } elseif ($estadoEntrega === 'Entregado') {
                            $sheet->getStyle('I' . $i)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'C6EFCE']
                                ],
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => '006100']
                                ]
                            ]);
                        }
                    }
                }
                
                // ========== PIE DE PÁGINA ==========
                $footerRow = $lastRow + 2;
                $sheet->setCellValue('A' . $footerRow, 'Fecha de generación: ' . date('d/m/Y H:i'));
                $sheet->mergeCells('A' . $footerRow . ':E' . $footerRow);
                $sheet->getStyle('A' . $footerRow)->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // N°
            'B' => 15,  // Código
            'C' => 12,  // DNI
            'D' => 35,  // Nombres
            'E' => 25,  // Carrera
            'F' => 12,  // Turno
            'G' => 10,  // Impreso
            'H' => 13,  // Fecha Imp
            'I' => 18,  // Estado Entrega
            'J' => 18,  // Fecha Entrega
            'K' => 25,  // Entregado Por
            'L' => 25,  // Firma
            'M' => 20   // Observaciones
        ];
    }

    public function title(): string
    {
        $titulo = str_replace(['/', '\\', '?', '*', '[', ']'], '-', $this->nombreAula);
        return substr($titulo, 0, 31);
    }
}
