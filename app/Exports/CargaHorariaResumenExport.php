<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Ciclo;
use App\Models\HorarioDocente;
use App\Models\PagoDocente;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class CargaHorariaResumenExport implements FromCollection, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $cicloId;
    protected $ciclo;
    protected $data;
    protected $semanas;
    protected $mergeTeacherRanges = [];
    protected $mergeCourseRanges = [];

    // Paleta de colores moderna, sobria y profesional
    private const COLORS = [
        'PRIMARY_BLUE'      => 'FF1A2A40',    // Slate oscuro / azul profundo
        'SECONDARY_BLUE'    => 'FF2B4C7E',    // Azul acero / secundario
        'ACCENT_GOLD'       => 'FFC5A880',    // Dorado cálido / arena premium
        'LIGHT_BLUE'        => 'FFF0F4F8',    // Celeste grisáceo muy claro (alternados)
        'WHITE'             => 'FFFFFFFF',    // Blanco puro
        'TEXT_DARK'         => 'FF2D3748',    // Gris carbón para textos
        'BEIGE_PAYMENT'     => 'FFFDFBF7',    // Crema muy suave para pago
        'BORDER_CHARCOAL'   => 'FF4A5568',    // Gris carbón definido para cuadrícula visible
        'HEADER_BG'         => 'FF2B4C7E',    // Fondo encabezado tabla
        'WARNING_RED'       => 'FFE53E3E',    // Rojo para errores
    ];

    public function __construct($cicloId)
    {
        $this->cicloId = $cicloId;
        $this->ciclo = Ciclo::findOrFail($cicloId);

        // Calcular semanas de duración del ciclo
        $inicio = Carbon::parse($this->ciclo->fecha_inicio);
        $fin = Carbon::parse($this->ciclo->fecha_fin);
        $diasCiclo = abs($inicio->diffInDays($fin));
        $this->semanas = (int) round($diasCiclo / 7) ?: 1;

        $this->data = $this->collectData();
    }

    /**
     * Genera una versión pastel extra-suave de un color hexadecimal (90% blanco) para legibilidad profesional
     */
    private function getPastelColor($hex, $factor = 0.90)
    {
        if (empty($hex)) {
            return 'FFFFFFFF';
        }
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return 'FFFFFFFF';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Mezclar con blanco según el factor (0.90 significa 90% blanco, 10% color original)
        $r = (int) (($r * (1 - $factor)) + (255 * $factor));
        $g = (int) (($g * (1 - $factor)) + (255 * $factor));
        $b = (int) (($b * (1 - $factor)) + (255 * $factor));

        return sprintf('FF%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Genera un color pastel único ultra-suave (94% blanco) para cada aula basado en su nombre
     */
    private function getAulaColor($aulaNombre)
    {
        if (empty($aulaNombre) || $aulaNombre === 'Sin aula') {
            return 'FFFFFFFF';
        }
        // Crear un hash del nombre del aula para obtener un color consistente
        $hash = md5($aulaNombre);
        $hex = substr($hash, 0, 6);
        return $this->getPastelColor($hex, 0.94); // 94% blanco para un fondo sumamente sutil y sobrio
    }

    private function collectData()
    {
        // Obtener docentes con rol profesor que tienen horarios en este ciclo
        $docentes = User::whereHas('roles', function($q) {
            $q->where('nombre', 'profesor');
        })->whereHas('horarios', function($q) {
            $q->where('ciclo_id', $this->cicloId);
        })->orderBy('apellido_paterno')->orderBy('nombre')->get();

        $results = new Collection();
        $contador = 1;

        $this->mergeTeacherRanges = [];
        $this->mergeCourseRanges = [];
        $currentRow = 8; // La tabla de datos empieza en la fila 8

        $controller = new \App\Http\Controllers\CargaHorariaController();

        foreach ($docentes as $docente) {
            // Obtener la carga horaria procesada mediante el controlador para aplicar descuentos y recesos correctamente
            $datos = $controller->obtenerDatosCargaHoraria($docente->id, $this->cicloId);

            // Filtrar horarios que no sean receso
            $horariosReales = collect($datos['horarios'])->filter(function($h) {
                return !$h->es_receso;
            });

            if ($horariosReales->isEmpty()) {
                continue;
            }

            // Agrupar horarios por curso
            $horariosPorCurso = $horariosReales->groupBy('curso_id');

            // Calcular horas y montos a nivel de docente
            $totalHorasSemana = $horariosReales->sum('horas_decimal');
            
            // CÁLCULO EN BASE AL HORARIO Y CLASES DE RECUPERACIÓN (Calculado en el controlador usando ocurrencias y sábados rotativos)
            $totalHorasCiclo = $datos['horas_totales_ciclo'];
            $tarifa = $datos['tarifa_por_hora'];
            $montoTotal = $datos['pago_total_ciclo'];

            // Determinar color de fondo para alternar por docente
            $bgColor = ($contador % 2 === 0) ? self::COLORS['LIGHT_BLUE'] : self::COLORS['WHITE'];

            $startTeacherRow = $currentRow;
            $isFirstTeacherRow = true;

            foreach ($horariosPorCurso as $cursoId => $slots) {
                $startCourseRow = $currentRow;
                $horasCursoSemana = $slots->sum('horas_decimal');
                
                $primerSlotCurso = $slots->first()->curso;
                $cursoNombre = $primerSlotCurso ? $primerSlotCurso->nombre : 'Sin curso';
                $cursoColorDb = $primerSlotCurso ? $primerSlotCurso->color : '#FFFFFF';
                // Obtener el color pastel ultra-suave del curso
                $cursoColorPastel = $this->getPastelColor($cursoColorDb, 0.90);

                // AGRUPAR POR AULA: Evita duplicados si el docente enseña en la misma aula para el mismo curso
                $slotsAgrupadosPorAula = $slots->groupBy('aula_id');
                $isFirstCourseRow = true;

                foreach ($slotsAgrupadosPorAula as $aulaId => $slotsDeAula) {
                    $primerSlot = $slotsDeAula->first();
                    $aulaNombre = $primerSlot->aula ? $primerSlot->aula->nombre : 'Sin aula';
                    
                    // Sumar las horas semanales asignadas a esta misma aula
                    $horasSlot = $slotsDeAula->sum('horas_decimal');
                    // Obtener el color pastel del aula
                    $aulaColorPastel = $this->getAulaColor($aulaNombre);

                    $results->push([
                        'contador' => $contador,
                        'nombre' => $docente->nombre_completo,
                        'condicion' => ($tarifa >= 40) ? 'EXTERNO' : 'CON VINCULO',
                        'curso' => $cursoNombre,
                        'aula' => $aulaNombre,
                        'horas_por_curso' => round($horasSlot, 2),
                        'horas_semana_curso' => round($horasCursoSemana, 2),
                        'total_horas_semana' => round($totalHorasSemana, 2),
                        'total_horas_ciclo' => round($totalHorasCiclo, 2),
                        'tarifa' => $tarifa,
                        'monto_total' => $montoTotal,
                        'bg_color' => $bgColor,
                        'curso_color' => $cursoColorPastel,
                        'aula_color' => $aulaColorPastel,
                        'is_first_teacher_row' => $isFirstTeacherRow,
                        'is_first_course_row' => $isFirstCourseRow,
                    ]);

                    $isFirstTeacherRow = false;
                    $isFirstCourseRow = false;
                    $currentRow++;
                }

                $endCourseRow = $currentRow - 1;
                if ($endCourseRow > $startCourseRow) {
                    $this->mergeCourseRanges[] = [$startCourseRow, $endCourseRow];
                }
            }

            $endTeacherRow = $currentRow - 1;
            if ($endTeacherRow > $startTeacherRow) {
                $this->mergeTeacherRanges[] = [$startTeacherRow, $endTeacherRow];
            }

            $contador++;
        }

        return $results;
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'Resumen Carga Horaria';
    }

    public function headings(): array
    {
        $titulo3 = "REPORTE DE CARGA HORARIA - " . strtoupper($this->ciclo->nombre) . " (" . $this->semanas . " SEMANAS)";

        return [
            ['UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS'],
            ['CENTRO PRE UNIVERSITARIO'],
            [$titulo3],
            [''], [''], [''], // Filas de espaciado y resumen
            [
                'N°',
                'DOCENTE',
                'CONDICION LABORAL',
                'CURSO',
                'AULA',
                'HORAS POR CURSO',
                'HORAS A LA SEMANA POR CURSO',
                'TOTAL DE HORAS A LA SEMANA POR DOCENTE',
                'TOTAL DE HORAS POR ' . $this->semanas . ' SEMANAS',
                'COSTO POR HORA',
                'MONTO TOTAL POR ' . $this->semanas . ' SEMANAS'
            ]
        ];
    }

    public function map($row): array
    {
        return [
            $row['is_first_teacher_row'] ? $row['contador'] : '',
            $row['is_first_teacher_row'] ? $row['nombre'] : '',
            $row['is_first_teacher_row'] ? $row['condicion'] : '',
            $row['is_first_course_row'] ? $row['curso'] : '',
            $row['aula'],
            $row['horas_por_curso'],
            $row['is_first_course_row'] ? $row['horas_semana_curso'] : '',
            $row['is_first_teacher_row'] ? $row['total_horas_semana'] : '',
            $row['is_first_teacher_row'] ? $row['total_horas_ciclo'] : '',
            $row['is_first_teacher_row'] ? $row['tarifa'] : '',
            $row['is_first_teacher_row'] ? $row['monto_total'] : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'K';
                $lastDataRow = $sheet->getHighestRow();
                $dataStart = 8;
                if ($lastDataRow < $dataStart) {
                    $lastDataRow = $dataStart;
                }

                // 1. --- CONFIGURACIÓN DE PÁGINA PROFESIONAL ---
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);
                $sheet->getStyle("A1:{$lastCol}{$lastDataRow}")->getFont()->setName('Segoe UI');

                // Asegurar que las cuadrículas (gridlines) estén visibles de forma nativa en Excel
                $sheet->setShowGridlines(true);

                // 2. --- ESTILIZAR CABECERAS DE TÍTULO (FILAS 1-3) ---
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->mergeCells("A3:{$lastCol}3");
                
                $sheet->getStyle("A1:A3")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getStyle("A1")->getFont()->setSize(16);
                $sheet->getStyle("A2")->getFont()->setSize(12)->setBold(false)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF5A6B82'));
                $sheet->getStyle("A3")->getFont()->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLORS['SECONDARY_BLUE']));

                // Agregar logos institucionales si existen
                $logoUnamad = public_path('assets/images/logo unamad constancia.png');
                if (file_exists($logoUnamad)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo UNAMAD');
                    $drawing->setPath($logoUnamad);
                    $drawing->setHeight(70);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(15);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);
                }
                $logoCepre = public_path('assets/images/logo cepre costancia.png');
                if (file_exists($logoCepre)) {
                    $drawing2 = new Drawing();
                    $drawing2->setName('Logo CEPRE');
                    $drawing2->setPath($logoCepre);
                    $drawing2->setHeight(70);
                    $drawing2->setCoordinates('J1');
                    $drawing2->setOffsetX(80);
                    $drawing2->setOffsetY(5);
                    $drawing2->setWorksheet($sheet);
                }

                // 3. --- CUADRO DE RESUMEN EJECUTIVO (FILA 4-5) ---
                $totalDocentes = $this->data->groupBy('contador')->count();
                $totalHorasSemanal = $this->data->where('is_first_teacher_row', true)->sum('total_horas_semana');
                $totalMontoPlanilla = $this->data->where('is_first_teacher_row', true)->sum('monto_total');

                $sheet->mergeCells("B4:E5");
                $sheet->setCellValue("B4", "RESUMEN EJECUTIVO:\nN° DOCENTES: {$totalDocentes} | TOTAL H. SEMANALES: {$totalHorasSemanal} | TOTAL PRESUPUESTO: S/. " . number_format($totalMontoPlanilla, 2));
                $sheet->getStyle("B4")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("B4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLORS['LIGHT_BLUE']]],
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLORS['ACCENT_GOLD']]]]
                ]);

                // 4. --- METADATOS DE AUDITORÍA (FILA 6) ---
                $user = auth()->user() ? auth()->user()->nombre_completo : 'Auditoría de Sistema';
                $now = now()->format('d/m/Y H:i');
                $sheet->mergeCells("H6:K6");
                $sheet->setCellValue("H6", "GENERADO POR: " . strtoupper($user) . " [" . $now . "]");
                $sheet->getStyle("H6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H6")->getFont()->setBold(true)->setSize(8)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF7F8C8D'));

                // 5. --- ENCABEZADOS DE TABLA (FILA 7) ---
                $rowHeaders = 7;
                $sheet->getStyle("A{$rowHeaders}:{$lastCol}{$rowHeaders}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => self::COLORS['WHITE']], 'size' => 9.5],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLORS['HEADER_BG']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']],
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::COLORS['ACCENT_GOLD']]]
                    ]
                ]);
                $sheet->getRowDimension($rowHeaders)->setRowHeight(45);

                // 6. --- APLICACIÓN DE BORDES Y FONDOS DE FILAS ---
                // Aplicar un borde completo a toda la tabla de datos en gris carbón bien definido
                $sheet->getStyle("A{$dataStart}:{$lastCol}{$lastDataRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => self::COLORS['BORDER_CHARCOAL']]
                        ],
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                        ]
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
                ]);

                for ($row = $dataStart; $row <= $lastDataRow; $row++) {
                    $rowData = $this->data->get($row - $dataStart);
                    $bgColor = $rowData['bg_color'] ?? self::COLORS['WHITE'];
                    $cursoColor = $rowData['curso_color'] ?? self::COLORS['WHITE'];
                    $aulaColor = $rowData['aula_color'] ?? self::COLORS['WHITE'];

                    // Fondo de área de Docente (Columnas A a C)
                    $sheet->getStyle("A{$row}:C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($bgColor);

                    // Fondo de área de Curso (Columna D) - Con color del curso sutil
                    $sheet->getStyle("D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($cursoColor);

                    // Fondo de área de Aula (Columna E) - Con color de aula sutil
                    $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($aulaColor);

                    // Fondo de área de Horas del Curso (Columnas F a G)
                    $sheet->getStyle("F{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($bgColor);

                    // Fondo de totales semanales y de ciclo (Columnas H a I)
                    $sheet->getStyle("H{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($bgColor);

                    // Fondo de área económica (Columnas J a K)
                    $sheet->getStyle("J{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB(self::COLORS['BEIGE_PAYMENT']);

                    // Resaltar tarifa vacía o en cero
                    $tarifaRaw = $sheet->getCell('J' . $row)->getValue();
                    if ($tarifaRaw === 0 || $tarifaRaw === "0" || $tarifaRaw === 0.0 || $tarifaRaw === '') {
                        $sheet->getStyle("J{$row}:K{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLORS['WARNING_RED']))->setBold(true);
                    }
                }

                // Ajustar tamaños de fuente de datos
                $sheet->getStyle("A{$dataStart}:{$lastCol}{$lastDataRow}")->getFont()->setSize(9.5);

                // Alineaciones de texto
                $sheet->getStyle("A{$dataStart}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$dataStart}:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C{$dataStart}:C{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$dataStart}:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("E{$dataStart}:I{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$dataStart}:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Formato de moneda
                $currFormat = '_-"S/."* #,##0.00_-;-"S/."* #,##0.00_-;_-"S/."* "-"??_-;_-@_-';
                $sheet->getStyle("J{$dataStart}:K{$lastDataRow}")->getNumberFormat()->setFormatCode($currFormat);

                // 7. --- FUSIONAR CELDAS HORIZONTALMENTE / VERTICALMENTE ---
                // Combinar para docentes
                foreach ($this->mergeTeacherRanges as $range) {
                    $start = $range[0];
                    $end = $range[1];
                    if ($end > $start) {
                        $sheet->mergeCells("A{$start}:A{$end}");
                        $sheet->mergeCells("B{$start}:B{$end}");
                        $sheet->mergeCells("C{$start}:C{$end}");
                        $sheet->mergeCells("H{$start}:H{$end}");
                        $sheet->mergeCells("I{$start}:I{$end}");
                        $sheet->mergeCells("J{$start}:J{$end}");
                        $sheet->mergeCells("K{$start}:K{$end}");
                    }
                }

                // Combinar para cursos
                foreach ($this->mergeCourseRanges as $range) {
                    $start = $range[0];
                    $end = $range[1];
                    if ($end > $start) {
                        $sheet->mergeCells("D{$start}:D{$end}");
                        $sheet->mergeCells("G{$start}:G{$end}");
                    }
                }

                // 8. --- RESUMEN TOTAL CONSOLIDADO ---
                $totalRow = $lastDataRow + 2;
                $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", "RESUMEN CONSOLIDADO DE PLANILLA POR CARGA HORARIA");
                $sheet->setCellValue("K{$totalRow}", $totalMontoPlanilla);

                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => self::COLORS['WHITE']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLORS['PRIMARY_BLUE']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => self::COLORS['ACCENT_GOLD']]],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => self::COLORS['ACCENT_GOLD']]],
                        'left' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]],
                        'right' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]]
                    ]
                ]);
                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode($currFormat);
                $sheet->getRowDimension($totalRow)->setRowHeight(35);

                // 9. --- SECCIÓN DE FIRMAS ---
                $signRow = $totalRow + 4;
                if ($signRow + 2 > 1000) {
                    $signRow = $totalRow + 2;
                }
                $sheet->mergeCells("B{$signRow}:D{$signRow}");
                $sheet->mergeCells("H{$signRow}:J{$signRow}");
                $sheet->setCellValue("B{$signRow}", "__________________________\nRESPONSABLE DE CARGA\nUnidad de Personal");
                $sheet->setCellValue("H{$signRow}", "__________________________\nVº Bº DIRECCIÓN CEPRE\nUniversidad Nacional Amazónica");
                $sheet->getStyle("B{$signRow}:J{$signRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                $sheet->getStyle("B{$signRow}:J{$signRow}")->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLORS['TEXT_DARK']));

                // 10. --- ANCHOS OPTIMIZADOS ---
                $widths = [
                    'A' => 6,
                    'B' => 45,
                    'C' => 25,
                    'D' => 35,
                    'E' => 15,
                    'F' => 15,
                    'G' => 18,
                    'H' => 18,
                    'I' => 18,
                    'J' => 15,
                    'K' => 20
                ];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }
            }
        ];
    }
}
