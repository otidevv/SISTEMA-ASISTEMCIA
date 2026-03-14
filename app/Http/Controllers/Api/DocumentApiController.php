<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Carnet;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Storage;

class DocumentApiController extends BaseController
{
    /**
     * Get the student's digital identification card
     */
    public function getMyCarnet(Request $request)
    {
        $user = $request->user();
        
        $carnet = Carnet::where('estudiante_id', $user->id)
            ->whereHas('ciclo', fn($q) => $q->where('es_activo', true))
            ->first();

        if (!$carnet) {
            return $this->sendError('No se encontró carnet activo para el estudiante.', [], 404);
        }

        return $this->sendResponse([
            'id' => $carnet->id,
            'qr_code' => $carnet->codigo_qr,
            'status' => $carnet->entregado ? 'entregado' : ($carnet->impreso ? 'impreso' : 'pendiente'),
            'photo_url' => asset(Storage::url('carnets/' . $user->numero_documento . '.jpg')),
        ], 'Carnet recuperado con éxito.');
    }

    /**
     * Get student certificates (Enrollment, etc.)
     */
    public function getMyCertificates(Request $request)
    {
        $user = $request->user();
        // Dynamic generation logic or retrieval from storage
        return $this->sendResponse([], 'No hay certificados disponibles actualmente.');
    }
}
