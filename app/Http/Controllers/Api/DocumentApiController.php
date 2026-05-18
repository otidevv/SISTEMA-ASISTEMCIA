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
        
        $inscripcion = Inscripcion::where('estudiante_id', $user->id)
            ->whereIn('estado_inscripcion', ['activo', 'aprobada', 'validado'])
            ->whereHas('ciclo', function ($query) {
                $query->where('es_activo', true);
            })
            ->with(['ciclo', 'carrera', 'aula'])
            ->first();

        if (!$inscripcion) {
            $inscripcion = Inscripcion::whereHas('estudiante', function($q) use ($user) {
                $q->where('numero_documento', $user->numero_documento);
            })
            ->whereIn('estado_inscripcion', ['activo', 'aprobada', 'validado'])
            ->whereHas('ciclo', function ($query) {
                $query->where('es_activo', true);
            })
            ->with(['ciclo', 'carrera', 'aula'])
            ->first();
        }

        if (!$inscripcion) {
            $inscripcion = Inscripcion::where('estudiante_id', $user->id)
                ->with(['ciclo', 'carrera', 'aula'])
                ->latest()
                ->first();
        }

        if (!$inscripcion) {
            return $this->sendError('No se encontró una inscripción activa para generar el carnet.', [], 404);
        }

        return $this->sendResponse([
            'nombre_completo' => $user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno,
            'numero_documento' => $user->numero_documento,
            'carrera' => $inscripcion->carrera->nombre ?? 'N/A',
            'ciclo' => $inscripcion->ciclo->nombre ?? 'N/A',
            'aula' => $inscripcion->aula->nombre ?? 'N/A',
            'qr_code' => $user->numero_documento, // El DNI es el estándar para el escáner
            'photo_url' => $user->foto_perfil ? asset(Storage::url($user->foto_perfil)) : null,
            'color_primario' => '#003366', // Azul UNAMAD
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
