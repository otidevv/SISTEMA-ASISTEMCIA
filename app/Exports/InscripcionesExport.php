<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InscripcionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $inscripciones;

    public function __construct($inscripciones)
    {
        $this->inscripciones = $inscripciones;
    }

    public function collection()
    {
        return $this->inscripciones;
    }

    public function headings(): array
    {
        return [
            'Código Inscripción',
            'Documento Estudiante',
            'Nombre Completo',
            'Email',
            'Carrera',
            'Ciclo',
            'Turno',
            'Aula',
            'Fecha Inscripción',
            'Estado',
            'Fecha Retiro',
            'Motivo Retiro',
            'Observaciones'
        ];
    }

    public function map($inscripcion): array
    {
        return [
            $inscripcion->codigo_inscripcion,
            $inscripcion->estudiante->numero_documento ?? 'N/A',
            $inscripcion->estudiante->nombre . ' ' .
                $inscripcion->estudiante->apellido_paterno . ' ' .
                $inscripcion->estudiante->apellido_materno,
            $inscripcion->estudiante->email,
            $inscripcion->carrera->nombre,
            $inscripcion->ciclo->nombre,
            $inscripcion->turno->nombre,
            $inscripcion->aula->codigo . ' - ' . $inscripcion->aula->nombre,
            $inscripcion->fecha_inscripcion->format('d/m/Y'),
            ucfirst($inscripcion->estado_inscripcion),
            $inscripcion->fecha_retiro ? $inscripcion->fecha_retiro->format('d/m/Y') : '',
            $inscripcion->motivo_retiro ?? '',
            $inscripcion->observaciones ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para encabezados
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
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

        // Altura de la fila de encabezados
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Aplicar bordes a toda la tabla
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        return [];
    }
}
