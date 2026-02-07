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
use Maatwebsite\Excel\Concerns\WithStartRow;
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

    // Paleta de colores institucional (Sincronizada con AsistenciasDocentesExport)
    private const COLORS = [
        'PRIMARY_BLUE'      => 'FF1B365D',    // Azul institucional principal
        'SECONDARY_BLUE'    => 'FF2E5A87',    // Azul secundario
        'ACCENT_GOLD'       => 'FFD4AF37',    // Dorado de acento
        'LIGHT_BLUE'        => 'FFE8F0F8',    // Azul claro para alternos
        'HEADER_BLUE'       => 'FF4A6FA5',    // Azul para encabezados de tabla
        'WHITE'             => 'FFFFFFFF',    // Blanco puro
        'LIGHT_GRAY'        => 'FFF5F7FA',    // Gris muy claro
        'BORDER_GRAY'       => 'FFBDC3C7',    // Gris para bordes
        'TEXT_DARK'         => 'FF2C3E50',    // Texto oscuro
        'SUCCESS_GREEN'     => 'FF27AE60',    // Verde para totales
        'WARNING_ORANGE'    => 'FFF39C12',    // Naranja para alertas
        'PALE_GREEN'        => 'FFE2EFDA',    // Verde pálido (académico)
        'PALE_ORANGE'       => 'FFFFF2CC'     // Naranja pálido (costos)
    ];

    public function __construct($cicloId)
    {
        $this->cicloId = $cicloId;
        $this->ciclo = Ciclo::findOrFail($cicloId);
        $this->data = $this->collectData();
    }

    private function collectData()
    {
        // 🚨 FILTRO CRÍTICO: Solo docentes que tienen horarios registrados en el ciclo seleccionado
        $docentes = User::whereHas('roles', function($q) {
            $q->where('nombre', 'profesor');
        })->whereHas('horarios', function($q) {
            $q->where('ciclo_id', $this->cicloId);
        })->with(['horarios' => function($q) {
            $q->where('ciclo_id', $this->cicloId)->with(['curso', 'aula', 'ciclo']);
        }])->orderBy('apellido_paterno')->orderBy('nombre')->get();

        $results = new Collection();
        $contador = 1;

        // Rango de fechas del ciclo
        $startDate = Carbon::parse($this->ciclo->fecha_inicio)->startOfDay();
        $endDate = Carbon::parse($this->ciclo->fecha_fin)->endOfDay();
        
        // Si la fecha fin es futura, usar la fecha actual
        if ($endDate->isFuture()) {
            $endDate = Carbon::today()->endOfDay();
        }

        foreach ($docentes as $docente) {
            $pago = PagoDocente::where('docente_id', $docente->id)
                ->where(function($q) {
                    $q->where('fecha_inicio', '<=', now())
                      ->orWhereNull('fecha_inicio');
                })
                ->orderBy('fecha_inicio', 'desc')
                ->first();
            
            $tarifa = $pago ? $pago->tarifa_por_hora : 0;

            // Obtener todos los registros biométricos del docente en el rango del ciclo
            $registrosBiometricos = \App\Models\RegistroAsistencia::where('nro_documento', $docente->numero_documento)
                ->whereBetween('fecha_registro', [$startDate, $endDate])
                ->orderBy('fecha_registro', 'asc')
                ->get()
                ->groupBy(function($item) {
                    return Carbon::parse($item->fecha_registro)->toDateString();
                });

            // Agrupar por curso y grupo
            $horariosAgrupados = $docente->horarios->groupBy(function($item) {
                return $item->curso_id . '-' . $item->grupo;
            });

            $totalHorasSemanalesDocente = 0;
            $totalHorasRealDocente = 0;
            $totalCostoDocente = 0;

            $rowsDocenteTemp = [];

            foreach ($horariosAgrupados as $key => $horariosDoc) {
                $primerHorario = $horariosDoc->first();
                $nombreCurso = $primerHorario->curso ? $primerHorario->curso->nombre : 'Sin curso';
                
                // Aulas únicas
                $aulas = $horariosDoc->pluck('aula.nombre')->unique()->filter()->implode(', ');
                $grupoOriginal = $primerHorario->grupo ?: 'A';
                $grupoTexto = $aulas ? $aulas . " (G: {$grupoOriginal})" : $grupoOriginal;

                $horasSemanalesCurso = 0;
                $horasRealCurso = 0;

                // Calcular horas semanales programadas del curso
                foreach ($horariosDoc as $h) {
                    if ($h->es_receso) continue;
                    
                    $ini = Carbon::parse($h->hora_inicio);
                    $fni = Carbon::parse($h->hora_fin);
                    $decimal = abs($fni->diffInMinutes($ini)) / 60;
                    $decimal = $this->ajustarHorasReceso($h->hora_inicio, $h->hora_fin, $decimal);
                    $horasSemanalesCurso += $decimal;
                }

                // ⚡ CÁLCULO REAL: Iterar cada día del ciclo y calcular horas reales dictadas
                $currentDate = $startDate->copy();
                while ($currentDate->lte($endDate)) {
                    $diaSemanaNombre = $this->ciclo->getDiaHorarioParaFecha($currentDate);
                    $fechaString = $currentDate->toDateString();
                    
                    // Filtrar horarios de este curso/grupo para este día
                    $horariosDelDia = $horariosDoc->filter(function($horario) use ($diaSemanaNombre) {
                        return strtolower($horario->dia_semana) === strtolower($diaSemanaNombre);
                    });

                    $registrosDelDia = $registrosBiometricos->get($fechaString, collect([]));

                    foreach ($horariosDelDia as $horario) {
                        $horasRealSesion = $this->calcularHorasRealesSesion($horario, $currentDate, $registrosDelDia, $docente, $pago);
                        $horasRealCurso += $horasRealSesion;
                    }
                    
                    $currentDate->addDay();
                }

                $costoCursoCiclo = $horasRealCurso * $tarifa;

                $rowsDocenteTemp[] = [
                    'contador' => $contador,
                    'nombre' => $docente->nombre_completo,
                    'curso' => $nombreCurso,
                    'grupo' => $grupoTexto,
                    'horas_semana_curso' => round($horasSemanalesCurso, 2),
                    'horas_ciclo_curso' => round($horasRealCurso, 2),
                    'tarifa' => $tarifa,
                    'costo_curso' => $costoCursoCiclo,
                    'is_first' => false,
                ];

                $totalHorasSemanalesDocente += $horasSemanalesCurso;
                $totalHorasRealDocente += $horasRealCurso;
                $totalCostoDocente += $costoCursoCiclo;
            }

            if (count($rowsDocenteTemp) > 0) {
                $rowsDocenteTemp[0]['is_first'] = true;
                $rowsDocenteTemp[0]['total_horas_ciclo_doc'] = round($totalHorasRealDocente, 2);
                $rowsDocenteTemp[0]['horas_semanales_total_doc'] = round($totalHorasSemanalesDocente, 2);
                $rowsDocenteTemp[0]['costo_total_doc'] = $totalCostoDocente;
                
                foreach ($rowsDocenteTemp as $row) {
                    $results->push($row);
                }
                $contador++;
            }
        }

        return $results;
    }

    /**
     * Calcula las horas reales dictadas para una sesión, aplicando descuentos por tardanza y recesos.
     * Lógica unificada con ProcessesTeacherSessions trait.
     */
    private function calcularHorasRealesSesion($horario, $currentDate, $registrosDelDia, $docente, $pagoDocente)
    {
        if (!$horario || !$horario->hora_inicio || !$horario->hora_fin) {
            return 0;
        }

        $horaInicioProgramada = Carbon::parse($horario->hora_inicio);
        $horaFinProgramada = Carbon::parse($horario->hora_fin);

        $horarioInicioHoy = $currentDate->copy()->setTime($horaInicioProgramada->hour, $horaInicioProgramada->minute, $horaInicioProgramada->second);
        $horarioFinHoy = $currentDate->copy()->setTime($horaFinProgramada->hour, $horaFinProgramada->minute, $horaFinProgramada->second);

        // Tolerancias (mismas que ProcessesTeacherSessions)
        $toleranciaEntradaAnticipada = 15;
        $toleranciaEntradaTarde = 5;
        $toleranciaVentanaEntrada = 120;
        $toleranciaVentanaSalida = 60;
        $toleranciaSalidaAnticipada = 15;

        // Búsqueda de registros biométricos
        $entradaBiometrica = $registrosDelDia
            ->filter(function($r) use ($horarioInicioHoy, $toleranciaEntradaAnticipada, $toleranciaVentanaEntrada) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioInicioHoy->copy()->subMinutes($toleranciaEntradaAnticipada),
                    $horarioInicioHoy->copy()->addMinutes($toleranciaVentanaEntrada)
                );
            })
            ->sortBy('fecha_registro')
            ->first();

        $salidaBiometrica = $registrosDelDia
            ->filter(function($r) use ($horarioFinHoy, $toleranciaSalidaAnticipada, $toleranciaVentanaSalida) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioFinHoy->copy()->subMinutes($toleranciaSalidaAnticipada),
                    $horarioFinHoy->copy()->addMinutes($toleranciaVentanaSalida)
                );
            })
            ->sortByDesc('fecha_registro')
            ->first();

        // Si no hay entrada y salida, no se pagó esta sesión
        if (!$entradaBiometrica || !$salidaBiometrica) {
            return 0;
        }

        $entradaCarbon = Carbon::parse($entradaBiometrica->fecha_registro);
        $salidaCarbon = Carbon::parse($salidaBiometrica->fecha_registro);

        // Determinar hora de inicio efectiva respetando tolerancia de tardanza
        $tardinessThreshold = $horarioInicioHoy->copy()->addMinutes($toleranciaEntradaTarde);
        
        if ($entradaCarbon->lessThanOrEqualTo($tardinessThreshold)) {
            $effectiveStartTime = $horarioInicioHoy;
        } else {
            $effectiveStartTime = $entradaCarbon;
        }

        // El fin efectivo es el más temprano entre la hora programada y la hora de salida
        $finEfectivo = $salidaCarbon->min($horarioFinHoy);

        if ($finEfectivo <= $effectiveStartTime) {
            return 0;
        }

        $duracionBruta = $effectiveStartTime->diffInMinutes($finEfectivo);

        // Descuento de recesos
        $cicloDelHorario = $horario->ciclo ?? $this->ciclo;
        $minutosRecesoManana = 0;
        $minutosRecesoTarde = 0;

        // Receso de mañana
        if ($cicloDelHorario && $cicloDelHorario->receso_manana_inicio && $cicloDelHorario->receso_manana_fin) {
            $recesoMananaInicio = $currentDate->copy()->setTimeFromTimeString($cicloDelHorario->receso_manana_inicio);
            $recesoMananaFin = $currentDate->copy()->setTimeFromTimeString($cicloDelHorario->receso_manana_fin);
            
            if ($effectiveStartTime < $recesoMananaFin && $finEfectivo > $recesoMananaInicio) {
                $superposicionInicio = $effectiveStartTime->max($recesoMananaInicio);
                $superposicionFin = $finEfectivo->min($recesoMananaFin);
                if ($superposicionFin > $superposicionInicio) {
                    $minutosRecesoManana = $superposicionInicio->diffInMinutes($superposicionFin);
                }
            }
        }

        // Receso de tarde
        if ($cicloDelHorario && $cicloDelHorario->receso_tarde_inicio && $cicloDelHorario->receso_tarde_fin) {
            $recesoTardeInicio = $currentDate->copy()->setTimeFromTimeString($cicloDelHorario->receso_tarde_inicio);
            $recesoTardeFin = $currentDate->copy()->setTimeFromTimeString($cicloDelHorario->receso_tarde_fin);
            
            if ($effectiveStartTime < $recesoTardeFin && $finEfectivo > $recesoTardeInicio) {
                $superposicionInicio = $effectiveStartTime->max($recesoTardeInicio);
                $superposicionFin = $finEfectivo->min($recesoTardeFin);
                if ($superposicionFin > $superposicionInicio) {
                    $minutosRecesoTarde = $superposicionInicio->diffInMinutes($superposicionFin);
                }
            }
        }

        $minutosNetos = $duracionBruta - $minutosRecesoManana - $minutosRecesoTarde;
        return max(0, $minutosNetos) / 60;
    }

    private function ajustarHorasReceso($inicio, $fin, $decimal)
    {
        $baseDate = Carbon::today();
        $startH = $baseDate->copy()->setTimeFromTimeString(Carbon::parse($inicio)->format('H:i'));
        $endH = $baseDate->copy()->setTimeFromTimeString(Carbon::parse($fin)->format('H:i'));
        if ($endH < $startH) $endH->addDay();

        $minutosSustraer = 0;

        // Receso Mañana
        if ($this->ciclo->receso_manana_inicio && $this->ciclo->receso_manana_fin) {
            $rS = $baseDate->copy()->setTimeFromTimeString($this->ciclo->receso_manana_inicio);
            $rE = $baseDate->copy()->setTimeFromTimeString($this->ciclo->receso_manana_fin);
            
            if ($startH < $rE && $endH > $rS) {
                $overlapS = $startH->max($rS);
                $overlapE = $endH->min($rE);
                if ($overlapE > $overlapS) {
                    $minutosSustraer += $overlapS->diffInMinutes($overlapE);
                }
            }
        }

        // Receso Tarde
        if ($this->ciclo->receso_tarde_inicio && $this->ciclo->receso_tarde_fin) {
            $rS = $baseDate->copy()->setTimeFromTimeString($this->ciclo->receso_tarde_inicio);
            $rE = $baseDate->copy()->setTimeFromTimeString($this->ciclo->receso_tarde_fin);
            
            if ($startH < $rE && $endH > $rS) {
                $overlapS = $startH->max($rS);
                $overlapE = $endH->min($rE);
                if ($overlapE > $overlapS) {
                    $minutosSustraer += $overlapS->diffInMinutes($overlapE);
                }
            }
        }

        if ($minutosSustraer > 0) {
            $decimal -= ($minutosSustraer / 60);
        }

        return max(0, $decimal);
    }

    private function contarOcurrenciasDias($ciclo)
    {
        $inicio = Carbon::parse($ciclo->fecha_inicio);
        $fin = Carbon::parse($ciclo->fecha_fin);
        $conteo = ['Lunes'=>0,'Martes'=>0,'Miércoles'=>0,'Miercoles'=>0,'Jueves'=>0,'Viernes'=>0];
        $actual = $inicio->copy();
        while ($actual <= $fin) {
            $diaHorario = $ciclo->getDiaHorarioParaFecha($actual);
            if ($diaHorario && isset($conteo[$diaHorario])) $conteo[$diaHorario]++;
            $actual->addDay();
        }
        return $conteo;
    }

    public function collection() { return $this->data; }
    public function title(): string { return 'Resumen Carga Horaria'; }
    public function headings(): array
    {
        $inicio = Carbon::parse($this->ciclo->fecha_inicio);
        $fin = Carbon::parse($this->ciclo->fecha_fin);
        $semanas = ceil($inicio->diffInDays($fin) / 7);
        $titulo3 = "REPORTE DE CARGA HORARIA (HORAS REALES) - ". strtoupper($this->ciclo->nombre) ." (". $semanas ." SEMANAS)";

        return [
            ['UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS'],
            ['CENTRO PRE UNIVERSITARIO'],
            [$titulo3],
            [''], [''], [''], // Filas 4, 5, 6
            ['N°', 'DOCENTE', 'CURSO', 'AULAS / GRUPOS', 'H. SEMANALES (PROG.)', 'H. REALES DICTADAS', 'TOTAL H. REALES', 'H. SEMANALES TOTAL', 'COSTO HORA', 'COSTO CURSO', 'TOTAL COSTO'] // Fila 7
        ];
    }

    public function map($row): array
    {
        // Solo mostrar datos combinables si es la primera fila del docente
        return [
            $row['is_first'] ? $row['contador'] : '',
            $row['is_first'] ? $row['nombre'] : '',
            $row['curso'],
            $row['grupo'],
            $row['horas_semana_curso'],
            $row['horas_ciclo_curso'],
            $row['is_first'] ? $row['total_horas_ciclo_doc'] : '',
            $row['is_first'] ? $row['horas_semanales_total_doc'] : '',
            $row['is_first'] ? $row['tarifa'] : '',
            $row['costo_curso'],
            $row['is_first'] ? $row['costo_total_doc'] : '',
        ];
    }

    public function styles(Worksheet $sheet) { return []; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'K';
                $lastDataRow = $sheet->getHighestRow();
                
                // 1. --- CONFIGURACIÓN DE PÁGINA PROFESIONAL ---
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);
                $sheet->getStyle("A1:{$lastCol}{$lastDataRow}")->getFont()->setName('Calibri');

                // 2. --- ESTILIZAR CABECERAS (FILAS 1-3) ---
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->getStyle("A1:A3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getStyle("A1")->getFont()->setSize(20);
                $sheet->getStyle("A3")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLORS['SECONDARY_BLUE']));

                // Logos
                $logoUnamad = public_path('assets/images/logo unamad constancia.png');
                if (file_exists($logoUnamad)) {
                    $drawing = new Drawing();
                    $drawing->setName('Logo UNAMAD'); $drawing->setPath($logoUnamad); $drawing->setHeight(90);
                    $drawing->setCoordinates('A1'); $drawing->setOffsetX(15); $drawing->setWorksheet($sheet);
                }
                $logoCepre = public_path('assets/images/logo cepre costancia.png');
                if (file_exists($logoCepre)) {
                    $drawing2 = new Drawing();
                    $drawing2->setName('Logo CEPRE'); $drawing2->setPath($logoCepre); $drawing2->setHeight(90);
                    $drawing2->setCoordinates('J1'); $drawing2->setOffsetX(80); $drawing2->setWorksheet($sheet);
                }

                // 3. --- CUADRO DE RESUMEN EJECUTIVO (FILA 4-5) ---
                $totalDocentes = $this->data->groupBy('contador')->count();
                $totalHorasSemanal = $this->data->where('is_first', true)->sum('horas_semanales_total_doc');
                $totalMontoPlanilla = 0;
                foreach($this->data->groupBy('contador') as $rows) { $totalMontoPlanilla += $rows->first()['costo_total_doc']; }

                $sheet->mergeCells("B4:E5");
                $sheet->setCellValue("B4", "RESUMEN EJECUTIVO:\nN° DOCENTES: {$totalDocentes} | TOTAL H. SEMANALES: {$totalHorasSemanal} | TOTAL PRESUPUESTO: S/. " . number_format($totalMontoPlanilla, 2));
                $sheet->getStyle("B4")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("B4")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLORS['LIGHT_BLUE']]],
                    'borders' => ['outline' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => self::COLORS['ACCENT_GOLD']]]]
                ]);

                // 4. --- METADATOS DE AUDITORÍA (FILA 6) ---
                $user = auth()->user() ? auth()->user()->nombre_completo : 'Auditoría de Sistema';
                $now = now()->format('d/m/Y H:i');
                $sheet->mergeCells("H6:K6");
                $sheet->setCellValue("H6", "GENERADO POR: " . strtoupper($user) . " [" . $now . "]");
                $sheet->getStyle("H6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("H6")->getFont()->setBold(true)->setSize(8)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF7F8C8D'));

                // 5. --- ENCABEZADOS TABLA (FILA 7) ---
                $rowHeaders = 7;
                $sheet->getStyle("A{$rowHeaders}:{$lastCol}{$rowHeaders}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => self::COLORS['WHITE']], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLORS['HEADER_BLUE']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']],
                        'bottom' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => self::COLORS['ACCENT_GOLD']]]
                    ]
                ]);
                $sheet->getRowDimension($rowHeaders)->setRowHeight(50);

                // 6. --- DATOS Y ESTILOS (FILA 8+) ---
                $dataStart = 8;
                if ($lastDataRow < $dataStart) $lastDataRow = $dataStart;

                $sheet->getStyle("A{$dataStart}:{$lastCol}{$lastDataRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLORS['BORDER_GRAY']]]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
                ]);

                // Estilización por Filas y Alertas
                for ($row = $dataStart; $row <= $lastDataRow; $row++) {
                    // Fondo área académica (Blanco / Celeste alterno)
                    $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($row % 2 == 0 ? self::COLORS['LIGHT_BLUE'] : self::COLORS['WHITE']);
                    
                    // Fondo área económica (Arena Suave)
                    $sheet->getStyle("I{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFAEB');

                    // 🚨 AUDITORÍA: Resaltar docentes sin tarifa (ERROR DE CONFIGURACIÓN)
                    $tarifaRaw = $sheet->getCell('I' . $row)->getValue();
                    if ($tarifaRaw === 0 || $tarifaRaw === "0" || $tarifaRaw === 0.0) {
                        $sheet->getStyle("I{$row}:K{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED))->setBold(true);
                    }
                }

                // Alineaciones y Formato de Moneda Premium
                $sheet->getStyle("B{$dataStart}:D{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("E{$dataStart}:I{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("K{$dataStart}:K{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$dataStart}:J{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A{$dataStart}:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currFormat = '_-"S/."* #,##0.00_-;-"S/."* #,##0.00_-;_-"S/."* "-"??_-;_-@_-';
                $sheet->getStyle("I{$dataStart}:K{$lastDataRow}")->getNumberFormat()->setFormatCode($currFormat);

                // 7. --- FUSIONAR CELDAS ---
                $curr = $dataStart;
                foreach($this->data->groupBy('contador') as $rows) {
                    $cnt = count($rows);
                    if ($cnt >= 1) {
                        $end = $curr + $cnt - 1;
                        $sheet->mergeCells("A{$curr}:A{$end}");
                        $sheet->mergeCells("B{$curr}:B{$end}");
                        $sheet->mergeCells("G{$curr}:G{$end}");
                        $sheet->mergeCells("H{$curr}:H{$end}");
                        $sheet->mergeCells("I{$curr}:I{$end}");
                        $sheet->mergeCells("K{$curr}:K{$end}");
                    }
                    $curr += $cnt;
                }

                // 8. --- RESUMEN TOTAL (Borde doble contable) ---
                $totalRow = $lastDataRow + 2;
                $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", "RESUMEN CONSOLIDADO DE PLANILLA POR CARGA HORARIA");
                $sheet->setCellValue("K{$totalRow}", $totalMontoPlanilla);
                
                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => self::COLORS['WHITE']]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLORS['PRIMARY_BLUE']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => self::COLORS['ACCENT_GOLD']]],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => self::COLORS['ACCENT_GOLD']]]
                    ]
                ]);
                $sheet->getStyle("K{$totalRow}")->getNumberFormat()->setFormatCode($currFormat);
                $sheet->getRowDimension($totalRow)->setRowHeight(40);

                // 9. --- SECCIÓN DE FIRMAS ELEGANTES ---
                $signRow = $totalRow + 4;
                if ($signRow + 2 > 1000) $signRow = $totalRow + 2; // Prevent overflow
                $sheet->mergeCells("B{$signRow}:D{$signRow}");
                $sheet->mergeCells("H{$signRow}:J{$signRow}");
                $sheet->setCellValue("B{$signRow}", "__________________________\nRESPONSABLE DE CARGA\nUnidad de Personal");
                $sheet->setCellValue("H{$signRow}", "__________________________\nVº Bº DIRECCIÓN CEPRE\nUniversidad Nacional Amazonica");
                $sheet->getStyle("B{$signRow}:J{$signRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                $sheet->getStyle("B{$signRow}:J{$signRow}")->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(self::COLORS['TEXT_DARK']));

                // 10. --- ANCHOS OPTIMIZADOS ---
                $widths = ['A'=>6, 'B'=>45, 'C'=>30, 'D'=>40, 'E'=>14, 'F'=>14, 'G'=>16, 'H'=>16, 'I'=>15, 'J'=>15, 'K'=>20];
                foreach($widths as $col => $w) { $sheet->getColumnDimension($col)->setWidth($w); }
            }
        ];
    }
}
