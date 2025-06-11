<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HorarioDocente;
use App\Models\User;
use App\Models\Ciclo;
use App\Models\Aula;
use App\Models\Curso;

class HorarioDocenteController extends Controller
{
    public function index()
    {
        // Carga las relaciones necesarias para mostrar en la vista
        $horarios = HorarioDocente::with('docente', 'aula', 'ciclo', 'curso')->paginate(10);
        return view('horarios_docentes.index', compact('horarios'));
    }

    public function create()
    {
        // Obtener todos los usuarios con rol 'profesor'
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->get();

        $aulas = Aula::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::where('estado', true)->get(); // ← solo cursos activos
        return view('horarios_docentes.create', compact('docentes', 'aulas', 'ciclos', 'cursos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'docente_id'   => 'required',
            'aula_id'      => 'required',
            'ciclo_id'     => 'required',
            'curso_id'     => 'required', // ← validamos el curso
            'dia_semana'   => 'required',
            'hora_inicio'  => 'required',
            'hora_fin'     => 'required',
        ]);

        HorarioDocente::create($request->all());

        return redirect()->route('horarios-docentes.index')
            ->with('success', 'Horario asignado correctamente.');
    }

    public function edit($id)
    {
        $horario = HorarioDocente::findOrFail($id);

        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor');
        })->get();

        $aulas = Aula::all();
        $ciclos = Ciclo::all();
        $cursos = Curso::all(); // ← agregamos cursos

        return view('horarios_docentes.edit', compact('horario', 'docentes', 'aulas', 'ciclos', 'cursos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'docente_id'   => 'required',
            'aula_id'      => 'required',
            'ciclo_id'     => 'required',
            'curso_id'     => 'required', // ← validamos también en update
            'dia_semana'   => 'required',
            'hora_inicio'  => 'required',
            'hora_fin'     => 'required',
        ]);

        $horario = HorarioDocente::findOrFail($id);
        $horario->update($request->all());

        return redirect()->route('horarios-docentes.index')
            ->with('success', 'Horario actualizado.');
    }

    public function destroy($id)
    {
        $horario = HorarioDocente::findOrFail($id);
        $horario->delete();

        return back()->with('success', 'Horario eliminado.');
    }
}
