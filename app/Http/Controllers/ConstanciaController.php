<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConstanciaController extends Controller
{
    /**
     * Mostrar lista de constancias del usuario
     */
    public function index()
    {
        $user = Auth::user();

        // Obtener constancias del usuario (como estudiante o generadas por él)
        $constancias = DB::table('constancias_generadas')
            ->join('inscripciones', 'constancias_generadas.inscripcion_id', '=', 'inscripciones.id')
            ->join('users', 'constancias_generadas.estudiante_id', '=', 'users.id')
            ->join('ciclos', 'inscripciones.ciclo_id', '=', 'ciclos.id')
            ->join('carreras', 'inscripciones.carrera_id', '=', 'carreras.id')
            ->where(function($query) use ($user) {
                $query->where('constancias_generadas.estudiante_id', $user->id)
                      ->orWhere('constancias_generadas.generado_por', $user->id);
            })
            ->select(
                'constancias_generadas.*',
                'inscripciones.id as inscripcion_id',
                'users.nombre',
                'users.apellido_paterno',
                'users.apellido_materno',
                'ciclos.nombre as ciclo_nombre',
                'carreras.nombre as carrera_nombre'
            )
            ->orderBy('constancias_generadas.created_at', 'desc')
            ->get();

        // Transformar los resultados para que sean más fáciles de usar en la vista
        $constancias = $constancias->map(function($constancia) {
            $constancia->estudiante = (object) [
                'nombre' => $constancia->nombre,
                'apellido_paterno' => $constancia->apellido_paterno,
                'apellido_materno' => $constancia->apellido_materno
            ];
            $constancia->inscripcion = (object) [
                'id' => $constancia->inscripcion_id,
                'ciclo' => (object) ['nombre' => $constancia->ciclo_nombre],
                'carrera' => (object) ['nombre' => $constancia->carrera_nombre]
            ];
            return $constancia;
        });

        return view('constancias.index', compact('constancias'));
    }

    /**
     * Obtener constancias por estudiante (para AJAX)
     */
    public function getByEstudiante($estudianteId)
    {
        $user = Auth::user();

        // Verificar permisos
        if ($user->id !== $estudianteId && !$user->hasRole('admin')) {
            abort(403, 'No tienes permiso para ver estas constancias');
        }

        $constancias = DB::table('constancias_generadas')
            ->where('estudiante_id', $estudianteId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($constancias);
    }

    /**
     * Obtener estadísticas de constancias
     */
    public function estadisticas()
    {
        $user = Auth::user();

        $estadisticas = DB::table('constancias_generadas')
            ->selectRaw('tipo, COUNT(*) as total')
            ->where('generado_por', $user->id)
            ->groupBy('tipo')
            ->get();

        return response()->json($estadisticas);
    }

    /**
     * Obtener inscripciones disponibles para generar constancias
     */
    public function getInscripcionesDisponibles(Request $request)
    {
        $user = Auth::user();
        $tipo = $request->get('tipo'); // 'estudios' o 'vacante'
        $dni = $request->get('dni');
        $cicloId = $request->get('ciclo_id');

        $query = DB::table('inscripciones')
            ->join('users', 'inscripciones.estudiante_id', '=', 'users.id')
            ->join('ciclos', 'inscripciones.ciclo_id', '=', 'ciclos.id')
            ->join('carreras', 'inscripciones.carrera_id', '=', 'carreras.id')
            ->leftJoin('turnos', 'inscripciones.turno_id', '=', 'turnos.id')
            ->where('inscripciones.estado_inscripcion', 'activo')
            ->select(
                'inscripciones.id as inscripcion_id',
                'inscripciones.codigo_inscripcion',
                'users.nombre',
                'users.apellido_paterno',
                'users.apellido_materno',
                'users.numero_documento',
                'ciclos.nombre as ciclo_nombre',
                'ciclos.fecha_inicio',
                'ciclos.fecha_fin',
                'carreras.nombre as carrera_nombre',
                'turnos.nombre as turno_nombre'
            );

        // Filtrar por ciclo si se proporciona
        if ($cicloId) {
            $query->where('inscripciones.ciclo_id', $cicloId);
        }

        // Filtrar por DNI si se proporciona
        if ($dni) {
            $query->where('users.numero_documento', $dni);
        }

        // Si no es admin, solo mostrar inscripciones del usuario actual o que tenga permisos
        if (!$user->hasRole('admin')) {
            if ($user->hasPermission('constancias.generar-estudios') || $user->hasPermission('constancias.generar-vacante')) {
                // Los usuarios con permisos pueden ver todas las inscripciones activas
            } else {
                // Solo las propias
                $query->where('inscripciones.estudiante_id', $user->id);
            }
        }

        $inscripciones = $query->orderBy('ciclos.fecha_inicio', 'desc')
            ->orderBy('users.apellido_paterno')
            ->get();

        // Transformar los resultados
        $inscripciones = $inscripciones->map(function($inscripcion) {
            return [
                'id' => $inscripcion->inscripcion_id,
                'codigo_inscripcion' => $inscripcion->codigo_inscripcion,
                'estudiante' => [
                    'nombre' => $inscripcion->nombre,
                    'apellido_paterno' => $inscripcion->apellido_paterno,
                    'apellido_materno' => $inscripcion->apellido_materno,
                    'numero_documento' => $inscripcion->numero_documento
                ],
                'ciclo' => [
                    'nombre' => $inscripcion->ciclo_nombre,
                    'fecha_inicio' => $inscripcion->fecha_inicio,
                    'fecha_fin' => $inscripcion->fecha_fin
                ],
                'carrera' => [
                    'nombre' => $inscripcion->carrera_nombre
                ],
                'turno' => [
                    'nombre' => $inscripcion->turno_nombre
                ]
            ];
        });

        return response()->json($inscripciones);
    }

    /**
     * Obtener ciclos disponibles para filtrar inscripciones
     */
    public function getCiclosDisponibles()
    {
        $user = Auth::user();

        $query = DB::table('ciclos')
            ->join('inscripciones', 'ciclos.id', '=', 'inscripciones.ciclo_id')
            ->where('inscripciones.estado_inscripcion', 'activo')
            ->select(
                'ciclos.id',
                'ciclos.nombre',
                'ciclos.fecha_inicio',
                'ciclos.fecha_fin'
            )
            ->distinct();

        // Si no es admin, solo mostrar ciclos con inscripciones del usuario actual o que tenga permisos
        if (!$user->hasRole('admin')) {
            if ($user->hasPermission('constancias.generar-estudios') || $user->hasPermission('constancias.generar-vacante')) {
                // Los usuarios con permisos pueden ver todos los ciclos con inscripciones activas
            } else {
                // Solo los ciclos donde el usuario está inscrito
                $query->where('inscripciones.estudiante_id', $user->id);
            }
        }

        $ciclos = $query->orderBy('ciclos.fecha_inicio', 'desc')
            ->get();

        return response()->json($ciclos);
    }

    /**
     * Eliminar una constancia
     */
    public function eliminar($constanciaId)
    {
        try {
            $user = Auth::user();

            // Buscar la constancia
            $constancia = DB::table('constancias_generadas')
                ->where('id', $constanciaId)
                ->first();

            if (!$constancia) {
                return response()->json(['error' => 'Constancia no encontrada'], 404);
            }

            // Verificar permisos
            if (!$user->hasRole('admin') && !$user->hasPermission('constancias.eliminar')) {
                return response()->json(['error' => 'No tienes permisos para eliminar esta constancia'], 403);
            }

            // Si no es admin, verificar que la constancia le pertenece o la generó
            if (!$user->hasRole('admin')) {
                if ($constancia->estudiante_id !== $user->id && $constancia->generado_por !== $user->id) {
                    return response()->json(['error' => 'No tienes permisos para eliminar esta constancia'], 403);
                }
            }

            // Eliminar archivo si existe
            if ($constancia->constancia_firmada_path && Storage::disk('public')->exists($constancia->constancia_firmada_path)) {
                Storage::disk('public')->delete($constancia->constancia_firmada_path);
            }

            // Eliminar registro de la base de datos
            DB::table('constancias_generadas')->where('id', $constanciaId)->delete();

            return response()->json(['success' => 'Constancia eliminada correctamente']);

        } catch (\Exception $e) {
            \Log::error('Error al eliminar constancia: ' . $e->getMessage());
            return response()->json(['error' => 'Error al eliminar la constancia'], 500);
        }
    }
}
