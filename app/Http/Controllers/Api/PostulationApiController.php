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

    /**
     * ADMIN: List all postulations for the active cycle
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('postulaciones.view')) {
            return $this->sendError('Sin permisos.', [], 403);
        }

        $cicloId = $request->input('ciclo_id');
        if (empty($cicloId)) {
            $cicloActivo = \App\Models\Ciclo::where('es_activo', true)->where('programa_id', 1)->first();
            $cicloId = $cicloActivo ? $cicloActivo->id : null;
        }

        $query = Postulacion::with(['estudiante', 'carrera', 'turno'])
            ->orderBy('created_at', 'desc');

        if ($cicloId) {
            $query->where('ciclo_id', $cicloId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('estudiante', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        $postulaciones = $query->paginate(20);

        return $this->sendResponse($postulaciones, 'Postulaciones recuperadas.');
    }

    /**
     * ADMIN: Show postulation detail
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->hasPermission('postulaciones.show')) {
            return $this->sendError('Sin permisos.', [], 403);
        }

        $postulacion = Postulacion::with([
            'estudiante.parentescos.padre',
            'ciclo',
            'carrera',
            'turno',
            'centroEducativo'
        ])->findOrFail($id);

        // Map documents
        $documentos = [
            'dni' => ['existe' => !empty($postulacion->dni_path), 'url' => $postulacion->dni_path ? \Illuminate\Support\Facades\Storage::url($postulacion->dni_path) : null],
            'certificado' => ['existe' => !empty($postulacion->certificado_estudios_path), 'url' => $postulacion->certificado_estudios_path ? \Illuminate\Support\Facades\Storage::url($postulacion->certificado_estudios_path) : null],
            'foto' => ['existe' => !empty($postulacion->foto_path), 'url' => $postulacion->foto_path ? \Illuminate\Support\Facades\Storage::url($postulacion->foto_path) : null],
            'voucher' => ['existe' => !empty($postulacion->voucher_path), 'url' => $postulacion->voucher_path ? \Illuminate\Support\Facades\Storage::url($postulacion->voucher_path) : null],
        ];

        return $this->sendResponse([
            'postulacion' => $postulacion,
            'documentos' => $documentos,
            'foto_perfil_url' => $postulacion->estudiante->foto_perfil ? \Illuminate\Support\Facades\Storage::url($postulacion->estudiante->foto_perfil) : null,
        ], 'Detalle de postulación recuperado.');
    }
}
