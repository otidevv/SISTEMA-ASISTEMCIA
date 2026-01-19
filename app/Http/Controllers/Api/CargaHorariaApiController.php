<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CargaHorariaController;
use App\Models\Ciclo;
use App\Models\User;
use Illuminate\Http\Request;

class CargaHorariaApiController extends Controller
{
    protected $mainController;

    public function __construct(CargaHorariaController $mainController)
    {
        $this->mainController = $mainController;
    }

    public function calcular($docenteId, $cicloId)
    {
        try {
            $data = $this->mainController->obtenerDatosCargaHoraria($docenteId, $cicloId);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular carga horaria: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarDocentes($cicloId)
    {
        try {
            $docentes = User::whereHas('roles', function($q){
                $q->where('nombre', 'profesor');
            })
            ->whereHas('horariosDocente', function($q) use ($cicloId) {
                $q->where('ciclo_id', $cicloId);
            })
            ->orderBy('nombre')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $docentes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar docentes: ' . $e->getMessage()
            ], 500);
        }
    }
}
