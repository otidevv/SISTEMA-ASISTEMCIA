<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsistenciaDocente;
use App\Models\AsistenciaEvento;
use App\Models\User;
use App\Models\HorarioDocente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Models\RegistroAsistencia;
use App\Models\Ciclo; // Usando tu modelo Ciclo.php
use App\Models\PagoDocente; // Importa el modelo PagoDocente
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Importa la clase Excel de Maatwebsite
use Maatwebsite\Excel\Facades\Excel;
// Importa tu clase de exportación
use App\Exports\AsistenciasDocentesExport; 

class AsistenciaDocenteController extends Controller
{
    // La tarifa por minuto fija se remueve si es dinámica por docente.
    // const TARIFA_POR_MINUTO = 3.00; 

    // Tolerancia en minutos para la entrada anticipada (ej. 10 minutos antes de las 7:00 AM, se puede marcar desde las 6:50 AM)
    const TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS = 10; 
    // Tolerancia en minutos para considerar tardanza (ej. si la hora de inicio es 7:00 AM, la tardanza es a partir de las 7:05 AM)
    const TOLERANCIA_TARDE_MINUTOS = 5; 

    public function __construct()
    {
        Artisan::call('asistencia:procesar-eventos');
    }

    public function reports(Request $request)
    {
        // 1. Obtener parámetros de filtrado desde la URL
        $selectedDocenteId = $request->input('docente_id');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $selectedCicloAcademico = $request->input('ciclo_academico');

        // Determinar mes y año para los filtros si no hay rango de fechas
        $selectedMonth = $selectedMonth ?? Carbon::now()->month;
        $selectedYear = $selectedYear ?? Carbon::now()->year;

        if (empty($fechaInicio) && empty($fechaFin)) {
            $selectedMonth = $selectedMonth ?? Carbon::now()->month;
            $selectedYear = $selectedYear ?? Carbon::now()->year;
        } else {
            $selectedMonth = null; 
            $selectedYear = null;
        }

        // Obtener todos los docentes para el filtro de selección
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'nombre', 'apellido_paterno', 'numero_documento')->get();

        // Obtener Ciclos Académicos de la base de datos usando tu modelo Ciclo
        // Usamos 'codigo' como valor para el select y 'nombre' para mostrar
        $ciclosAcademicos = Ciclo::orderBy('nombre', 'desc')->pluck('nombre', 'codigo')->toArray();


        // 2. Construir la consulta base para asistencias docentes, aplicando filtros
        $baseQuery = AsistenciaDocente::query();

        if ($selectedDocenteId) {
            $baseQuery->where('docente_id', $selectedDocenteId);
        }

        if ($fechaInicio && $fechaFin) {
            $baseQuery->whereBetween('fecha_hora', [Carbon::parse($fechaInicio)->startOfDay(), Carbon::parse($fechaFin)->endOfDay()]);
        } elseif (!empty($selectedMonth) && !empty($selectedYear)) {
            $baseQuery->whereMonth('fecha_hora', $selectedMonth)
                      ->whereYear('fecha_hora', $selectedYear);
        }
        
        // ¡¡¡CORRECCIÓN CLAVE AQUÍ!!!
        // REEMPLAZA '[TU_COLUMNA_CICLO_EN_ASISTENCIAS_DOCENTES_REAL]' con el nombre real de la columna
        // en tu tabla 'asistencias_docentes' que guarda el código del ciclo.
        // Ejemplos: 'ciclo_id', 'codigo_ciclo', 'periodo_academico', etc.
        if ($selectedCicloAcademico) {
            // Asume que tu modelo AsistenciaDocente tiene una relación 'horario' y este a su vez con 'ciclo'
            // Y que la columna 'codigo' en tu tabla 'ciclos' contiene los códigos como '2025-1'
            $baseQuery->whereHas('horario.ciclo', function ($query) use ($selectedCicloAcademico) {
                $query->where('codigo', $selectedCicloAcademico);
            });
        }

        // 3. Calcular estadísticas generales (para el periodo filtrado)
        $totalRegistrosPeriodo = (clone $baseQuery)->count();
        
        // Asistencia por día del mes/rango de fechas para el gráfico
        $asistenciaSemana = (clone $baseQuery)
            ->selectRaw('DATE(fecha_hora) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get()
            ->keyBy('fecha')
            ->map(function($item) { return $item->total; })
            ->toArray();

        // Ajustar fechas del gráfico para el rango de fechas o mes/año
        $fechasCompletasMes = [];
        if ($fechaInicio && $fechaFin) {
            $currentDate = Carbon::parse($fechaInicio)->startOfDay();
            $endDate = Carbon::parse($fechaFin)->endOfDay();
            while ($currentDate->lte($endDate)) {
                $fechasCompletasMes[$currentDate->format('Y-m-d')] = $asistenciaSemana[$currentDate->format('Y-m-d')] ?? 0;
                $currentDate->addDay();
            }
        } elseif (!empty($selectedMonth) && !empty($selectedYear)) { 
            $diasEnMes = Carbon::createFromDate((int)$selectedYear, (int)$selectedMonth, 1)->daysInMonth;
            for ($i = 1; $i <= $diasEnMes; $i++) {
                $fecha = Carbon::createFromDate((int)$selectedYear, (int)$selectedMonth, $i)->format('Y-m-d');
                $fechasCompletasMes[$fecha] = $asistenciaSemana[$fecha] ?? 0;
            }
        }
        $asistenciaSemana = $fechasCompletasMes;


        // 4. Asistencia por docente (resumen para métricas y tabla de resumen)
        $asistenciaPorDocenteQuery = AsistenciaDocente::query();
        
        if ($fechaInicio && $fechaFin) {
            $asistenciaPorDocenteQuery->whereBetween('fecha_hora', [Carbon::parse($fechaInicio)->startOfDay(), Carbon::parse($fechaFin)->endOfDay()]);
        } elseif (!empty($selectedMonth) && !empty($selectedYear)) {
            $asistenciaPorDocenteQuery->whereMonth('fecha_hora', $selectedMonth)
                                      ->whereYear('fecha_hora', $selectedYear);
        }

        if ($selectedDocenteId) {
            $asistenciaPorDocenteQuery->where('docente_id', $selectedDocenteId);
        }

        // ¡¡¡CORRECCIÓN CLAVE AQUÍ!!!
        if ($selectedCicloAcademico) {
            $asistenciaPorDocenteQuery->whereHas('horario.ciclo', function ($query) use ($selectedCicloAcademico) {
                $query->where('codigo', $selectedCicloAcademico);
            });
        }

        $asistenciaPorDocente = $asistenciaPorDocenteQuery
            ->with('docente')
            ->selectRaw('docente_id, COUNT(*) as total_asistencias')
            ->groupBy('docente_id')
            ->get();

        // Calcular horas_dictadas y monto_total por docente para la tabla resumen
        $asistenciaPorDocente->transform(function ($item) use ($fechaInicio, $fechaFin, $selectedMonth, $selectedYear, $selectedCicloAcademico) {
            $docenteAsistenciasQuery = AsistenciaDocente::where('docente_id', $item->docente_id)
                ->orderBy('fecha_hora', 'asc');

            if ($fechaInicio && $fechaFin) {
                $docenteAsistenciasQuery->whereBetween('fecha_hora', [Carbon::parse($fechaInicio)->startOfDay(), Carbon::parse($fechaFin)->endOfDay()]);
            } elseif (!empty($selectedMonth) && !empty($selectedYear)) {
                $docenteAsistenciasQuery->whereMonth('fecha_hora', $selectedMonth)
                                        ->whereYear('fecha_hora', $selectedYear);
            }
            // ¡¡¡CORRECCIÓN CLAVE AQUÍ!!!
            if ($selectedCicloAcademico) {
                $docenteAsistenciasQuery->whereHas('horario.ciclo', function ($query) use ($selectedCicloAcademico) {
                    $query->where('codigo', $selectedCicloAcademico);
                });
            }

            $docenteAsistencias = $docenteAsistenciasQuery->get();

            $totalHorasDictadas = 0;
            $totalMontoPago = 0;

            $groupedByDayAndHorario = $docenteAsistencias->groupBy(function ($asistencia) {
                return Carbon::parse($asistencia->fecha_hora)->format('Y-m-d') . '_' . $asistencia->horario_id;
            });

            foreach ($groupedByDayAndHorario as $group) {
                $entrada = $group->where('estado', 'entrada')->sortBy('fecha_hora')->first();
                $salida = $group->where('estado', 'salida')->sortByDesc('fecha_hora')->first();

                $horasDictadasSesion = 0;
                $montoTotalSesion = 0;

                // Si 'horas_dictadas' ya están en la DB, úsalas.
                if ($salida && $salida->horas_dictadas !== null) { 
                    $horasDictadasSesion = $salida->horas_dictadas;
                } elseif ($entrada && $entrada->horas_dictadas !== null) {
                    $horasDictadasSesion = $entrada->horas_dictadas;
                } else { // Recalcula si no están en DB
                    if ($entrada && $salida && Carbon::parse($salida->fecha_hora)->greaterThan(Carbon::parse($entrada->fecha_hora))) {
                        $minutosDictados = Carbon::parse($salida->fecha_hora)->diffInMinutes(Carbon::parse($entrada->fecha_hora));
                        $horasDictadasSesion = round($minutosDictados / 60, 2);
                    }
                }
                
                // Obtener la tarifa dinámica desde PagoDocente
                $tarifaPorHoraAplicable = 0;
                if ($horasDictadasSesion > 0 && $entrada) { // Solo si hay horas y un punto de referencia de fecha
                    $pagoDocente = PagoDocente::where('docente_id', $item->docente_id)
                        ->whereDate('fecha_inicio', '<=', $entrada->fecha_hora) // Fecha del registro de asistencia
                        ->whereDate('fecha_fin', '>=', $entrada->fecha_hora)
                        ->first();
                    if ($pagoDocente) {
                        $tarifaPorHoraAplicable = $pagoDocente->tarifa_por_hora;
                    }
                }
                // ¡¡¡CORRECCIÓN DE FÓRMULA DE PAGO AQUÍ!!! Si tarifa_por_hora es por hora, simplemente multiplica por las horas.
                $montoTotalSesion = $horasDictadasSesion * $tarifaPorHoraAplicable; // ¡Esta es la corrección!

                $totalHorasDictadas += $horasDictadasSesion;
                $totalMontoPago += $montoTotalSesion;
            }
            $item->total_horas = $totalHorasDictadas;
            $item->total_pagos = $totalMontoPago;
            return $item;
        });

        // 5. Preparar datos detallados agrupados para la tabla en la vista
        $processedDetailedAsistencias = [];
        $detailedAsistenciasQuery = AsistenciaDocente::with(['docente', 'horario.curso', 'horario.aula'])
            ->orderBy('fecha_hora', 'asc');

        // Aplicar los mismos filtros a esta consulta detallada
        if ($selectedDocenteId) {
            $detailedAsistenciasQuery->where('docente_id', $selectedDocenteId);
        }
        if ($fechaInicio && $fechaFin) {
            $detailedAsistenciasQuery->whereBetween('fecha_hora', [Carbon::parse($fechaInicio)->startOfDay(), Carbon::parse($fechaFin)->endOfDay()]);
        } elseif (!empty($selectedMonth) && !empty($selectedYear)) {
            $detailedAsistenciasQuery->whereMonth('fecha_hora', $selectedMonth)->whereYear('fecha_hora', $selectedYear);
        }
        // ¡¡¡CORRECCIÓN CLAVE AQUÍ!!!
        if ($selectedCicloAcademico) {
            $detailedAsistenciasQuery->whereHas('horario.ciclo', function ($query) use ($selectedCicloAcademico) {
                $query->where('codigo', $selectedCicloAcademico);
            });
        }
        
        $rawDetailedAsistencias = $detailedAsistenciasQuery->get();

        // Agrupar y calcular para la tabla detallada
        $groupedForDisplay = $rawDetailedAsistencias->groupBy(function ($item) {
            return $item->docente_id . '_' . Carbon::parse($item->fecha_hora)->format('Y-m-d') . '_' . $item->horario_id;
        });

        foreach ($groupedForDisplay as $groupKey => $records) {
            $docente = $records->first()->docente;
            $docenteId = $docente->id;
            $fecha = Carbon::parse($records->first()->fecha_hora)->format('Y-m-d');
            $horarioId = $records->first()->horario_id;

            $entrada = $records->where('estado', 'entrada')->sortBy('fecha_hora')->first();
            $salida = $records->where('estado', 'salida')->sortByDesc('fecha_hora')->first();

            $horaEntrada = $entrada ? Carbon::parse($entrada->fecha_hora) : null;
            $horaSalida = $salida ? Carbon::parse($salida->fecha_hora) : null;
            $temaDesarrollado = $salida->tema_desarrollado ?? ($entrada->tema_desarrollado ?? 'N/A');

            $horasDictadas = 0;
            $montoTotal = 0;

            if ($salida && ($salida->horas_dictadas !== null || $salida->monto_total !== null)) {
                $horasDictadas = $salida->horas_dictadas;
                $montoTotal = $salida->monto_total;
            } elseif ($entrada && ($entrada->horas_dictadas !== null || $entrada->monto_total !== null)) {
                $horasDictadas = $entrada->horas_dictadas;
                $montoTotal = $entrada->monto_total;
            }
            
            if (($horasDictadas === null || $horasDictadas == 0) && ($montoTotal === null || $montoTotal == 0)) {
                if ($horaEntrada && $horaSalida && Carbon::parse($salida->fecha_hora)->greaterThan(Carbon::parse($entrada->fecha_hora))) {
                    $minutosDictados = Carbon::parse($salida->fecha_hora)->diffInMinutes(Carbon::parse($entrada->fecha_hora));
                    $horasDictadas = round($minutosDictados / 60, 2);
                    // Aquí también se recalcula el monto si no hay en DB
                    $montoTotal = $minutosDictados * ($tarifaPorHoraAplicable > 0 ? ($tarifaPorHoraAplicable / 60) : 0); // Asumiendo que PagoDocente es la fuente
                }
            }
            // Obtener la tarifa dinámica para la tabla detallada (similar al resumen)
            $tarifaPorHoraAplicableDetalle = 0;
            if ($horasDictadas > 0 && $entrada) {
                $pagoDocenteDetalle = PagoDocente::where('docente_id', $docenteId)
                    ->whereDate('fecha_inicio', '<=', $entrada->fecha_hora)
                    ->whereDate('fecha_fin', '>=', $entrada->fecha_hora)
                    ->first();
                if ($pagoDocenteDetalle) {
                    $tarifaPorHoraAplicableDetalle = $pagoDocenteDetalle->tarifa_por_hora;
                }
            }
            // ¡¡¡CORRECCIÓN DE FÓRMULA DE PAGO AQUÍ!!!
            $montoTotal = $horasDictadas * $tarifaPorHoraAplicableDetalle;

            // Agrupar por docente, luego por mes, luego por semana, luego los detalles
            if (!isset($processedDetailedAsistencias[$docenteId])) {
                $processedDetailedAsistencias[$docenteId] = [
                    'docente_info' => $docente,
                    'months' => [],
                    'total_horas' => 0,
                    'total_pagos' => 0,
                ];
            }

            $monthKey = Carbon::parse($fecha)->format('Y-m');
            if (!isset($processedDetailedAsistencias[$docenteId]['months'][$monthKey])) {
                $processedDetailedAsistencias[$docenteId]['months'][$monthKey] = [
                    'month_name' => Carbon::parse($fecha)->locale('es')->monthName,
                    'weeks' => [],
                    'total_horas' => 0,
                    'total_pagos' => 0,
                ];
            }

            $weekKey = Carbon::parse($fecha)->weekOfYear;
            if (!isset($processedDetailedAsistencias[$docenteId]['months'][$monthKey]['weeks'][$weekKey])) {
                $processedDetailedAsistencias[$docenteId]['months'][$monthKey]['weeks'][$weekKey] = [
                    'week_number' => $weekKey,
                    'details' => [],
                    'total_horas' => 0,
                    'total_pagos' => 0,
                ];
            }
            
            $processedDetailedAsistencias[$docenteId]['months'][$monthKey]['weeks'][$weekKey]['details'][] = [
                'fecha' => $fecha,
                'curso' => $records->first()->horario->curso->nombre ?? 'N/A',
                'tema_desarrollado' => $temaDesarrollado,
                'aula' => $records->first()->horario->aula->nombre ?? 'N/A',
                'turno' => $records->first()->horario->turno ?? 'N/A',
                'hora_entrada' => $horaEntrada ? $horaEntrada->format('H:i a') : 'N/A',
                'hora_salida' => $horaSalida ? $horaSalida->format('H:i a') : 'N/A',
                'horas_dictadas' => $horasDictadas,
                'pago' => $montoTotal,
            ];

            // Acumular totales para la vista
            $processedDetailedAsistencias[$docenteId]['months'][$monthKey]['weeks'][$weekKey]['total_horas'] += $horasDictadas;
            $processedDetailedAsistencias[$docenteId]['months'][$monthKey]['weeks'][$weekKey]['total_pagos'] += $montoTotal;
            $processedDetailedAsistencias[$docenteId]['months'][$monthKey]['total_horas'] += $horasDictadas;
            $processedDetailedAsistencias[$docenteId]['months'][$monthKey]['total_pagos'] += $montoTotal;
            $processedDetailedAsistencias[$docenteId]['total_horas'] += $horasDictadas;
            $processedDetailedAsistencias[$docenteId]['total_pagos'] += $montoTotal;
        }

        // Calcular rowspans para la tabla de la vista
        foreach ($processedDetailedAsistencias as $docenteId => &$docenteData) {
            $docenteData['rowspan'] = 0;
            foreach ($docenteData['months'] as &$monthData) {
                $monthData['rowspan'] = 0;
                foreach ($monthData['weeks'] as &$weekData) {
                    $weekData['rowspan'] = count($weekData['details']) + 1; // Detalles + fila de total semanal
                    $monthData['rowspan'] += $weekData['rowspan'];
                }
                $monthData['rowspan'] += 1; // +1 para la fila de total mensual
                $docenteData['rowspan'] += $monthData['rowspan'];
            }
            $docenteData['rowspan'] += 1; // +1 para la fila de total docente
        }
        unset($docenteData, $monthData, $weekData); // Romper referencia

        // Pasar todas las variables necesarias a la vista.
        return view('asistencia-docente.reportes', compact(
            'totalRegistrosPeriodo', 
            'asistenciaSemana', 
            'asistenciaPorDocente', 
            'docentes',
            'ciclosAcademicos', 
            'selectedDocenteId', 
            'selectedMonth',    
            'selectedYear',     
            'fechaInicio',      
            'fechaFin',         
            'selectedCicloAcademico',
            'processedDetailedAsistencias' // Datos detallados para la tabla
        ));
    }

    public function index(Request $request)
    {
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));
        $documento = $request->get('documento');

        $docentesDocumentos = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->pluck('numero_documento')->toArray();

        $query = RegistroAsistencia::with(['usuario.roles'])
            ->whereIn('nro_documento', $docentesDocumentos);

        if ($fecha) {
            $query->where(function ($q) use ($fecha) {
                $q->whereDate('fecha_registro', $fecha)
                    ->orWhere(function ($q2) use ($fecha) {
                        $q2->where('tipo_verificacion', 4) // Manual
                            ->whereDate('fecha_hora', $fecha);
                    });
            });
        }

        if ($documento) {
            $query->where('nro_documento', 'like', '%' . $documento . '%');
        }

        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'numero_documento', 'nombre', 'apellido_paterno')->get();

        $asistencias->getCollection()->transform(function ($asistencia) {
            if ($asistencia->usuario) {
                $fechaAsistencia = Carbon::parse($asistencia->fecha_hora);
                $diaSemana = $fechaAsistencia->dayOfWeek;

                $diasSemana = [0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'];
                $nombreDia = $diasSemana[$diaSemana];

                // Buscar un horario que la asistencia pueda corresponder, considerando la tolerancia de entrada
                $horario = HorarioDocente::where('docente_id', $asistencia->usuario->id)
                    ->where('dia_semana', $nombreDia)
                    ->where(function ($q) use ($fechaAsistencia) {
                        // Condición 1: La asistencia está dentro del horario programado real
                        $q->whereTime('hora_inicio', '<=', $fechaAsistencia->format('H:i:s'))
                          ->whereTime('hora_fin', '>=', $fechaAsistencia->format('H:i:s'));
                    })
                    ->orWhere(function ($q) use ($fechaAsistencia) {
                        // Condición 2: La asistencia está dentro de la ventana de tolerancia temprana antes de hora_inicio
                        $q->whereTime('hora_inicio', '>=', $fechaAsistencia->copy()->subMinutes(self::TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS)->format('H:i:s'))
                          ->whereTime('hora_inicio', '<=', $fechaAsistencia->format('H:i:s'));
                    })
                    ->with('curso')
                    ->first();

                $asistencia->horario = $horario;

                if ($horario) {
                    $horaAsistenciaProgramada = Carbon::parse($horario->hora_inicio);
                    $horaFinProgramada = Carbon::parse($horario->hora_fin);

                    // Determinar entrada/salida basándose en la proximidad a las horas programadas reales
                    $diffInicio = abs($fechaAsistencia->diffInMinutes($horaAsistenciaProgramada));
                    $diffFin = abs($fechaAsistencia->diffInMinutes($horaFinProgramada));

                    $asistencia->tipo_asistencia = $diffInicio < $diffFin ? 'entrada' : 'salida';

                    // Calcular tardanza solo para el tipo 'entrada'
                    if ($asistencia->tipo_asistencia === 'entrada') {
                        $tardinessThreshold = $horaAsistenciaProgramada->copy()->addMinutes(self::TOLERANCIA_TARDE_MINUTOS);
                        if ($fechaAsistencia->greaterThan($tardinessThreshold)) {
                            $asistencia->es_tardanza = true;
                            $asistencia->minutos_tardanza = $fechaAsistencia->diffInMinutes($tardinessThreshold);
                        } else {
                            $asistencia->es_tardanza = false;
                            $asistencia->minutos_tardanza = 0;
                        }
                    } else {
                        $asistencia->es_tardanza = false;
                        $asistencia->minutos_tardanza = 0;
                    }

                } else {
                    $asistencia->tipo_asistencia = $fechaAsistencia->hour < 12 ? 'entrada' : 'salida';
                    $asistencia->es_tardanza = false; // Sin horario, no hay tardanza
                    $asistencia->minutos_tardanza = 0;
                }
            }

            return $asistencia;
        });

        return view('asistencia-docente.index', compact('asistencias', 'docentes', 'fecha', 'documento'));
    }

    public function monitor()
    {
        $ultimasAsistencias = AsistenciaDocente::with(['docente', 'horario.curso'])
            ->orderBy('fecha_hora', 'desc')
            ->take(10)
            ->get();

        return view('asistencia-docente.monitor', compact('ultimasAsistencias'));
    }

    /**
     * NUEVO: Mostrar el formulario para registrar asistencia docente manualmente.
     */
    public function create()
    {
        // Obtener docentes para el select (ordenados alfabéticamente)
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->orderBy('apellido_paterno', 'asc')
          ->orderBy('apellido_materno', 'asc')
          ->orderBy('nombre', 'asc')
          ->get();

        return view('asistencia-docente.create', compact('docentes'));
    }

    /**
     * ACTUALIZADO: Guardar un nuevo registro de asistencia docente manual.
     */
    public function store(Request $request)
    {
        // NUEVO: Si viene docente_id sin tema_desarrollado, es registro manual de asistencia biométrica
        if ($request->has('docente_id') && !$request->has('tema_desarrollado')) {
            $request->validate([
                'docente_id' => 'required|exists:users,id',
                'fecha_hora' => 'required|date',
                'estado' => 'required|in:entrada,salida',
                'tipo_verificacion' => 'nullable|in:manual,biometrico,tarjeta,codigo',
                'terminal_id' => 'nullable|string',
                'codigo_trabajo' => 'nullable|string'
            ], [
                'docente_id.required' => 'Debe seleccionar un docente',
                'docente_id.exists' => 'El docente seleccionado no es válido',
                'fecha_hora.required' => 'La fecha y hora son obligatorias',
                'fecha_hora.date' => 'El formato de fecha no es válido',
                'estado.required' => 'Debe seleccionar un estado (entrada o salida)',
                'estado.in' => 'El estado debe ser entrada o salida'
            ]);

            try {
                // Obtener el docente
                $docente = User::findOrFail($request->docente_id);
                
                // Verificar que el usuario sea efectivamente un docente
                if (!$docente->hasRole('profesor')) {
                    return back()->withErrors(['docente_id' => 'El usuario seleccionado no es un docente.']);
                }

                // Verificar si ya existe un registro similar reciente (evitar duplicados)
                $registroExistente = RegistroAsistencia::where('nro_documento', $docente->numero_documento)
                    ->where('fecha_registro', '>=', Carbon::parse($request->fecha_hora)->subMinutes(5))
                    ->where('fecha_registro', '<=', Carbon::parse($request->fecha_hora)->addMinutes(5))
                    ->first();

                if ($registroExistente) {
                    return back()->withErrors(['fecha_hora' => 'Ya existe un registro de asistencia cercano a esta fecha y hora.']);
                }

                // Convertir tipo_verificacion a número (siguiendo tu lógica existente)
                $tipoVerificacionMap = [
                    'biometrico' => 0,
                    'tarjeta' => 1,
                    'facial' => 2,
                    'codigo' => 3,
                    'manual' => 4
                ];

                $tipoVerificacion = $tipoVerificacionMap[$request->tipo_verificacion] ?? 4;

                // Procesar terminal_id: debe ser numérico o null
                $terminalId = null;
                if ($request->terminal_id) {
                    if (is_numeric($request->terminal_id)) {
                        $terminalId = (int)$request->terminal_id;
                    } else {
                        // Si no es numérico pero tiene valor, usar 999 como valor por defecto para manual
                        $terminalId = 999;
                    }
                }

                // Crear el registro de asistencia (siguiendo la estructura de tu AsistenciaController)
                $registro = RegistroAsistencia::create([
                    'usuario_id' => $docente->id,
                    'nro_documento' => $docente->numero_documento,
                    'fecha_hora' => $request->fecha_hora,
                    'tipo_verificacion' => $tipoVerificacion,
                    'estado' => 1, // Activo por defecto
                    'codigo_trabajo' => $request->codigo_trabajo,
                    'terminal_id' => $terminalId,
                    'sn_dispositivo' => $request->terminal_id ?? 'MANUAL',
                    'fecha_registro' => $request->fecha_hora,
                ]);

                return redirect()
                    ->route('asistencia-docente.create')
                    ->with('success', "Asistencia de {$request->estado} registrada correctamente para {$docente->nombre} {$docente->apellido_paterno}");

            } catch (\Exception $e) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Error al registrar la asistencia: ' . $e->getMessage()]);
            }
        }

        // Lógica existente para cuando viene tema_desarrollado sin estado (actualización de tema)
        if ($request->has('tema_desarrollado') && !$request->has('estado')) {
            $request->validate([
                'asistencia_id' => 'required|exists:asistencias_docentes,id',
                'tema_desarrollado' => 'required|string|max:500',
            ]);

            $asistencia = AsistenciaDocente::findOrFail($request->asistencia_id);
            $asistencia->update(['tema_desarrollado' => $request->tema_desarrollado]);

            return redirect()->back()->with('success', 'Tema desarrollado actualizado correctamente.');
        }

        // Lógica existente para registro completo con tema desarrollado
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'fecha_hora' => 'required|date',
            'estado' => 'required|in:entrada,salida',
            'tipo_verificacion' => 'nullable|string',
            'terminal_id' => 'nullable|string',
            'codigo_trabajo' => 'nullable|string',
            'tema_desarrollado' => 'required|string',
        ]);

        $fecha = Carbon::parse($request->fecha_hora);
        $diaSemana = strtolower($fecha->locale('es')->dayName);

        // Buscar un horario que la asistencia pueda corresponder, considerando la tolerancia de entrada
        $horario = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('dia_semana', $diaSemana)
            ->where(function ($q) use ($fecha) {
                // Condición 1: La asistencia está dentro del horario programado real
                $q->whereTime('hora_inicio', '<=', $fecha->format('H:i:s'))
                  ->whereTime('hora_fin', '>=', $fecha->format('H:i:s'));
            })
            ->orWhere(function ($q) use ($fecha) {
                // Condición 2: La asistencia está dentro de la ventana de tolerancia temprana antes de hora_inicio
                $q->whereTime('hora_inicio', '>=', $fecha->copy()->subMinutes(self::TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS)->format('H:i:s'))
                  ->whereTime('hora_inicio', '<=', $fecha->format('H:i:s'));
            })
            ->first();

        if (!$horario) {
            return redirect()->back()->withInput()->withErrors(['horario_id' => 'No existe un horario programado para la fecha y hora seleccionadas o está fuera del rango de tolerancia para la entrada.']);
        }

        AsistenciaDocente::updateOrInsert(
            [
                'docente_id' => $request->docente_id,
                'horario_id' => $horario->id,
                'fecha_hora' => $fecha,
                'estado' => $request->estado,
            ],
            [
                'tipo_verificacion' => $request->tipo_verificacion ?? 'manual',
                'tema_desarrollado' => $request->tema_desarrollado,
                'curso_id' => $horario->curso_id,
                'aula_id' => $horario->aula_id,
                'turno' => $horario->turno,
            ]
        );

        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia docente registrada correctamente.');
    }

    private function determinarEstado($tipoVerificacion)
    {
        return 'entrada';
    }

    /**
     * NUEVO: Obtiene los últimos registros procesados para mostrar en el sidebar
     */
    public function ultimasProcesadas()
    {
        try {
            $registros = RegistroAsistencia::select([
                'registros_asistencia.*',
                DB::raw("CONCAT(users.nombre, ' ', users.apellido_paterno, ' ', COALESCE(users.apellido_materno, '')) as docente_nombre")
            ])
            ->join('users', 'registros_asistencia.usuario_id', '=', 'users.id')
            ->whereHas('usuario.roles', function ($query) {
                $query->where('nombre', 'profesor');
            })
            ->orderBy('registros_asistencia.fecha_registro', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($registro) {
                // Determinar el estado basado en la hora (lógica mejorada)
                $hora = Carbon::parse($registro->fecha_registro)->format('H:i');
                $estado = $hora < '12:00' ? 'entrada' : 'salida';
                
                // Mapeo de tipos de verificación
                $tiposVerificacion = [
                    0 => 'biometrico',
                    1 => 'tarjeta',
                    2 => 'facial',
                    3 => 'codigo',
                    4 => 'manual'
                ];
                
                return [
                    'id' => $registro->id,
                    'docente_nombre' => $registro->docente_nombre,
                    'estado' => $estado,
                    'fecha_hora' => $registro->fecha_registro,
                    'tipo_verificacion' => $tiposVerificacion[$registro->tipo_verificacion] ?? 'manual',
                    'terminal_id' => $registro->terminal_id,
                ];
            });

            return response()->json([
                'success' => true,
                'registros' => $registros
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener registros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarTemaDesarrollado(Request $request)
    {
        $request->validate([
            'asistencia_id' => 'required|exists:asistencias_docentes,id',
            'tema_desarrollado' => 'required|string|max:500',
        ]);

        $asistencia = AsistenciaDocente::findOrFail($request->asistencia_id);
        $asistencia->update([
            'tema_desarrollado' => $request->tema_desarrollado,
            // No actualizamos fecha_hora ni estado aquí para mantener la flexibilidad
            // de solo actualizar el tema sin afectar la marcación original.
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tema desarrollado actualizado correctamente.'
        ]);
    }

    public function editar($id)
    {
        $asistencia = AsistenciaDocente::findOrFail($id);
        return view('asistencia-docente.editar', compact('asistencia'));
    }

    public function registrarTema(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|integer',
            'tema_desarrollado' => 'required|string|min:10|max:1000',
            'fecha_seleccionada' => 'required|date_format:Y-m-d', // Validar que la fecha venga en el formato correcto
        ]);
    
        $user = auth()->user();
        // Usar la fecha seleccionada del request en lugar de Carbon::today()
        $fechaParaRegistro = Carbon::parse($request->fecha_seleccionada); 
    
        // Primero, encontrar el horario específico solicitado por horario_id
        $horario = HorarioDocente::find($request->horario_id);
        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el horario seleccionado.',
            ], 404);
        }
    
        // Buscar un registro de AsistenciaDocente existente para la FECHA SELECCIONADA y este horario
        $asistencia = AsistenciaDocente::where('horario_id', $request->horario_id)
            ->where('docente_id', $user->id)
            ->whereDate('fecha_hora', $fechaParaRegistro->toDateString()) // Usar $fechaParaRegistro
            ->first();
    
        if (!$asistencia) {
            // Si no existe un registro de AsistenciaDocente, necesitamos encontrar la entrada biométrica.
            // La entrada biométrica debe haber ocurrido dentro de la ventana de tolerancia de entrada o el horario de clase.
            $horarioStartTime = Carbon::parse($horario->hora_inicio);
            $horarioEndTime = Carbon::parse($horario->hora_fin);
            $tolerantEntryStart = $horarioStartTime->copy()->subMinutes(self::TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS);
    
            $entrada = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaParaRegistro->toDateString()) // Usar $fechaParaRegistro
                // La hora de entrada biométrica debe estar dentro del inicio tolerante y el final real del horario.
                ->whereTime('fecha_registro', '>=', $tolerantEntryStart->format('H:i:s'))
                ->whereTime('fecha_registro', '<=', $horarioEndTime->format('H:i:s'))
                ->orderBy('fecha_registro', 'asc')
                ->first();
    
            if (!$entrada) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la asistencia biométrica para este horario en la fecha seleccionada dentro del rango de tolerancia. Marca tu entrada primero.',
                ], 404);
            }
    
            AsistenciaDocente::create([
                'docente_id' => $user->id,
                'horario_id' => $horario->id,
                'curso_id'   => $horario->curso_id,
                'aula_id'    => $horario->aula_id,
                'fecha_hora' => $entrada->fecha_registro, // Usar la hora de entrada biométrica
                'estado'     => 'entrada',
                'tipo_verificacion' => $entrada->tipo_verificacion ?? 'biometrico',
                'terminal_id'       => $entrada->terminal_id ?? null,
                'codigo_trabajo'    => $entrada->codigo_trabajo ?? null,
                'turno'      => $horario->turno,
                'tema_desarrollado' => $request->tema_desarrollado,
            ]);
        } else {
            // Si ya existe un registro de AsistenciaDocente, simplemente actualiza el tema
            $asistencia->tema_desarrollado = $request->tema_desarrollado;
            $asistencia->save();
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Tema desarrollado registrado correctamente.'
        ]);
    }

    public function exportar(Request $request)
    {
        $selectedDocenteId = $request->input('docente_id');
        $selectedMonth = $request->input('mes'); 
        $selectedYear = $request->input('anio');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $selectedCicloAcademico = $request->input('ciclo_academico');

        return Excel::download(
            new AsistenciasDocentesExport($selectedDocenteId, $selectedMonth, $selectedYear, $fechaInicio, $fechaFin, $selectedCicloAcademico), 
            'reporte_asistencia_docentes.xlsx'
        );
    }

    /**
     * NUEVO: Actualizar un registro de asistencia docente.
     */
    public function update(Request $request, $id)
    {
        $asistencia = RegistroAsistencia::findOrFail($id);

        $request->validate([
            'nro_documento' => 'required|string|max:20',
            'fecha_registro' => 'required|date',
            'tipo_verificacion' => 'required|integer',
            'estado' => 'required|boolean',
        ]);

        $asistencia->update([
            'nro_documento' => $request->nro_documento,
            'fecha_registro' => $request->fecha_registro,
            'tipo_verificacion' => $request->tipo_verificacion,
            'estado' => $request->estado,
            'codigo_trabajo' => $request->codigo_trabajo,
            'terminal_id' => $request->terminal_id,
            'sn_dispositivo' => $request->sn_dispositivo,
        ]);

        return redirect()->route('asistencia-docente.index')->with('success', 'Registro de asistencia docente actualizado exitosamente.');
    }

    /**
     * NUEVO: Eliminar un registro de asistencia docente.
     */
    public function destroy($id)
    {
        try {
            $asistencia = RegistroAsistencia::findOrFail($id);
            $asistencia->delete();

            return redirect()->route('asistencia-docente.index')->with('success', 'Registro eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el registro: ' . $e->getMessage()]);
        }
    }
}