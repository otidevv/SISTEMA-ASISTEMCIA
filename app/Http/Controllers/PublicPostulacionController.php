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
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\CentroEducativo;
use Illuminate\Support\Facades\Validator;

class PublicPostulacionController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentValidationService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function checkPostulante(Request $request)
    {
        $request->validate([
            'dni' => 'required|numeric|digits:8',
        ]);

        $dni = $request->dni;
        $cicloActivo = Ciclo::where('es_activo', 1)->first();

        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay un ciclo activo para postulaciones.'], 400);
        }

        $estudiante = User::where('numero_documento', $dni)->first();

        if ($estudiante) {
            // Verificar si ya tiene postulación en el ciclo actual
            $postulacion = Postulacion::where('estudiante_id', $estudiante->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();

            if ($postulacion) {
                // Cargar relaciones para mostrar información completa
                $postulacion->load(['carrera', 'turno']);
                
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
                        'carrera' => $postulacion->carrera ? $postulacion->carrera->nombre : 'N/A',
                        'turno' => $postulacion->turno ? $postulacion->turno->nombre : 'N/A',
                        'fecha_postulacion' => $postulacion->fecha_postulacion ? $postulacion->fecha_postulacion->format('d/m/Y') : 'N/A',
                    ]
                ]);
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

            return response()->json([
                'status' => 'recurrent',
                'estudiante' => $estudiante,
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
        // Validación básica
        $request->validate([
            'estudiante_dni' => 'required|digits:8',
            'estudiante_nombre' => 'required|string',
            'estudiante_apellido_paterno' => 'required|string',
            'estudiante_apellido_materno' => 'required|string',
            'carrera_id' => 'required|exists:carreras,id',
            'turno_id' => 'required|exists:turnos,id',
            'voucher_secuencia' => 'required',
            // Archivos
            'foto' => 'required|file|image|max:2048',
            'dni_pdf' => 'required|file|mimes:pdf|max:5120',
            'certificado_estudios' => 'required|file|mimes:pdf|max:5120',
            'voucher_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $cicloActivo = Ciclo::where('es_activo', 1)->firstOrFail();
            $rolPostulante = Role::where('nombre', 'postulante')->first();
            $rolPadre = Role::where('nombre', 'padre')->first();

            // 1. Crear o Actualizar Estudiante
            $estudiante = User::where('numero_documento', $request->estudiante_dni)->first();
            
            if (!$estudiante) {
                $estudiante = new User();
                $estudiante->numero_documento = $request->estudiante_dni;
                $estudiante->username = $this->generateUsername($request->estudiante_nombre, $request->estudiante_apellido_paterno);
                $estudiante->password_hash = Hash::make($request->estudiante_password ?? $request->estudiante_dni);
                $estudiante->email = $request->estudiante_email;
                $estudiante->estado = true; // Activo como usuario, pero postulación pendiente
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
            
            // Generar código
            $ultimoCodigo = Postulacion::max('codigo_postulante') ?? 1000000;
            $nuevoCodigo = $ultimoCodigo + 1;
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

        $padre = User::where('numero_documento', $dni)->first();

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
        } else {
            // Actualizar datos si existen
            $padre->update([
                'telefono' => $telefono,
                'ocupacion' => $ocupacion,
                'email' => $email ?? $padre->email
            ]);
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
            $departamentos = CentroEducativo::getDepartamentos();
            
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
}
