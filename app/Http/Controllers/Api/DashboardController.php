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
use App\Models\Role;
use App\Models\Permission;
use App\Models\Turno;
use App\Models\Curso;
use App\Models\Carrera;
use App\Models\Aula;
use App\Models\Postulacion;
use App\Models\Carnet;
use App\Models\ResultadoExamen;

class DashboardController extends Controller
{
    /**
     * Obtener datos generales del dashboard CON CONTEXTO DEL CICLO ACTIVO
     */
    public function getDatosGenerales()
    {
        try {
            $user = Auth::user();
            $cicloActivo = Ciclo::where('es_activo', true)->first();

            $data = [];

            // INFORMACIÓN DEL CICLO ACTIVO
            if ($cicloActivo) {
                $hoy = Carbon::now();
                $totalDias = $cicloActivo->fecha_inicio->diffInDays($cicloActivo->fecha_fin);
                $diasTranscurridos = max(0, $cicloActivo->fecha_inicio->diffInDays($hoy));
                $diasRestantes = (int) max(0, $hoy->diffInDays($cicloActivo->fecha_fin));
                
                // Próximo examen
                $proximoExamen = $cicloActivo->getProximoExamen();
                
                $data['cicloActivo'] = [
                    'id' => $cicloActivo->id,
                    'nombre' => $cicloActivo->nombre,
                    'fecha_inicio' => $cicloActivo->fecha_inicio->format('d/m/Y'),
                    'fecha_fin' => $cicloActivo->fecha_fin->format('d/m/Y'),
                    'total_dias' => $totalDias,
                    'dias_transcurridos' => $diasTranscurridos,
                    'dias_restantes' => (int) $diasRestantes,
                    'progreso_porcentaje' => $totalDias > 0 ? round(($diasTranscurridos / $totalDias) * 100, 1) : 0,
                    'porcentaje_amonestacion' => $cicloActivo->porcentaje_amonestacion,
                    'porcentaje_inhabilitacion' => $cicloActivo->porcentaje_inhabilitacion,
                    'proximo_examen' => $proximoExamen ? [
                        'nombre' => $proximoExamen['nombre'],
                        'fecha' => $proximoExamen['fecha']->format('d/m/Y'),
                        'dias_faltantes' => (int) max(0, $hoy->diffInDays($proximoExamen['fecha']))
                    ] : null
                ];

                // ESTADÍSTICAS DEL CICLO ACTIVO
                $inscritosActivos = Inscripcion::where('ciclo_id', $cicloActivo->id)
                    ->where('estado_inscripcion', 'activo')
                    ->count();

                $data['totalInscritosActivos'] = $inscritosActivos;

                // Estudiantes únicos con al menos 1 registro de asistencia en el ciclo
                $estudiantesConAsistencia = DB::table('inscripciones as i')
                    ->join('users as u', 'i.estudiante_id', '=', 'u.id')
                    ->join('registros_asistencia as ra', 'u.numero_documento', '=', 'ra.nro_documento')
                    ->where('i.ciclo_id', $cicloActivo->id)
                    ->where('i.estado_inscripcion', 'activo')
                    ->whereBetween('ra.fecha_registro', [$cicloActivo->fecha_inicio, $cicloActivo->fecha_fin])
                    ->distinct('u.id')
                    ->count('u.id');

                $data['estudiantesConAsistencia'] = $estudiantesConAsistencia;

                // Docentes activos (que tienen horarios en el ciclo)
                $docentesActivos = HorarioDocente::where('ciclo_id', $cicloActivo->id)
                    ->distinct('docente_id')
                    ->count('docente_id');

                $data['totalDocentesActivos'] = $docentesActivos;

                // Aulas asignadas en el ciclo
                $aulasAsignadas = Inscripcion::where('ciclo_id', $cicloActivo->id)
                    ->where('estado_inscripcion', 'activo')
                    ->whereNotNull('aula_id')
                    ->distinct('aula_id')
                    ->count('aula_id');

                $data['totalAulasAsignadas'] = $aulasAsignadas;

            } else {
                // No hay ciclo activo
                $data['cicloActivo'] = null;
                $data['totalInscritosActivos'] = 0;
                $data['estudiantesConAsistencia'] = 0;
                $data['totalDocentesActivos'] = 0;
                $data['totalAulasAsignadas'] = 0;
            }

            // ASISTENCIA DE HOY (datos en tiempo real)
            $today = Carbon::today();
            $registrosHoy = RegistroAsistencia::whereDate('fecha_registro', $today)->count();
            
            // Estudiantes únicos que registraron asistencia hoy
            $estudiantesHoy = RegistroAsistencia::whereDate('fecha_registro', $today)
                ->where('nro_documento', '!=', '')
                ->distinct('nro_documento')
                ->count('nro_documento');

            $data['asistenciaHoy'] = [
                'total_registros' => $registrosHoy,
                'estudiantes_unicos' => $estudiantesHoy,
                'porcentaje_asistencia' => $cicloActivo && $data['totalInscritosActivos'] > 0 
                    ? round(($estudiantesHoy / $data['totalInscritosActivos']) * 100, 1) 
                    : 0
            ];

            // Datos del usuario
            $data['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
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
            $anuncios = Anuncio::select('id', 'titulo', 'contenido', 'fecha_publicacion')
                ->where('es_activo', true)
                ->where(function ($query) {
                    $query->whereNull('fecha_expiracion')
                          ->orWhere('fecha_expiracion', '>', now());
                })
                ->where('fecha_publicacion', '<=', now())
                ->orderBy('fecha_publicacion', 'desc')
                ->take(3) // Reducido a 3 para cargar más rápido
                ->get();

            return response()->json($anuncios);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener anuncios'], 500);
        }
    }

    /**
     * Obtener últimos registros de asistencia
     */
    public function getUltimosRegistros()
    {
        try {
            $registros = RegistroAsistencia::with('usuario')
                ->orderBy('fecha_registro', 'desc')
                ->take(10)
                ->get()
                ->map(function ($registro) {
                    return [
                        'id' => $registro->id,
                        'nro_documento' => $registro->nro_documento,
                        'fecha_registro' => $registro->fecha_registro,
                        'estado' => $registro->estado,
                        'usuario' => $registro->usuario ? [
                            'name' => $registro->usuario->name,
                            'email' => $registro->usuario->email
                        ] : null
                    ];
                });

            return response()->json($registros);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener registros'], 500);
        }
    }

    /**
     * Obtener datos COMPLETOS del dashboard administrativo
     * Accesible para: admin, ADMINISTRATIVOS, MONITOREO, COORDINACIÓN, ASISTENTES
     */
    public function getDatosAdmin()
    {
        try {
            $user = Auth::user();

            // Roles con acceso al dashboard administrativo
            $rolesAdministrativos = [
                'admin',
                'ADMINISTRATIVOS',
                'CEPRE UNAMAD MONITOREO',
                'COORDINACIÓN ACADEMICA',
                'ASISTENTE ADMINISTRATIVO II'
            ];

            $tieneAcceso = false;
            foreach ($rolesAdministrativos as $rol) {
                if ($user->hasRole($rol)) {
                    $tieneAcceso = true;
                    break;
                }
            }

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

            $data = [];

            // INFORMACIÓN DEL CICLO (sin caché, siempre actualizado)
            $hoy = Carbon::now();
            $totalDias = $cicloActivo->fecha_inicio->diffInDays($cicloActivo->fecha_fin);
            $diasTranscurridos = max(0, $cicloActivo->fecha_inicio->diffInDays($hoy));
            $diasRestantes = (int) max(0, $hoy->diffInDays($cicloActivo->fecha_fin));
            $proximoExamen = $cicloActivo->getProximoExamen();

            $data['cicloActivo'] = [
                'id' => $cicloActivo->id,
                'nombre' => $cicloActivo->nombre,
                'fecha_inicio' => $cicloActivo->fecha_inicio->format('d/m/Y'),
                'fecha_fin' => $cicloActivo->fecha_fin->format('d/m/Y'),
                'dias_transcurridos' => $diasTranscurridos,
                'dias_restantes' => (int) $diasRestantes,
                'progreso_porcentaje' => $totalDias > 0 ? round(($diasTranscurridos / $totalDias) * 100, 1) : 0,
                'proximo_examen' => $proximoExamen ? [
                    'nombre' => $proximoExamen['nombre'],
                    'fecha' => $proximoExamen['fecha']->format('d/m/Y'),
                    'dias_faltantes' => (int) max(0, $hoy->diffInDays($proximoExamen['fecha'], false))
                ] : null
            ];

            // ESTADÍSTICAS DE INSCRIPCIONES
            $totalInscripciones = Inscripcion::where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->count();

            $data['totalInscripciones'] = $totalInscripciones;

            // ESTADÍSTICAS DE POSTULACIONES
            $postulaciones = Postulacion::where('ciclo_id', $cicloActivo->id)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = "pendiente" THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = "aprobado" THEN 1 ELSE 0 END) as aprobadas,
                    SUM(CASE WHEN estado = "rechazado" THEN 1 ELSE 0 END) as rechazadas,
                    SUM(CASE WHEN estado = "observado" THEN 1 ELSE 0 END) as observadas,
                    SUM(CASE WHEN constancia_generada = 1 THEN 1 ELSE 0 END) as constancias_generadas,
                    SUM(CASE WHEN constancia_firmada = 1 THEN 1 ELSE 0 END) as constancias_firmadas
                ')
                ->first();

            $data['postulaciones'] = [
                'total' => $postulaciones->total ?? 0,
                'pendientes' => $postulaciones->pendientes ?? 0,
                'aprobadas' => $postulaciones->aprobadas ?? 0,
                'rechazadas' => $postulaciones->rechazadas ?? 0,
                'observadas' => $postulaciones->observadas ?? 0,
                'constancias_generadas' => $postulaciones->constancias_generadas ?? 0,
                'constancias_firmadas' => $postulaciones->constancias_firmadas ?? 0
            ];

            // ESTADÍSTICAS DE CARNETS
            $carnets = Carnet::where('ciclo_id', $cicloActivo->id)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN impreso = 0 THEN 1 ELSE 0 END) as pendientes_impresion,
                    SUM(CASE WHEN impreso = 1 AND entregado = 0 THEN 1 ELSE 0 END) as pendientes_entrega,
                    SUM(CASE WHEN entregado = 1 THEN 1 ELSE 0 END) as entregados
                ')
                ->first();

            $data['carnets'] = [
                'total' => $carnets->total ?? 0,
                'pendientes_impresion' => $carnets->pendientes_impresion ?? 0,
                'pendientes_entrega' => $carnets->pendientes_entrega ?? 0,
                'entregados' => $carnets->entregados ?? 0
            ];

            // ESTADÍSTICAS DE ASISTENCIA
            $data['estadisticasAsistencia'] = Cache::remember('dashboard.admin.asistencia.' . $cicloActivo->id, 900, function () use ($cicloActivo) {
                 return $this->obtenerEstadisticasGenerales($cicloActivo);
            });

            // ASISTENCIA DE HOY
            $today = Carbon::today();
            $registrosHoy = RegistroAsistencia::whereDate('fecha_registro', $today)->count();
            $estudiantesHoy = RegistroAsistencia::whereDate('fecha_registro', $today)
                ->distinct('nro_documento')
                ->count('nro_documento');

            $data['asistenciaHoy'] = [
                'total_registros' => $registrosHoy,
                'estudiantes_unicos' => $estudiantesHoy,
                'porcentaje' => $totalInscripciones > 0 ? round(($estudiantesHoy / $totalInscripciones) * 100, 1) : 0
            ];

            // DOCENTES Y HORARIOS
            $docentesActivos = HorarioDocente::where('ciclo_id', $cicloActivo->id)
                ->distinct('docente_id')
                ->count('docente_id');

            $sesionesHoy = HorarioDocente::where('ciclo_id', $cicloActivo->id)
                ->where('dia_semana', strtolower($hoy->locale('es')->dayName))
                ->count();

            $data['totalDocentesActivos'] = $docentesActivos;
            $data['sesionesHoy'] = $sesionesHoy;

            // RESULTADOS DE EXÁMENES
            $resultados = ResultadoExamen::where('ciclo_id', $cicloActivo->id)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN visible = 1 THEN 1 ELSE 0 END) as publicados
                ')
                ->first();

            $data['resultadosExamenes'] = [
                'total' => $resultados->total ?? 0,
                'publicados' => $resultados->publicados ?? 0
            ];

            // CARRERAS Y AULAS
            $data['totalCarreras'] = Carrera::where('estado', true)->count();
            $data['totalAulas'] = Inscripcion::where('ciclo_id', $cicloActivo->id)
                ->where('estado_inscripcion', 'activo')
                ->whereNotNull('aula_id')
                ->distinct('aula_id')
                ->count('aula_id');

            $data['totalCursos'] = Curso::where('estado', true)->count();

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

            $estudiantesEnRiesgo = $data['estadisticasAsistencia']['amonestados'] + $data['estadisticasAsistencia']['inhabilitados'];
            if ($estudiantesEnRiesgo > 0) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'mensaje' => "{$estudiantesEnRiesgo} estudiantes en riesgo (amonestados/inhabilitados)",
                    'icono' => 'mdi-account-alert',
                    'url' => route('asistencia.index')
                ];
            }

            if ($proximoExamen) {
                $diasFaltantes = (int) max(0, $hoy->diffInDays($proximoExamen['fecha'], false));
                
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
            return response()->json(['error' => 'Error al obtener datos administrativos'], 500);
        }
    }

    /**
     * Obtener SOLO estadísticas de asistencia (endpoint separado para carga progresiva)
     * Este endpoint se llama después de cargar el dashboard básico
     */
    public function getEstadisticasAsistencia()
    {
        try {
            $user = Auth::user();

            // Roles con acceso al dashboard administrativo
            $rolesAdministrativos = [
                'admin',
                'ADMINISTRATIVOS',
                'CEPRE UNAMAD MONITOREO',
                'COORDINACIÓN ACADEMICA',
                'ASISTENTE ADMINISTRATIVO II'
            ];

            $tieneAcceso = false;
            foreach ($rolesAdministrativos as $rol) {
                if ($user->hasRole($rol)) {
                    $tieneAcceso = true;
                    break;
                }
            }

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

            // Obtener estadísticas SIN caché para ver skeleton siempre
            $estadisticas = $this->obtenerEstadisticasGenerales($cicloActivo);

            // Generar alerta de estudiantes en riesgo
            $alerta = null;
            $estudiantesEnRiesgo = $estadisticas['amonestados'] + $estadisticas['inhabilitados'];
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
                ->whereHas('ciclo', function ($query) {
                    $query->where('es_activo', true);
                })
                ->with(['ciclo', 'carrera', 'aula', 'turno'])
                ->first();

            $data = [
                'inscripcionActiva' => $inscripcionActiva,
                'infoAsistencia' => []
            ];

            if ($inscripcionActiva) {
                $ciclo = $inscripcionActiva->ciclo;
                
                $primerRegistro = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                    ->where('fecha_registro', '>=', $ciclo->fecha_inicio)
                    ->where('fecha_registro', '<=', $ciclo->fecha_fin)
                    ->orderBy('fecha_registro')
                    ->first();

                if ($primerRegistro) {
                    // Calcular asistencia para cada examen
                    $data['infoAsistencia']['primer_examen'] = $this->calcularAsistenciaExamen(
                        $user->numero_documento,
                        $primerRegistro->fecha_registro,
                        $ciclo->fecha_primer_examen,
                        $ciclo
                    );

                    if ($ciclo->fecha_segundo_examen) {
                        $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen);
                        $data['infoAsistencia']['segundo_examen'] = $this->calcularAsistenciaExamen(
                            $user->numero_documento,
                            $inicioSegundo,
                            $ciclo->fecha_segundo_examen,
                            $ciclo
                        );
                    }

                    if ($ciclo->fecha_tercer_examen && $ciclo->fecha_segundo_examen) {
                        $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen);
                        $data['infoAsistencia']['tercer_examen'] = $this->calcularAsistenciaExamen(
                            $user->numero_documento,
                            $inicioTercero,
                            $ciclo->fecha_tercer_examen,
                            $ciclo
                        );
                    }

                    // Asistencia total del ciclo
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
            
            $diaSemanaSeleccionada = $fechaSeleccionada->locale('es')->dayName;
            
            // Obtener ciclo activo
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            // Obtener horarios del día
            $horariosDelDia = HorarioDocente::where('docente_id', $user->id)
                ->where('dia_semana', $diaSemanaSeleccionada)
                ->with(['aula', 'curso', 'ciclo'])
                ->orderBy('hora_inicio')
                ->get();
            
            // Obtener registros del biométrico
            $registrosDelDia = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaSeleccionada->format('Y-m-d'))
                ->orderBy('fecha_registro')
                ->get();

            // Procesar horarios con detalles
            $horasDelDia = 0;
            $sesionesPendientes = 0;
            $tarifaPorHora = $this->obtenerTarifaDocente($user->id, $cicloActivo);
            
            $horariosConDetalles = $horariosDelDia->map(function ($horario) use ($registrosDelDia, &$horasDelDia, $user, &$sesionesPendientes, $fechaSeleccionada) {
                // Procesar cada horario (simplificado)
                $horaInicio = Carbon::parse($horario->hora_inicio);
                $horaFin = Carbon::parse($horario->hora_fin);
                
                $horarioInicioHoy = $fechaSeleccionada->copy()->setTime($horaInicio->hour, $horaInicio->minute);
                $horarioFinHoy = $fechaSeleccionada->copy()->setTime($horaFin->hour, $horaFin->minute);
                
                // Buscar entrada y salida
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
                
                // Calcular horas trabajadas
                if ($entrada && $salida) {
                    $hEntrada = Carbon::parse($entrada->fecha_registro);
                    $hSalida = Carbon::parse($salida->fecha_registro);
                    if ($hSalida->greaterThan($hEntrada)) {
                        $horasDelDia += $hSalida->diffInMinutes($hEntrada) / 60;
                    }
                }
                
                // Buscar asistencia docente
                $asistencia = AsistenciaDocente::where('docente_id', $user->id)
                    ->where('horario_id', $horario->id)
                    ->whereDate('fecha_hora', $fechaSeleccionada->format('Y-m-d'))
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

            // Calcular métricas
            $pagoEstimadoHoy = $this->calcularPagoEstimado($user->id, $fechaSeleccionada, $horariosDelDia, $tarifaPorHora);
            
            // Resumen semanal
            $resumenSemanal = AsistenciaDocente::where('docente_id', $user->id)
                ->whereBetween('fecha_hora', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
                ->selectRaw('
                    COUNT(*) as total_sesiones,
                    SUM(horas_dictadas) as total_horas,
                    SUM(monto_total) as total_ingresos,
                    AVG(CASE WHEN estado = "completada" THEN 1 ELSE 0 END) * 100 as porcentaje_asistencia
                ')
                ->first();
            
            $data = [
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
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener datos del profesor: ' . $e->getMessage()], 500);
        }
    }

    // Métodos auxiliares privados
    
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
            ->whereDate('fecha_hora', $fechaSeleccionada->format('Y-m-d'))
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
        $diaActualSemana = $ahora->locale('es')->dayName;

        // Buscar clases de hoy que aún no han comenzado
        $proximaClaseHoy = HorarioDocente::where('docente_id', $docenteId)
            ->where('dia_semana', $diaActualSemana)
            ->where('hora_inicio', '>', $ahora->format('H:i:s'))
            ->with(['aula', 'curso'])
            ->orderBy('hora_inicio')
            ->first();

        if ($proximaClaseHoy) {
            return $proximaClaseHoy;
        }

        // Buscar en los próximos días
        $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        $diaActualIndex = array_search(strtolower($diaActualSemana), $diasSemana);
        
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

        $diasHabilesTotales = $this->contarDiasHabiles($fechaInicio, $fechaExamen);
        $diasHabilesTranscurridos = $this->contarDiasHabiles($fechaInicio, $fechaFinCalculo);

        $registrosAsistencia = RegistroAsistencia::where('nro_documento', $numeroDocumento)
            ->whereBetween('fecha_registro', [
                $fechaInicioCarbon->startOfDay(),
                $fechaFinCalculo->endOfDay()
            ])
            ->select(DB::raw('DATE(fecha_registro) as fecha'))
            ->distinct()
            ->get()
            ->pluck('fecha');

        $diasConAsistencia = 0;
        foreach ($registrosAsistencia as $fecha) {
            $carbonFecha = Carbon::parse($fecha);
            if ($carbonFecha->isWeekday()) {
                $diasConAsistencia++;
            }
        }

        $diasFaltaActuales = $diasHabilesTranscurridos - $diasConAsistencia;
        $porcentajeAsistenciaProyectado = $diasHabilesTotales > 0 ?
            round(($diasConAsistencia / $diasHabilesTotales) * 100, 2) : 0;
        $porcentajeInasistenciaProyectado = 100 - $porcentajeAsistenciaProyectado;

        $limiteAmonestacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_amonestacion / 100));
        $limiteInhabilitacion = ceil($diasHabilesTotales * ($ciclo->porcentaje_inhabilitacion / 100));

        $estado = 'regular';
        $mensaje = '';
        $puedeRendir = true;

        if ($diasFaltaActuales >= $limiteInhabilitacion) {
            $estado = 'inhabilitado';
            $mensaje = 'Has superado el límite de inasistencias.';
            $puedeRendir = false;
        } elseif ($diasFaltaActuales >= $limiteAmonestacion) {
            $estado = 'amonestado';
            $mensaje = 'Has sido amonestado por inasistencias.';
        } else {
            $mensaje = 'Tu asistencia es regular.';
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

    private function contarDiasHabiles($fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->startOfDay();
        $diasHabiles = 0;

        while ($inicio <= $fin) {
            if ($inicio->isWeekday()) {
                $diasHabiles++;
            }
            $inicio->addDay();
        }

        return $diasHabiles;
    }

    private function getSiguienteDiaHabil($fecha)
    {
        $dia = Carbon::parse($fecha)->addDay();
        while (!$dia->isWeekday()) {
            $dia->addDay();
        }
        return $dia;
    }

    private function obtenerEstadisticasGenerales($ciclo)
    {
        $hoy = Carbon::now()->startOfDay();
        
        // 1. Determinar el examen vigente y su fecha (Lógica mirror de Dashboard Estudiante/Export)
        // El ciclo se divide en periodos (Inicio->1er, 1er->2do, 2do->3er).
        // El dashboard general debe mostrar el estado del ALUMNO en el "Reto Actual".
        
        $fechaPrimerExamen = Carbon::parse($ciclo->fecha_primer_examen)->endOfDay();
        $fechaSegundoExamen = $ciclo->fecha_segundo_examen ? Carbon::parse($ciclo->fecha_segundo_examen)->endOfDay() : null;
        $fechaTercerExamen = $ciclo->fecha_tercer_examen ? Carbon::parse($ciclo->fecha_tercer_examen)->endOfDay() : null;

        // Determinamos qué "Periodo de Examen" estamos evaluando
        $fechaExamenObjetivo = $fechaPrimerExamen;
        $fechaInicioPeriodoTeorico = Carbon::parse($ciclo->fecha_inicio)->startOfDay();

        if ($hoy > $fechaPrimerExamen && $fechaSegundoExamen) {
             $fechaExamenObjetivo = $fechaSegundoExamen;
             // Si evaluamos 2do examen, el inicio "teórico" de conteo es post-1er examen
             $fechaInicioPeriodoTeorico = $this->getSiguienteDiaHabil($fechaPrimerExamen);
        } elseif ($fechaSegundoExamen && $hoy > $fechaSegundoExamen && $fechaTercerExamen) {
             $fechaExamenObjetivo = $fechaTercerExamen;
             $fechaInicioPeriodoTeorico = $this->getSiguienteDiaHabil($fechaSegundoExamen);
        }

        // Fecha de corte para "Faltas Actuales" (Hoy o Fin de Periodo)
        $fechaFinEsfuerzo = $hoy < $fechaExamenObjetivo ? $hoy : $fechaExamenObjetivo;

        // 2. Obtener primera asistencia RELEVANTE para este periodo por alumno
        // Nota: Si es 2do examen, buscamos primera asistencia DESDE el inicio del 2do periodo.
        $primerasAsistencias = DB::table('registros_asistencia as ra')
            ->join('users as u', 'ra.nro_documento', '=', 'u.numero_documento')
            ->join('inscripciones as i', 'u.id', '=', 'i.estudiante_id')
            ->where('i.ciclo_id', $ciclo->id)
            ->whereBetween('ra.fecha_registro', [$fechaInicioPeriodoTeorico, $fechaExamenObjetivo])
            ->select('i.estudiante_id', DB::raw('MIN(ra.fecha_registro) as fecha_primer_asistencia'))
            ->groupBy('i.estudiante_id')
            ->pluck('fecha_primer_asistencia', 'i.estudiante_id');

        // 3. Obtener conteo de asistencias en este periodo específico
        $conteoAsistencias = DB::table('inscripciones as i')
            ->join('users as u', 'i.estudiante_id', '=', 'u.id')
            ->join('registros_asistencia as ra', 'u.numero_documento', '=', 'ra.nro_documento')
            ->where('i.ciclo_id', $ciclo->id)
            ->where('i.estado_inscripcion', 'activo')
            ->whereBetween('ra.fecha_registro', [$fechaInicioPeriodoTeorico, $fechaFinEsfuerzo->copy()->endOfDay()])
            ->whereRaw('DAYOFWEEK(ra.fecha_registro) BETWEEN 2 AND 6')
            ->select('i.estudiante_id', DB::raw('COUNT(DISTINCT DATE(ra.fecha_registro)) as dias_asistidos'))
            ->groupBy('i.estudiante_id')
            ->pluck('dias_asistidos', 'i.estudiante_id');
            
        // 4. Lista de inscritos
        $estudiantesIds = DB::table('inscripciones')
            ->where('ciclo_id', $ciclo->id)
            ->where('estado_inscripcion', 'activo')
            ->pluck('estudiante_id');
        
        $totalEstudiantes = $estudiantesIds->count();
        $estudiantesRegulares = 0;
        $estudiantesAmonestados = 0;
        $estudiantesInhabilitados = 0;
        
        foreach ($estudiantesIds as $estudianteId) {
            if (!isset($primerasAsistencias[$estudianteId])) {
                // Sin registros en este periodo => Regular (o Pendiente)
                $estudiantesRegulares++;
                continue;
            }

            $fechaInicioPersonal = Carbon::parse($primerasAsistencias[$estudianteId])->startOfDay();
            
            // A. Días Hábiles TRANSCURRIDOS (StartPersonal -> Hoy/Corte)
            $diasHabilesTranscurridos = $this->contarDiasHabiles($fechaInicioPersonal, $fechaFinEsfuerzo);

            // B. Asistencias Reales
            $diasAsistidos = $conteoAsistencias[$estudianteId] ?? 0;

            // C. Faltas Actuales
            $diasFalta = max(0, $diasHabilesTranscurridos - $diasAsistidos);

            // D. Límite: Basado en periodo StartPersonal -> ExamenObjetivo
            // "diasHabilesTotales" en calcularAsistenciaExamen
            $diasHabilesTotalesPeriodo = $this->contarDiasHabiles($fechaInicioPersonal, $fechaExamenObjetivo);
            
            $limiteAmonestacion = ceil($diasHabilesTotalesPeriodo * ($ciclo->porcentaje_amonestacion / 100));
            $limiteInhabilitacion = ceil($diasHabilesTotalesPeriodo * ($ciclo->porcentaje_inhabilitacion / 100));
            
            if ($diasFalta >= $limiteInhabilitacion) {
                $estudiantesInhabilitados++;
            } elseif ($diasFalta >= $limiteAmonestacion) {
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
            'porcentaje_regulares' => $totalEstudiantes > 0 ? round(($estudiantesRegulares / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_amonestados' => $totalEstudiantes > 0 ? round(($estudiantesAmonestados / $totalEstudiantes) * 100, 2) : 0,
            'porcentaje_inhabilitados' => $totalEstudiantes > 0 ? round(($estudiantesInhabilitados / $totalEstudiantes) * 100, 2) : 0
        ];
    }
}