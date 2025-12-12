<?php

namespace App\Http\Controllers;

use App\Models\ResultadoExamen;
use App\Models\Ciclo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NuevoResultadoExamen;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ResultadoExamenController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource (Admin view).
     */
    public function index()
    {
        $this->authorize('resultados-examenes.view');
        
        $resultados = ResultadoExamen::with(['ciclo', 'creador'])
            ->ordenado()
            ->paginate(15);
        
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        
        return view('resultados-examenes.index', compact('resultados', 'ciclos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('resultados-examenes.create');
        
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        
        return view('resultados-examenes.create', compact('ciclos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('resultados-examenes.create');
        
        $validated = $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'nombre_examen' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_resultado' => 'required|in:pdf,link,ambos',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:10240', // 10MB
            'link_externo' => 'nullable|url',
            'fecha_examen' => 'required|date',
            'visible' => 'boolean',
            'orden' => 'nullable|integer',
        ]);

        // Validar que al menos tenga PDF o link según el tipo
        if ($validated['tipo_resultado'] === 'pdf' && !$request->hasFile('archivo_pdf')) {
            return back()->withErrors(['archivo_pdf' => 'Debe subir un archivo PDF'])->withInput();
        }
        
        if ($validated['tipo_resultado'] === 'link' && empty($validated['link_externo'])) {
            return back()->withErrors(['link_externo' => 'Debe proporcionar un enlace'])->withInput();
        }
        
        if ($validated['tipo_resultado'] === 'ambos' && (!$request->hasFile('archivo_pdf') || empty($validated['link_externo']))) {
            return back()->withErrors(['tipo_resultado' => 'Debe proporcionar tanto el PDF como el enlace'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Subir archivo PDF si existe
            $archivoPdfPath = null;
            if ($request->hasFile('archivo_pdf')) {
                $file = $request->file('archivo_pdf');
                $filename = time() . '_' . $file->getClientOriginalName();
                $archivoPdfPath = $file->storeAs('resultados-examenes', $filename, 'public');
            }

            // Crear el resultado
            $resultado = ResultadoExamen::create([
                'ciclo_id' => $validated['ciclo_id'],
                'nombre_examen' => $validated['nombre_examen'],
                'descripcion' => $validated['descripcion'],
                'tipo_resultado' => $validated['tipo_resultado'],
                'archivo_pdf' => $archivoPdfPath,
                'link_externo' => $validated['link_externo'] ?? null,
                'fecha_examen' => $validated['fecha_examen'],
                'fecha_publicacion' => $request->has('visible') && $request->visible ? now() : null,
                'visible' => $request->has('visible') ? true : false,
                'orden' => $validated['orden'] ?? 0,
                'created_by' => Auth::id(),
            ]);

            // Enviar notificaciones si está visible
            if ($resultado->visible) {
                $this->enviarNotificaciones($resultado);
            }

            DB::commit();

            return redirect()->route('resultados-examenes.index')
                ->with('success', 'Resultado de examen creado exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Eliminar archivo si se subió
            if ($archivoPdfPath && Storage::disk('public')->exists($archivoPdfPath)) {
                Storage::disk('public')->delete($archivoPdfPath);
            }
            
            return back()->withErrors(['error' => 'Error al crear el resultado: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->authorize('resultados-examenes.edit');
        
        $resultado = ResultadoExamen::with('ciclo')->findOrFail($id);
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        
        return view('resultados-examenes.edit', compact('resultado', 'ciclos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('resultados-examenes.edit');
        
        $resultado = ResultadoExamen::findOrFail($id);
        
        $validated = $request->validate([
            'ciclo_id' => 'required|exists:ciclos,id',
            'nombre_examen' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_resultado' => 'required|in:pdf,link,ambos',
            'archivo_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'link_externo' => 'nullable|url',
            'fecha_examen' => 'required|date',
            'visible' => 'boolean',
            'orden' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $archivoPdfPath = $resultado->archivo_pdf;
            
            // Si se sube un nuevo archivo, eliminar el anterior
            if ($request->hasFile('archivo_pdf')) {
                if ($archivoPdfPath && Storage::disk('public')->exists($archivoPdfPath)) {
                    Storage::disk('public')->delete($archivoPdfPath);
                }
                
                $file = $request->file('archivo_pdf');
                $filename = time() . '_' . $file->getClientOriginalName();
                $archivoPdfPath = $file->storeAs('resultados-examenes', $filename, 'public');
            }

            $wasVisible = $resultado->visible;
            $isNowVisible = $request->has('visible');

            // Actualizar el resultado
            $resultado->update([
                'ciclo_id' => $validated['ciclo_id'],
                'nombre_examen' => $validated['nombre_examen'],
                'descripcion' => $validated['descripcion'],
                'tipo_resultado' => $validated['tipo_resultado'],
                'archivo_pdf' => $archivoPdfPath,
                'link_externo' => $validated['link_externo'] ?? null,
                'fecha_examen' => $validated['fecha_examen'],
                'fecha_publicacion' => $isNowVisible && !$wasVisible ? now() : $resultado->fecha_publicacion,
                'visible' => $isNowVisible,
                'orden' => $validated['orden'] ?? 0,
            ]);

            // Enviar notificaciones si se acaba de hacer visible
            if ($isNowVisible && !$wasVisible) {
                $this->enviarNotificaciones($resultado);
            }

            DB::commit();

            return redirect()->route('resultados-examenes.index')
                ->with('success', 'Resultado de examen actualizado exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el resultado: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorize('resultados-examenes.delete');
        
        try {
            $resultado = ResultadoExamen::findOrFail($id);
            $resultado->delete(); // El modelo se encarga de eliminar el archivo
            
            return redirect()->route('resultados-examenes.index')
                ->with('success', 'Resultado de examen eliminado exitosamente');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el resultado: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle visibility of the result.
     */
    public function toggleVisibility($id)
    {
        $this->authorize('resultados-examenes.publish');
        
        try {
            $resultado = ResultadoExamen::findOrFail($id);
            $resultado->visible = !$resultado->visible;
            
            if ($resultado->visible && !$resultado->fecha_publicacion) {
                $resultado->fecha_publicacion = now();
                // Enviar notificaciones
                $this->enviarNotificaciones($resultado);
            }
            
            $resultado->save();
            
            $estado = $resultado->visible ? 'publicado' : 'despublicado';
            
            return response()->json([
                'success' => true,
                'message' => "Resultado {$estado} exitosamente",
                'visible' => $resultado->visible
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la visibilidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display public view of exam results.
     */
    public function publicIndex(Request $request)
    {
        $cicloId = $request->get('ciclo');
        
        $query = ResultadoExamen::with('ciclo')
            ->visible()
            ->ordenado();
        
        if ($cicloId) {
            $query->porCiclo($cicloId);
        }
        
        // Agrupar resultados por ciclo
        $resultados = $query->get()->groupBy('ciclo_id');
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();
        
        return view('resultados-examenes.public', compact('resultados', 'ciclos', 'cicloId'));
    }

    /**
     * View PDF file inline in browser.
     */
    public function view($id)
    {
        $resultado = ResultadoExamen::findOrFail($id);
        
        if (!$resultado->tiene_pdf) {
            abort(404, 'Archivo no encontrado');
        }
        
        $path = Storage::disk('public')->path($resultado->archivo_pdf);
        
        if (!file_exists($path)) {
            abort(404, 'Archivo no encontrado');
        }
        
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $resultado->nombre_archivo_pdf . '"'
        ]);
    }

    /**
     * Download PDF file.
     */
    public function download($id)
    {
        $resultado = ResultadoExamen::findOrFail($id);
        
        if (!$resultado->tiene_pdf) {
            abort(404, 'Archivo no encontrado');
        }
        
        return Storage::disk('public')->download($resultado->archivo_pdf, $resultado->nombre_archivo_pdf);
    }

    /**
     * Enviar notificaciones a estudiantes sobre nuevo resultado.
     */
    private function enviarNotificaciones($resultado)
    {
        try {
            // Obtener todos los estudiantes del ciclo
            $estudiantes = User::whereHas('roles', function ($query) {
                $query->where('nombre', 'estudiante');
            })
            ->whereHas('inscripciones', function ($query) use ($resultado) {
                $query->where('ciclo_id', $resultado->ciclo_id);
            })
            ->get();

            // Enviar notificación a cada estudiante
            foreach ($estudiantes as $estudiante) {
                // Crear notificación en la base de datos
                DB::table('notifications')->insert([
                    'type' => 'App\\Notifications\\NuevoResultadoExamen',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $estudiante->id,
                    'data' => json_encode([
                        'titulo' => 'Nuevo Resultado de Examen',
                        'mensaje' => "Se ha publicado el resultado de: {$resultado->nombre_examen}",
                        'resultado_id' => $resultado->id,
                        'ciclo' => $resultado->ciclo->nombre,
                        'url' => route('resultados-examenes.public')
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
        } catch (\Exception $e) {
            // Log error pero no fallar la operación principal
            \Log::error('Error al enviar notificaciones de resultado de examen: ' . $e->getMessage());
        }
    }
}
