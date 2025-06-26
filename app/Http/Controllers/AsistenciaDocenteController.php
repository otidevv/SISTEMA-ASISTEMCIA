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

// Importa la clase Excel de Maatwebsite
use Maatwebsite\Excel\Facades\Excel;
// Importa tu clase de exportación
use App\Exports\AsistenciasDocentesExport; 

class AsistenciaDocenteController extends Controller
{
    // La tarifa por minuto fija se remueve si es dinámica por docente.
    // const TARIFA_POR_MINUTO = 3.00; 

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
        $selectedMonth = $request->input('mes'); 
        $selectedYear = $request->input('anio');

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

                $horario = HorarioDocente::where('docente_id', $asistencia->usuario->id)
                    ->where('dia_semana', $nombreDia)
                    ->whereTime('hora_inicio', '<=', $fechaAsistencia->format('H:i:s'))
                    ->whereTime('hora_fin', '>=', $fechaAsistencia->format('H:i:s'))
                    ->with('curso')
                    ->first();

                $asistencia->horario = $horario;

                if ($horario) {
                    $horaAsistencia = Carbon::parse($horario->hora_inicio);
                    $horaFin = Carbon::parse($horario->hora_fin);

                    $diffInicio = abs($fechaAsistencia->diffInMinutes($horaAsistencia));
                    $diffFin = abs($fechaAsistencia->diffInMinutes($horaFin));

                    $asistencia->tipo_asistencia = $diffInicio < $diffFin ? 'entrada' : 'salida';
                } else {
                    $asistencia->tipo_asistencia = $fechaAsistencia->hour < 12 ? 'entrada' : 'salida';
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

    public function create()
    {
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->get();

        return view('asistencia-docente.create', compact('docentes'));
    }

    public function store(Request $request)
    {
        if ($request->has('tema_desarrollado') && !$request->has('estado')) {
            $request->validate([
                'asistencia_id' => 'required|exists:asistencias_docentes,id',
                'tema_desarrollado' => 'required|string|max:500',
            ]);

            $asistencia = AsistenciaDocente::findOrFail($request->asistencia_id);
            $asistencia->update(['tema_desarrollado' => $request->tema_desarrollado]);

            return redirect()->back()->with('success', 'Tema desarrollado actualizado correctamente.');
        }

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
        $hora = $fecha->format('H:i:s');

        $horario = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('dia_semana', $nombreDia)
            ->whereTime('hora_inicio', '<=', $fecha->format('H:i:s'))
            ->whereTime('hora_fin', '>=', $fecha->format('H:i:s'))
            ->first();

        if (!$horario) {
            return redirect()->back()->withInput()->withErrors(['horario_id' => 'No existe un horario programado para la fecha y hora seleccionadas.']);
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

    public function actualizarTemaDesarrollado(Request $request)
    {
        $request->validate([
            'asistencia_id' => 'required|exists:asistencias_docentes,id',
            'tema_desarrollado' => 'required|string|max:500',
        ]);

        $asistencia = AsistenciaDocente::findOrFail($request->asistencia_id);
        $asistencia->update([
            'tema_desarrollado' => $request->tema_desarrollado,
            'fecha_hora' => now(),
            'estado' => 'entrada',
        ]);

        return redirect()->back()->with('success', 'Tema desarrollado y hora actualizada correctamente.');
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
            'tema_desarrollado' => 'required|string|min:10|max:1000'
        ]);
    
        $user = auth()->user();
    
        $asistencia = AsistenciaDocente::where('horario_id', $request->horario_id)
            ->where('docente_id', $user->id)
            ->whereDate('fecha_hora', now()->toDateString())
            ->first();
    
        if (!$asistencia) {
            $horario = HorarioDocente::find($request->horario_id);
            if (!$horario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el horario seleccionado.',
                ], 404);
            }
    
            $entrada = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', now()->toDateString())
                ->whereTime('fecha_registro', '>=', $horario->hora_inicio)
                ->whereTime('fecha_registro', '<=', $horario->hora_fin)
                ->orderBy('fecha_registro', 'asc')
                ->first();
    
            if (!$entrada) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la asistencia biométrica del día para este horario. Marca tu entrada primero.',
                ], 404);
            }
    
            AsistenciaDocente::create([
                'docente_id' => $user->id,
                'horario_id' => $horario->id,
                'curso_id'   => $horario->curso_id,
                'aula_id'    => $horario->aula_id,
                'fecha_hora' => $entrada->fecha_registro,
                'estado'     => 'entrada',
                'tipo_verificacion' => $entrada->tipo_verificacion ?? 'biometrico',
                'terminal_id'       => $entrada->terminal_id ?? null,
                'codigo_trabajo'    => $entrada->codigo_trabajo ?? null,
                'turno'      => $horario->turno,
                'tema_desarrollado' => $request->tema_desarrollado,
            ]);
        } else {
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
}