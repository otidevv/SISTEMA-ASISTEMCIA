<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Estandariza las respuestas de éxito de la API.
     */
    protected function sendResponse($result, string $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ], $code);
    }

    /**
     * Estandariza las respuestas de error de la API.
     */
    protected function sendError(string $error, array $errorMessages = [], int $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Resolves the target student user. If the authenticated user is a parent/apoderado
     * and request specifies an 'estudiante_id', it returns the student user if they are linked.
     */
    protected function resolveStudentUser(\Illuminate\Http\Request $request)
    {
        $user = $request->user();
        if ($request->has('estudiante_id')) {
            $esPadre = $user->hasRole('padre') || $user->hasRole('apoderado') || $user->hasRole('Madre') || $user->hasRole('Padre');
            if ($esPadre) {
                // Verificar parentesco en la BD
                $parentescoExiste = \App\Models\Parentesco::where('padre_id', $user->id)
                    ->where('estudiante_id', $request->input('estudiante_id'))
                    ->where('estado', true)
                    ->exists();
                if ($parentescoExiste) {
                    $student = \App\Models\User::find($request->input('estudiante_id'));
                    if ($student) {
                        return $student;
                    }
                }
            }
        }
        return $user;
    }
}
