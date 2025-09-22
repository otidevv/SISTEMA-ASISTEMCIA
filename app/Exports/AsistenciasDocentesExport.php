<?php

namespace App\Exports;

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

/**
 * Esta clase genera un INFORME DE AVANCE ACADÉMICO profesional
 * con todas las sesiones del ciclo completo ordenadas cronológicamente
 */
class AsistenciasDocentesExport implements WithMultipleSheets 
{
    private $processedData; 
    private $selectedDocenteId;
    private $selectedMonth;
    private $selectedYear;
    private $fechaInicio; 
    private $fechaFin;     
    private $selectedCicloAcademico; 

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

        // PRIORIDAD MÁXIMA: Si hay ciclo académico seleccionado, usar SUS fechas
        if ($this->selectedCicloAcademico) {
            $ciclo = Ciclo::where('codigo', $this->selectedCicloAcademico)->first();
            if ($ciclo) {
                $cicloStartDate = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
                $cicloEndDate = Carbon::parse($ciclo->fecha_fin)->endOfDay();
                
                // Si NO hay filtros adicionales, usar TODO el ciclo académico
                if (!$this->fechaInicio && !$this->fechaFin && !$this->selectedMonth && !$this->selectedYear) {
                    $startDate = $cicloStartDate;
                    $endDate = $cicloEndDate;
                }
                // Si hay fechas específicas, validar que estén dentro del ciclo
                elseif ($this->fechaInicio && $this->fechaFin) {
                    $customStart = Carbon::parse($this->fechaInicio)->startOfDay();
                    $customEnd = Carbon::parse($this->fechaFin)->endOfDay();
                    
                    $startDate = $customStart->max($cicloStartDate);
                    $endDate = $customEnd->min($cicloEndDate);
                }
                // Si hay mes/año específico, validar que esté dentro del ciclo
                elseif ($this->selectedMonth && $this->selectedYear) {
                    $monthStart = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->startOfDay();
                    $monthEnd = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->endOfMonth()->endOfDay();
                    
                    $startDate = $monthStart->max($cicloStartDate);
                    $endDate = $monthEnd->min($cicloEndDate);
                }
                else {
                    // Usar todo el ciclo académico como fallback
                    $startDate = $cicloStartDate;
                    $endDate = $cicloEndDate;
                }
            }
        }
        // Si NO hay ciclo académico pero hay fechas específicas
        elseif ($this->fechaInicio && $this->fechaFin) {
            $startDate = Carbon::parse($this->fechaInicio)->startOfDay();
            $endDate = Carbon::parse($this->fechaFin)->endOfDay();
        }
        // Si NO hay ciclo académico pero hay mes/año específico
        elseif ($this->selectedMonth && $this->selectedYear) {
            $startDate = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->startOfDay();
            $endDate = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->endOfMonth()->endOfDay();
        }
        // Fallback final: últimos 30 días
        else {
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
                    if (!$horario || !$horario->hora_inicio || !$horario->hora_fin) {
                        continue;
                    }

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

        return $processedDetailedAsistencias;
    }

    private function processSession($horario, $currentDate, $registrosBiometricosDelDia, $docente)
    {
        $horaInicioProgramada = Carbon::parse($horario->hora_inicio);
        $horaFinProgramada = Carbon::parse($horario->hora_fin);

        $horarioInicioHoy = $currentDate->copy()->setTime($horaInicioProgramada->hour, $horaInicioProgramada->minute, $horaInicioProgramada->second);
        $horarioFinHoy = $currentDate->copy()->setTime($horaFinProgramada->hour, $horaFinProgramada->minute, $horaFinProgramada->second); 

        // Buscar registros biométricos
        $entradaBiometrica = $registrosBiometricosDelDia
            ->filter(function($r) use ($horarioInicioHoy) {
                $horaRegistro = Carbon::parse($r->fecha_registro); 
                return $horaRegistro->between(
                    $horarioInicioHoy->copy()->subMinutes(15),
                    $horarioInicioHoy->copy()->addMinutes(30)
                );
            })
            ->sortBy('fecha_registro')
            ->first();

        $salidaBiometrica = $registrosBiometricosDelDia
            ->filter(function($r) use ($horarioFinHoy) {
                $horaRegistro = Carbon::parse($r->fecha_registro); 
                return $horaRegistro->between(
                    $horarioFinHoy->copy()->subMinutes(15),
                    $horarioFinHoy->copy()->addMinutes(60)
                );
            })
            ->sortByDesc('fecha_registro')
            ->first();
        
        // Buscar tema desarrollado
        $asistenciaDocenteProcesada = AsistenciaDocente::where('docente_id', $docente->id)
            ->where('horario_id', $horario->id)
            ->whereDate('fecha_hora', $currentDate->toDateString())
            ->first();

        $temaDesarrollado = $asistenciaDocenteProcesada->tema_desarrollado ?? 'Pendiente';
        
        // CALCULAR SIEMPRE LAS HORAS PROGRAMADAS
        $horasProgramadas = $horaInicioProgramada->diffInHours($horaFinProgramada, true);
        $horasDictadas = $horasProgramadas; // Por defecto, asumir horas programadas
        $estadoTexto = 'PENDIENTE';

        $cursoNombre = $horario->curso->nombre ?? 'N/A';
        $aulaNombre = $horario->aula->nombre ?? 'N/A';
        $turnoNombre = $horario->turno ?? 'N/A';

        // Determinar estado basado en registros biométricos
        if ($entradaBiometrica && $salidaBiometrica) {
            $estadoTexto = 'COMPLETADA';
            
            // Lógica mejorada para el cálculo de horas dictadas
            $horaEntradaMarcada = Carbon::parse($entradaBiometrica->fecha_registro);
            $horaSalidaMarcada = Carbon::parse($salidaBiometrica->fecha_registro);

            // La hora de inicio efectiva es la más tardía entre la programada y la marcada.
            $inicioEfectivo = $horaEntradaMarcada->greaterThan($horarioInicioHoy) ? $horaEntradaMarcada : $horarioInicioHoy;

            // La hora de fin efectiva es la más temprana entre la programada y la marcada.
            $finEfectivo = $horaSalidaMarcada->lessThan($horarioFinHoy) ? $horaSalidaMarcada : $horarioFinHoy;

            // Calcular la diferencia en minutos, asegurándose de que no sea negativa.
            if ($finEfectivo->greaterThan($inicioEfectivo)) {
                $minutosDictados = $finEfectivo->diffInMinutes($inicioEfectivo);
            } else {
                $minutosDictados = 0;
            }
            $horasDictadas = round($minutosDictados / 60, 2);

        } elseif ($entradaBiometrica && !$salidaBiometrica) {
            if ($currentDate->lessThan(Carbon::today()) || ($currentDate->isToday() && Carbon::now()->greaterThan($horarioFinHoy))) {
                $estadoTexto = 'INCOMPLETA';
            } else {
                $estadoTexto = 'EN CURSO';
            }
        } elseif (!$entradaBiometrica && !$salidaBiometrica) {
            if ($currentDate->lessThan(Carbon::today()) || ($currentDate->isToday() && Carbon::now()->greaterThan($horarioFinHoy))) {
                $estadoTexto = 'FALTA';
            } else {
                $estadoTexto = 'PROGRAMADA';
            }
        }

        // CALCULAR PAGO SIEMPRE (basado en horas programadas o reales)
        $montoTotal = 0;
        $pagoDocente = PagoDocente::where('docente_id', $docente->id)
            ->whereDate('fecha_inicio', '<=', $currentDate)
            ->whereDate('fecha_fin', '>=', $currentDate)
            ->first();
        
        if ($pagoDocente) {
            $montoTotal = $horasDictadas * $pagoDocente->tarifa_por_hora;
        }

        // CORREGIR EL FORMATO DE HORAS - PROBLEMA SOLUCIONADO
        $horaEntradaDisplay = $entradaBiometrica ? 
            Carbon::parse($entradaBiometrica->fecha_registro)->format('g:i A') : 
            $horaInicioProgramada->format('g:i A');
        
        $horaSalidaDisplay = $salidaBiometrica ? 
            Carbon::parse($salidaBiometrica->fecha_registro)->format('g:i A') : 
            $horaFinProgramada->format('g:i A');

        return [
            'fecha' => $currentDate->toDateString(),
            'curso' => $cursoNombre,
            'tema_desarrollado' => $temaDesarrollado,
            'aula' => $aulaNombre,
            'turno' => $turnoNombre,
            'hora_entrada' => $horaEntradaDisplay,
            'hora_salida' => $horaSalidaDisplay,
            'horas_dictadas' => $horasDictadas,
            'pago' => $montoTotal,
            'estado_sesion' => $estadoTexto,
            'mes' => $currentDate->locale('es')->monthName,
            'semana' => $currentDate->weekOfYear,
            'carbon_date' => $currentDate->copy(),
            'tiene_registros' => ($entradaBiometrica && $salidaBiometrica) ? 'SI' : 'NO',
            // NUEVOS CAMPOS PARA ORDENAMIENTO CRONOLÓGICO
            'year' => $currentDate->year,
            'month_number' => $currentDate->month, // 1=enero, 2=febrero, etc.
            'day_number' => $currentDate->day
        ];
    }

    /**
     * Método para generar encabezado dinámico basado en los filtros aplicados
     */
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

    /**
     * Define las hojas individuales del Excel.
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // GENERAR ENCABEZADO DINÁMICO CORREGIDO
        $rangoFechasHeader = $this->generateDynamicHeader();

        foreach ($this->processedData as $docenteId => $docenteData) {
            $docente = $docenteData['docente_info'];
            $docenteName = 'Lic. ' . $docente->nombre . ' ' . $docente->apellido_paterno . ' ' . $docente->apellido_materno;
            
            // Crear hoja para cada docente con diseño profesional
            $sheets[] = new class($docenteData, $docenteName, $rangoFechasHeader, $this->selectedCicloAcademico) implements 
                \Maatwebsite\Excel\Concerns\FromCollection, 
                \Maatwebsite\Excel\Concerns\WithTitle, 
                \Maatwebsite\Excel\Concerns\WithHeadings, 
                \Maatwebsite\Excel\Concerns\WithMapping, 
                \Maatwebsite\Excel\Concerns\ShouldAutoSize, 
                \Maatwebsite\Excel\Concerns\WithEvents,
                \Maatwebsite\Excel\Concerns\WithStyles
            {
                private $docenteData;
                private $docenteName;
                private $filterPeriodHeader;
                private $selectedCicloAcademico;
                private $currentRow = 1;

                public function __construct(array $docenteData, string $docenteName, string $filterPeriodHeader, ?string $selectedCicloAcademico)
                {
                    $this->docenteData = $docenteData;
                    $this->docenteName = $docenteName;
                    $this->filterPeriodHeader = $filterPeriodHeader;
                    $this->selectedCicloAcademico = $selectedCicloAcademico;
                }

                public function title(): string
                {
                    $title = substr(\preg_replace('/[\\\\\/:\*\?\[\]]/', '', $this->docenteName), 0, 31);
                    return $title ?: 'Docente';
                }

                public function collection()
                {
                    $dataRows = new \Illuminate\Support\Collection();

                    // ENCABEZADO INSTITUCIONAL
                    $dataRows->push([
                        'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS',
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    $dataRows->push([
                        'CENTRO PRE UNIVERSITARIO',
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    $dataRows->push([
                        'CICLO ORDINARIO 2025-1',
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    $dataRows->push([
                        'INFORME DE AVANCE ACADÉMICO',
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    
                    $dataRows->push([
                        $this->filterPeriodHeader, // AQUÍ SE USA EL HEADER DINÁMICO
                        '', '', '', '', '', '', '', '', '', '', ''
                    ]);

                    // Línea en blanco
                    $dataRows->push(['', '', '', '', '', '', '', '', '', '', '', '']);

                    // ENCABEZADOS DE TABLA
                    $dataRows->push([
                        'DOCENTE', 'MES', 'SEMANA', 'FECHA', 'CURSO', 'TEMA DESARROLLADO', 
                        'AULA', 'TURNO', 'HORA ENTRADA', 'HORA SALIDA', 'HORAS DICTADAS', 'PAGO'
                    ]);

                    // *** ORDENAMIENTO CRONOLÓGICO CORREGIDO ***
                    // Primero ordenar todas las sesiones por fecha cronológica
                    $sortedSessions = collect($this->docenteData['sessions'])
                        ->sortBy([
                            ['year', 'asc'],
                            ['month_number', 'asc'],
                            ['day_number', 'asc']
                        ]);

                    // Agrupar sesiones por mes cronológicamente ordenado
                    $sessionsByMonth = [];
                    foreach ($sortedSessions as $session) {
                        $monthKey = $session['year'] . '-' . sprintf('%02d', $session['month_number']); // 2025-01, 2025-02, etc.
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

                    // Los meses ya están ordenados cronológicamente por el key
                    ksort($sessionsByMonth);

                    // DATOS DE SESIONES
                    $docenteTotalHoras = 0;
                    $docenteTotalPago = 0;
                    $isFirstRowForDocente = true;

                    foreach ($sessionsByMonth as $monthKey => $monthData) {
                        $isFirstRowForMes = true;
                        
                        // Ordenar semanas dentro del mes
                        ksort($monthData['weeks']);
                        
                        foreach ($monthData['weeks'] as $semana => $sessions) {
                            $isFirstRowForSemana = true;
                            
                            foreach ($sessions as $session) {
                                $dataRows->push([
                                    $isFirstRowForDocente ? $this->docenteName : '',
                                    $isFirstRowForMes ? \strtoupper($monthData['month_name']) : '',
                                    $isFirstRowForSemana ? 'SEMANA ' . \sprintf('%02d', $semana) : '',
                                    \Carbon\Carbon::parse($session['fecha'])->format('d/m/Y'),
                                    $session['curso'],
                                    $session['tema_desarrollado'],
                                    $session['aula'],
                                    $session['turno'],
                                    $session['hora_entrada'],
                                    $session['hora_salida'],
                                    \number_format($session['horas_dictadas'], 2) . ' Horas/Min',
                                    'S/. ' . \number_format($session['pago'], 2)
                                ]);
                                
                                $docenteTotalHoras += $session['horas_dictadas'];
                                $docenteTotalPago += $session['pago'];
                                
                                $isFirstRowForDocente = false;
                                $isFirstRowForSemana = false;
                            }
                            $isFirstRowForMes = false;
                        }
                    }

                    // FILA DE TOTALES
                    $dataRows->push([
                        '', '', '', '', '', '', '', '', '', 'TOTAL',
                        \number_format($docenteTotalHoras, 2) . ' HORAS',
                        'S/. ' . \number_format($docenteTotalPago, 2)
                    ]);

                    return $dataRows;
                }

                public function headings(): array { return []; }
                public function map($row): array { return $row; }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    return [
                        // Estilos base para encabezados
                        1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF1F4E79']]],
                        2 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1F4E79']]],
                        3 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1F4E79']]],
                        4 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1F4E79']]],
                        5 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF1F4E79']]],
                        7 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF000000']]]
                    ];
                }

                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                            $sheet = $event->sheet->getDelegate();
                            
                            // CONFIGURACIÓN DE ENCABEZADOS
                            $sheet->mergeCells('A1:L1');
                            $sheet->mergeCells('A2:L2');
                            $sheet->mergeCells('A3:L3');
                            $sheet->mergeCells('A4:L4');
                            $sheet->mergeCells('A5:L5');

                            // Alineación centrada para encabezados institucionales
                            $sheet->getStyle('A1:L5')->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            
                            // ESTILO DE ENCABEZADOS DE TABLA (Fila 7)
                            $sheet->getStyle('A7:L7')->applyFromArray([
                                'font' => [
                                    'bold' => true, 
                                    'size' => 11,
                                    'color' => ['argb' => 'FFFFFFFF']
                                ],
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 
                                    'startColor' => ['argb' => 'FF366092']
                                ],
                                'alignment' => [
                                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => 'FF000000']
                                    ]
                                ]
                            ]);

                            // Altura de fila para encabezados
                            $sheet->getRowDimension(7)->setRowHeight(25);

                            // APLICAR BORDES A TODA LA TABLA
                            $lastRow = $sheet->getHighestRow();
                            $sheet->getStyle('A7:L' . $lastRow)->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['argb' => 'FF000000']
                                    ]
                                ]
                            ]);

                            // CONFIGURAR ANCHOS DE COLUMNA
                            $sheet->getColumnDimension('A')->setWidth(30); // DOCENTE
                            $sheet->getColumnDimension('B')->setWidth(12); // MES
                            $sheet->getColumnDimension('C')->setWidth(12); // SEMANA
                            $sheet->getColumnDimension('D')->setWidth(12); // FECHA
                            $sheet->getColumnDimension('E')->setWidth(15); // CURSO
                            $sheet->getColumnDimension('F')->setWidth(35); // TEMA DESARROLLADO
                            $sheet->getColumnDimension('G')->setWidth(8);  // AULA
                            $sheet->getColumnDimension('H')->setWidth(10); // TURNO
                            $sheet->getColumnDimension('I')->setWidth(15); // HORA ENTRADA
                            $sheet->getColumnDimension('J')->setWidth(15); // HORA SALIDA
                            $sheet->getColumnDimension('K')->setWidth(18); // HORAS DICTADAS
                            $sheet->getColumnDimension('L')->setWidth(15); // PAGO

                            // FUSIONAR CELDAS AGRUPADAS
                            $this->mergeCellsForGroupedData($sheet);
                            
                            // APLICAR FORMATO CONDICIONAL
                            $this->applyProfessionalFormatting($sheet, $lastRow);
                        }
                    ];
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
                        
                        // Docente grouping
                        if ($docente !== '' && $docente !== $currentGroups['docente']) {
                            if ($currentGroups['docente'] !== '' && $startRows['docente'] < $row - 1) {
                                $sheet->mergeCells('A' . $startRows['docente'] . ':A' . ($row - 1));
                                $sheet->getStyle('A' . $startRows['docente'])->getAlignment()
                                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            }
                            $startRows['docente'] = $row;
                            $currentGroups['docente'] = $docente;
                        }
                        
                        // Mes grouping
                        if ($mes !== '' && $mes !== $currentGroups['mes']) {
                            if ($currentGroups['mes'] !== '' && $startRows['mes'] < $row - 1) {
                                $sheet->mergeCells('B' . $startRows['mes'] . ':B' . ($row - 1));
                                $sheet->getStyle('B' . $startRows['mes'])->getAlignment()
                                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            }
                            $startRows['mes'] = $row;
                            $currentGroups['mes'] = $mes;
                        }
                        
                        // Semana grouping
                        if ($semana !== '' && $semana !== $currentGroups['semana']) {
                            if ($currentGroups['semana'] !== '' && $startRows['semana'] < $row - 1) {
                                $sheet->mergeCells('C' . $startRows['semana'] . ':C' . ($row - 1));
                                $sheet->getStyle('C' . $startRows['semana'])->getAlignment()
                                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            }
                            $startRows['semana'] = $row;
                            $currentGroups['semana'] = $semana;
                        }
                    }
                    
                    // Merge final groups
                    if ($startRows['docente'] < $lastRow) {
                        $sheet->mergeCells('A' . $startRows['docente'] . ':A' . $lastRow);
                        $sheet->getStyle('A' . $startRows['docente'])->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                    if ($startRows['mes'] < $lastRow) {
                        $sheet->mergeCells('B' . $startRows['mes'] . ':B' . $lastRow);
                        $sheet->getStyle('B' . $startRows['mes'])->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                    if ($startRows['semana'] < $lastRow) {
                        $sheet->mergeCells('C' . $startRows['semana'] . ':C' . $lastRow);
                        $sheet->getStyle('C' . $startRows['semana'])->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    }
                }

                private function applyProfessionalFormatting($sheet, $lastRow)
                {
                    // FORMATO ALTERNO PARA FILAS
                    for ($row = 8; $row < $lastRow; $row++) {
                        if (($row - 8) % 2 == 0) {
                            // Filas pares - fondo blanco
                            $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFFFFFFF']
                                ]
                            ]);
                        } else {
                            // Filas impares - fondo gris muy claro
                            $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFF8F9FA']
                                ]
                            ]);
                        }
                    }
                    
                    // FILA DE TOTALES - Formato especial
                    $sheet->getStyle('A' . $lastRow . ':L' . $lastRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['argb' => 'FFFFFFFF']
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF1F4E79']
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ]
                    ]);

                    // ALINEACIÓN PARA COLUMNAS ESPECÍFICAS
                    $sheet->getStyle('D8:D' . ($lastRow-1))->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('G8:H' . ($lastRow-1))->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('I8:L' . ($lastRow-1))->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                    // FORMATO PARA COLUMNAS AGRUPADAS
                    $sheet->getStyle('A8:C' . ($lastRow-1))->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFE7F3FF']
                        ]
                    ]);
                }
            };
        }

        return $sheets;
    }
}