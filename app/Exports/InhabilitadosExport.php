<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InhabilitadosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'N°',
            'Estudiante',
            'DNI',
            'Carrera',
            'Aula',
            'Turno',
            'Faltas',
            'Asistencias',
            'Total Días Habiles',
            '% Inasistencia',
            'Límite de Faltas',
            'Estado'
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;
        
        return [
            $index,
            $item['nombres'],
            $item['dni'],
            $item['carrera'],
            $item['aula'],
            $item['turno'],
            $item['faltas'],
            $item['asistencias'],
            $item['total_dias'],
            (100 - $item['porcentaje']) . '%',
            $item['limite'],
            'Inhabilitado'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para la fila de encabezados
        $sheet->getStyle('1:1')->getFont()->setBold(true);
        $sheet->getStyle('1:1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('4F32C2');
        $sheet->getStyle('1:1')->getFont()->getColor()->setARGB('FFFFFF');

        // Auto-ajustar el tamaño de las columnas
        foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Añadir bordes a todas las celdas
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ];
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestRow())->applyFromArray($styleArray);

        return [];
    }
}
