<?php

namespace App\Http\Controllers;

use App\Models\MaterialAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Curso;
use App\Models\Ciclo;
use App\Models\Aula;
use App\Models\HorarioDocente;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class MaterialAcademicoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MaterialAcademico::with('curso', 'profesor', 'ciclo', 'aula');

        if ($user->hasRole('Docente')) {
            $query->where('profesor_id', $user->id);
        } elseif ($user->hasRole('Estudiante')) {
            $inscripcion = Inscripcion::where('estudiante_id', $user->id)->where('estado', 'activo')->first();
            if ($inscripcion) {
                $query->where('ciclo_id', $inscripcion->ciclo_id)
                      ->where('aula_id', $inscripcion->aula_id);
            } else {
                $query->whereRaw('1 = 0'); // No mostrar nada si no está inscrito
            }
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('profesor.nombre_completo', function (MaterialAcademico $material) {
                    return $material->profesor->nombre_completo ?? 'N/A';
                })
                ->addColumn('curso.nombre', function (MaterialAcademico $material) {
                    return $material->curso->nombre ?? 'N/A';
                })
                ->addColumn('acciones', function (MaterialAcademico $material) use ($user) {
                    $viewUrl = $material->tipo === 'link' 
                                ? $material->archivo 
                                : asset('storage/' . $material->archivo);
                    $editUrl = route('materiales-academicos.edit', $material);
                    $deleteUrl = route('materiales-academicos.destroy', $material);

                    $actions = '<a href="' . $viewUrl . '" target="_blank" class="action-icon"> 
                                    <i class="mdi mdi-eye"></i>
                                </a>';

                    if ($user->can('update', $material)) {
                        $actions .= '<a href="' . $editUrl . '" class="action-icon"> 
                                        <i class="mdi mdi-pencil"></i>
                                     </a>';
                    }
                    if ($user->can('delete', $material)) {
                        $actions .= '<form action="' . $deleteUrl . '" method="POST" class="d-inline">' .
                                    csrf_field() . method_field('DELETE') .
                                    '<button type="submit" class="btn action-icon delete-material" 
                                        onclick="return confirm(\'¿Estás seguro de que quieres eliminar este material?\')">
                                        <i class="mdi mdi-delete"></i>
                                    </button>' .
                                    '</form>';
                    }

                    return $actions;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        return view('materiales-academicos.index');
    }

    public function create()
    {
        $user = Auth::user();
        $cursos = collect();
        $ciclos = collect();
        $aulas = collect();

        if ($user->hasRole('Admin')) {
            $cursos = Curso::where('estado', true)->get();
            $ciclos = Ciclo::where('estado', 'activo')->get();
            $aulas = Aula::where('estado', true)->get();
        }

        return view('materiales-academicos.create', compact('cursos', 'ciclos', 'aulas'));
    }

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
            'archivo' => 'required_if:tipo,!=,link|file|max:10240', // Máx 10MB
            'link' => 'required_if:tipo,==,link|url',
        ]);

        $data = $request->all();
        $data['profesor_id'] = Auth::id();

        if ($request->hasFile('archivo') && $request->tipo !== 'link') {
            $path = $request->file('archivo')->store('materiales-academicos', 'public');
            $data['archivo'] = $path;
        } elseif ($request->tipo === 'link') {
            $data['archivo'] = $request->link;
        }

        MaterialAcademico::create($data);

        return redirect()->route('materiales-academicos.index')->with('success', 'Material académico subido con éxito.');
    }

    public function show(MaterialAcademico $materialAcademico)
    {
        return view('materiales-academicos.show', compact('materialAcademico'));
    }

    public function edit(MaterialAcademico $materialAcademico)
    {
        $this->authorize('update', $materialAcademico);

        $user = Auth::user();
        $cursos = collect();
        $ciclos = collect();
        $aulas = collect();

        if ($user->hasRole('Admin')) {
            $cursos = Curso::where('estado', true)->get();
            $ciclos = Ciclo::where('estado', 'activo')->get();
            $aulas = Aula::where('estado', true)->get();
        } elseif ($user->hasRole('Docente')) {
            $horarios = HorarioDocente::where('docente_id', $user->id)
                                      ->with('curso', 'ciclo', 'aula')
                                      ->get();
            $cursos = $horarios->map->curso->unique('id');
            $ciclos = $horarios->map->ciclo->unique('id');
            $aulas = $horarios->map->aula->unique('id');
        }

        return view('materiales-academicos.edit', compact('materialAcademico', 'cursos', 'ciclos', 'aulas'));
    }

    public function update(Request $request, MaterialAcademico $materialAcademico)
    {
        $this->authorize('update', $materialAcademico);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'curso_id' => 'required|exists:cursos,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'aula_id' => 'required|exists:aulas,id',
            'semana' => 'required|integer|min:1',
            'tipo' => 'required|in:pdf,word,ppt,link,otro',
            'archivo' => 'nullable|file|max:10240',
            'link' => 'nullable|url',
        ]);

        $data = $request->all();

        if ($request->hasFile('archivo') && $request->tipo !== 'link') {
            // Eliminar archivo anterior si existe
            if ($materialAcademico->archivo && $materialAcademico->tipo !== 'link') {
                Storage::disk('public')->delete($materialAcademico->archivo);
            }
            $path = $request->file('archivo')->store('materiales-academicos', 'public');
            $data['archivo'] = $path;
        } elseif ($request->tipo === 'link') {
            $data['archivo'] = $request->link;
        }

        $materialAcademico->update($data);

        return redirect()->route('materiales-academicos.index')->with('success', 'Material académico actualizado con éxito.');
    }

    public function destroy(MaterialAcademico $materialAcademico)
    {
        $this->authorize('delete', $materialAcademico);

        if ($materialAcademico->archivo && $materialAcademico->tipo !== 'link') {
            Storage::disk('public')->delete($materialAcademico->archivo);
        }
        
        $materialAcademico->delete();

        return redirect()->route('materiales-academicos.index')->with('success', 'Material académico eliminado con éxito.');
    }
}
