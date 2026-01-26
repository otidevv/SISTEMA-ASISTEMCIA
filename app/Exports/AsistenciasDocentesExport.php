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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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
    use \App\Http\Controllers\Traits\HandlesSaturdayRotation;

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
        // ⚡ OPTIMIZACIÓN: Aumentar límites de PHP para exportaciones grandes
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '512M');
        
        $this->selectedDocenteId = $selectedDocenteId;
        $this->selectedMonth = $selectedMonth ? (int)$selectedMonth : null;
        $this->selectedYear = $selectedYear ? (int)$selectedYear : null;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->selectedCicloAcademico = $selectedCicloAcademico;

        $this->processedData = $this->processAttendanceData();
    }

    /**
     * Obtener los datos procesados (para uso en el controlador de reportes)
     */
    public function getProcessedData()
    {
        return $this->processedData;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PROCESAMIENTO DE DATOS (LÓGICA ORIGINAL RESTAURADA)
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

        // 3. Procesar sesiones día por día (LÓGICA ORIGINAL)
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
                // ⚡ OPTIMIZACIÓN: Pre-cargar todos los horarios del docente de una vez
                $todosHorariosDocente = HorarioDocente::where('docente_id', $docente->id)
                    ->with(['curso', 'aula', 'ciclo']);
                
                if ($this->selectedCicloAcademico) {
                    $todosHorariosDocente->whereHas('ciclo', function ($q) {
                        $q->where('codigo', $this->selectedCicloAcademico);
                    });
                }
                
                $todosHorariosDocente = $todosHorariosDocente->get();
                
                // ⚡ OPTIMIZACIÓN: Pre-cargar todos los registros biométricos del rango
                $todosRegistrosDocente = RegistroAsistencia::where('nro_documento', $docente->numero_documento)
                    ->whereBetween('fecha_registro', [$startDate, $endDate])
                    ->orderBy('fecha_registro', 'asc')
                    ->get();
                
                // Indexar por fecha para acceso rápido
                $registrosPorFecha = $todosRegistrosDocente->groupBy(function($item) {
                    return Carbon::parse($item->fecha_registro)->toDateString();
                });
                
                // ⚡ OPTIMIZACIÓN CRÍTICA: Obtener ciclo activo UNA SOLA VEZ fuera del loop
                $cicloActivoParaRotacion = Ciclo::where('es_activo', true)->first();
                
                $currentDate = $startDate->copy();
                
                // DEBUG: Log para ver el rango de fechas y horarios
                \Log::info("EXPORT DEBUG - Docente {$docente->nombre}: Rango {$startDate->toDateString()} a {$endDate->toDateString()}, Horarios precargados: " . $todosHorariosDocente->count() . ", Registros biometricos: " . $todosRegistrosDocente->count());
                
                while ($currentDate->lte($endDate)) {
                    // ⚡ OPTIMIZADO: Usar ciclo pre-cargado en lugar de consultar cada vez
                    $diaSemanaNombre = $this->getDiaHorarioParaFecha($currentDate, $cicloActivoParaRotacion);
                    $fechaString = $currentDate->toDateString();

                    // Filtrar horarios del día desde la colección pre-cargada
                    $horariosDelDia = $todosHorariosDocente->filter(function($horario) use ($diaSemanaNombre) {
                        return strtolower($horario->dia_semana) === strtolower($diaSemanaNombre);
                    })->sortBy('hora_inicio');
                    
                    // DEBUG: Log para ver si se encuentran horarios para cada día
                    if ($horariosDelDia->count() > 0) {
                        \Log::info("EXPORT DEBUG - {$fechaString} ({$diaSemanaNombre}): {$horariosDelDia->count()} horarios encontrados");
                    }

                    // Obtener registros biométricos del día desde la colección pre-cargada
                    $registrosBiometricosDelDia = $registrosPorFecha->get($fechaString, collect([]));

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
                
                // ═══ RENUMERAR SEMANAS DESDE 1 ═══
                if (isset($processedDetailedAsistencias[$docente->id]['sessions']) && 
                    count($processedDetailedAsistencias[$docente->id]['sessions']) > 0) {
                    
                    // Ordenar sesiones por fecha
                    usort($processedDetailedAsistencias[$docente->id]['sessions'], function($a, $b) {
                        return strcmp($a['fecha'], $b['fecha']);
                    });
                    
                    // Obtener la primera fecha
                    $primeraFecha = Carbon::parse($processedDetailedAsistencias[$docente->id]['sessions'][0]['fecha']);
                    
                    // Renumerar semanas desde 1
                    foreach ($processedDetailedAsistencias[$docente->id]['sessions'] as &$session) {
                        $fechaSesion = Carbon::parse($session['fecha']);
                        // Calcular semana relativa (diferencia en semanas desde la primera fecha + 1)
                        $semanaRelativa = $primeraFecha->diffInWeeks($fechaSesion) + 1;
                        $session['semana'] = $semanaRelativa;
                    }
                    unset($session); // Liberar referencia
                }
                
                // DEBUG: Log para ver los totales del export
                \Log::info("EXPORT - Docente {$docente->nombre}: " . count($processedDetailedAsistencias[$docente->id]['sessions']) . " sesiones, Total horas: " . $processedDetailedAsistencias[$docente->id]['total_horas']);
            }
        }
        
        // DEBUG: Log del total general del export
        $totalGeneralExport = collect($processedDetailedAsistencias)->sum('total_horas');
        \Log::info("TOTAL GENERAL EXPORT: {$totalGeneralExport} horas");

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
            // ⚡ FILTRO: Solo crear hoja si el docente tiene sesiones
            if (empty($docenteData['sessions']) || count($docenteData['sessions']) === 0) {
                continue; // Saltar docentes sin sesiones
            }
            
            $docente = $docenteData['docente_info'];
            $docenteName = trim(($docente->nombre ?? '') . ' ' . ($docente->apellido_paterno ?? '') . ' ' . ($docente->apellido_materno ?? ''));
            
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

                // PALETA FORMAL Y PROFESIONAL
                private const COLORS = [
                    'PRIMARY_BLUE'      => 'FF1B3B6F',    // Azul marino corporativo
                    'SECONDARY_BLUE'    => 'FF2C5282',    // Azul oscuro
                    'ACCENT_GOLD'       => 'FFC19A6B',    // Dorado elegante
                    'LIGHT_BLUE'        => 'FFF8F9FA',    // Gris muy claro
                    'HEADER_BLUE'       => 'FF1E3A5F',    // Azul encabezado
                    'WHITE'             => 'FFFFFFFF',    // Blanco
                    'LIGHT_GRAY'        => 'FFF1F3F5',    // Gris claro
                    'BORDER_GRAY'       => 'FFDEE2E6',    // Gris borde
                    'TEXT_DARK'         => 'FF2D3748',    // Texto oscuro
                    'SUCCESS_GREEN'     => 'FF2F855A',    // Verde formal
                    'WARNING_ORANGE'    => 'FFED8936'     // Naranja formal
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
                    // ENCABEZADO INSTITUCIONAL PROFESIONAL
                    // ═══════════════════════════════════════════════════════════
                    
                    // Fila 1: Universidad
                    $dataRows->push([
                        'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS',
                        '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    // Fila 2: Centro Pre Universitario
                    $dataRows->push([
                        'CENTRO PRE UNIVERSITARIO - CEPRE-UNAMAD',
                        '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    // Fila 3: Tipo de reporte
                    $dataRows->push([
                        'REPORTE DE ASISTENCIA DOCENTE',
                        '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    // Fila 4: Docente
                    $dataRows->push([
                        'DOCENTE: ' . $this->docenteName,
                        '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    // Fila 5: Período
                    $dataRows->push([
                        'PERÍODO: ' . $this->filterPeriodHeader,
                        '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);

                    // Fila 6: Separador
                    $dataRows->push(['', '', '', '', '', '', '', '', '', '', '', '', '']);

                    // ═══════════════════════════════════════════════════════════
                    // ENCABEZADOS DE COLUMNAS
                    // ═══════════════════════════════════════════════════════════
                    
                    $dataRows->push([
                        'MES', 
                        'SEMANA', 
                        'FECHA', 
                        'CURSO', 
                        'TEMA DESARROLLADO', 
                        'AULA', 
                        'TURNO', 
                        'ENTRADA', 
                        'SALIDA', 
                        'HORAS DICTADAS', 
                        'TARDANZA',
                        'PAGO',
                        ''
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
                                // Calcular tardanza en formato MM:SS
                                // La tardanza ya viene calculada respetando la tolerancia de 5 minutos
                                $minutosTardanza = $session['minutos_tardanza'] ?? 0;
                                $minutosEnteros = floor($minutosTardanza);
                                $segundos = round(($minutosTardanza - $minutosEnteros) * 60);
                                // Mostrar 00:00 si no hay tardanza, o MM:SS si hay tardanza
                                $tardanzaFormateada = sprintf('%02d:%02d', $minutosEnteros, $segundos);
                                
                                // Convertir horas decimales a formato HH:MM:SS
                                $horasDictadas = $session['horas_dictadas'];
                                $horas = floor($horasDictadas);
                                $minutosDecimales = ($horasDictadas - $horas) * 60;
                                $minutos = floor($minutosDecimales);
                                $segundos = round(($minutosDecimales - $minutos) * 60);
                                $horasFormateadas = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
                                
                                $dataRows->push([
                                    $isFirstRowForMes ? strtoupper($monthData['month_name']) : '',
                                    $isFirstRowForSemana ? 'SEMANA ' . sprintf('%02d', $semana) : '',
                                    Carbon::parse($session['fecha'])->format('d/m/Y'),
                                    $session['curso'],
                                    $session['tema_desarrollado'],
                                    $session['aula'],
                                    $session['turno'],
                                    $session['hora_entrada'],
                                    $session['hora_salida'],
                                    $horasFormateadas,
                                    $tardanzaFormateada,
                                    'S/. ' . number_format($session['pago'], 2),
                                    ''
                                ]);
                                
                                $docenteTotalHoras += $session['horas_dictadas'];
                                $docenteTotalPago += $session['pago'];
                                
                                $isFirstRowForDocente = false;
                                $isFirstRowForSemana = false;
                                $isFirstRowForMes = false;
                            }
                        }
                    }

                    // ═══════════════════════════════════════════════════════════
                    // SECCIÓN 4: FILA DE TOTALES PROFESIONAL
                    // ═══════════════════════════════════════════════════════════
                    
                    // Convertir total de horas a formato HH:MM:SS
                    $totalHoras = floor($docenteTotalHoras);
                    $minutosDecimales = ($docenteTotalHoras - $totalHoras) * 60;
                    $totalMinutos = floor($minutosDecimales);
                    $totalSegundos = round(($minutosDecimales - $totalMinutos) * 60);
                    $totalHorasFormateado = sprintf('%02d:%02d:%02d', $totalHoras, $totalMinutos, $totalSegundos);
                    
                    $dataRows->push([
                        '', '', '', '', '', '', '', '', 
                        'TOTAL GENERAL',
                        $totalHorasFormateado,
                        '',
                        'S/. ' . number_format($docenteTotalPago, 2),
                        ''
                    ]);
                    
                    // Fila con monto redondeado (sin decimales)
                    $dataRows->push([
                        '', '', '', '', '', '', '', '', 
                        'MONTO REDONDEADO',
                        '',
                        '',
                        'S/. ' . number_format(round($docenteTotalPago), 0),
                        ''
                    ]);

                    // ═══════════════════════════════════════════════════════════
                    // SECCIÓN 5: PIE DE PÁGINA CON FIRMA
                    // ═══════════════════════════════════════════════════════════
                    
                    // Filas vacías de separación (más espacio para firma)
                    $dataRows->push(['', '', '', '', '', '', '', '', '', '', '', '', '']);
                    $dataRows->push(['', '', '', '', '', '', '', '', '', '', '', '', '']);
                    $dataRows->push(['', '', '', '', '', '', '', '', '', '', '', '', '']);
                    $dataRows->push(['', '', '', '', '', '', '', '', '', '', '', '', '']);
                    
                    // Fila con líneas de firma (dos líneas paralelas)
                    $dataRows->push([
                        '_____________________', '', '', '', '', '',
                        '__________________________________',
                        '', '', '', '', '', ''
                    ]);
                    
                    // Fila con Vº Bº y nombre del responsable (al mismo nivel)
                    $dataRows->push([
                        'Vº Bº', '', '', '', '', '',
                        'Ing. Roy Kevin Bonifacio Fernández',
                        '', '', '', '', '', ''
                    ]);
                    
                    // Fila con descripción del servicio (centrada debajo del nombre)
                    $dataRows->push([
                        '', '', '', '', '', '',
                        'SERVICIO ESPECIALIZADO EN GESTIÓN DE SERVICIOS INFORMÁTICOS DEL CEPRE UNAMAD',
                        '', '', '', '', '', ''
                    ]);

                    return $dataRows;
                }

                public function headings(): array { return []; }
                public function map($row): array { return $row; }

                public function styles(Worksheet $sheet)
                {
                    return [
                        // ═══ ESTILOS ELEGANTES PARA ENCABEZADOS ═══
                        1 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 25, 
                                'name' => 'Calibri',
                                'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                            ]
                        ],
                        2 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 22, 
                                'name' => 'Calibri',
                                'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                            ]
                        ],
                        3 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 22, 
                                'name' => 'Calibri',
                                'color' => ['argb' => self::COLORS['TEXT_DARK']]
                            ]
                        ],
                        4 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 20, 
                                'name' => 'Calibri',
                                'color' => ['argb' => self::COLORS['TEXT_DARK']]
                            ]
                        ],
                        5 => [
                            'font' => [
                                'bold' => false, 
                                'size' => 19, 
                                'name' => 'Calibri',
                                'color' => ['argb' => self::COLORS['TEXT_DARK']]
                            ]
                        ],
                        // ═══ ESTILO PARA ENCABEZADOS DE TABLA ═══
                        7 => [
                            'font' => [
                                'bold' => true, 
                                'size' => 10,
                                'name' => 'Calibri',
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
                    // ═══════════════════════════════════════════════════
                    // AGREGAR LOGOS INSTITUCIONALES COMO OVERLAY
                    // ═══════════════════════════════════════════════════
                    
                    // Logo UNAMAD (izquierda) - Flotando sobre el encabezado
                    $logoUnamad = public_path('assets/images/logo unamad constancia.png');
                    if (file_exists($logoUnamad)) {
                        $drawingUnamad = new Drawing();
                        $drawingUnamad->setName('Logo UNAMAD');
                        $drawingUnamad->setDescription('Logo UNAMAD');
                        $drawingUnamad->setPath($logoUnamad);
                        $drawingUnamad->setHeight(120);
                        $drawingUnamad->setCoordinates('A1');
                        $drawingUnamad->setOffsetX(10);
                        $drawingUnamad->setOffsetY(5);  // Offset mínimo para que flote sobre el texto
                        $drawingUnamad->setWorksheet($sheet);
                    }
                    
                    // Logo CEPRE (derecha) - Flotando sobre el encabezado
                    $logoCepre = public_path('assets/images/logo cepre costancia.png');
                    if (file_exists($logoCepre)) {
                        $drawingCepre = new Drawing();
                        $drawingCepre->setName('Logo CEPRE');
                        $drawingCepre->setDescription('Logo CEPRE');
                        $drawingCepre->setPath($logoCepre);
                        $drawingCepre->setHeight(120);
                        $drawingCepre->setCoordinates('K1');
                        $drawingCepre->setOffsetX(10);
                        $drawingCepre->setOffsetY(5);  // Offset mínimo para que flote sobre el texto
                        $drawingCepre->setWorksheet($sheet);
                    }
                    
                    // Fusionar celdas para encabezados
                    $sheet->mergeCells('A1:L1');  // Universidad
                    $sheet->mergeCells('A2:L2');  // Centro Pre
                    $sheet->mergeCells('A3:L3');  // Informe
                    $sheet->mergeCells('A4:L4');  // Docente
                    $sheet->mergeCells('A5:L5');  // Período

                    // Fondo con borde elegante para toda la cabecera
                    $sheet->getStyle('A1:L5')->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF8F9FA']
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                            ]
                        ]
                    ]);

                    // Altura especial para encabezados (sin fila dedicada a logos)
                    $sheet->getRowDimension(1)->setRowHeight(28);  // Universidad
                    $sheet->getRowDimension(2)->setRowHeight(28);  // Centro Pre
                    $sheet->getRowDimension(3)->setRowHeight(25);  // Informe
                    $sheet->getRowDimension(4)->setRowHeight(25);  // Docente
                    $sheet->getRowDimension(5)->setRowHeight(25);  // Período
                    
                    // Línea separadora elegante con doble borde
                    $sheet->getStyle('A6:L6')->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_DOUBLE,
                                'color' => ['argb' => self::COLORS['PRIMARY_BLUE']]
                            ]
                        ]
                    ]);
                }

                private function setupDataTable($sheet)
                {
                    $lastRow = $sheet->getHighestRow();
                    
                    // ═══ ENCABEZADOS DE TABLA FORMALES ═══
                    $sheet->getStyle('A7:L7')->applyFromArray([
                        'font' => [
                            'bold' => true, 
                            'size' => 10,
                            'name' => 'Calibri',
                            'color' => ['argb' => self::COLORS['WHITE']]
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => self::COLORS['HEADER_BLUE']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => self::COLORS['BORDER_GRAY']]
                            ]
                        ]
                    ]);

                    // Altura especial para encabezados de tabla
                    $sheet->getRowDimension(7)->setRowHeight(35);

                    // ═══ CONFIGURAR ANCHOS DE COLUMNA OPTIMIZADOS ═══
                    $columnWidths = [
                        'A' => 16,  // MES - Optimizado
                        'B' => 14,  // SEMANA - Optimizado
                        'C' => 12,  // FECHA - Compacto
                        'D' => 20,  // CURSO - Más espacio
                        'E' => 45,  // TEMA DESARROLLADO - Máximo espacio
                        'F' => 8,   // AULA - Compacto
                        'G' => 12,  // TURNO - Optimizado
                        'H' => 14,  // HORA ENTRADA - Con segundos
                        'I' => 14,  // HORA SALIDA - Con segundos
                        'J' => 12,  // HORAS DICTADAS - Optimizado
                        'K' => 12,  // TARDANZA - Formato MM:SS
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
                    
                    // $lastRow ahora incluye las filas de pie de página (8 filas: 1 TOTAL + 1 MONTO REDONDEADO + 4 empty + 3 footer)
                    // TOTAL GENERAL: $lastRow - 8
                    // MONTO REDONDEADO: $lastRow - 7
                    // Separadores: $lastRow - 6, -5, -4, -3
                    // Línea firma: $lastRow - 2
                    // Nombre: $lastRow - 1
                    // Servicio: $lastRow
                    $totalsRow = $lastRow - 8;
                    
                    for ($row = 8; $row < $totalsRow; $row++) {
                        $fillColor = ($row % 2 == 0) ? self::COLORS['WHITE'] : self::COLORS['LIGHT_GRAY'];
                        
                        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => $fillColor]
                            ]
                        ]);
                    }
                    
                    // ═══════════════════════════════════════════════════
                    // FILA DE TOTALES FORMAL
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('A' . $totalsRow . ':L' . $totalsRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                            'name' => 'Calibri',
                            'color' => ['argb' => self::COLORS['TEXT_DARK']]
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => self::COLORS['LIGHT_GRAY']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => Border::BORDER_DOUBLE,
                                'color' => ['argb' => self::COLORS['TEXT_DARK']]
                            ],
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => self::COLORS['BORDER_GRAY']]
                            ]
                        ]
                    ]);

                    // Altura especial para fila de totales
                    $sheet->getRowDimension($totalsRow)->setRowHeight(30);
                    
                    // ═══════════════════════════════════════════════════
                    // FILA DE MONTO REDONDEADO
                    // ═══════════════════════════════════════════════════
                    
                    $roundedRow = $totalsRow + 1;
                    $sheet->getStyle('A' . $roundedRow . ':L' . $roundedRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 10,
                            'name' => 'Calibri',
                            'color' => ['argb' => self::COLORS['SUCCESS_GREEN']]
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => self::COLORS['WHITE']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => self::COLORS['BORDER_GRAY']]
                            ]
                        ]
                    ]);

                    // Altura especial para fila de monto redondeado
                    $sheet->getRowDimension($roundedRow)->setRowHeight(25);

                    // ═══════════════════════════════════════════════════
                    // PIE DE PÁGINA CON FIRMA
                    // ═══════════════════════════════════════════════════
                    
                    // Fusionar celdas para la línea de firma izquierda (A a E)
                    $sheet->mergeCells('A' . ($lastRow - 2) . ':E' . ($lastRow - 2));
                    
                    // Fusionar celdas para la línea de firma derecha (G a L)
                    $sheet->mergeCells('G' . ($lastRow - 2) . ':L' . ($lastRow - 2));
                    
                    // Fusionar celdas para "Vº Bº" (A a E)
                    $sheet->mergeCells('A' . ($lastRow - 1) . ':E' . ($lastRow - 1));
                    
                    // Fusionar celdas para el nombre del responsable (G a L)
                    $sheet->mergeCells('G' . ($lastRow - 1) . ':L' . ($lastRow - 1));
                    
                    // Fusionar celdas para la descripción del servicio (G a L)
                    $sheet->mergeCells('G' . $lastRow . ':L' . $lastRow);
                    
                    // Estilo para la línea de firma izquierda (Vº Bº)
                    $sheet->getStyle('A' . ($lastRow - 2))->applyFromArray([
                        'font' => [
                            'bold' => false,
                            'size' => 11,
                            'name' => 'Calibri',
                            'color' => ['argb' => self::COLORS['TEXT_DARK']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_BOTTOM
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_NONE
                            ]
                        ]
                    ]);
                    
                    // Estilo para la línea de firma derecha (Ingeniero)
                    $sheet->getStyle('G' . ($lastRow - 2))->applyFromArray([
                        'font' => [
                            'bold' => false,
                            'size' => 11,
                            'name' => 'Calibri',
                            'color' => ['argb' => self::COLORS['TEXT_DARK']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_BOTTOM
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_NONE
                            ]
                        ]
                    ]);
                    
                    // Estilo para "Vº Bº" (centrado en su área)
                    $sheet->getStyle('A' . ($lastRow - 1))->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                            'name' => 'Calibri',
                            'color' => ['argb' => self::COLORS['TEXT_DARK']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_NONE
                            ]
                        ]
                    ]);
                    
                    // Estilo para el nombre del responsable (centrado)
                    $sheet->getStyle('G' . ($lastRow - 1))->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                            'name' => 'Calibri',
                            'color' => ['argb' => self::COLORS['TEXT_DARK']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_NONE
                            ]
                        ]
                    ]);
                    
                    // Estilo para la descripción del servicio (centrado)
                    $sheet->getStyle('G' . $lastRow)->applyFromArray([
                        'font' => [
                            'bold' => false,
                            'size' => 9,
                            'name' => 'Calibri',
                            'color' => ['argb' => self::COLORS['TEXT_DARK']]
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_NONE
                            ]
                        ]
                    ]);
                    
                    // IMPORTANTE: Quitar todos los bordes de las filas de pie de página
                    // Esto evita que salgan cuadrados en la impresión
                    for ($footerRow = $lastRow - 7; $footerRow <= $lastRow; $footerRow++) {
                        $sheet->getStyle('A' . $footerRow . ':L' . $footerRow)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_NONE
                                ]
                            ]
                        ]);
                    }
                    
                    // Altura para las filas de pie de página
                    $sheet->getRowDimension($lastRow - 2)->setRowHeight(30); // Líneas de firma
                    $sheet->getRowDimension($lastRow - 1)->setRowHeight(20); // Vº Bº y Nombre
                    $sheet->getRowDimension($lastRow)->setRowHeight(18);     // Servicio

                    // ═══════════════════════════════════════════════════
                    // ALINEACIONES ESPECÍFICAS POR COLUMNA
                    // ═══════════════════════════════════════════════════
                    
                    // Fechas centradas
                    $sheet->getStyle('C8:C' . ($totalsRow-1))->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Aula y turno centrados
                    $sheet->getStyle('F8:G' . ($totalsRow-1))->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Horarios, tardanza y pagos centrados
                    $sheet->getStyle('H8:L' . ($totalsRow-1))->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Tema desarrollado con wrap text
                    $sheet->getStyle('E8:E' . ($totalsRow-1))->getAlignment()
                        ->setWrapText(true);

                    // ═══════════════════════════════════════════════════
                    // FORMATO ESPECIAL PARA COLUMNAS AGRUPADAS
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('A8:B' . ($totalsRow-1))->applyFromArray([
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
                    // Excluir filas de totales y pie de página (9 filas: 1 TOTAL GENERAL + 1 MONTO REDONDEADO + 7 footer)
                    $finalRow = $lastRow - 9;
                    
                    // ═══ FUSIONAR CELDAS DE MES ═══
                    $startRowMes = null;
                    $lastMesValue = '';
                    
                    for ($row = 8; $row <= $finalRow; $row++) {
                        $mes = trim($sheet->getCell('A' . $row)->getValue());
                        
                        // Si encontramos un valor no vacío
                        if ($mes !== '') {
                            // Si hay un grupo anterior, fusionarlo
                            if ($startRowMes !== null && $startRowMes < $row - 1) {
                                $sheet->mergeCells('A' . $startRowMes . ':A' . ($row - 1));
                                $sheet->getStyle('A' . $startRowMes)->getAlignment()
                                    ->setVertical(Alignment::VERTICAL_CENTER);
                            }
                            // Iniciar nuevo grupo
                            $startRowMes = $row;
                            $lastMesValue = $mes;
                        }
                    }
                    
                    // Fusionar el último grupo de mes
                    if ($startRowMes !== null && $startRowMes < $finalRow) {
                        $sheet->mergeCells('A' . $startRowMes . ':A' . $finalRow);
                        $sheet->getStyle('A' . $startRowMes)->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }
                    
                    // ═══ FUSIONAR CELDAS DE SEMANA ═══
                    $startRowSemana = null;
                    $lastSemanaValue = '';
                    
                    for ($row = 8; $row <= $finalRow; $row++) {
                        $semana = trim($sheet->getCell('B' . $row)->getValue());
                        
                        // Si encontramos un valor no vacío
                        if ($semana !== '') {
                            // Si hay un grupo anterior, fusionarlo
                            if ($startRowSemana !== null && $startRowSemana < $row - 1) {
                                $sheet->mergeCells('B' . $startRowSemana . ':B' . ($row - 1));
                                $sheet->getStyle('B' . $startRowSemana)->getAlignment()
                                    ->setVertical(Alignment::VERTICAL_CENTER);
                            }
                            // Iniciar nuevo grupo
                            $startRowSemana = $row;
                            $lastSemanaValue = $semana;
                        }
                    }
                    
                    // Fusionar el último grupo de semana
                    if ($startRowSemana !== null && $startRowSemana < $finalRow) {
                        $sheet->mergeCells('B' . $startRowSemana . ':B' . $finalRow);
                        $sheet->getStyle('B' . $startRowSemana)->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }
                }

                private function applyVisualEffects($sheet, $lastRow)
                {
                    // Calcular fila de totales (excluyendo MONTO REDONDEADO + 7 filas de pie de página)
                    $totalsRow = $lastRow - 8;
                    
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
                    
                    $sheet->getStyle('L8:L' . $totalsRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => self::COLORS['SUCCESS_GREEN']]
                        ]
                    ]);

                    // ═══════════════════════════════════════════════════
                    // FORMATO ESPECIAL PARA HORAS
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('J8:J' . $totalsRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => self::COLORS['HEADER_BLUE']]
                        ]
                    ]);

                    // ═══════════════════════════════════════════════════
                    // APLICAR FORMATO DE FECHA CONSISTENTE
                    // ═══════════════════════════════════════════════════
                    
                    $sheet->getStyle('C8:C' . ($totalsRow-1))->applyFromArray([
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

                    // Repetir encabezados en cada página (incluye logos y encabezados)
                    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);

                    // ═══════════════════════════════════════════════════
                    // AÑADIR LÍNEAS DE DIVISIÓN ELEGANTES
                    // ═══════════════════════════════════════════════════
                    
                    // Línea divisoria antes de totales
                    $sheet->getStyle('A' . ($totalsRow-1) . ':L' . ($totalsRow-1))->applyFromArray([
                        'borders' => [
                            'bottom' => [
                                'borderStyle' => Border::BORDER_DOUBLE,
                                'color' => ['argb' => self::COLORS['HEADER_BLUE']]
                            ]
                        ]
                    ]);
                }
            };
        }

        // 🚨 CRÍTICO: Si no hay hojas, PhpSpreadsheet lanza error "out of bounds index: 0"
        if (empty($sheets)) {
            $sheets[] = new class($rangoFechasHeader) implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize {
                private $period;
                public function __construct($period) { $this->period = $period; }
                public function title(): string { return 'Sin Datos'; }
                public function headings(): array { return ['REPORTE DE ASISTENCIA - SIN REGISTROS']; }
                public function collection() {
                    return collect([
                        ['No se encontraron registros de asistencia para el período seleccionado.'],
                        ['Período: ' . $this->period],
                        ['Por favor, verifique los filtros seleccionados (Docente, Mes, Año, Ciclo).']
                    ]);
                }
            };
        }

        return $sheets;
    }
}