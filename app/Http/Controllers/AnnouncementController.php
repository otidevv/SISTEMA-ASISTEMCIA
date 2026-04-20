<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\User;
use App\Models\Role;
use App\Notifications\GeneralAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
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
        if (!Auth::user()->hasPermission('announcements_create')) {
            abort(403, 'No tienes permisos para crear anuncios.');
        }

        $roles = Role::orderBy('nombre')->get();
        return view('anuncios.create', compact('roles'));
    }

    public function show(Anuncio $anuncio)
    {
        if (!Auth::user()->hasPermission('announcements_view')) {
            abort(403, 'No tienes permisos para ver este anuncio.');
        }

        return view('anuncios.show', compact('anuncio'));
    }

    public function edit(Anuncio $anuncio)
    {
        if (!Auth::user()->hasPermission('announcements_edit')) {
            abort(403, 'No tienes permisos para editar anuncios.');
        }

        $roles = Role::orderBy('nombre')->get();
        $selectedRoles = $anuncio->roles->pluck('id')->toArray();
        return view('anuncios.edit', compact('anuncio', 'roles', 'selectedRoles'));
    }

    public function store(Request $request)
    {
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
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'archivo_adjunto' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,mp4,mov,avi|max:102400',
            'enviar_notificacion' => 'boolean'
        ]);

        try {
            $data = $request->only([
                'titulo', 'contenido', 'descripcion', 'es_activo',
                'fecha_publicacion', 'fecha_expiracion', 'prioridad', 'tipo'
            ]);

            $data['creado_por'] = Auth::id();
            $data['es_activo'] = $request->boolean('es_activo', true);

            if (!$data['fecha_publicacion']) {
                $data['fecha_publicacion'] = now();
            }

            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $rutaImagen = $imagen->storeAs('anuncios/imagenes', $nombreImagen, 'public');
                $data['imagen'] = $rutaImagen;
            }

            if ($request->hasFile('archivo_adjunto')) {
                $archivo = $request->file('archivo_adjunto');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $rutaArchivo = $archivo->storeAs('anuncios/documentos', $nombreArchivo, 'public');
                $data['archivo_adjunto'] = $rutaArchivo;
                $data['tipo_archivo'] = $archivo->getClientOriginalExtension();
            }

            $anuncio = Anuncio::create($data);
            
            if ($request->has('role_ids')) {
                $anuncio->roles()->sync($request->role_ids);
            }

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
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = $request->only([
                'titulo', 'contenido', 'descripcion', 'es_activo',
                'fecha_publicacion', 'fecha_expiracion', 'prioridad', 'tipo'
            ]);

            $data['es_activo'] = $request->boolean('es_activo');

            if ($request->hasFile('imagen')) {
                if ($anuncio->imagen && Storage::disk('public')->exists($anuncio->imagen)) {
                    Storage::disk('public')->delete($anuncio->imagen);
                }

                $imagen = $request->file('imagen');
                $nombreImagen = time() . '_' . $imagen->getClientOriginalName();
                $rutaImagen = $imagen->storeAs('anuncios/imagenes', $nombreImagen, 'public');
                $data['imagen'] = $rutaImagen;
            }

            if ($request->hasFile('archivo_adjunto')) {
                if ($anuncio->archivo_adjunto && Storage::disk('public')->exists($anuncio->archivo_adjunto)) {
                    Storage::disk('public')->delete($anuncio->archivo_adjunto);
                }

                $archivo = $request->file('archivo_adjunto');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $rutaArchivo = $archivo->storeAs('anuncios/documentos', $nombreArchivo, 'public');
                $data['archivo_adjunto'] = $rutaArchivo;
                $data['tipo_archivo'] = $archivo->getClientOriginalExtension();
            }

            $anuncio->update($data);
            
            if ($request->has('role_ids')) {
                $anuncio->roles()->sync($request->role_ids);
            }

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

    private function enviarNotificaciones(Anuncio $anuncio)
    {
        try {
            $roleIds = $anuncio->roles->pluck('id')->toArray();
            $usuarios = collect();

            if (!empty($roleIds)) {
                $usuarios = User::whereHas('roles', function($q) use ($roleIds) {
                    $q->whereIn('roles.id', $roleIds);
                })->where('estado', true)->get();
            }

            if ($usuarios->count() > 0) {
                Notification::send($usuarios, new GeneralAnnouncement(
                    $anuncio->titulo, 
                    $anuncio->contenido, 
                    $anuncio->imagen,
                    $anuncio->archivo_adjunto,
                    $anuncio->tipo_archivo
                ));
            }

        } catch (\Exception $e) {
            \Log::error('Error enviando notificaciones de anuncio: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Anuncio $anuncio)
    {
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

    public function getActivos()
    {
        try {
            $query = Anuncio::publicados()
                ->ordenadosPorPrioridad()
                ->whereIn('tipo', ['importante', 'urgente', 'evento', 'informativo', 'mantenimiento']);

            if (Auth::check()) {
                $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                $query->whereHas('roles', function($q) use ($userRoleIds) {
                    $q->whereIn('roles.id', $userRoleIds);
                });
            } else {
                $query->whereDoesntHave('roles');
            }

            $anuncios = $query->get(['id', 'titulo', 'descripcion', 'imagen', 'archivo_adjunto', 'tipo_archivo', 'tipo', 'prioridad']);
            
            return response()->json($anuncios);
        } catch (\Exception $e) {
            \Log::error('Error fetching active announcements: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}