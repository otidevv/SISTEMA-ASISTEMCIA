<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;

class AsistenciaDiaExport implements FromView, WithTitle, WithColumnWidths, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.asistencia-dia', $this->data);
    }

    public function title(): string
    {
        return 'Asistencia ' . $this->data['fecha_reporte']->format('d-m-Y');
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // #
            'B' => 15,  // Código
            'C' => 35,  // Estudiante
            'D' => 15,  // Documento
            'E' => 25,  // Carrera
            'F' => 10,  // Turno
            'G' => 10,  // Aula
        ];

        if ($this->data['tipo_reporte'] !== 'faltas_dia') {
            $widths['H'] = 12; // Entrada
            $widths['I'] = 12; // Salida
            $widths['J'] = 12; // Estado
        }

        if ($this->data['es_examen'] && $this->data['tipo_reporte'] === 'resumen_examen') {
            $widths['K'] = 12; // % Asist.
            $widths['L'] = 15; // Puede Rendir
        }

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data['estudiantes']) + 10; // Ajustar según el contenido

        return [
            // Estilo del encabezado principal
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 14]],

            // Encabezados de tabla
            'A7:L7' => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2C3E50']
                ]
            ],

            // Bordes para toda la tabla
            'A7:L' . $lastRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ]
                ]
            ]
        ];
    }
}
