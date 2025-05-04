<?php

namespace App\Http\Controllers;

use App\Models\Parentesco;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ParentescoController extends Controller
{
    /**
     * Display a listing of the parentescos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parentescos = Parentesco::with(['estudiante', 'padre'])->paginate(10);
        return view('parentescos.index', compact('parentescos'));
    }

    /**
     * Show the form for creating a new parentesco.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtener estudiantes (usuarios con rol de estudiante)
        $estudiantes = User::whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'estudiante');
        })->get();

        // Obtener padres (usuarios con rol de padre)
        $padres = User::whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'padre');
        })->get();

        $tiposParentesco = ['padre', 'madre', 'tutor', 'otro'];

        return view('parentescos.create', compact('estudiantes', 'padres', 'tiposParentesco'));
    }

    /**
     * Store a newly created parentesco in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'padre_id' => 'required|exists:users,id',
            'tipo_parentesco' => 'required|string|max:30',
            'acceso_portal' => 'boolean',
            'recibe_notificaciones' => 'boolean',
            'contacto_emergencia' => 'boolean',
        ]);

        // Verificar que no exista el mismo tipo de parentesco para el mismo estudiante y padre
        $existente = Parentesco::where('estudiante_id', $request->estudiante_id)
            ->where('padre_id', $request->padre_id)
            ->where('tipo_parentesco', $request->tipo_parentesco)
            ->first();

        if ($existente) {
            return redirect()->back()->with('error', 'Ya existe este tipo de parentesco para el estudiante y padre seleccionados.');
        }

        $parentesco = Parentesco::create([
            'estudiante_id' => $request->estudiante_id,
            'padre_id' => $request->padre_id,
            'tipo_parentesco' => $request->tipo_parentesco,
            'acceso_portal' => $request->has('acceso_portal'),
            'recibe_notificaciones' => $request->has('recibe_notificaciones'),
            'contacto_emergencia' => $request->has('contacto_emergencia'),
            'estado' => true,
        ]);

        return redirect()->route('parentescos.index')->with('success', 'Parentesco creado exitosamente.');
    }

    /**
     * Show the form for editing the specified parentesco.
     *
     * @param  \App\Models\Parentesco  $parentesco
     * @return \Illuminate\Http\Response
     */
    public function edit(Parentesco $parentesco)
    {
        // Obtener estudiantes (usuarios con rol de estudiante)
        $estudiantes = User::whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'estudiante');
        })->get();

        // Obtener padres (usuarios con rol de padre)
        $padres = User::whereHas('roles', function ($query) {
            $query->where('roles.nombre', 'padre');
        })->get();

        $tiposParentesco = ['padre', 'madre', 'tutor', 'otro'];

        return view('parentescos.edit', compact('parentesco', 'estudiantes', 'padres', 'tiposParentesco'));
    }

    /**
     * Update the specified parentesco in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Parentesco  $parentesco
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Parentesco $parentesco)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|exists:users,id',
            'padre_id' => 'required|exists:users,id',
            'tipo_parentesco' => 'required|string|max:30',
            'acceso_portal' => 'boolean',
            'recibe_notificaciones' => 'boolean',
            'contacto_emergencia' => 'boolean',
            'estado' => 'boolean',
        ]);

        // Verificar que no exista el mismo tipo de parentesco para el mismo estudiante y padre (excepto el actual)
        $existente = Parentesco::where('estudiante_id', $request->estudiante_id)
            ->where('padre_id', $request->padre_id)
            ->where('tipo_parentesco', $request->tipo_parentesco)
            ->where('id', '!=', $parentesco->id)
            ->first();

        if ($existente) {
            return redirect()->back()->with('error', 'Ya existe este tipo de parentesco para el estudiante y padre seleccionados.');
        }

        $parentesco->update([
            'estudiante_id' => $request->estudiante_id,
            'padre_id' => $request->padre_id,
            'tipo_parentesco' => $request->tipo_parentesco,
            'acceso_portal' => $request->has('acceso_portal'),
            'recibe_notificaciones' => $request->has('recibe_notificaciones'),
            'contacto_emergencia' => $request->has('contacto_emergencia'),
            'estado' => $request->has('estado'),
        ]);

        return redirect()->route('parentescos.index')->with('success', 'Parentesco actualizado exitosamente.');
    }

    /**
     * Remove the specified parentesco from storage.
     *
     * @param  \App\Models\Parentesco  $parentesco
     * @return \Illuminate\Http\Response
     */
    public function destroy(Parentesco $parentesco)
    {
        $parentesco->delete();
        return redirect()->route('parentescos.index')->with('success', 'Parentesco eliminado exitosamente.');
    }
}
