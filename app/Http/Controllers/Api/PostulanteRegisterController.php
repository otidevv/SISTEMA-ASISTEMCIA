<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Parentesco;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PostulanteRegisterController extends Controller
{
    public function register(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            // Datos del postulante
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'required|string|max:50',
            'tipo_documento' => 'required|in:DNI,CE',
            'numero_documento' => 'required|string|max:12|unique:users,numero_documento',
            'fecha_nacimiento' => 'required|date|before:-14 years',
            'genero' => 'required|in:M,F',
            'telefono' => 'required|string|max:9|min:9',
            'direccion' => 'required|string|max:255',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            
            // Datos del padre
            'padre_nombre' => 'required|string|max:100',
            'padre_apellidos' => 'required|string|max:100',
            'padre_tipo_documento' => 'required|in:DNI,CE',
            'padre_numero_documento' => 'required|string|max:12',
            'padre_telefono' => 'required|string|max:9|min:9',
            'padre_email' => 'nullable|email|max:100',
            
            // Datos de la madre
            'madre_nombre' => 'required|string|max:100',
            'madre_apellidos' => 'required|string|max:100',
            'madre_tipo_documento' => 'required|in:DNI,CE',
            'madre_numero_documento' => 'required|string|max:12',
            'madre_telefono' => 'required|string|max:9|min:9',
            'madre_email' => 'nullable|email|max:100',
            
            // Términos
            'terms' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        
        $response = [
            'success' => false,
            'message' => '',
            'data' => [],
            'email_status' => 'not_sent',
            'email_error' => null
        ];

        try {
            // Obtener roles
            $rolPostulante = Role::where('nombre', 'postulante')->first();
            if (!$rolPostulante) {
                throw new \Exception('El rol de postulante no existe. Por favor, ejecute el seeder.');
            }

            $rolPadre = Role::where('nombre', 'padre')->first();
            if (!$rolPadre) {
                throw new \Exception('El rol de padre no existe. Por favor, ejecute el seeder.');
            }

            // Generar token de verificación
            $verificationToken = Str::random(60);

            // Crear el usuario postulante con estado pendiente
            $postulante = User::create([
                'username' => $this->generateUsername($request->nombre, $request->apellido_paterno),
                'email' => $request->email,
                'email_verification_token' => $verificationToken,
                'password_hash' => Hash::make($request->password),
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'tipo_documento' => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'estado' => false // Estado 0 = pendiente hasta verificar email
            ]);

            // Asignar rol de postulante
            $postulante->roles()->attach($rolPostulante->id, [
                'fecha_asignacion' => now(),
                'asignado_por' => null
            ]);

            $response['data']['postulante'] = [
                'id' => $postulante->id,
                'username' => $postulante->username,
                'email' => $postulante->email,
                'nombre_completo' => $postulante->nombre . ' ' . $postulante->apellido_paterno . ' ' . $postulante->apellido_materno
            ];

            // Buscar o crear usuario del padre
            $padre = User::where('numero_documento', $request->padre_numero_documento)->first();
            
            if (!$padre) {
                // Crear nuevo usuario padre
                $apellidosPadre = explode(' ', $request->padre_apellidos, 2);
                $apellidoPaternoPadre = $apellidosPadre[0] ?? $request->padre_apellidos;
                $apellidoMaternoPadre = $apellidosPadre[1] ?? '';

                $padre = User::create([
                    'username' => $this->generateUsername($request->padre_nombre, $apellidoPaternoPadre),
                    'email' => $request->padre_email ?: 'padre_' . $request->padre_numero_documento . '@sistema.edu',
                    'email_verified_at' => now(),
                    'password_hash' => Hash::make($request->padre_numero_documento),
                    'nombre' => $request->padre_nombre,
                    'apellido_paterno' => $apellidoPaternoPadre,
                    'apellido_materno' => $apellidoMaternoPadre,
                    'tipo_documento' => $request->padre_tipo_documento,
                    'numero_documento' => $request->padre_numero_documento,
                    'telefono' => $request->padre_telefono,
                    'genero' => 'M',
                    'estado' => true
                ]);

                $padre->roles()->attach($rolPadre->id, [
                    'fecha_asignacion' => now(),
                    'asignado_por' => null
                ]);
                
                $response['data']['padre_creado'] = true;
                $response['data']['padre_info'] = 'Nuevo usuario padre creado';
            } else {
                // Padre ya existe - actualizar datos si es necesario
                $padreActualizado = false;
                
                // Actualizar email si proporcionaron uno y el actual es genérico
                if ($request->padre_email && str_contains($padre->email, '@sistema.edu')) {
                    $padre->email = $request->padre_email;
                    $padreActualizado = true;
                }
                
                // Actualizar teléfono si cambió
                if ($request->padre_telefono && $padre->telefono != $request->padre_telefono) {
                    $padre->telefono = $request->padre_telefono;
                    $padreActualizado = true;
                }
                
                if ($padreActualizado) {
                    $padre->save();
                    $response['data']['padre_info'] = 'Datos del padre actualizados';
                } else {
                    $response['data']['padre_info'] = 'Padre ya registrado, vinculando con nuevo hijo';
                }
                
                // Verificar si ya tiene rol de padre
                if (!$padre->hasRole('padre')) {
                    $padre->roles()->attach($rolPadre->id, [
                        'fecha_asignacion' => now(),
                        'asignado_por' => null
                    ]);
                }
                
                $response['data']['padre_creado'] = false;
                $response['data']['padre_existente'] = [
                    'dni' => $padre->numero_documento,
                    'nombre' => $padre->nombre . ' ' . $padre->apellido_paterno,
                    'hijos_registrados' => Parentesco::where('padre_id', $padre->id)
                        ->where('tipo_parentesco', 'Padre')
                        ->count()
                ];
            }

            // Buscar o crear usuario de la madre
            $madre = User::where('numero_documento', $request->madre_numero_documento)->first();
            
            if (!$madre) {
                // Crear nueva usuaria madre
                $apellidosMadre = explode(' ', $request->madre_apellidos, 2);
                $apellidoPaternoMadre = $apellidosMadre[0] ?? $request->madre_apellidos;
                $apellidoMaternoMadre = $apellidosMadre[1] ?? '';

                $madre = User::create([
                    'username' => $this->generateUsername($request->madre_nombre, $apellidoPaternoMadre),
                    'email' => $request->madre_email ?: 'madre_' . $request->madre_numero_documento . '@sistema.edu',
                    'email_verified_at' => now(),
                    'password_hash' => Hash::make($request->madre_numero_documento),
                    'nombre' => $request->madre_nombre,
                    'apellido_paterno' => $apellidoPaternoMadre,
                    'apellido_materno' => $apellidoMaternoMadre,
                    'tipo_documento' => $request->madre_tipo_documento,
                    'numero_documento' => $request->madre_numero_documento,
                    'telefono' => $request->madre_telefono,
                    'genero' => 'F',
                    'estado' => true
                ]);

                $madre->roles()->attach($rolPadre->id, [
                    'fecha_asignacion' => now(),
                    'asignado_por' => null
                ]);
                
                $response['data']['madre_creada'] = true;
                $response['data']['madre_info'] = 'Nueva usuaria madre creada';
            } else {
                // Madre ya existe - actualizar datos si es necesario
                $madreActualizada = false;
                
                // Actualizar email si proporcionaron uno y el actual es genérico
                if ($request->madre_email && str_contains($madre->email, '@sistema.edu')) {
                    $madre->email = $request->madre_email;
                    $madreActualizada = true;
                }
                
                // Actualizar teléfono si cambió
                if ($request->madre_telefono && $madre->telefono != $request->madre_telefono) {
                    $madre->telefono = $request->madre_telefono;
                    $madreActualizada = true;
                }
                
                if ($madreActualizada) {
                    $madre->save();
                    $response['data']['madre_info'] = 'Datos de la madre actualizados';
                } else {
                    $response['data']['madre_info'] = 'Madre ya registrada, vinculando con nuevo hijo';
                }
                
                // Verificar si ya tiene rol de padre
                if (!$madre->hasRole('padre')) {
                    $madre->roles()->attach($rolPadre->id, [
                        'fecha_asignacion' => now(),
                        'asignado_por' => null
                    ]);
                }
                
                $response['data']['madre_creada'] = false;
                $response['data']['madre_existente'] = [
                    'dni' => $madre->numero_documento,
                    'nombre' => $madre->nombre . ' ' . $madre->apellido_paterno,
                    'hijos_registrados' => Parentesco::where('padre_id', $madre->id)
                        ->where('tipo_parentesco', 'Madre')
                        ->count()
                ];
            }

            // Crear relaciones de parentesco (verificar que no existan)
            $parentescoPadre = Parentesco::where('estudiante_id', $postulante->id)
                ->where('padre_id', $padre->id)
                ->first();
                
            if (!$parentescoPadre) {
                Parentesco::create([
                    'estudiante_id' => $postulante->id,
                    'padre_id' => $padre->id,
                    'tipo_parentesco' => 'Padre',
                    'acceso_portal' => true,
                    'recibe_notificaciones' => true,
                    'contacto_emergencia' => true,
                    'estado' => true
                ]);
            }

            $parentescoMadre = Parentesco::where('estudiante_id', $postulante->id)
                ->where('padre_id', $madre->id)
                ->first();
                
            if (!$parentescoMadre) {
                Parentesco::create([
                    'estudiante_id' => $postulante->id,
                    'padre_id' => $madre->id,
                    'tipo_parentesco' => 'Madre',
                    'acceso_portal' => true,
                    'recibe_notificaciones' => true,
                    'contacto_emergencia' => true,
                    'estado' => true
                ]);
            }

            DB::commit();

            // Intentar enviar email de verificación (fuera de la transacción)
            try {
                // Verificar configuración de correo primero
                $mailConfig = config('mail.mailers.smtp');
                Log::info('Configuración de correo:', [
                    'host' => $mailConfig['host'] ?? 'no configurado',
                    'port' => $mailConfig['port'] ?? 'no configurado',
                    'from' => config('mail.from.address')
                ]);

                // Enviar notificación
                $postulante->notify(new VerifyEmailNotification($verificationToken));
                
                $response['email_status'] = 'sent';
                $response['message'] = 'Registro completado exitosamente. Se ha enviado un correo de verificación a ' . $request->email;
                
                Log::info('Email de verificación enviado exitosamente', [
                    'user_id' => $postulante->id,
                    'email' => $postulante->email
                ]);
                
            } catch (\Exception $mailException) {
                // El registro fue exitoso pero el email falló
                $response['email_status'] = 'failed';
                $response['email_error'] = $mailException->getMessage();
                $response['message'] = 'Registro completado, pero hubo un problema al enviar el correo de verificación. Por favor, contacte al administrador.';
                
                Log::error('Error enviando email de verificación', [
                    'user_id' => $postulante->id,
                    'email' => $postulante->email,
                    'error' => $mailException->getMessage(),
                    'trace' => $mailException->getTraceAsString()
                ]);
            }

            $response['success'] = true;
            $response['data']['verification_token'] = $verificationToken; // Solo para debug, quitar en producción
            
            return response()->json($response, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error en registro de postulante', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el registro: ' . $e->getMessage(),
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar estado del servidor de correo
     */
    public function checkEmailServer()
    {
        try {
            $config = config('mail.mailers.smtp');
            
            $connection = @fsockopen(
                $config['host'],
                $config['port'],
                $errno,
                $errstr,
                5
            );

            if ($connection) {
                fclose($connection);
                return response()->json([
                    'success' => true,
                    'message' => 'Servidor de correo accesible',
                    'config' => [
                        'host' => $config['host'],
                        'port' => $config['port'],
                        'from' => config('mail.from.address')
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede conectar al servidor de correo',
                    'error' => $errstr
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verificando servidor de correo',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reenviar correo de verificación
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email no válido o no registrado',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if ($user->estado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta cuenta ya ha sido verificada'
                ], 400);
            }

            if (!$user->email_verification_token) {
                $user->email_verification_token = Str::random(60);
                $user->save();
            }

            $user->notify(new VerifyEmailNotification($user->email_verification_token));

            return response()->json([
                'success' => true,
                'message' => 'Correo de verificación reenviado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error reenviando email de verificación', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar el correo de verificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar un username único
     */
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
}