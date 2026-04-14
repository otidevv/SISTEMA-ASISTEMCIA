<?php

namespace App\Http\Controllers\Api;

use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\CentroEducativo;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Parentesco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\PaymentValidationService;

class PostulanteRegisterApiController extends BaseController
{
    /**
     * Registrar un nuevo postulante y crear su postulación (Todo en uno)
     */
    public function registerAndPostulate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Datos del estudiante
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'tipo_documento' => 'required|in:DNI,CE',
            'numero_documento' => 'required|string|size:8', // Quitamos unique para pruebas locales
            'email' => 'required|email', // Quitamos unique
            'telefono' => 'required|string|max:15',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|in:M,F',
            'direccion' => 'required|string|max:255',
            'password' => 'required|string|min:8',

            // ... (Datos de padres siguen igual: nullable)

            // Datos académicos
            'ciclo_id' => 'required|exists:ciclos,id',
            'carrera_id' => 'required|exists:carreras,id',
            'turno_id' => 'required|exists:turnos,id',
            'tipo_inscripcion' => 'required|in:postulante,reforzamiento',
            'centro_educativo_id' => 'required',
            'anio_egreso' => 'required|numeric|min:1950|max:' . (date('Y') + 1),

            // Datos del voucher
            'numero_recibo' => 'required|string|max:50',
            'fecha_emision_voucher' => 'required|date',
            'monto_matricula' => 'required|numeric|min:0',
            'monto_ensenanza' => 'required|numeric|min:0',

            // Documentos (Relajamos a nullable para facilitar pruebas móviles)
            'voucher_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificado_estudios' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'carta_compromiso' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'constancia_estudios' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'dni_documento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'foto_carnet' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error de validación', $validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();

        try {
            $ciclo = Ciclo::findOrFail($request->ciclo_id);

            // 1. Crear o Actualizar Estudiante
            $estudiante = User::updateOrCreate(
                ['numero_documento' => $request->numero_documento],
                [
                    'nombre' => $request->nombre,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                    'email' => $request->email,
                    'telefono' => $request->telefono,
                    'tipo_documento' => $request->tipo_documento,
                    'username' => 'S' . $request->numero_documento,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'genero' => $request->genero,
                    'direccion' => $request->direccion,
                    'centro_educativo_id' => $request->centro_educativo_id,
                    'password_hash' => Hash::make($request->password),
                    'estado' => true,
                ]
            );

            $estudiante->assignRole('Postulante');

            // 2. Verificar si ya existe una postulación activa para este ciclo
            $existe = Postulacion::where('estudiante_id', $estudiante->id)
                ->where('ciclo_id', $ciclo->id)
                ->exists();

            if ($existe) {
                return $this->sendError(
                    'Ya tienes una postulación activa para este ciclo académico. Si cometiste algún error o necesitas modificar tus datos, por favor acércate a la oficina de Admisión.',
                    [],
                    422
                );
            }

            // 2. Crear o Actualizar Padre
            if ($request->filled('padre_numero_documento')) {
                $apellidosPadre = explode(' ', $request->padre_apellidos, 2);
                $padre = User::updateOrCreate(
                    ['numero_documento' => $request->padre_numero_documento],
                    [
                        'nombre' => $request->padre_nombre,
                        'apellido_paterno' => $apellidosPadre[0],
                        'apellido_materno' => $apellidosPadre[1] ?? '',
                        'tipo_documento' => $request->padre_tipo_documento,
                        'username' => 'P' . $request->padre_numero_documento,
                        'email' => $request->padre_email ?? 'padre_' . $request->padre_numero_documento . '@cepre.com',
                        'telefono' => $request->padre_telefono,
                        'password_hash' => Hash::make(Str::random(12)),
                        'estado' => true,
                    ]
                );
                $padre->assignRole('Padre');
                
                // Relación con el Padre
                Parentesco::updateOrCreate(
                    ['estudiante_id' => $estudiante->id, 'padre_id' => $padre->id],
                    ['tipo_parentesco' => 'Padre', 'estado' => true]
                );
            }
            // 3. Crear o Actualizar Madre
            if ($request->filled('madre_numero_documento')) {
                $apellidosMadre = explode(' ', $request->madre_apellidos, 2);
                $madre = User::updateOrCreate(
                    ['numero_documento' => $request->madre_numero_documento],
                    [
                        'nombre' => $request->madre_nombre,
                        'apellido_paterno' => $apellidosMadre[0],
                        'apellido_materno' => $apellidosMadre[1] ?? '',
                        'tipo_documento' => $request->madre_tipo_documento,
                        'numero_documento' => $request->madre_numero_documento,
                        'username' => 'M' . $request->madre_numero_documento,
                        'email' => $request->madre_email ?? 'madre_' . $request->madre_numero_documento . '@cepre.com',
                        'telefono' => $request->madre_telefono,
                        'password_hash' => Hash::make(Str::random(12)),
                        'estado' => true,
                    ]
                );
                $madre->assignRole('Madre');

                // Relación con la Madre
                Parentesco::updateOrCreate(
                    ['estudiante_id' => $estudiante->id, 'padre_id' => $madre->id],
                    ['tipo_parentesco' => 'Madre', 'estado' => true]
                );
            }

            // 5. Subir Documentos
            $uploadPath = 'inscripciones/' . $ciclo->codigo . '/' . $estudiante->numero_documento;
            
            $documentPaths = [];
            $files = [
                'voucher_pago' => 'voucher',
                'certificado_estudios' => 'certificados',
                'carta_compromiso' => 'carta',
                'constancia_estudios' => 'constancia',
                'dni_documento' => 'dni',
                'foto_carnet' => 'foto'
            ];

            foreach ($files as $field => $folder) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store($uploadPath . '/' . $folder, 'public');
                    $documentPaths[$field] = $path;
                    
                    if ($field === 'foto_carnet') {
                        $estudiante->foto_perfil = $path;
                        $estudiante->save();
                    }
                }
            }

            // 6. Crear Postulación
            $montoTotal = $request->monto_matricula + $request->monto_ensenanza;
            
            // Generar código correlativo
            $ultimoCodigo = Postulacion::max('codigo_postulante') ?? 1000000;
            $nuevoCodigo = $ultimoCodigo + 1;
            while (Postulacion::where('codigo_postulante', $nuevoCodigo)->exists()) {
                $nuevoCodigo++;
            }

            $postulacion = Postulacion::create([
                'estudiante_id' => $estudiante->id,
                'ciclo_id' => $ciclo->id,
                'carrera_id' => $request->carrera_id,
                'turno_id' => $request->turno_id,
                'tipo_inscripcion' => $request->tipo_inscripcion,
                'centro_educativo_id' => $request->centro_educativo_id,
                'anio_egreso' => $request->anio_egreso,
                'fecha_postulacion' => now(),
                'estado' => 'pendiente',
                'codigo_postulante' => $nuevoCodigo,
                'numero_recibo' => $request->numero_recibo,
                'fecha_emision_voucher' => $request->fecha_emision_voucher,
                'monto_matricula' => $request->monto_matricula,
                'monto_ensenanza' => $request->monto_ensenanza,
                'monto_total_pagado' => $montoTotal,
                'voucher_path' => $documentPaths['voucher_pago'] ?? null,
                'certificado_estudios_path' => $documentPaths['certificado_estudios'] ?? null,
                'carta_compromiso_path' => $documentPaths['carta_compromiso'] ?? null,
                'constancia_estudios_path' => $documentPaths['constancia_estudios'] ?? null,
                'dni_path' => $documentPaths['dni_documento'] ?? null,
                'foto_path' => $documentPaths['foto_carnet'] ?? null,
                'documentos_verificados' => false,
                'pago_verificado' => false,
                'created_by' => null, // Público
            ]);

            // Disparar Evento en Tiempo Real (Reverb) y Notificación Persistente
            $nombreCarrera = \App\Models\Carrera::find($request->carrera_id)->nombre ?? 'Carrera';
            $nombreCompleto = $estudiante->nombre . ' ' . $estudiante->apellido_paterno;
            
            \App\Events\NuevaPostulacionCreada::dispatch($nombreCompleto, $nombreCarrera, $estudiante->numero_documento, null, $postulacion->foto_path, 'cepre');

            // Notificar a administradores (Base de Datos + Campana)
            $admins = \App\Models\User::whereHas('roles', function($q) {
                $q->where('nombre', 'admin');
            })->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NuevaPostulacionDatabaseNotification($nombreCompleto, $nombreCarrera));
            }

            DB::commit();

            return $this->sendResponse([
                'estudiante_id' => $estudiante->id,
                'postulacion_id' => $postulacion->id,
                'codigo_postulante' => $postulacion->codigo_postulante,
                'mensaje' => 'Registro y postulación realizados con éxito. Pendiente de revisión.'
            ], 'Registro exitoso.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error al procesar el registro: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Obtener todos los vouchers disponibles para un DNI (Sin necesidad de secuencia)
     */
    public function getAvailablePayments($dni)
    {
        try {
            $paymentService = app(PaymentValidationService::class);
            $vouchers = $paymentService->validateVoucher($dni, null, true);

            if ($vouchers && count($vouchers) > 0) {
                $disponibles = [];
                foreach ($vouchers as $voucher) {
                    $yaUsado = Postulacion::where('numero_recibo', $voucher['serial'])->exists();
                    if (!$yaUsado) {
                        $disponibles[] = $voucher;
                    }
                }
                
                return $this->sendResponse($disponibles, 'Vouchers disponibles encontrados.');
            }

            return $this->sendResponse([], 'No se encontraron vouchers disponibles.');
        } catch (\Exception $e) {
            return $this->sendError('Error al consultar pagos: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Verificar un voucher de pago mediante DNI y secuencia (Para App Móvil)
     */
    public function validatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string',
            'secuencia' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Datos inválidos', $validator->errors()->toArray(), 422);
        }

        try {
            $paymentService = app(PaymentValidationService::class);
            $result = $paymentService->validateVoucher($request->dni, $request->secuencia, true);

            if ($result && count($result) > 0) {
                foreach ($result as $payment) {
                    // Soporte para coincidencia parcial (ej: el usuario ingresa 49237 y el serial es V002-00049237)
                    $cleanSerial = preg_replace('/[^0-9]/', '', $payment['serial']);
                    $cleanInput = preg_replace('/[^0-9]/', '', $request->secuencia);
                    
                    if (str_ends_with($payment['serial'], $request->secuencia) || 
                        str_ends_with($cleanSerial, $cleanInput) ||
                        $payment['serial'] == $request->secuencia) {
                        
                        $yaUsado = Postulacion::where('numero_recibo', $payment['serial'])->exists();
                        if (!$yaUsado) {
                            return $this->sendResponse([
                                'valid' => true,
                                'payment' => $payment,
                                'message' => 'Voucher verificado correctamente.'
                            ], 'Pago válido.');
                        } else {
                            return $this->sendError('El voucher ya ha sido utilizado en otra postulación.', [], 400);
                        }
                    }
                }
            }

            return $this->sendError('Voucher no encontrado o DNI no coincide. Por favor, verifica en el banco.', [], 404);
        } catch (\Exception $e) {
            return $this->sendError('Error al conectar con el servicio de pagos: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Obtener datos necesarios para el formulario de postulación (Catálogos)
     */
    public function getFormDependencies()
    {
        $data = \Illuminate\Support\Facades\Cache::remember('public_postulation_dependencies', 60, function () {
            $cicloActivo = Ciclo::where('es_activo', true)->where('programa_id', 1)->first();
            if (!$cicloActivo) {
                return null;
            }

            $carreras = Carrera::where('estado', true)->get(['id', 'nombre', 'codigo']);
            $turnos = Turno::where('estado', true)->get(['id', 'nombre', 'hora_inicio', 'hora_fin']);
            
            return [
                'ciclo' => [
                    'id' => $cicloActivo->id,
                    'nombre' => $cicloActivo->nombre,
                    'codigo' => $cicloActivo->codigo,
                ],
                'carreras' => $carreras,
                'turnos' => $turnos,
                'tipos_inscripcion' => [
                    ['id' => 'postulante', 'nombre' => 'Postulante'],
                    ['id' => 'reforzamiento', 'nombre' => 'Reforzamiento'],
                ]
            ];
        });

        if (!$data) {
            return $this->sendError('No hay ciclo activo disponible.');
        }

        return $this->sendResponse($data, 'Catálogos obtenidos correctamente.');
    }
}
