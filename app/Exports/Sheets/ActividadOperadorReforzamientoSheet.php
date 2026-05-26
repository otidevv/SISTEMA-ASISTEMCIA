<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ActividadOperadorReforzamientoSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $reforzamientos;
    protected $operadorNombre;
    protected $rangoFechas;

    public function __construct($reforzamientos, $operadorNombre, $rangoFechas)
    {
        $this->reforzamientos = $reforzamientos;
        $this->operadorNombre = $operadorNombre;
        $this->rangoFechas = $rangoFechas;
    }

    public function collection()
    {
        return $this->reforzamientos;
    }

    public function title(): string
    {
        return 'Reforzamiento Escolar';
    }

    public function headings(): array
    {
        return [
            ['Reporte de Actividad - Reforzamiento Escolar'],
            ['Operador:', $this->operadorNombre],
            ['Periodo:', $this->rangoFechas],
            [],
            [
                'Nro. Constancia',
                'Documento Estudiante',
                'Nombre Completo',
                'Grado Académico',
                'Colegio Procedencia',
                'Turno',
                'Aula Asignada',
                'Nro. Operación',
                'Monto Pagado',
                'Fecha Validación',
                'Estado'
            ]
        ];
    }

    public function map($reforzamiento): array
    {
        // Obtener el primer pago aprobado o el primero existente
        $pago = $reforzamiento->pagos->where('estado_pago', 'aprobado')->first() ?? $reforzamiento->pagos->first();
        
        return [
            $reforzamiento->nro_constancia ?? 'N/A',
            $reforzamiento->estudiante->numero_documento ?? 'N/A',
            strtoupper(($reforzamiento->estudiante->nombre ?? '') . ' ' . ($reforzamiento->estudiante->apellido_paterno ?? '') . ' ' . ($reforzamiento->estudiante->apellido_materno ?? '')),
            strtoupper($reforzamiento->grado ?? 'N/A'),
            strtoupper($reforzamiento->colegio_procedencia ?? 'N/A'),
            strtoupper($reforzamiento->turno ?? 'N/A'),
            $reforzamiento->aula->nombre ?? 'SIN AULA',
            $pago->numero_operacion ?? 'N/A',
            $pago->monto ?? 0.00,
            $reforzamiento->fecha_validacion ? $reforzamiento->fecha_validacion->format('d/m/Y H:i') : 'N/A',
            strtoupper($reforzamiento->estado_inscripcion ?? 'PENDIENTE')
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
                'color' => ['rgb' => '1B5E20'] // Verde oscuro institucional
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
            $sheet->getStyle('F6:H' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J6:K' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Formato de moneda para montos
            $sheet->getStyle('I6:I' . $highestRow)->getNumberFormat()->setFormatCode('S/. #,##0.00');
        }

        return [];
    }
}
