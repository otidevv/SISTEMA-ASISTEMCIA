<?php

namespace App\Http\Controllers;

use App\Models\PagoDocente;
use App\Models\Ciclo;
use App\Models\User;
use Illuminate\Http\Request;

class PagoDocenteController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener todos los ciclos para el selector
        $ciclos = Ciclo::orderBy('nombre', 'desc')->get();

        // 2. Determinar el ciclo a mostrar
        $cicloSeleccionadoId = $request->input('ciclo_id');
        $cicloSeleccionado = null;
        
        if ($cicloSeleccionadoId === 'all') {
            $cicloSeleccionado = null;
        } elseif ($cicloSeleccionadoId === 'none') {
            $cicloSeleccionado = 'none';
        } elseif ($cicloSeleccionadoId) {
            $cicloSeleccionado = $ciclos->find($cicloSeleccionadoId);
        } else {
            // Comportamiento por defecto: mostrar el ciclo activo más reciente
            $cicloSeleccionado = Ciclo::where('es_activo', true)->orderBy('fecha_inicio', 'desc')->first();
            
            // Si no hay activos pero hay pagos sin ciclo, quizás convenga mostrarlos, 
            // pero mantendremos la lógica de mostrar el primer ciclo si existe.
            if (!$cicloSeleccionado && $ciclos->isNotEmpty()) {
                $cicloSeleccionado = $ciclos->first();
            }
        }

        // 3. Construir la consulta de pagos
        $query = PagoDocente::with('docente');

        // 4. Filtrar por el ciclo seleccionado
        if ($cicloSeleccionado === 'none') {
            $query->whereNull('ciclo_id');
        } elseif ($cicloSeleccionado instanceof Ciclo) {
            $query->where('ciclo_id', $cicloSeleccionado->id);
        }
        // Si es null o 'all', no filtramos por ciclo (muestra todo)

        // --- NUEVO: Estadísticas para el Dashboard ---
        $stats = [
            'total' => (clone $query)->count(),
            'activos' => (clone $query)->whereNull('fecha_fin')->count(),
            'promedio' => (clone $query)->avg('tarifa_por_hora') ?? 0,
        ];

        $pagos = $query->latest()->paginate(10);

    // --- NUEVO: Datos para Modales (Create/Edit) ---
    $docentes = \App\Models\User::whereHas('roles', function($q) {
        $q->where('nombre', 'profesor');
    })->get();

    $cicloActivo = $ciclos->where('es_activo', true)->first();

    // 5. Pasar los datos a la vista
    return view('pagos-docentes.index', compact('pagos', 'ciclos', 'cicloSeleccionado', 'stats', 'docentes', 'cicloActivo'));
    }

    public function create()
    {
        // CORRECCIÓN 2: Usar User directamente (ya está importado)
        $docentes = User::whereHas('roles', function($q){
            $q->where('nombre', 'profesor');
        })->get();

        // Traer todos los ciclos para mayor flexibilidad
        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        
        // Pero marcar cuál es el preferido (el más reciente activo)
        $cicloActivo = $ciclos->where('es_activo', true)->first();

        return view('pagos-docentes.create', compact('docentes', 'ciclos', 'cicloActivo'));
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
            'ciclo_id' => $request->ciclo_id,
            'tarifa_por_hora' => $request->tarifa_por_hora,
            'fecha_inicio' => $ciclo->fecha_inicio,
            'fecha_fin' => $ciclo->fecha_fin,
        ]);

        return redirect()->route('pagos-docentes.index')->with('success', 'Pago registrado correctamente.');
    }

    public function edit($id)
    {
        $pago = PagoDocente::findOrFail($id);
        
        $docentes = User::whereHas('roles', function($q){
            $q->where('nombre', 'profesor');
        })->get();

        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();

        return view('pagos-docentes.edit', compact('pago', 'docentes', 'ciclos'));
    }

    public function update(Request $request, $id)
    {
        $pago = PagoDocente::findOrFail($id);
        
        $request->validate([
            'docente_id' => 'required|exists:users,id',
            'ciclo_id' => 'required|exists:ciclos,id',
            'tarifa_por_hora' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        // CORRECCIÓN 4: Usar solo los campos necesarios en lugar de $request->all()
        $pago->update([
            'docente_id' => $request->docente_id,
            'ciclo_id' => $request->ciclo_id,
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