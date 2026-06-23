<?php

namespace App\Http\Controllers;

use App\Models\TusneConcepto;
use App\Models\TusneDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TusneController extends Controller
{
    public function index()
    {
        $conceptos = TusneConcepto::orderBy('categoria')->orderBy('codigo')->paginate(20);
        $documentos = TusneDocumento::latest()->get();
        return view('tusne.index', compact('conceptos', 'documentos'));
    }

    /** Subir el TUSNE oficial en PDF (sustento legal). */
    public function subirDocumento(Request $request)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf|max:20480',
            'anio' => 'nullable|string|max:10',
        ]);

        $path = $request->file('documento')->store('tusne', 'public');

        TusneDocumento::create([
            'anio' => $request->input('anio'),
            'nombre_original' => $request->file('documento')->getClientOriginalName(),
            'path' => $path,
            'vigente' => true,
        ]);

        return back()->with('success', 'Documento TUSNE (PDF) subido como sustento.');
    }

    public function eliminarDocumento(TusneDocumento $documento)
    {
        Storage::disk('public')->delete($documento->path);
        $documento->delete();
        return back()->with('success', 'Documento TUSNE eliminado.');
    }

    public function create()
    {
        return view('tusne.form', ['concepto' => new TusneConcepto()]);
    }

    public function store(Request $request)
    {
        TusneConcepto::create($this->validar($request));
        return redirect()->route('tusne.index')->with('success', 'Concepto TUSNE creado.');
    }

    public function edit(TusneConcepto $tusne)
    {
        return view('tusne.form', ['concepto' => $tusne]);
    }

    public function update(Request $request, TusneConcepto $tusne)
    {
        $tusne->update($this->validar($request, $tusne->id));
        return redirect()->route('tusne.index')->with('success', 'Concepto TUSNE actualizado.');
    }

    public function destroy(TusneConcepto $tusne)
    {
        $tusne->delete();
        return back()->with('success', 'Concepto TUSNE eliminado.');
    }

    private function validar(Request $request, $id = null): array
    {
        $unique = 'unique:tusne_conceptos,codigo' . ($id ? ',' . $id : '');

        $data = $request->validate([
            'codigo' => 'required|string|max:50|' . $unique,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'costo' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:50',
            'anio' => 'nullable|string|max:10',
        ]);

        $data['requiere_pago'] = $request->boolean('requiere_pago');
        $data['activo'] = $request->boolean('activo');

        return $data;
    }
}
