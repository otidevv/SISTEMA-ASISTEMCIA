<?php
// app/Models/Carrera.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarreraController extends Controller
{
    public function index()
    {
        $carreras = Carrera::with(['creadoPor', 'actualizadoPor'])
            ->withCount(['inscripciones' => function ($query) {
                $query->where('estado_inscripcion', 'activo');
            }])
            ->get();

        $data = $carreras->map(function ($carrera) {
            return [
                'id' => $carrera->id,
                'codigo' => $carrera->codigo,
                'nombre' => $carrera->nombre,
                'grupo' => $carrera->grupo,
                'descripcion' => $carrera->descripcion,
                'estado' => $carrera->estado,
                'estudiantes_activos' => $carrera->inscripciones_count,
                'creado_por' => $carrera->creadoPor ? $carrera->creadoPor->nombre . ' ' . $carrera->creadoPor->apellido_paterno : null,
                'fecha_creacion' => $carrera->created_at->format('d/m/Y'),
                'actions' => $this->getActionButtons($carrera)
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function getActionButtons($carrera)
    {
        $buttons = '';

        if (auth()->user()->hasPermission('carreras.edit')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit-carrera" data-id="' . $carrera->id . '" title="Editar"><i class="uil uil-edit"></i></a> ';
        }

        if (auth()->user()->hasPermission('carreras.change_status')) {
            $statusIcon = $carrera->estado ? 'uil-ban' : 'uil-check';
            $statusTitle = $carrera->estado ? 'Desactivar' : 'Activar';
            $statusClass = $carrera->estado ? 'warning' : 'success';

            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-' . $statusClass . ' change-status" data-id="' . $carrera->id . '" title="' . $statusTitle . '"><i class="uil ' . $statusIcon . '"></i></a> ';
        }

        if (auth()->user()->hasPermission('carreras.delete')) {
            $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-carrera" data-id="' . $carrera->id . '" title="Eliminar"><i class="uil uil-trash-alt"></i></a>';
        }

        return $buttons;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:carreras',
            'nombre' => 'required|string|max:150',
            'grupo' => 'required|in:A,B,C,D',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
            'grado' => 'nullable|string|max:255',
            'titulo' => 'nullable|string|max:255',
            'duracion' => 'nullable|string|max:100',
            'mision' => 'nullable|string',
            'vision' => 'nullable|string',
            'perfil' => 'nullable|string',
            'objetivos' => 'nullable|string',
            'campo_laboral' => 'nullable|string',
            'malla_file' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
            'imagen_file' => 'nullable|image|max:2048' // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->except(['malla_file', 'imagen_file']);
            $data['creado_por'] = auth()->id();
            $data['estado'] = $request->estado ?? true;

            // Manejo de archivos
            if ($request->hasFile('malla_file')) {
                $path = $request->file('malla_file')->store('carreras/mallas', 'public');
                $data['malla_url'] = 'storage/' . $path;
            }

            if ($request->hasFile('imagen_file')) {
                $path = $request->file('imagen_file')->store('carreras/imagenes', 'public');
                $data['imagen_url'] = 'storage/' . $path;
            }

            // Convertir objetivos de texto a array si es necesario
            if (isset($data['objetivos']) && is_string($data['objetivos'])) {
                $data['objetivos'] = array_filter(array_map('trim', explode("\n", $data['objetivos'])));
            }

            // Convertir campo_laboral de texto a array
            if (isset($data['campo_laboral']) && is_string($data['campo_laboral'])) {
                $data['campo_laboral'] = array_filter(array_map('trim', explode("\n", $data['campo_laboral'])));
            }

            $carrera = Carrera::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Carrera creada exitosamente',
                'data' => $carrera
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la carrera'
            ], 500);
        }
    }

    public function show($id)
    {
        $carrera = Carrera::with(['creadoPor', 'actualizadoPor'])
            ->find($id);

        if (!$carrera) {
            return response()->json([
                'success' => false,
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $carrera
        ]);
    }

    public function update(Request $request, $id)
    {
        $carrera = Carrera::find($id);

        if (!$carrera) {
            return response()->json([
                'success' => false,
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:20|unique:carreras,codigo,' . $id,
            'nombre' => 'required|string|max:150',
            'grupo' => 'required|in:A,B,C,D',
            'descripcion' => 'nullable|string',
            'estado' => 'boolean',
            'grado' => 'nullable|string|max:255',
            'titulo' => 'nullable|string|max:255',
            'duracion' => 'nullable|string|max:100',
            'mision' => 'nullable|string',
            'vision' => 'nullable|string',
            'perfil' => 'nullable|string',
            'objetivos' => 'nullable|string',
            'campo_laboral' => 'nullable|string',
            'malla_file' => 'nullable|file|mimes:pdf|max:5120',
            'imagen_file' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $request->except(['malla_file', 'imagen_file']);
            $data['actualizado_por'] = auth()->id();

            // Manejo de archivos al actualizar
            if ($request->hasFile('malla_file')) {
                // Eliminar archivo anterior si existe
                if ($carrera->malla_url && file_exists(public_path($carrera->malla_url))) {
                    unlink(public_path($carrera->malla_url));
                }
                $path = $request->file('malla_file')->store('carreras/mallas', 'public');
                $data['malla_url'] = 'storage/' . $path;
            }

            if ($request->hasFile('imagen_file')) {
                // Eliminar imagen anterior si existe
                if ($carrera->imagen_url && file_exists(public_path($carrera->imagen_url))) {
                    unlink(public_path($carrera->imagen_url));
                }
                $path = $request->file('imagen_file')->store('carreras/imagenes', 'public');
                $data['imagen_url'] = 'storage/' . $path;
            }

            // Convertir objetivos de texto a array si es necesario
            if (isset($data['objetivos']) && is_string($data['objetivos'])) {
                $data['objetivos'] = array_filter(array_map('trim', explode("\n", $data['objetivos'])));
            }

            // Convertir campo_laboral de texto a array
            if (isset($data['campo_laboral']) && is_string($data['campo_laboral'])) {
                $data['campo_laboral'] = array_filter(array_map('trim', explode("\n", $data['campo_laboral'])));
            }

            $carrera->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Carrera actualizada exitosamente',
                'data' => $carrera
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la carrera'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $carrera = Carrera::find($id);

        if (!$carrera) {
            return response()->json([
                'success' => false,
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        // Verificar si tiene inscripciones
        if ($carrera->inscripciones()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la carrera porque tiene inscripciones asociadas'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $carrera->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Carrera eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la carrera'
            ], 500);
        }
    }

    public function changeStatus($id)
    {
        $carrera = Carrera::find($id);

        if (!$carrera) {
            return response()->json([
                'success' => false,
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        $carrera->estado = !$carrera->estado;
        $carrera->actualizado_por = auth()->id();
        $carrera->save();

        $status = $carrera->estado ? 'activada' : 'desactivada';

        return response()->json([
            'success' => true,
            'message' => "Carrera {$status} exitosamente",
            'data' => $carrera
        ]);
    }

    public function listaActivas()
    {
        $carreras = Carrera::activas()
            ->select('id', 'codigo', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $carreras
        ]);
    }
}
