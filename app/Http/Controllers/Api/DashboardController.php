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
use App\Helpers\AsistenciaHelper;
use App\Http\Controllers\Traits\ProcessesTeacherSessions;
use App\Http\Controllers\Traits\HandlesSaturdayRotation;

use App\Http\Controllers\Traits\TeacherDashboardHelpers;

class DashboardController extends Controller
{
    use ProcessesTeacherSessions, HandlesSaturdayRotation, TeacherDashboardHelpers;

    public function getDatosProfesor(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user->hasRole('profesor')) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $fechaSeleccionada = $request->input('fecha') ? Carbon::parse($request->input('fecha')) : Carbon::today();
            $ciclosActivos = Ciclo::where('es_activo', true)->orderBy('fecha_inicio', 'desc')->get();
            $cicloActivo = $ciclosActivos->first();

            if (!$cicloActivo) {
                 return response()->json(['error' => 'No hay ciclo académico activo'], 404);
            }

            // 1. Horarios y Sesiones
            $todosHorariosActivos = HorarioDocente::where('docente_id', $user->id)
                ->whereHas('ciclo', function ($query) {
                    $query->where('es_activo', true);
                })
                ->with(['aula', 'curso', 'ciclo'])
                ->get();

            $horariosDelDia = $todosHorariosActivos->filter(function ($horario) use ($fechaSeleccionada) {
                $ciclo = $horario->ciclo;
                if (!$ciclo) return false;
                $diaHorarioNecesario = $ciclo->getDiaHorarioParaFecha($fechaSeleccionada);
                return mb_strtolower($diaHorarioNecesario, 'UTF-8') === mb_strtolower($horario->dia_semana, 'UTF-8');
            })->sortBy('hora_inicio');

            $registrosDelDia = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaSeleccionada->format('Y-m-d'))
                ->orderBy('fecha_registro')
                ->get();

            $totalMinutosNetosHoy = 0;
            $totalPagoHoy = 0;
            $sesionesPendientes = 0;

            $horariosDelDiaConDetalles = $horariosDelDia->map(function ($horario) use ($registrosDelDia, &$totalMinutosNetosHoy, &$totalPagoHoy, $user, &$sesionesPendientes, $fechaSeleccionada) {
                $sessionDetails = $this->processTeacherSessionLogic($horario, $fechaSeleccionada, $registrosDelDia, $user);
                
                if (!$sessionDetails) return null;

                $horaInicio = Carbon::parse($horario->hora_inicio);
                $horaFin = Carbon::parse($horario->hora_fin);
                $momentoActual = $fechaSeleccionada->isToday() ? Carbon::now() : $fechaSeleccionada->copy()->endOfDay();
                $horarioInicioHoy = $fechaSeleccionada->copy()->setTime($horaInicio->hour, $horaInicio->minute);
                $horarioFinHoy = $fechaSeleccionada->copy()->setTime($horaFin->hour, $horaFin->minute);

                $claseTerminada = $momentoActual->greaterThan($horarioFinHoy);
                $asistencia = AsistenciaDocente::where('docente_id', $user->id)
                    ->where('horario_id', $horario->id)
                    ->whereDate('fecha_hora', $fechaSeleccionada->toDateString())
                    ->first();

                if ($claseTerminada && !$asistencia && $sessionDetails['tiene_registros']) {
                    $sesionesPendientes++;
                }

                $totalMinutosNetosHoy += $sessionDetails['horas_dictadas'] * 60;
                $totalPagoHoy += $sessionDetails['pago'];

                return array_merge($sessionDetails, [
                    'id' => $horario->id,
                    'clase_terminada' => $claseTerminada,
                    'asistencia_id' => $asistencia ? $asistencia->id : null,
                    'tiempo_info' => $this->calcularInfoTiempo($horarioInicioHoy, $horarioFinHoy, $momentoActual, $fechaSeleccionada),
                    'curso_nombre' => $horario->curso->nombre ?? 'N/A',
                    'aula_nombre' => $horario->aula->nombre ?? 'N/A',
                    'tarifa_sesion' => $this->obtenerTarifaDocente($user->id, $horario->ciclo, $fechaSeleccionada),
                    'ciclo_nombre' => $horario->ciclo->nombre ?? 'N/A',
                ]);
            })->filter()->values();

            // 2. Métricas
            $eficienciaData = $this->calcularEficienciaYPuntualidad($user->id, $cicloActivo) ?? ['eficiencia' => 0, 'puntualidad' => 0];
            $resumenSemanal = AsistenciaDocente::where('docente_id', $user->id)
                ->whereBetween('fecha_hora', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
                ->selectRaw('COUNT(*) as total_sesiones, SUM(horas_dictadas) as total_horas, SUM(monto_total) as total_ingresos')
                ->first();

            $proximaClase = $this->obtenerProximaClaseCorregida($user->id, $cicloActivo);

            // 3. Anuncios
            $anuncios = Anuncio::where('es_activo', true)
                ->where('fecha_publicacion', '<=', now())
                ->orderBy('fecha_publicacion', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre . ' ' . $user->apellido_paterno,
                    'rol' => 'profesor'
                ],
                // Datos directos para el dashboard móvil
                'sesionesHoy' => $horariosDelDia->count(),
                'horasHoy' => round($totalMinutosNetosHoy / 60, 1),
                'pagoHoy' => round($totalPagoHoy, 2),
                'sesionesPendientes' => $sesionesPendientes,
                'eficiencia' => $eficienciaData['eficiencia'],
                'puntualidad' => $eficienciaData['puntualidad'],
                
                // Resumen semanal esperado por el UI
                'resumenSemanal' => [
                    'sesiones' => ($resumenSemanal) ? ($resumenSemanal->total_sesiones ?? 0) : 0,
                    'horas' => round(($resumenSemanal) ? ($resumenSemanal->total_horas ?? 0) : 0, 1),
                    'ingresos' => round(($resumenSemanal) ? ($resumenSemanal->total_ingresos ?? 0) : 0, 2),
                    'asistencia' => $eficienciaData['eficiencia'], // Mapeado a "Cumplimiento"
                    'tendencia' => $this->calcularTendenciaSemanal($user->id)
                ],
                
                // Lista de sesiones esperada por el UI
                'horariosDelDia' => $horariosDelDiaConDetalles->map(function($s) {
                    return [
                        'horario' => [
                            'id' => $s['id'],
                            'curso' => ['nombre' => $s['curso_nombre']],
                            'aula' => ['nombre' => $s['aula_nombre']],
                            'hora_inicio' => Carbon::parse($s['horario']->hora_inicio)->format('H:i'),
                            'hora_fin' => Carbon::parse($s['horario']->hora_fin)->format('H:i'),
                        ],
                        'asistencia' => [
                            'tema_desarrollado' => $s['tema_desarrollado'],
                            'hora_entrada' => $s['hora_entrada'],
                            'hora_salida' => $s['hora_salida'],
                            'estado' => $s['estado_sesion'],
                        ],
                        'clase_terminada' => $s['clase_terminada'],
                        'tiempo_info' => $s['tiempo_info'],
                        'tiene_registros' => $s['tiene_registros'],
                        'tarifa_sesion' => $s['tarifa_sesion'],
                        'ciclo_nombre' => $s['ciclo_nombre'],
                    ];
                }),
                
                'proximaClase' => ($proximaClase && $proximaClase->curso) ? [
                    'curso' => $proximaClase->curso->nombre,
                    'aula' => $proximaClase->aula->nombre ?? 'N/A',
                    'hora' => Carbon::parse($proximaClase->hora_inicio)->format('H:i'),
                    'dia' => $proximaClase->dia_semana
                ] : null,
                
                'anuncios' => $anuncios,
                'fecha_actual' => $fechaSeleccionada->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function registrarTemaDesarrollado(Request $request)
    {
        try {
            $request->validate([
                'horario_id' => 'required|exists:horarios_docentes,id',
                'fecha_seleccionada' => 'required|date',
                'tema_desarrollado' => 'required|string|min:10|max:1000'
            ]);

            $user = Auth::user();
            $fechaSeleccionada = Carbon::parse($request->fecha_seleccionada)->startOfDay();
            
            // Corregido: Usar el día rotativo si es sábado
            $cicloActivo = Ciclo::where('es_activo', true)->find(HorarioDocente::find($request->horario_id)->ciclo_id ?? 0);
            if ($cicloActivo) {
                $diaSemanaSeleccionada = $cicloActivo->getDiaHorarioParaFecha($fechaSeleccionada);
            } else {
                $diaSemanaSeleccionada = $fechaSeleccionada->locale('es')->dayName;
            }
            
            $horario = HorarioDocente::where('id', $request->horario_id)
                ->where('docente_id', $user->id)
                ->where(function($q) use ($diaSemanaSeleccionada) {
                    $q->where('dia_semana', $diaSemanaSeleccionada)
                      ->orWhere(DB::raw('LOWER(dia_semana)'), mb_strtolower($diaSemanaSeleccionada, 'UTF-8'));
                })
                ->first();
                
            if (!$horario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horario no válido o no corresponde al día de la sesión (Día detectado: ' . $diaSemanaSeleccionada . ').'
                ], 400);
            }

            $horarioInicioClase = $fechaSeleccionada->copy()->setTime(Carbon::parse($horario->hora_inicio)->hour, Carbon::parse($horario->hora_inicio)->minute);
            $horarioFinClase = $fechaSeleccionada->copy()->setTime(Carbon::parse($horario->hora_fin)->hour, Carbon::parse($horario->hora_fin)->minute);
            
            $registrosDiaSeleccionado = RegistroAsistencia::where('nro_documento', $user->numero_documento)
                ->whereDate('fecha_registro', $fechaSeleccionada->format('Y-m-d'))
                ->orderBy('fecha_registro')
                ->get();

            $entrada = $registrosDiaSeleccionado->filter(function($r) use ($horarioInicioClase) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioInicioClase->copy()->subMinutes(15),
                    $horarioInicioClase->copy()->addMinutes(120) // Tolerancia unificada con web
                );
            })->first();

            $salida = $registrosDiaSeleccionado->filter(function($r) use ($horarioFinClase) {
                $horaRegistro = Carbon::parse($r->fecha_registro);
                return $horaRegistro->between(
                    $horarioFinClase->copy()->subMinutes(15),
                    $horarioFinClase->copy()->addMinutes(60)
                );
            })->sortByDesc('fecha_registro')->first();

            if (!$entrada || !$salida) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron registros biométricos de entrada/salida válidos.'
                ], 400);
            }

            $horaEntrada = Carbon::parse($entrada->fecha_registro);
            $horaSalida = Carbon::parse($salida->fecha_registro);
            
            $inicioEfectivo = $horaEntrada->max($horarioInicioClase);
            $finEfectivo = $horaSalida->min($horarioFinClase);
            
            $horasTrabajadas = 0;
            $montoTotal = 0;

            if ($finEfectivo->greaterThan($inicioEfectivo)) {
                $minutosBrutos = $inicioEfectivo->diffInMinutes($finEfectivo);

                $cicloDelHorario = $horario->ciclo;
                $minutosRecesoManana = 0;
                $minutosRecesoTarde = 0;
                
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
                $horasTrabajadas = max(0, $minutosNetos) / 60;
                
                $tarifaHora = $this->obtenerTarifaDocente($user->id, $cicloDelHorario, $fechaSeleccionada);
                $montoTotal = $horasTrabajadas * $tarifaHora;
            } else {
                 return response()->json([
                    'success' => false,
                    'message' => 'No hay tiempo de clase efectivo.'
                ], 400);
            }

            $asistencia = AsistenciaDocente::updateOrCreate(
                [
                    'docente_id' => $user->id,
                    'horario_id' => $request->horario_id,
                    'fecha_hora' => $fechaSeleccionada->toDateString()
                ],
                [
                    'tema_desarrollado' => $request->tema_desarrollado,
                    'hora_entrada' => $horaEntrada->toDateTimeString(),
                    'hora_salida' => $horaSalida->toDateTimeString(),
                    'horas_dictadas' => round($horasTrabajadas, 2),
                    'monto_total' => round($montoTotal, 2),
                    'estado' => 'completada'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Tema desarrollado registrado exitosamente',
                'data' => [
                    'tema' => $asistencia->tema_desarrollado,
                    'horas' => $asistencia->horas_dictadas,
                    'monto' => $asistencia->monto_total
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDatosGenerales()
    {
        try {
            $user = Auth::user();
            $ciclosActivos = Ciclo::where('es_activo', true)->get();
            $totalInscritos = 0;
            
            foreach ($ciclosActivos as $ciclo) {
                $totalInscritos += Inscripcion::where('ciclo_id', $ciclo->id)->where('estado_inscripcion', 'activo')->count();
            }

            $today = Carbon::today();
            $estudiantesHoy = RegistroAsistencia::whereDate('fecha_registro', $today)->where('nro_documento', '!=', '')->distinct('nro_documento')->count('nro_documento');

            return response()->json([
                'user' => ['name' => $user->name],
                'totalInscritosActivos' => $totalInscritos,
                'asistenciaHoy' => [
                    'estudiantes_unicos' => $estudiantesHoy,
                    'porcentaje_asistencia' => $totalInscritos > 0 ? round(($estudiantesHoy / $totalInscritos) * 100, 1) : 0
                ]
            ]);
        } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 500); }
    }

    public function getDatosAdmin(Request $request)
    {
        try {
            $user = Auth::user();
            $ciclo_id = $request->input('ciclo_id', 'global');
            
            $queryCiclos = Ciclo::query();
            if ($ciclo_id === 'global') {
                $queryCiclos->where('es_activo', true);
            } else {
                $queryCiclos->where('id', $ciclo_id);
            }
            $ciclosToProcess = $queryCiclos->get();

            if ($ciclosToProcess->isEmpty()) {
                return response()->json(['error' => 'No hay ciclos activos', 'cicloActivo' => null]);
            }

            $hoy = Carbon::now();
            $data = [
                'totalInscripciones' => 0,
                'postulaciones' => ['total' => 0, 'pendientes' => 0, 'aprobadas' => 0, 'rechazadas' => 0],
                'carnets' => ['total' => 0, 'pendientes_impresion' => 0, 'pendientes_entrega' => 0, 'entregados' => 0],
                'totalDocentesActivos' => 0,
                'totalAulas' => 0,
                'estadisticasAsistencia' => ['regulares' => 0, 'amonestados' => 0, 'inhabilitados' => 0, 'total_estudiantes' => 0],
                'alertas' => []
            ];

            // Info estratégica consolidada
            $proximoHitoGlobal = null;
            $totalPct = 0;
            $ciclosCount = $ciclosToProcess->count();

            foreach ($ciclosToProcess as $ciclo) {
                $inicio = Carbon::parse($ciclo->fecha_inicio);
                $fin = Carbon::parse($ciclo->fecha_fin);
                $totalDias = max(1, $inicio->diffInDays($fin));
                $transcurridos = max(0, $inicio->diffInDays($hoy));
                $totalPct += min(100, round(($transcurridos / $totalDias) * 100, 1));

                // Escanear hitos de este ciclo para encontrar el más cercano globalmente
                $hitosCiclo = [
                    ['n' => 'Inicio de Clases', 'f' => $ciclo->fecha_inicio],
                    ['n' => '1er Examen', 'f' => $ciclo->fecha_primer_examen],
                    ['n' => '2do Examen', 'f' => $ciclo->fecha_segundo_examen],
                    ['n' => '3er Examen', 'f' => $ciclo->fecha_tercer_examen],
                    ['n' => 'Cierre de Ciclo', 'f' => $ciclo->fecha_fin]
                ];

                foreach ($hitosCiclo as $h) {
                    if ($h['f'] && $h['f'] !== '-' && $h['f'] !== '00/00/0000') {
                        $fHito = Carbon::parse($h['f']);
                        if ($fHito->endOfDay()->greaterThan($hoy)) {
                            if (!$proximoHitoGlobal || $fHito->lessThan(Carbon::parse($proximoHitoGlobal['fecha']))) {
                                $proximoHitoGlobal = [
                                    'nombre' => ($ciclo_id === 'global' ? "[{$ciclo->nombre}] " : "") . $h['n'],
                                    'fecha' => $fHito->format('Y-m-d H:i:s'),
                                    'dias_faltantes' => $hoy->diffInDays($fHito, false)
                                ];
                            }
                        }
                    }
                }
            }

            $cRef = $ciclosToProcess->first();
            $data['cicloActivo'] = [
                'nombre' => $ciclo_id === 'global' ? "CONSOLIDADO MAESTRO" : $cRef->nombre,
                'fecha_inicio' => Carbon::parse($cRef->fecha_inicio)->format('d/m/Y'),
                'fecha_fin' => Carbon::parse($cRef->fecha_fin)->format('d/m/Y'),
                'progreso_porcentaje' => $ciclosCount > 0 ? round($totalPct / $ciclosCount, 1) : 0,
                'proximo_hito' => $proximoHitoGlobal,
                'total_ciclos' => $ciclosCount,
                'es_global' => $ciclo_id === 'global'
            ];

            if ($ciclo_id !== 'global') {
                $data['cicloActivo']['fecha_examen_1'] = $cRef->fecha_primer_examen ? Carbon::parse($cRef->fecha_primer_examen)->format('d/m/Y') : null;
                $data['cicloActivo']['fecha_examen_2'] = $cRef->fecha_segundo_examen ? Carbon::parse($cRef->fecha_segundo_examen)->format('d/m/Y') : null;
                $data['cicloActivo']['fecha_examen_3'] = $cRef->fecha_tercer_examen ? Carbon::parse($cRef->fecha_tercer_examen)->format('d/m/Y') : null;
            }

            foreach ($ciclosToProcess as $ciclo) {
                // Agregar datos con seguridad
                $data['totalInscripciones'] += Inscripcion::where('ciclo_id', $ciclo->id)->where('estado_inscripcion', 'activo')->count();
                
                $post = Postulacion::where('ciclo_id', $ciclo->id)
                    ->selectRaw('COUNT(*) as t, SUM(CASE WHEN estado="pendiente" THEN 1 ELSE 0 END) as p, SUM(CASE WHEN estado="aprobado" THEN 1 ELSE 0 END) as a')
                    ->first();
                $data['postulaciones']['total'] += $post->t ?? 0;
                $data['postulaciones']['pendientes'] += $post->p ?? 0;
                $data['postulaciones']['aprobadas'] += $post->a ?? 0;

                $data['totalDocentesActivos'] += HorarioDocente::where('ciclo_id', $ciclo->id)->distinct('docente_id')->count('docente_id');
                $data['totalAulas'] += Inscripcion::where('ciclo_id', $ciclo->id)->where('estado_inscripcion', 'activo')->whereNotNull('aula_id')->distinct('aula_id')->count('aula_id');
                
                $stats = AsistenciaHelper::obtenerEstadisticasCiclo($ciclo);
                $data['estadisticasAsistencia']['regulares'] += $stats['regulares'];
                $data['estadisticasAsistencia']['amonestados'] += $stats['amonestados'];
                $data['estadisticasAsistencia']['inhabilitados'] += $stats['inhabilitados'];
                $data['estadisticasAsistencia']['total_estudiantes'] += $stats['total_estudiantes'];
            }

            $estudiantesHoy = RegistroAsistencia::whereDate('fecha_registro', Carbon::today())->distinct('nro_documento')->count('nro_documento');
            $data['asistenciaHoy'] = [
                'estudiantes_unicos' => $estudiantesHoy,
                'porcentaje' => $data['totalInscripciones'] > 0 ? round(($estudiantesHoy / $data['totalInscripciones']) * 100, 1) : 0
            ];

            return response()->json($data);
        } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 500); }
    }

    public function getEstadisticasAsistencia(Request $request) { return $this->getDatosAdmin($request); }
    public function getAnuncios() { return response()->json(Anuncio::where('es_activo', true)->orderBy('fecha_publicacion', 'desc')->take(3)->get()); }

    /**
     * Exportar PDF de Carga Horaria (Visual o Detallado) para el docente autenticado.
     */
    public function exportWorkloadPdf(Request $request, $type = 'visual')
    {
        try {
            $user = $request->user();
            $cicloId = $request->input('ciclo_id');
            
            if (!$cicloId) {
                $cicloActivo = Ciclo::where('es_activo', true)->first();
                $cicloId = $cicloActivo ? $cicloActivo->id : null;
            }

            if (!$cicloId) {
                return response()->json(['success' => false, 'message' => 'No hay ciclo activo o seleccionado.'], 404);
            }

            $controller = new \App\Http\Controllers\CargaHorariaController();
            
            if ($type === 'detallado') {
                return $controller->pdfDetallado($user->id, $cicloId);
            }
            
            return $controller->pdfVisual($user->id, $cicloId);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al generar PDF de carga horaria: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exportar PDF de Asistencia Individual para el docente autenticado.
     */
    public function exportAttendancePdf(Request $request)
    {
        try {
            $user = $request->user();
            
            // Forzar que el reporte sea solo para el usuario autenticado
            $request->merge(['docente_id' => $user->id]);
            
            $controller = new \App\Http\Controllers\AsistenciaDocenteController();
            return $controller->exportarPdfIndividual($request);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al generar PDF de asistencia: ' . $e->getMessage()], 500);
        }
    }
}
