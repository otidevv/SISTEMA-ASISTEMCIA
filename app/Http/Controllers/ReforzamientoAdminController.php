<?php

namespace App\Http\Controllers;

use App\Models\InscripcionReforzamiento;
use App\Models\Ciclo;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ReforzamientoAdminController extends Controller
{
    public function index()
    {
        $ciclos = Ciclo::where('nombre', 'like', '%Reforzamiento%')->get();
        return view('admin.reforzamiento.index', compact('ciclos'));
    }

    public function getData(Request $request)
    {
        $baseQuery = InscripcionReforzamiento::query();
        
        if ($request->ciclo_id) {
            $baseQuery->where('ciclo_id', $request->ciclo_id);
        }

        // Obtener conteos para las tarjetas
        $counts = [
            'total' => (clone $baseQuery)->count(),
            'pendiente' => (clone $baseQuery)->where('estado_inscripcion', 'pendiente')->count(),
            'aprobado' => (clone $baseQuery)->where('estado_inscripcion', 'validado')->count(),
        ];

        $query = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'pagos']);

        if ($request->ciclo_id) {
            $query->where('ciclo_id', $request->ciclo_id);
        }

        return DataTables::of($query)
            ->addColumn('estudiante_nombre', function($row) {
                return '<div class="d-flex align-items-center">
                            <div class="avatar-sm me-3 mr-2">
                                <span class="avatar-title bg-soft-primary text-primary rounded-circle font-size-14" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                    ' . substr($row->estudiante->nombre, 0, 1) . '
                                </span>
                            </div>
                            <div>
                                <h5 class="fs-14 my-1"><a href="javascript:void(0);" class="text-reset">' . $row->estudiante->nombre_completo . '</a></h5>
                                <p class="text-muted mb-0 font-size-12"><i class="mdi mdi-calendar-clock mr-1"></i> ' . $row->created_at->format('d/m/Y H:i') . '</p>
                            </div>
                        </div>';
            })
            ->addColumn('dni', function($row) {
                return '<span class="fw-bold text-primary font-size-13">' . $row->estudiante->numero_documento . '</span>';
            })
            ->addColumn('grado_turno', function($row) {
                return '<div class="text-dark font-weight-bold">' . $row->grado . '</div>
                        <div class="text-muted small">' . strtoupper($row->turno) . '</div>';
            })
            ->addColumn('estado', function($row) {
                $status = strtoupper($row->estado_inscripcion);
                $class = $status === 'VALIDADO' ? 'success' : ($status === 'PENDIENTE' ? 'warning' : 'danger');
                $icon = $status === 'VALIDADO' ? 'check-decagram' : ($status === 'PENDIENTE' ? 'clock-fast' : 'alert-circle');
                return '<div class="text-center">
                            <span class="badge-reforzamiento badge-reforzamiento-'.$class.'">
                                <i class="mdi mdi-'.$icon.' mr-1"></i>' . $status . '
                            </span>
                        </div>';
            })
            ->addColumn('semaforo_pagos', function($row) {
                $pago = $row->pagos()->where('estado_pago', 'aprobado')->first();
                $class = $pago ? 'paid' : 'unpaid';
                $icon = $pago ? 'check-circle' : 'close-circle';
                $text = $pago ? 'PAGO OK' : 'PENDIENTE';
                return '<div class="text-center">
                            <span class="payment-chip payment-chip-'.$class.' shadow-none">
                                <i class="mdi mdi-'.$icon.' mr-1"></i>'.$text.'
                            </span>
                        </div>';
            })
            ->addColumn('acciones', function($row) {
                $btn = '<div class="text-center">
                            <div class="d-flex justify-content-center">';
                
                $btn .= '<button type="button" class="btn-action-reforzamiento" onclick="viewDetails(' . $row->id . ')" title="Ver Expediente">
                            <i class="mdi mdi-eye text-primary"></i>
                         </button>';

                if ($row->estado_inscripcion === 'pendiente') {
                    $btn .= '<button type="button" class="btn-action-reforzamiento" onclick="approve(' . $row->id . ')" title="Aprobar Inscripción">
                                <i class="mdi mdi-check-bold text-success"></i>
                             </button>';
                }

                if ($row->estudiante && $row->estudiante->telefono) {
                    $btn .= '<a href="https://wa.me/51' . $row->estudiante->telefono . '" target="_blank" class="btn-action-reforzamiento" title="WhatsApp">
                                <i class="mdi mdi-whatsapp text-success"></i>
                             </a>';
                }

                $btn .= '<button type="button" class="btn-action-reforzamiento" onclick="deleteRecord(' . $row->id . ')" title="Eliminar">
                            <i class="mdi mdi-trash-can-outline text-danger"></i>
                         </button>';

                $btn .= '   </div>
                        </div>';
                return $btn;
            })
            ->with('counts', $counts)
            ->rawColumns(['estudiante_nombre', 'dni', 'grado_turno', 'estado', 'semaforo_pagos', 'acciones'])
            ->make(true);
    }

    public function show($id)
    {
        $inscripcion = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'apoderados', 'pagos'])->findOrFail($id);
        return response()->json($inscripcion);
    }

    public function updateStatus(Request $request, $id)
    {
        $inscripcion = InscripcionReforzamiento::findOrFail($id);
        $inscripcion->estado_inscripcion = $request->estado; // ej: 'validado'
        
        if ($request->estado == 'validado' || $request->estado == 'VALIDADO') {
            $inscripcion->estado_inscripcion = 'validado';
            $pago = $inscripcion->pagos()->first();
            if ($pago) {
                $pago->estado_pago = 'aprobado';
                $pago->save();
            }
        }
        $inscripcion->save();

        return response()->json(['message' => 'Inscripción actualizada correctamente.']);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $inscripcion = InscripcionReforzamiento::findOrFail($id);
            // Eliminar relaciones primero
            $inscripcion->apoderados()->delete();
            $inscripcion->pagos()->delete();
            $inscripcion->delete();
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
