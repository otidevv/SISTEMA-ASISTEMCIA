<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Parentesco;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostulanteRegisterController extends Controller
{
    /**
     * Registrar un nuevo postulante con sus padres
     */
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
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio',
            'apellido_materno.required' => 'El apellido materno es obligatorio',
            'tipo_documento.required' => 'Debe seleccionar un tipo de documento',
            'numero_documento.required' => 'El número de documento es obligatorio',
            'numero_documento.unique' => 'Este número de documento ya está registrado',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fecha_nacimiento.before' => 'Debe tener al menos 14 años',
            'genero.required' => 'Debe seleccionar el género',
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.min' => 'El teléfono debe tener 9 dígitos',
            'direccion.required' => 'La dirección es obligatoria',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'terms.accepted' => 'Debe aceptar los términos y condiciones'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('register_tab', true);
        }

        DB::beginTransaction();

        try {
            // Obtener el rol de postulante
            $rolPostulante = Role::where('nombre', 'postulante')->first();
            if (!$rolPostulante) {
                throw new \Exception('El rol de postulante no existe. Por favor, ejecute el seeder.');
            }

            // Obtener el rol de padre
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

            // Crear usuario del padre (si no existe)
            $padre = User::where('numero_documento', $request->padre_numero_documento)->first();
            
            if (!$padre) {
                // Separar apellidos del padre
                $apellidosPadre = explode(' ', $request->padre_apellidos, 2);
                $apellidoPaternoPadre = $apellidosPadre[0] ?? $request->padre_apellidos;
                $apellidoMaternoPadre = $apellidosPadre[1] ?? '';

                $padre = User::create([
                    'username' => $this->generateUsername($request->padre_nombre, $apellidoPaternoPadre),
                    'email' => $request->padre_email ?: 'padre_' . $request->padre_numero_documento . '@sistema.edu',
                    'email_verified_at' => now(), // Auto-verificado para padres
                    'password_hash' => Hash::make($request->padre_numero_documento), // Contraseña temporal: su DNI
                    'nombre' => $request->padre_nombre,
                    'apellido_paterno' => $apellidoPaternoPadre,
                    'apellido_materno' => $apellidoMaternoPadre,
                    'tipo_documento' => $request->padre_tipo_documento,
                    'numero_documento' => $request->padre_numero_documento,
                    'telefono' => $request->padre_telefono,
                    'genero' => 'M',
                    'estado' => true // Padres activos automáticamente
                ]);

                // Asignar rol de padre
                $padre->roles()->attach($rolPadre->id, [
                    'fecha_asignacion' => now(),
                    'asignado_por' => null
                ]);
            }

            // Crear usuario de la madre (si no existe)
            $madre = User::where('numero_documento', $request->madre_numero_documento)->first();
            
            if (!$madre) {
                // Separar apellidos de la madre
                $apellidosMadre = explode(' ', $request->madre_apellidos, 2);
                $apellidoPaternoMadre = $apellidosMadre[0] ?? $request->madre_apellidos;
                $apellidoMaternoMadre = $apellidosMadre[1] ?? '';

                $madre = User::create([
                    'username' => $this->generateUsername($request->madre_nombre, $apellidoPaternoMadre),
                    'email' => $request->madre_email ?: 'madre_' . $request->madre_numero_documento . '@sistema.edu',
                    'email_verified_at' => now(), // Auto-verificado para padres
                    'password_hash' => Hash::make($request->madre_numero_documento), // Contraseña temporal: su DNI
                    'nombre' => $request->madre_nombre,
                    'apellido_paterno' => $apellidoPaternoMadre,
                    'apellido_materno' => $apellidoMaternoMadre,
                    'tipo_documento' => $request->madre_tipo_documento,
                    'numero_documento' => $request->madre_numero_documento,
                    'telefono' => $request->madre_telefono,
                    'genero' => 'F',
                    'estado' => true // Padres activos automáticamente
                ]);

                // Asignar rol de padre (madre)
                $madre->roles()->attach($rolPadre->id, [
                    'fecha_asignacion' => now(),
                    'asignado_por' => null
                ]);
            }

            // Crear relaciones de parentesco
            Parentesco::create([
                'estudiante_id' => $postulante->id,
                'padre_id' => $padre->id,
                'tipo_parentesco' => 'Padre',
                'acceso_portal' => true,
                'recibe_notificaciones' => true,
                'contacto_emergencia' => true,
                'estado' => true
            ]);

            Parentesco::create([
                'estudiante_id' => $postulante->id,
                'padre_id' => $madre->id,
                'tipo_parentesco' => 'Madre',
                'acceso_portal' => true,
                'recibe_notificaciones' => true,
                'contacto_emergencia' => true,
                'estado' => true
            ]);

            DB::commit();

            // Enviar email de verificación
            try {
                $postulante->notify(new VerifyEmailNotification($verificationToken));
            } catch (\Exception $e) {
                // Log el error pero no fallar el registro
                \Log::error('Error enviando email de verificación: ' . $e->getMessage());
            }

            // Iniciar sesión automáticamente al nuevo postulante
            auth()->login($postulante);

            return redirect()->route('dashboard')
                ->with('success', 'Registro completado exitosamente. Se ha enviado un correo de verificación a ' . $request->email . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al procesar el registro: ' . $e->getMessage()])
                ->withInput()
                ->with('register_tab', true);
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