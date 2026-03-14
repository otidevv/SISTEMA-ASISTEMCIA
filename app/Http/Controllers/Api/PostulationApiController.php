<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Postulacion;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class PostulationApiController extends BaseController
{
    /**
     * Store a new postulation from mobile app
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_documento' => 'required|exists:users,numero_documento',
            'email' => 'required|email',
            'carrera_id' => 'required|exists:carreras,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error de validación.', $validator->errors());
        }

        // Logic to create or update postulation
        return $this->sendResponse([], 'Postulación registrada correctamente.');
    }

    /**
     * Get the current status of a postulation
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $postulation = Postulacion::where('estudiante_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$postulation) {
            return $this->sendError('No se encontró postulación.', [], 404);
        }

        return $this->sendResponse($postulation, 'Estado de postulación recuperado.');
    }
}
