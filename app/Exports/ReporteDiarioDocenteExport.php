<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReporteDiarioDocenteExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $reporte;
    protected $estadisticas;
    protected $fecha;

    public function __construct($reporte, $estadisticas, $fecha)
    {
        $this->reporte = $reporte;
        $this->estadisticas = $estadisticas;
        $this->fecha = $fecha;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->reporte)->map(function ($item) {
            return [
                'Docente' => $item['docente'],
                'Curso' => $item['curso'],
                'Aula' => $item['aula'],
                'Turno' => $item['turno'],
                'Hora Inicio' => $item['hora_inicio'],
                'Hora Fin' => $item['hora_fin'],
                'Entrada Real' => $item['hora_entrada'],
                'Salida Real' => $item['hora_salida'],
                'Horas Dictadas' => $item['horas_dictadas'],
                'Estado' => $item['estado'],
                'Tema Desarrollado' => $item['tema_desarrollado'],
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Docente',
            'Curso',
            'Aula',
            'Turno',
            'Hora Inicio',
            'Hora Fin',
            'Entrada Real',
            'Salida Real',
            'Horas Dictadas',
            'Estado',
            'Tema Desarrollado',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Reporte ' . $this->fecha;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
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
                
                // Agregar título principal
                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A1', 'REPORTE DIARIO DE ASISTENCIA DOCENTE');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1F4E78'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Agregar fecha
                $sheet->mergeCells('A2:K2');
                $sheet->setCellValue('A2', 'Fecha: ' . $this->fecha);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Agregar estadísticas
                $sheet->mergeCells('A3:K3');
                $estadisticasTexto = sprintf(
                    'Total Clases: %d | Asistencias: %d | Faltas: %d | Temas Pendientes: %d | Horas Dictadas: %.2f',
                    $this->estadisticas['total_clases'],
                    $this->estadisticas['total_asistencias'],
                    $this->estadisticas['total_faltas'],
                    $this->estadisticas['total_temas_pendientes'],
                    $this->estadisticas['total_horas_dictadas']
                );
                $sheet->setCellValue('A3', $estadisticasTexto);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'italic' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E7E6E6'],
                    ],
                ]);

                // Aplicar bordes a toda la tabla de datos
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A4:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Colorear filas de faltas en rojo claro
                for ($row = 5; $row <= $highestRow; $row++) {
                    $estado = $sheet->getCell('J' . $row)->getValue();
                    if ($estado === 'Falta') {
                        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFE6E6'],
                            ],
                        ]);
                    } elseif ($estado === 'Asistió') {
                        // Verificar si tiene tema pendiente
                        $tema = $sheet->getCell('K' . $row)->getValue();
                        if ($tema === 'Pendiente') {
                            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFF4E6'],
                                ],
                            ]);
                        }
                    }
                }

                // Ajustar altura de filas
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(20);
            },
        ];
    }
}
