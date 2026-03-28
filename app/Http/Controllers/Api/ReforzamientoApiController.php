<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ciclo;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InscripcionReforzamiento;
use App\Models\ApoderadoReforzamiento;
use App\Models\PagoReforzamiento;
use App\Events\NuevaPostulacionCreada;
use App\Notifications\NuevaPostulacionDatabaseNotification;
use App\Services\PaymentValidationService;
use Carbon\Carbon;

class ReforzamientoApiController extends BaseController
{
    protected $paymentService;

    public function __construct(PaymentValidationService $paymentService)
    {
        $this->paymentService = $paymentService;
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
        $ciclo = Ciclo::where('nombre', 'like', '%Reforzamiento%')
            ->where('es_activo', true)
            ->first();

        if (!$ciclo) {
            Log::warning("Intento de verificación de DNI sin ciclo de Reforzamiento activo.", ['dni' => $dni]);
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
        $pagos = $this->paymentService->validateVoucher($dni, null);
        $pagoEncontrado = null;

        if ($pagos) {
            foreach ($pagos as $pago) {
                if ((float)$pago['monto_total'] >= 200) {
                    $pagoEncontrado = $pago;
                    break;
                }
            }
        }

        return $this->sendResponse([
            'ciclo' => $ciclo,
            'pago_encontrado' => $pagoEncontrado,
            'estudiante_existente' => $estudiante ? [
                'nombre' => $estudiante->nombre,
                'paterno' => $estudiante->apellido_paterno,
                'materno' => $estudiante->apellido_materno,
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
            'telefono' => 'required|string|size:9',
            'email' => 'nullable|email',
            'grado' => 'required|string',
            'seccion' => 'required|string',
            'colegio_id' => 'nullable',
            'colegio_nombre_manual' => 'nullable|string',
            'apoderados' => 'required|array|min:1',
            'apoderados.*.dni' => 'required|string|size:8',
            'apoderados.*.nombre' => 'required|string|min:2',
            'apoderados.*.telefono' => 'required|string',
            'ciclo_id' => 'required|exists:ciclos,id',
            'es_manual' => 'required',
            'foto' => 'required|image|max:5120',
            'dni_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dni_apoderado_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'voucher_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificado_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Datos incompletos', $validator->errors()->toArray(), 422);
        }

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
            $inscripcion->programa_id = 2; // Reforzamiento Escolar id=2
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
                    'monto' => $request->monto_voucher ?? 200.00,
                    'fecha_pago' => $request->voucher_fecha ?? Carbon::now()->toDateString(),
                    'mes_pagado' => Carbon::now()->format('F Y'),
                    'voucher_path' => $request->hasFile('voucher_file') ? $request->file('voucher_file')->store($path, 'public') : null,
                    'estado_pago' => 'pendiente'
                ]);
            } else {
                // Pago Automático API
                $apiSerial = $request->input('pago_api_serial');
                PagoReforzamiento::create([
                    'inscripcion_id' => $inscripcion->id,
                    'numero_operacion' => $apiSerial ?: ('AUTO-' . $request->dni . '-' . time()),
                    'monto' => 200.00,
                    'fecha_pago' => Carbon::now()->toDateString(),
                    'mes_pagado' => Carbon::now()->format('F Y'),
                    'verificado_api' => 1,
                    'estado_pago' => 'aprobado'
                ]);
            }

            // Notificaciones
            try {
                NuevaPostulacionCreada::dispatch(
                    $estudiante->nombre . ' ' . $estudiante->apellido_paterno, 
                    'REFORZAMIENTO - ' . ($request->grado ?? '') . '° SEC.'
                );
            } catch (\Exception $e) { Log::error("Error notificando: " . $e->getMessage()); }

            DB::commit();
            return $this->sendResponse($inscripcion, '¡Inscripción exitosa! Tu solicitud está en proceso.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error Reforzamiento: " . $e->getMessage());
            return $this->sendError('Error al procesar la inscripción: ' . $e->getMessage());
        }
    }
}
