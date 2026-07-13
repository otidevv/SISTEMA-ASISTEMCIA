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
    use \App\Http\Controllers\Traits\HandlesSaturdayRotation;
    use \App\Http\Controllers\Traits\TeacherDashboardHelpers;
    use \App\Http\Controllers\Traits\ProcessesTeacherSessions;
    // La tarifa por minuto fija se remueve si es dinámica por docente.
    // const TARIFA_POR_MINUTO = 3.00; 

    // Tolerancia en minutos para la entrada anticipada (ej. 15 minutos antes de las 7:00 AM, se puede marcar desde las 6:45 AM)
    const TOLERANCIA_ENTRADA_ANTICIPADA_MINUTOS = 15; 
    // Tolerancia en minutos para considerar tardanza (ej. si la hora de inicio es 7:00 AM, la tardanza es a partir de las 7:05 AM)
    const TOLERANCIA_TARDE_MINUTOS = 5; 

    public function reports(Request $request)
    {
        return view('asistencia-docente.reportes', $this->getReportData($request));
    }

    /**
     * Obtiene los datos procesados para los reportes de asistencia docente
     * (Lógica extraída para reuso en web y PDF)
     */
    private function getReportData(Request $request)
    {
        // 1. Obtener parámetros de filtrado desde la URL
        $selectedDocenteId = $request->input('docente_id');
        $selectedMonth = $request->input('mes');
        $selectedYear = $request->input('anio');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $selectedCicloAcademico = $request->input('ciclo_academico');

        // Obtener Ciclos Académicos de la base de datos usando tu modelo Ciclo
        $ciclosAcademicos = Ciclo::orderBy('nombre', 'desc')->pluck('nombre', 'codigo')->toArray();
        
        // NUEVO: Determinar el ciclo a usar para filtrar docentes
        $cicloParaFiltroDocentes = $selectedCicloAcademico;
        if (!$cicloParaFiltroDocentes) {
            $cicloActivo = Ciclo::where('es_activo', true)
                ->orderBy('programa_id', 'asc') // Priorizar CEPRE
                ->first();
            $cicloParaFiltroDocentes = $cicloActivo?->codigo;
        }
        
        // MEJORADO: Obtener solo docentes con carga horaria en el ciclo seleccionado/activo
        $docentesQuery = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->select('id', 'nombre', 'apellido_paterno', 'apellido_materno', 'numero_documento');
        
        if ($cicloParaFiltroDocentes) {
            $docentesQuery->whereHas('horarios.ciclo', function ($query) use ($cicloParaFiltroDocentes) {
                $query->where('codigo', $cicloParaFiltroDocentes);
            });
        }
        
        // Ordenar alfabéticamente para facilitar búsqueda
        $docentes = $docentesQuery->orderBy('apellido_paterno')->orderBy('nombre')->get();

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
        // NUEVO: Fallback al ciclo activo en lugar de últimos 30 días
        else {
            $cicloActivo = Ciclo::where('es_activo', true)
                ->orderBy('programa_id', 'asc')
                ->first();
            if ($cicloActivo) {
                $startDate = Carbon::parse($cicloActivo->fecha_inicio)->startOfDay();
                $endDate = Carbon::parse($cicloActivo->fecha_fin)->endOfDay();
                // Auto-seleccionar el ciclo activo para que aparezca en el filtro
                $selectedCicloAcademico = $cicloActivo->codigo;
                // NUEVO: Mostrar las fechas del ciclo en los campos de fecha
                $fechaInicio = $startDate->toDateString();
                $fechaFin = $endDate->toDateString();
            } else {
                // Si no hay ciclo activo, usar últimos 30 días como último recurso
                $endDate = Carbon::today()->endOfDay();
                $startDate = $endDate->copy()->subDays(30)->startOfDay();
                // Mostrar estas fechas también
                $fechaInicio = $startDate->toDateString();
                $fechaFin = $endDate->toDateString();
            }
        }

        // 3. Construir la consulta base para asistencias docentes, aplicando filtros
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

        // 4. Calcular estadísticas generales
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
            $fechasCompletasMes = $asistenciaSemana;
        }
        $asistenciaSemana = $fechasCompletasMes;

        // 5. Asistencia por docente (DETERMINACIÓN INICIAL PARA REPORTE WEB)
        // Se recalculará después del procesamiento detallado para asegurar consistencia total
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

        // 6. OPTIMIZACIÓN: LÓGICA PARA DATOS DETALLADOS (POR LOTES)
        $processedDetailedAsistencias = [];
        $cicloActivoParaRotacion = Ciclo::where('es_activo', true)
            ->orderBy('programa_id', 'asc')
            ->first();
        
        $docentesQuery = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        });
        
        if ($selectedDocenteId) {
            $docentesQuery->where('id', $selectedDocenteId);
        }
        
        $cicloParaFiltrar = $selectedCicloAcademico;
        if (!$cicloParaFiltrar && $cicloActivoParaRotacion) {
            $cicloParaFiltrar = $cicloActivoParaRotacion->codigo;
        }
        
        if ($cicloParaFiltrar) {
            $docentesQuery->whereHas('horarios.ciclo', function ($q) use ($cicloParaFiltrar) {
                $q->where('codigo', $cicloParaFiltrar);
            });
        }
        
        $docentesParaProcesar = $docentesQuery->get();
        if ($docentesParaProcesar->isEmpty()) {
             return [
                'totalRegistrosPeriodo' => $totalRegistrosPeriodo, 
                'asistenciaSemana' => $asistenciaSemana, 
                'asistenciaPorDocente' => $asistenciaPorDocente, 
                'docentes' => $docentes,
                'ciclosAcademicos' => $ciclosAcademicos, 
                'selectedDocenteId' => $selectedDocenteId, 
                'selectedMonth' => $selectedMonth,    
                'selectedYear' => $selectedYear,     
                'fechaInicio' => $fechaInicio,      
                'fechaFin' => $fechaFin,         
                'selectedCicloAcademico' => $selectedCicloAcademico,
                'processedDetailedAsistencias' => [],
                'startDate' => $startDate,
                'endDate' => $endDate
            ];
        }

        $idsDocentes = $docentesParaProcesar->pluck('id')->toArray();
        $docsDocentes = $docentesParaProcesar->pluck('numero_documento')->filter()->toArray();

        // ⚡ BULK FETCH: Todos los horarios de los docentes relevantes
        $horariosQuery = HorarioDocente::whereIn('docente_id', $idsDocentes)->with(['curso', 'aula', 'ciclo']);
        if ($selectedCicloAcademico) {
            $horariosQuery->whereHas('ciclo', function ($q) use ($selectedCicloAcademico) {
                $q->where('codigo', $selectedCicloAcademico);
            });
        }
        $todosHorarios = $horariosQuery->get()->groupBy('docente_id');

        // ⚡ BULK FETCH: Todos los registros biométricos del rango para todos los docentes
        $todosRegistros = RegistroAsistencia::whereIn('nro_documento', $docsDocentes)
            ->whereBetween('fecha_registro', [$startDate, $endDate])
            ->orderBy('fecha_registro', 'asc')
            ->get()
            ->groupBy('nro_documento');

        // ⚡ OPTIMIZACIÓN: precargar en bloque las asistencias procesadas (tema) y cachear tarifas.
        // Evita una consulta por cada sesión dentro del bucle (eran miles en un ciclo completo).
        $this->reiniciarCacheSesiones();
        $this->precargarAsistenciasProcesadas($idsDocentes, $startDate, $endDate);

        foreach ($docentesParaProcesar as $docente) {
            $docenteSessions = [];
            $horariosDocente = $todosHorarios->get($docente->id, collect());
            $registrosDocente = $todosRegistros->get($docente->numero_documento, collect());
            
            $registrosPorFecha = $registrosDocente->groupBy(function($item) {
                return Carbon::parse($item->fecha_registro)->toDateString();
            });

            $currentDateLoop = $startDate->copy();
            while ($currentDateLoop->lte($endDate)) {
                $fechaString = $currentDateLoop->toDateString();
                $registrosBiometricosDelDia = $registrosPorFecha->get($fechaString, collect([]));

                foreach ($horariosDocente as $horario) {
                    if (!$horario || !$horario->hora_inicio || !$horario->hora_fin) continue;
                    
                    // Obtener el día que le corresponde a este horario según SU ciclo
                    $diaSemanaNombre = $horario->ciclo ? $horario->ciclo->getDiaHorarioParaFecha($currentDateLoop) : $this->getDiaHorarioParaFecha($currentDateLoop);
                    
                    if (strtolower($horario->dia_semana) === strtolower($diaSemanaNombre)) {
                        $sessionData = $this->processSessionForReports($horario, $currentDateLoop, $registrosBiometricosDelDia, $docente);
                        if ($sessionData) $docenteSessions[] = $sessionData;
                    }
                }
                $currentDateLoop->addDay();
            }

            if (!empty($docenteSessions)) {
                $processedDetailedAsistencias[$docente->id] = $this->structureDocenteDataForReports($docente, $docenteSessions);
            }
        }

        // ⚡ RECÁLCULO DE ASISTENCIA POR DOCENTE PARA CONSISTENCIA TOTAL
        // Actualizamos $asistenciaPorDocente con los valores procesados en la lógica detallada
        $asistenciaPorDocente->transform(function ($item) use ($processedDetailedAsistencias) {
            if (isset($processedDetailedAsistencias[$item->docente_id])) {
                $docData = $processedDetailedAsistencias[$item->docente_id];
                $item->total_horas = $docData['total_horas'];
                $item->total_pagos = $docData['total_pagos'];
            } else {
                $item->total_horas = 0;
                $item->total_pagos = 0;
            }
            return $item;
        });

        uasort($processedDetailedAsistencias, function($a, $b) {
            $nameA = trim(($a['docente_info']->apellido_paterno ?? '') . ' ' . ($a['docente_info']->nombre ?? ''));
            $nameB = trim(($b['docente_info']->apellido_paterno ?? '') . ' ' . ($b['docente_info']->nombre ?? ''));
            return strcasecmp($nameA, $nameB);
        });

        return [
            'totalRegistrosPeriodo' => $totalRegistrosPeriodo, 
            'asistenciaSemana' => $asistenciaSemana, 
            'asistenciaPorDocente' => $asistenciaPorDocente, 
            'docentes' => $docentes,
            'ciclosAcademicos' => $ciclosAcademicos, 
            'selectedDocenteId' => $selectedDocenteId, 
            'selectedMonth' => $selectedMonth,    
            'selectedYear' => $selectedYear,     
            'fechaInicio' => $fechaInicio,      
            'fechaFin' => $fechaFin,         
            'selectedCicloAcademico' => $selectedCicloAcademico,
            'processedDetailedAsistencias' => $processedDetailedAsistencias,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }

    /**
     * Exportar reporte de planilla de asistencia docente en PDF
     */
    public function exportarPlanillaPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $fecha_generacion = Carbon::now()->format('d/m/Y H:i:s');
        
        // Determinar el ciclo actual para el encabezado
        $cicloNombre = "Todos los Ciclos";
        if ($data['selectedCicloAcademico']) {
            $ciclo = Ciclo::where('codigo', $data['selectedCicloAcademico'])->first();
            if ($ciclo) $cicloNombre = $ciclo->nombre;
        }

        // Generar QR en Base64 para el PDF
        $qrData = "PLANILLA OFICIAL CEPRE UNAMAD\nCiclo: {$cicloNombre}\nFecha: {$fecha_generacion}\nValidación: " . uniqid();
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(80)->generate($qrData));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('asistencia-docente.planilla-pdf', array_merge($data, [
            'fecha_generacion' => $fecha_generacion,
            'cicloNombre' => $cicloNombre,
            'qrCode' => $qrCode
        ]));

        $pdf->setPaper('a4', 'portrait');

        $fileName = 'Planilla_Asistencia_Docente_' . str_replace(' ', '_', $cicloNombre) . '_' . date('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }

    public function index(Request $request)
    {
        // Procesar eventos pendientes para esta vista en tiempo real (antes vivía en el constructor)
        Artisan::call('asistencia:procesar-eventos');

        // 1. Obtener ciclos y determinar el ciclo seleccionado
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        $cicloSeleccionadoId = $request->input('ciclo_id');
        // Por defecto el ciclo activo de CEPRE (menor programa_id), no el de Reforzamiento
        $cicloActivo = $ciclos->where('es_activo', true)->sortBy('programa_id')->first();

        if ($cicloSeleccionadoId) {
            $cicloSeleccionado = $ciclos->find($cicloSeleccionadoId);
        } else {
            $cicloSeleccionado = $cicloActivo;
        }

        // 2. Obtener otros filtros
        $fecha = $request->get('fecha');
        $documento = $request->get('documento');

        // Por defecto mostrar HOY en la carga inicial.
        // Si el usuario limpia la fecha y filtra (envía fecha vacía), se respeta el rango del ciclo.
        if (!$request->has('fecha')) {
            $fecha = Carbon::today()->toDateString();
        }

        // 3. Construir la consulta base — docentes LIGADOS al ciclo seleccionado (por su carga horaria)
        $docentesBase = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        });
        if ($cicloSeleccionado) {
            $docentesBase->whereHas('horarios', function ($q) use ($cicloSeleccionado) {
                $q->where('ciclo_id', $cicloSeleccionado->id);
            });
        }

        $docentesDocumentos = (clone $docentesBase)->pluck('numero_documento')->toArray();

        $query = RegistroAsistencia::with(['usuario.roles'])
            ->whereIn('nro_documento', $docentesDocumentos);

        // 4. Aplicar filtros de fecha (se COMBINAN con la búsqueda por docente):
        // - Si hay fecha específica → solo ese día.
        // - Si la fecha está vacía → todo el rango del ciclo (útil para ver el histórico de un docente).
        if ($fecha) {
            $query->whereDate('fecha_registro', $fecha);
        } elseif ($cicloSeleccionado) {
            $query->whereBetween('fecha_registro', [$cicloSeleccionado->fecha_inicio, $cicloSeleccionado->fecha_fin]);
        }

        if ($documento) {
            // Buscar por DNI o por nombre/apellidos. Cada palabra debe aparecer en algún campo
            // del nombre (así "Juan Perez" coincide con nombre="Juan" y apellido="Perez").
            $terminos = array_filter(preg_split('/\s+/', trim($documento)));
            $query->where(function ($q) use ($documento, $terminos) {
                $q->where('nro_documento', 'like', '%' . $documento . '%')
                  ->orWhereHas('usuario', function ($u) use ($terminos) {
                      foreach ($terminos as $t) {
                          $u->where(function ($w) use ($t) {
                              $w->where('nombre', 'like', '%' . $t . '%')
                                ->orWhere('apellido_paterno', 'like', '%' . $t . '%')
                                ->orWhere('apellido_materno', 'like', '%' . $t . '%');
                          });
                      }
                  });
            });
        }

        $asistencias = $query->orderBy('fecha_hora', 'desc')->paginate(15);

        $docentes = (clone $docentesBase)
            ->select('id', 'numero_documento', 'nombre', 'apellido_paterno')
            ->orderBy('apellido_paterno')->orderBy('nombre')->get();

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
        // Procesar eventos pendientes para el monitor en tiempo real (antes vivía en el constructor)
        Artisan::call('asistencia:procesar-eventos');

        $hoy = Carbon::today();
        
        // Últimas asistencias del día
        $ultimasAsistencias = AsistenciaDocente::with(['docente', 'horario.curso'])
            ->whereDate('fecha_hora', $hoy)
            ->orderBy('fecha_hora', 'desc')
            ->take(20)
            ->get();

        // Estadísticas del día
        $estadisticasHoy = [
            'total_registros' => AsistenciaDocente::whereDate('fecha_hora', $hoy)->count(),
            'total_entradas' => AsistenciaDocente::whereDate('fecha_hora', $hoy)->where('estado', 'entrada')->count(),
            'total_salidas' => AsistenciaDocente::whereDate('fecha_hora', $hoy)->where('estado', 'salida')->count(),
            'temas_pendientes' => AsistenciaDocente::whereDate('fecha_hora', $hoy)
                ->where('estado', 'salida')
                ->whereNull('tema_desarrollado')
                ->count(),
        ];

        // Obtener ciclo activo (Priorizamos CEPRE o el primero encontrado si hay varios)
        $cicloActivo = Ciclo::where('es_activo', true)
            ->orderBy('programa_id', 'asc')
            ->first();

        return view('asistencia-docente.monitor', compact('ultimasAsistencias', 'estadisticasHoy', 'cicloActivo'));
    }

    /**
     * Obtener horario completo del día actual con estados de asistencia
     */
    public function getDailySchedule(Request $request)
    {
        try {
            // Procesar eventos pendientes para datos en tiempo real (antes vivía en el constructor)
            Artisan::call('asistencia:procesar-eventos');

            $fecha = $request->input('fecha', Carbon::today()->toDateString());
            $fechaCarbon = Carbon::parse($fecha);
            
            // Obtener ciclo activo (Priorizando CEPRE para visualización general)
            $cicloActivo = Ciclo::where('es_activo', true)
                ->orderBy('programa_id', 'asc')
                ->first();
            
            $diaSemana = $fechaCarbon->locale('es')->dayName;
            
            // Obtener todos los horarios activos del día (considerando rotación por ciclo)
            $todosHorariosActivos = HorarioDocente::with(['docente', 'curso', 'aula', 'ciclo'])
                ->whereHas('ciclo', function($q) {
                    $q->where('es_activo', true);
                })
                ->get();
            
            $horarios = $todosHorariosActivos->filter(function($horario) use ($fechaCarbon) {
                $diaNecesario = $horario->ciclo ? $horario->ciclo->getDiaHorarioParaFecha($fechaCarbon) : null;
                return $diaNecesario && strtolower($diaNecesario) === strtolower($horario->dia_semana);
            })->sortBy('hora_inicio');
            
            // Procesar cada horario con su estado de asistencia
            $schedule = $horarios->map(function ($horario) use ($fechaCarbon) {
                // 1. Buscar marca de entrada y salida reales en registros_asistencia (fecha_registro) para evitar desfases
                $docente = $horario->docente;
                $registrosDocente = $docente ? RegistroAsistencia::where('nro_documento', $docente->numero_documento)
                    ->whereDate('fecha_registro', $fechaCarbon)
                    ->get() : collect();

                $horaInicio = Carbon::parse($fechaCarbon->toDateString() . ' ' . $horario->hora_inicio);
                $horaFin = Carbon::parse($fechaCarbon->toDateString() . ' ' . $horario->hora_fin);

                $toleranciaAnticipada = 45; // minutos
                $toleranciaTardia = 60; // minutos
                $registroEntrada = $registrosDocente->filter(function($r) use ($horaInicio, $toleranciaAnticipada, $toleranciaTardia) {
                    $horaReg = Carbon::parse($r->fecha_registro);
                    return $horaReg->between(
                        $horaInicio->copy()->subMinutes($toleranciaAnticipada),
                        $horaInicio->copy()->addMinutes($toleranciaTardia)
                    );
                })->sortBy('fecha_registro')->first();

                $tieneEntrada = !is_null($registroEntrada);
                $horaEntrada = $registroEntrada ? Carbon::parse($registroEntrada->fecha_registro)->format('H:i:s') : null;

                $toleranciaSalidaAnticipada = 30; // minutos
                $toleranciaSalidaTardia = 60; // minutos
                $registroSalida = $registrosDocente->filter(function($r) use ($horaFin, $toleranciaSalidaAnticipada, $toleranciaSalidaTardia) {
                    $horaReg = Carbon::parse($r->fecha_registro);
                    return $horaReg->between(
                        $horaFin->copy()->subMinutes($toleranciaSalidaAnticipada),
                        $horaFin->copy()->addMinutes($toleranciaSalidaTardia)
                    );
                })->sortByDesc('fecha_registro')->first();

                $tieneSalida = !is_null($registroSalida);
                $horaSalida = $registroSalida ? Carbon::parse($registroSalida->fecha_registro)->format('H:i:s') : null;

                // 2. Determinar si hubo tardanza (tolerancia de 5 minutos)
                $esTardanza = false;
                $minutosTardanza = 0;
                if ($tieneEntrada) {
                    $tolTarde = 5;
                    $umbralTarde = $horaInicio->copy()->addMinutes($tolTarde);
                    $entradaCarbon = Carbon::parse($registroEntrada->fecha_registro);
                    if ($entradaCarbon->greaterThan($umbralTarde)) {
                        $esTardanza = true;
                        $minutosTardanza = (int) abs($horaInicio->diffInMinutes($entradaCarbon));
                    }
                }

                // 3. Buscar la asistencia de salida procesada para verificar si ya cargó el tema desarrollado
                $asistenciaSalida = AsistenciaDocente::where('horario_id', $horario->id)
                    ->where('docente_id', $horario->docente_id)
                    ->whereDate('fecha_hora', $fechaCarbon)
                    ->where('estado', 'salida')
                    ->first();

                // Determinar estado para visualización
                $ahora = Carbon::now();
                $estado = 'pendiente'; // Por defecto
                $estadoTexto = 'Pendiente';
                $estadoColor = 'secondary';

                if ($tieneEntrada && $tieneSalida) {
                    if ($asistenciaSalida && $asistenciaSalida->tema_desarrollado) {
                        $estado = 'completo';
                        $estadoTexto = $esTardanza ? 'Completo (Tarde)' : 'Completo';
                        $estadoColor = 'success';
                    } else {
                        $estado = 'tema_pendiente';
                        $estadoTexto = $esTardanza ? 'Tema Pend. (Tarde)' : 'Tema Pendiente';
                        $estadoColor = 'warning';
                    }
                } elseif ($tieneEntrada && $ahora->between($horaInicio, $horaFin)) {
                    $estado = 'en_curso';
                    $estadoTexto = $esTardanza ? 'En Curso (Tarde)' : 'En Curso';
                    $estadoColor = 'info';
                } elseif (!$tieneEntrada && $ahora->greaterThan($horaFin)) {
                    $estado = 'falta';
                    $estadoTexto = 'Falta';
                    $estadoColor = 'danger';
                } elseif ($tieneEntrada && $ahora->greaterThan($horaFin)) {
                    $estado = 'sin_salida';
                    $estadoTexto = $esTardanza ? 'Sin Salida (Tarde)' : 'Sin Salida';
                    $estadoColor = 'warning';
                }

                return [
                    'horario_id' => $horario->id,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'docente_id' => $horario->docente_id,
                    'docente_nombre' => $horario->docente ? trim("{$horario->docente->nombre} {$horario->docente->apellido_paterno} {$horario->docente->apellido_materno}") : 'N/A',
                    'docente_telefono' => $horario->docente ? $horario->docente->telefono : null,
                    'curso' => $horario->curso ? $horario->curso->nombre : 'N/A',
                    'aula' => $horario->aula ? $horario->aula->nombre : 'N/A',
                    'estado' => $estado,
                    'estado_texto' => $estadoTexto,
                    'estado_color' => $estadoColor,
                    'tiene_entrada' => $tieneEntrada,
                    'tiene_salida' => $tieneSalida,
                    'hora_entrada' => $horaEntrada,
                    'hora_salida' => $horaSalida,
                    'es_tardanza' => $esTardanza,
                    'minutos_tardanza' => $minutosTardanza,
                    'tema_desarrollado' => $asistenciaSalida ? $asistenciaSalida->tema_desarrollado : null,
                    'asistencia_id' => $asistenciaSalida ? $asistenciaSalida->id : null,
                ];
            });
            
            return response()->json([
                'success' => true,
                'fecha' => $fechaCarbon->format('Y-m-d'),
                'dia_semana' => ucfirst($diaSemana ?? ''),
                'schedule' => $schedule,
                'total_clases' => $schedule->count(),
                'hora_actual' => Carbon::now()->format('H:i:s'),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener horario completo del día con estados de cada clase
     */
    public function getTeachersWithoutTheme(Request $request)
    {
        try {
            // Procesar eventos pendientes para datos en tiempo real (antes vivía en el constructor)
            Artisan::call('asistencia:procesar-eventos');

            $fecha = $request->input('fecha', Carbon::today()->toDateString());
            $fechaCarbon = Carbon::parse($fecha);
            
            // Obtener ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            // Obtener todos los horarios activos del día (considerando rotación por ciclo)
            $todosHorariosActivos = HorarioDocente::with(['docente', 'curso', 'aula', 'ciclo'])
                ->whereHas('ciclo', function($q) {
                    $q->where('es_activo', true);
                })
                ->get();
            
            $horarios = $todosHorariosActivos->filter(function($horario) use ($fechaCarbon) {
                $diaNecesario = $horario->ciclo ? $horario->ciclo->getDiaHorarioParaFecha($fechaCarbon) : null;
                return $diaNecesario && strtolower($diaNecesario) === strtolower($horario->dia_semana);
            })->sortBy('hora_inicio');
            
            // Procesar cada horario con su estado
            $clases = $horarios->map(function ($horario) use ($fechaCarbon) {
                // Buscar registros biométricos del docente para este día
                $registrosBiometricos = \App\Models\RegistroAsistencia::where('nro_documento', $horario->docente->numero_documento)
                    ->whereDate('fecha_registro', $fechaCarbon)
                    ->orderBy('fecha_registro')
                    ->get();
                
                // Buscar entrada y salida usando la misma lógica del dashboard
                $horaInicioProgramada = Carbon::parse($horario->hora_inicio);
                $horaFinProgramada = Carbon::parse($horario->hora_fin);
                $horarioInicioHoy = $fechaCarbon->copy()->setTime($horaInicioProgramada->hour, $horaInicioProgramada->minute, $horaInicioProgramada->second);
                $horarioFinHoy = $fechaCarbon->copy()->setTime($horaFinProgramada->hour, $horaFinProgramada->minute, $horaFinProgramada->second);
                
                // Buscar entrada biométrica (15 min antes hasta 120 min después del inicio)
                $entradaBiometrica = $registrosBiometricos
                    ->filter(function($r) use ($horarioInicioHoy) {
                        $horaRegistro = Carbon::parse($r->fecha_registro);
                        return $horaRegistro->between(
                            $horarioInicioHoy->copy()->subMinutes(15),
                            $horarioInicioHoy->copy()->addMinutes(120)
                        );
                    })
                    ->sortBy('fecha_registro')
                    ->first();
                
                // Buscar salida biométrica (15 min antes hasta 60 min después del fin)
                $salidaBiometrica = $registrosBiometricos
                    ->filter(function($r) use ($horarioFinHoy) {
                        $horaRegistro = Carbon::parse($r->fecha_registro);
                        return $horaRegistro->between(
                            $horarioFinHoy->copy()->subMinutes(15),
                            $horarioFinHoy->copy()->addMinutes(60)
                        );
                    })
                    ->sortByDesc('fecha_registro')
                    ->first();
                
                // Buscar asistencia procesada para obtener el tema desarrollado
                $asistenciaDocente = AsistenciaDocente::where('horario_id', $horario->id)
                    ->where('docente_id', $horario->docente_id)
                    ->whereDate('fecha_hora', $fechaCarbon)
                    ->first();
                
                // Determinar estado de la clase
                $ahora = Carbon::now();
                $horaInicio = Carbon::parse($fechaCarbon->toDateString() . ' ' . $horario->hora_inicio);
                $horaFin = Carbon::parse($fechaCarbon->toDateString() . ' ' . $horario->hora_fin);
                
                $estado = 'pendiente';
                $estadoTexto = 'Pendiente';
                $estadoColor = 'secondary';
                $estadoIcono = 'clock';
                
                if ($entradaBiometrica && $salidaBiometrica) {
                    // Asistió y salió - verificar si registró tema
                    if ($asistenciaDocente && $asistenciaDocente->tema_desarrollado) {
                        $estado = 'completado';
                        $estadoTexto = 'Tema Registrado';
                        $estadoColor = 'success';
                        $estadoIcono = 'check-circle';
                    } else {
                        $estado = 'tema_pendiente';
                        $estadoTexto = 'Tema Pendiente';
                        $estadoColor = 'warning';
                        $estadoIcono = 'exclamation-triangle';
                    }
                } elseif ($entradaBiometrica && !$salidaBiometrica) {
                    // Registró entrada pero no salida
                    if ($ahora->between($horaInicio, $horaFin)) {
                        $estado = 'en_curso';
                        $estadoTexto = 'En Transcurso';
                        $estadoColor = 'info';
                        $estadoIcono = 'spinner';
                    } else {
                        // Ya pasó la hora de fin pero no registró salida
                        $estado = 'sin_salida';
                        $estadoTexto = 'Falta Registrar Salida';
                        $estadoColor = 'warning';
                        $estadoIcono = 'exclamation-circle';
                    }
                } elseif (!$entradaBiometrica && $ahora->greaterThan($horaFin)) {
                    // No registró entrada y ya pasó la hora
                    $estado = 'falta';
                    $estadoTexto = 'Falta';
                    $estadoColor = 'danger';
                    $estadoIcono = 'times-circle';
                } elseif (!$entradaBiometrica && $ahora->between($horaInicio, $horaFin)) {
                    // Está en horario pero no ha registrado entrada
                    $estado = 'en_curso_sin_registro';
                    $estadoTexto = 'En Horario - Sin Registro';
                    $estadoColor = 'danger';
                    $estadoIcono = 'exclamation-triangle';
                }
                
                $tiempoTranscurrido = null;
                if ($salidaBiometrica) {
                    $tiempoTranscurrido = Carbon::parse($salidaBiometrica->fecha_registro)->diffForHumans();
                }
                
                
                
                return [
                    'horario_id' => $horario->id,
                    'docente_id' => $horario->docente_id,
                    'docente_nombre' => $horario->docente ? $horario->docente->nombre . ' ' . $horario->docente->apellido_paterno : 'Sin asignar',
                    'docente_telefono' => $horario->docente ? $horario->docente->telefono : null,
                    'curso' => $horario->curso ? $horario->curso->nombre : 'Sin asignar',
                    'aula' => $horario->aula ? $horario->aula->nombre : 'Sin asignar',
                    'horario' => $horario->hora_inicio . ' - ' . $horario->hora_fin,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'hora_entrada' => $entradaBiometrica ? Carbon::parse($entradaBiometrica->fecha_registro)->format('H:i') : null,
                    'hora_salida' => $salidaBiometrica ? Carbon::parse($salidaBiometrica->fecha_registro)->format('H:i') : null,
                    'tiempo_transcurrido' => $tiempoTranscurrido,
                    'tema_desarrollado' => $asistenciaDocente ? $asistenciaDocente->tema_desarrollado : null,
                    'estado' => $estado,
                    'estado_texto' => $estadoTexto,
                    'estado_color' => $estadoColor,
                    'estado_icono' => $estadoIcono,
                    'fecha' => $fechaCarbon->format('d/m/Y'),
                ];
            });
            
            // Contar por estados
            $estadisticas = [
                'total' => $clases->count(),
                'completados' => $clases->where('estado', 'completado')->count(),
                'temas_pendientes' => $clases->where('estado', 'tema_pendiente')->count(),
                'en_curso' => $clases->where('estado', 'en_curso')->count(),
                'faltas' => $clases->where('estado', 'falta')->count(),
                'pendientes' => $clases->where('estado', 'pendiente')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'clases' => $clases,
                'estadisticas' => $estadisticas,
                'fecha' => $fechaCarbon->format('d/m/Y'),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar reporte diario detallado
     */
    public function getDailyReport(Request $request)
    {
        try {
            // Procesar eventos pendientes para datos en tiempo real (antes vivía en el constructor)
            Artisan::call('asistencia:procesar-eventos');

            $fecha = $request->input('fecha', Carbon::today()->toDateString());
            $cicloId = $request->input('ciclo_id');
            $turno = $request->input('turno');
            
            $fechaCarbon = Carbon::parse($fecha);
            
            // Obtener ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            // Obtener el día de la semana correspondiente (tomando en cuenta rotaciones y recuperaciones)
            if ($cicloActivo) {
                $diaSemana = $cicloActivo->getDiaHorarioParaFecha($fechaCarbon);
            } else {
                $diaSemana = $fechaCarbon->locale('es')->dayName;
            }
            
            // Query base para horarios
            $horariosQuery = HorarioDocente::with(['docente', 'curso', 'aula', 'ciclo'])
                ->where('dia_semana', $diaSemana);
            
            if ($cicloId) {
                $horariosQuery->where('ciclo_id', $cicloId);
            }
            
            if ($turno) {
                $horariosQuery->where('turno', $turno);
            }
            
            $horarios = $horariosQuery->orderBy('hora_inicio')->get();
            
            // Procesar reporte detallado
            $reporte = $horarios->map(function ($horario) use ($fechaCarbon) {
                // Buscar registros biométricos
                $registrosBiometricos = \App\Models\RegistroAsistencia::where('nro_documento', $horario->docente->numero_documento)
                    ->whereDate('fecha_registro', $fechaCarbon)
                    ->orderBy('fecha_registro')
                    ->get();
                
                $horaInicioProgramada = Carbon::parse($horario->hora_inicio);
                $horaFinProgramada = Carbon::parse($horario->hora_fin);
                $horarioInicioHoy = $fechaCarbon->copy()->setTime($horaInicioProgramada->hour, $horaInicioProgramada->minute, $horaInicioProgramada->second);
                $horarioFinHoy = $fechaCarbon->copy()->setTime($horaFinProgramada->hour, $horaFinProgramada->minute, $horaFinProgramada->second);
                
                $entradaBiometrica = $registrosBiometricos->filter(function($r) use ($horarioInicioHoy) {
                    $horaRegistro = Carbon::parse($r->fecha_registro);
                    return $horaRegistro->between($horarioInicioHoy->copy()->subMinutes(15), $horarioInicioHoy->copy()->addMinutes(120));
                })->sortBy('fecha_registro')->first();
                
                $salidaBiometrica = $registrosBiometricos->filter(function($r) use ($horarioFinHoy) {
                    $horaRegistro = Carbon::parse($r->fecha_registro);
                    return $horaRegistro->between($horarioFinHoy->copy()->subMinutes(15), $horarioFinHoy->copy()->addMinutes(60));
                })->sortByDesc('fecha_registro')->first();
                
                $asistenciaDocente = AsistenciaDocente::where('horario_id', $horario->id)
                    ->where('docente_id', $horario->docente_id)
                    ->whereDate('fecha_hora', $fechaCarbon)
                    ->first();
                
                return [
                    'docente' => $horario->docente ? $horario->docente->nombre . ' ' . $horario->docente->apellido_paterno : 'N/A',
                    'curso' => $horario->curso ? $horario->curso->nombre : 'N/A',
                    'aula' => $horario->aula ? $horario->aula->nombre : 'N/A',
                    'turno' => $horario->turno ?? 'N/A',
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'hora_entrada' => $entradaBiometrica ? Carbon::parse($entradaBiometrica->fecha_registro)->format('H:i') : '-',
                    'hora_salida' => $salidaBiometrica ? Carbon::parse($salidaBiometrica->fecha_registro)->format('H:i') : '-',
                    'horas_dictadas' => $asistenciaDocente && $asistenciaDocente->horas_dictadas ? round($asistenciaDocente->horas_dictadas, 2) : 0,
                    'tema_desarrollado' => $asistenciaDocente ? ($asistenciaDocente->tema_desarrollado ?? 'Pendiente') : 'Pendiente',
                    'estado' => $entradaBiometrica && $salidaBiometrica ? 'Asistió' : 'Falta',
                ];
            });
            
            // Estadísticas del reporte
            $estadisticas = [
                'total_clases' => $reporte->count(),
                'total_asistencias' => $reporte->where('estado', 'Asistió')->count(),
                'total_faltas' => $reporte->where('estado', 'Falta')->count(),
                'total_temas_pendientes' => $reporte->where('tema_desarrollado', 'Pendiente')->count(),
                'total_horas_dictadas' => $reporte->sum('horas_dictadas'),
            ];
            
            return response()->json([
                'success' => true,
                'fecha' => $fechaCarbon->format('d/m/Y'),
                'reporte' => $reporte,
                'estadisticas' => $estadisticas,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar mensaje de WhatsApp
     */
    public function generateWhatsAppMessage(Request $request)
    {
        try {
            $request->validate([
                'docente_id' => 'required|exists:users,id',
                'tipo' => 'required|in:tema_pendiente,falta,recordatorio',
                'data' => 'nullable|array',
            ]);
            
            $docente = User::findOrFail($request->docente_id);
            
            if (!$docente->telefono) {
                return response()->json([
                    'success' => false,
                    'message' => 'El docente no tiene número de teléfono registrado.'
                ], 400);
            }
            
            // Preparar datos para el mensaje
            $data = array_merge([
                'docente_nombre' => $docente->nombre . ' ' . $docente->apellido_paterno,
            ], $request->input('data', []));
            
            // Generar mensaje usando el helper
            $mensaje = \App\Helpers\WhatsAppHelper::getMessageTemplate($request->tipo, $data);
            $link = \App\Helpers\WhatsAppHelper::generateLink($docente->telefono, $mensaje);
            
            return response()->json([
                'success' => true,
                'link' => $link,
                'mensaje' => $mensaje,
                'telefono' => $docente->telefono,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar mensaje: ' . $e->getMessage()
            ], 500);
        }
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

        $asistencia = AsistenciaDocente::updateOrCreate(
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

        // Si es salida sin tema (o el tema es el mismo que antes null), verificar si la entrada tiene tema
        if ($request->estado === 'salida' && !$request->tema_desarrollado) {
            $temaRegistrado = AsistenciaDocente::where('docente_id', $request->docente_id)
                ->where('horario_id', $horario->id)
                ->where('estado', 'entrada')
                ->whereDate('fecha_hora', $fecha->toDateString())
                ->whereNotNull('tema_desarrollado')
                ->exists();

            if (!$temaRegistrado) {
                User::find($request->docente_id)->notify(new \App\Notifications\SesionPendienteTemaNotification($horario));
            }
        }

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
    
        
        // ✅ 4. CAMBIO CRÍTICO: usar 'edit' en lugar de 'editar'
        return view('asistencia-docente.edit', compact('asistencia', 'docentes'));
    }

    public function registrarTema(Request $request)
    {
        try {
            $request->validate([
                'horario_id' => 'required|integer',
                'tema_desarrollado' => 'required|string|min:10|max:1000',
                'fecha_seleccionada' => 'required|date_format:Y-m-d', // Validar que la fecha venga en el formato correcto
            ]);
            
            // Limpiar HTML antes de procesar
            $temaLimpio = \App\Models\AsistenciaDocente::cleanQuillHtml($request->tema_desarrollado);

            $user = auth()->user();
            $fechaSeleccionada = Carbon::parse($request->fecha_seleccionada)->startOfDay(); 
        
            // Primero, encontrar el horario específico solicitado por horario_id
            $horario = HorarioDocente::find($request->horario_id);
            if (!$horario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el horario seleccionado.',
                ], 404);
            }
            
            // Combinar la fecha seleccionada con las horas del horario
            $horarioInicioClase = $fechaSeleccionada->copy()->setTime(Carbon::parse($horario->hora_inicio)->hour, Carbon::parse($horario->hora_inicio)->minute);
            $horarioFinClase = $fechaSeleccionada->copy()->setTime(Carbon::parse($horario->hora_fin)->hour, Carbon::parse($horario->hora_fin)->minute);
        
            // Buscar registros de AsistenciaDocente existentes para la FECHA SELECCIONADA y este horario
            $asistencias = AsistenciaDocente::where('horario_id', $request->horario_id)
                ->where('docente_id', $user->id)
                ->whereDate('fecha_hora', $fechaSeleccionada->toDateString())
                ->get();
        
            // Obtener registros biométricos para la fecha seleccionada
            $registrosDiaSeleccionado = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaSeleccionada->format('Y-m-d'))
                ->orderBy('fecha_registro')
                ->get();

            // Buscar entrada válida
            $entrada = $registrosDiaSeleccionado->filter(function($r) use ($horarioInicioClase) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioInicioClase->copy()->subMinutes(15),
                    $horarioInicioClase->copy()->addMinutes(30)
                );
            })->first();

            // Buscar salida válida
            $salida = $registrosDiaSeleccionado->filter(function($r) use ($horarioFinClase) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioFinClase->copy()->subMinutes(15),
                    $horarioFinClase->copy()->addMinutes(60)
                );
            })->sortByDesc('fecha_registro')->first();

            $horaEntrada = null;
            $horaSalida = null;

            if ($entrada) {
                $horaEntrada = Carbon::parse($entrada->fecha_registro);
            }
            if ($salida) {
                $horaSalida = Carbon::parse($salida->fecha_registro);
            }

            // Si no hay biométricos pero ya existen asistencias grabadas,
            // intentamos recuperar las horas de los registros de asistencia existentes.
            if ($asistencias->isNotEmpty()) {
                if (!$horaEntrada) {
                    $asisEntrada = $asistencias->where('estado', 'entrada')->first();
                    if ($asisEntrada) {
                        $horaEntrada = Carbon::parse($asisEntrada->fecha_hora);
                    }
                }
                if (!$horaSalida) {
                    $asisSalida = $asistencias->where('estado', 'salida')->first();
                    if ($asisSalida) {
                        $horaSalida = Carbon::parse($asisSalida->fecha_hora);
                    }
                }
                
                // Si aún falta alguno, hacemos fallback a las horas programadas
                if (!$horaEntrada) {
                    $horaEntrada = $horarioInicioClase->copy();
                }
                if (!$horaSalida) {
                    $horaSalida = $horarioFinClase->copy();
                }
            }

            if (!$horaEntrada || !$horaSalida) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron registros de asistencia ni biométricos de entrada/salida válidos para esta sesión.'
                ], 400);
            }

            // Calcular datos de asistencia
            $horasTrabajadas = 0;
            $montoTotal = 0;

            // El inicio efectivo es el más tardío entre la hora programada y la hora de entrada.
            $inicioEfectivo = $horaEntrada->max($horarioInicioClase);
            
            // El fin efectivo es el más temprano entre la hora programada y la hora de salida.
            $finEfectivo = $horaSalida->min($horarioFinClase);
            
            if ($finEfectivo->greaterThan($inicioEfectivo)) {
                $minutosBrutos = $inicioEfectivo->diffInMinutes($finEfectivo);

                // Descuento de recesos - Obtener valores del ciclo del horario
                $cicloDelHorario = $horario->ciclo;
                $minutosRecesoManana = 0;
                $minutosRecesoTarde = 0;
                
                // Receso de mañana (configurable por ciclo)
                if ($cicloDelHorario && $cicloDelHorario->receso_manana_inicio && $cicloDelHorario->receso_manana_fin) {
                    $recesoMananaInicio = $fechaSeleccionada->copy()->setTimeFromTimeString($cicloDelHorario->receso_manana_inicio);
                    $recesoMananaFin = $fechaSeleccionada->copy()->setTimeFromTimeString($cicloDelHorario->receso_manana_fin);
                    
                    if ($inicioEfectivo < $recesoMananaFin && $finEfectivo > $recesoMananaInicio) {
                        $superposicionInicio = $inicioEfectivo->max($recesoMananaInicio);
                        $superposicionFin = $finEfectivo->min($recesoMananaFin);
                        if ($superposicionFin > $superposicionInicio) {
                            $minutosRecesoManana = $superposicionInicio->diffInMinutes($superposicionFin);
                        }
                    }
                }

                // Receso de tarde (configurable por ciclo)
                if ($cicloDelHorario && $cicloDelHorario->receso_tarde_inicio && $cicloDelHorario->receso_tarde_fin) {
                    $recesoTardeInicio = $fechaSeleccionada->copy()->setTimeFromTimeString($cicloDelHorario->receso_tarde_inicio);
                    $recesoTardeFin = $fechaSeleccionada->copy()->setTimeFromTimeString($cicloDelHorario->receso_tarde_fin);
                    
                    if ($inicioEfectivo < $recesoTardeFin && $finEfectivo > $recesoTardeInicio) {
                        $superposicionInicio = $inicioEfectivo->max($recesoTardeInicio);
                        $superposicionFin = $finEfectivo->min($recesoTardeFin);
                        if ($superposicionFin > $superposicionInicio) {
                            $minutosRecesoTarde = $superposicionInicio->diffInMinutes($superposicionFin);
                        }
                    }
                }

                $minutosNetos = $minutosBrutos - $minutosRecesoManana - $minutosRecesoTarde;
                $horasTrabajadas = $minutosNetos / 60;
                
                // Usar el ciclo del horario para evitar cruces
                $cicloTarifa = $horario->ciclo ?: Ciclo::where('es_activo', true)->first();
                $tarifaHora = $this->obtenerTarifaDocente($user->id, $cicloTarifa, $fechaSeleccionada);
                $montoTotal = $horasTrabajadas * $tarifaHora;
            } else {
                 return response()->json([
                    'success' => false,
                    'message' => 'No hay tiempo de clase efectivo. La hora de entrada/salida está fuera del horario de clase o es inválida.'
                ], 400);
            }

            if ($asistencias->isNotEmpty()) {
                foreach ($asistencias as $asistencia) {
                    $asistencia->update([
                        'tema_desarrollado' => $temaLimpio,
                        'hora_entrada' => $horaEntrada->toDateTimeString(),
                        'hora_salida' => $horaSalida->toDateTimeString(),
                        'horas_dictadas' => round($horasTrabajadas, 2),
                        'monto_total' => round($montoTotal, 2),
                    ]);
                }
            } else {
                AsistenciaDocente::create([
                    'docente_id' => $user->id,
                    'horario_id' => $horario->id,
                    'curso_id'   => $horario->curso_id,
                    'aula_id'    => $horario->aula_id,
                    'fecha_hora' => $entrada->fecha_registro ?? $fechaSeleccionada->copy()->setTimeFromTimeString($horario->hora_inicio),
                    'estado'     => 'entrada',
                    'tipo_verificacion' => $entrada->tipo_verificacion ?? 'biometrico',
                    'terminal_id'       => $entrada->terminal_id ?? null,
                    'codigo_trabajo'    => $entrada->codigo_trabajo ?? null,
                    'turno'      => $horario->turno,
                    'tema_desarrollado' => $temaLimpio,
                    'hora_entrada' => $horaEntrada->toDateTimeString(),
                    'hora_salida' => $horaSalida->toDateTimeString(),
                    'horas_dictadas' => round($horasTrabajadas, 2),
                    'monto_total' => round($montoTotal, 2),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tema desarrollado registrado correctamente.'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar: ' . $e->getMessage()
            ], 500);
        }
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
     * Exportar reporte individual de asistencia en PDF
     */
    public function exportarPdfIndividual(Request $request)
    {
        $selectedDocenteId = $request->input('docente_id');
        $selectedMonth = $request->input('mes');
        $selectedYear = $request->input('anio');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $selectedCicloAcademico = $request->input('ciclo_academico');

        // Seguridad: Si el usuario es profesor, solo puede ver su propio reporte
        $user = auth()->user();
        if ($user->hasRole('profesor')) {
            $selectedDocenteId = $user->id;
        }

        if (!$selectedDocenteId) {
            return back()->with('error', 'Debe seleccionar un docente para generar el reporte PDF.');
        }

        $docente = User::findOrFail($selectedDocenteId);

        // Determinación de fechas (Lógica similar a reports)
        $startDate = null;
        $endDate = null;

        $cicloId = $request->input('ciclo_id');

        if ($cicloId) {
             $ciclo = Ciclo::find($cicloId);
        } elseif ($selectedCicloAcademico) {
            $ciclo = Ciclo::where('codigo', $selectedCicloAcademico)->first();
        } else {
            $ciclo = Ciclo::where('es_activo', true)->first();
        }

        if ($ciclo) {
            $cicloStartDate = Carbon::parse($ciclo->fecha_inicio)->startOfDay();
            $cicloEndDate = Carbon::parse($ciclo->fecha_fin)->endOfDay();
            
            if (!$fechaInicio && !$fechaFin && !$selectedMonth && !$selectedYear) {
                $startDate = $cicloStartDate;
                $endDate = Carbon::now()->endOfDay()->min($cicloEndDate);
            } elseif ($fechaInicio && $fechaFin) {
                $startDate = Carbon::parse($fechaInicio)->startOfDay()->max($cicloStartDate);
                $endDate = Carbon::parse($fechaFin)->endOfDay()->min($cicloEndDate);
            } elseif ($selectedMonth && $selectedYear) {
                $monthStart = Carbon::createFromDate($selectedYear, (int)$selectedMonth, 1)->startOfDay();
                $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();
                $startDate = $monthStart->max($cicloStartDate);
                $endDate = $monthEnd->min($cicloEndDate);
            } else {
                $startDate = $cicloStartDate;
                $endDate = Carbon::now()->endOfDay()->min($cicloEndDate);
            }
        } else {
            $endDate = Carbon::today()->endOfDay();
            $startDate = $endDate->copy()->subDays(30)->startOfDay();
        }

        // Procesar sesiones (Lógica simplificada de reports para un solo docente)
        $docenteSessions = [];
        $cicloActivoParaRotacion = $ciclo ?: Ciclo::where('es_activo', true)->first();
        
        $todosHorariosDocente = HorarioDocente::where('docente_id', $docente->id)
            ->with(['curso', 'aula', 'ciclo']);
        
        if ($ciclo) {
            $todosHorariosDocente->where('ciclo_id', $ciclo->id);
        } elseif ($selectedCicloAcademico) {
            $todosHorariosDocente->whereHas('ciclo', function ($q) use ($selectedCicloAcademico) {
                $q->where('codigo', $selectedCicloAcademico);
            });
        }
        $todosHorariosDocente = $todosHorariosDocente->get();
        
        $todosRegistrosDocente = RegistroAsistencia::where('nro_documento', $docente->numero_documento)
            ->whereBetween('fecha_registro', [$startDate, $endDate])
            ->orderBy('fecha_registro', 'asc')
            ->get();
        
        $registrosPorFecha = $todosRegistrosDocente->groupBy(function($item) {
            return Carbon::parse($item->fecha_registro)->toDateString();
        });

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $diaSemanaNombre = $this->getDiaHorarioParaFecha($currentDate, $cicloActivoParaRotacion);
            $fechaString = $currentDate->toDateString();

            $horariosDelDia = $todosHorariosDocente->filter(function($horario) use ($diaSemanaNombre) {
                return strtolower($horario->dia_semana) === strtolower($diaSemanaNombre);
            })->sortBy('hora_inicio');

            $registrosBiometricosDelDia = $registrosPorFecha->get($fechaString, collect([]));

            foreach ($horariosDelDia as $horario) {
                if (!$horario || !$horario->hora_inicio || !$horario->hora_fin) continue;
                $sessionData = $this->processSessionForReports($horario, $currentDate, $registrosBiometricosDelDia, $docente, $ciclo->fecha_inicio);
                if ($sessionData) $docenteSessions[] = $sessionData;
            }
            $currentDate->addDay();
        }

        $data = $this->structureDocenteDataForReports($docente, $docenteSessions);

        $fecha_generacion = Carbon::now()->format('d/m/Y H:i:s');
        // Generar QR en Base64 para el PDF
        $qrData = "REPORTE OFICIAL CEPRE UNAMAD\nDocente: {$docente->nombre} {$docente->apellido_paterno}\nCiclo: {$ciclo->nombre}\nFecha: {$fecha_generacion}\nValidación: " . uniqid();
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(100)->generate($qrData));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.asistencia-docente-pdf', compact(
            'docente', 
            'ciclo', 
            'data', 
            'fechaInicio', 
            'fechaFin', 
            'fecha_generacion',
            'qrCode'
        ));

        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'asistencia_' . $docente->numero_documento . '_' . Carbon::now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * NUEVO: Procesa una sesión individual para reportes
     */
    private function processSessionForReports($horario, $currentDate, $registrosBiometricosDelDia, $docente, $fechaInicioCiclo = null)
    {
        $horaInicioProgramada = Carbon::parse($horario->hora_inicio);
        $horaFinProgramada = Carbon::parse($horario->hora_fin);

        $horarioFinHoy = $currentDate->copy()->setTime($horaFinProgramada->hour, $horaFinProgramada->minute, $horaFinProgramada->second);

        // ⚡ FUENTE ÚNICA DE VERDAD: el trait calcula matching biométrico, horas, tardanza y pago.
        // Aquí solo enriquecemos con campos de presentación específicos del reporte/planilla.
        $base = $this->processTeacherSessionLogic($horario, $currentDate, $registrosBiometricosDelDia, $docente);

        $entradaCarbon = $base['entrada_carbon'];
        $salidaCarbon = $base['salida_carbon'];

        // Tema desarrollado (criterio de reportes: por defecto "Pendiente")
        $temaDesarrollado = $base['tema_desarrollado'] ?? 'Pendiente';

        // Horas (provenientes del trait, fuente única de la matemática de planilla)
        $horasProgramadas = $base['horas_programadas'];
        $horasDictadas = $base['horas_dictadas'];
        $estadoTexto = 'PENDIENTE';
        $duracionTexto = '00:00:00'; // Inicializar por defecto

        $cursoNombre = $horario->curso->nombre ?? 'N/A';
        $aulaNombre = $horario->aula->nombre ?? 'N/A';
        $turnoNombre = $horario->turno ?? 'N/A';

        // Determinar estado (criterio de reportes: por fecha y distinguiendo SIN TEMA).
        // Las horas/pago YA vienen calculados del trait; aquí solo se decide la etiqueta de estado.
        if ($entradaCarbon && $salidaCarbon) {
            // Validación Adicional: Para estar COMPLETADA debe tener tema registrado
            if ($temaDesarrollado && $temaDesarrollado !== 'Pendiente') {
                $estadoTexto = 'COMPLETADA';
            } else {
                $estadoTexto = 'SIN TEMA'; // Asistencia ok, pero falta tema
            }

            // Duración en texto HH:MM:SS a partir de las horas dictadas (trait)
            $segundos = (int) round($horasDictadas * 3600);
            $hH = floor($segundos / 3600);
            $mM = floor(($segundos - ($hH * 3600)) / 60);
            $sS = floor($segundos % 60);
            $duracionTexto = sprintf('%02d:%02d:%02d', $hH, $mM, $sS);
        } elseif ($entradaCarbon && !$salidaCarbon) {
            if ($currentDate->lessThan(Carbon::today()) || ($currentDate->isToday() && Carbon::now()->greaterThan($horarioFinHoy))) {
                $estadoTexto = 'INCOMPLETA';
            } else {
                $estadoTexto = 'EN CURSO';
            }
            $duracionTexto = '00:00:00';
        } elseif (!$entradaCarbon && $salidaCarbon) {
            // Marcó salida pero no entrada → asistencia incompleta
            $estadoTexto = 'INCOMPLETA';
            $duracionTexto = '00:00:00';
        } elseif (!$entradaCarbon && !$salidaCarbon) {
            if ($currentDate->lessThan(Carbon::today()) || ($currentDate->isToday() && Carbon::now()->greaterThan($horarioFinHoy))) {
                $estadoTexto = 'FALTA';
            } else {
                $estadoTexto = 'PROGRAMADA';
            }
            $duracionTexto = '00:00:00';
        }

        // Calcular pago
        $montoTotal = 0;
        $montoDescuento = 0;

        $pagoDocente = null;

        // Priorizar pago asignado al ciclo del horario para soportar m�ltiples ciclos activos
        if ($horario->ciclo_id) {
            $pagoDocente = PagoDocente::where('docente_id', $docente->id)
                ->where('ciclo_id', $horario->ciclo_id)
                ->orderBy('fecha_inicio', 'desc')
                ->first();
        }

        // Fallback por rango de fechas (compatibilidad con registros sin ciclo_id)
        if (!$pagoDocente) {
            $pagoDocente = PagoDocente::where('docente_id', $docente->id)
                ->whereDate('fecha_inicio', '<=', $currentDate)
                ->where(function ($query) use ($currentDate) {
                    $query->whereDate('fecha_fin', '>=', $currentDate)
                          ->orWhereNull('fecha_fin');
                })
                ->orderBy('fecha_inicio', 'desc')
                ->first();
        }

        if ($pagoDocente) {
            $montoTotal = $horasDictadas * $pagoDocente->tarifa_por_hora;
            $montoDescuento = ($horasProgramadas - $horasDictadas) * $pagoDocente->tarifa_por_hora;
        }
        
        // Evitar descuentos negativos por si acaso (aunque la lógica ya capa el fin)
        $montoDescuento = max(0, $montoDescuento);

        // FORMATO DE HORAS CORREGIDO - CON SEGUNDOS Y SIN DATOS FALSOS
        // Si no hay registro biométrico, mostramos "---" para que sea evidente la falta
        $horaEntradaDisplay = $entradaCarbon ? $entradaCarbon->format('g:i:s A') : '--:--:--';
        $horaSalidaDisplay = $salidaCarbon ? $salidaCarbon->format('g:i:s A') : '--:--:--';

        return [
            'fecha' => $currentDate->toDateString(),
            'curso' => $cursoNombre,
            'tema_desarrollado' => $temaDesarrollado,
            'aula' => $aulaNombre,
            'turno' => $turnoNombre,
            'hora_entrada' => $horaEntradaDisplay,
            'hora_salida' => $horaSalidaDisplay,
            'hora_entrada_prog' => $horaInicioProgramada->format('g:i A'),
            'hora_salida_prog' => $horaFinProgramada->format('g:i A'),
            'horas_dictadas' => $horasDictadas,
            'horas_programadas' => $horasProgramadas,
            'duracion_texto' => $duracionTexto,
            'pago' => $montoTotal,
            'monto_descuento' => $montoDescuento,
            'tarifa_por_hora' => $pagoDocente->tarifa_por_hora ?? 0,
            'estado_sesion' => $estadoTexto,
            'mes' => $currentDate->locale('es')->monthName,
            'semana' => floor($currentDate->diffInDays(Carbon::parse($fechaInicioCiclo), false) * -1 / 7) + 1,
            'carbon_date' => $currentDate->copy(),
            'tiene_registros' => ($entradaCarbon && $salidaCarbon) ? 'SI' : 'NO'
        ];
    }

    /**
     * Estructurar datos por docente para reportes con métricas de rendimiento.
     * Preserva todos los campos originales (total_horas, total_pagos, etc.)
     * y agrega métricas de estado de sesión para la barra de rendimiento visual.
     */
    private function structureDocenteDataForReports($docente, $sessions)
    {
        // ── ACUMULADORES ORIGINALES ────────────────────────────────────────────
        $groupedData    = [];
        $totalHoras     = 0;
        $totalPagos     = 0;
        $totalDescuentos = 0;

        // ── CONTADORES DE RENDIMIENTO POR DOCENTE ─────────────────────────────
        // Sesiones pasadas/del día donde la hora ya terminó.
        // PROGRAMADA / EN CURSO se cuentan por separado y NO entran al denominador.
        $contCompletada  = 0; // Asistió + tema registrado
        $contSinTema     = 0; // Asistió pero NO registró tema
        $contIncompleta  = 0; // Solo entrada O solo salida
        $contFalta       = 0; // Inasistencia total
        $contProgramada  = 0; // Sesión futura / en curso (no computable)
        // ──────────────────────────────────────────────────────────────────────

        foreach ($sessions as $session) {
            $mes    = $session['mes'];
            $semana = $session['semana'];

            if (!isset($groupedData[$mes])) {
                $groupedData[$mes] = [
                    'month_name'      => $mes,
                    'weeks'           => [],
                    'total_horas'     => 0,
                    'total_pagos'     => 0,
                    'total_descuentos'=> 0,
                    'rowspan'         => 0,
                ];
            }

            if (!isset($groupedData[$mes]['weeks'][$semana])) {
                $groupedData[$mes]['weeks'][$semana] = [
                    'week_number'     => sprintf('%02d', $semana),
                    'details'         => [],
                    'total_horas'     => 0,
                    'total_pagos'     => 0,
                    'total_descuentos'=> 0,
                    'rowspan'         => 0,
                ];
            }

            $groupedData[$mes]['weeks'][$semana]['details'][]         = $session;
            $groupedData[$mes]['weeks'][$semana]['total_horas']       += $session['horas_dictadas'];
            $groupedData[$mes]['weeks'][$semana]['total_pagos']       += $session['pago'];
            $groupedData[$mes]['weeks'][$semana]['total_descuentos']  += $session['monto_descuento'];
            $groupedData[$mes]['weeks'][$semana]['rowspan']++;

            $groupedData[$mes]['total_horas']      += $session['horas_dictadas'];
            $groupedData[$mes]['total_pagos']      += $session['pago'];
            $groupedData[$mes]['total_descuentos'] += $session['monto_descuento'];
            $groupedData[$mes]['rowspan']++;

            $totalHoras      += $session['horas_dictadas'];
            $totalPagos      += $session['pago'];
            $totalDescuentos += $session['monto_descuento'];

            // ── Conteo de estados para la barra de rendimiento ─────────────
            switch ($session['estado_sesion'] ?? 'PROGRAMADA') {
                case 'COMPLETADA':  $contCompletada++; break;
                case 'SIN TEMA':    $contSinTema++;    break;
                case 'INCOMPLETA':  $contIncompleta++; break;
                case 'FALTA':       $contFalta++;      break;
                default:            $contProgramada++; break; // PROGRAMADA / EN CURSO
            }
            // ───────────────────────────────────────────────────────────────
        }

        // Calcular redondeo y duración por semana / mes
        foreach ($groupedData as &$mesData) {
            foreach ($mesData['weeks'] as &$weekData) {
                $weekData['total_pagos_redondeado'] = round($weekData['total_pagos']);

                $wSeconds = round($weekData['total_horas'] * 3600);
                $wHours   = floor($wSeconds / 3600);
                $wMins    = floor(($wSeconds - ($wHours * 3600)) / 60);
                $wSecs    = floor($wSeconds % 60);
                $weekData['total_duracion_texto'] = sprintf('%d:%02d:%02d', $wHours, $wMins, $wSecs);
            }
            $mesData['total_pagos_redondeado'] = round($mesData['total_pagos']);
        }

        // ⚡ Redondeo FINAL sobre el total exacto (evita acumulación de errores)
        $totalPagosRedondeado = round($totalPagos);

        // Rowspan total para el docente (para la vista de planilla)
        $totalRowspan = 0;
        foreach ($groupedData as $monthData) {
            $totalRowspan += $monthData['rowspan'];
        }

        // Duración total en texto (HH:MM:SS)
        $totalSeconds = round($totalHoras * 3600);
        $tHours = floor($totalSeconds / 3600);
        $tMins  = floor(($totalSeconds - ($tHours * 3600)) / 60);
        $tSecs  = floor($totalSeconds % 60);
        $totalDuracionTexto = sprintf('%d:%02d:%02d', $tHours, $tMins, $tSecs);

        // ── CÁLCULO DE MÉTRICAS PORCENTUALES ──────────────────────────────────
        // Denominador: solo sesiones que YA debieron ocurrir
        $totalTranscurridas = $contCompletada + $contSinTema + $contIncompleta + $contFalta;

        // % Asistencia real (presentó entrada+salida independientemente del tema)
        $pctAsistencia = $totalTranscurridas > 0
            ? round(($contCompletada + $contSinTema) / $totalTranscurridas * 100, 1)
            : 0;

        // % Temas registrados sobre las sesiones en que efectivamente asistió
        $sesionesAsistidas = $contCompletada + $contSinTema;
        $pctTemas = $sesionesAsistidas > 0
            ? round($contCompletada / $sesionesAsistidas * 100, 1)
            : 0;

        // % Faltas absolutas
        $pctFaltas = $totalTranscurridas > 0
            ? round($contFalta / $totalTranscurridas * 100, 1)
            : 0;

        // Anchos proporcionales para la barra multi-segmento (sobre $totalTranscurridas)
        $wCompletada = $totalTranscurridas > 0 ? round($contCompletada / $totalTranscurridas * 100, 1) : 0;
        $wSinTema    = $totalTranscurridas > 0 ? round($contSinTema    / $totalTranscurridas * 100, 1) : 0;
        $wIncompleta = $totalTranscurridas > 0 ? round($contIncompleta / $totalTranscurridas * 100, 1) : 0;
        $wFalta      = $totalTranscurridas > 0 ? round($contFalta      / $totalTranscurridas * 100, 1) : 0;
        // ──────────────────────────────────────────────────────────────────────

        return [
            // ── Campos originales (no romper planilla-pdf ni demás vistas) ──
            'docente_info'          => $docente,
            'months'                => $groupedData,
            'total_horas'           => $totalHoras,
            'total_duracion_texto'  => $totalDuracionTexto,
            'total_pagos'           => $totalPagos,
            'total_descuentos'      => $totalDescuentos,
            'total_pagos_redondeado'=> $totalPagosRedondeado,
            'rowspan'               => $totalRowspan,

            // ── Métricas de rendimiento por docente ──────────────────────────
            'cont_completada'       => $contCompletada,
            'cont_sin_tema'         => $contSinTema,
            'cont_incompleta'       => $contIncompleta,
            'cont_falta'            => $contFalta,
            'cont_programada'       => $contProgramada,
            'total_transcurridas'   => $totalTranscurridas,
            'pct_asistencia'        => $pctAsistencia,
            'pct_temas'             => $pctTemas,
            'pct_faltas'            => $pctFaltas,
            'w_completada'          => $wCompletada,
            'w_sin_tema'            => $wSinTema,
            'w_incompleta'          => $wIncompleta,
            'w_falta'               => $wFalta,
            // ─────────────────────────────────────────────────────────────────
        ];
    }

    /**
     * Exportar reporte diario a Excel
     */
    public function exportarReporteDiario(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::today()->toDateString());
            $cicloId = $request->input('ciclo_id');
            $turno = $request->input('turno');
            
            $fechaCarbon = Carbon::parse($fecha);
            
            // Obtener ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            // Obtener el día de la semana correspondiente (tomando en cuenta rotaciones y recuperaciones)
            if ($cicloActivo) {
                $diaSemana = $cicloActivo->getDiaHorarioParaFecha($fechaCarbon);
            } else {
                $diaSemana = $fechaCarbon->locale('es')->dayName;
            }
            
            $horariosQuery = HorarioDocente::with(['docente', 'curso', 'aula', 'ciclo'])
                ->where('dia_semana', $diaSemana);
            
            if ($cicloId) {
                $horariosQuery->where('ciclo_id', $cicloId);
            }
            
            if ($turno) {
                $horariosQuery->where('turno', $turno);
            }
            
            $horarios = $horariosQuery->orderBy('hora_inicio')->get();
            
            $reporte = $horarios->map(function ($horario) use ($fechaCarbon) {
                // Buscar registros biométricos
                $registrosBiometricos = \App\Models\RegistroAsistencia::where('nro_documento', $horario->docente->numero_documento)
                    ->whereDate('fecha_registro', $fechaCarbon)
                    ->orderBy('fecha_registro')
                    ->get();
                
                $horaInicioProgramada = Carbon::parse($horario->hora_inicio);
                $horaFinProgramada = Carbon::parse($horario->hora_fin);
                $horarioInicioHoy = $fechaCarbon->copy()->setTime($horaInicioProgramada->hour, $horaInicioProgramada->minute, $horaInicioProgramada->second);
                $horarioFinHoy = $fechaCarbon->copy()->setTime($horaFinProgramada->hour, $horaFinProgramada->minute, $horaFinProgramada->second);
                
                $entradaBiometrica = $registrosBiometricos->filter(function($r) use ($horarioInicioHoy) {
                    $horaRegistro = Carbon::parse($r->fecha_registro);
                    return $horaRegistro->between($horarioInicioHoy->copy()->subMinutes(15), $horarioInicioHoy->copy()->addMinutes(120));
                })->sortBy('fecha_registro')->first();
                
                $salidaBiometrica = $registrosBiometricos->filter(function($r) use ($horarioFinHoy) {
                    $horaRegistro = Carbon::parse($r->fecha_registro);
                    return $horaRegistro->between($horarioFinHoy->copy()->subMinutes(15), $horarioFinHoy->copy()->addMinutes(60));
                })->sortByDesc('fecha_registro')->first();
                
                $asistenciaDocente = AsistenciaDocente::where('horario_id', $horario->id)
                    ->where('docente_id', $horario->docente_id)
                    ->whereDate('fecha_hora', $fechaCarbon)
                    ->first();
                
                return [
                    'docente' => $horario->docente ? $horario->docente->nombre . ' ' . $horario->docente->apellido_paterno : 'N/A',
                    'curso' => $horario->curso ? $horario->curso->nombre : 'N/A',
                    'aula' => $horario->aula ? $horario->aula->nombre : 'N/A',
                    'turno' => $horario->turno ?? 'N/A',
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'hora_entrada' => $entradaBiometrica ? Carbon::parse($entradaBiometrica->fecha_registro)->format('H:i') : '-',
                    'hora_salida' => $salidaBiometrica ? Carbon::parse($salidaBiometrica->fecha_registro)->format('H:i') : '-',
                    'horas_dictadas' => $asistenciaDocente && $asistenciaDocente->horas_dictadas ? round($asistenciaDocente->horas_dictadas, 2) : 0,
                    'tema_desarrollado' => $asistenciaDocente ? ($asistenciaDocente->tema_desarrollado ?? 'Pendiente') : 'Pendiente',
                    'estado' => $entradaBiometrica && $salidaBiometrica ? 'Asistió' : 'Falta',
                ];
            });
            
            $estadisticas = [
                'total_clases' => $reporte->count(),
                'total_asistencias' => $reporte->where('estado', 'Asistió')->count(),
                'total_faltas' => $reporte->where('estado', 'Falta')->count(),
                'total_temas_pendientes' => $reporte->where('tema_desarrollado', 'Pendiente')->count(),
                'total_horas_dictadas' => $reporte->sum('horas_dictadas'),
            ];
            
            $nombreArchivo = 'Reporte_Diario_Docentes_' . $fechaCarbon->format('Y-m-d') . '.xlsx';
            
            return Excel::download(
                new \App\Exports\ReporteDiarioDocenteExport($reporte, $estadisticas, $fechaCarbon->format('d/m/Y')),
                $nombreArchivo
            );
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al exportar: ' . $e->getMessage()]);
        }
    }

    /**
     * Enviar notificaciones masivas por WhatsApp
     */
    public function notificarMasivoWhatsApp(Request $request)
    {
        try {
            $request->validate([
                'tipo' => 'required|in:tema_pendiente,falta,recordatorio',
                'fecha' => 'nullable|date',
            ]);
            
            $fecha = $request->input('fecha', Carbon::today()->toDateString());
            $fechaCarbon = Carbon::parse($fecha);
            $tipo = $request->tipo;
            
            $docentes = [];
            
            if ($tipo === 'tema_pendiente') {
                $asistenciasSinTema = AsistenciaDocente::with(['docente', 'horario.curso', 'horario.aula'])
                    ->whereDate('fecha_hora', $fechaCarbon)
                    ->where('estado', 'salida')
                    ->whereNull('tema_desarrollado')
                    ->get();
                
                foreach ($asistenciasSinTema as $asistencia) {
                    if ($asistencia->docente && $asistencia->docente->telefono) {
                        $data = [
                            'docente_nombre' => $asistencia->docente->nombre . ' ' . $asistencia->docente->apellido_paterno,
                            'curso' => $asistencia->horario && $asistencia->horario->curso ? $asistencia->horario->curso->nombre : 'el curso',
                            'fecha' => $fechaCarbon->format('d/m/Y'),
                            'hora' => Carbon::parse($asistencia->fecha_hora)->format('H:i'),
                        ];
                        
                        $mensaje = \App\Helpers\WhatsAppHelper::getMessageTemplate($tipo, $data);
                        $link = \App\Helpers\WhatsAppHelper::generateLink($asistencia->docente->telefono, $mensaje);
                        
                        $docentes[] = [
                            'id' => $asistencia->docente_id,
                            'nombre' => $data['docente_nombre'],
                            'telefono' => $asistencia->docente->telefono,
                            'link' => $link,
                            'mensaje' => $mensaje,
                        ];
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'total' => count($docentes),
                'docentes' => $docentes,
                'message' => count($docentes) . ' docentes encontrados para notificar'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para gráficos estadísticos
     */
    public function getEstadisticasGraficos(Request $request)
    {
        try {
            $fecha = $request->input('fecha', Carbon::today()->toDateString());
            $fechaCarbon = Carbon::parse($fecha);
            
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            // Obtener el día de la semana correspondiente (tomando en cuenta rotaciones y recuperaciones)
            if ($cicloActivo) {
                $diaSemana = $cicloActivo->getDiaHorarioParaFecha($fechaCarbon);
            } else {
                $diaSemana = $fechaCarbon->locale('es')->dayName;
            }
            
            $horariosQuery = HorarioDocente::with(['docente', 'curso'])
                ->where('dia_semana', $diaSemana);
            
            if ($cicloActivo) {
                $horariosQuery->where('ciclo_id', $cicloActivo->id);
            }
            
            $horarios = $horariosQuery->get();
            
            $asistencias = 0;
            $faltas = 0;
            $temasPendientes = 0;
            $temasRegistrados = 0;
            
            foreach ($horarios as $horario) {
                $asistenciaEntrada = AsistenciaDocente::where('horario_id', $horario->id)
                    ->where('docente_id', $horario->docente_id)
                    ->whereDate('fecha_hora', $fechaCarbon)
                    ->where('estado', 'entrada')
                    ->first();
                
                $asistenciaSalida = AsistenciaDocente::where('horario_id', $horario->id)
                    ->where('docente_id', $horario->docente_id)
                    ->whereDate('fecha_hora', $fechaCarbon)
                    ->where('estado', 'salida')
                    ->first();
                
                if ($asistenciaEntrada && $asistenciaSalida) {
                    $asistencias++;
                    if ($asistenciaSalida->tema_desarrollado) {
                        $temasRegistrados++;
                    } else {
                        $temasPendientes++;
                    }
                } else {
                    $faltas++;
                }
            }
            
            $asistenciasPorHora = AsistenciaDocente::whereDate('fecha_hora', $fechaCarbon)
                ->where('estado', 'entrada')
                ->selectRaw('HOUR(fecha_hora) as hora, COUNT(*) as total')
                ->groupBy('hora')
                ->orderBy('hora')
                ->get()
                ->pluck('total', 'hora')
                ->toArray();
            
            return response()->json([
                'success' => true,
                'fecha' => $fechaCarbon->format('d/m/Y'),
                'asistencias_vs_faltas' => [
                    'labels' => ['Asistencias', 'Faltas'],
                    'data' => [$asistencias, $faltas],
                    'colors' => ['#28a745', '#dc3545'],
                ],
                'temas_status' => [
                    'labels' => ['Temas Registrados', 'Temas Pendientes'],
                    'data' => [$temasRegistrados, $temasPendientes],
                    'colors' => ['#28a745', '#ffc107'],
                ],
                'asistencias_por_hora' => [
                    'labels' => array_keys($asistenciasPorHora),
                    'data' => array_values($asistenciasPorHora),
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function indexReportes()
    {
        $user = auth()->user();
        
        // Obtener ciclos activos donde el docente tiene carga horaria (o todos los activos si se prefiere)
        // Usamos la misma lógica del dashboard para consistencia
        $ciclosActivos = Ciclo::where('es_activo', true)->orderBy('fecha_inicio', 'desc')->get();
        
        return view('reportes.profesor-index', compact('user', 'ciclosActivos'));
    }
}