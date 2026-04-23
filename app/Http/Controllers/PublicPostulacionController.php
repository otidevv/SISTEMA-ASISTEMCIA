<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Postulacion;
use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\Parentesco;
use App\Models\Role;
use App\Services\PaymentValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\CentroEducativo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Services\InstitucionalPdfService;

class PublicPostulacionController extends Controller
{
    protected $paymentService;
    protected $pdfService;

    public function __construct(PaymentValidationService $paymentService, InstitucionalPdfService $pdfService)
    {
        $this->paymentService = $paymentService;
        $this->pdfService = $pdfService;
    }

    public function checkPostulante(Request $request)
    {
        // Log para debug (puedes revisarlo en storage/logs/laravel.log)

        $request->validate([
            'dni' => 'required|numeric|digits:8',
            'digito' => 'nullable|numeric|digits:1', // Lo hacemos nullable aquí para manejar el error manualmente con un mensaje mejor si falta
        ]);

        $dni = $request->dni;
        $digitoProporcionado = $request->digito ?? $request->check_dv; // Soporte para ambos nombres

        if (is_null($digitoProporcionado) || $digitoProporcionado === '') {
            return response()->json([
                'error' => 'Dígito de verificación requerido.',
                'message' => 'El campo del dígito verificador es obligatorio para continuar.'
            ], 422);
        }

        $cicloActivo = Ciclo::where('es_activo', 1)->first();

        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay un ciclo activo para postulaciones.'], 400);
        }

        // VALIDACIÓN DE SEGURIDAD: Verificar dígito con la API de Base de Datos
        try {
            $cacheKey = 'reniec_dni_' . $dni;
            $datosReniec = Cache::get($cacheKey);
            
            if (!$datosReniec) {
                $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://apidatos.unamad.edu.pe/api/consulta/' . $dni);
                if ($response->successful()) {
                    $datosReniec = $response->json();
                    // El dígito viene como DIG_RUC en esta API
                    $digitoCorrecto = $datosReniec['DIG_RUC'] ?? null;
                } else {
                    Log::warning("No se pudo conectar con Base de Datos para validar dígito del DNI: " . $dni);
                    $digitoCorrecto = null; // Permitir continuar manualmente si la API falla
                }
            } else {
                // Si está en caché, los datos ya están formateados por el ReniecController
                $digitoCorrecto = $datosReniec['digito_verificador'] ?? null;
                
                // Si por alguna razón el caché no tiene el dígito (viejo caché), forzar actualización
                if ($digitoCorrecto === null) {
                    $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://apidatos.unamad.edu.pe/api/consulta/' . $dni);
                    if ($response->successful()) {
                        $datosReniec = $response->json();
                        $digitoCorrecto = $datosReniec['DIG_RUC'] ?? null;
                    }
                }
            }

            if ($digitoCorrecto !== null && $digitoProporcionado != $digitoCorrecto) {
                return response()->json([
                    'error' => 'El dígito de verificación es incorrecto.',
                    'message' => 'Por favor, revise su DNI e ingrese el dígito que aparece después del guion.'
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error("Error validando dígito DNI: " . $e->getMessage());
            // En caso de error técnico extremo, permitimos pasar si es un error de la API externa
            // para no bloquear al usuario, pero lo ideal es que funcione.
        }

        $estudiante = User::where('numero_documento', $dni)->first();

        if ($estudiante) {
            // Verificar si ya tiene postulación en el ciclo actual
            $postulacion = Postulacion::where('estudiante_id', $estudiante->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();

            if ($postulacion) {
                // Cargar relaciones para mostrar información completa
                $postulacion->load(['carrera', 'turno', 'ciclo']);
                
                // Determinar el estado legible
                $estadoTexto = [
                    'pendiente' => 'Pendiente de Revisión',
                    'aprobada' => 'Aprobada',
                    'rechazada' => 'Rechazada',
                    'observada' => 'Observada'
                ];
                
                return response()->json([
                    'status' => 'registered',
                    'message' => 'Ya tienes una postulación registrada para este ciclo.',
                    'postulacion' => [
                        'id' => $postulacion->id,
                        'codigo' => $postulacion->codigo_postulante,
                        'estado' => $postulacion->estado,
                        'estado_texto' => $estadoTexto[$postulacion->estado] ?? ucfirst($postulacion->estado),
                        'ciclo' => $postulacion->ciclo ? $postulacion->ciclo->nombre : 'N/A',
                        'carrera' => $postulacion->carrera ? $postulacion->carrera->nombre : 'N/A',
                        'turno' => $postulacion->turno ? $postulacion->turno->nombre : 'N/A',
                        'fecha_postulacion' => $postulacion->fecha_postulacion ? $postulacion->fecha_postulacion->format('d/m/Y') : 'N/A',
                    ]
                ]);
            }

            // --- NUEVO: Autocompletado Proactivo desde el Servidor ---
            // Verificamos si faltan datos básicos o si son inválidos (ej. fecha 0000-00-00 o género no asignado)
            $faltaNombre = empty($estudiante->nombre) || empty($estudiante->apellido_paterno);
            $faltaGenero = !in_array($estudiante->genero, ['M', 'F']);
            $faltaFecha = empty($estudiante->fecha_nacimiento) || 
                         ($estudiante->fecha_nacimiento instanceof \Carbon\Carbon && $estudiante->fecha_nacimiento->year < 1920);

            if ($faltaNombre || $faltaGenero || $faltaFecha) {
                try {
                    // Consultar Base de Datos si hay huecos en la información
                    $responseReniec = \Illuminate\Support\Facades\Http::timeout(5)->get('https://apidatos.unamad.edu.pe/api/consulta/' . $dni);
                    
                    if ($responseReniec->successful()) {
                        $reniecData = $responseReniec->json();
                        
                        if (!empty($reniecData) && isset($reniecData['DNI'])) {
                            // 1. Nombres y Apellidos
                            if (empty($estudiante->nombre)) $estudiante->nombre = $reniecData['NOMBRES'] ?? '';
                            if (empty($estudiante->apellido_paterno)) $estudiante->apellido_paterno = $reniecData['AP_PAT'] ?? '';
                            if (empty($estudiante->apellido_materno)) $estudiante->apellido_materno = $reniecData['AP_MAT'] ?? '';
                            
                            // 2. Género (Mapping robusto)
                            if ($faltaGenero) {
                                $sexoRaw = $reniecData['SEXO'] ?? ($reniecData['sexo'] ?? null);
                                if ($sexoRaw) {
                                    $estudiante->genero = ($sexoRaw == '2' || strtoupper($sexoRaw) == 'F') ? 'F' : 'M';
                                }
                            }
                            
                            // 3. Fecha de Nacimiento (Parsing robusto)
                            if ($faltaFecha) {
                                $fechaRaw = $reniecData['FECHA_NAC'] ?? ($reniecData['fecha_nacimiento'] ?? null);
                                if ($fechaRaw) {
                                    try {
                                        $estudiante->fecha_nacimiento = \Carbon\Carbon::parse($fechaRaw)->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        \Log::error("Error parseando fecha Base de Datos ($fechaRaw) para DNI $dni: " . $e->getMessage());
                                    }
                                }
                            }
                            
                            // 4. Dirección (Opcional)
                            if (empty($estudiante->direccion)) {
                                $estudiante->direccion = $reniecData['DIRECCION'] ?? ($reniecData['direccion'] ?? '');
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning("No se pudo completar datos de estudiante recurrente $dni desde Base de Datos: " . $e->getMessage());
                }
            }

            // Cargar datos de padres (padre y madre)
            $parentescos = Parentesco::where('estudiante_id', $estudiante->id)
                ->with('padre')
                ->get();

            $padres = [
                'padre' => null,
                'madre' => null
            ];

            foreach ($parentescos as $parentesco) {
                $tipoLower = strtolower($parentesco->tipo_parentesco);
                if ($tipoLower === 'padre' && $parentesco->padre) {
                    $padres['padre'] = $parentesco->padre;
                } elseif ($tipoLower === 'madre' && $parentesco->padre) {
                    $padres['madre'] = $parentesco->padre;
                }
            }

            // Obtener la última postulación del estudiante (de cualquier ciclo anterior)
            $ultimaPostulacion = Postulacion::where('estudiante_id', $estudiante->id)
                ->with('centroEducativo')
                ->orderBy('created_at', 'desc')
                ->first();

            $datosPostulacion = null;
            if ($ultimaPostulacion) {
                // Preparar URLs de archivos
                $archivos = [];
                $camposArchivo = [
                    'foto_path',
                    'dni_path',
                    'certificado_estudios_path',
                    'voucher_path',
                    'carta_compromiso_path',
                    'constancia_estudios_path'
                ];

                foreach ($camposArchivo as $campo) {
                    if ($ultimaPostulacion->$campo) {
                        // Generar URL completa del archivo
                        $archivos[$campo] = asset('storage/' . $ultimaPostulacion->$campo);
                    } else {
                        $archivos[$campo] = null;
                    }
                }

                // Preparar datos del centro educativo con ubicación
                $centroEducativoData = null;
                if ($ultimaPostulacion->centroEducativo) {
                    $ce = $ultimaPostulacion->centroEducativo;
                    $centroEducativoData = [
                        'id' => $ce->id,
                        'nombre' => $ce->cen_edu ?? $ce->nombre ?? 'Sin nombre',
                        'nivel' => $ce->d_niv_mod ?? $ce->nivel ?? null,
                        'direccion' => $ce->dir_cen ?? $ce->direccion ?? null,
                        // Datos de ubicación
                        'departamento' => $ce->d_dpto ?? null,
                        'provincia' => $ce->d_prov ?? null,
                        'distrito' => $ce->d_dist ?? null
                    ];
                }

                $datosPostulacion = [
                    'archivos' => $archivos,
                    'datos_academicos' => [
                        'centro_educativo_id' => $ultimaPostulacion->centro_educativo_id,
                        'centro_educativo' => $centroEducativoData,
                        'anio_egreso' => $ultimaPostulacion->anio_egreso,
                        'carrera_id' => $ultimaPostulacion->carrera_id,
                        'turno_id' => $ultimaPostulacion->turno_id,
                        'tipo_inscripcion' => $ultimaPostulacion->tipo_inscripcion
                    ]
                ];
            }

            // Preparar el objeto estudiante para el JSON (Aseguramos formato de fecha para input type="date")
            $estudianteData = $estudiante->toArray();
            if ($estudiante->fecha_nacimiento) {
                // Carbon parse/format para garantizar YYYY-MM-DD
                $estudianteData['fecha_nacimiento'] = \Carbon\Carbon::parse($estudiante->fecha_nacimiento)->format('Y-m-d');
            }

            return response()->json([
                'status' => 'recurrent',
                'estudiante' => $estudianteData,
                'padres' => $padres,
                'ultima_postulacion' => $datosPostulacion,
                'message' => 'Estudiante encontrado. Sus datos serán cargados.'
            ]);
        }

        return response()->json([
            'status' => 'new',
            'message' => 'Estudiante nuevo.'
        ]);
    }

    public function validatePayment(Request $request)
    {
        $request->validate([
            'secuencia' => 'required|string',
            'dni' => 'required|string',
        ]);

        $result = $this->paymentService->validateVoucher($request->dni, $request->secuencia);

        if ($result) {
            return response()->json([
                'valid' => true,
                'payments' => $result
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Voucher no válido o no encontrado.'
        ], 400);
    }

    public function store(Request $request)
    {
        // Validación básica adaptable al tipo de documento
        $rules = [
            'estudiante_tipo_documento' => 'required',
            'estudiante_nombre' => 'required|string',
            'estudiante_apellido_paterno' => 'required|string',
            'estudiante_apellido_materno' => 'required|string',
            'carrera_id' => 'required|exists:carreras,id',
            'turno_id' => 'required|exists:turnos,id',
            'voucher_secuencia' => 'required',
            'foto' => 'required|file|image|max:2048',
            'dni_pdf' => 'required|file|mimes:pdf|max:5120',
            'certificado_estudios' => 'required|file|mimes:pdf|max:5120',
            'voucher_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        // Validación específica según tipo de documento (DNI = 1)
        if ($request->estudiante_tipo_documento == '1') {
            $rules['estudiante_dni'] = 'required|digits:8';
        } else {
            $rules['estudiante_dni'] = 'required|string|min:6|max:15';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $cicloActivo = Ciclo::where('es_activo', 1)->where('programa_id', 1)->firstOrFail();
            $rolPostulante = Role::where('nombre', 'postulante')->first();
            $rolPadre = Role::where('nombre', 'padre')->first();

            // 1. Crear o Actualizar Estudiante
            $estudiante = User::where('numero_documento', $request->estudiante_dni)->first();
            
            if (!$estudiante) {
                // Verificar si el email ya existe
                $emailExistente = User::where('email', $request->estudiante_email)->first();
                
                if ($emailExistente) {
                    throw new \Exception(
                        "El correo electrónico '{$request->estudiante_email}' ya está registrado en el sistema. " .
                        "Si ya te postulaste anteriormente, por favor contacta con el administrador."
                    );
                }
                
                $estudiante = new User();
                $estudiante->tipo_documento = $request->estudiante_tipo_documento;
                $estudiante->numero_documento = $request->estudiante_dni;
                $estudiante->nombre = $request->estudiante_nombre;
                $estudiante->apellido_paterno = $request->estudiante_apellido_paterno;
                $estudiante->apellido_materno = $request->estudiante_apellido_materno;
                $estudiante->username = $this->generateUsername($request->estudiante_nombre, $request->estudiante_apellido_paterno);
                $estudiante->password_hash = Hash::make($request->estudiante_password ?? $request->estudiante_dni);
                $estudiante->email = $request->estudiante_email;
                $estudiante->estado = true; 
            } else {
                // Si el estudiante existe pero el email es diferente, verificar que el nuevo email no esté en uso
                if ($estudiante->email !== $request->estudiante_email) {
                    $emailExistente = User::where('email', $request->estudiante_email)
                        ->where('id', '!=', $estudiante->id)
                        ->first();
                    
                    if ($emailExistente) {
                        throw new \Exception(
                            "El correo electrónico '{$request->estudiante_email}' ya está registrado por otro usuario."
                        );
                    }
                    $estudiante->email = $request->estudiante_email;
                }

                // Siempre actualizar nombres y tipo de documento por si acaso
                $estudiante->tipo_documento = $request->estudiante_tipo_documento;
                $estudiante->nombre = $request->estudiante_nombre;
                $estudiante->apellido_paterno = $request->estudiante_apellido_paterno;
                $estudiante->apellido_materno = $request->estudiante_apellido_materno;
                $estudiante->telefono = $request->estudiante_telefono;
                $estudiante->direccion = $request->estudiante_direccion;
                $estudiante->genero = $request->estudiante_genero;
                $estudiante->fecha_nacimiento = $request->estudiante_fecha_nacimiento;
            }
            
            $estudiante->nombre = $request->estudiante_nombre;
            $estudiante->apellido_paterno = $request->estudiante_apellido_paterno;
            $estudiante->apellido_materno = $request->estudiante_apellido_materno;
            $estudiante->fecha_nacimiento = $request->estudiante_fecha_nacimiento;
            $estudiante->genero = $request->estudiante_genero;
            $estudiante->telefono = $request->estudiante_telefono;
            $estudiante->direccion = $request->estudiante_direccion;
            $estudiante->tipo_documento = 'DNI';
            $estudiante->save();

            // Asignar rol si es nuevo
            if ($rolPostulante && !$estudiante->roles()->where('rol_id', $rolPostulante->id)->exists()) {
                $estudiante->roles()->attach($rolPostulante->id, ['fecha_asignacion' => now()]);
            }

            // 2. Procesar Padre (si se proporcionó DNI)
            if ($request->filled('padre_dni')) {
                $this->procesarPadre($request, 'padre', $estudiante, $rolPadre);
            }

            // 3. Procesar Madre (si se proporcionó DNI)
            if ($request->filled('madre_dni')) {
                $this->procesarPadre($request, 'madre', $estudiante, $rolPadre);
            }

            // 4. Crear Postulación (NO Inscripción)
            $postulacion = new Postulacion();
            $postulacion->estudiante_id = $estudiante->id;
            $postulacion->ciclo_id = $cicloActivo->id;
            $postulacion->carrera_id = $request->carrera_id;
            $postulacion->turno_id = $request->turno_id;
            $postulacion->centro_educativo_id = $request->centro_educativo_id;
            $postulacion->anio_egreso = $request->anio_egreso;
            $postulacion->tipo_inscripcion = $request->tipo_inscripcion ?? 'Regular';
            $postulacion->estado = 'pendiente';
            $postulacion->fecha_postulacion = now();
            
            // Generar código (Respetando el Correlativo Inicial del Ciclo)
            $correlativoBase = $cicloActivo->correlativo_inicial ?? 100000;
            
            // Buscar el último correlativo para ESTE ciclo específicamente
            $ultimoDelCiclo = Postulacion::where('ciclo_id', $cicloActivo->id)->max('codigo_postulante');
            
            if (!$ultimoDelCiclo) {
                // Si es el primero del ciclo, iniciamos en el correlativo configurado
                $nuevoCodigo = $correlativoBase;
            } else {
                // Si ya existen, incrementamos el último
                $nuevoCodigo = $ultimoDelCiclo + 1;
            }

            // Asegurar unicidad global por si acaso hubo solapamientos con ciclos antiguos
            while (Postulacion::where('codigo_postulante', $nuevoCodigo)->exists()) {
                $nuevoCodigo++;
            }
            $postulacion->codigo_postulante = $nuevoCodigo;

            // Datos del voucher
            $postulacion->numero_recibo = $request->voucher_secuencia; 
            $postulacion->fecha_emision_voucher = $request->fecha_emision_voucher;
            $postulacion->monto_matricula = $request->monto_matricula;
            $postulacion->monto_ensenanza = $request->monto_ensenanza;
            
            // 5. Subir Archivos (Usando rutas de Postulacion)
            $uploadPath = 'postulaciones/' . $cicloActivo->codigo . '/' . $estudiante->numero_documento;

            if ($request->hasFile('foto')) {
                $postulacion->foto_path = $request->file('foto')->store($uploadPath . '/fotos', 'public');
                $estudiante->foto_perfil = $postulacion->foto_path;
                $estudiante->save();
            }
            if ($request->hasFile('dni_pdf')) {
                $postulacion->dni_path = $request->file('dni_pdf')->store($uploadPath . '/documentos', 'public');
            }
            if ($request->hasFile('certificado_estudios')) {
                $postulacion->certificado_estudios_path = $request->file('certificado_estudios')->store($uploadPath . '/documentos', 'public');
            }
            if ($request->hasFile('voucher_pago')) {
                $postulacion->voucher_path = $request->file('voucher_pago')->store($uploadPath . '/pagos', 'public');
            }
            if ($request->hasFile('carta_compromiso')) {
                $postulacion->carta_compromiso_path = $request->file('carta_compromiso')->store($uploadPath . '/documentos', 'public');
            }
            if ($request->hasFile('constancia_estudios')) {
                $postulacion->constancia_estudios_path = $request->file('constancia_estudios')->store($uploadPath . '/documentos', 'public');
            }

            $postulacion->save();

            // Disparar Evento en Tiempo Real (Reverb)
            $nombreCarrera = \App\Models\Carrera::find($request->carrera_id)->nombre ?? 'Carrera';
            \App\Events\NuevaPostulacionCreada::dispatch($estudiante->nombre . ' ' . $estudiante->apellido_paterno, $nombreCarrera, $estudiante->numero_documento, null, $postulacion->foto_path, 'cepre');

            // Notificar a administradores (Base de Datos + Campana)
            $admins = \App\Models\User::whereHas('roles', function($q) {
                $q->where('nombre', 'admin');
            })->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NuevaPostulacionDatabaseNotification($estudiante->nombre . ' ' . $estudiante->apellido_paterno, $nombreCarrera));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Postulación registrada correctamente! Tu código es: ' . $postulacion->codigo_postulante . '. Para acceder al portal del estudiante, usa tu email y tu DNI como contraseña.',
                'postulacion_id' => $postulacion->id,
                'codigo_postulante' => $postulacion->codigo_postulante
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en postulación pública: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la postulación: ' . $e->getMessage()
            ], 500);
        }
    }

    private function procesarPadre($request, $tipo, $estudiante, $rolPadre)
    {
        $dni = $request->input("{$tipo}_dni");
        $nombre = $request->input("{$tipo}_nombre");
        $apellidos = $request->input("{$tipo}_apellidos");
        $telefono = $request->input("{$tipo}_telefono");
        $ocupacion = $request->input("{$tipo}_ocupacion");
        $email = $request->input("{$tipo}_email");

        if (!$dni) return;

        // Buscar padre por DNI primero
        $padre = User::where('numero_documento', $dni)->first();

        if (!$padre) {
            // Si se proporciona email, verificar que no exista
            if ($email) {
                $emailExistente = User::where('email', $email)->first();
                if ($emailExistente) {
                    // Si el email existe, usar ese usuario como padre
                    $padre = $emailExistente;
                }
            }
            
            // Si aún no hay padre, crear uno nuevo
            if (!$padre) {
                // Separar apellidos
                $parts = explode(' ', $apellidos, 2);
                $paterno = $parts[0] ?? $apellidos;
                $materno = $parts[1] ?? '';

                $padre = User::create([
                    'username' => $this->generateUsername($nombre, $paterno),
                    'email' => $email ?? "{$tipo}_{$dni}@sistema.edu",
                    'password_hash' => Hash::make($dni),
                    'nombre' => $nombre,
                    'apellido_paterno' => $paterno,
                    'apellido_materno' => $materno,
                    'tipo_documento' => 'DNI',
                    'numero_documento' => $dni,
                    'telefono' => $telefono,
                    'ocupacion' => $ocupacion,
                    'genero' => $tipo === 'padre' ? 'M' : 'F',
                    'estado' => true
                ]);

                if ($rolPadre) {
                    $padre->roles()->attach($rolPadre->id, ['fecha_asignacion' => now()]);
                }
            }
        } else {
            // Actualizar datos si existen
            $updateData = [
                'telefono' => $telefono,
                'ocupacion' => $ocupacion,
            ];
            
            // Solo actualizar email si se proporciona y es diferente
            if ($email && $padre->email !== $email) {
                // Verificar que el nuevo email no esté en uso por otro usuario
                $emailExistente = User::where('email', $email)
                    ->where('id', '!=', $padre->id)
                    ->first();
                
                if (!$emailExistente) {
                    $updateData['email'] = $email;
                } else {
                    Log::warning("No se pudo actualizar email de {$tipo} porque {$email} ya está en uso");
                }
            }
            
            $padre->update($updateData);
        }

        // Crear Parentesco
        Parentesco::firstOrCreate(
            [
                'estudiante_id' => $estudiante->id,
                'padre_id' => $padre->id
            ],
            [
                'tipo_parentesco' => ucfirst($tipo),
                'acceso_portal' => true,
                'recibe_notificaciones' => true,
                'contacto_emergencia' => true,
                'estado' => true
            ]
        );
    }

    private function generateUsername($nombre, $apellido)
    {
        $baseUsername = strtolower(substr($nombre, 0, 1) . $apellido);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);
        
        $username = $baseUsername;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * API: Obtener departamentos para select
     */
    public function getDepartamentos()
    {
        try {
            $departamentos = Cache::remember('departamentos_list', 86400, function () {
                return CentroEducativo::getDepartamentos();
            });
            
            // Modificación: Mapear a un array de objetos con 'id' y 'nombre'
            $departamentosFormatoCorrecto = $departamentos->map(function($departamento) {
                return [
                    'id' => $departamento,
                    'nombre' => $departamento
                ];
            });

            return response()->json([
                'success' => true,
                'departamentos' => $departamentosFormatoCorrecto
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener departamentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener provincias por departamento
     */
    public function getProvincias($departamento)
    {
        try {
            $provincias = CentroEducativo::getProvincias($departamento);

            // Modificación: Mapear a un array de objetos con 'id' y 'nombre'
            $provinciasFormatoCorrecto = $provincias->map(function($provincia) {
                return [
                    'id' => $provincia,
                    'nombre' => $provincia
                ];
            });
            
            return response()->json([
                'success' => true,
                'provincias' => $provinciasFormatoCorrecto
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener provincias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener distritos por departamento y provincia
     */
    public function getDistritos($departamento, $provincia)
    {
        try {
            $distritos = CentroEducativo::getDistritos($departamento, $provincia);

            // Modificación: Mapear a un array de objetos con 'id' y 'nombre'
            $distritosFormatoCorrecto = $distritos->map(function($distrito) {
                return [
                    'id' => $distrito,
                    'nombre' => $distrito
                ];
            });
            
            return response()->json([
                'success' => true,
                'distritos' => $distritosFormatoCorrecto
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener distritos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Buscar colegios
     */
    public function buscarColegios(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'departamento' => 'required|string',
                'provincia' => 'required|string',
                'distrito' => 'required|string',
                'termino' => 'nullable|string|min:2'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $colegios = CentroEducativo::buscarColegios(
                $request->departamento,
                $request->provincia,
                $request->distrito,
                $request->termino
            );
            
            return response()->json([
                'success' => true,
                'colegios' => $colegios->map(function($colegio) {
                    return [
                        'id' => $colegio->id,
                        'nombre' => $colegio->cen_edu,
                        'nivel' => $colegio->d_niv_mod,
                        'direccion' => $colegio->dir_cen
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar colegios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar Pack de Inscripción Institucional para CEPRE (POSTULACIÓN)
     */
    public function downloadRegistrationPack(Request $request)
    {
        try {
            $data = $request->all();

            // Obtener ciclo activo para el PDF
            $cicloActivo = \App\Models\Ciclo::where('es_activo', 1)->first();
            $cicloNombre = $cicloActivo ? $cicloActivo->nombre : date('Y');

            // Calcular edad si hay fecha de nacimiento
            $edad = $data['edad'] ?? '_____';
            if (!empty($data['fecha_nacimiento'])) {
                try {
                    $fechaNac = \Carbon\Carbon::parse($data['fecha_nacimiento']);
                    $edad = $fechaNac->age;
                } catch (\Exception $e) {
                    // Mantener la edad del request si falla el parseo
                }
            }

            // Capturar DNI (puede venir como check_dni, estudiante_dni o dni)
            $estudianteDni = $data['check_dni'] ?? ($data['estudiante_dni'] ?? ($data['dni'] ?? ''));

            // Obtener carrera y turno si están presentes (Solo CEPRE)
            $carreraNombre = '';
            if (!empty($data['carrera_id'])) {
                $carrera = \App\Models\Carrera::find($data['carrera_id']);
                $carreraNombre = $carrera ? $carrera->nombre : '';
            }

            $turnoNombre = '';
            if (!empty($data['turno_id'])) {
                $turno = \App\Models\Turno::find($data['turno_id']);
                $turnoNombre = $turno ? $turno->nombre : '';
            }

            // Calcular edad basándose en fecha de nacimiento (Solo si es una fecha lógica)
            $fechaNac = $data['estudiante_fecha_nacimiento'] ?? $data['fecha_nacimiento'] ?? null;
            $edad = '____';
            if ($fechaNac && strlen($fechaNac) > 6) {
                try {
                    $birthDate = \Carbon\Carbon::parse($fechaNac);
                    if ($birthDate->year > 1900 && $birthDate->year <= date('Y')) {
                        $edad = $birthDate->age;
                    }
                } catch (\Exception $e) {}
            }

            // Mapear campos si vienen con nombres diferentes desde el frontend
            // Intentar capturar nombres con los prefijos correctos 'estudiante_' como vienen del modal
            $nombreOriginal = $data['estudiante_nombre'] ?? $data['nombre'] ?? '';
            $paternoOriginal = $data['estudiante_apellido_paterno'] ?? $data['apellido_paterno'] ?? '';
            $maternoOriginal = $data['estudiante_apellido_materno'] ?? $data['apellido_materno'] ?? '';

            $pdfData = [
                'estudiante_nombre' => trim($nombreOriginal . ' ' . $paternoOriginal . ' ' . $maternoOriginal),
                'estudiante_dni' => $estudianteDni,
                'estudiante_edad' => $edad,
                'apoderado_nombre' => '',
                'apoderado_dni' => '',
                'apoderado_celular' => '',
                'apoderado_email' => $request->input('apoderado_email', $request->input('estudiante_email', $request->input('email', ''))),
                'apoderado_direccion' => $request->input('apoderado_direccion', $request->input('estudiante_direccion', $request->input('direccion', ''))),
                'apoderado_parentesco' => 'Padre/Madre',
                'programa_id' => 1, // CEPRE por defecto en este controlador
                'ciclo_nombre' => $cicloNombre,
                'carrera_nombre' => $carreraNombre,
                'turno_nombre' => $turnoNombre
            ];

            // Si el nombre resultante está vacío (posiblemente por inputs mal nombrados), fallback de seguridad
            if (empty($pdfData['estudiante_nombre'])) {
                $pdfData['estudiante_nombre'] = $data['estudiante_nombre'] ?? $data['nombre'] ?? 'Estudiante';
            }

            // Prioridad al Padre, luego Madre para el Pack de Inscripción
            if (!empty($data['padre_nombre'])) {
                $pdfData['apoderado_nombre'] = trim($data['padre_nombre'] . ' ' . ($data['padre_apellidos'] ?? ''));
                $pdfData['apoderado_dni'] = $data['padre_dni'] ?? '';
                $pdfData['apoderado_celular'] = $data['padre_telefono'] ?? '';
                $pdfData['apoderado_email'] = $data['padre_email'] ?? '';
                $pdfData['apoderado_direccion'] = $data['apoderado_direccion'] ?? $data['estudiante_direccion'] ?? $data['direccion'] ?? '';
                $pdfData['apoderado_parentesco'] = 'Padre';
            } elseif (!empty($data['madre_nombre'])) {
                $pdfData['apoderado_nombre'] = trim($data['madre_nombre'] . ' ' . ($data['madre_apellidos'] ?? ''));
                $pdfData['apoderado_dni'] = $data['madre_dni'] ?? '';
                $pdfData['apoderado_celular'] = $data['madre_telefono'] ?? '';
                $pdfData['apoderado_email'] = $data['madre_email'] ?? '';
                $pdfData['apoderado_direccion'] = $data['apoderado_direccion'] ?? $data['estudiante_direccion'] ?? $data['direccion'] ?? '';
                $pdfData['apoderado_parentesco'] = 'Madre';
            }

            // Si aún no hay datos, intentar buscarlos en la base de datos por DNI
            if (empty($pdfData['estudiante_nombre']) || $pdfData['estudiante_nombre'] == '') {
                $dniConsultar = $pdfData['estudiante_dni'];
                if ($dniConsultar) {
                    $user = \App\Models\User::where('numero_documento', $dniConsultar)->first();
                    if ($user) {
                        $pdfData['estudiante_nombre'] = $user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno;
                        $pdfData['estudiante_dni'] = $user->numero_documento;
                        
                        // Si no hay apoderado en el request, buscarlo en parentescos
                        if (empty($pdfData['apoderado_nombre'])) {
                            $parentesco = \App\Models\Parentesco::where('estudiante_id', $user->id)->with('padre')->first();
                            if ($parentesco && $parentesco->padre) {
                                $p = $parentesco->padre;
                                $pdfData['apoderado_nombre'] = $p->nombre . ' ' . $p->apellido_paterno . ' ' . $p->apellido_materno;
                                $pdfData['apoderado_dni'] = $p->numero_documento;
                                $pdfData['apoderado_celular'] = $p->telefono;
                                $pdfData['apoderado_direccion'] = $p->direccion;
                                $pdfData['apoderado_parentesco'] = $parentesco->tipo_parentesco ?? 'Tutor';
                            }
                        }
                    }
                }
            }

            $pdf = $this->pdfService->generateRegistrationPack($pdfData);

            return response($pdf->output())
                ->header('Content-Type', 'application/pdf');

        } catch (\Exception $e) {
            \Log::error('Error en downloadRegistrationPack: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Error al generar Pack PDF: ' . $e->getMessage()], 500);
        }
    }
}
