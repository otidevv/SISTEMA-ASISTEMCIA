<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagoDocenteController extends Controller
{
    public function index()
    {
        // Aquí puedes obtener la lista de pagos, por ahora retorna solo la vista
        return view('pagos-docentes.index');
    }

    public function create()
    {
        // Mostrar formulario de nuevo pago
        return view('pagos-docentes.create');
    }

    public function store(Request $request)
    {
        // Aquí registrarías el nuevo pago en la base de datos
        // Por ahora solo puedes hacer un dump
        // dd($request->all());

        return redirect()->route('pagos-docentes.index')->with('success', 'Pago registrado correctamente.');
    }

    public function edit($id)
    {
        // Aquí cargarías el pago para editarlo
        // $pago = PagoDocente::findOrFail($id);

        return view('pagos-docentes.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Aquí actualizarías el pago con los nuevos datos
        return redirect()->route('pagos-docentes.index')->with('success', 'Pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        // Aquí eliminarías el pago
        return redirect()->route('pagos-docentes.index')->with('success', 'Pago eliminado correctamente.');
    }
}
