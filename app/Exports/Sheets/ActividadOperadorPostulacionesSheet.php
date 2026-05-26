<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ActividadOperadorPostulacionesSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $postulaciones;
    protected $operadorNombre;
    protected $rangoFechas;

    public function __construct($postulaciones, $operadorNombre, $rangoFechas)
    {
        $this->postulaciones = $postulaciones;
        $this->operadorNombre = $operadorNombre;
        $this->rangoFechas = $rangoFechas;
    }

    public function collection()
    {
        return $this->postulaciones;
    }

    public function title(): string
    {
        return 'Postulaciones Ordinarias';
    }

    public function headings(): array
    {
        return [
            ['Reporte de Actividad - Postulaciones Ordinarias'],
            ['Operador:', $this->operadorNombre],
            ['Periodo:', $this->rangoFechas],
            [],
            [
                'Código Postulación',
                'Documento Estudiante',
                'Nombre Completo',
                'Programa / Carrera',
                'Turno',
                'Nro. Recibo',
                'Monto Matrícula',
                'Monto Enseñanza',
                'Monto Total',
                'Fecha Aprobación',
                'Estado'
            ]
        ];
    }

    public function map($postulacion): array
    {
        return [
            $postulacion->codigo_postulante ?? 'N/A',
            $postulacion->estudiante->numero_documento ?? 'N/A',
            strtoupper(($postulacion->estudiante->nombre ?? '') . ' ' . ($postulacion->estudiante->apellido_paterno ?? '') . ' ' . ($postulacion->estudiante->apellido_materno ?? '')),
            $postulacion->carrera->nombre ?? 'N/A',
            $postulacion->turno->nombre ?? 'N/A',
            $postulacion->numero_recibo ?? 'N/A',
            $postulacion->monto_matricula ?? 0.00,
            $postulacion->monto_ensenanza ?? 0.00,
            $postulacion->monto_total_pagado ?? 0.00,
            $postulacion->fecha_revision ? $postulacion->fecha_revision->format('d/m/Y H:i') : 'N/A',
            strtoupper($postulacion->estado ?? 'PENDIENTE')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Título principal
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'D81B60'] // Magenta / Cepre color
            ]
        ]);

        // Datos del reporte
        $sheet->getStyle('A2:B3')->applyFromArray([
            'font' => ['bold' => true]
        ]);

        // Estilo para encabezados de tabla
        $sheet->getStyle('A5:K5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);

        // Altura de filas
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Bordes de la tabla
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 5) {
            $sheet->getStyle('A5:K' . $highestRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'BDC3C7']
                    ]
                ]
            ]);
            
            // Alinear al centro columnas de códigos, DNI, fechas y estados
            $sheet->getStyle('A6:B' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E6:F' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J6:K' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Formato de moneda para montos
            $sheet->getStyle('G6:I' . $highestRow)->getNumberFormat()->setFormatCode('S/. #,##0.00');
        }

        return [];
    }
}
