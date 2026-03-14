<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\User;

class PaymentApiController extends BaseController
{
    /**
     * Validate a student's payment status by DNI
     */
    public function validateByDni($dni)
    {
        $user = User::where('numero_documento', $dni)->first();
        
        if (!$user) {
            return $this->sendError('Estudiante no encontrado.', [], 404);
        }

        $inscripcion = Inscripcion::where('estudiante_id', $user->id)
            ->whereHas('ciclo', fn($q) => $q->where('es_activo', true))
            ->first();

        $data = [
            'dni' => $dni,
            'has_payment' => $inscripcion ? true : false,
            'details' => $inscripcion ? 'Pago verificado correctamente.' : 'No se registra pago activo para este ciclo.'
        ];

        return $this->sendResponse($data, 'Validación de pago completada.');
    }
}
