<?php
// app/Http/Controllers/InscripcionController.php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
        // Obtener datos para los filtros
        $ciclos = \App\Models\Ciclo::where('es_activo', 1)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $carreras = \App\Models\Carrera::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $turnos = \App\Models\Turno::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $aulas = \App\Models\Aula::where('estado', 1)
            ->orderBy('codigo')
            ->get();

        return view('inscripciones.reportes', compact('ciclos', 'carreras', 'turnos', 'aulas'));
    }
    // Agregar este método en la clase InscripcionController


}
