<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\User;
use App\Notifications\GeneralAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        // ✅ USAR CÓDIGOS CORRECTOS CON GUIONES BAJOS
        if (!Auth::user()->hasPermission('announcements_view')) {
            abort(403, 'No tienes permisos para ver anuncios.');
        }
        
        $anuncios = Anuncio::with('creador')
            ->orderByRaw('CASE WHEN es_activo = 1 THEN 0 ELSE 1 END')
            ->orderBy('prioridad', 'desc')
            ->orderBy('fecha_publicacion', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('anuncios.index', compact('anuncios'));
    }

    public function create()
    {
        // ✅ USAR CÓDIGOS CORRECTOS CON GUIONES BAJOS
        if (!Auth::user()->hasPermission('announcements_create')) {
            abort(403, 'No tienes permisos para crear anuncios.');
        }

        return view('anuncios.create');
    }

    public function show(Anuncio $anuncio)
    {
        // ✅ USAR CÓDIGOS CORRECTOS CON GUIONES BAJOS
        if (!Auth::user()->hasPermission('announcements_view')) {
            abort(403, 'No tienes permisos para ver este anuncio.');
        }

        return view('anuncios.show', compact('anuncio'));
    }

    public function edit(Anuncio $anuncio)
    {
        // ✅ USAR CÓDIGOS CORRECTOS CON GUIONES BAJOS
        if (!Auth::user()->hasPermission('announcements_edit')) {
            abort(403, 'No tienes permisos para editar anuncios.');
        }

        return view('anuncios.edit', compact('anuncio'));
    }

    public function store(Request $request)
    {
        // ✅ USAR CÓDIGOS CORRECTOS CON GUIONES BAJOS
        if (!Auth::user()->hasPermission('announcements_create')) {
            abort(403, 'No tienes permisos para crear anuncios.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'descripcion' => 'nullable|string|max:500',
            'es_activo' => 'boolean',
            'fecha_publicacion' => 'nullable|date',
            'fecha_expiracion' => 'nullable|date|after_or_equal:fecha_publicacion',
            'prioridad' => 'required|integer|between:1,4',
            'tipo' => 'required|in:informativo,importante,urgente,mantenimiento,evento',
            'dirigido_a' => 'required|in:todos,estudiantes,docentes,administrativos,padres',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'enviar_notificacion' => 'boolean'
        ]);

        try {
            $data = $request->only([
                'titulo', 'contenido', 'descripcion', 'es_activo',
                'fecha_publicacion', 'fecha_expiracion', 'prioridad', 'tipo', 'dirigido_a'
            ]);

            $data['creado_por'] = Auth::id();
            $data['es_activo'] = $request->boolean('es_activo', true);

            if (!$data['fecha_publicacion']) {
                $data['fecha_publicacion'] = now();
            }

            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $rutaImagen = $imagen->storeAs('anuncios', $nombreImagen, 'public');
                $data['imagen'] = $rutaImagen;
            }

            $anuncio = Anuncio::create($data);

            if ($request->boolean('enviar_notificacion', false)) {
                $this->enviarNotificaciones($anuncio);
            }

            return redirect()->route('anuncios.index')
                ->with('success', 'Anuncio creado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el anuncio: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Anuncio $anuncio)
    {
        // ✅ USAR CÓDIGOS CORRECTOS CON GUIONES BAJOS
        if (!Auth::user()->hasPermission('announcements_edit')) {
            abort(403, 'No tienes permisos para actualizar anuncios.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'descripcion' => 'nullable|string|max:500',
            'es_activo' => 'boolean',
            'fecha_publicacion' => 'nullable|date',
            'fecha_expiracion' => 'nullable|date|after_or_equal:fecha_publicacion',
            'prioridad' => 'required|integer|between:1,4',
            'tipo' => 'required|in:informativo,importante,urgente,mantenimiento,evento',
            'dirigido_a' => 'required|in:todos,estudiantes,docentes,administrativos,padres',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = $request->only([
                'titulo', 'contenido', 'descripcion', 'es_activo',
                'fecha_publicacion', 'fecha_expiracion', 'prioridad', 'tipo', 'dirigido_a'
            ]);

            $data['es_activo'] = $request->boolean('es_activo');

            if ($request->hasFile('imagen')) {
                if ($anuncio->imagen && Storage::disk('public')->exists($anuncio->imagen)) {
                    Storage::disk('public')->delete($anuncio->imagen);
                }

                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $rutaImagen = $imagen->storeAs('anuncios', $nombreImagen, 'public');
                $data['imagen'] = $rutaImagen;
            }

            $anuncio->update($data);

            return redirect()->route('anuncios.index')
                ->with('success', 'Anuncio actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el anuncio: ' . $e->getMessage());
        }
    }

    public function destroy(Anuncio $anuncio)
    {
        // ✅ USAR CÓDIGOS CORRECTOS CON GUIONES BAJOS
        if (!Auth::user()->hasPermission('announcements_delete')) {
            abort(403, 'No tienes permisos para eliminar anuncios.');
        }

        try {
            if ($anuncio->imagen && Storage::disk('public')->exists($anuncio->imagen)) {
                Storage::disk('public')->delete($anuncio->imagen);
            }

            $anuncio->delete();

            return redirect()->route('anuncios.index')
                ->with('success', 'Anuncio eliminado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el anuncio: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificaciones
     */
    private function enviarNotificaciones(Anuncio $anuncio)
    {
        try {
            $usuarios = collect();

            switch($anuncio->dirigido_a) {
                case 'todos':
                    $usuarios = User::where('estado', true)->get();
                    break;
                case 'estudiantes':
                    $usuarios = User::whereHas('roles', function($q) {
                        $q->where('nombre', 'estudiante');
                    })->where('estado', true)->get();
                    break;
                case 'docentes':
                    $usuarios = User::whereHas('roles', function($q) {
                        $q->where('nombre', 'profesor');
                    })->where('estado', true)->get();
                    break;
                case 'administrativos':
                    $usuarios = User::whereHas('roles', function($q) {
                        $q->where('nombre', 'admin');
                    })->where('estado', true)->get();
                    break;
                case 'padres':
                    $usuarios = User::whereHas('roles', function($q) {
                        $q->where('nombre', 'padre');
                    })->where('estado', true)->get();
                    break;
            }

            if (class_exists(GeneralAnnouncement::class) && $usuarios->count() > 0) {
                Notification::send($usuarios, new GeneralAnnouncement($anuncio->titulo, $anuncio->contenido));
            }

        } catch (\Exception $e) {
            \Log::error('Error enviando notificaciones de anuncio: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado del anuncio
     */
    public function toggleStatus(Anuncio $anuncio)
    {
        // ✅ USAR CÓDIGOS CORRECTOS CON GUIONES BAJOS
        if (!Auth::user()->hasPermission('announcements_edit')) {
            abort(403, 'No tienes permisos para cambiar el estado de anuncios.');
        }

        $anuncio->update([
            'es_activo' => !$anuncio->es_activo
        ]);

        $status = $anuncio->es_activo ? 'activado' : 'desactivado';

        return redirect()->back()
                        ->with('success', "Anuncio {$status} exitosamente.");
    }

    /**
     * Obtener anuncios activos para el modal público
     */
    public function getActivos()
    {
        try {
            $anuncios = Anuncio::publicados()
                ->ordenadosPorPrioridad()
                ->whereIn('tipo', ['importante', 'urgente', 'evento']) // Solo tipos relevantes
                ->get(['id', 'titulo', 'descripcion', 'imagen', 'tipo', 'prioridad']);
            
            return response()->json($anuncios);
        } catch (\Exception $e) {
            \Log::error('Error fetching active announcements: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}