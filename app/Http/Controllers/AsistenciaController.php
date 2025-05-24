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
            $query->where(function($q) use ($fecha) {
                $q->whereDate('fecha_registro', $fecha)
                ->orWhere(function($q2) use ($fecha) {
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
        
        // Obtener usuarios para el filtro (opcional)
        $usuarios = User::select('id', 'numero_documento', 'nombre', 'apellido_paterno')->get();
        
        return view('asistencia.index', compact('registros', 'usuarios', 'fecha', 'documento'));
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
            'fecha_registro' => now(),
        ]);

        return redirect()->route('asistencia.index')->with('success', 'Registro de asistencia creado exitosamente.');
    }

    /**
     * Mostrar el formulario para editar registros de asistencia.
     */
    /**
     * Mostrar el formulario para editar registros de asistencia.
     */
    public function editarIndex(Request $request)
    {
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

            return view('asistencia.editar_index', compact('registros'));
        }

        // Si no hay parámetros, mostrar solo el formulario de búsqueda
        return view('asistencia.editar_index');
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
            'fecha_hora' => 'required|date',
            'tipo_verificacion' => 'required|integer',
            'estado' => 'required|boolean',
        ]);

        $asistencia->update([
            'nro_documento' => $request->nro_documento,
            'fecha_hora' => $request->fecha_hora,
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
}
