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
        $selectedMonth = $request->input('mes');
        $selectedYear = $request->input('anio');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $selectedCicloAcademico = $request->input('ciclo_academico');

        // Obtener todos los docentes para el filtro de selección
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'nombre', 'apellido_paterno', 'numero_documento')->get();

        // Obtener Ciclos Académicos de la base de datos usando tu modelo Ciclo
        $ciclosAcademicos = Ciclo::orderBy('nombre', 'desc')->pluck('nombre', 'codigo')->toArray();

        // 2. NUEVA LÓGICA DE DETERMINACIÓN DE FECHAS - PRIORIDAD AL CICLO
        $startDate = null;
        $endDate = null;

        // PRIORIDAD MÁXIMA: Si hay ciclo académico seleccionado, usar SUS fechas
        if ($selectedCicloAcademico) {
            $ciclo = Ciclo::where('codigo', $selectedCicloAcademico)->first();
            if ($ciclo) {
                $cicloStartDate = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
                $cicloEndDate = Carbon::parse($ciclo->fecha_fin)->endOfDay();
                
                // Si NO hay filtros adicionales, usar TODO el ciclo académico
                if (!$fechaInicio && !$fechaFin && !$selectedMonth && !$selectedYear) {
                    $startDate = $cicloStartDate;
                    $endDate = $cicloEndDate;
                }
                // Si hay fechas específicas, validar que estén dentro del ciclo
                elseif ($fechaInicio && $fechaFin) {
                    $customStart = Carbon::parse($fechaInicio)->startOfDay();
                    $customEnd = Carbon::parse($fechaFin)->endOfDay();
                    
                    $startDate = $customStart->max($cicloStartDate);
                    $endDate = $customEnd->min($cicloEndDate);
                }
                // Si hay mes/año específico, validar que esté dentro del ciclo
                elseif ($selectedMonth && $selectedYear) {
                    $monthStart = Carbon::createFromDate($selectedYear, (int)$selectedMonth, 1)->startOfDay();
                    $monthEnd = Carbon::createFromDate($selectedYear, (int)$selectedMonth, 1)->endOfMonth()->endOfDay();
                    
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
        elseif ($fechaInicio && $fechaFin) {
            $startDate = Carbon::parse($fechaInicio)->startOfDay();
            $endDate = Carbon::parse($fechaFin)->endOfDay();
        }
        // Si NO hay ciclo académico pero hay mes/año específico
        elseif ($selectedMonth && $selectedYear) {
            $startDate = Carbon::createFromDate($selectedYear, (int)$selectedMonth, 1)->startOfDay();
            $endDate = Carbon::createFromDate($selectedYear, (int)$selectedMonth, 1)->endOfMonth()->endOfDay();
        }
        // Fallback final: últimos 30 días
        else {
            $endDate = Carbon::today()->endOfDay();
            $startDate = $endDate->copy()->subDays(30)->startOfDay();
        }

        // 3. Construir la consulta base para asistencias docentes, aplicando filtros (TU LÓGICA EXISTENTE)
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
        
        if ($selectedCicloAcademico) {
            $baseQuery->whereHas('horario.ciclo', function ($query) use ($selectedCicloAcademico) {
                $query->where('codigo', $selectedCicloAcademico);
            });
        }

        // 4. Calcular estadísticas generales (TU LÓGICA EXISTENTE)
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

        // Ajustar fechas del gráfico para el rango de fechas o mes/año (TU LÓGICA EXISTENTE)
        $fechasCompletasMes = [];
        if ($fechaInicio && $fechaFin) {
            $currentDate = Carbon::parse($fechaInicio)->startOfDay();
            $endDateLoop = Carbon::parse($fechaFin)->endOfDay();
            while ($currentDate->lte($endDateLoop)) {
                $fechasCompletasMes[$currentDate->format('Y-m-d')] = $asistenciaSemana[$currentDate->format('Y-m-d')] ?? 0;
                $currentDate->addDay();
            }
        } elseif (!empty($selectedMonth) && !empty($selectedYear)) { 
            $diasEnMes = Carbon::createFromDate((int)$selectedYear, (int)$selectedMonth, 1)->daysInMonth;
            for ($i = 1; $i <= $diasEnMes; $i++) {
                $fecha = Carbon::createFromDate((int)$selectedYear, (int)$selectedMonth, $i)->format('Y-m-d');
                $fechasCompletasMes[$fecha] = $asistenciaSemana[$fecha] ?? 0;
            }
        } else {
            // Para otros casos, mantener los datos como están
            $fechasCompletasMes = $asistenciaSemana;
        }
        $asistenciaSemana = $fechasCompletasMes;

        // 5. Asistencia por docente (TU LÓGICA EXISTENTE MEJORADA)
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

        // Calcular horas_dictadas y monto_total por docente (TU LÓGICA EXISTENTE)
        $asistenciaPorDocente->transform(function ($item) use ($fechaInicio, $fechaFin, $selectedMonth, $selectedYear, $selectedCicloAcademico) {
            $docenteAsistenciasQuery = AsistenciaDocente::where('docente_id', $item->docente_id)
                ->orderBy('fecha_hora', 'asc');

            if ($fechaInicio && $fechaFin) {
                $docenteAsistenciasQuery->whereBetween('fecha_hora', [Carbon::parse($fechaInicio)->startOfDay(), Carbon::parse($fechaFin)->endOfDay()]);
            } elseif (!empty($selectedMonth) && !empty($selectedYear)) {
                $docenteAsistenciasQuery->whereMonth('fecha_hora', $selectedMonth)
                                        ->whereYear('fecha_hora', $selectedYear);
            }
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

                if ($salida && $salida->horas_dictadas !== null) { 
                    $horasDictadasSesion = $salida->horas_dictadas;
                } elseif ($entrada && $entrada->horas_dictadas !== null) {
                    $horasDictadasSesion = $entrada->horas_dictadas;
                } else {
                    if ($entrada && $salida && Carbon::parse($salida->fecha_hora)->greaterThan(Carbon::parse($entrada->fecha_hora))) {
                        $minutosDictados = Carbon::parse($salida->fecha_hora)->diffInMinutes(Carbon::parse($entrada->fecha_hora));
                        $horasDictadasSesion = round($minutosDictados / 60, 2);
                    }
                }
                
                $tarifaPorHoraAplicable = 0;
                if ($horasDictadasSesion > 0 && $entrada) {
                    $pagoDocente = PagoDocente::where('docente_id', $item->docente_id)
                        ->whereDate('fecha_inicio', '<=', $entrada->fecha_hora)
                        ->whereDate('fecha_fin', '>=', $entrada->fecha_hora)
                        ->first();
                    if ($pagoDocente) {
                        $tarifaPorHoraAplicable = $pagoDocente->tarifa_por_hora;
                    }
                }
                $montoTotalSesion = $horasDictadasSesion * $tarifaPorHoraAplicable;

                $totalHorasDictadas += $horasDictadasSesion;
                $totalMontoPago += $montoTotalSesion;
            }
            $item->total_horas = $totalHorasDictadas;
            $item->total_pagos = $totalMontoPago;
            return $item;
        });

        // 6. NUEVA LÓGICA PARA DATOS DETALLADOS - USANDO NUEVA METODOLOGÍA
        $processedDetailedAsistencias = [];
        
        // Obtener docentes según filtros
        $docentesQuery = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        });
        if ($selectedDocenteId) {
            $docentesQuery->where('id', $selectedDocenteId);
        }
        $docentesParaProcesar = $docentesQuery->get();

        foreach ($docentesParaProcesar as $docente) {
            $docenteSessions = [];

            // Iterar día por día dentro del rango
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $diaSemanaNombre = strtolower($currentDate->locale('es')->dayName);

                // Obtener sesiones programadas para este día
                $horariosQuery = HorarioDocente::where('docente_id', $docente->id)
                    ->where('dia_semana', $diaSemanaNombre)
                    ->with(['curso', 'aula', 'ciclo']);

                // Aplicar filtro de ciclo SOLO si está especificado
                if ($selectedCicloAcademico) {
                    $horariosQuery->whereHas('ciclo', function ($q) use ($selectedCicloAcademico) {
                        $q->where('codigo', $selectedCicloAcademico);
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

                    $sessionData = $this->processSessionForReports($horario, $currentDate, $registrosBiometricosDelDia, $docente);
                    
                    if ($sessionData) {
                        $docenteSessions[] = $sessionData;
                    }
                }
                
                $currentDate->addDay();
            }

            // Estructurar datos por docente, mes y semana
            if (!empty($docenteSessions)) {
                $processedDetailedAsistencias[$docente->id] = $this->structureDocenteDataForReports($docente, $docenteSessions);
            }
        }

        // CAMBIO CLAVE: Retornar la vista correcta
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
            'processedDetailedAsistencias' // NUEVO: Datos detallados para la tabla
        ));
    }

    public function index(Request $request)
    {
        // 1. Obtener ciclos y determinar el ciclo seleccionado
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        $cicloSeleccionadoId = $request->input('ciclo_id');
        $cicloActivo = $ciclos->firstWhere('es_activo', true);

        if ($cicloSeleccionadoId) {
            $cicloSeleccionado = $ciclos->find($cicloSeleccionadoId);
        } else {
            $cicloSeleccionado = $cicloActivo;
        }

        // 2. Obtener otros filtros
        $fecha = $request->get('fecha');
        $documento = $request->get('documento');

        // 3. Construir la consulta base
        $docentesDocumentos = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->pluck('numero_documento')->toArray();

        $query = RegistroAsistencia::with(['usuario.roles'])
            ->whereIn('nro_documento', $docentesDocumentos);

        // 4. Aplicar filtros de fecha (con prioridad para el ciclo)
        if ($fecha) {
            // Si se especifica una fecha, se usa esa fecha
            $query->whereDate('fecha_registro', $fecha);
        } elseif ($cicloSeleccionado) {
            // Si no hay fecha, pero sí ciclo, usar el rango de fechas del ciclo
            $query->whereBetween('fecha_registro', [$cicloSeleccionado->fecha_inicio, $cicloSeleccionado->fecha_fin]);
        }
        // Si no hay ni fecha ni ciclo, no se aplica filtro de fecha (se podría añadir un default si se quiere)

        if ($documento) {
            $query->where('nro_documento', 'like', '%' . $documento . '%');
        }

        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'numero_documento', 'nombre', 'apellido_paterno')->get();

        // (La lógica de transformación de la colección de asistencias permanece igual)
        $asistencias->getCollection()->transform(function ($asistencia) {
            if ($asistencia->usuario) {
                $fechaAsistencia = Carbon::parse($asistencia->fecha_hora);
                $diaSemana = $fechaAsistencia->dayOfWeek;

                $diasSemana = [0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'];
                $nombreDia = $diasSemana[$diaSemana];

                $horario = HorarioDocente::where('docente_id', $asistencia->usuario->id)
                    ->where('dia_semana', $nombreDia)
                    ->where(function ($q) use ($fechaAsistencia) {
                        $q->whereTime('hora_inicio', '<=', $fechaAsistencia->format('H:i:s'))
                          ->whereTime('hora_fin', '>=', $fechaAsistencia->format('H:i:s'));
                    })
                    ->orWhere(function ($q) use ($fechaAsistencia) {
                        $q->whereTime('hora_inicio', '>=', $fechaAsistencia->copy()->subMinutes(self::TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS)->format('H:i:s'))
                          ->whereTime('hora_inicio', '<=', $fechaAsistencia->format('H:i:s'));
                    })
                    ->with('curso')
                    ->first();

                $asistencia->horario = $horario;

                if ($horario) {
                    $horaAsistenciaProgramada = Carbon::parse($horario->hora_inicio);
                    $horaFinProgramada = Carbon::parse($horario->hora_fin);

                    $diffInicio = abs($fechaAsistencia->diffInMinutes($horaAsistenciaProgramada));
                    $diffFin = abs($fechaAsistencia->diffInMinutes($horaFinProgramada));

                    $asistencia->tipo_asistencia = $diffInicio < $diffFin ? 'entrada' : 'salida';

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

        // 5. Pasar todos los datos necesarios a la vista
        return view('asistencia-docente.index', compact('asistencias', 'docentes', 'fecha', 'documento', 'ciclos', 'cicloSeleccionado'));
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

    public function edit($id)
    {
        // 1. Obtener el registro de asistencia
        $asistencia = RegistroAsistencia::findOrFail($id);
        
        // 2. Obtener TODOS los docentes para el select
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'nombre', 'apellido_paterno', 'apellido_materno', 'numero_documento')
          ->orderBy('apellido_paterno', 'asc')
          ->get();
    
        // 3. DEBUGGING MEJORADO
        \Log::info('=== EDITANDO ASISTENCIA ===');
        \Log::info('ID: ' . $id);
        \Log::info('Usuario ID: ' . ($asistencia->usuario_id ?? 'NULL'));
        \Log::info('Documento: ' . $asistencia->nro_documento);
        \Log::info('Total docentes: ' . $docentes->count());
        
        // Verificar si existe el docente específico
        $docenteEncontrado = $docentes->where('numero_documento', $asistencia->nro_documento)->first();
        if ($docenteEncontrado) {
            \Log::info('✅ DOCENTE ENCONTRADO: ID=' . $docenteEncontrado->id . ', NOMBRE=' . $docenteEncontrado->nombre . ' ' . $docenteEncontrado->apellido_paterno);
        } else {
            \Log::info('❌ DOCENTE NO ENCONTRADO para documento: ' . $asistencia->nro_documento);
            
            // Mostrar los primeros 5 docentes para debugging
            \Log::info('Primeros 5 docentes disponibles:');
            foreach ($docentes->take(5) as $doc) {
                \Log::info('  - ID=' . $doc->id . ', DOC=' . $doc->numero_documento . ', NOMBRE=' . $doc->nombre . ' ' . $doc->apellido_paterno);
            }
        }
        
        // ✅ 4. CAMBIO CRÍTICO: usar 'edit' en lugar de 'editar'
        return view('asistencia-docente.edit', compact('asistencia', 'docentes'));
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

        // Manual casting for 'estado' and 'tipo_verificacion' before validation
        if ($request->has('estado')) {
            if ($request->estado === 'entrada') {
                $request->merge(['estado' => 1]);
            } elseif ($request->estado === 'salida') {
                $request->merge(['estado' => 0]);
            }
        }

        if ($request->has('tipo_verificacion')) {
            $tipoVerificacionMap = [
                'biometrico' => 0,
                'tarjeta' => 1,
                'facial' => 2,
                'codigo' => 3,
                'manual' => 4
            ];
            $request->merge(['tipo_verificacion' => $tipoVerificacionMap[$request->tipo_verificacion] ?? $request->tipo_verificacion]);
        }

        // Si nro_documento no viene en la request, intentar obtenerlo del docente_id
        if (!$request->has('nro_documento') || empty($request->nro_documento)) {
            $docente = User::find($request->docente_id);
            if ($docente) {
                $request->merge(['nro_documento' => $docente->numero_documento]);
            }
        }

        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'nro_documento' => 'required|string|max:20',
            'fecha_hora' => 'required|date',
            'tipo_verificacion' => 'required|numeric',
            'estado' => 'required|in:0,1',
        ]);

        $asistencia->update([
            'usuario_id' => $request->docente_id, // Update usuario_id
            'nro_documento' => $request->nro_documento,
            'fecha_registro' => $request->fecha_hora, // Mapear fecha_hora del request a fecha_registro del modelo
            'tipo_verificacion' => $request->tipo_verificacion,
            'estado' => $request->estado,
            'codigo_trabajo' => $request->codigo_trabajo,
            'terminal_id' => $request->terminal_id,
            'sn_dispositivo' => $request->terminal_id, // Usar terminal_id como sn_dispositivo si no hay otro
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

    /**
     * NUEVO: Procesa una sesión individual para reportes
     */
    private function processSessionForReports($horario, $currentDate, $registrosBiometricosDelDia, $docente)
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
                    $horarioInicioHoy->copy()->subMinutes(self::TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS),
                    $horarioInicioHoy->copy()->addMinutes(120)
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
        
        // Calcular horas
        $horasProgramadas = $horaInicioProgramada->diffInHours($horaFinProgramada, true);
        $horasDictadas = $horasProgramadas;
        $estadoTexto = 'PENDIENTE';

        $cursoNombre = $horario->curso->nombre ?? 'N/A';
        $aulaNombre = $horario->aula->nombre ?? 'N/A';
        $turnoNombre = $horario->turno ?? 'N/A';

        // Determinar estado
        if ($entradaBiometrica && $salidaBiometrica) {
            $estadoTexto = 'COMPLETADA';
            $entradaCarbon = Carbon::parse($entradaBiometrica->fecha_registro);
            $salidaCarbon = Carbon::parse($salidaBiometrica->fecha_registro);

            // --- INICIO DE LA LÓGICA DE RECESO PARA REPORTES ---
            // Determinar la hora de inicio efectiva para el cálculo, respetando la tolerancia de tardanza.
            $tardinessThreshold = $horarioInicioHoy->copy()->addMinutes(self::TOLERANCIA_TARDE_MINUTOS);
            
            $effectiveStartTime;
            // Si la entrada es ANTES o DENTRO del umbral de tardanza, se usa la hora de inicio programada.
            if ($entradaCarbon->lessThanOrEqualTo($tardinessThreshold)) {
                $effectiveStartTime = $horarioInicioHoy;
            } else {
                // Si la entrada es DESPUÉS del umbral, se usa la hora de entrada real (se aplica descuento).
                $effectiveStartTime = $entradaCarbon;
            }
            
            $duracionBruta = $effectiveStartTime->diffInMinutes($salidaCarbon);

            // Receso de Mañana
            $recesoMananaInicio = $currentDate->copy()->setTime(10, 0, 0);
            $recesoMananaFin = $currentDate->copy()->setTime(10, 30, 0);
            $minutosRecesoManana = 0;
            if ($entradaCarbon < $recesoMananaFin && $salidaCarbon > $recesoMananaInicio) {
                $superposicionInicio = $entradaCarbon->max($recesoMananaInicio);
                $superposicionFin = $salidaCarbon->min($recesoMananaFin);
                if ($superposicionFin > $superposicionInicio) {
                    $minutosRecesoManana = $superposicionInicio->diffInMinutes($superposicionFin);
                }
            }

            // Receso de Tarde
            $recesoTardeInicio = $currentDate->copy()->setTime(18, 0, 0);
            $recesoTardeFin = $currentDate->copy()->setTime(18, 30, 0);
            $minutosRecesoTarde = 0;
            if ($entradaCarbon < $recesoTardeFin && $salidaCarbon > $recesoTardeInicio) {
                $superposicionInicio = $entradaCarbon->max($recesoTardeInicio);
                $superposicionFin = $salidaCarbon->min($recesoTardeFin);
                if ($superposicionFin > $superposicionInicio) {
                    $minutosRecesoTarde = $superposicionInicio->diffInMinutes($superposicionFin);
                }
            }

            $minutosDictados = $duracionBruta - $minutosRecesoManana - $minutosRecesoTarde;
            // --- FIN DE LA LÓGICA DE RECESO ---

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

        // Calcular pago
        $montoTotal = 0;
        $pagoDocente = PagoDocente::where('docente_id', $docente->id)
            ->whereDate('fecha_inicio', '<=', $currentDate)
            ->whereDate('fecha_fin', '>=', $currentDate)
            ->first();
        
        if ($pagoDocente) {
            $montoTotal = $horasDictadas * $pagoDocente->tarifa_por_hora;
        }

        // FORMATO DE HORAS CORREGIDO - ESTE ERA EL PROBLEMA
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
            'tiene_registros' => ($entradaBiometrica && $salidaBiometrica) ? 'SI' : 'NO'
        ];
    }

    /**
     * NUEVO: Estructurar datos por docente para reportes
     */
    private function structureDocenteDataForReports($docente, $sessions)
    {
        // Agrupar sesiones por mes y semana
        $groupedData = [];
        $totalHoras = 0;
        $totalPagos = 0;

        foreach ($sessions as $session) {
            $mes = $session['mes'];
            $semana = $session['semana'];
            
            if (!isset($groupedData[$mes])) {
                $groupedData[$mes] = [
                    'month_name' => $mes,
                    'weeks' => [],
                    'total_horas' => 0,
                    'total_pagos' => 0,
                    'rowspan' => 0
                ];
            }
            
            if (!isset($groupedData[$mes]['weeks'][$semana])) {
                $groupedData[$mes]['weeks'][$semana] = [
                    'week_number' => sprintf('%02d', $semana),
                    'details' => [],
                    'total_horas' => 0,
                    'total_pagos' => 0,
                    'rowspan' => 0
                ];
            }
            
            $groupedData[$mes]['weeks'][$semana]['details'][] = $session;
            $groupedData[$mes]['weeks'][$semana]['total_horas'] += $session['horas_dictadas'];
            $groupedData[$mes]['weeks'][$semana]['total_pagos'] += $session['pago'];
            $groupedData[$mes]['weeks'][$semana]['rowspan']++;
            
            $groupedData[$mes]['total_horas'] += $session['horas_dictadas'];
            $groupedData[$mes]['total_pagos'] += $session['pago'];
            $groupedData[$mes]['rowspan']++;
            
            $totalHoras += $session['horas_dictadas'];
            $totalPagos += $session['pago'];
        }

        // Calcular rowspan total para el docente
        $totalRowspan = 0;
        foreach ($groupedData as $monthData) {
            $totalRowspan += $monthData['rowspan'];
        }

        return [
            'docente_info' => $docente,
            'months' => $groupedData,
            'total_horas' => $totalHoras,
            'total_pagos' => $totalPagos,
            'rowspan' => $totalRowspan
        ];
    }
}