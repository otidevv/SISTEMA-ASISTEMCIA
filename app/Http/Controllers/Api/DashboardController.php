<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\Inscripcion;
use App\Models\RegistroAsistencia;
use App\Models\Ciclo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\AsistenciaDocente;
use App\Models\HorarioDocente;
use App\Models\PagoDocente;
use App\Models\User;
use App\Models\Postulacion;
use App\Models\Carnet;
use App\Models\ResultadoExamen;
use App\Models\Carrera;
use App\Models\Curso;

class DashboardController extends Controller
{
    /**
     * Obtener datos generales del dashboard CON CONTEXTO DEL CICLO ACTIVO
     */
    public function getDatosGenerales()
    {
        try {
            $user = Auth::user();
            $cicloActivo = Cache::remember('ciclo_activo', 300, function () {
                return Ciclo::where('es_activo', true)->first();
            });

            $data = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ];

            if (!$cicloActivo) {
                return response()->json(array_merge($data, [
                    'cicloActivo' => null,
                    'totalInscritosActivos' => 0,
                    'estudiantesConAsistencia' => 0,
                    'totalDocentesActivos' => 0,
                    'totalAulasAsignadas' => 0,
                    'asistenciaHoy' => [
                        'total_registros' => 0,
                        'estudiantes_unicos' => 0,
                        'porcentaje_asistencia' => 0
                    ]
                ]));
            }

            // INFORMACIÓN DEL CICLO ACTIVO
            $hoy = Carbon::now();
            $totalDias = (int) $cicloActivo->fecha_inicio->diffInDays($cicloActivo->fecha_fin);
            $diasTranscurridos = (int) max(0, $cicloActivo->fecha_inicio->diffInDays($hoy));
            $diasRestantes = (int) max(0, $hoy->diffInDays($cicloActivo->fecha_fin));
            
            $proximoExamen = $cicloActivo->getProximoExamen();
            
            $data['cicloActivo'] = [
                'id' => $cicloActivo->id,
                'nombre' => $cicloActivo->nombre,
                'fecha_inicio' => $cicloActivo->fecha_inicio->format('d/m/Y'),
                'fecha_fin' => $cicloActivo->fecha_fin->format('d/m/Y'),
                'total_dias' => $totalDias,
                'dias_transcurridos' => $diasTranscurridos,
                'dias_restantes' => $diasRestantes,
                'progreso_porcentaje' => $totalDias > 0 ? round(($diasTranscurridos / $totalDias) * 100, 1) : 0,
                'porcentaje_amonestacion' => $cicloActivo->porcentaje_amonestacion,
                'porcentaje_inhabilitacion' => $cicloActivo->porcentaje_inhabilitacion,
                'proximo_examen' => $proximoExamen ? [
                    'nombre' => $proximoExamen['nombre'],
                    'fecha' => $proximoExamen['fecha']->format('d/m/Y'),
                    'dias_faltantes' => (int) max(0, $hoy->diffInDays($proximoExamen['fecha'], false))
                ] : null
            ];

            // ESTADÍSTICAS DEL CICLO ACTIVO (con caché)
            $stats = Cache::remember("dashboard.stats.{$cicloActivo->id}", 600, function () use ($cicloActivo) {
                return [
                    'totalInscritosActivos' => Inscripcion::where('ciclo_id', $cicloActivo->id)
                        ->where('estado_inscripcion', 'activo')
                        ->count(),
                    
                    'estudiantesConAsistencia' => DB::table('inscripciones as i')
                        ->join('users as u', 'i.estudiante_id', '=', 'u.id')
                        ->join('registros_asistencia as ra', 'u.numero_documento', '=', 'ra.nro_documento')
                        ->where('i.ciclo_id', $cicloActivo->id)
                        ->where('i.estado_inscripcion', 'activo')
                        ->whereBetween('ra.fecha_registro', [$cicloActivo->fecha_inicio, $cicloActivo->fecha_fin])
                        ->distinct('u.id')
                        ->count('u.id'),
                    
                    'totalDocentesActivos' => HorarioDocente::where('ciclo_id', $cicloActivo->id)
                        ->distinct('docente_id')
                        ->count('docente_id'),
                    
                    'totalAulasAsignadas' => Inscripcion::where('ciclo_id', $cicloActivo->id)
                        ->where('estado_inscripcion', 'activo')
                        ->whereNotNull('aula_id')
                        ->distinct('aula_id')
                        ->count('aula_id')
                ];
            });

            $data = array_merge($data, $stats);

            // ASISTENCIA DE HOY (tiempo real, sin caché)
            $today = Carbon::today();
            $registrosHoy = RegistroAsistencia::whereDate('fecha_registro', $today)->count();
            $estudiantesHoy = RegistroAsistencia::whereDate('fecha_registro', $today)
                ->where('nro_documento', '!=', '')
                ->distinct('nro_documento')
                ->count('nro_documento');

            $data['asistenciaHoy'] = [
                'total_registros' => $registrosHoy,
                'estudiantes_unicos' => $estudiantesHoy,
                'porcentaje_asistencia' => $data['totalInscritosActivos'] > 0 
                    ? round(($estudiantesHoy / $data['totalInscritosActivos']) * 100, 1) 
                    : 0
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error en getDatosGenerales: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener datos generales'], 500);
        }
    }

    /**
     * Obtener anuncios activos
     */
    public function getAnuncios()
    {
        try {
            $anuncios = Cache::remember('dashboard.anuncios', 300, function () {
                return Anuncio::select('id', 'titulo', 'contenido', 'fecha_publicacion')
                    ->where('es_activo', true)
                    ->where(function ($query) {
                        $query->whereNull('fecha_expiracion')
                               ->orWhere('fecha_expiracion', '>', now());
                    })
                    ->where('fecha_publicacion', '<=', now())
                    ->orderBy('fecha_publicacion', 'desc')
                    ->take(3)
                    ->get();
            });

            return response()->json($anuncios);
        } catch (\Exception $e) {
            \Log::error('Error en getAnuncios: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }

    /**
     * Obtener datos COMPLETOS del dashboard administrativo
     */
    public function getDatosAdmin()
    {
        try {
            $user = Auth::user();

            // Verificar acceso
            $rolesAdministrativos = [
                'admin',
                'ADMINISTRATIVOS',
                'CEPRE UNAMAD MONITOREO',
                'COORDINACIÓN ACADEMICA',
                'ASISTENTE ADMINISTRATIVO II'
            ];

            $tieneAcceso = collect($rolesAdministrativos)->some(fn($rol) => $user->hasRole($rol));

            if (!$tieneAcceso && !$user->hasPermission('dashboard.admin')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $cicloActivo = Ciclo::where('es_activo', true)->first();

            if (!$cicloActivo) {
                return response()->json([
                    'error' => 'No hay ciclo activo',
                    'cicloActivo' => null
                ]);
            }

            $hoy = Carbon::now();
            $data = [];

            // INFORMACIÓN DEL CICLO
            $totalDias = (int) $cicloActivo->fecha_inicio->diffInDays($cicloActivo->fecha_fin);
            $diasTranscurridos = (int) max(0, $cicloActivo->fecha_inicio->diffInDays($hoy));
            $diasRestantes = (int) max(0, $hoy->diffInDays($cicloActivo->fecha_fin));
            $proximoExamen = $cicloActivo->getProximoExamen();

            $data['cicloActivo'] = [
                'id' => $cicloActivo->id,
                'nombre' => $cicloActivo->nombre,
                'fecha_inicio' => $cicloActivo->fecha_inicio->format('d/m/Y'),
                'fecha_fin' => $cicloActivo->fecha_fin->format('d/m/Y'),
                'dias_transcurridos' => $diasTranscurridos,
                'dias_restantes' => $diasRestantes,
                'progreso_porcentaje' => $totalDias > 0 ? round(($diasTranscurridos / $totalDias) * 100, 1) : 0,
                'proximo_examen' => $proximoExamen ? [
                    'nombre' => $proximoExamen['nombre'],
                    'fecha' => $proximoExamen['fecha']->format('d/m/Y'),
                    'dias_faltantes' => (int) max(0, $hoy->diffInDays($proximoExamen['fecha'], false))
                ] : null
            ];

            // ESTADÍSTICAS EN PARALELO (optimizado)
            $cacheKey = "dashboard.admin.{$cicloActivo->id}." . $hoy->format('Y-m-d');
            
            $estadisticas = Cache::remember($cacheKey, 900, function () use ($cicloActivo, $hoy) {
                // Todas las consultas en una sola transacción
                return DB::transaction(function () use ($cicloActivo, $hoy) {
                    $stats = [];

                    // Inscripciones
                    $stats['totalInscripciones'] = Inscripcion::where('ciclo_id', $cicloActivo->id)
                        ->where('estado_inscripcion', 'activo')
                        ->count();

                    // Postulaciones
                    $postulaciones = Postulacion::where('ciclo_id', $cicloActivo->id)
                        ->selectRaw('
                            COUNT(*) as total,
                            SUM(CASE WHEN estado = "pendiente" THEN 1 ELSE 0 END) as pendientes,
                            SUM(CASE WHEN estado = "aprobado" THEN 1 ELSE 0 END) as aprobadas,
                            SUM(CASE WHEN estado = "rechazado" THEN 1 ELSE 0 END) as rechazadas
                        ')
                        ->first();

                    $stats['postulaciones'] = [
                        'total' => $postulaciones->total ?? 0,
                        'pendientes' => $postulaciones->pendientes ?? 0,
                        'aprobadas' => $postulaciones->aprobadas ?? 0,
                        'rechazadas' => $postulaciones->rechazadas ?? 0
                    ];

                    // Carnets
                    $carnets = Carnet::where('ciclo_id', $cicloActivo->id)
                        ->selectRaw('
                            COUNT(*) as total,
                            SUM(CASE WHEN impreso = 0 THEN 1 ELSE 0 END) as pendientes_impresion,
                            SUM(CASE WHEN impreso = 1 AND entregado = 0 THEN 1 ELSE 0 END) as pendientes_entrega,
                            SUM(CASE WHEN entregado = 1 THEN 1 ELSE 0 END) as entregados
                        ')
                        ->first();

                    $stats['carnets'] = [
                        'total' => $carnets->total ?? 0,
                        'pendientes_impresion' => $carnets->pendientes_impresion ?? 0,
                        'pendientes_entrega' => $carnets->pendientes_entrega ?? 0,
                        'entregados' => $carnets->entregados ?? 0
                    ];

                    // Docentes y horarios
                    $stats['totalDocentesActivos'] = HorarioDocente::where('ciclo_id', $cicloActivo->id)
                        ->distinct('docente_id')
                        ->count('docente_id');

                    $stats['sesionesHoy'] = HorarioDocente::where('ciclo_id', $cicloActivo->id)
                        ->where('dia_semana', strtolower($hoy->locale('es')->dayName))
                        ->count();

                    // Resultados de exámenes
                    $resultados = ResultadoExamen::where('ciclo_id', $cicloActivo->id)
                        ->selectRaw('
                            COUNT(*) as total,
                            SUM(CASE WHEN visible = 1 THEN 1 ELSE 0 END) as publicados
                        ')
                        ->first();

                    $stats['resultadosExamenes'] = [
                        'total' => $resultados->total ?? 0,
                        'publicados' => $resultados->publicados ?? 0
                    ];

                    // Carreras, aulas y cursos
                    $stats['totalCarreras'] = Carrera::where('estado', true)->count();
                    $stats['totalCursos'] = Curso::where('estado', true)->count();
                    $stats['totalAulas'] = Inscripcion::where('ciclo_id', $cicloActivo->id)
                        ->where('estado_inscripcion', 'activo')
                        ->whereNotNull('aula_id')
                        ->distinct('aula_id')
                        ->count('aula_id');

                    // Estadísticas de asistencia
                    $stats['estadisticasAsistencia'] = $this->obtenerEstadisticasGenerales($cicloActivo);

                    return $stats;
                });
            });

            $data = array_merge($data, $estadisticas);

            // ASISTENCIA DE HOY (tiempo real)
            $today = Carbon::today();
            $registrosHoy = RegistroAsistencia::whereDate('fecha_registro', $today)->count();
            $estudiantesHoy = RegistroAsistencia::whereDate('fecha_registro', $today)
                ->distinct('nro_documento')
                ->count('nro_documento');

            $data['asistenciaHoy'] = [
                'total_registros' => $registrosHoy,
                'estudiantes_unicos' => $estudiantesHoy,
                'porcentaje' => $data['totalInscripciones'] > 0 
                    ? round(($estudiantesHoy / $data['totalInscripciones']) * 100, 1) 
                    : 0
            ];

            // ALERTAS INTELIGENTES
            $alertas = [];

            if ($data['postulaciones']['pendientes'] > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'mensaje' => "{$data['postulaciones']['pendientes']} postulaciones pendientes de revisión",
                    'icono' => 'mdi-alert-circle',
                    'url' => route('postulaciones.index')
                ];
            }

            if ($data['carnets']['pendientes_impresion'] > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'mensaje' => "{$data['carnets']['pendientes_impresion']} carnets pendientes de impresión",
                    'icono' => 'mdi-card-account-details',
                    'url' => route('carnets.index')
                ];
            }

            $estudiantesEnRiesgo = $data['estadisticasAsistencia']['amonestados'] + 
                                   $data['estadisticasAsistencia']['inhabilitados'];
            
            if ($estudiantesEnRiesgo > 0) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'mensaje' => "{$estudiantesEnRiesgo} estudiantes en riesgo (amonestados/inhabilitados)",
                    'icono' => 'mdi-account-alert',
                    'url' => route('asistencia.index')
                ];
            }

            if ($proximoExamen) {
                $diasFaltantes = max(0, $hoy->diffInDays($proximoExamen['fecha'], false));
                
                if ($diasFaltantes <= 7) {
                    $alertas[] = [
                        'tipo' => 'info',
                        'mensaje' => "{$proximoExamen['nombre']} en {$diasFaltantes} días",
                        'icono' => 'mdi-calendar-clock',
                        'url' => '#'
                    ];
                }
            }

            $data['alertas'] = $alertas;

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error en getDatosAdmin: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Error al obtener datos administrativos'], 500);
        }
    }

    /**
     * Obtener estadísticas de asistencia (endpoint separado)
     */
    public function getEstadisticasAsistencia()
    {
        try {
            $user = Auth::user();

            $rolesAdministrativos = [
                'admin',
                'ADMINISTRATIVOS',
                'CEPRE UNAMAD MONITOREO',
                'COORDINACIÓN ACADEMICA',
                'ASISTENTE ADMINISTRATIVO II'
            ];

            $tieneAcceso = collect($rolesAdministrativos)->some(fn($rol) => $user->hasRole($rol));

            if (!$tieneAcceso && !$user->hasPermission('dashboard.admin')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $cicloActivo = Ciclo::where('es_activo', true)->first();

            if (!$cicloActivo) {
                return response()->json([
                    'error' => 'No hay ciclo activo',
                    'estadisticas' => null
                ]);
            }

            $estadisticas = $this->obtenerEstadisticasGenerales($cicloActivo);
            
            $estudiantesEnRiesgo = $estadisticas['amonestados'] + $estadisticas['inhabilitados'];
            
            $alerta = null;
            if ($estudiantesEnRiesgo > 0) {
                $alerta = [
                    'tipo' => 'danger',
                    'mensaje' => "{$estudiantesEnRiesgo} estudiantes en riesgo (amonestados/inhabilitados)",
                    'icono' => 'mdi-account-alert',
                    'url' => route('asistencia.index')
                ];
            }

            return response()->json([
                'estadisticas' => $estadisticas,
                'alerta' => $alerta
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getEstadisticasAsistencia: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener estadísticas de asistencia'], 500);
        }
    }

    /**
     * Obtener datos del dashboard de estudiante
     */
    public function getDatosEstudiante()
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('estudiante')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $inscripcionActiva = Inscripcion::where('estudiante_id', $user->id)
                ->where('estado_inscripcion', 'activo')
                ->whereHas('ciclo', fn($q) => $q->where('es_activo', true))
                ->with(['ciclo', 'carrera', 'aula', 'turno'])
                ->first();

            $data = [
                'inscripcionActiva' => $inscripcionActiva,
                'infoAsistencia' => []
            ];

            if ($inscripcionActiva) {
                $ciclo = $inscripcionActiva->ciclo;
                
                $primerRegistro = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                    ->whereBetween('fecha_registro', [$ciclo->fecha_inicio, $ciclo->fecha_fin])
                    ->orderBy('fecha_registro')
                    ->first();

                if ($primerRegistro) {
                    $data['infoAsistencia']['primer_examen'] = $this->calcularAsistenciaExamen(
                        $user->numero_documento,
                        $primerRegistro->fecha_registro,
                        $ciclo->fecha_primer_examen,
                        $ciclo
                    );

                    if ($ciclo->fecha_segundo_examen) {
                        $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen, $ciclo);
                        $data['infoAsistencia']['segundo_examen'] = $this->calcularAsistenciaExamen(
                            $user->numero_documento,
                            $inicioSegundo,
                            $ciclo->fecha_segundo_examen,
                            $ciclo
                        );
                    }

                    if ($ciclo->fecha_tercer_examen && $ciclo->fecha_segundo_examen) {
                        $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen, $ciclo);
                        $data['infoAsistencia']['tercer_examen'] = $this->calcularAsistenciaExamen(
                            $user->numero_documento,
                            $inicioTercero,
                            $ciclo->fecha_tercer_examen,
                            $ciclo
                        );
                    }

                    $data['infoAsistencia']['total_ciclo'] = $this->calcularAsistenciaExamen(
                        $user->numero_documento,
                        $primerRegistro->fecha_registro,
                        min(Carbon::now(), Carbon::parse($ciclo->fecha_fin)),
                        $ciclo
                    );
                }

                $data['primerRegistro'] = $primerRegistro;
            }

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error en getDatosEstudiante: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener datos del estudiante'], 500);
        }
    }

    /**
     * Obtener datos del dashboard de profesor
     */
    public function getDatosProfesor(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasRole('profesor')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $fechaSeleccionada = $request->input('fecha') ? 
                Carbon::parse($request->input('fecha')) : Carbon::today();
            
            $diaSemanaSeleccionada = strtolower($fechaSeleccionada->locale('es')->dayName);
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            $horariosDelDia = HorarioDocente::where('docente_id', $user->id)
                ->where('dia_semana', $diaSemanaSeleccionada)
                ->with(['aula', 'curso', 'ciclo'])
                ->orderBy('hora_inicio')
                ->get();
            
            $registrosDelDia = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaSeleccionada)
                ->orderBy('fecha_registro')
                ->get();

            $horasDelDia = 0;
            $sesionesPendientes = 0;
            $tarifaPorHora = $this->obtenerTarifaDocente($user->id, $cicloActivo);
            
            $horariosConDetalles = $horariosDelDia->map(function ($horario) use (
                $registrosDelDia, 
                &$horasDelDia, 
                $user, 
                &$sesionesPendientes, 
                $fechaSeleccionada
            ) {
                $horaInicio = Carbon::parse($horario->hora_inicio);
                $horaFin = Carbon::parse($horario->hora_fin);
                
                $horarioInicioHoy = $fechaSeleccionada->copy()->setTime($horaInicio->hour, $horaInicio->minute);
                $horarioFinHoy = $fechaSeleccionada->copy()->setTime($horaFin->hour, $horaFin->minute);
                
                $entrada = $registrosDelDia->filter(function($r) use ($horarioInicioHoy) {
                    $horaRegistro = Carbon::parse($r->fecha_registro);
                    return $horaRegistro->between(
                        $horarioInicioHoy->copy()->subMinutes(15),
                        $horarioInicioHoy->copy()->addMinutes(30)
                    );
                })->first();
                
                $salida = $registrosDelDia->filter(function($r) use ($horarioFinHoy) {
                    $horaRegistro = Carbon::parse($r->fecha_registro);
                    return $horaRegistro->between(
                        $horarioFinHoy->copy()->subMinutes(15),
                        $horarioFinHoy->copy()->addMinutes(60)
                    );
                })->sortByDesc('fecha_registro')->first();
                
                if ($entrada && $salida) {
                    $hEntrada = Carbon::parse($entrada->fecha_registro);
                    $hSalida = Carbon::parse($salida->fecha_registro);
                    if ($hSalida->greaterThan($hEntrada)) {
                        $horasDelDia += $hSalida->diffInMinutes($hEntrada) / 60;
                    }
                }
                
                $asistencia = AsistenciaDocente::where('docente_id', $user->id)
                    ->where('horario_id', $horario->id)
                    ->whereDate('fecha_hora', $fechaSeleccionada)
                    ->first();
                
                $claseTerminada = Carbon::now()->greaterThan($horarioFinHoy);
                if ($claseTerminada && !$asistencia && $entrada && $salida) {
                    $sesionesPendientes++;
                }
                
                return [
                    'horario' => $horario,
                    'hora_entrada_registrada' => $entrada ? Carbon::parse($entrada->fecha_registro)->format('H:i A') : null,
                    'hora_salida_registrada' => $salida ? Carbon::parse($salida->fecha_registro)->format('H:i A') : null,
                    'asistencia' => $asistencia,
                    'tiene_registros' => $entrada && $salida,
                    'clase_terminada' => $claseTerminada
                ];
            });

            $pagoEstimadoHoy = $this->calcularPagoEstimado($user->id, $fechaSeleccionada, $horariosDelDia, $tarifaPorHora);
            
            $resumenSemanal = AsistenciaDocente::where('docente_id', $user->id)
                ->whereBetween('fecha_hora', [
                    Carbon::now()->subDays(6)->startOfDay(), 
                    Carbon::now()->endOfDay()
                ])
                ->selectRaw('
                    COUNT(*) as total_sesiones,
                    SUM(horas_dictadas) as total_horas,
                    SUM(monto_total) as total_ingresos,
                    AVG(CASE WHEN estado = "completada" THEN 1 ELSE 0 END) * 100 as porcentaje_asistencia
                ')
                ->first();
            
            return response()->json([
                'fechaSeleccionada' => $fechaSeleccionada->format('Y-m-d'),
                'horariosDelDia' => $horariosConDetalles,
                'horasHoy' => round($horasDelDia, 2),
                'sesionesHoy' => $horariosDelDia->count(),
                'sesionesPendientes' => $sesionesPendientes,
                'tarifaPorHora' => $tarifaPorHora,
                'pagoEstimadoHoy' => $pagoEstimadoHoy,
                'resumenSemanal' => [
                    'sesiones' => $resumenSemanal->total_sesiones ?? 0,
                    'horas' => round($resumenSemanal->total_horas ?? 0, 2),
                    'ingresos' => $resumenSemanal->total_ingresos ?? 0,
                    'asistencia' => round($resumenSemanal->porcentaje_asistencia ?? 0)
                ],
                'proximaClase' => $this->obtenerProximaClase($user->id)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en getDatosProfesor: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener datos del profesor'], 500);
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    
    private function obtenerTarifaDocente($docenteId, $cicloActivo = null)
    {
        if (!$cicloActivo) {
            $cicloActivo = Ciclo::where('es_activo', true)->first();
        }

        if ($cicloActivo) {
            $pagoDocente = PagoDocente::where('docente_id', $docenteId)
                ->where('fecha_inicio', '<=', $cicloActivo->fecha_fin)
                ->where(function ($query) use ($cicloActivo) {
                    $query->where('fecha_fin', '>=', $cicloActivo->fecha_inicio)
                          ->orWhereNull('fecha_fin');
                })
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            if ($pagoDocente) {
                return $pagoDocente->tarifa_por_hora;
            }
        }

        $user = User::find($docenteId);
        return $user->tarifa_por_hora ?? 25.00;
    }

    private function calcularPagoEstimado($docenteId, $fechaSeleccionada, $horariosDelDia, $tarifaPorHora)
    {
        $pagoReal = AsistenciaDocente::where('docente_id', $docenteId)
            ->whereDate('fecha_hora', $fechaSeleccionada)
            ->sum('monto_total');

        if ($pagoReal > 0) {
            return $pagoReal;
        }

        $totalHorasEstimadas = 0;
        foreach ($horariosDelDia as $horario) {
            $horaInicio = Carbon::parse($horario->hora_inicio);
            $horaFin = Carbon::parse($horario->hora_fin);
            $totalHorasEstimadas += $horaInicio->diffInMinutes($horaFin) / 60;
        }

        return $totalHorasEstimadas * $tarifaPorHora;
    }

    private function obtenerProximaClase($docenteId)
    {
        $ahora = Carbon::now();
        $diaActualSemana = strtolower($ahora->locale('es')->dayName);

        $proximaClaseHoy = HorarioDocente::where('docente_id', $docenteId)
            ->where('dia_semana', $diaActualSemana)
            ->where('hora_inicio', '>', $ahora->format('H:i:s'))
            ->with(['aula', 'curso'])
            ->orderBy('hora_inicio')
            ->first();

        if ($proximaClaseHoy) {
            return $proximaClaseHoy;
        }

        $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        $diaActualIndex = array_search($diaActualSemana, $diasSemana);
        
        for ($i = 1; $i <= 7; $i++) {
            $indexDia = ($diaActualIndex + $i) % 7;
            $diaBuscado = $diasSemana[$indexDia];
            
            $claseEncontrada = HorarioDocente::where('docente_id', $docenteId)
                ->where('dia_semana', $diaBuscado)
                ->with(['aula', 'curso'])
                ->orderBy('hora_inicio')
                ->first();
            
            if ($claseEncontrada) {
                return $claseEncontrada;
            }
        }

        return null;
    }

    private function calcularAsistenciaExamen($numeroDocumento, $fechaInicio, $fechaExamen, $ciclo)
    {
        $hoy = Carbon::now()->startOfDay();
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaExamenCarbon = Carbon::parse($fechaExamen)->startOfDay();
        $fechaFinCalculo = $hoy < $fechaExamenCarbon ? $hoy : $fechaExamenCarbon;

        if ($fechaInicioCarbon > $hoy) {
            return [
                'dias_habiles' => 0,
                'dias_asistidos' => 0,
                'dias_falta' => 0,
                'porcentaje_asistencia' => 0,
                'porcentaje_inasistencia' => 0,
                'estado' => 'pendiente',
                'mensaje' => 'Este período aún no ha comenzado.',
                'puede_rendir' => true
            ];
        }

        $diasHabilesTotales = $this->contarDiasHabiles($fechaInicio, $fechaExamen, $ciclo);
        $diasHabilesTranscurridos = $this->contarDiasHabiles($fechaInicio, $fechaFinCalculo, $ciclo);

        $registros = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                $fechaInicioCarbon->copy()->startOfDay(),
                $fechaFinCalculo->copy()->endOfDay()
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        $diasConAsistencia = 0;
        foreach ($registros as $fecha) {
            if ($ciclo->esDiaHabil(Carbon::parse($fecha))) {
                $diasConAsistencia++;
            }
        }

        $diasFaltaActuales = max(0, $diasHabilesTranscurridos - $diasConAsistencia);
        $porcentajeAsistenciaProyectado = $diasHabilesTotales > 0 ?
            round(($diasConAsistencia / $diasHabilesTotales) * 100, 2) : 0;
        $porcentajeInasistenciaProyectado = 100 - $porcentajeAsistenciaProyectado;

        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

        $estado = 'regular';
        $mensaje = 'Tu asistencia es regular.';
        $puedeRendir = true;

        if ($diasFaltaActuales >= $limiteInhabilitacion) {
            $estado = 'inhabilitado';
            $mensaje = 'Usted ha superado el límite máximo de inasistencias (30%). De acuerdo con el Reglamento Académico, queda INHABILITADO y NO puede rendir el presente examen.';
            $puedeRendir = false;
        } elseif ($diasFaltaActuales >= $limiteAmonestacion) {
            $estado = 'amonestado';
            $mensaje = 'ADVERTENCIA: Ha superado el umbral de amonestación (20%). De continuar con inasistencias, quedará inhabilitado para el examen según el reglamento.';
        }

        return [
            'dias_habiles' => $diasHabilesTotales,
            'dias_habiles_transcurridos' => $diasHabilesTranscurridos,
            'dias_asistidos' => $diasConAsistencia,
            'dias_falta' => $diasFaltaActuales,
            'porcentaje_asistencia' => $porcentajeAsistenciaProyectado,
            'porcentaje_inasistencia' => $porcentajeInasistenciaProyectado,
            'limite_amonestacion' => $limiteAmonestacion,
            'limite_inhabilitacion' => $limiteInhabilitacion,
            'estado' => $estado,
            'mensaje' => $mensaje,
            'puede_rendir' => $puedeRendir
        ];
    }

    private function contarDiasHabiles($fechaInicio, $fechaFin, $ciclo)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $diasHabiles = 0;

        while ($inicio <= $fin) {
            if ($ciclo->esDiaHabil($inicio)) {
                $diasHabiles++;
            }
            $inicio->addDay();
        }

        return $diasHabiles;
    }

    private function getSiguienteDiaHabil($fecha, $ciclo)
    {
        $dia = Carbon::parse($fecha)->addDay();
        while (!$ciclo->esDiaHabil($dia)) {
            $dia->addDay();
        }
        return $dia;
    }

    private function obtenerEstadisticasGenerales($ciclo)
    {
        // Obtener inscripciones activas con el numero_documento del usuario
        $inscripciones = DB::table('inscripciones as i')
            ->join('users as u', 'i.estudiante_id', '=', 'u.id')
            ->where('i.ciclo_id', $ciclo->id)
            ->where('i.estado_inscripcion', 'activo')
            ->select('i.estudiante_id', 'u.numero_documento')
            ->get();
        
        $totalEstudiantes = $inscripciones->count();
        $estudiantesRegulares = 0;
        $estudiantesAmonestados = 0;
        $estudiantesInhabilitados = 0;
        $estudiantesSinRegistros = 0;

        // Obtener todos los documentos que tienen al menos un registro en el rango del ciclo
        $documentosConRegistro = DB::table('registros_asistencia')
            ->whereIn('nro_documento', $inscripciones->pluck('numero_documento')->filter())
            ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
            ->where('fecha_registro', '<=', $ciclo->fecha_fin)
            ->distinct('nro_documento')
            ->pluck('nro_documento')
            ->toArray();
        
        foreach ($inscripciones as $inscripcion) {
            $doc = $inscripcion->numero_documento;
            
            // Verificar si no tiene documento o no tiene registros
            if (empty($doc) || !in_array($doc, $documentosConRegistro)) {
                $estudiantesSinRegistros++;
                continue;
            }

            // Usar AsistenciaHelper que ya tiene la lógica del Excel unificada
            $info = \App\Helpers\AsistenciaHelper::obtenerEstadoHabilitacion($doc, $ciclo);
            
            if ($info['estado'] === 'inhabilitado') {
                $estudiantesInhabilitados++;
            } elseif ($info['estado'] === 'amonestado') {
                $estudiantesAmonestados++;
            } else {
                $estudiantesRegulares++;
            }
        }
        
        return [
            'total_estudiantes' => $totalEstudiantes,
            'regulares' => $estudiantesRegulares,
            'amonestados' => $estudiantesAmonestados,
            'inhabilitados' => $estudiantesInhabilitados,
            'sin_asistencia' => $estudiantesSinRegistros,
            'porcentaje_regulares' => $totalEstudiantes > 0 ? 
                round(($estudiantesRegulares / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_amonestados' => $totalEstudiantes > 0 ? 
                round(($estudiantesAmonestados / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_inhabilitados' => $totalEstudiantes > 0 ? 
                round(($estudiantesInhabilitados / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_sin_asistencia' => $totalEstudiantes > 0 ? 
                round(($estudiantesSinRegistros / $totalEstudiantes) * 100, 2) : 0
        ];
    }
}