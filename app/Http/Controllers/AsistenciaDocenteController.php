<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsistenciaDocente;
use App\Models\User;
use App\Models\HorarioDocente;
use Carbon\Carbon;

class AsistenciaDocenteController extends Controller
{
    public function index(Request $request)
    {
        $query = AsistenciaDocente::with(['docente', 'horario.curso']);

        // Filtro por fechas
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha_hora', '>=', $request->fecha_inicio . ' 00:00:00');
        }

        if ($request->filled('fecha_fin')) {
            $query->where('fecha_hora', '<=', $request->fecha_fin . ' 23:59:59');
        }

        // Filtro por documento
        if ($request->filled('documento')) {
            $query->whereHas('docente', function ($q) use ($request) {
                $q->where('documento', 'like', '%' . $request->documento . '%');
            });
        }

        $asistencias = $query->orderByDesc('fecha_hora')->paginate(10);

        return view('asistencia-docente.index', compact('asistencias'));
    }

    public function create()
    {
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'profesor'); // Solo docentes
       })->get();

        return view('asistencia-docente.create', compact('docentes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'fecha_hora' => 'required|date',
            'estado' => 'required|in:entrada,salida',
            'tipo_verificacion' => 'nullable|string',
            'terminal_id' => 'nullable|string',
            'codigo_trabajo' => 'nullable|string',
        ]);

        $fecha = Carbon::parse($request->fecha_hora);
        $diaSemana = strtolower($fecha->dayName);
        $hora = $fecha->format('H:i:s');

        $horario = HorarioDocente::where('docente_id', $request->docente_id)
            ->where('dia_semana', $diaSemana)
            ->whereTime('hora_inicio', '<=', $hora)
            ->whereTime('hora_fin', '>=', $hora)
            ->first();

        AsistenciaDocente::create([
            'docente_id' => $request->docente_id,
            'fecha_hora' => $request->fecha_hora,
            'estado' => $request->estado,
            'tipo_verificacion' => $request->tipo_verificacion,
            'terminal_id' => $request->terminal_id,
            'codigo_trabajo' => $request->codigo_trabajo,
            'horario_id' => $horario?->id,
            'curso_id' => $horario?->curso_id,
            'tema_desarrollado' => null,
        ]);

        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia registrada correctamente.');
    }

    public function destroy($id)
    {
        $asistencia = AsistenciaDocente::findOrFail($id);
        $asistencia->delete();

        return redirect()->route('asistencia-docente.index')->with('success', 'Asistencia eliminada.');
    }
}
