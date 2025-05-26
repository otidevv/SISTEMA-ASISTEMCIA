<?php
// app/Http/Controllers/InscripcionController.php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    public function index()
    {
        return view('inscripciones.index');
    }

    public function create()
    {
        return view('inscripciones.create');
    }

    public function edit(Inscripcion $inscripcion)
    {
        return view('inscripciones.edit', compact('inscripcion'));
    }

    public function reportes()
    {
        return view('inscripciones.reportes');
    }
}
