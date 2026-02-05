<?php
// app/Http/Controllers/CicloController.php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use Illuminate\Http\Request;

class CicloController extends Controller
{
    public function index()
    {
        return view('academico.ciclos.index');
    }

    public function create()
    {
        return view('academico.ciclos.create');
    }

    public function edit(Ciclo $ciclo)
    {
        return view('academico.ciclos.edit', compact('ciclo'));
    }

    public function activar(Ciclo $ciclo)
    {
        $ciclo->activar();
        return redirect()->route('ciclos.index')->with('success', 'Ciclo académico activado exitosamente');
    }

    public function desactivar(Ciclo $ciclo)
    {
        $ciclo->desactivar();
        return redirect()->route('ciclos.index')->with('success', 'Ciclo académico desactivado exitosamente');
    }
}
