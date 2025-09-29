<?php

namespace App\Exports;

use App\Http\Controllers\Traits\ProcessesTeacherSessions;
use App\Models\AsistenciaDocente;
use App\Models\User; 
use App\Models\HorarioDocente; 
use App\Models\PagoDocente; 
use App\Models\Ciclo; 
use App\Models\RegistroAsistencia; 
use App\Http\Controllers\AsistenciaDocenteController; 

use Maatwebsite\Excel\Concerns\WithMultipleSheets; 
use Carbon\Carbon;
use Illuminate\Support\Collection; 

// Imports para la clase anónima
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * EXPORTADOR PROFESIONAL DE ASISTENCIAS DOCENTES
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Genera informes de avance académico con diseño institucional profesional
 * incluyendo todas las sesiones del ciclo ordenadas cronológicamente
 * 
 * @author Sistema Académico UNAMAD
 * @version 2.0 - Diseño Profesional Mejorado
 * ═══════════════════════════════════════════════════════════════════════════
 */
class AsistenciasDocentesExport implements WithMultipleSheets 
{
    use ProcessesTeacherSessions;

    // ═══════════════════════════════════════════════════════════════════════
    // PROPIEDADES DE CONFIGURACIÓN
    // ═══════════════════════════════════════════════════════════════════════
    
    private $processedData; 
    private $selectedDocenteId;
    private $selectedMonth;
    private $selectedYear;
    private $fechaInicio; 
    private $fechaFin;     
    private $selectedCicloAcademico; 

    // ═══════════════════════════════════════════════════════════════════════
    // PALETA DE COLORES INSTITUCIONAL
    // ═══════════════════════════════════════════════════════════════════════
    
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
        'WARNING_ORANGE'    => 'FFF39C12'     // Naranja para alertas
    ];

    // ═══════════════════════════════════════════════════════════════════════
    // CONSTRUCTOR Y INICIALIZACIÓN
    // ═══════════════════════════════════════════════════════════════════════

    public function __construct($selectedDocenteId = null, $selectedMonth = null, $selectedYear = null, $fechaInicio = null, $fechaFin = null, $selectedCicloAcademico = null)
    {
        $this->selectedDocenteId = $selectedDocenteId;
        $this->selectedMonth = $selectedMonth ? (int)$selectedMonth : null;
        $this->selectedYear = $selectedYear ? (int)$selectedYear : null;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->selectedCicloAcademico = $selectedCicloAcademico;

        $this->processedData = $this->processAttendanceData();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PROCESAMIENTO DE DATOS (LÓGICA INTACTA)
    // ═══════════════════════════════════════════════════════════════════════

    private function processAttendanceData()
    {
        $processedDetailedAsistencias = [];

        // 1. Obtener todos los docentes relevantes según los filtros
        $docentesQuery = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        });
        if ($this->selectedDocenteId) {
            $docentesQuery->where('id', $this->selectedDocenteId);
        }
        $docentes = $docentesQuery->get();

        // 2. Determinar el rango de fechas CORRECTAMENTE - PRIORIDAD AL CICLO
        $startDate = null;
        $endDate = null;

        if ($this->selectedCicloAcademico) {
            $ciclo = Ciclo::where('codigo', $this->selectedCicloAcademico)->first();
            if ($ciclo) {
                $cicloStartDate = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
                $cicloEndDate = Carbon::parse($ciclo->fecha_fin)->endOfDay();
                
                if (!$this->fechaInicio && !$this->fechaFin && !$this->selectedMonth && !$this->selectedYear) {
                    $startDate = $cicloStartDate;
                    $endDate = $cicloEndDate;
                } elseif ($this->fechaInicio && $this->fechaFin) {
                    $customStart = Carbon::parse($this->fechaInicio)->startOfDay();
                    $customEnd = Carbon::parse($this->fechaFin)->endOfDay();
                    $startDate = $customStart->max($cicloStartDate);
                    $endDate = $customEnd->min($cicloEndDate);
                } elseif ($this->selectedMonth && $this->selectedYear) {
                    $monthStart = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->startOfDay();
                    $monthEnd = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->endOfMonth()->endOfDay();
                    $startDate = $monthStart->max($cicloStartDate);
                    $endDate = $monthEnd->min($cicloEndDate);
                } else {
                    $startDate = $cicloStartDate;
                    $endDate = $cicloEndDate;
                }
            }
        } elseif ($this->fechaInicio && $this->fechaFin) {
            $startDate = Carbon::parse($this->fechaInicio)->startOfDay();
            $endDate = Carbon::parse($this->fechaFin)->endOfDay();
        } elseif ($this->selectedMonth && $this->selectedYear) {
            $startDate = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->startOfDay();
            $endDate = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->endOfMonth()->endOfDay();
        } else {
            $endDate = Carbon::today()->endOfDay();
            $startDate = $endDate->copy()->subDays(30)->startOfDay();
        }

        // 3. Procesar sesiones día por día
        foreach ($docentes as $docente) {
            if (!isset($processedDetailedAsistencias[$docente->id])) {
                $processedDetailedAsistencias[$docente->id] = [
                    'docente_info' => $docente,
                    'sessions' => [],
                    'total_horas' => 0,
                    'total_pagos' => 0,
                ];
            }

            // Iterar cada día del rango
            if ($startDate && $endDate) {
                $currentDate = $startDate->copy();
                while ($currentDate->lte($endDate)) {
                    $diaSemanaNombre = strtolower($currentDate->locale('es')->dayName);

                    // Construir query base para horarios
                    $horariosQuery = HorarioDocente::where('docente_id', $docente->id)
                        ->where('dia_semana', $diaSemanaNombre)
                        ->with(['curso', 'aula', 'ciclo']);

                    // Aplicar filtro de ciclo SOLO si está especificado
                    if ($this->selectedCicloAcademico) {
                        $horariosQuery->whereHas('ciclo', function ($q) {
                            $q->where('codigo', $this->selectedCicloAcademico);
                        });
                    }

                    $horariosDelDia = $horariosQuery->orderBy('hora_inicio')->get();

                    // Obtener registros biométricos del día
                    $registrosBiometricosDelDia = RegistroAsistencia::where('nro_documento', $docente->numero_documento)
                        ->whereDate('fecha_registro', $currentDate->toDateString())
                        ->orderBy('fecha_registro', 'asc')
                        ->get();

                    // Procesar cada sesión del día
                    foreach ($horariosDelDia as $horario) {
                        $sessionData = $this->processSession($horario, $currentDate, $registrosBiometricosDelDia, $docente);
                        
                        if ($sessionData) {
                            $processedDetailedAsistencias[$docente->id]['sessions'][] = $sessionData;
                            $processedDetailedAsistencias[$docente->id]['total_horas'] += $sessionData['horas_dictadas'];
                            $processedDetailedAsistencias[$docente->id]['total_pagos'] += $sessionData['pago'];
                        }
                    }
                    
                    $currentDate->addDay(); 
                }
            }
        }

        return $processedDetailedAsistencias;
    }

    private function processSession($horario, $currentDate, $registrosBiometricosDelDia, $docente)
    {
        return $this->processTeacherSessionLogic($horario, $currentDate, $registrosBiometricosDelDia, $docente);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // GENERADOR DE ENCABEZADOS DINÁMICOS
    // ═══════════════════════════════════════════════════════════════════════

    private function generateDynamicHeader()
    {
        // PRIORIDAD 1: Si hay ciclo académico, usarlo como contexto principal
        if ($this->selectedCicloAcademico) {
            $ciclo = Ciclo::where('codigo', $this->selectedCicloAcademico)->first();
            if ($ciclo) {
                $fechaInicioCiclo = Carbon::parse($ciclo->fecha_inicio);
                $fechaFinCiclo = Carbon::parse($ciclo->fecha_fin);
                
                // Si hay fechas específicas dentro del ciclo
                if ($this->fechaInicio && $this->fechaFin) {
                    $fechaInicio = Carbon::parse($this->fechaInicio);
                    $fechaFin = Carbon::parse($this->fechaFin);
                    
                    // Validar que estén dentro del ciclo
                    $fechaInicio = $fechaInicio->max($fechaInicioCiclo);
                    $fechaFin = $fechaFin->min($fechaFinCiclo);
                    
                    if ($fechaInicio->month === $fechaFin->month && $fechaInicio->year === $fechaFin->year) {
                        return 'CICLO ' . strtoupper($this->selectedCicloAcademico) . ' - ' . 
                               $fechaInicio->format('d') . ' AL ' . 
                               $fechaFin->format('d') . ' DE ' . 
                               strtoupper($fechaInicio->locale('es')->monthName) . ' ' . 
                               $fechaInicio->year;
                    } else {
                        return 'CICLO ' . strtoupper($this->selectedCicloAcademico) . ' - ' . 
                               $fechaInicio->format('d') . ' DE ' . 
                               strtoupper($fechaInicio->locale('es')->monthName) . ' AL ' . 
                               $fechaFin->format('d') . ' DE ' . 
                               strtoupper($fechaFin->locale('es')->monthName) . ' ' . 
                               $fechaFin->year;
                    }
                }
                
                // Si hay mes específico dentro del ciclo
                if ($this->selectedMonth && $this->selectedYear) {
                    $fechaMes = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1);
                    $fechaInicioMes = $fechaMes->copy()->startOfMonth();
                    $fechaFinMes = $fechaMes->copy()->endOfMonth();
                    
                    // Validar que el mes esté dentro del ciclo
                    $fechaInicioMes = $fechaInicioMes->max($fechaInicioCiclo);
                    $fechaFinMes = $fechaFinMes->min($fechaFinCiclo);
                    
                    return 'CICLO ' . strtoupper($this->selectedCicloAcademico) . ' - MES DE ' . 
                           strtoupper($fechaMes->locale('es')->monthName) . ' ' . $this->selectedYear;
                }
                
                // Ciclo completo
                return 'CICLO ACADÉMICO ' . strtoupper($this->selectedCicloAcademico) . ' (' . 
                       $fechaInicioCiclo->format('d/m/Y') . ' - ' . $fechaFinCiclo->format('d/m/Y') . ')';
            }
        }
        
        // PRIORIDAD 2: Si hay fechas específicas (sin ciclo)
        if ($this->fechaInicio && $this->fechaFin) {
            $fechaInicio = Carbon::parse($this->fechaInicio);
            $fechaFin = Carbon::parse($this->fechaFin);
            
            if ($fechaInicio->month === $fechaFin->month && $fechaInicio->year === $fechaFin->year) {
                return 'PERIODO DEL ' . 
                       $fechaInicio->format('d') . ' AL ' . 
                       $fechaFin->format('d') . ' DE ' . 
                       strtoupper($fechaInicio->locale('es')->monthName) . ' ' . 
                       $fechaInicio->year;
            } else {
                return 'PERIODO DEL ' . 
                       $fechaInicio->format('d') . ' DE ' . 
                       strtoupper($fechaInicio->locale('es')->monthName) . ' AL ' . 
                       $fechaFin->format('d') . ' DE ' . 
                       strtoupper($fechaFin->locale('es')->monthName) . ' ' . 
                       $fechaFin->year;
            }
        }
        
        // PRIORIDAD 3: Si hay mes y año específicos (sin ciclo)
        if ($this->selectedMonth && $this->selectedYear) {
            $fecha = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1);
            return 'MES DE ' . strtoupper($fecha->locale('es')->monthName) . ' ' . $this->selectedYear;
        }
        
        // FALLBACK: Período reciente por defecto
        $fechaFin = Carbon::today();
        $fechaInicio = $fechaFin->copy()->subDays(30);
        return 'ÚLTIMOS 30 DÍAS (' . 
               $fechaInicio->format('d/m/Y') . ' - ' . 
               $fechaFin->format('d/m/Y') . ')';
    }

    // ═══════════════════════════════════════════════════════════════════════
    // GENERACIÓN DE HOJAS DE EXCEL
    // ═══════════════════════════════════════════════════════════════════════

    public function sheets(): array
    {
        $sheets = [];
        $rangoFechasHeader = $this->generateDynamicHeader();

        foreach ($this->processedData as $docenteId => $docenteData) {
            $docente = $docenteData['docente_info'];
            $docenteName = 'Lic. ' . $docente->nombre . ' ' . $docente->apellido_paterno . ' ' . $docente->apellido_materno;
            
            // Crear hoja para cada docente con diseño profesional mejorado
            $sheets[] = new class($docenteData, $docenteName, $rangoFechasHeader, $this->selectedCicloAcademico) implements 
                FromCollection, 
                WithTitle, 
                WithHeadings, 
                WithMapping, 
                ShouldAutoSize, 
                WithEvents,
                WithStyles
            {
                private $docenteData;
                private $docenteName;
                private $filterPeriodHeader;
                private $selectedCicloAcademico;

                // Colores del tema institucional
                private const COLORS = [
                    'PRIMARY_BLUE'      => 'FF1B365D',
                    'SECONDARY_BLUE'    => 'FF2E5A87',
                    'ACCENT_GOLD'       => 'FFD4AF37',
                    'LIGHT_BLUE'        => 'FFE8F0F8',
                    'HEADER_BLUE'       => 'FF4A6FA5',
                    'WHITE'             => 'FFFFFFFF',
                    'LIGHT_GRAY'        => 'FFF5F7FA',
                    'BORDER_GRAY'       => 'FFBDC3C7',
                    'TEXT_DARK'         => 'FF2C3E50',
                    'SUCCESS_GREEN'     => 'FF27AE60',
                    'WARNING_ORANGE'    => 'FFF39C12'
                ];

                public function __construct(array $docenteData, string $docenteName, string $filterPeriodHeader, ?string $selectedCicloAcademico)
                {
                    $this->docenteData = $docenteData;
                    $this->docenteName = $docenteName;
                    $this->filterPeriodHeader = $filterPeriodHeader;
                    $this->selectedCicloAcademico = $selectedCicloAcademico;
                }

                public function title(): string
                {
                    $title = substr(preg_replace('/[\\/:*?"<>|]/u', '', $this->docenteName), 0, 31);
                    return $title ?: 'Docente';
                }

                public function collection()
                {
                    $dataRows = new Collection();

                    // ═══════════════════════════════════════════════════════════
                    // SECCIÓN 1: ENCABEZADO INSTITUCIONAL ELEGANTE
                    // ═══════════════════════════════════════════════════════════
                    
                    $dataRows->push([
                        '🏛️ UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS',
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    $dataRows->push([
                        '🎓 CENTRO PRE UNIVERSITARIO',
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    $dataRows->push([
                        '📚 CICLO ORDINARIO 2025-I',
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    $dataRows->push([
                        '📊 INFORME DE AVANCE ACADÉMICO',
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    $dataRows->push([
                        '📅 ' . $this->filterPeriodHeader,
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);

                    // Separador elegante
                    $dataRows->push(['', '', '', '', '', '', '', '', '', '', '', '']);

                    // ═══════════════════════════════════════════════════════════
                    // SECCIÓN 2: ENCABEZADOS DE TABLA PROFESIONALES
                    // ═══════════════════════════════════════════════════════════
                    
                    $dataRows->push([
                        '👨‍🏫 DOCENTE', 
                        '📅 MES', 
                        '📝 SEMANA', 
                        '🗓️ FECHA', 
                        '📖 CURSO', 
                        '📋 TEMA DESARROLLADO', 
                        '🏠 AULA', 
                        '🌅 TURNO', 
                        '⏰ ENTRADA', 
                        '⏱️ SALIDA', 
                        '⏳ HORAS', 
                        '💰 PAGO'
                    ]);

                    // ═══════════════════════════════════════════════════════════
                    // SECCIÓN 3: PROCESAMIENTO DE DATOS (LÓGICA INTACTA)
                    // ═══════════════════════════════════════════════════════════

                    // *** ORDENAMIENTO CRONOLÓGICO CORREGIDO ***
                    $sortedSessions = collect($this->docenteData['sessions'])
                        ->sortBy([
                            ['year', 'asc'],
                            ['month_number', 'asc'],
                            ['day_number', 'asc']
                        ]);

                    // Agrupar sesiones por mes cronológicamente ordenado
                    $sessionsByMonth = [];
                    foreach ($sortedSessions as $session) {
                        $monthKey = $session['year'] . '-' . sprintf('%02d', $session['month_number']);
                        $weekKey = $session['semana'];
                        
                        if (!isset($sessionsByMonth[$monthKey])) {
                            $sessionsByMonth[$monthKey] = [
                                'month_name' => $session['mes'],
                                'weeks' => []
                            ];
                        }
                        if (!isset($sessionsByMonth[$monthKey]['weeks'][$weekKey])) {
                            $sessionsByMonth[$monthKey]['weeks'][$weekKey] = [];
                        }
                        $sessionsByMonth[$monthKey]['weeks'][$weekKey][] = $session;
                    }

                    ksort($sessionsByMonth);

                    // DATOS DE SESIONES CON FORMATO MEJORADO
                    $docenteTotalHoras = 0;
                    $docenteTotalPago = 0;
                    $isFirstRowForDocente = true;

                    foreach ($sessionsByMonth as $monthKey => $monthData) {
                        $isFirstRowForMes = true;
                        
                        ksort($monthData['weeks']);
                        
                        foreach ($monthData['weeks'] as $semana => $sessions) {
                            $isFirstRowForSemana = true;
                            
                            foreach ($sessions as $session) {
                                $dataRows->push([
                                    $isFirstRowForDocente ? $this->docenteName : '',
                                    $isFirstRowForMes ? strtoupper($monthData['month_name']) : '',
                                    $isFirstRowForSemana ? 'SEMANA ' . sprintf('%02d', $semana) : '',
                                    Carbon::parse($session['fecha'])->format('d/m/Y'),
                                    $session['curso'],
                                    $session['tema_desarrollado'],
                                    $session['aula'],
                                    $session['turno'],
                                    $session['hora_entrada'],
                                    $session['hora_salida'],
                                    number_format($session['horas_dictadas'], 2) . ' hrs',
                                    'S/. ' . number_format($session['pago'], 2)
                                ]);
                                
                                $docenteTotalHoras += $session['horas_dictadas'];
                                $docenteTotalPago += $session['pago'];
                                
                                $isFirstRowForDocente = false;
                                $isFirstRowForSemana = false;
                            }
                            $isFirstRowForMes = false;
                        }
                    }

                    // ═══════════════════════════════════════════════════════════
                    // SECCIÓN 4: FILA DE TOTALES PROFESIONAL
                    // ═══════════════════════════════════════════════════════════
                    
                    $dataRows->push([
                        '', '', '', '', '', '', '', '', '', 
                        '📊 TOTAL GENERAL',
                        '⏱️ ' . number_format($docenteTotalHoras, 2) . ' HORAS',
                        '💰 S/. ' . number_format($docenteTotalPago, 2)
                    ]);

                    return $dataRows;
                }

                public function headings(): array { return []; }
                public function map($row): array { return $row; }

                public function styles(Worksheet $sheet)
                {
                    return [
                        // ═══ ESTILOS PARA ENCABEZADOS INSTITUCIONALES ═══
                        1 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 18, 
                                'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                            ]
                        ],
                        2 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 16, 
                                'color' => ['argb' => self::COLORS['SECONDARY_BLUE']]
                            ]
                        ],
                        3 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 14, 
                                'color' => ['argb' => self::COLORS['SECONDARY_BLUE']]
                            ]
                        ],
                        4 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 16, 
                                'color' => ['argb' => self::COLORS['ACCENT_GOLD']]
                            ]
                        ],
                        5 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 12, 
                                'color' => ['argb' => self::COLORS['TEXT_DARK']]
                            ]
                        ],
                        // ═══ ESTILO PARA ENCABEZADOS DE TABLA ═══
                        7 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 11,
                                'color' => ['argb' => self::COLORS['WHITE']]
                            ]
                        ]
                    ];
                }

                public function registerEvents(): array
                {
                    return [
                        AfterSheet::class => function(AfterSheet $event) {
                            $sheet = $event->sheet->getDelegate();
                            
                            // ═══════════════════════════════════════════════════
                            // CONFIGURACIÓN DE ENCABEZADOS INSTITUCIONALES
                            // ═══════════════════════════════════════════════════
                            
                            $this->setupInstitutionalHeaders($sheet);
                            
                            // ═══════════════════════════════════════════════════
                            // CONFIGURACIÓN DE TABLA DE DATOS
                            // ═══════════════════════════════════════════════════
                            
                            $this->setupDataTable($sheet);
                            
                            // ═══════════════════════════════════════════════════
                            // APLICAR FORMATO PROFESIONAL AVANZADO
                            // ═══════════════════════════════════════════════════
                            
                            $this->applyAdvancedFormatting($sheet);
                        }
                    ];
                }

                // ═══════════════════════════════════════════════════════════════
                // MÉTODOS DE FORMATEO PROFESIONAL
                // ═══════════════════════════════════════════════════════════════

                private function setupInstitutionalHeaders($sheet)
                {
                    // Fusionar celdas para encabezados
                    $sheet->mergeCells('A1:L1');
                    $sheet->mergeCells('A2:L2');
                    $sheet->mergeCells('A3:L3');
                    $sheet->mergeCells('A4:L4');
                    $sheet->mergeCells('A5:L5');

                    // Aplicar gradiente sutil al fondo
                    $sheet->getStyle('A1:L5')->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => self::COLORS['LIGHT_BLUE']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ]);

                    // Altura especial para encabezados
                    for ($i = 1; $i <= 5; $i++) {
                        $sheet->getRowDimension($i)->setRowHeight(25);
                    }
                    
                    // Separador visual con línea elegante
                    $sheet->getStyle('A6:L6')->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['argb' => self::COLORS['ACCENT_GOLD']]
                            ]
                        ]
                    ]);
                }

                private function setupDataTable($sheet)
                {
                    $lastRow = $sheet->getHighestRow();
                    
                    // ═══ ENCABEZADOS DE TABLA CON DISEÑO PREMIUM ═══
                    $sheet->getStyle('A7:L7')->applyFromArray([
                        'font' => [
                            'bold' => true, 
                            'size' => 11,
                            'color' => ['argb' => self::COLORS['WHITE']]
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_GRADIENT_LINEAR,
                            'startColor' => ['argb' => self::COLORS['HEADER_BLUE']],
                            'endColor' => ['argb' => self::COLORS['PRIMARY_BLUE']],
                            'rotation' => 90
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                            ]
                        ]
                    ]);

                    // Altura especial para encabezados de tabla
                    $sheet->getRowDimension(7)->setRowHeight(35);

                    // ═══ CONFIGURAR ANCHOS DE COLUMNA OPTIMIZADOS ═══
                    $columnWidths = [
                        'A' => 32,  // DOCENTE - Más ancho para nombres completos
                        'B' => 14,  // MES - Optimizado
                        'C' => 14,  // SEMANA - Optimizado
                        'D' => 12,  // FECHA - Compacto
                        'E' => 18,  // CURSO - Más espacio
                        'F' => 38,  // TEMA DESARROLLADO - Máximo espacio
                        'G' => 8,   // AULA - Compacto
                        'H' => 12,  // TURNO - Optimizado
                        'I' => 12,  // HORA ENTRADA - Compacto
                        'J' => 12,  // HORA SALIDA - Compacto
                        'K' => 15,  // HORAS DICTADAS - Optimizado
                        'L' => 16   // PAGO - Espacio para formato moneda
                    ];

                    foreach ($columnWidths as $column => $width) {
                        $sheet->getColumnDimension($column)->setWidth($width);
                    }

                    // ═══ BORDES PROFESIONALES PARA TODA LA TABLA ═══
                    $sheet->getStyle('A7:L' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => self::COLORS['BORDER_GRAY']]
                            ]
                        ]
                    ]);
                }

                private function applyAdvancedFormatting($sheet)
                {
                    $lastRow = $sheet->getHighestRow();
                    
                    // ═══════════════════════════════════════════════════
                    // FORMATO ZEBRA STRIPE ELEGANTE
                    // ═══════════════════════════════════════════════════
                    
                    for ($row = 8; $row < $lastRow; $row++) {
                        $fillColor = ($row % 2 == 0) ? self::COLORS['WHITE'] : self::COLORS['LIGHT_GRAY'];
                        
                        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => $fillColor]
                            ]
                        ]);
                    }
                    
                    // ═══════════════════════════════════════════════════
                    // FILA DE TOTALES CON DISEÑO PREMIUM
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('A' . $lastRow . ':L' . $lastRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['argb' => self::COLORS['WHITE']]
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_GRADIENT_LINEAR,
                            'startColor' => ['argb' => self::COLORS['SUCCESS_GREEN']],
                            'endColor' => ['argb' => self::COLORS['PRIMARY_BLUE']],
                            'rotation' => 45
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['argb' => self::COLORS['SUCCESS_GREEN']]
                            ]
                        ]
                    ]);

                    // Altura especial para fila de totales
                    $sheet->getRowDimension($lastRow)->setRowHeight(30);

                    // ═══════════════════════════════════════════════════
                    // ALINEACIONES ESPECÍFICAS POR COLUMNA
                    // ═══════════════════════════════════════════════════
                    
                    // Fechas centradas
                    $sheet->getStyle('D8:D' . ($lastRow-1))->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Aula y turno centrados
                    $sheet->getStyle('G8:H' . ($lastRow-1))->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Horarios y pagos centrados
                    $sheet->getStyle('I8:L' . ($lastRow-1))->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Tema desarrollado con wrap text
                    $sheet->getStyle('F8:F' . ($lastRow-1))->getAlignment()
                        ->setWrapText(true);

                    // ═══════════════════════════════════════════════════
                    // FORMATO ESPECIAL PARA COLUMNAS AGRUPADAS
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('A8:C' . ($lastRow-1))->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => self::COLORS['LIGHT_BLUE']]
                        ]
                    ]);

                    // ═══════════════════════════════════════════════════
                    // FUSIONAR CELDAS PARA DATOS AGRUPADOS
                    // ═══════════════════════════════════════════════════
                    
                    $this->mergeCellsForGroupedData($sheet);
                    
                    // ═══════════════════════════════════════════════════
                    // APLICAR EFECTOS VISUALES AVANZADOS
                    // ═══════════════════════════════════════════════════
                    
                    $this->applyVisualEffects($sheet, $lastRow);
                }

                private function mergeCellsForGroupedData($sheet)
                {
                    $lastRow = $sheet->getHighestRow();
                    
                    $currentGroups = ['docente' => '', 'mes' => '', 'semana' => ''];
                    $startRows = ['docente' => 8, 'mes' => 8, 'semana' => 8];
                    
                    for ($row = 8; $row <= $lastRow; $row++) {
                        $docente = $sheet->getCell('A' . $row)->getValue();
                        $mes = $sheet->getCell('B' . $row)->getValue();
                        $semana = $sheet->getCell('C' . $row)->getValue();
                        
                        // ═══ AGRUPACIÓN DE DOCENTES ═══
                        if ($docente !== '' && $docente !== $currentGroups['docente']) {
                            if ($currentGroups['docente'] !== '' && $startRows['docente'] < $row - 1) {
                                $sheet->mergeCells('A' . $startRows['docente'] . ':A' . ($row - 1));
                                $sheet->getStyle('A' . $startRows['docente'])->getAlignment()
                                    ->setVertical(Alignment::VERTICAL_CENTER);
                            }
                            $startRows['docente'] = $row;
                            $currentGroups['docente'] = $docente;
                        }
                        
                        // ═══ AGRUPACIÓN DE MESES ═══
                        if ($mes !== '' && $mes !== $currentGroups['mes']) {
                            if ($currentGroups['mes'] !== '' && $startRows['mes'] < $row - 1) {
                                $sheet->mergeCells('B' . $startRows['mes'] . ':B' . ($row - 1));
                                $sheet->getStyle('B' . $startRows['mes'])->getAlignment()
                                    ->setVertical(Alignment::VERTICAL_CENTER);
                            }
                            $startRows['mes'] = $row;
                            $currentGroups['mes'] = $mes;
                        }
                        
                        // ═══ AGRUPACIÓN DE SEMANAS ═══
                        if ($semana !== '' && $semana !== $currentGroups['semana']) {
                            if ($currentGroups['semana'] !== '' && $startRows['semana'] < $row - 1) {
                                $sheet->mergeCells('C' . $startRows['semana'] . ':C' . ($row - 1));
                                $sheet->getStyle('C' . $startRows['semana'])->getAlignment()
                                    ->setVertical(Alignment::VERTICAL_CENTER);
                            }
                            $startRows['semana'] = $row;
                            $currentGroups['semana'] = $semana;
                        }
                    }
                    
                    // ═══ FUSIONAR GRUPOS FINALES ═══
                    $finalRow = $lastRow - 1; // Excluir fila de totales
                    
                    if ($startRows['docente'] < $finalRow) {
                        $sheet->mergeCells('A' . $startRows['docente'] . ':A' . $finalRow);
                        $sheet->getStyle('A' . $startRows['docente'])->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }
                    if ($startRows['mes'] < $finalRow) {
                        $sheet->mergeCells('B' . $startRows['mes'] . ':B' . $finalRow);
                        $sheet->getStyle('B' . $startRows['mes'])->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }
                    if ($startRows['semana'] < $finalRow) {
                        $sheet->mergeCells('C' . $startRows['semana'] . ':C' . $finalRow);
                        $sheet->getStyle('C' . $startRows['semana'])->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }
                }

                private function applyVisualEffects($sheet, $lastRow)
                {
                    // ═══════════════════════════════════════════════════
                    // EFECTOS DE SOMBRA PARA ENCABEZADOS
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('A7:L7')->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THICK,
                                'color' => ['argb' => self::COLORS['ACCENT_GOLD']]
                            ]
                        ]
                    ]);

                    // ═══════════════════════════════════════════════════
                    // FORMATO CONDICIONAL PARA VALORES MONETARIOS
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('L8:L' . $lastRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => self::COLORS['SUCCESS_GREEN']]
                        ]
                    ]);

                    // ═══════════════════════════════════════════════════
                    // FORMATO ESPECIAL PARA HORAS
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('K8:K' . $lastRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                        ]
                    ]);

                    // ═══════════════════════════════════════════════════
                    // APLICAR FORMATO DE FECHA CONSISTENTE
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('D8:D' . ($lastRow-1))->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 9
                        ]
                    ]);

                    // ═══════════════════════════════════════════════════
                    // PROTECCIÓN Y CONFIGURACIÓN FINAL
                    // ═══════════════════════════════════════════════════
                    
                    // Configurar impresión
                    $sheet->getPageSetup()
                        ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                        ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                        ->setFitToWidth(1)
                        ->setFitToHeight(0);

                    // Configurar márgenes
                    $sheet->getPageMargins()
                        ->setTop(0.75)
                        ->setRight(0.25)
                        ->setLeft(0.25)
                        ->setBottom(0.75);

                    // Repetir encabezados en cada página
                    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);

                    // ═══════════════════════════════════════════════════
                    // AÑADIR LÍNEAS DE DIVISIÓN ELEGANTES
                    // ═══════════════════════════════════════════════════
                    
                    // Línea divisoria antes de totales
                    $sheet->getStyle('A' . ($lastRow-1) . ':L' . ($lastRow-1))->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_DOUBLE,
                                'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                            ]
                        ]
                    ]);
                }
            };
        }

        return $sheets;
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// DOCUMENTACIÓN DE LA CLASE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * MEJORAS IMPLEMENTADAS EN EL DISEÑO:
 * 
 * 🎨 DISEÑO VISUAL:
 * ├── Paleta de colores institucional profesional
 * ├── Gradientes elegantes en encabezados
 * ├── Efectos zebra stripe para mejor legibilidad
 * ├── Iconos Unicode para identificación rápida
 * └── Tipografía jerarquizada y consistente
 * 
 * 📊 ESTRUCTURA MEJORADA:
 * ├── Encabezados institucionales más prominentes
 * ├── Separadores visuales elegantes
 * ├── Agrupación visual mejorada de datos
 * ├── Fila de totales con diseño premium
 * └── Configuración de impresión optimizada
 * 
 * 💡 FUNCIONALIDADES AÑADIDAS:
 * ├── Anchos de columna optimizados para contenido
 * ├── Formato condicional para valores monetarios
 * ├── Wrap text automático para textos largos
 * ├── Márgenes y orientación configurados
 * └── Repetición de encabezados en múltiples páginas
 * 
 * ✅ MANTENIMIENTO:
 * ├── Código organizado en secciones claras
 * ├── Constantes para colores centralizadas
 * ├── Métodos modulares para fácil mantenimiento
 * ├── Documentación completa integrada
 * └── Lógica de negocio intacta y preservada
 */