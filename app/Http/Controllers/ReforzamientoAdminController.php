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
        $cicloActual = $ciclos->first(); // El más reciente
        $aulas = \App\Models\Aula::where('estado', true)->get();
        return view('admin.reforzamiento.index', compact('ciclos', 'aulas', 'cicloActual'));
    }

    public function getData(Request $request)
    {
        // Consulta base para conteos y datos
        $queryBuilder = InscripcionReforzamiento::query();
        
        if ($request->filled('ciclo_id')) {
            $queryBuilder->where('ciclo_id', $request->ciclo_id);
        }

        // Obtener conteos y RECAUDACIÓN TOTAL en una sola consulta
        $stats = DB::table('inscripciones_reforzamiento as i')
            ->leftJoin('pagos_reforzamiento as p', function($join) {
                $join->on('i.id', '=', 'p.inscripcion_id')
                     ->where('p.estado_pago', '=', 'aprobado');
            })
            ->selectRaw('count(DISTINCT i.id) as total')
            ->selectRaw('SUM(CASE WHEN i.estado_inscripcion = "pendiente" THEN 1 ELSE 0 END) as pendiente')
            ->selectRaw('SUM(CASE WHEN i.estado_inscripcion = "validado" THEN 1 ELSE 0 END) as aprobado')
            ->selectRaw('COALESCE(SUM(p.monto), 0) as recaudado')
            ->when($request->filled('ciclo_id'), function($q) use ($request) {
                return $q->where('i.ciclo_id', $request->ciclo_id);
            })
            ->first();

        $counts = [
            'total' => $stats->total,
            'pendiente' => $stats->pendiente,
            'aprobado' => $stats->aprobado,
            'recaudado' => number_format($stats->recaudado, 2, '.', ','),
        ];

        // Preparar consulta para DataTables con relaciones - Se añade 'apoderados'
        $query = InscripcionReforzamiento::with(['estudiante', 'ciclo', 'pagos', 'aula', 'apoderados'])
            ->orderBy('created_at', 'desc');
        
        if ($request->filled('ciclo_id')) {
            $query->where('ciclo_id', $request->ciclo_id);
        }

        // Filtros Rápidos
        if ($request->filled('quick_filter')) {
            switch ($request->quick_filter) {
                case 'debtors':
                    // Alumnos que no han completado el pago (Asumimos 400 como meta)
                    $query->where(function($q) {
                        $q->whereDoesntHave('pagos')
                          ->orWhereHas('pagos', function($p) {
                              $p->select(DB::raw('SUM(monto)'))->havingRaw('SUM(monto) < 400');
                          });
                    });
                    break;
                case 'validated':
                    $query->where('estado_inscripcion', 'validado');
                    break;
                case 'today':
                    $query->whereDate('created_at', \Carbon\Carbon::today());
                    break;
            }
        }

        return DataTables::of($query)
            ->filterColumn('estudiante_nombre', function($query, $keyword) {
                $query->whereHas('estudiante', function($q) use ($keyword) {
                    $q->where('nombre', 'like', "%{$keyword}%")
                      ->orWhere('apellido_paterno', 'like', "%{$keyword}%")
                      ->orWhere('apellido_materno', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('dni', function($query, $keyword) {
                $query->whereHas('estudiante', function($q) use ($keyword) {
                    $q->where('numero_documento', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('estudiante_nombre', function($row) {
                // Buscamos la foto: primero la de la inscripción actual, luego la de perfil del estudiante
                $foto = $row->foto_path ?? $row->estudiante->foto_perfil;
                $avatar_url = $foto ? asset('storage/' . $foto) : 'https://ui-avatars.com/api/?name=' . urlencode($row->estudiante->nombre) . '&background=2d3436&color=fff';
                
                $avatar = '<img src="' . $avatar_url . '" alt="" class="avatar-sm rounded-circle me-3 mr-2 shadow-sm" style="width:38px; height:38px; object-fit:cover; border: 2px solid #fff;">';
                
                return '<div class="d-flex align-items-center">
                            ' . $avatar . '
                            <div>
                                <h5 class="fs-13 my-0 fw-bold"><a href="javascript:void(0);" onclick="viewDetails('.$row->id.')" class="text-reset">' . strtoupper($row->estudiante->nombre_completo) . '</a></h5>
                                <p class="text-muted mb-0 font-size-10" style="line-height:1;"><i class="mdi mdi-calendar-clock mr-1"></i> ' . $row->created_at->format('d/m/Y H:i') . '</p>
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
                $totalPagado = $row->pagos()->where('estado_pago', 'aprobado')->sum('monto');
                $meta = 400; // Meta de Reforzamiento
                
                if ($totalPagado >= $meta) {
                    $class = 'paid';
                    $icon = 'check-circle';
                    $text = 'PAGO COMPLETO';
                } elseif ($totalPagado > 0) {
                    $class = 'partial'; // Nueva clase CSS para naranja
                    $icon = 'clock-alert';
                    $text = 'PAGO PARCIAL (S/. '.$totalPagado.')';
                } else {
                    $class = 'unpaid';
                    $icon = 'close-circle';
                    $text = 'DEUDOR';
                }

                return '<div class="text-center">
                            <span class="payment-chip payment-chip-'.$class.'">
                                <i class="mdi mdi-'.$icon.' mr-1"></i>' . $text . '
                            </span>
                        </div>';
            })
            ->addColumn('acciones', function($row) {
                $btn = '<div class="text-center">
                            <div class="d-flex justify-content-center">';
                
                // Botón Ver Detalle (Ojito)
                $btn .= '<a href="javascript:void(0);" onclick="viewDetails(' . $row->id . ')" class="btn-action-reforzamiento btn-solid-view" title="Ver Expediente">
                            <i class="mdi mdi-eye"></i>
                         </a>';

                // Botón Editar (Lápiz) - Ahora para TODOS
                $btn .= '<a href="javascript:void(0);" onclick="editInscripcion(' . $row->id . ')" class="btn-action-reforzamiento btn-solid-edit" title="Editar Expediente">
                            <i class="mdi mdi-pencil"></i>
                         </a>';

                if ($row->estado_inscripcion === 'pendiente') {
                    $btn .= '<button type="button" class="btn-action-reforzamiento btn-solid-approve" onclick="approve(' . $row->id . ')" title="Aprobar Inscripción">
                                <i class="mdi mdi-check-bold"></i>
                             </button>';
                } else {
                    // Botón Imprimir (Impresora)
                    $btn .= '<a href="' . route('admin.reforzamiento.print', $row->id) . '" target="_blank" class="btn-action-reforzamiento btn-solid-print" title="Imprimir Constancia">
                                <i class="mdi mdi-printer"></i>
                             </a>';
                }

                if ($row->estudiante && $row->estudiante->telefono) {
                    $btn .= '<a href="https://wa.me/51' . $row->estudiante->telefono . '" target="_blank" class="btn-action-reforzamiento btn-solid-wa" title="WhatsApp">
                                <i class="mdi mdi-whatsapp"></i>
                             </a>';
                }

                $btn .= '<button type="button" class="btn-action-reforzamiento btn-solid-delete" onclick="deleteRecord(' . $row->id . ')" title="Eliminar">
                            <i class="mdi mdi-trash-can-outline"></i>
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
            $estudiante->update(array_filter([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'telefono' => $request->telefono,
                'email' => $request->email,
            ], function($value) {
                return !is_null($value);
            }));

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

    /**
     * Sincronizar Pagos desde la API de UNAMAD para un estudiante específico
     */
    public function syncPayments($id)
    {
        try {
            $inscripcion = InscripcionReforzamiento::with('estudiante')->findOrFail($id);
            $estudiante = $inscripcion->estudiante;
            
            if (!$estudiante || !$estudiante->numero_documento) {
                return response()->json(['success' => false, 'message' => 'No se encontró el DNI del estudiante.'], 422);
            }

            $pagosApi = $this->paymentService->validateVoucher($estudiante->numero_documento, null);
            
            if (!$pagosApi || empty($pagosApi)) {
                return response()->json(['success' => false, 'message' => 'No se encontraron pagos nuevos en la API de UNAMAD.'], 404);
            }

            $nuevosPagosCount = 0;
            $pagosEncontrados = 0;

            foreach ($pagosApi as $voucher) {
                $serial = $voucher['serial_voucher'] ?? $voucher['serial'] ?? null;
                if (!$serial) continue;

                $pagosEncontrados++;
                
                // Mapeo selectivo para filtrar solo lo que es reforzamiento 598
                $montoReforzamiento = 0;
                $hayReforzamiento = false;
                $items = $voucher['items'] ?? [];
                
                foreach ($items as $item) {
                    $desc = strtoupper($item['description'] ?? '');
                    if (str_contains($desc, '598') || str_contains($desc, 'REFORZAMIENTO')) {
                        $montoReforzamiento += (float)$item['total'];
                        $hayReforzamiento = true;
                    }
                }

                if (!$hayReforzamiento) continue;

                // FILTRO DE SEGURIDAD: Solo pagos relacionados al ciclo actual
                // Permitimos pagos desde 2 meses antes del inicio del ciclo
                $fechaVoucher = $voucher['fecha'] ? \Carbon\Carbon::parse($voucher['fecha']) : null;
                $ciclo = $inscripcion->ciclo;
                
                if ($fechaVoucher && $ciclo && $ciclo->fecha_inicio) {
                    $fechaLimite = \Carbon\Carbon::parse($ciclo->fecha_inicio)->subMonths(2);
                    if ($fechaVoucher->lt($fechaLimite)) {
                        continue;
                    }
                }

                // Verificar si ya existe este pago
                $existe = \App\Models\PagoReforzamiento::where('inscripcion_id', $inscripcion->id)
                    ->where('numero_operacion', $serial)
                    ->first();

                if (!$existe) {
                    // Detectar qué número de pago es para este alumno (+1 porque aún no se inserta)
                    $ordenPago = $inscripcion->pagos()->count();
                    $mesAtribuido = null;
                    
                    if ($inscripcion->ciclo && $inscripcion->ciclo->fecha_inicio) {
                        $mesAtribuido = \Carbon\Carbon::parse($inscripcion->ciclo->fecha_inicio)
                            ->addMonths($ordenPago)
                            ->translatedFormat('F Y');
                    }

                    // Crear el nuevo pago detectado
                    \App\Models\PagoReforzamiento::create([
                        'inscripcion_id' => $inscripcion->id,
                        'numero_operacion' => $serial,
                        'monto' => $montoReforzamiento,
                        'fecha_pago' => $voucher['fecha'] ? \Carbon\Carbon::parse($voucher['fecha'])->toDateString() : now()->toDateString(),
                        'mes_pagado' => $mesAtribuido ? ucwords($mesAtribuido) : \Carbon\Carbon::parse($voucher['fecha'])->translatedFormat('F Y'),
                        'verificado_api' => true,
                        'estado_pago' => 'aprobado',
                        'fecha_verificacion_api' => now()
                    ]);
                    $nuevosPagosCount++;
                } else {
                    // Actualizar si es necesario
                    $existe->update([
                        'monto' => $montoReforzamiento,
                        'verificado_api' => true,
                        'estado_pago' => 'aprobado'
                    ]);
                }
            }

            return response()->json([
                'success' => true, 
                'message' => "Sincronización completa. Se encontraron $pagosEncontrados vouchers y se procesaron $nuevosPagosCount pagos nuevos.",
                'nuevos' => $nuevosPagosCount
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al sincronizar: ' . $e->getMessage()], 500);
        }
    }
}
