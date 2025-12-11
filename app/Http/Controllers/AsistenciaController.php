<?php

namespace App\Http\Controllers;

use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class AsistenciaController extends Controller
{

    // En app/Http/Controllers/AsistenciaController.php
    public function __construct()
    {
        // Procesar eventos pendientes cada vez que se carga la página
        Artisan::call('asistencia:procesar-eventos');
    }
    /**
     * Mostrar la lista de registros de asistencia de BIOMETRICO Y MANUAL.
     */
    public function index(Request $request)
    {
        // Obtener parámetros de filtrado
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));
        $documento = $request->get('documento');

        // Crear la consulta base
        $query = RegistroAsistencia::query();

        // Aplicar filtros
        if ($fecha) {
            $query->where(function ($q) use ($fecha) {
                $q->whereDate('fecha_registro', $fecha)
                    ->orWhere(function ($q2) use ($fecha) {
                        $q2->where('tipo_verificacion', 4)
                            ->whereDate('fecha_hora', $fecha);
                    });
            });
        }

        if ($documento) {
            $query->where('nro_documento', $documento);
        }

        // Obtener registros paginados
        $registros = $query->orderBy('fecha_registro', 'desc')->paginate(15);

        // Obtener el ciclo activo para calcular días hábiles
        $cicloActivo = \App\Models\Ciclo::where('es_activo', true)->first();

        // Calcular estadísticas de asistencias y faltas por estudiante
        foreach ($registros as $registro) {
            if ($cicloActivo) {
                // Determinar el examen activo (próximo examen o último si ya pasaron todos)
                $examenActivo = $this->determinarExamenActivo($cicloActivo);
                
                if ($examenActivo) {
                    // Contar días únicos de asistencia para el período del examen activo
                    $diasAsistidos = RegistroAsistencia::where('nro_documento', $registro->nro_documento)
                        ->whereBetween('fecha_registro', [
                            $examenActivo['fecha_inicio']->startOfDay(),
                            min(now(), $examenActivo['fecha_examen'])->endOfDay()
                        ])
                        ->select(\DB::raw('DATE(fecha_registro) as fecha'))
                        ->distinct()
                        ->get()
                        ->filter(function($item) {
                            $fecha = \Carbon\Carbon::parse($item->fecha);
                            return $fecha->isWeekday(); // Solo días hábiles (lunes a viernes)
                        })
                        ->count();

                    $registro->total_asistencias = $diasAsistidos;

                    // Calcular días hábiles del período del examen activo
                    $fechaFin = now() < $examenActivo['fecha_examen'] 
                        ? now() 
                        : $examenActivo['fecha_examen'];
                    
                    // Días hábiles totales del período del examen
                    $diasHabilesTotales = $this->contarDiasHabiles(
                        $examenActivo['fecha_inicio'],
                        $examenActivo['fecha_examen']
                    );
                    
                    // Días hábiles transcurridos hasta hoy
                    $diasHabilesTranscurridos = $this->contarDiasHabiles(
                        $examenActivo['fecha_inicio'],
                        $fechaFin
                    );

                    // Calcular faltas = días hábiles transcurridos - días asistidos
                    $registro->total_faltas = max(0, $diasHabilesTranscurridos - $diasAsistidos);
                    
                    // Calcular límites de amonestación e inhabilitación basados en días hábiles TOTALES
                    $porcentajeAmonestacion = $cicloActivo->porcentaje_amonestacion ?? 20;
                    $porcentajeInhabilitacion = $cicloActivo->porcentaje_inhabilitacion ?? 30;
                    
                    $limiteAmonestacion = ceil($diasHabilesTotales * ($porcentajeAmonestacion / 100));
                    $limiteInhabilitacion = ceil($diasHabilesTotales * ($porcentajeInhabilitacion / 100));
                    
                    // Log para debug (puedes comentar después)
                    \Log::info("Estudiante {$registro->nro_documento}: Asist={$diasAsistidos}, Faltas={$registro->total_faltas}, DíasHábiles={$diasHabilesTotales}, LimAmon={$limiteAmonestacion}, LimInhab={$limiteInhabilitacion}");
                    
                    // Determinar estado de habilitación
                    // IMPORTANTE: Comparamos FALTAS con los límites
                    if ($registro->total_faltas >= $limiteInhabilitacion) {
                        $registro->estado_habilitacion = 'inhabilitado';
                        $registro->puede_rendir = false;
                    } elseif ($registro->total_faltas >= $limiteAmonestacion) {
                        $registro->estado_habilitacion = 'amonestado';
                        $registro->puede_rendir = true; // Puede rendir pero con advertencia
                    } else {
                        $registro->estado_habilitacion = 'regular';
                        $registro->puede_rendir = true;
                    }
                    
                    // Guardar información del examen para mostrar en la vista
                    $registro->examen_actual = $examenActivo['nombre'];
                    $registro->limite_amonestacion = $limiteAmonestacion;
                    $registro->limite_inhabilitacion = $limiteInhabilitacion;
                    $registro->dias_habiles_totales = $diasHabilesTotales;
                } else {
                    $registro->total_asistencias = 0;
                    $registro->total_faltas = 0;
                    $registro->examen_actual = 'Sin examen activo';
                }
            } else {
                $registro->total_asistencias = 0;
                $registro->total_faltas = 0;
                $registro->examen_actual = 'Sin ciclo activo';
            }
        }

        // Obtener usuarios para el filtro (opcional)
        $usuarios = User::select('id', 'numero_documento', 'nombre', 'apellido_paterno')->get();

        return view('asistencia.index', compact('registros', 'usuarios', 'fecha', 'documento'));
    }

    /**
     * Determinar el examen activo (próximo examen o el último si ya pasaron todos)
     */
    private function determinarExamenActivo($ciclo)
    {
        $hoy = \Carbon\Carbon::now();
        
        // Primer Examen
        if ($ciclo->fecha_primer_examen && $hoy <= $ciclo->fecha_primer_examen) {
            return [
                'nombre' => 'Primer Examen',
                'fecha_inicio' => $ciclo->fecha_inicio,
                'fecha_examen' => $ciclo->fecha_primer_examen
            ];
        }
        
        // Segundo Examen
        if ($ciclo->fecha_segundo_examen && $hoy <= $ciclo->fecha_segundo_examen) {
            $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen);
            return [
                'nombre' => 'Segundo Examen',
                'fecha_inicio' => $inicioSegundo,
                'fecha_examen' => $ciclo->fecha_segundo_examen
            ];
        }
        
        // Tercer Examen
        if ($ciclo->fecha_tercer_examen && $hoy <= $ciclo->fecha_tercer_examen) {
            $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen);
            return [
                'nombre' => 'Tercer Examen',
                'fecha_inicio' => $inicioTercero,
                'fecha_examen' => $ciclo->fecha_tercer_examen
            ];
        }
        
        // Si ya pasaron todos los exámenes, usar el último
        if ($ciclo->fecha_tercer_examen) {
            $inicioTercero = $this->getSiguienteDiaHabil($ciclo->fecha_segundo_examen);
            return [
                'nombre' => 'Tercer Examen (Finalizado)',
                'fecha_inicio' => $inicioTercero,
                'fecha_examen' => $ciclo->fecha_tercer_examen
            ];
        } elseif ($ciclo->fecha_segundo_examen) {
            $inicioSegundo = $this->getSiguienteDiaHabil($ciclo->fecha_primer_examen);
            return [
                'nombre' => 'Segundo Examen (Finalizado)',
                'fecha_inicio' => $inicioSegundo,
                'fecha_examen' => $ciclo->fecha_segundo_examen
            ];
        } elseif ($ciclo->fecha_primer_examen) {
            return [
                'nombre' => 'Primer Examen (Finalizado)',
                'fecha_inicio' => $ciclo->fecha_inicio,
                'fecha_examen' => $ciclo->fecha_primer_examen
            ];
        }
        
        return null;
    }

    /**
     * Obtener el siguiente día hábil después de una fecha
     */
    private function getSiguienteDiaHabil($fecha)
    {
        $siguiente = \Carbon\Carbon::parse($fecha)->addDay();
        
        while (!$siguiente->isWeekday()) {
            $siguiente->addDay();
        }
        
        return $siguiente;
    }

    /**
     * Contar días hábiles entre dos fechas (lunes a viernes)
     */
    private function contarDiasHabiles($fechaInicio, $fechaFin)
    {
        $inicio = \Carbon\Carbon::parse($fechaInicio)->startOfDay();
        $fin = \Carbon\Carbon::parse($fechaFin)->startOfDay();
        $diasHabiles = 0;

        while ($inicio <= $fin) {
            // Contar solo días de lunes a viernes (1-5)
            if ($inicio->isWeekday()) {
                $diasHabiles++;
            }
            $inicio->addDay();
        }

        return $diasHabiles;
    }


    /**
     * Muestra la vista de monitoreo en tiempo real de registros de asistencia.
     */
    public function tiempoReal()
    {
        // Obtenemos los últimos 5 registros para mostrarlos inicialmente
        $ultimosRegistros = RegistroAsistencia::with('usuario')
            ->orderBy('fecha_registro', 'desc')  // Cambiado de fecha_hora a fecha_registro
            ->take(10)
            ->get();

        return view('asistencia.monitor_realtime', compact('ultimosRegistros'));
    }

    /**
     * Mostrar el formulario para registrar asistencia manualmente.
     */
    public function registrarForm()
    {
        // Obtener estudiantes para el select
        $estudiantes = User::whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'estudiante');
        })->get();

        return view('asistencia.registrar', compact('estudiantes'));
    }

    /**
     * Guardar un nuevo registro de asistencia.
     */
    public function registrar(Request $request)
    {
        $request->validate([
            'nro_documento' => 'required|string|max:20',
            'fecha_hora' => 'required|date',
            'tipo_verificacion' => 'required|integer',
        ]);

        // Crear el nuevo registro
        RegistroAsistencia::create([
            'nro_documento' => $request->nro_documento,
            'fecha_hora' => $request->fecha_hora,
            'tipo_verificacion' => $request->tipo_verificacion,
            'estado' => 1, // Activo por defecto
            'codigo_trabajo' => $request->codigo_trabajo,
            'terminal_id' => $request->terminal_id,
            'sn_dispositivo' => $request->sn_dispositivo ?? 'MANUAL',
            'fecha_registro' => $request->fecha_hora,
        ]);

        return redirect()->route('asistencia.index')->with('success', 'Registro de asistencia creado exitosamente.');
    }

    /**
     * Mostrar el formulario para editar registros de asistencia.
     */
    public function editarIndex(Request $request)
    {
        // Cargar datos para filtros de registro masivo y regularización
        $ciclos = \App\Models\Ciclo::orderBy('nombre', 'desc')->get();
        $cicloActivo = \App\Models\Ciclo::where('es_activo', true)->first();
        $aulas = \App\Models\Aula::where('estado', true)->orderBy('nombre')->get();
        $turnos = \App\Models\Turno::where('estado', true)->orderBy('orden')->get();
        $carreras = \App\Models\Carrera::where('estado', true)->orderBy('nombre')->get();
        
        // Obtener estudiantes activos con inscripciones
        $estudiantes = User::whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'estudiante');
        })
        ->whereHas('inscripciones', function ($query) {
            $query->where('estado_inscripcion', 'activo');
        })
        ->select('id', 'numero_documento', 'nombre', 'apellido_paterno', 'apellido_materno')
        ->orderBy('apellido_paterno')
        ->get();

        // Verificar si se han enviado parámetros de búsqueda
        if ($request->has('fecha_desde') || $request->has('fecha_hasta') || $request->has('documento')) {
            // Crear la consulta base
            $query = RegistroAsistencia::query();

            // Aplicar filtros
            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
            }

            if ($request->has('documento')) {
                $query->where('nro_documento', 'like', '%' . $request->documento . '%');
            }

            // Obtener registros paginados
            $registros = $query->orderBy('fecha_hora', 'desc')->paginate(15);

            return view('asistencia.editar_index', compact('registros', 'ciclos', 'cicloActivo', 'aulas', 'turnos', 'carreras', 'estudiantes'));
        }

        // Si no hay parámetros, mostrar solo el formulario de búsqueda
        return view('asistencia.editar_index', compact('ciclos', 'cicloActivo', 'aulas', 'turnos', 'carreras', 'estudiantes'));
    }

    /**
     * Mostrar el formulario para editar un registro específico.
     */
    public function editar(RegistroAsistencia $asistencia)
    {
        return view('asistencia.editar', compact('asistencia'));
    }

    /**
     * Actualizar un registro de asistencia.
     */
    public function update(Request $request, RegistroAsistencia $asistencia)
    {
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

        return redirect()->route('asistencia.index')->with('success', 'Registro de asistencia actualizado exitosamente.');
    }

    /**
     * Mostrar el formulario para exportar registros de asistencia.
     */
    public function exportarIndex()
    {
        // Obtener usuarios para el filtro
        $usuarios = User::select('id', 'numero_documento', 'nombre', 'apellido_paterno')->get();

        return view('asistencia.exportar', compact('usuarios'));
    }

    /**
     * Exportar registros de asistencia según los filtros.
     */
    public function exportar(Request $request)
    {
        // Lógica para exportar registros a Excel o CSV
        // Aquí puedes usar paquetes como maatwebsite/excel

        return redirect()->route('asistencia.exportar')->with('success', 'Registros exportados exitosamente.');
    }

    /**
     * Mostrar los reportes y estadísticas de asistencia.
     */
    public function reportesIndex()
    {
        // Obtener datos para estadísticas
        $totalHoy = RegistroAsistencia::whereDate('fecha_hora', Carbon::today())->count();
        $totalSemana = RegistroAsistencia::whereBetween('fecha_hora', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $totalMes = RegistroAsistencia::whereMonth('fecha_hora', Carbon::now()->month)->count();

        // Gráfico de asistencia por día de la semana actual
        $asistenciaSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $fecha = Carbon::now()->startOfWeek()->addDays($i);
            $asistenciaSemana[$fecha->format('Y-m-d')] = RegistroAsistencia::whereDate('fecha_hora', $fecha)->count();
        }

        return view('asistencia.reportes', compact('totalHoy', 'totalSemana', 'totalMes', 'asistenciaSemana'));
    }

    public function ultimosProcesados(Request $request)
    {
        // Obtener la marca de tiempo de la última consulta
        $ultimaConsulta = $request->input('ultima_consulta', 0);

        // Buscar los eventos que han sido procesados desde la última consulta
        $eventosRecientes = AsistenciaEvento::where('procesado', true)
            ->where('updated_at', '>', Carbon::createFromTimestamp($ultimaConsulta))
            ->orderBy('id', 'asc')
            ->take(5)
            ->get();

        // Si no hay eventos recién procesados, retornar una respuesta vacía
        if ($eventosRecientes->isEmpty()) {
            return response()->json([
                'success' => true,
                'tiene_nuevos' => false,
                'hora_actual' => now()->timestamp,
                'registros' => []
            ]);
        }

        // Obtener los registros de asistencia correspondientes
        $registros = [];
        foreach ($eventosRecientes as $evento) {
            $registro = RegistroAsistencia::with('usuario')
                ->find($evento->registros_asistencia_id);

            if ($registro) {
                $registros[] = [
                    'id' => $registro->id,
                    'nro_documento' => $registro->nro_documento,
                    'nombre_completo' => $registro->usuario ?
                        $registro->usuario->nombre . ' ' . $registro->usuario->apellido_paterno :
                        null,
                    'fecha_hora_formateada' => $registro->fecha_hora->format('d/m/Y H:i:s'),
                    'tipo_verificacion' => $registro->tipo_verificacion,
                    'tipo_verificacion_texto' => $registro->tipo_verificacion_texto,
                    'estado' => $registro->estado,
                    'foto_url' => $registro->usuario && $registro->usuario->foto_perfil ?
                        asset('storage/' . $registro->usuario->foto_perfil) : null,
                    'iniciales' => $registro->usuario ?
                        strtoupper(substr($registro->usuario->nombre, 0, 1)) : null,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'tiene_nuevos' => true,
            'hora_actual' => now()->timestamp,
            'registros' => $registros
        ]);
    }

    /**
     * Obtener estudiantes filtrados por ciclo, aula, turno y carrera (AJAX)
     */
    public function getEstudiantesPorFiltros(Request $request)
    {
        try {
            $query = User::whereHas('roles', function ($q) {
                $q->where('roles.nombre', 'estudiante');
            })
            ->whereHas('inscripciones', function ($q) use ($request) {
                $q->where('estado_inscripcion', 'activo');
                
                if ($request->filled('ciclo_id')) {
                    $q->where('ciclo_id', $request->ciclo_id);
                }
                
                if ($request->filled('aula_id')) {
                    $q->where('aula_id', $request->aula_id);
                }
                
                if ($request->filled('turno_id')) {
                    $q->where('turno_id', $request->turno_id);
                }
                
                if ($request->filled('carrera_id')) {
                    $q->where('carrera_id', $request->carrera_id);
                }
            })
            ->with(['inscripciones' => function ($q) use ($request) {
                $q->where('estado_inscripcion', 'activo');
                
                if ($request->filled('ciclo_id')) {
                    $q->where('ciclo_id', $request->ciclo_id);
                }
            }, 'inscripciones.aula', 'inscripciones.turno', 'inscripciones.carrera'])
            ->select('id', 'numero_documento', 'nombre', 'apellido_paterno', 'apellido_materno')
            ->orderBy('apellido_paterno')
            ->get();

            $estudiantes = $query->map(function ($estudiante) {
                $inscripcion = $estudiante->inscripciones->first();
                return [
                    'numero_documento' => $estudiante->numero_documento,
                    'nombre_completo' => trim("{$estudiante->nombre} {$estudiante->apellido_paterno} {$estudiante->apellido_materno}"),
                    'aula' => $inscripcion && $inscripcion->aula ? $inscripcion->aula->nombre : 'N/A',
                    'turno' => $inscripcion && $inscripcion->turno ? $inscripcion->turno->nombre : 'N/A',
                    'carrera' => $inscripcion && $inscripcion->carrera ? $inscripcion->carrera->nombre : 'N/A',
                ];
            });

            return response()->json([
                'success' => true,
                'estudiantes' => $estudiantes,
                'total' => $estudiantes->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiantes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar asistencias masivas para una fecha específica
     */
    public function registrarMasivo(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required',
            'tipo_verificacion' => 'required|integer|between:0,4',
            'ciclo_id' => 'required|exists:ciclos,id',
            'estudiantes' => 'required|array|min:1',
            'estudiantes.*' => 'required|string|max:20',
        ]);

        try {
            $fechaHora = Carbon::parse($request->fecha . ' ' . $request->hora);
            $registrados = 0;
            $omitidos = 0;
            $errores = [];

            // Usar transacción para asegurar consistencia
            \DB::beginTransaction();

            try {
                foreach ($request->estudiantes as $dni) {
                    try {
                        // Verificar si ya existe un registro para este estudiante en esta fecha y hora exacta
                        // Permitimos múltiples registros por día pero no en la misma hora
                        $existe = RegistroAsistencia::where('nro_documento', $dni)
                            ->where('fecha_hora', $fechaHora)
                            ->exists();

                        if ($existe) {
                            $omitidos++;
                            continue;
                        }

                        // Crear el registro
                        $registro = RegistroAsistencia::create([
                            'nro_documento' => $dni,
                            'fecha_hora' => $fechaHora,
                            'tipo_verificacion' => $request->tipo_verificacion,
                            'estado' => 1,
                            'sn_dispositivo' => 'MASIVO',
                            'fecha_registro' => $fechaHora,
                        ]);

                        $registrados++;
                    } catch (\Exception $e) {
                        // Registrar error individual pero continuar con los demás
                        $errores[] = [
                            'dni' => $dni,
                            'error' => $e->getMessage()
                        ];
                        \Log::error("Error al registrar asistencia para DNI {$dni}: " . $e->getMessage());
                    }
                }

                // Confirmar la transacción
                \DB::commit();

                $mensaje = "Registro masivo completado. {$registrados} registrados, {$omitidos} omitidos (duplicados).";
                if (count($errores) > 0) {
                    $mensaje .= " " . count($errores) . " errores.";
                }

                return response()->json([
                    'success' => true,
                    'message' => $mensaje,
                    'registrados' => $registrados,
                    'omitidos' => $omitidos,
                    'errores' => count($errores),
                    'detalles_errores' => $errores
                ]);

            } catch (\Exception $e) {
                // Revertir la transacción en caso de error crítico
                \DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error en registro masivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar asistencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regularizar asistencias de un estudiante en múltiples fechas
     */
    public function regularizarEstudiante(Request $request)
    {
        $request->validate([
            'nro_documento' => 'required|string|max:20',
            'fechas' => 'required|array|min:1',
            'fechas.*' => 'required|date',
            'hora' => 'required',
            'tipo_verificacion' => 'required|integer|between:0,4',
        ]);

        try {
            // Verificar que el estudiante existe
            $estudiante = User::where('numero_documento', $request->nro_documento)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado.'
                ], 404);
            }

            $registrados = 0;
            $omitidos = 0;
            $detalles = [];

            foreach ($request->fechas as $fecha) {
                // Verificar si ya existe un registro para esta fecha
                $existe = RegistroAsistencia::where('nro_documento', $request->nro_documento)
                    ->whereDate('fecha_hora', $fecha)
                    ->exists();

                if ($existe) {
                    $omitidos++;
                    $detalles[] = [
                        'fecha' => Carbon::parse($fecha)->format('d/m/Y'),
                        'estado' => 'omitido',
                        'motivo' => 'Ya existe registro'
                    ];
                    continue;
                }

                // Crear el registro
                $fechaHora = Carbon::parse($fecha . ' ' . $request->hora);
                
                RegistroAsistencia::create([
                    'nro_documento' => $request->nro_documento,
                    'fecha_hora' => $fechaHora,
                    'tipo_verificacion' => $request->tipo_verificacion,
                    'estado' => 1,
                    'sn_dispositivo' => 'REGULARIZACION',
                    'fecha_registro' => $fechaHora,
                ]);

                $registrados++;
                $detalles[] = [
                    'fecha' => Carbon::parse($fecha)->format('d/m/Y'),
                    'estado' => 'registrado',
                    'motivo' => 'OK'
                ];
            }

            return response()->json([
                'success' => true,
                'message' => "Regularización completada. {$registrados} registrados, {$omitidos} omitidos.",
                'registrados' => $registrados,
                'omitidos' => $omitidos,
                'detalles' => $detalles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al regularizar asistencias: ' . $e->getMessage()
            ], 500);
        }
    }
}
