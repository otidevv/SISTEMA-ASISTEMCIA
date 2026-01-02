<?php

namespace App\Http\Controllers;

use App\Models\CarnetTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CarnetTemplateController extends Controller
{
    /**
     * Mostrar lista de plantillas
     */
    public function index()
    {
        if (!Auth::user()->hasPermission('carnets.templates.view')) {
            abort(403, 'Sin permisos para ver plantillas');
        }

        $templates = CarnetTemplate::with(['creador', 'actualizador'])
            ->orderBy('activa', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('carnets.templates.index', compact('templates'));
    }

    /**
     * Mostrar editor visual para crear plantilla
     */
    public function create()
    {
        if (!Auth::user()->hasPermission('carnets.templates.create')) {
            abort(403, 'Sin permisos para crear plantillas');
        }

        return view('carnets.templates.editor');
    }

    /**
     * Guardar nueva plantilla
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.templates.create')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:postulante,estudiante,docente,administrativo',
            'ancho_mm' => 'required|numeric|min:0',
            'alto_mm' => 'required|numeric|min:0',
            'campos_config' => 'required|json',
            'descripcion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $template = CarnetTemplate::create([
                'nombre' => $request->nombre,
                'tipo' => $request->tipo,
                'fondo_path' => $request->fondo_path,
                'ancho_mm' => $request->ancho_mm,
                'alto_mm' => $request->alto_mm,
                'campos_config' => json_decode($request->campos_config, true),
                'descripcion' => $request->descripcion,
                'activa' => false,
                'creado_por' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plantilla creada exitosamente',
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar editor para editar plantilla
     */
    public function edit($id)
    {
        if (!Auth::user()->hasPermission('carnets.templates.edit')) {
            abort(403, 'Sin permisos para editar plantillas');
        }

        $template = CarnetTemplate::findOrFail($id);

        return view('carnets.templates.editor', compact('template'));
    }

    /**
     * Actualizar plantilla
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('carnets.templates.edit')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:postulante,estudiante,docente,administrativo',
            'ancho_mm' => 'required|numeric|min:0',
            'alto_mm' => 'required|numeric|min:0',
            'campos_config' => 'required|json',
            'descripcion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $template = CarnetTemplate::findOrFail($id);
            
            $template->update([
                'nombre' => $request->nombre,
                'tipo' => $request->tipo,
                'fondo_path' => $request->fondo_path ?? $template->fondo_path,
                'ancho_mm' => $request->ancho_mm,
                'alto_mm' => $request->alto_mm,
                'campos_config' => json_decode($request->campos_config, true),
                'descripcion' => $request->descripcion,
                'actualizado_por' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plantilla actualizada exitosamente',
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar plantilla
     */
    public function activate($id)
    {
        if (!Auth::user()->hasPermission('carnets.templates.activate')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $template = CarnetTemplate::findOrFail($id);
            $template->activar();

            return response()->json([
                'success' => true,
                'message' => 'Plantilla activada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar plantilla
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasPermission('carnets.templates.delete')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $template = CarnetTemplate::findOrFail($id);
            
            if ($template->activa) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar una plantilla activa'
                ], 400);
            }

            // Eliminar imagen de fondo si existe
            if ($template->fondo_path && Storage::disk('public')->exists($template->fondo_path)) {
                Storage::disk('public')->delete($template->fondo_path);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plantilla eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar plantilla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir imagen de fondo
     */
    public function uploadFondo(Request $request)
    {
        if (!Auth::user()->hasPermission('carnets.templates.create')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $validator = Validator::make($request->all(), [
            'fondo' => 'required|image|mimes:jpeg,png,jpg|max:5120' // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('fondo');
            $filename = 'carnet_fondo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('carnets/fondos', $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'Imagen subida exitosamente',
                'path' => $path,
                'url' => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir imagen: ' . $e->getMessage()
            ], 500);
        }
    }
}
