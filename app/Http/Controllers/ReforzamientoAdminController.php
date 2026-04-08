<?php

namespace App\Http\Controllers;

use App\Models\InscripcionReforzamiento;
use App\Models\Ciclo;
use App\Models\User;
use App\Services\PaymentValidationService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ReforzamientoAdminController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentValidationService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $ciclos = Ciclo::where('programa_id', 2)->orderBy('id', 'desc')->get();
        $aulas = \App\Models\Aula::where('estado', true)->get();
        return view('admin.reforzamiento.index', compact('ciclos', 'aulas'));
    }

    public function getData(Request $request)
    {
        // Consulta base para conteos y datos
        $queryBuilder = InscripcionReforzamiento::query();
        
        if ($request->filled('ciclo_id')) {
            $queryBuilder->where('ciclo_id', $request->ciclo_id);
        }

        // Obtener conteos para las tarjetas (usando el filtro aplicado)
        $counts = [
            'total' => (clone $queryBuilder)->count(),
            'pendiente' => (clone $queryBuilder)->where('estado_inscripcion', 'pendiente')->count(),
            'aprobado' => (clone $queryBuilder)->where('estado_inscripcion', 'validado')->count(),
        ];

        // Preparar consulta para DataTables con relaciones - Se añade 'apoderados'
        $query = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'pagos', 'aula', 'apoderados']);
        
        if ($request->filled('ciclo_id')) {
            $query->where('ciclo_id', $request->ciclo_id);
        }

        return DataTables::of($query)
            ->addColumn('estudiante_nombre', function($row) {
                $foto = $row->estudiante->foto_perfil;
                $avatar_url = $foto ? asset('storage/' . $foto) : 'https://ui-avatars.com/api/?name=' . urlencode($row->estudiante->nombre) . '&background=random&color=fff';
                
                $avatar = '<img src="' . $avatar_url . '" alt="" class="avatar-sm rounded-circle me-3 mr-2" style="width:36px; height:36px; object-fit:cover;">';
                
                return '<div class="d-flex align-items-center">
                            ' . $avatar . '
                            <div>
                                <h5 class="fs-14 my-1"><a href="javascript:void(0);" class="text-reset">' . $row->estudiante->nombre_completo . '</a></h5>
                                <p class="text-muted mb-0 font-size-11" style="line-height:1;"><i class="mdi mdi-calendar-clock mr-1"></i> ' . $row->created_at->format('d/m/Y H:i') . '</p>
                            </div>
                        </div>';
            })
            ->addColumn('dni', function($row) {
                return '<span class="fw-bold text-primary font-size-13">' . $row->estudiante->numero_documento . '</span>';
            })
            ->addColumn('grado_turno', function($row) {
                $aulaInfo = $row->aula ? '<div class="text-success small fw-bold">AULA: ' . $row->aula->nombre . '</div>' : '';
                return '<div class="text-dark font-weight-bold">' . $row->grado . '</div>
                        <div class="text-muted small">' . strtoupper($row->turno) . '</div>' . $aulaInfo;
            })
            ->addColumn('estado', function($row) {
                $status = strtoupper($row->estado_inscripcion);
                $class = $status === 'VALIDADO' ? 'success' : ($status === 'PENDIENTE' ? 'warning' : 'danger');
                $icon = $status === 'VALIDADO' ? 'check-decagram' : ($status === 'PENDIENTE' ? 'clock-fast' : 'alert-circle');
                return '<div class="text-center">
                            <span class="badge-reforzamiento badge-reforzamiento-'.$class.'">
                                <i class="mdi mdi-'.$icon.' mr-1"></i>' . $status . '
                            </span>
                             ' . ($row->nro_constancia ? '<div class="mt-1 small text-muted">N° ' . $row->nro_constancia . '</div>' : '') . '
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
                
                // Botón Ver Detalle (Ojito)
                $btn .= '<a href="javascript:void(0);" onclick="viewDetails(' . $row->id . ')" class="btn-action-reforzamiento" title="Ver Expediente">
                            <i class="mdi mdi-eye text-primary"></i>
                         </a>';

                // Botón Editar (Lápiz) - Ahora para TODOS
                $btn .= '<a href="javascript:void(0);" onclick="editInscripcion(' . $row->id . ')" class="btn-action-reforzamiento" title="Editar Expediente">
                            <i class="mdi mdi-pencil text-info"></i>
                         </a>';

                if ($row->estado_inscripcion === 'pendiente') {
                    $btn .= '<button type="button" class="btn-action-reforzamiento" onclick="approve(' . $row->id . ')" title="Aprobar Inscripción">
                                <i class="mdi mdi-check-bold text-success"></i>
                             </button>';
                } else {
                    // Botón Imprimir (Impresora)
                    $btn .= '<a href="' . route('admin.reforzamiento.print', $row->id) . '" target="_blank" class="btn-action-reforzamiento" title="Imprimir Constancia">
                                <i class="mdi mdi-printer text-dark"></i>
                             </a>';
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
        $inscripcion = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'apoderados', 'pagos', 'aula'])->findOrFail($id);
        
        // ... (resto del código de auto-reparación existente)
        
        return response()->json($inscripcion);
    }

    public function updateStatus(Request $request, $id)
    {
        $inscripcion = InscripcionReforzamiento::findOrFail($id);
        
        if ($request->estado == 'validado' || $request->estado == 'VALIDADO') {
            $inscripcion->estado_inscripcion = 'validado';
            $inscripcion->aula_id = $request->aula_id;
            $inscripcion->validado_por = auth()->id();
            $inscripcion->fecha_validacion = now();

            // Sincronizar Pagos: El administrativo que valida la inscripción se convierte en el validador humano de sus pagos
            $pagos = $inscripcion->pagos;
            foreach ($pagos as $pago) {
                $pago->estado_pago = 'aprobado';
                $pago->validado_por = auth()->id(); // <--- Aquí capturamos al administrativo que está operando ahora
                $pago->save();
            }

            // El total pagado se calcula dinámicamente sumando los pagos aprobados
            $inscripcion->save();
            
            // Generar nro_constancia secuencial basado en el ciclo
            if (!$inscripcion->nro_constancia) {
                $ciclo = $inscripcion->ciclo;
                $correlativo_inicial = (int)($ciclo->correlativo_inicial ?? 1);
                
                // Contar cuántas inscripciones ya fueron validadas y tienen constancia en este ciclo
                $anteriores = DB::table('inscripciones_reforzamiento')
                    ->where('ciclo_id', $inscripcion->ciclo_id)
                    ->whereNotNull('nro_constancia')
                    ->count();
                
                $nuevo_numero = $correlativo_inicial + $anteriores;
                $inscripcion->nro_constancia = (string) $nuevo_numero;
            }
            
            // Aprobar pago
            $pago = $inscripcion->pagos()->first();
            if ($pago) {
                $pago->estado_pago = 'aprobado';
                $pago->save();
            }
        } else {
            $inscripcion->estado_inscripcion = $request->estado;
        }
        
        $inscripcion->save();

        return response()->json([
            'success' => true,
            'message' => 'Inscripción actualizada correctamente.',
            'nro_constancia' => $inscripcion->nro_constancia
        ]);
    }

    public function updateData(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $inscripcion = InscripcionReforzamiento::with(['estudiante', 'pagos', 'apoderados'])->findOrFail($id);
            $estudiante = $inscripcion->estudiante;

            // 1. Actualizar Datos Personales del Estudiante
            $estudiante->update([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'telefono' => $request->telefono,
                'email' => $request->email,
            ]);

            // 2. Actualizar/Crear Datos del Apoderado (Tabla propia)
            $apo = $inscripcion->apoderados()->first();
            $apoData = [
                'nombres' => $request->apoderado_nombre,
                'numero_documento' => $request->apoderado_dni,
                'celular' => $request->apoderado_telefono,
            ];
            
            if ($apo) {
                $apo->update($apoData);
            } else {
                $inscripcion->apoderados()->create($apoData);
            }

            // 3. Actualizar Datos de Inscripción
            if ($request->has('grado')) $inscripcion->grado = $request->grado;
            if ($request->has('turno')) $inscripcion->turno = $request->turno;
            if ($request->has('colegio_procedencia')) $inscripcion->colegio_procedencia = $request->colegio_procedencia;
            if ($request->has('observaciones')) $inscripcion->observaciones = $request->observaciones;
            if ($request->has('aula_id')) $inscripcion->aula_id = $request->aula_id;

            // --- NUEVO: Manejo de Archivos Correctivos ---
            $path = "reforzamiento/{$estudiante->numero_documento}";
            
            if ($request->hasFile('dni_file')) {
                $inscripcion->dni_estudiante_path = $request->file('dni_file')->store($path, 'public');
            }
            // El voucher_path se maneja abajo en la sección de pagos
            if ($request->hasFile('compromiso_file')) {
                $inscripcion->carta_compromiso_path = $request->file('compromiso_file')->store($path, 'public');
            }
            if ($request->hasFile('certificado_file')) {
                $inscripcion->certificado_path = $request->file('certificado_file')->store($path, 'public');
            }
            if ($request->hasFile('dni_apoderado_file')) {
                $inscripcion->dni_apoderado_path = $request->file('dni_apoderado_file')->store($path, 'public');
            }
            if ($request->hasFile('foto_file')) {
                $inscripcion->foto_path = $request->file('foto_file')->store($path, 'public');
            }

            $inscripcion->save();

            // 4. Actualizar Datos de Pago
            $pago = $inscripcion->pagos()->first();
            if ($pago) {
                if ($request->has('numero_operacion')) $pago->numero_operacion = $request->numero_operacion;
                if ($request->has('monto')) $pago->monto = $request->monto;
                if ($request->has('mes_pagado')) $pago->mes_pagado = $request->mes_pagado;
                
                // Mover el guardado del voucher aquí
                if ($request->hasFile('voucher_file')) {
                    $pago->voucher_path = $request->file('voucher_file')->store($path, 'public');
                }
                
                $pago->save();
            } else if ($request->hasFile('voucher_file')) {
                // Si no hay pago pero se subió un voucher, creamos un registro de pago básico
                $inscripcion->pagos()->create([
                    'numero_operacion' => $request->numero_operacion ?? 'PENDIENTE',
                    'monto' => $request->monto ?? 0,
                    'mes_pagado' => $request->mes_pagado ?? now()->format('F'),
                    'voucher_path' => $request->file('voucher_file')->store($path, 'public'),
                    'estado_pago' => 'pendiente'
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Expediente actualizado con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
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

    public function print($id)
    {
        $inscripcion = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'apoderados', 'pagos', 'aula'])->findOrFail($id);
        
        $estudiante = $inscripcion->estudiante;
        $ciclo = $inscripcion->ciclo;
        $pago = $inscripcion->pagos()->where('estado_pago', 'aprobado')->first() ?? $inscripcion->pagos()->first();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.constancia-reforzamiento', compact('inscripcion', 'estudiante', 'ciclo', 'pago'));
        
        return $pdf->stream("Constancia_Reforzamiento_{$estudiante->numero_documento}.pdf");
    }
}
