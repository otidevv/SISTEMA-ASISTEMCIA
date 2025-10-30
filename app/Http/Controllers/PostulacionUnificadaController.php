<?php

namespace App\Http\Controllers;

use App\Models\Ciclo;
use App\Models\Carrera;
use App\Models\Turno;
use App\Models\CentroEducativo;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Parentesco;
use App\Models\CicloCarreraVacante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostulacionUnificadaController extends Controller
{
    /**
     * Mostrar formulario unificado de postulación
     */
    public function create()
    {
        // Verificar si hay ciclo activo
        $cicloActivo = Ciclo::activo()->first();
        
        if (!$cicloActivo) {
            return redirect()->route('dashboard')->with('error', 'No hay ciclo activo para postulaciones');
        }

        // Verificar si el usuario ya tiene una postulación en el ciclo actual
        $postulacionExistente = Postulacion::where('estudiante_id', Auth::id())
            ->where('ciclo_id', $cicloActivo->id)
            ->first();

        if ($postulacionExistente) {
            return redirect()->route('dashboard')->with('info', 'Ya tienes una postulación en proceso para este ciclo');
        }

        // Obtener carreras con vacantes disponibles
        $carreras = CicloCarreraVacante::with('carrera')
            ->where('ciclo_id', $cicloActivo->id)
            ->where('estado', true)
            ->where(function($query) {
                $query->where('vacantes_total', 0) // Sin límite
                      ->orWhereRaw('vacantes_total > (vacantes_ocupadas + vacantes_reservadas)'); // Con vacantes disponibles
            })
            ->get()
            ->map(function($vacante) {
                return [
                    'id' => $vacante->carrera_id,
                    'nombre' => $vacante->carrera->nombre,
                    'codigo' => $vacante->carrera->codigo,
                    'vacantes_disponibles' => $vacante->vacantes_total == 0 ? 
                        'Sin límite' : $vacante->vacantes_disponibles
                ];
            });

        // Obtener turnos disponibles
        $turnos = Turno::where('estado', true)
            ->select('id', 'nombre', 'hora_inicio', 'hora_fin')
            ->get();

        // Obtener datos del usuario actual (si los tiene)
        $usuario = Auth::user();

        return view('postulaciones.create-unificado', compact(
            'cicloActivo', 
            'carreras', 
            'turnos', 
            'usuario'
        ));
    }

    /**
     * Procesar y guardar postulación unificada
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Verificar si es postulante existente (tiene estudiante_id)
            if ($request->has('estudiante_id') && $request->estudiante_id) {
                return $this->storePostulacionExistente($request);
            }
            
            // Si no tiene estudiante_id, es un flujo de creación completa (registro + postulación)
            // Determinar el ID del estudiante para las reglas de validación 'unique'
            $estudianteId = null;
            if ($request->input('estudiante_dni')) {
                $existingUser = User::where('numero_documento', $request->input('estudiante_dni'))->first();
                if ($existingUser) {
                    $estudianteId = $existingUser->id;
                }
            }

            $validator = Validator::make($request->all(), [
                // Datos del estudiante
                'estudiante_nombre' => 'required|string|max:100',
                'estudiante_apellido_paterno' => 'required|string|max:100',
                'estudiante_apellido_materno' => 'required|string|max:100',
                'estudiante_dni' => 'required|string|size:8|unique:users,numero_documento,' . $estudianteId,
                'estudiante_email' => 'required|email|unique:users,email,' . $estudianteId,
            'estudiante_telefono' => 'required|string|max:15',
            'estudiante_fecha_nacimiento' => 'required|date|before:today',
            'estudiante_genero' => 'required|in:M,F',
            'estudiante_direccion' => 'required|string|max:255',
            'estudiante_password' => 'required|string|min:8|confirmed',
            'estudiante_password_confirmation' => 'required|string|min:8',

            // Datos del padre
            'padre_nombre' => 'required|string|max:100',
            'padre_apellido_paterno' => 'required|string|max:100',
            'padre_apellido_materno' => 'nullable|string|max:100',
            'padre_dni' => 'required|string|size:8',
            'padre_telefono' => 'required|string|max:15',
            'padre_email' => 'nullable|email',
            'padre_ocupacion' => 'nullable|string|max:100',

            // Datos de la madre
            'madre_nombre' => 'required|string|max:100',
            'madre_apellido_paterno' => 'required|string|max:100',
            'madre_apellido_materno' => 'nullable|string|max:100',
            'madre_dni' => 'required|string|size:8',
            'madre_telefono' => 'required|string|max:15',
            'madre_email' => 'nullable|email',
            'madre_ocupacion' => 'nullable|string|max:100',

            // Datos académicos
            'tipo_inscripcion' => 'required|in:postulante,reforzamiento',
            'carrera_id' => 'required|exists:carreras,id',
            'turno_id' => 'required|exists:turnos,id',
            'centro_educativo_id' => 'required',

            // Documentos obligatorios
            'voucher_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'certificado_estudios' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'carta_compromiso' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'constancia_estudios' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'dni_documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'foto_carnet' => 'required|file|mimes:jpg,jpeg,png|max:2048',

            // Datos del voucher
            'numero_recibo' => 'required|string|max:50',
            'fecha_emision_voucher' => 'required|date',
            'monto_matricula' => 'required|numeric|min:0',
            'monto_ensenanza' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $cicloActivo = Ciclo::activo()->first();
            
            if (!$cicloActivo) {
                throw new \Exception('No hay ciclo activo para postulación');
            }

            // 1. Buscar o crear al estudiante
            $estudiante = User::firstOrNew(['numero_documento' => $request->estudiante_dni]);
            
            $estudianteData = [
                'nombre' => $request->estudiante_nombre,
                'apellido_paterno' => $request->estudiante_apellido_paterno,
                'apellido_materno' => $request->estudiante_apellido_materno,
                'email' => $request->estudiante_email,
                'telefono' => $request->estudiante_telefono,
                'fecha_nacimiento' => $request->estudiante_fecha_nacimiento,
                'genero' => $request->estudiante_genero,
                'direccion' => $request->estudiante_direccion,
                'centro_educativo_id' => $request->centro_educativo_id,
                'username' => $estudiante->exists ? $estudiante->username : $request->estudiante_dni,
                'password_hash' => Hash::make($request->estudiante_password)
            ];

            $estudiante->fill($estudianteData)->save();

            // Asignar rol de postulante si es nuevo
            if (!$estudiante->wasRecentlyCreated && !$estudiante->hasRole('Estudiante')) {
                $estudiante->assignRole('Postulante');
            } else if($estudiante->wasRecentlyCreated) {
                $estudiante->assignRole('Postulante');
            }
            
            // 2. Crear o actualizar usuario padre
            $padre = User::firstOrNew(['numero_documento' => $request->padre_dni]);
            $padre->fill([
                'username' => $padre->exists ? $padre->username : 'padre_' . $request->padre_dni,
                'email' => $request->padre_email ?: 'padre_' . $request->padre_dni . '@temp.com',
                'password_hash' => $padre->exists ? $padre->password_hash : Hash::make(Str::random(12)), // Password temporal
                'nombre' => $request->padre_nombre,
                'apellido_paterno' => $request->padre_apellido_paterno,
                'apellido_materno' => $request->padre_apellido_materno,
                'telefono' => $request->padre_telefono,
                'tipo_documento' => 'DNI',
                'estado' => true
            ])->save();

            // Asignar rol de padre
            if (!$padre->hasRole('Padre')) {
                $padre->assignRole('Padre');
            }

            // 3. Crear o actualizar usuario madre
            $madre = User::firstOrNew(['numero_documento' => $request->madre_dni]);
            $madre->fill([
                'username' => $madre->exists ? $madre->username : 'madre_' . $request->madre_dni,
                'email' => $request->madre_email ?: 'madre_' . $request->madre_dni . '@temp.com',
                'password_hash' => $madre->exists ? $madre->password_hash : Hash::make(Str::random(12)), // Password temporal
                'nombre' => $request->madre_nombre,
                'apellido_paterno' => $request->madre_apellido_paterno,
                'apellido_materno' => $request->madre_apellido_materno,
                'telefono' => $request->madre_telefono,
                'tipo_documento' => 'DNI',
                'estado' => true
            ])->save();

            // Asignar rol de madre
            if (!$madre->hasRole('Madre')) {
                $madre->assignRole('Madre');
            }

            // 4. Crear o actualizar relaciones de parentesco
            Parentesco::updateOrCreate(
                ['estudiante_id' => $estudiante->id, 'padre_id' => $padre->id],
                [
                    'tipo_parentesco' => 'Padre',
                    'acceso_portal' => true,
                    'recibe_notificaciones' => true,
                    'contacto_emergencia' => true,
                    'estado' => true
                ]
            );

            Parentesco::updateOrCreate(
                ['estudiante_id' => $estudiante->id, 'padre_id' => $madre->id],
                [
                    'tipo_parentesco' => 'Madre',
                    'acceso_portal' => true,
                    'recibe_notificaciones' => true,
                    'contacto_emergencia' => true,
                    'estado' => true
                ]
            );

            // 5. Subir documentos
            $userIdentifier = $request->estudiante_dni;
            $uploadPath = 'inscripciones/' . $cicloActivo->codigo . '/' . $userIdentifier;
            
            $documentPaths = [];
            
            // Voucher de pago
            if ($request->hasFile('voucher_pago')) {
                $documentPaths['voucher_path'] = $request->file('voucher_pago')
                    ->store($uploadPath . '/voucher', 'public');
            }
            
            // Certificado de estudios
            if ($request->hasFile('certificado_estudios')) {
                $documentPaths['certificado_estudios_path'] = $request->file('certificado_estudios')
                    ->store($uploadPath . '/certificados', 'public');
            }
            
            // Carta de compromiso
            if ($request->hasFile('carta_compromiso')) {
                $documentPaths['carta_compromiso_path'] = $request->file('carta_compromiso')
                    ->store($uploadPath . '/carta', 'public');
            }
            
            // Constancia de estudios
            if ($request->hasFile('constancia_estudios')) {
                $documentPaths['constancia_estudios_path'] = $request->file('constancia_estudios')
                    ->store($uploadPath . '/constancia', 'public');
            }
            
            // DNI
            if ($request->hasFile('dni_documento')) {
                $documentPaths['dni_path'] = $request->file('dni_documento')
                    ->store($uploadPath . '/dni', 'public');
            }
            
            // Foto carnet
            if ($request->hasFile('foto_carnet')) {
                $documentPaths['foto_path'] = $request->file('foto_carnet')
                    ->store($uploadPath . '/foto', 'public');
                
                // También guardar como foto de perfil del usuario
                $estudiante->foto_perfil = $documentPaths['foto_path'];
                $estudiante->save();
            }

            // 6. Crear postulación
            $montoTotal = $request->monto_matricula + $request->monto_ensenanza;
            
            $datosPostulacion = [
                'estudiante_id' => $estudiante->id,
                'ciclo_id' => $cicloActivo->id,
                'carrera_id' => $request->carrera_id,
                'turno_id' => $request->turno_id,
                'tipo_inscripcion' => $request->tipo_inscripcion,
                'centro_educativo_id' => $request->centro_educativo_id,
                'fecha_postulacion' => now(),
                'estado' => 'pendiente',
                // Datos del voucher
                'numero_recibo' => $request->numero_recibo,
                'fecha_emision_voucher' => $request->fecha_emision_voucher,
                'monto_matricula' => $request->monto_matricula,
                'monto_ensenanza' => $request->monto_ensenanza,
                'monto_total_pagado' => $montoTotal,
                'documentos_verificados' => false,
                'pago_verificado' => false,
                'created_by' => Auth::id() // Guardar el ID del admin que crea el registro
            ];
            
            // Agregar documentos al array
            foreach ($documentPaths as $key => $value) {
                $datosPostulacion[$key] = $value;
            }
            
            // Generar código postulante correlativo único
            $ultimoCodigo = Postulacion::max('codigo_postulante') ?? 1000000;
            $nuevoCodigo = $ultimoCodigo + 1;
            
            // Asegurar que sea único
            while (Postulacion::where('codigo_postulante', $nuevoCodigo)->exists()) {
                $nuevoCodigo++;
            }
            
            $datosPostulacion['codigo_postulante'] = $nuevoCodigo;
            
            $postulacion = Postulacion::create($datosPostulacion);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Postulación unificada enviada exitosamente. Se encuentra pendiente de aprobación.',
                'postulacion' => [
                    'id' => $postulacion->id,
                    'codigo_postulante' => $postulacion->codigo_postulante,
                    'tipo' => $postulacion->tipo_inscripcion,
                    'carrera' => $postulacion->carrera->nombre,
                    'turno' => $postulacion->turno->nombre,
                    'fecha' => $postulacion->fecha_postulacion,
                    'estado' => 'pendiente'
                ],
                'redirect' => route('dashboard')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
        
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la postulación: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Mostrar formulario unificado de postulación para modal (sin layout)
     */
    public function createModal()
    {
        // Verificar si hay ciclo activo
        $cicloActivo = Ciclo::activo()->first();
        
        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay ciclo activo para postulaciones'], 400);
        }

        // Obtener carreras con vacantes disponibles
        $carreras = CicloCarreraVacante::with('carrera')
            ->where('ciclo_id', $cicloActivo->id)
            ->where('estado', true)
            ->where(function($query) {
                $query->where('vacantes_total', 0) // Sin límite
                      ->orWhereRaw('vacantes_total > (vacantes_ocupadas + vacantes_reservadas)'); // Con vacantes disponibles
            })
            ->get()
            ->map(function($vacante) {
                return [
                    'id' => $vacante->carrera_id,
                    'nombre' => $vacante->carrera->nombre,
                    'codigo' => $vacante->carrera->codigo,
                    'vacantes_disponibles' => $vacante->vacantes_total == 0 ? 
                        'Sin límite' : $vacante->vacantes_disponibles
                ];
            });

        // Obtener turnos disponibles
        $turnos = Turno::where('estado', true)
            ->select('id', 'nombre', 'hora_inicio', 'hora_fin')
            ->get();

        // Obtener datos del usuario actual (si los tiene)
        $usuario = Auth::user();

        return view('postulaciones.create-modal', compact(
            'cicloActivo', 
            'carreras', 
            'turnos', 
            'usuario'
        ));
    }
    
    /**
     * Procesar postulación para usuario existente (simplificado)
     */
    private function storePostulacionExistente(Request $request)
    {
        DB::beginTransaction();
        try {
            // Validar solo los campos necesarios para postulación existente
            $validator = Validator::make($request->all(), [
                'estudiante_id' => 'required|exists:users,id',
                'carrera_id' => 'required|exists:carreras,id',
                'turno_id' => 'required|exists:turnos,id',
                'tipo_inscripcion' => 'required|in:postulante,reforzamiento',
                'colegio_procedencia' => 'required|string|max:255',
                'año_egreso' => 'required|integer|min:1990|max:' . date('Y'),
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'certificado_estudios' => 'required|mimes:pdf|max:5120',
                'voucher_pago' => 'required|mimes:pdf|max:5120',
                'dni_pdf' => 'nullable|mimes:pdf|max:5120',

                // Datos del padre (pueden ser opcionales si ya existen)
                'padre_nombre' => 'nullable|string|max:100',
                'padre_apellido_paterno' => 'nullable|string|max:100',
                'padre_dni' => 'nullable|string|size:8',
                'padre_telefono' => 'nullable|string|max:15',

                // Datos de la madre (pueden ser opcionales si ya existen)
                'madre_nombre' => 'nullable|string|max:100',
                'madre_apellido_paterno' => 'nullable|string|max:100',
                'madre_dni' => 'nullable|string|size:8',
                'madre_telefono' => 'nullable|string|max:15',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $estudiante = User::find($request->estudiante_id);
            if (!$estudiante) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            // Procesar y guardar padres
            if ($request->filled('padre_dni')) {
                $padre = User::firstOrCreate(
                    ['numero_documento' => $request->padre_dni],
                    [
                        'nombre' => $request->padre_nombre,
                        'apellido_paterno' => $request->padre_apellido_paterno,
                        'apellido_materno' => $request->padre_apellido_materno ?? ''
                    ]
                );
                Parentesco::updateOrCreate(
                    ['estudiante_id' => $estudiante->id, 'padre_id' => $padre->id],
                    ['tipo_parentesco' => 'padre']
                );
            }

            if ($request->filled('madre_dni')) {
                $madre = User::firstOrCreate(
                    ['numero_documento' => $request->madre_dni],
                    [
                        'nombre' => $request->madre_nombre,
                        'apellido_paterno' => $request->madre_apellido_paterno,
                        'apellido_materno' => $request->madre_apellido_materno ?? ''
                    ]
                );
                Parentesco::updateOrCreate(
                    ['estudiante_id' => $estudiante->id, 'padre_id' => $madre->id],
                    ['tipo_parentesco' => 'madre']
                );
            }
            
            $cicloActivo = Ciclo::activo()->first();
            if (!$cicloActivo) {
                return response()->json(['success' => false, 'message' => 'No hay ciclo activo para postulaciones'], 400);
            }
            
            $postulacionExistente = Postulacion::where('estudiante_id', $estudiante->id)
                ->where('ciclo_id', $cicloActivo->id)
                ->first();
                
            if ($postulacionExistente) {
                return response()->json(['success' => false, 'message' => 'Ya existe una postulación para este ciclo'], 400);
            }
            
            $postulacion = new Postulacion($request->only([
                'carrera_id', 'turno_id', 'tipo_inscripcion', 'colegio_procedencia', 'año_egreso'
            ]));

            $postulacion->estudiante_id = $estudiante->id;
            $postulacion->ciclo_id = $cicloActivo->id;
            $postulacion->estado = 'pendiente';
            $postulacion->fecha_postulacion = now();
            $postulacion->codigo_postulante = 'POST-' . date('Y') . '-' . str_pad(Postulacion::count() + 1, 5, '0', STR_PAD_LEFT);
            $postulacion->creado_por = Auth::id();
            
            // Guardar documentos
            if ($request->hasFile('foto')) {
                $postulacion->foto = $request->file('foto')->store('postulaciones/fotos', 'public');
            }
            if ($request->hasFile('dni_pdf')) {
                $postulacion->dni_pdf = $request->file('dni_pdf')->store('postulaciones/documentos', 'public');
            }
            if ($request->hasFile('certificado_estudios')) {
                $postulacion->certificado_estudios = $request->file('certificado_estudios')->store('postulaciones/documentos', 'public');
            }
            if ($request->hasFile('voucher_pago')) {
                $postulacion->voucher_pago = $request->file('voucher_pago')->store('postulaciones/documentos', 'public');
            }
            
            $postulacion->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Postulación creada exitosamente',
                'postulacion_id' => $postulacion->id,
                'codigo_postulante' => $postulacion->codigo_postulante
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la postulación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar postulante existente por DNI
     */
    public function buscarPostulante($dni)
    {
        try {
            $postulante = User::where('numero_documento', $dni)
                ->whereHas('roles', function($query) {
                    $query->whereIn('nombre', ['postulante', 'estudiante']);
                })
                ->first();
            
            if ($postulante) {
                // Obtener padres/apoderados
                $padres = Parentesco::with('padre')->where('estudiante_id', $postulante->id)->get();

                return response()->json([
                    'success' => true,
                    'postulante' => [
                        'id' => $postulante->id,
                        'nombre' => $postulante->nombre,
                        'apellido_paterno' => $postulante->apellido_paterno,
                        'apellido_materno' => $postulante->apellido_materno,
                        'numero_documento' => $postulante->numero_documento,
                        'email' => $postulante->email,
                        'telefono' => $postulante->telefono,
                        'fecha_nacimiento' => $postulante->fecha_nacimiento,
                        'genero' => $postulante->genero,
                        'direccion' => $postulante->direccion,
                        'padres' => $padres->map(function ($p) {
                            return [
                                'id' => $p->padre->id,
                                'nombre' => $p->padre->nombre,
                                'apellido_paterno' => $p->padre->apellido_paterno,
                                'apellido_materno' => $p->padre->apellido_materno,
                                'numero_documento' => $p->padre->numero_documento,
                                'telefono' => $p->padre->telefono,
                                'tipo_parentesco' => $p->tipo_parentesco,
                            ];
                        })
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Postulante no encontrado'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar postulante: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener formulario de registro completo (registro + postulación)
     */
    public function getFormRegistro()
    {
        // Verificar si hay ciclo activo
        $cicloActivo = Ciclo::activo()->first();
        
        if (!$cicloActivo) {
            return '<div class="alert alert-danger">No hay ciclo activo para postulaciones</div>';
        }

        // Obtener carreras con vacantes disponibles
        $carreras = CicloCarreraVacante::with('carrera')
            ->where('ciclo_id', $cicloActivo->id)
            ->where('estado', true)
            ->where(function($query) {
                $query->where('vacantes_total', 0) // Sin límite
                      ->orWhereRaw('vacantes_total > (vacantes_ocupadas + vacantes_reservadas)'); // Con vacantes disponibles
            })
            ->get()
            ->map(function($vacante) {
                return [
                    'id' => $vacante->carrera_id,
                    'nombre' => $vacante->carrera->nombre,
                    'codigo' => $vacante->carrera->codigo,
                    'vacantes_disponibles' => $vacante->vacantes_total == 0 ? 
                        'Sin límite' : $vacante->vacantes_disponibles
                ];
            });

        // Obtener turnos disponibles
        $turnos = Turno::where('estado', true)
            ->select('id', 'nombre', 'hora_inicio', 'hora_fin')
            ->get();

        return view('postulaciones.form-registro-completo', compact(
            'cicloActivo', 
            'carreras', 
            'turnos'
        ));
    }
    
    /**
     * Guardar registro completo (crear usuario + postulación)
     */
    public function storeRegistroCompleto(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Validar datos del estudiante y postulación
            $validator = Validator::make($request->all(), [
                // Datos del estudiante
                'estudiante_nombre' => 'required|string|max:100',
                'estudiante_apellido_paterno' => 'required|string|max:100',
                'estudiante_apellido_materno' => 'required|string|max:100',
                'estudiante_dni' => 'required|string|size:8|unique:users,numero_documento',
                'estudiante_email' => 'required|email|unique:users,email',
                'estudiante_password' => 'required|string|min:8|confirmed',
                'estudiante_telefono' => 'required|string|max:20',
                'estudiante_fecha_nacimiento' => 'required|date',
                'estudiante_genero' => 'required|in:M,F',
                'estudiante_direccion' => 'required|string|max:255',
                
                // Datos del padre
                'padre_nombre' => 'nullable|string|max:100',
                'padre_apellido_paterno' => 'nullable|string|max:100',
                'padre_apellido_materno' => 'nullable|string|max:100',
                'padre_dni' => 'nullable|string|size:8',
                'padre_telefono' => 'nullable|string|max:20',
                
                // Datos de la madre
                'madre_nombre' => 'nullable|string|max:100',
                'madre_apellido_paterno' => 'nullable|string|max:100',
                'madre_apellido_materno' => 'nullable|string|max:100',
                'madre_dni' => 'nullable|string|size:8',
                'madre_telefono' => 'nullable|string|max:20',
                
                // Datos académicos
                'carrera_id' => 'required|exists:carreras,id',
                'turno_id' => 'required|exists:turnos,id',
                'colegio_procedencia' => 'required|string|max:255',
                'año_egreso' => 'required|integer|min:1990|max:' . date('Y'),
                
                // Documentos
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'dni_pdf' => 'required|mimes:pdf|max:5120',
                'certificado_estudios' => 'required|mimes:pdf|max:5120',
                'voucher_pago' => 'required|mimes:pdf|max:5120'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // 1. Crear el usuario estudiante
            $estudiante = new User();
            $estudiante->nombre = $request->estudiante_nombre;
            $estudiante->apellido_paterno = $request->estudiante_apellido_paterno;
            $estudiante->apellido_materno = $request->estudiante_apellido_materno;
            $estudiante->tipo_documento = 'DNI';
            $estudiante->numero_documento = $request->estudiante_dni;
            $estudiante->email = $request->estudiante_email;
            $estudiante->password = Hash::make($request->estudiante_password);
            $estudiante->telefono = $request->estudiante_telefono;
            $estudiante->fecha_nacimiento = $request->estudiante_fecha_nacimiento;
            $estudiante->genero = $request->estudiante_genero;
            $estudiante->direccion = $request->estudiante_direccion;
            $estudiante->save();
            
            // Asignar rol de postulante
            $rolPostulante = \App\Models\Role::where('nombre', 'postulante')->first();
            if ($rolPostulante) {
                $estudiante->roles()->attach($rolPostulante->id);
            }
            
            // 2. Crear usuarios para padres si se proporcionaron datos
            $padreId = null;
            $madreId = null;
            
            if ($request->padre_dni) {
                $padre = User::firstOrCreate(
                    ['numero_documento' => $request->padre_dni],
                    [
                        'nombre' => $request->padre_nombre,
                        'apellido_paterno' => $request->padre_apellido_paterno,
                        'apellido_materno' => $request->padre_apellido_materno,
                        'tipo_documento' => 'DNI',
                        'telefono' => $request->padre_telefono,
                        'email' => $request->padre_dni . '@temporal.com',
                        'password' => Hash::make($request->padre_dni)
                    ]
                );
                $padreId = $padre->id;
                
                // Asignar rol de padre si no lo tiene
                $rolPadre = \App\Models\Role::where('nombre', 'padre')->first();
                if ($rolPadre && !$padre->hasRole('padre')) {
                    $padre->roles()->attach($rolPadre->id);
                }
                
                // Crear relación de parentesco
                Parentesco::create([
                    'estudiante_id' => $estudiante->id,
                    'padre_id' => $padreId,
                    'tipo_parentesco' => 'padre'
                ]);
            }
            
            if ($request->madre_dni) {
                $madre = User::firstOrCreate(
                    ['numero_documento' => $request->madre_dni],
                    [
                        'nombre' => $request->madre_nombre,
                        'apellido_paterno' => $request->madre_apellido_paterno,
                        'apellido_materno' => $request->madre_apellido_materno,
                        'tipo_documento' => 'DNI',
                        'telefono' => $request->madre_telefono,
                        'email' => $request->madre_dni . '@temporal.com',
                        'password' => Hash::make($request->madre_dni)
                    ]
                );
                $madreId = $madre->id;
                
                // Asignar rol de padre si no lo tiene
                $rolPadre = \App\Models\Role::where('nombre', 'padre')->first();
                if ($rolPadre && !$madre->hasRole('padre')) {
                    $madre->roles()->attach($rolPadre->id);
                }
                
                // Crear relación de parentesco
                Parentesco::create([
                    'estudiante_id' => $estudiante->id,
                    'padre_id' => $madreId,
                    'tipo_parentesco' => 'madre'
                ]);
            }
            
            // 3. Crear la postulación
            $cicloActivo = Ciclo::activo()->first();
            
            $postulacion = new Postulacion();
            $postulacion->estudiante_id = $estudiante->id;
            $postulacion->ciclo_id = $cicloActivo->id;
            $postulacion->carrera_id = $request->carrera_id;
            $postulacion->turno_id = $request->turno_id;
            $postulacion->tipo_inscripcion = 'postulante';
            $postulacion->estado = 'pendiente';
            $postulacion->fecha_postulacion = now();
            $postulacion->codigo_postulante = 'POST-' . date('Y') . '-' . str_pad(Postulacion::count() + 1, 5, '0', STR_PAD_LEFT);
            $postulacion->colegio_procedencia = $request->colegio_procedencia;
            $postulacion->año_egreso = $request->año_egreso;
            $postulacion->creado_por = Auth::id();
            $postulacion->save();
            
            // 4. Guardar documentos
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('postulaciones/fotos', 'public');
                $postulacion->foto = $fotoPath;
            }
            
            if ($request->hasFile('dni_pdf')) {
                $dniPath = $request->file('dni_pdf')->store('postulaciones/documentos', 'public');
                $postulacion->dni_pdf = $dniPath;
            }
            
            if ($request->hasFile('certificado_estudios')) {
                $certificadoPath = $request->file('certificado_estudios')->store('postulaciones/documentos', 'public');
                $postulacion->certificado_estudios = $certificadoPath;
            }
            
            if ($request->hasFile('voucher_pago')) {
                $voucherPath = $request->file('voucher_pago')->store('postulaciones/documentos', 'public');
                $postulacion->voucher_pago = $voucherPath;
            }
            
            $postulacion->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Registro y postulación creados exitosamente',
                'postulacion_id' => $postulacion->id,
                'codigo_postulante' => $postulacion->codigo_postulante
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el registro: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener solo el contenido del formulario para el modal (formato Blade)
     */
    public function getFormContent(Request $request)
    {
        // Verificar si hay ciclo activo
        $cicloActivo = Ciclo::activo()->first();
        
        if (!$cicloActivo) {
            return response()->json(['error' => 'No hay ciclo activo para postulaciones'], 400);
        }

        // Obtener carreras con vacantes disponibles
        $carreras = CicloCarreraVacante::with('carrera')
            ->where('ciclo_id', $cicloActivo->id)
            ->where('estado', true)
            ->where(function($query) {
                $query->where('vacantes_total', 0) // Sin límite
                      ->orWhereRaw('vacantes_total > (vacantes_ocupadas + vacantes_reservadas)'); // Con vacantes disponibles
            })
            ->get()
            ->map(function($vacante) {
                return [
                    'id' => $vacante->carrera_id,
                    'nombre' => $vacante->carrera->nombre,
                    'codigo' => $vacante->carrera->codigo,
                    'vacantes_disponibles' => $vacante->vacantes_total == 0 ? 
                        'Sin límite' : $vacante->vacantes_disponibles
                ];
            });

        // Obtener turnos disponibles
        $turnos = Turno::where('estado', true)
            ->select('id', 'nombre', 'hora_inicio', 'hora_fin')
            ->get();

        // Si se pasa el ID del postulante, obtener sus datos y usar formulario simplificado
        $postulanteId = $request->get('postulante_id');
        $usuario = null;
        $padres = [];
        
        if ($postulanteId) {
            $usuario = User::with('roles')->find($postulanteId);
            
            if ($usuario) {
                // Verificar si ya tiene padres registrados
                $padres = Parentesco::with('padre')
                    ->where('estudiante_id', $usuario->id)
                    ->get()
                    ->map(function($parentesco) {
                        return [
                            'tipo' => $parentesco->tipo_parentesco,
                            'padre' => $parentesco->padre
                        ];
                    });
                
                // Usar formulario simplificado para usuarios existentes
                return view('postulaciones.form-simplificado', compact(
                    'cicloActivo', 
                    'carreras', 
                    'turnos', 
                    'usuario',
                    'padres'
                ));
            }
        }

        // Formulario completo para usuarios nuevos o sin ID
        return view('postulaciones.form-content', compact(
            'cicloActivo', 
            'carreras', 
            'turnos', 
            'usuario'
        ));
    }
}