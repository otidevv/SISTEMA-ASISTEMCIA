<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ciclo;
use App\Models\Carrera;

class FilterController extends Controller
{
    public function getCiclos(Request $request)
    {
        $search = $request->input('q');

        $query = Ciclo::orderBy('fecha_inicio', 'desc');

        if ($search) {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        $ciclos = $query->get()->map(function($ciclo) {
            return ['id' => $ciclo->id, 'text' => $ciclo->nombre];
        });

        return response()->json($ciclos);
    }

    public function getCarreras(Request $request)
    {
        $search = $request->input('q');

        $query = Carrera::where('estado', true)->orderBy('nombre', 'asc');

        if ($search) {
            $query->where('nombre', 'like', '%' . $search . '%');
        }

        $carreras = $query->get()->map(function($carrera) {
            return ['id' => $carrera->id, 'text' => $carrera->nombre];
        });

        return response()->json($carreras);
    }
}
