<?php

namespace App\Http\Controllers;

use App\Models\PagoDocente;
use App\Models\Ciclo;
use App\Models\User;
use Illuminate\Http\Request;

class PagoDocenteController extends Controller
{
    public function index()
    {
        // CORRECCIÓN 1: Agregar with('docente') para cargar la relación y mostrar nombres
        $pagos = PagoDocente::with('docente')->paginate(10);
        return view('pagos-docentes.index', compact('pagos'));
    }

    public function create()
    {
        // CORRECCIÓN 2: Usar User directamente (ya está importado)
        $docentes = User::whereHas('roles', function($q){
            $q->where('nombre', 'profesor');
        })->get();

        // Solo traer ciclos activos (es_activo = true)
        $ciclos = Ciclo::where('es_activo', true)->get();

        return view('pagos-docentes.create', compact('docentes', 'ciclos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'tarifa_por_hora' => 'required|numeric|min:0',
        ]);

        $ciclo = Ciclo::findOrFail($request->ciclo_id);

        PagoDocente::create([
            'docente_id' => $request->docente_id,
            'tarifa_por_hora' => $request->tarifa_por_hora,
            'fecha_inicio' => $ciclo->fecha_inicio,
            'fecha_fin' => $ciclo->fecha_fin,
        ]);

        return redirect()->route('pagos-docentes.index')->with('success', 'Pago registrado correctamente.');
    }

    public function edit($id)
    {
        $pago = PagoDocente::findOrFail($id);
        
        // CORRECCIÓN 3: Usar User directamente
        $docentes = User::whereHas('roles', function($q){
            $q->where('nombre', 'profesor');
        })->get();

        $ciclos = Ciclo::where('es_activo', true)->get();

        return view('pagos-docentes.edit', compact('pago', 'docentes', 'ciclos'));
    }

    public function update(Request $request, $id)
    {
        $pago = PagoDocente::findOrFail($id);
        
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'tarifa_por_hora' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        // CORRECCIÓN 4: Usar solo los campos necesarios en lugar de $request->all()
        $pago->update([
            'docente_id' => $request->docente_id,
            'tarifa_por_hora' => $request->tarifa_por_hora,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        return redirect()->route('pagos-docentes.index')->with('success', 'Pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        $pago = PagoDocente::findOrFail($id);
        
        // CORRECCIÓN 5: Agregar try-catch para manejar errores de eliminación
        try {
            $pago->delete();
            return redirect()->route('pagos-docentes.index')->with('success', 'Pago eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('pagos-docentes.index')->with('error', 'Error al eliminar el pago.');
        }
    }

    // Nuevo método para obtener tarifa por docente y ciclo
    public function obtenerTarifa($docenteId, $cicloId)
    {
        $ciclo = Ciclo::findOrFail($cicloId);

        $pago = PagoDocente::where('docente_id', $docenteId)
            ->where('fecha_inicio', '<=', $ciclo->fecha_fin)
            ->where(function ($query) use ($ciclo) {
                $query->where('fecha_fin', '>=', $ciclo->fecha_inicio)
                      ->orWhereNull('fecha_fin');
            })
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        if ($pago) {
            return response()->json(['tarifa_por_hora' => $pago->tarifa_por_hora]);
        }

        return response()->json(['tarifa_por_hora' => null]);
    }
}