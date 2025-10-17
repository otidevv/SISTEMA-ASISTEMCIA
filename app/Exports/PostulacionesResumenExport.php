<?php

namespace App\Exports;

use App\Models\Postulacion;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PostulacionesResumenExport implements FromArray, WithStyles, WithColumnWidths
{
    protected $ciclo_id;
    protected $carrera_id;
    protected $turno_id;
    protected $aula_id;
    protected $carrerasPorGrupo = [];
    protected $table1RowCount = 0;

    // Definir grupos de carreras con sus colores
    protected $gruposCarreras = [
        'Grupo A' => [
            'carreras' => [
                'INGENIERÍA AGROINDUSTRIAL',
                'INGENIERÍA DE SISTEMAS E INFORMÁTICA',
                'INGENIERÍA FORESTAL Y MEDIO AMBIENTE'
            ],
            'color' => 'E8F4F8' // Azul claro
        ],
        'Grupo B' => [
            'carreras' => [
                'ENFERMERÍA',
                'MEDICINA VETERINARIA - ZOOTECNIA'
            ],
            'color' => 'F8E8E8' // Rosa claro
        ],
        'Grupo C' => [
            'carreras' => [
                'ADMINISTRACIÓN Y NEGOCIOS INTERNACIONALES',
                'CONTABILIDAD Y FINANZAS',
                'DERECHO Y CIENCIAS POLÍTICAS',
                'ECOTURISMO',
                'EDUCACIÓN: ESPECIALIDAD INICIAL Y ESPECIAL',
                'EDUCACIÓN: ESPECIALIDAD MATEMÁTICA Y COMPUTACIÓN',
                'EDUCACIÓN: ESPECIALIDAD PRIMARIA E INFORMÁTICA'
            ],
            'color' => 'F8F4E8' // Amarillo claro
        ]
    ];

    public function __construct($ciclo_id, $carrera_id, $turno_id, $aula_id)
    {
        $this->ciclo_id = $ciclo_id;
        $this->carrera_id = $carrera_id;
        $this->turno_id = $turno_id;
        $this->aula_id = $aula_id;
    }

    private function encontrarGrupo($carrera)
    {
        $carreraNormalizada = strtoupper(trim($carrera));
        foreach ($this->gruposCarreras as $nombreGrupo => $grupoData) {
            foreach ($grupoData['carreras'] as $carreraGrupo) {
                if (strtoupper(trim($carreraGrupo)) === $carreraNormalizada) {
                    return $nombreGrupo;
                }
            }
        }
        return 'Sin Grupo';
    }

    public function array(): array
    {
        // --- Base Query ---
        $baseQuery = Postulacion::query();
        if ($this->ciclo_id) $baseQuery->where('postulaciones.ciclo_id', $this->ciclo_id);
        if ($this->carrera_id) $baseQuery->where('postulaciones.carrera_id', $this->carrera_id);
        if ($this->turno_id) $baseQuery->where('postulaciones.turno_id', $this->turno_id);
        if ($this->aula_id) {
            $baseQuery->whereHas('inscripcion', function ($q) {
                $q->where('aula_id', $this->aula_id);
            });
        }

        // --- Generar datos para Tabla 1 (izquierda) ---
        $report_tabla1 = $this->generateMainTableData($baseQuery);
        $this->table1RowCount = count($report_tabla1);

        // --- Generar datos para Tabla 2 (derecha) ---
        $report_tabla2 = $this->generateSummaryTableData($baseQuery);

        // --- Combinar ambas tablas lado a lado ---
        $finalReport = [];
        $totalRows = max(count($report_tabla1), count($report_tabla2));

        for ($i = 0; $i < $totalRows; $i++) {
            $row1 = $report_tabla1[$i] ?? ['', '', '', '', ''];
            $row2 = $report_tabla2[$i] ?? ['', ''];

            $finalReport[] = array_merge($row1, [''], $row2);
        }

        return $finalReport;
    }

    private function generateMainTableData($baseQuery): array
    {
        $datos = (clone $baseQuery)
            ->join('carreras', 'postulaciones.carrera_id', '=', 'carreras.id')
            ->join('inscripciones', 'postulaciones.codigo_postulante', '=', 'inscripciones.codigo_inscripcion')
            ->join('aulas', 'inscripciones.aula_id', '=', 'aulas.id')
            ->select('carreras.nombre as carrera', 'aulas.nombre as aula', DB::raw('count(postulaciones.id) as total'))
            ->groupBy('carreras.nombre', 'aulas.nombre')
            ->orderBy('carreras.nombre')->orderBy('aulas.nombre')
            ->get();

        $datosOrganizados = [];
        foreach ($datos as $item) {
            $datosOrganizados[strtoupper(trim($item->carrera))][$item->aula] = $item->total;
        }

        $carrerasPorGrupo = [];
        foreach ($datosOrganizados as $carrera => $aulas) {
            $grupo = $this->encontrarGrupo($carrera);
            if (!isset($carrerasPorGrupo[$grupo])) $carrerasPorGrupo[$grupo] = [];
            $carrerasPorGrupo[$grupo][$carrera] = count($aulas);
        }
        $this->carrerasPorGrupo = $carrerasPorGrupo;

        $report = [];
        $report[] = ['RESUMEN DE POSTULANTES POR CARRERA'];
        $report[] = ['#', 'Grupo', 'Carrera Profesional:', 'AULA', 'Total'];
        
        $numeroGrupo = 1;
        $totalesGeneral = 0;

        $ordenGrupos = ['Grupo A', 'Grupo B', 'Grupo C'];
        $gruposOrdenados = [];
        foreach ($ordenGrupos as $grupo) {
            if (isset($carrerasPorGrupo[$grupo])) $gruposOrdenados[$grupo] = $carrerasPorGrupo[$grupo];
        }
        foreach ($carrerasPorGrupo as $grupo => $carreras) {
            if (!isset($gruposOrdenados[$grupo])) $gruposOrdenados[$grupo] = $carreras;
        }

        foreach ($gruposOrdenados as $nombreGrupo => $carreras) {
            $primeraFilaDelGrupo = true;
            foreach ($carreras as $carrera => $aulaCount) {
                if (!isset($datosOrganizados[$carrera])) continue;
                $aulas = $datosOrganizados[$carrera];
                $primeraFilaDeCarrera = true;
                foreach ($aulas as $aulaNombre => $total) {
                    $report[] = [
                        $primeraFilaDelGrupo ? $numeroGrupo : '',
                        $primeraFilaDelGrupo ? $nombreGrupo : '',
                        $primeraFilaDeCarrera ? ucwords(strtolower($carrera)) : '',
                        $aulaNombre,
                        $total
                    ];
                    $totalesGeneral += $total;
                    $primeraFilaDelGrupo = false;
                    $primeraFilaDeCarrera = false;
                }
            }
            $numeroGrupo++;
        }
        $report[] = ['', '', 'Total', '', $totalesGeneral];
        return $report;
    }

    private function generateSummaryTableData($baseQuery): array
    {
        $report = [];
        $report[] = ['RESUMEN GENERAL POR AULA'];
        $report[] = ['Aula', 'N° de Postulantes'];

        $resumenPorAula = (clone $baseQuery)
            ->join('inscripciones', 'postulaciones.codigo_postulante', '=', 'inscripciones.codigo_inscripcion')
            ->join('aulas', 'inscripciones.aula_id', '=', 'aulas.id')
            ->select('aulas.nombre as aula', DB::raw('count(postulaciones.id) as total'))
            ->groupBy('aulas.nombre')
            ->orderBy('aulas.nombre')
            ->get();

        $totalResumenAula = 0;
        foreach ($resumenPorAula as $item) {
            $report[] = [$item->aula, $item->total];
            $totalResumenAula += $item->total;
        }
        $report[] = ['Total', $totalResumenAula];
        return $report;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // #
            'B' => 12,  // Grupo
            'C' => 50,  // Carrera Profesional
            'D' => 12,  // AULA
            'E' => 10,  // Total
            'F' => 2,   // Spacer
            'G' => 20,  // Aula (Resumen)
            'H' => 20,  // N° Postulantes
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // --- Estilo Tabla 1 (Izquierda) ---
        $this->styleMainTable($sheet);

        // --- Estilo Tabla 2 (Derecha) ---
        $this->styleSummaryTable($sheet);

        return [];
    }

    private function styleMainTable(Worksheet $sheet)
    {
        $tableEndRow = $this->table1RowCount;
        if ($tableEndRow == 0) return;

        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A2:E2")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $currentRow = 3;
        $ordenGrupos = ['Grupo A', 'Grupo B', 'Grupo C'];
        $gruposOrdenados = [];
        foreach ($ordenGrupos as $grupo) {
            if (isset($this->carrerasPorGrupo[$grupo])) $gruposOrdenados[$grupo] = $this->carrerasPorGrupo[$grupo];
        }
        foreach ($this->carrerasPorGrupo as $grupo => $carreras) {
            if (!isset($gruposOrdenados[$grupo])) $gruposOrdenados[$grupo] = $carreras;
        }

        foreach ($gruposOrdenados as $nombreGrupo => $carreras) {
            if (empty($carreras)) continue;
            $numeroFilasGrupo = array_sum($carreras);
            if ($numeroFilasGrupo == 0) continue;

            $filaInicio = $currentRow;
            $filaFin = $currentRow + $numeroFilasGrupo - 1;
            $colorGrupo = $this->gruposCarreras[$nombreGrupo]['color'] ?? 'FFFFFF';
            
            $sheet->getStyle("A{$filaInicio}:E{$filaFin}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorGrupo]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            
            if ($numeroFilasGrupo > 1) {
                $sheet->mergeCells("A{$filaInicio}:A{$filaFin}");
                $sheet->mergeCells("B{$filaInicio}:B{$filaFin}");
                $sheet->getStyle("A{$filaInicio}:B{$filaFin}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            
            $sheet->getStyle("A{$filaInicio}:B{$filaInicio}")->getFont()->setBold(true);

            $careerStartRow = $filaInicio;
            foreach ($carreras as $carrera => $aulaCount) {
                if ($aulaCount > 1) {
                    $careerEndRow = $careerStartRow + $aulaCount - 1;
                    $sheet->mergeCells("C{$careerStartRow}:C{$careerEndRow}");
                    $sheet->getStyle("C{$careerStartRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
                $careerStartRow += $aulaCount;
            }
            $currentRow = $filaFin + 1;
        }

        $totalRow1 = $tableEndRow;
        $sheet->getStyle("A{$totalRow1}:E{$totalRow1}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'D9D9D9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ]);
        $sheet->mergeCells("A{$totalRow1}:D{$totalRow1}");
        $sheet->getStyle("A{$totalRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$totalRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A1:E{$totalRow1}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A3:B{$totalRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D3:E{$totalRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function styleSummaryTable(Worksheet $sheet)
    {
        $sheet->mergeCells('G1:H1');
        $sheet->getStyle('G1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("G2:H2")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $lastRow = $sheet->getHighestRow();
        $summaryTotalRow = 0;
        for ($i = 3; $i <= $lastRow; $i++) {
            if ($sheet->getCell("G{$i}")->getValue() == 'Total') {
                $summaryTotalRow = $i;
                break;
            }
        }

        if ($summaryTotalRow > 0) {
            $sheet->getStyle("G{$summaryTotalRow}:H{$summaryTotalRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'D9D9D9']],
            ]);
            $sheet->getStyle("G2:H{$summaryTotalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("H2:H{$summaryTotalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }
}