<?php

namespace App\Http\Controllers;

use App\Models\ProgramaAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProgramaAcademicoController extends Controller
{
    public function index()
    {
        $programas = ProgramaAcademico::all();
        return view('admin.programas.index', compact('programas'));
    }

    public function create()
    {
        return view('admin.programas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|unique:programas_academicos,nombre',
        ]);

        ProgramaAcademico::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre),
            'estado' => 1,
        ]);

        return redirect()->route('programas.index')->with('success', 'Programa creado correctamente');
    }

    public function toggle($id)
    {
        $programa = ProgramaAcademico::findOrFail($id);
        $programa->estado = !$programa->estado;
        $programa->save();

        return back()->with('success', 'Estado del programa actualizado');
    }
}
