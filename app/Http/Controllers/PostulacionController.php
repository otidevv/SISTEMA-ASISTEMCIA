<?php

namespace App\Http\Controllers;

use App\Models\Postulacion;
use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostulacionController extends Controller
{
    /**
     * Mostrar la vista principal de postulaciones
     */
    public function index()
    {
        // Verificar permisos
        if (!Auth::user()->hasPermission('postulaciones.view')) {
            abort(403, 'No tienes permisos para ver postulaciones');
        }

        $ciclos = Ciclo::orderBy('fecha_inicio', 'desc')->get();
        $carreras = Carrera::where('estado', true)->orderBy('nombre')->get();
        $turnos = Turno::where('estado', true)->orderBy('nombre')->get();

        return view('postulaciones.index', compact('ciclos', 'carreras', 'turnos'));
    }

    /**
     * Obtener lista de postulaciones en formato JSON para DataTables
     */
    public function listar(Request $request)
    {
        if (!Auth::user()->hasPermission('postulaciones.view')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $query = Postulacion::with(['estudiante', 'ciclo', 'carrera', 'turno']);

            // Filtros opcionales
            if ($request->ciclo_id) {
                $query->where('ciclo_id', $request->ciclo_id);
            }
            if ($request->estado) {
                $query->where('estado', $request->estado);
            }
            if ($request->carrera_id) {
                $query->where('carrera_id', $request->carrera_id);
            }

            $postulaciones = $query->orderBy('created_at', 'desc')->get();

            $data = $postulaciones->map(function ($postulacion) {
                $actions = $this->generarAcciones($postulacion);
                
                return [
                    'id' => $postulacion->id,
                    'codigo' => $postulacion->codigo_postulacion,
                    'estudiante' => $postulacion->estudiante->nombre . ' ' . 
                                   $postulacion->estudiante->apellido_paterno . ' ' . 
                                   $postulacion->estudiante->apellido_materno,
                    'dni' => $postulacion->estudiante->numero_documento ?? 'N/A',
                    'email' => $postulacion->estudiante->email,
                    'ciclo' => $postulacion->ciclo->nombre,
                    'carrera' => $postulacion->carrera->nombre,
                    'turno' => $postulacion->turno->nombre,
                    'tipo_inscripcion' => $postulacion->tipo_inscripcion,
                    'fecha_postulacion' => $postulacion->fecha_postulacion->format('d/m/Y H:i'),
                    'estado' => $postulacion->estado,
                    'documentos_verificados' => $postulacion->documentos_verificados,
                    'pago_verificado' => $postulacion->pago_verificado,
                    'numero_recibo' => $postulacion->numero_recibo,
                    'monto_total' => $postulacion->monto_total_pagado,
                    'actions' => $actions
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar postulaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de una postulación
     */
    public function show($id)
    {
        if (!Auth::user()->hasPermission('postulaciones.show')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $postulacion = Postulacion::with(['estudiante', 'ciclo', 'carrera', 'turno', 'centroEducativo'])
                ->findOrFail($id);

            // Preparar información de documentos
            $documentos = [
                'voucher_pago' => [
                    'nombre' => 'Voucher de Pago',
                    'existe' => !empty($postulacion->voucher_pago_path),
                    'url' => $postulacion->voucher_pago_path ? Storage::url($postulacion->voucher_pago_path) : null
                ],
                'certificado_estudios' => [
                    'nombre' => 'Certificado de Estudios',
                    'existe' => !empty($postulacion->certificado_estudios_path),
                    'url' => $postulacion->certificado_estudios_path ? Storage::url($postulacion->certificado_estudios_path) : null
                ],
                'carta_compromiso' => [
                    'nombre' => 'Carta de Compromiso',
                    'existe' => !empty($postulacion->carta_compromiso_path),
                    'url' => $postulacion->carta_compromiso_path ? Storage::url($postulacion->carta_compromiso_path) : null
                ],
                'constancia_estudios' => [
                    'nombre' => 'Constancia de Estudios',
                    'existe' => !empty($postulacion->constancia_estudios_path),
                    'url' => $postulacion->constancia_estudios_path ? Storage::url($postulacion->constancia_estudios_path) : null
                ],
                'dni' => [
                    'nombre' => 'DNI',
                    'existe' => !empty($postulacion->dni_path),
                    'url' => $postulacion->dni_path ? Storage::url($postulacion->dni_path) : null
                ],
                'foto_carnet' => [
                    'nombre' => 'Foto Carnet',
                    'existe' => !empty($postulacion->foto_carnet_path),
                    'url' => $postulacion->foto_carnet_path ? Storage::url($postulacion->foto_carnet_path) : null
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'postulacion' => $postulacion,
                    'documentos' => $documentos
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener postulación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar documentos
     */
    public function verificarDocumentos(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.verify_documents')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $postulacion = Postulacion::findOrFail($id);
            $postulacion->documentos_verificados = $request->verificado ? 1 : 0;
            $postulacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Documentos ' . ($request->verificado ? 'verificados' : 'marcados como no verificados')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar pago
     */
    public function verificarPago(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.verify_payment')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $postulacion = Postulacion::findOrFail($id);
            $postulacion->pago_verificado = $request->verificado ? 1 : 0;
            $postulacion->save();

            return response()->json([
                'success' => true,
                'message' => 'Pago ' . ($request->verificado ? 'verificado' : 'marcado como no verificado')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar postulación
     */
    public function rechazar(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.reject')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'motivo' => 'required|string|min:10'
        ]);

        try {
            $postulacion = Postulacion::findOrFail($id);
            $postulacion->rechazar(Auth::id(), $request->motivo);

            return response()->json([
                'success' => true,
                'message' => 'Postulación rechazada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Observar postulación
     */
    public function observar(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('postulaciones.observe')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        $request->validate([
            'observaciones' => 'required|string|min:10'
        ]);

        try {
            $postulacion = Postulacion::findOrFail($id);
            $postulacion->observar(Auth::id(), $request->observaciones);

            return response()->json([
                'success' => true,
                'message' => 'Postulación marcada con observaciones'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar postulación
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasPermission('postulaciones.delete')) {
            return response()->json(['error' => 'Sin permisos'], 403);
        }

        try {
            $postulacion = Postulacion::findOrFail($id);
            
            // Eliminar archivos asociados
            if ($postulacion->voucher_pago_path) Storage::delete($postulacion->voucher_pago_path);
            if ($postulacion->certificado_estudios_path) Storage::delete($postulacion->certificado_estudios_path);
            if ($postulacion->carta_compromiso_path) Storage::delete($postulacion->carta_compromiso_path);
            if ($postulacion->constancia_estudios_path) Storage::delete($postulacion->constancia_estudios_path);
            if ($postulacion->dni_path) Storage::delete($postulacion->dni_path);
            if ($postulacion->foto_carnet_path) Storage::delete($postulacion->foto_carnet_path);
            
            $postulacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Postulación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar acciones disponibles según permisos
     */
    private function generarAcciones($postulacion)
    {
        $actions = [];
        $user = Auth::user();

        // Ver detalle
        if ($user->hasPermission('postulaciones.show')) {
            $actions[] = '<button class="btn btn-sm btn-info view-postulacion" data-id="' . $postulacion->id . '" title="Ver detalle">
                <i class="uil uil-eye"></i>
            </button>';
        }

        // Verificar documentos
        if ($user->hasPermission('postulaciones.verify_documents') && $postulacion->estado == 'pendiente') {
            $checkIcon = $postulacion->documentos_verificados ? 'uil-check-circle' : 'uil-file-check-alt';
            $checkClass = $postulacion->documentos_verificados ? 'btn-success' : 'btn-warning';
            $actions[] = '<button class="btn btn-sm ' . $checkClass . ' verify-docs" data-id="' . $postulacion->id . '" 
                data-verified="' . $postulacion->documentos_verificados . '" title="Verificar documentos">
                <i class="uil ' . $checkIcon . '"></i>
            </button>';
        }

        // Verificar pago
        if ($user->hasPermission('postulaciones.verify_payment') && $postulacion->estado == 'pendiente') {
            $payIcon = $postulacion->pago_verificado ? 'uil-money-bill' : 'uil-money-withdraw';
            $payClass = $postulacion->pago_verificado ? 'btn-success' : 'btn-warning';
            $actions[] = '<button class="btn btn-sm ' . $payClass . ' verify-payment" data-id="' . $postulacion->id . '" 
                data-verified="' . $postulacion->pago_verificado . '" title="Verificar pago">
                <i class="uil ' . $payIcon . '"></i>
            </button>';
        }

        // Observar
        if ($user->hasPermission('postulaciones.observe') && $postulacion->estado == 'pendiente') {
            $actions[] = '<button class="btn btn-sm btn-warning observe-postulacion" data-id="' . $postulacion->id . '" title="Observar">
                <i class="uil uil-comment-exclamation"></i>
            </button>';
        }

        // Rechazar
        if ($user->hasPermission('postulaciones.reject') && $postulacion->estado == 'pendiente') {
            $actions[] = '<button class="btn btn-sm btn-danger reject-postulacion" data-id="' . $postulacion->id . '" title="Rechazar">
                <i class="uil uil-times-circle"></i>
            </button>';
        }

        // Aprobar (placeholder por ahora)
        if ($user->hasPermission('postulaciones.approve') && 
            $postulacion->estado == 'pendiente' && 
            $postulacion->documentos_verificados && 
            $postulacion->pago_verificado) {
            $actions[] = '<button class="btn btn-sm btn-success approve-postulacion" data-id="' . $postulacion->id . '" title="Aprobar">
                <i class="uil uil-check-circle"></i>
            </button>';
        }

        // Eliminar
        if ($user->hasPermission('postulaciones.delete')) {
            $actions[] = '<button class="btn btn-sm btn-danger delete-postulacion" data-id="' . $postulacion->id . '" title="Eliminar">
                <i class="uil uil-trash-alt"></i>
            </button>';
        }

        return '<div class="btn-group">' . implode(' ', $actions) . '</div>';
    }
}