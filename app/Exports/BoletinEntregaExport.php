<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
        // Ordenar estudiantes alfabéticamente por nombre
        $this->data = collect($data)->sortBy('student', SORT_NATURAL | SORT_FLAG_CASE)->values();
        $this->cursos = $cursos;
        
        // Traducir mes al español
        $mesesES = [
            'January' => 'ENERO', 'February' => 'FEBRERO', 'March' => 'MARZO',
            'April' => 'ABRIL', 'May' => 'MAYO', 'June' => 'JUNIO',
            'July' => 'JULIO', 'August' => 'AGOSTO', 'September' => 'SEPTIEMBRE',
            'October' => 'OCTUBRE', 'November' => 'NOVIEMBRE', 'December' => 'DICIEMBRE'
        ];
        
        $mesActual = $mes ?? date('F');
        $this->mes = isset($mesesES[$mesActual]) ? $mesesES[$mesActual] : strtoupper($mesActual);
        $this->anio = $anio ?? date('Y');
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $headings = ['N°', 'Estudiante'];
        foreach ($this->cursos as $curso) {
            $headings[] = $curso->nombre;
        }
        $headings[] = 'Total Entregas';
        $headings[] = '% Entrega';
        $headings[] = 'Faltas';
        return $headings;
    }

    public function map($row): array
    {
        static $rowNumber = 0;
        $rowNumber++;
        
        // Contar el número real de cursos que el estudiante debería tener.
        // Asumiendo que $row['courses'] solo contiene los cursos aplicables al estudiante.
        $totalCursos = count($row['courses']); 
        $entregados = 0;
        
        $mappedRow = [
            $rowNumber,
            $row['student']
        ];
        
        foreach ($row['courses'] as $course) {
            if ($course['entregado']) {
                $entregados++;
                $fecha = '';
                if (!empty($course['fecha_entrega'])) {
                    try {
                        // Usar un formato sin la marca de hora si es posible
                        $fecha = \Carbon\Carbon::parse($course['fecha_entrega'])->format('d/m/Y');
                    } catch (\Exception $e) {
                        $fecha = '';
                    }
                }
                // Si está entregado, retorna el checkmark con la fecha
                $mappedRow[] = '✓ ' . $fecha; 
            } else {
                // Si falta, retorna FALTA
                $mappedRow[] = 'FALTA';
            }
        }
        
        // Totales al final de la fila del estudiante
        $mappedRow[] = $entregados;
        $mappedRow[] = $totalCursos > 0 ? round(($entregados / $totalCursos) * 100) . '%' : '0%';
        $mappedRow[] = $totalCursos - $entregados;
        
        return $mappedRow;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 35,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Las estilizaciones principales se manejan en registerEvents
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
                
                // Insertar 3 filas al inicio para encabezado
                $sheet->insertNewRowBefore(1, 3);
                
                // Ahora las filas de datos empiezan en la fila 4
                $headerRow = 4;
                $firstDataRow = 5;
                // Ajustar la última fila de datos al nuevo conteo
                $lastDataRow = $highestRow + 3; 
                
                // ENCABEZADO INSTITUCIONAL (sin cambios)
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->setCellValue('A1', 'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1A5490']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);
                
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->setCellValue('A2', 'CENTRO PRE UNIVERSITARIO');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '2E7D32']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
                
                $sheet->mergeCells('A3:' . $highestColumn . '3');
                $titulo = 'CONTROL DE ENTREGA DE BOLETINES - ' . $this->mes . ' ' . $this->anio;
                $sheet->setCellValue('A3', $titulo);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A5490']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getRowDimension(3)->setRowHeight(28);
                
                $sheet->getStyle('A1:' . $highestColumn . '3')->applyFromArray([
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['rgb' => '1A5490']]]
                ]);
                
                // ENCABEZADOS DE COLUMNAS (sin cambios)
                $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerRow)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A5490']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'textRotation' => 90,
                        'wrapText' => true
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]]
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(85);
                
                // COLORES PARA CADA CURSO (sin cambios)
                $coloresCursos = [
                    '5B9BD5', '70AD47', 'FFC000', 'ED7D31', 
                    'A5A5A5', '4472C4', '548235', 'C55A11',
                    'A0AEC0', '68D391', 'F6AD55', 'F6E05E' 
                ];
                
                $numCursos = count($this->cursos);
                $cursoStartColIndex = Coordinate::columnIndexFromString('C');
                $cursoStartCol = 'C';
                
                for ($i = 0; $i < $numCursos; $i++) {
                    $colIndex = $cursoStartColIndex + $i;
                    $col = Coordinate::stringFromColumnIndex($colIndex);
                    $colorIndex = $i % count($coloresCursos);
                    
                    $sheet->getStyle($col . $headerRow)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $coloresCursos[$colorIndex]]]
                    ]);
                    
                    $sheet->getColumnDimension($col)->setWidth(13);
                }
                $endCursoCol = Coordinate::stringFromColumnIndex($cursoStartColIndex + $numCursos - 1);


                // ESTILOS COLUMNAS N° Y ESTUDIANTE (sin cambios)
                $sheet->getStyle('A' . $firstDataRow . ':B' . $lastDataRow)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DEE2E6']]]
                ]);
                
                $sheet->getStyle('A' . $firstDataRow . ':A' . $lastDataRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                $sheet->getStyle('B' . $firstDataRow . ':B' . $lastDataRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);
                
                // COLORES BASE POR CURSO (tonos claros para toda la columna) (sin cambios)
                $coloresLightCursos = [
                    'D6E9F5', 'E2F0D9', 'FFF2CC', 'FCE4D6', 
                    'F2F2F2', 'E9F0F7', 'E8F5E9', 'FCEEE6',
                    'E2E8F0', 'EBFBEF', 'FEEBC8', 'FAF5D8'
                ];
                
                // *** CORRECCIÓN DE COLORES DE CELDA: Color del Curso como fondo en entregas ***
                
                for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
                    $bgColor = ($row % 2 == 0) ? 'FFFFFF' : 'F8FAFB';
                    // Estilo de fondo para N° y Estudiante
                    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]]
                    ]);
                    
                    $colIndex = 0;
                    for ($colNum = $cursoStartColIndex; $colNum <= Coordinate::columnIndexFromString($endCursoCol); $colNum++) {
                        $col = Coordinate::stringFromColumnIndex($colNum);
                        $cellValue = $sheet->getCell($col . $row)->getValue();
                        $colorIndex = $colIndex % count($coloresCursos);
                        $colorCursoOscuro = $coloresCursos[$colorIndex];
                        $colorBaseClaro = $coloresLightCursos[$colorIndex];

                        // Aplica el color base claro del curso a toda la celda primero (como fondo general)
                        $sheet->getStyle($col . $row)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorBaseClaro]],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DEE2E6']]]
                        ]);
                        
                        if (strpos($cellValue, '✓') !== false) {
                            // Entregado - Fondo con Color Oscuro del Curso, Texto Blanco
                            $sheet->getStyle($col . $row)->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorCursoOscuro]], // *** CAMBIO CLAVE: Color Oscuro del Curso como fondo ***
                                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 9], // Texto Blanco
                                'borders' => [
                                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']],
                                    'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'FFFFFF']], 
                                    'right' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'FFFFFF']] 
                                ]
                            ]);
                        } elseif ($cellValue === 'FALTA') {
                            // Falta - Fondo Rojo Oscuro Fijo, Texto Blanco (Mantiene formalidad)
                            $sheet->getStyle($col . $row)->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B71C1C']], // Fondo Rojo Oscuro para Faltante
                                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true, 'size' => 9], // Texto Blanco
                                'borders' => [
                                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D32F2F']],
                                    'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'D32F2F']], 
                                    'right' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'D32F2F']] 
                                ]
                            ]);
                        } else {
                            // Celda vacía con color base del curso
                            $sheet->getStyle($col . $row)->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorBaseClaro]],
                                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DEE2E6']]]
                            ]);
                        }
                        
                        $colIndex++;
                    }
                    
                    $sheet->getRowDimension($row)->setRowHeight(24);
                }
                
                // COLUMNAS DE RESUMEN (Totales, Porcentaje, Faltas)
                $resumeStartCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($endCursoCol) + 1);
                $resumeEndCol = $highestColumn;

                $sheet->getStyle($resumeStartCol . $headerRow . ':' . $resumeEndCol . $headerRow)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'alignment' => ['textRotation' => 90]
                ]);
                
                for ($col = $resumeStartCol; $col <= $resumeEndCol; $col++) {
                    $sheet->getColumnDimension($col)->setWidth(11);
                }
                
                $sheet->getStyle($resumeStartCol . $firstDataRow . ':' . $resumeEndCol . $lastDataRow)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '2E7D32']]
                ]);
                
                // ALINEACIÓN CENTRAL PARA CURSOS
                $sheet->getStyle($cursoStartCol . $firstDataRow . ':' . $highestColumn . $lastDataRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                
                // BORDES GENERALES
                $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $lastDataRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DEE2E6']],
                        'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1A5490']]
                    ]
                ]);
                
                // FILA DE TOTALES
                $totalRow = $lastDataRow + 2;
                $sheet->getRowDimension($lastDataRow + 1)->setRowHeight(10);
                
                $sheet->mergeCells('A' . $totalRow . ':B' . $totalRow);
                $sheet->setCellValue('A' . $totalRow, 'TOTAL ENTREGAS POR CURSO');
                
                // ** SOLUCIÓN FINAL PARA EL TOTAL ENTREGAS POR CURSO **
                // Insertamos la fórmula COUNTIF y NO intentamos calcular el valor en PHP, confiando en que Excel lo hará al abrir.
                $colIndex = 0;
                for ($colNum = $cursoStartColIndex; $colNum <= Coordinate::columnIndexFromString($endCursoCol); $colNum++) {
                    $col = Coordinate::stringFromColumnIndex($colNum);
                    
                    // Contar si la celda empieza con '✓' (ignora la fecha)
                    $formula = '=COUNTIF(' . $col . $firstDataRow . ':' . $col . $lastDataRow . ',"✓*")';
                    $sheet->setCellValue($col . $totalRow, $formula); 
                    
                    $colorIndex = $colIndex % count($coloresCursos);
                    $sheet->getStyle($col . $totalRow)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $coloresCursos[$colorIndex]]],
                        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']]
                    ]);
                    
                    $colIndex++;
                }
                
                // Totales generales de la tabla (Suman los totales por estudiante)
                $totalEntregasCol = $resumeStartCol;
                $sheet->setCellValue($totalEntregasCol . $totalRow, '=SUM(' . $totalEntregasCol . $firstDataRow . ':' . $totalEntregasCol . $lastDataRow . ')');
                
                $percentCol = chr(ord($resumeStartCol) + 1);
                $sheet->setCellValue($percentCol . $totalRow, ''); // Se deja vacío
                
                $faltasCol = chr(ord($resumeStartCol) + 2);
                $sheet->setCellValue($faltasCol . $totalRow, '=SUM(' . $faltasCol . $firstDataRow . ':' . $faltasCol . $lastDataRow . ')');
                
                $sheet->getStyle('A' . $totalRow . ':B' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A5490']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0D3C6B']]]
                ]);
                
                $sheet->getStyle($resumeStartCol . $totalRow . ':' . $highestColumn . $totalRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E7E34']]]
                ]);
                
                $sheet->getRowDimension($totalRow)->setRowHeight(30);
            }
        ];
    }
}