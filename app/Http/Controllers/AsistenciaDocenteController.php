<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsistenciaDocente;

class AsistenciaDocenteController extends Controller
{
    public function index()
    {
        $asistencias = AsistenciaDocente::with(['docente', 'horario.curso', 'horario.ciclo'])
            ->orderByDesc('fecha')
            ->paginate(10);

        return view('asistencia-docente.index', compact('asistencias'));
    }

    public function create()
    {
        return view('asistencia-docente.create');
    }

    public function store(Request $request)
    {
        // Guardar nueva asistencia (esto se puede completar más adelante)
        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia registrada.');
    }

    public function destroy($id)
    {
        // Eliminar registro de asistencia
        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia eliminada.');
    }
}
