<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ciclo;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InscripcionReforzamiento;
use App\Models\ApoderadoReforzamiento;
use App\Models\PagoReforzamiento;
use App\Models\ProgramaAcademico;
use App\Events\NuevaPostulacionCreada;
use App\Services\PaymentValidationService;
use App\Services\ReforzamientoPdfService;
use Carbon\Carbon;

class ReforzamientoApiController extends BaseController
{
    protected $paymentService;
    protected $pdfService;

    public function __construct(PaymentValidationService $paymentService, ReforzamientoPdfService $pdfService)
    {
        $this->paymentService = $paymentService;
        $this->pdfService = $pdfService;
    }

    /**
     * Generar Pack de Inscripción (Carta de Compromiso) dinámico
     */
    public function generateRegistrationPack(Request $request)
    {
        try {
            // Validar datos mínimos necesarios para el PDF
            $data = $request->only([
                'estudiante_nombre', 'estudiante_dni', 
                'apoderado_nombre', 'apoderado_dni', 
                'apoderado_celular', 'apoderado_direccion'
            ]);

            if (!$data['estudiante_nombre'] || !$data['estudiante_dni']) {
                return response()->json(['error' => 'Datos incompletos para generar el PDF'], 422);
            }

            $pdf = $this->pdfService->fillRegistrationPack($data);

            return response($pdf->Output('S', 'Pack_Inscripcion_Reforzamiento.pdf'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="Pack_Inscripcion_Reforzamiento_' . $data['estudiante_dni'] . '.pdf"');

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar Pack PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generar PDF de Constancia de Inscripción
     */
    public function generarConstancia($id)
    {
        try {
            $inscripcion = InscripcionReforzamiento::with(['estudiante', 'apoderados', 'pagos'])
                ->findOrFail($id);

            // Permitir descarga si está validado o finalizado
            if ($inscripcion->estado_inscripcion !== 'validado' && $inscripcion->estado_inscripcion !== 'finalizado') {
                return response()->json(['error' => 'La inscripción debe estar validada para generar la constancia.'], 403);
            }

            $pago = $inscripcion->pagos()->orderBy('created_at', 'desc')->first();
            $estudiante = $inscripcion->estudiante;

            $pdf = Pdf::loadView('pdf.constancia-reforzamiento', [
                'inscripcion' => $inscripcion,
                'estudiante' => $estudiante,
                'pago' => $pago
            ]);

            return $pdf->stream('constancia-reforzamiento-' . $estudiante->numero_documento . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Paso 1: Verificar DNI y Ciclo Activo
     */
    public function verifyDni(Request $request, $dni)
    {
        $validator = Validator::make(['dni' => $dni], [
            'dni' => 'required|string|size:8',
        ]);

        if ($validator->fails()) {
            return $this->sendError('DNI inválido', $validator->errors()->toArray(), 422);
        }

        // 1. Buscar Ciclo de Reforzamiento Activo
        $ciclo = Ciclo::where('programa_id', 2)
            ->where('es_activo', true)
            ->first();

        if (!$ciclo) {
            return $this->sendError('No hay un ciclo de reforzamiento activo en este momento.', [], 422);
        }

        // 2. Verificar si ya está inscrito
        $estudiante = User::where('numero_documento', $dni)->first();
        if ($estudiante) {
            $inscripcion = InscripcionReforzamiento::where('estudiante_id', $estudiante->id)
                ->where('ciclo_id', $ciclo->id)
                ->first();

            if ($inscripcion) {
                return $this->sendError('Este estudiante ya se encuentra inscrito en ' . $ciclo->nombre, [
                    'estado' => $inscripcion->estado_inscripcion
                ], 400);
            }
        }

        // 3. Consultar Pagos AUTOMÁTICOS
        $pagosRaw = $this->paymentService->validateVoucher($dni, null);
        $pagoEncontrado = null;
        $yearActual = '2026'; // Forzamos año actual o lo detectamos de Carbon::now()->year

        if ($pagosRaw) {
            $recibosProcesados = [];

            foreach ($pagosRaw as $voucher) {
                $serial = $voucher['serial_voucher'] ?? 'AUTO';
                $totalRecibo = 0;
                $hayReforzamiento = false;
                $fechaPago = null;

                if (!isset($voucher['payments']) || !is_array($voucher['payments'])) continue;

                foreach ($voucher['payments'] as $p) {
                    $desc = strtoupper($p['description'] ?? '');
                    $status = (int)($p['status'] ?? 0);
                    $monto = (float)($p['total'] ?? $p['monto_total'] ?? 0);
                    $yearPago = substr($p['paymentDate'] ?? '', 0, 4);

                    // Filtros estrictos: Solo Reforzamiento, de este año y Pagado (status 2)
                    if (str_contains($desc, 'REFORZAMIENTO') && $yearPago === $yearActual && $status === 2) {
                        $totalRecibo += $monto;
                        $hayReforzamiento = true;
                        $fechaPago = $p['paymentDate'];
                    }
                }

                if ($hayReforzamiento && $totalRecibo >= 200) {
                    $recibosProcesados[] = [
                        'serial_voucher' => $serial,
                        'monto' => $totalRecibo,
                        'paymentDate' => $fechaPago,
                        'description' => 'REFORZAMIENTO ESCOLAR (API SUM)'
                    ];
                }
            }

            // Ordenar por fecha descendente y tomar el más reciente
            if (count($recibosProcesados) > 0) {
                usort($recibosProcesados, function($a, $b) {
                    return strcmp($b['paymentDate'], $a['paymentDate']);
                });
                $pagoEncontrado = $recibosProcesados[0];
                // Formatear para que el frontend lo entienda como un objeto de pago estándar
                $pagoEncontrado['total'] = $pagoEncontrado['monto'];
            }
        }

        // PROFESIONAL: Buscar el ciclo académico oficial de REFORZAMIENTO activo
        $ciclo = Ciclo::where('es_activo', true)->where('programa_id', 2)->first() 
                 ?? Ciclo::where('es_activo', true)->where('nombre', 'like', '%REFORZAMIENTO%')->first();

        if (!$ciclo) {
            return $this->sendError('Operación bloqueada: No se encontró un ciclo académico activo para Reforzamiento.', [], 404);
        }

        return $this->sendResponse([
            'ciclo' => [
                'id' => $ciclo->id,
                'nombre' => $ciclo->nombre,
                'fecha_inicio' => $ciclo->fecha_inicio ? $ciclo->fecha_inicio->format('Y-m-d') : null,
                'fecha_fin' => $ciclo->fecha_fin ? $ciclo->fecha_fin->format('Y-m-d') : null,
                'descripcion' => $ciclo->descripcion
            ],
            'pago_encontrado' => $pagoEncontrado,
            'estudiante_existente' => $estudiante ? [
                'nombre' => $estudiante->nombre,
                'paterno' => $estudiante->apellido_paterno,
                'materno' => $estudiante->apellido_materno,
                'fecha_nacimiento' => $estudiante->fecha_nacimiento,
                'genero' => $estudiante->genero,
            ] : null
        ], 'DNI verificado correctamente.');
    }

    /**
     * Paso Final: Registro Completo
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|size:8',
            'nombre' => 'required|string|min:2',
            'apellido_paterno' => 'required|string|min:2',
            'apellido_materno' => 'required|string|min:2',
            'telefono' => 'required|string|size:9',
            'email' => 'nullable|email',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|string|in:MASCULINO,FEMENINO',
            'grado' => 'required|string',
            'seccion' => 'required|string',
            'colegio_id' => 'nullable',
            'colegio_nombre_manual' => 'nullable|string',
            'apoderados' => 'required|array|min:1',
            'apoderados.*.dni' => 'required|string|size:8',
            'apoderados.*.nombre' => 'required|string|min:2',
            'apoderados.*.telefono' => 'nullable|string',
            'apoderados.*.parentesco' => 'nullable|string',
            'ciclo_id' => 'required|exists:ciclos,id',
            'es_manual' => 'required',
            'foto' => 'required|image|max:10240',
            'dni_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dni_apoderado_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'voucher_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificado_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'compromiso_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validación fallida: ' . implode(', ', $validator->errors()->all()), $validator->errors()->toArray(), 422);
        }

        // Buscar ID del Programa de Reforzamiento
        $prog = ProgramaAcademico::where('nombre', 'like', '%Reforzamiento%')->first();
        $programaId = $prog ? $prog->id : 2; 

        DB::beginTransaction();
        try {
            // 1. Crear/Actualizar Estudiante (User)
            $estudiante = User::updateOrCreate(
                ['numero_documento' => $request->dni],
                [
                    'username' => 'RS' . $request->dni,
                    'email' => $request->email ?: ($request->dni . '@reforzamiento.edu.pe'),
                    'nombre' => $request->nombre,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'genero' => $request->genero,
                    'tipo_documento' => 'DNI',
                    'role' => 'Estudiante',
                    'password_hash' => Hash::make($request->dni),
                    'estado' => true,
                ]
            );
            $estudiante->assignRole('Estudiante');

            // 2. Mapeo de campos para la nueva tabla
            $gradoMap = [1 => '1ro', 2 => '2do', 3 => '3ro', 4 => '4to', 5 => '5to'];

            // Obtener nombre del colegio
            $colegioId = $request->input('colegio_id');
            $nombreColegio = $request->input('colegio_nombre_manual', 'No especificado');

            if ($colegioId && is_numeric($colegioId)) {
                $colegio = DB::table('centros_educativos')->where('id', $colegioId)->first();
                if ($colegio) {
                    $nombreColegio = $colegio->cen_edu;
                }
            }

            // 3. Crear Inscripción
            $inscripcion = new InscripcionReforzamiento();
            $inscripcion->estudiante_id = $estudiante->id;
            $inscripcion->programa_id = $programaId;
            $inscripcion->ciclo_id = $request->ciclo_id;
            $inscripcion->grado = $gradoMap[$request->grado] ?? $request->grado;
            $inscripcion->turno = strtolower($request->seccion); // mañana o tarde
            $inscripcion->colegio_procedencia = $nombreColegio;
            $inscripcion->estado_inscripcion = 'pendiente';
            
            // Almacenar Archivos
            $path = "reforzamiento/" . $request->dni;
            if ($request->hasFile('foto')) {
                $inscripcion->foto_path = $request->file('foto')->store($path, 'public');
            }
            if ($request->hasFile('dni_file')) {
                $inscripcion->dni_estudiante_path = $request->file('dni_file')->store($path, 'public');
            }
            if ($request->hasFile('dni_apoderado_file')) {
                $inscripcion->dni_apoderado_path = $request->file('dni_apoderado_file')->store($path, 'public');
            }
            if ($request->hasFile('certificado_file')) {
                $inscripcion->certificado_path = $request->file('certificado_file')->store($path, 'public');
            }
            if ($request->hasFile('compromiso_file')) {
                $inscripcion->carta_compromiso_path = $request->file('compromiso_file')->store($path, 'public');
            }
            
            $inscripcion->save();

            // 4. Procesar Apoderados
            if ($request->has('apoderados')) {
                foreach ($request->apoderados as $apData) {
                    $par = 'Padre';
                    if (isset($apData['parentesco'])) {
                        if ($apData['parentesco'] == 'MADRE') $par = 'Madre';
                        if ($apData['parentesco'] == 'TUTOR') $par = 'Tutor';
                    }

                    $nombreCompleto = $apData['nombre'];
                    if (isset($apData['apellido_paterno'])) $nombreCompleto .= ' ' . $apData['apellido_paterno'];
                    if (isset($apData['apellido_materno'])) $nombreCompleto .= ' ' . $apData['apellido_materno'];

                    ApoderadoReforzamiento::create([
                        'inscripcion_id' => $inscripcion->id,
                        'numero_documento' => $apData['dni'],
                        'nombres' => trim($nombreCompleto),
                        'celular' => $apData['telefono'] ?? '',
                        'parentesco' => $par
                    ]);
                }
            }

            // 5. Procesar Pago
            $isManual = ($request->es_manual === "true" || $request->es_manual === true || $request->es_manual === "1");

            if ($isManual) {
                PagoReforzamiento::create([
                    'inscripcion_id' => $inscripcion->id,
                    'numero_operacion' => $request->voucher_secuencia ?? ('AUTO-M-' . time()),
                    'monto' => $request->monto_voucher ?: 200.00,
                    'fecha_pago' => $request->voucher_fecha ?? Carbon::now()->toDateString(),
                    'mes_pagado' => Carbon::now()->format('F Y'),
                    'voucher_path' => $request->hasFile('voucher_file') ? $request->file('voucher_file')->store($path, 'public') : null,
                    'estado_pago' => 'pendiente'
                ]);
            } else {
                // Pago Automático API
                $apiSerial = $request->input('pago_api_serial');
                $apiMonto = $request->input('monto_api');

                PagoReforzamiento::create([
                    'inscripcion_id' => $inscripcion->id,
                    'numero_operacion' => $apiSerial ?: ('AUTO-' . $request->dni . '-' . time()),
                    'monto' => $apiMonto ?: 200.00,
                    'fecha_pago' => $request->pago_api_fecha ?: Carbon::now()->toDateString(),
                    'mes_pagado' => Carbon::now()->format('F Y'),
                    'verificado_api' => 1,
                    'estado_pago' => 'aprobado'
                ]);
            }

            DB::commit();

            // Notificaciones en Tiempo Real (Reverb) - Ejecuatadas DESPUÉS del commit para evitar race conditions
            try {
                $nombreAlumno = ($estudiante->nombre ?? 'Un estudiante') . ' ' . ($estudiante->apellido_paterno ?? '');
                
                // 1. Notificación Global (Broadcast)
                event(new \App\Events\NuevaPostulacionCreada($nombreAlumno, 'REFORZAMIENTO ESCOLAR'));

                // 2. Notificación Persistente (Administradores con permiso)
                // OPTIMIZADO: Filtro directo en base de datos para no cargar todos los usuarios
                $supervisores = \App\Models\User::whereHas('roles.permissions', function($q) {
                    $q->where('nombre', 'postulaciones.view');
                })->orWhereHas('roles', function($q) {
                    $q->where('nombre', 'admin');
                })->get();
                
                if ($supervisores->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send(
                        $supervisores, 
                        new \App\Notifications\NuevaInscripcionReforzamiento($nombreAlumno, $inscripcion->id)
                    );
                }
            } catch (\Exception $e) { 
                // Error silenciado para no afectar la respuesta al cliente
            }

            return $this->sendResponse($inscripcion, '¡Inscripción exitosa! Tu solicitud está en proceso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error al procesar la inscripción: ' . $e->getMessage());
        }
    }

}
