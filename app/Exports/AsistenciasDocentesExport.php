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

// Estas importaciones son para la clase principal, no para la anónima directamente.
// Las importaciones para la clase anónima se harán dentro de ella con el FQN.
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Esta clase principal genera un REPORTE DETALLADO de asistencia de docentes,
 * con una hoja separada para cada docente.
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

        $processedDetailedAsistencias = [];

        // 1. Obtener todos los docentes relevantes según los filtros
        $docentesQuery = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        });
        if ($this->selectedDocenteId) {
            $docentesQuery->where('id', $this->selectedDocenteId);
        }
        $docentes = $docentesQuery->get();

        // 2. Determinar el rango de fechas para iterar
        $startDate = $this->fechaInicio ? Carbon::parse($this->fechaInicio)->startOfDay() : null;
        $endDate = $this->fechaFin ? Carbon::parse($this->fechaFin)->endOfDay() : null;

        if (!$startDate && $this->selectedMonth && $this->selectedYear) {
            $startDate = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->startOfDay();
            $endDate = Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->endOfMonth()->endOfDay();
        } elseif (!$startDate && !$endDate) {
            // Si no hay filtros de fecha, por defecto a un rango razonable, por ejemplo, los últimos 30 días
            $endDate = Carbon::today()->endOfDay();
            $startDate = $endDate->copy()->subDays(30)->startOfDay();
        }

        // Asegurar que la fecha de inicio no sea posterior a la fecha de fin
        if ($startDate && $endDate && $startDate->greaterThan($endDate)) {
            list($startDate, $endDate) = [$endDate, $startDate];
        }
        
        // 3. Iterar a través de cada docente y cada día en el rango seleccionado
        foreach ($docentes as $docente) {
            // Inicializar la estructura para este docente
            if (!isset($processedDetailedAsistencias[$docente->id])) {
                $processedDetailedAsistencias[$docente->id] = [
                    'docente_info' => $docente,
                    'months' => [],
                    'total_horas' => 0,
                    'total_pagos' => 0,
                ];
            }

            // Iterar día por día dentro del rango
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $diaSemanaNombre = strtolower($currentDate->locale('es')->dayName);

                // Obtener las sesiones programadas para este docente en este día específico
                $horariosDelDia = HorarioDocente::where('docente_id', $docente->id)
                    ->where('dia_semana', $diaSemanaNombre)
                    ->with(['curso', 'aula', 'ciclo']) // Cargar ciclo para el filtro
                    ->when($this->selectedCicloAcademico, function ($query) {
                        $query->whereHas('ciclo', function ($q) {
                            $q->where('codigo', $this->selectedCicloAcademico);
                        });
                    })
                    ->orderBy('hora_inicio')
                    ->get();

                // Cargar todos los registros BIOMÉTRICOS (RegistroAsistencia) para el docente en este día
                $registrosBiometricosDelDia = RegistroAsistencia::where('nro_documento', $docente->numero_documento)
                    ->whereDate('fecha_registro', $currentDate->toDateString())
                    ->orderBy('fecha_registro', 'asc')
                    ->get();

                // Para cada horario programado, buscar sus asistencias reales
                foreach ($horariosDelDia as $horario) {
                    // --- INICIO DE LA VERIFICACIÓN CRUCIAL DE $horario ---
                    if (!$horario || !$horario->hora_inicio || !$horario->hora_fin) {
                        // Si el horario es nulo o le faltan propiedades de tiempo, agregamos una fila de error
                        // para que el reporte sea completo y no se rompa.
                        $monthKey = $currentDate->format('Y-m');
                        $weekKey = $currentDate->weekOfYear;

                        // Asegurarse de que la estructura exista antes de añadir el detalle
                        if (!isset($processedDetailedAsistencias[$docente->id]['months'][$monthKey])) {
                            $processedDetailedAsistencias[$docente->id]['months'][$monthKey] = [
                                'month_name' => $currentDate->locale('es')->monthName,
                                'weeks' => [],
                                'total_horas' => 0,
                                'total_pagos' => 0,
                            ];
                        }
                        if (!isset($processedDetailedAsistencias[$docente->id]['months'][$monthKey]['weeks'][$weekKey])) {
                            $processedDetailedAsistencias[$docente->id]['months'][$monthKey]['weeks'][$weekKey] = [
                                'week_number' => $weekKey,
                                'details' => [],
                                'total_horas' => 0,
                                'total_pagos' => 0,
                            ];
                        }

                        $processedDetailedAsistencias[$docente->id]['months'][$monthKey]['weeks'][$weekKey]['details'][] = [
                            'fecha' => $currentDate->toDateString(),
                            'curso' => 'ERROR: Horario no encontrado/inválido', 
                            'tema_desarrollado' => 'N/A',
                            'aula' => 'N/A', 
                            'turno' => 'N/A', 
                            'hora_entrada' => 'N/A',
                            'hora_salida' => 'N/A',
                            'horas_dictadas' => 0,
                            'pago' => 0,
                            'minutos_tardanza' => 0,
                            'estado_sesion' => 'ERROR', 
                            'salida_source' => 'N/A', 
                        ];
                        continue; // Pasa a la siguiente iteración del loop foreach ($horariosDelDia as $horario)
                    }
                    // --- FIN DE LA VERIFICACIÓN CRUCIAL ---

                    $horaInicioProgramada = Carbon::parse($horario->hora_inicio);
                    $horaFinProgramada = Carbon::parse($horario->hora_fin);

                    // Combinar la fecha actual con las horas del horario para las ventanas de búsqueda
                    $horarioInicioHoy = $currentDate->copy()->setTime($horaInicioProgramada->hour, $horaInicioProgramada->minute, $horaInicioProgramada->second);
                    $horarioFinHoy = $currentDate->copy()->setTime($horaFinProgramada->hour, $horaFinProgramada->minute, $horaFinProgramada->second); 

                    // --- REPLICANDO LÓGICA DEL DASHBOARD PARA ENTRADA Y SALIDA ---
                    // Buscar entrada válida (15 min antes hasta 30 min después del inicio)
                    $entradaBiometrica = $registrosBiometricosDelDia
                        ->filter(function($r) use ($horarioInicioHoy) {
                            $horaRegistro = \Carbon\Carbon::parse($r->fecha_registro); 
                            return $horaRegistro->between(
                                $horarioInicioHoy->copy()->subMinutes(15),
                                $horarioInicioHoy->copy()->addMinutes(30)
                            );
                        })
                        ->sortBy('fecha_registro')
                        ->first();

                    // Buscar salida válida (15 min antes hasta 60 min después del final)
                    $salidaBiometrica = $registrosBiometricosDelDia
                        ->filter(function($r) use ($horarioFinHoy) {
                            $horaRegistro = \Carbon\Carbon::parse($r->fecha_registro); 
                            return $horaRegistro->between(
                                $horarioFinHoy->copy()->subMinutes(15),
                                $horarioFinHoy->copy()->addMinutes(60)
                            );
                        })
                        ->sortByDesc('fecha_registro')
                        ->first();
                    // --- FIN REPLICANDO LÓGICA DEL DASHBOARD ---
                    
                    // Buscar el registro de AsistenciaDocente (procesado) para obtener el tema desarrollado
                    $asistenciaDocenteProcesada = AsistenciaDocente::where('docente_id', $docente->id)
                        ->where('horario_id', $horario->id)
                        ->whereDate('fecha_hora', $currentDate->toDateString())
                        ->first(); // Puede ser null si aún no se ha procesado o registrado el tema

                    // Inicializar variables para los detalles de la sesión
                    $temaDesarrollado = $asistenciaDocenteProcesada->tema_desarrollado ?? 'No registrado';
                    $horasDictadas = 0;
                    $montoTotal = 0;
                    $minutosTardanza = 0;
                    $estadoTexto = 'PROGRAMADA';
                    $horaEntradaDisplay = 'N/A';
                    $horaSalidaDisplay = 'N/A';
                    $salidaSource = 'N/A'; 

                    $cursoNombre = $horario->curso->nombre ?? 'N/A';
                    $aulaNombre = $horario->aula->nombre ?? 'N/A';
                    $turnoNombre = $horario->turno ?? 'N/A';

                    // Procesar la entrada
                    if ($entradaBiometrica) {
                        $horaEntradaDisplay = Carbon::parse($entradaBiometrica->fecha_registro)->format('h:i A');
                        
                        // Calcular tardanza
                        $toleranciaTarde = $horaInicioProgramada->copy()->addMinutes(AsistenciaDocenteController::TOLERANCIA_TARDE_MINUTOS);
                        if (Carbon::parse($entradaBiometrica->fecha_registro)->greaterThan($toleranciaTarde)) {
                            $minutosTardanza = Carbon::parse($entradaBiometrica->fecha_registro)->diffInMinutes($toleranciaTarde);
                        }
                    }

                    // Procesar la salida
                    if ($salidaBiometrica) {
                        $horaSalidaDisplay = Carbon::parse($salidaBiometrica->fecha_registro)->format('h:i A');
                        $salidaSource = 'Biométrico'; 
                    }

                    // Calcular horas dictadas y pago si hay entrada y salida válidas
                    if ($entradaBiometrica && $salidaBiometrica && Carbon::parse($salidaBiometrica->fecha_registro)->greaterThan(Carbon::parse($entradaBiometrica->fecha_registro))) {
                        $minutosDictados = Carbon::parse($salidaBiometrica->fecha_registro)->diffInMinutes(Carbon::parse($entradaBiometrica->fecha_registro));
                        $horasDictadas = round($minutosDictados / 60, 2);

                        // Obtener tarifa dinámica desde PagoDocente
                        $tarifaPorHoraAplicable = 0;
                        $pagoDocente = PagoDocente::where('docente_id', $docente->id)
                            ->whereDate('fecha_inicio', '<=', $currentDate)
                            ->whereDate('fecha_fin', '>=', $currentDate)
                            ->first();
                        if ($pagoDocente) {
                            $tarifaPorHoraAplicable = $pagoDocente->tarifa_por_hora;
                        }
                        $montoTotal = $horasDictadas * $tarifaPorHoraAplicable;
                    }

                    // Determinar el estado de la sesión (lógica similar al dashboard)
                    if ($entradaBiometrica && $salidaBiometrica) {
                        $estadoTexto = 'COMPLETADA';
                    } elseif ($entradaBiometrica && !$salidaBiometrica) {
                        // Si solo hay entrada y la clase ya debería haber terminado (hoy o en el pasado)
                        if ($currentDate->lessThan(Carbon::today()) || ($currentDate->isToday() && Carbon::now()->greaterThan($horaFinProgramada))) {
                            $estadoTexto = 'PENDIENTE (solo entrada)';
                        } else {
                            $estadoTexto = 'EN CURSO (solo entrada)'; // Si es hoy y aún no termina
                        }
                    } elseif (!$entradaBiometrica && !$salidaBiometrica) {
                        // Si no hay registros y la clase ya pasó (hoy o en el pasado)
                        if ($currentDate->lessThan(Carbon::today()) || ($currentDate->isToday() && Carbon::now()->greaterThan($horaFinProgramada))) {
                            $estadoTexto = 'SIN REGISTRO';
                        } else {
                            $estadoTexto = 'PROGRAMADA'; // Clase futura o que aún no empieza hoy
                        }
                    }

                    // Almacenar los datos procesados para el reporte
                    $monthKey = $currentDate->format('Y-m');
                    if (!isset($processedDetailedAsistencias[$docente->id]['months'][$monthKey])) {
                        $processedDetailedAsistencias[$docente->id]['months'][$monthKey] = [
                            'month_name' => $currentDate->locale('es')->monthName,
                            'weeks' => [],
                            'total_horas' => 0,
                            'total_pagos' => 0,
                        ];
                    }

                    $weekKey = $currentDate->weekOfYear;
                    if (!isset($processedDetailedAsistencias[$docente->id]['months'][$monthKey]['weeks'][$weekKey])) {
                        $processedDetailedAsistencias[$docente->id]['months'][$monthKey]['weeks'][$weekKey] = [
                            'week_number' => $weekKey,
                            'details' => [],
                            'total_horas' => 0,
                            'total_pagos' => 0,
                        ];
                    }
                    
                    $processedDetailedAsistencias[$docente->id]['months'][$monthKey]['weeks'][$weekKey]['details'][] = [
                        'fecha' => $currentDate->toDateString(),
                        'curso' => $cursoNombre, 
                        'tema_desarrollado' => $temaDesarrollado,
                        'aula' => $aulaNombre, 
                        'turno' => $turnoNombre, 
                        'hora_entrada' => $horaEntradaDisplay,
                        'hora_salida' => $horaSalidaDisplay,
                        'horas_dictadas' => $horasDictadas,
                        'pago' => $montoTotal,
                        'minutos_tardanza' => $minutosTardanza,
                        'estado_sesion' => $estadoTexto, 
                        'salida_source' => $salidaSource, 
                    ];

                    // Acumular totales para la agrupación
                    $processedDetailedAsistencias[$docente->id]['months'][$monthKey]['weeks'][$weekKey]['total_horas'] += $horasDictadas;
                    $processedDetailedAsistencias[$docente->id]['months'][$monthKey]['weeks'][$weekKey]['total_pagos'] += $montoTotal;
                    $processedDetailedAsistencias[$docente->id]['months'][$monthKey]['total_horas'] += $horasDictadas;
                    $processedDetailedAsistencias[$docente->id]['months'][$monthKey]['total_pagos'] += $montoTotal;
                    $processedDetailedAsistencias[$docente->id]['total_horas'] += $horasDictadas;
                    $processedDetailedAsistencias[$docente->id]['total_pagos'] += $montoTotal;
                }
                $currentDate->addDay(); 
            }
        }
        $this->processedData = $processedDetailedAsistencias;
    }

    /**
     * Define las hojas individuales del Excel.
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $rangoFechasHeader = 'PERIODO: ';
        if ($this->fechaInicio && $this->fechaFin) {
            $rangoFechasHeader .= \Carbon\Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($this->fechaFin)->format('d/m/Y');
        } elseif ($this->selectedMonth && $this->selectedYear) {
            $rangoFechasHeader .= \Carbon\Carbon::createFromDate($this->selectedYear, (int)$this->selectedMonth, 1)->locale('es')->monthName . ' ' . $this->selectedYear;
        } else {
            $rangoFechasHeader .= 'Todo el Historial';
        }

        $cicloHeader = null;
        if ($this->selectedCicloAcademico) {
            $cicloHeader = ' - CICLO: ' . $this->selectedCicloAcademico;
        }

        foreach ($this->processedData as $docenteId => $docenteData) {
            $docenteName = $docenteData['docente_info']->nombre . ' ' . $docenteData['docente_info']->apellido_paterno;
            
            // Crea una nueva instancia de clase anónima para cada hoja
            $sheets[] = new class($docenteData, $docenteName, $rangoFechasHeader, $cicloHeader) implements 
                \Maatwebsite\Excel\Concerns\FromCollection, 
                \Maatwebsite\Excel\Concerns\WithTitle, 
                \Maatwebsite\Excel\Concerns\WithHeadings, 
                \Maatwebsite\Excel\Concerns\WithMapping, 
                \Maatwebsite\Excel\Concerns\ShouldAutoSize, 
                \Maatwebsite\Excel\Concerns\WithEvents 
            {
                // No se usan 'use' statements aquí, se usa el FQN directamente en la línea 'implements' y en el código.
                // Esto es para evitar el error "Trait not found" en clases anónimas.

                private $docenteData;
                private $docenteName;
                private $filterPeriodHeader;
                private $filterCicloHeader;
                private $currentRow = 1; // Track current row for styling

                public function __construct(array $docenteData, string $docenteName, string $filterPeriodHeader, ?string $filterCicloHeader)
                {
                    $this->docenteData = $docenteData;
                    $this->docenteName = $docenteName;
                    $this->filterPeriodHeader = $filterPeriodHeader;
                    $this->filterCicloHeader = $filterCicloHeader;
                }

                /**
                 * @return string
                 */
                public function title(): string
                {
                    // Usa el nombre del docente como título de la hoja
                    // Los nombres de hoja de Excel tienen un máximo de 31 caracteres y no pueden contener ciertos caracteres.
                    // Lo sanitizamos para mayor seguridad.
                    $title = substr(\preg_replace('/[\\\\\/:\*\?\[\]]/', '', $this->docenteName), 0, 31); // Usar FQN para preg_replace
                    return $title ?: 'Docente'; // Fallback si el nombre está vacío después de la sanitización
                }

                /**
                 * @return \Illuminate\Support\Collection
                 */
                public function collection()
                {
                    $dataRows = new \Illuminate\Support\Collection(); // Usar FQN
                    $this->currentRow = 1; // Reiniciar el contador de filas para cada hoja

                    // Sección de encabezado para cada hoja (similar al reporte principal)
                    $dataRows->push([
                        'UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS', 
                        '', '', '', '', '', '', '', '', '', '', '', '', '', '' 
                    ]);
                    $this->currentRow++;
                    $dataRows->push([
                        'CENTRO PRE UNIVERSITARIO', 
                        '', '', '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    $this->currentRow++;
                    $dataRows->push(['']); 
                    $this->currentRow++;
                    $dataRows->push([
                        'REPORTE DE ASISTENCIA DOCENTE', 
                        '', '', '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    $this->currentRow++;
                    $dataRows->push([
                        'INFORME DE AVANCE ACADÉMICO', 
                        '', '', '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    $this->currentRow++;
                    $dataRows->push([
                        $this->filterPeriodHeader . ($this->filterCicloHeader ? ' ' . $this->filterCicloHeader : ''), 
                        '', '', '', '', '', '', '', '', '', '', '', '', '', ''
                    ]);
                    $this->currentRow++;
                    $dataRows->push(['']); 
                    $this->currentRow++;

                    // Fila del nombre del Docente
                    $dataRows->push([$this->docenteName, '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
                    $this->currentRow++;

                    // Encabezados de la tabla
                    $dataRows->push([
                        'MES', 'SEMANA', 'FECHA', 'CURSO', 'TEMA DESARROLLADO', 'AULA', 'TURNO', 
                        'HORA ENTRADA', 'HORA SALIDA', 'TARDANZA (min)', 'HORAS DICTADAS', 'PAGO', 'ESTADO', 'NOTA DE SALIDA' 
                    ]);
                    $this->currentRow++; 

                    $docenteTotalHoras = 0;
                    $docenteTotalPago = 0;

                    ksort($this->docenteData['months']); 
                    foreach ($this->docenteData['months'] as $monthKey => $monthData) {
                        $monthName = \strtoupper($monthData['month_name']); 
                        
                        // Fila del Mes
                        $dataRows->push([$monthName, '', '', '', '', '', '', '', '', '', '', '', '', '']);
                        $this->currentRow++;

                        $monthTotalHoras = 0;
                        $monthTotalPago = 0;
                        
                        ksort($monthData['weeks']); 
                        foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                            // Fila de la Semana
                            $dataRows->push(['', 'SEMANA ' . $weekNumber, '', '', '', '', '', '', '', '', '', '', '', '']);
                            $this->currentRow++;

                            $weekTotalHoras = 0;
                            $weekTotalPago = 0;

                            // Filas de Detalles
                            foreach ($weekData['details'] as $detail) {
                                $dataRows->push([
                                    '', '', // Columnas de agrupación vacías
                                    \Carbon\Carbon::parse($detail['fecha'])->format('d/m/Y'), 
                                    $detail['curso'],
                                    $detail['tema_desarrollado'],
                                    $detail['aula'],
                                    $detail['turno'],
                                    $detail['hora_entrada'],
                                    $detail['hora_salida'],
                                    $detail['minutos_tardanza'] > 0 ? $detail['minutos_tardanza'] : '', 
                                    \number_format($detail['horas_dictadas'], 2), 
                                    'S/ ' . \number_format($detail['pago'], 2, '.', ','), 
                                    $detail['estado_sesion'], 
                                    $detail['salida_source'], 
                                ]);
                                $this->currentRow++;
                                $weekTotalHoras += $detail['horas_dictadas'];
                                $weekTotalPago += $detail['pago'];
                            }
                            // Fila de Total Semanal
                            $dataRows->push([
                                '', '', '', '', '', '', '', '', 'TOTAL SEMANA ' . $weekNumber,
                                '', // Vacío para Tardanza
                                \number_format($weekTotalHoras, 2),
                                'S/ ' . \number_format($weekTotalPago, 2, '.', ','),
                                '', // Vacío para Estado
                                '', // Vacío para Nota de Salida
                            ]);
                            $this->currentRow++;
                            $monthTotalHoras += $weekTotalHoras;
                            $monthTotalPago += $weekTotalPago;
                        }
                        // Fila de Total Mensual
                        $dataRows->push([
                            '', '', '', '', '', '', '', '', 'TOTAL MES ' . $monthName,
                            '', // Vacío para Tardanza
                            \number_format($monthTotalHoras, 2),
                            'S/ ' . \number_format($monthTotalPago, 2, '.', ','),
                            '', // Vacío para Estado
                            '', // Vacío para Nota de Salida
                        ]);
                        $this->currentRow++;
                        $docenteTotalHoras += $monthTotalHoras;
                        $docenteTotalPago += $monthTotalPago; 
                    }
                    // Fila de Total por Docente
                    $dataRows->push([
                        '', '', '', '', '', '', '', '', 'TOTAL ' . $this->docenteName,
                        '', // Vacío para Tardanza
                        \number_format($docenteTotalHoras, 2),
                        'S/ ' . \number_format($docenteTotalPago, 2, '.', ','),
                        '', // Vacío para Estado
                        '', // Vacío para Nota de Salida
                    ]);
                    $this->currentRow++;

                    return $dataRows;
                }

                /**
                 * @return array
                 */
                public function headings(): array
                {
                    return []; 
                }

                /**
                 * @param mixed $row
                 * @return array
                 */
                public function map($row): array
                {
                    return $row; 
                }

                /**
                 * @return array
                 */
                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) { // Usar FQN
                            $sheet = $event->sheet->getDelegate();
                            
                            $startHeaderRow = 1; 
                            $endHeaderRow = 7; 
                            $totalColumns = 'O'; // 15 columnas (A-O)

                            // Estilos para los encabezados principales (filas 1-6)
                            $sheet->mergeCells('A1:'.$totalColumns.'1');
                            $sheet->getStyle('A1')->applyFromArray([
                                'font' => ['bold' => true, 'size' => 14],
                                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                            ]);

                            $sheet->mergeCells('A2:'.$totalColumns.'2');
                            $sheet->getStyle('A2')->applyFromArray([
                                'font' => ['bold' => true, 'size' => 12],
                                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                            ]);

                            $sheet->mergeCells('A4:'.$totalColumns.'4');
                            $sheet->getStyle('A4')->applyFromArray([
                                'font' => ['bold' => true, 'size' => 12],
                                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                            ]);

                            $sheet->mergeCells('A5:'.$totalColumns.'5');
                            $sheet->getStyle('A5')->applyFromArray([
                                'font' => ['bold' => true, 'size' => 12],
                                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                            ]);

                            $sheet->mergeCells('A6:'.$totalColumns.'6');
                            $sheet->getStyle('A6')->applyFromArray([
                                'font' => ['bold' => true, 'size' => 12],
                                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                            ]);

                            // Teacher Name Header (Row 8)
                            $teacherNameRow = 8;
                            $sheet->mergeCells('A'.$teacherNameRow.':'.$totalColumns.$teacherNameRow);
                            $sheet->getStyle('A'.$teacherNameRow)->applyFromArray([
                                'font' => ['bold' => true, 'size' => 12],
                                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDDEBF7']], // Light Blue for teacher
                                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                            ]);

                            // Table Headers (Row 9)
                            $tableHeaderRow = 9;
                            $sheet->getStyle('A'.$tableHeaderRow.':'.$totalColumns.$tableHeaderRow)->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 
                                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF366092']], // Dark Blue
                                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                            ]);
                            $sheet->getRowDimension($tableHeaderRow)->setRowHeight(20); 

                            $actualRowIndex = $tableHeaderRow + 1; // Start data rows from row 10

                            // Apply styles and merges for grouped data
                            $docenteData = $this->docenteData; // Access the processed data for this sheet
                            
                            ksort($docenteData['months']); 
                            foreach ($docenteData['months'] as $monthKey => $monthData) {
                                $monthStartRow = $actualRowIndex;
                                $actualRowIndex++; 

                                // Month row style
                                $sheet->getStyle('A'.$monthStartRow.':'.$totalColumns.$monthStartRow)->applyFromArray([
                                    'font' => ['bold' => true, 'size' => 11],
                                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']], // Lighter blue for month
                                ]);
                                $sheet->mergeCells('A'.$monthStartRow.':B'.$monthStartRow); // Merge 'MES' and 'SEMANA' for month title

                                ksort($monthData['weeks']);
                                foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                                    $weekStartRow = $actualRowIndex;
                                    $actualRowIndex++; 

                                    // Week row style
                                    $sheet->getStyle('B'.$weekStartRow.':'.$totalColumns.$weekStartRow)->applyFromArray([
                                        'font' => ['bold' => true, 'size' => 10],
                                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEBF1DE']], // Light green for week
                                    ]);
                                    $sheet->mergeCells('B'.$weekStartRow.':C'.$weekStartRow); // Merge 'SEMANA' with 'FECHA' for week title

                                    // Count detail rows for the week
                                    foreach ($weekData['details'] as $detail) {
                                        $actualRowIndex++; 
                                    }
                                    
                                    // Weekly Total row style
                                    $sheet->getStyle('I'.$actualRowIndex.':'.$totalColumns.$actualRowIndex)->applyFromArray([
                                        'font' => ['bold' => true],
                                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2EFDA']], // Green for weekly total
                                    ]);
                                    $sheet->mergeCells('I'.$actualRowIndex.':J'.$actualRowIndex); // Merge "TOTAL SEMANA" with "TARDANZA"
                                    $actualRowIndex++; 

                                    // Merge 'SEMANA' column for the group
                                    $sheet->mergeCells('B'.$weekStartRow.':B'.($actualRowIndex - 2));
                                    $sheet->getStyle('B'.$weekStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                                }
                                // Monthly Total row style
                                $sheet->getStyle('I'.$actualRowIndex.':'.$totalColumns.$actualRowIndex)->applyFromArray([
                                    'font' => ['bold' => true],
                                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC6E0B4']], // Darker green for monthly total
                                ]);
                                $sheet->mergeCells('I'.$actualRowIndex.':J'.$actualRowIndex); 
                                $actualRowIndex++;
                                
                                // Merge 'MES' column for the group
                                $sheet->mergeCells('A'.$monthStartRow.':A'.($actualRowIndex - 2));
                                $sheet->getStyle('A'.$monthStartRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                            }
                            // Teacher's Total row style - This is the last row for the teacher's section
                            $sheet->getStyle('I'.$actualRowIndex.':'.$totalColumns.$actualRowIndex)->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFA9D18E']], // Even darker green for teacher total
                            ]);
                            $sheet->mergeCells('I'.$actualRowIndex.':J'.$actualRowIndex); 
                            $actualRowIndex++;
                            
                            // Apply borders to all data cells for this sheet
                            // Note: This range needs to be dynamic based on the actual content of the sheet
                            $sheet->getStyle('A'.$tableHeaderRow.':'.$totalColumns.($actualRowIndex - 1))->applyFromArray([
                                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]],
                            ]);

                            // Auto-size columns for this sheet
                            foreach (range('A', $totalColumns) as $col) {
                                $sheet->getColumnDimension($col)->setAutoSize(true);
                            }
                        },
                    ];
                }
            };
        }

        return $sheets;
    }
}
