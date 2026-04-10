<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\MaterialAcademico;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Aula;
use App\Models\HorarioDocente;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MaterialAcademicoApiController extends BaseController
{
    /**
     * Listado de materiales académicos (filtrado por rol)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = MaterialAcademico::with(['curso', 'profesor', 'ciclo', 'aula']);

            if ($user->hasRole('profesor')) {
                $query->where('profesor_id', $user->id);
            } elseif ($user->hasRole('estudiante')) {
                $inscripcion = Inscripcion::where('estudiante_id', $user->id)
                    ->where('estado_inscripcion', 'activo')
                    ->first();
                
                if ($inscripcion) {
                    $query->where('ciclo_id', $inscripcion->ciclo_id)
                          ->where('aula_id', $inscripcion->aula_id);
                } else {
                    return $this->sendResponse([], 'No tienes inscripciones activas para ver materiales.');
                }
            }

            // Filtros adicionales si se proveen
            if ($request->has('curso_id')) {
                $query->where('curso_id', $request->curso_id);
            }
            if ($request->has('semana')) {
                $query->where('semana', $request->semana);
            }

            $materiales = $query->get();

            // Formatear datos para el modelo AcademicMaterial en Flutter
            $materiales->transform(function ($material) {
                return [
                    'id' => $material->id,
                    'titulo' => $material->titulo,
                    'descripcion' => $material->descripcion ?? '',
                    'semana' => $material->semana,
                    'tipo' => $material->tipo,
                    'curso' => $material->curso->nombre ?? 'N/A',
                    'profesor' => $material->profesor ? ($material->profesor->nombre . ' ' . $material->profesor->apellido_paterno) : 'N/A',
                    'url' => ($material->tipo !== 'link') ? Storage::url($material->archivo) : $material->archivo,
                    'fecha' => $material->created_at->format('d/m/Y'),
                ];
            });

            return $this->sendResponse($materiales, 'Materiales recuperados con éxito.');
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar materiales: ' . $e->getMessage());
        }
    }

    /**
     * Obtener datos para el formulario de creación (Ciclos, Cursos, Aulas)
     */
    public function getFormData(Request $request)
    {
        try {
            $user = Auth::user();
            $cicloActivo = Ciclo::where('es_activo', true)->first();
            
            if (!$cicloActivo) {
                return $this->sendError('No hay ciclo académico activo.');
            }

            $data = [
                'ciclos' => [$cicloActivo],
                'cursos' => [],
                'aulas' => []
            ];

            if ($user->hasRole('admin')) {
                $data['cursos'] = Curso::where('estado', true)->get();
                $data['aulas'] = Aula::where('estado', true)->get();
            } elseif ($user->hasRole('profesor')) {
                $horarios = HorarioDocente::where('docente_id', $user->id)
                    ->where('ciclo_id', $cicloActivo->id)
                    ->with(['curso', 'aula'])
                    ->get();
                
                $data['cursos'] = $horarios->map->curso->where('estado', true)->unique('id')->values();
                $data['aulas'] = $horarios->map->aula->where('estado', true)->unique('id')->values();
            }

            return $this->sendResponse($data, 'Datos de formulario recuperados.');
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar datos: ' . $e->getMessage());
        }
    }

    /**
     * Guardar nuevo material
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'curso_id' => 'required|exists:cursos,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
            'semana' => 'required|integer|min:1',
            'tipo' => 'required|in:pdf,word,ppt,link,otro',
            'archivo' => ['required_if:tipo,!=,link', 'file', 'max:10240'],
            'link' => ['nullable', 'url', Rule::requiredIf($request->tipo == 'link')]
        ]);

        try {
            $user = Auth::user();
            $data = $request->only(['titulo', 'descripcion', 'curso_id', 'ciclo_id', 'aula_id', 'semana', 'tipo']);
            $data['profesor_id'] = $user->id;

            if ($request->hasFile('archivo') && $request->tipo !== 'link') {
                $path = $request->file('archivo')->store('materiales-academicos', 'public');
                $data['archivo'] = $path;
            } elseif ($request->tipo === 'link') {
                $data['archivo'] = $request->link;
            }

            $material = MaterialAcademico::create($data);

            return $this->sendResponse($material, 'Material académico creado con éxito.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error al crear material: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar material existente
     */
    public function update(Request $request, $id)
    {
        try {
            $material = MaterialAcademico::findOrFail($id);
            $user = Auth::user();

            if ($material->profesor_id !== $user->id && !$user->hasRole('admin')) {
                return $this->sendError('No tienes permiso para editar este material.', [], 403);
            }

            $request->validate([
                'titulo' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'semana' => 'required|integer|min:1',
                'tipo' => 'required|in:pdf,word,ppt,link,otro',
                'archivo' => ['nullable', 'file', 'max:10240'],
                'link' => ['nullable', 'url', Rule::requiredIf($request->tipo == 'link')]
            ]);

            $data = $request->only(['titulo', 'descripcion', 'semana', 'tipo']);

            if ($request->hasFile('archivo') && $request->tipo !== 'link') {
                if ($material->archivo && $material->tipo !== 'link') {
                    Storage::disk('public')->delete($material->archivo);
                }
                $path = $request->file('archivo')->store('materiales-academicos', 'public');
                $data['archivo'] = $path;
            } elseif ($request->tipo === 'link') {
                $data['archivo'] = $request->link;
            }

            $material->update($data);

            return $this->sendResponse($material, 'Material académico actualizado con éxito.');
        } catch (\Exception $e) {
            return $this->sendError('Error al actualizar material: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar material
     */
    public function destroy($id)
    {
        try {
            $material = MaterialAcademico::findOrFail($id);
            $user = Auth::user();

            if ($material->profesor_id !== $user->id && !$user->hasRole('admin')) {
                return $this->sendError('No tienes permiso para eliminar este material.', [], 403);
            }

            if ($material->archivo && $material->tipo !== 'link') {
                Storage::disk('public')->delete($material->archivo);
            }

            $material->delete();

            return $this->sendResponse(null, 'Material académico eliminado con éxito.');
        } catch (\Exception $e) {
            return $this->sendError('Error al eliminar material: ' . $e->getMessage());
        }
    }
}
