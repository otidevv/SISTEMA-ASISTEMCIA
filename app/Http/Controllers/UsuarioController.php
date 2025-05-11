<?php
// app/Http/Controllers/UsuarioController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('usuarios.index');
    }

    public function create()
    {
        $roles = Role::all();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // Depurar datos recibidos
        // dd($request->all());

        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'tipo_documento' => 'required|string|max:20',
            'numero_documento' => 'required|string|max:20|unique:users',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|string|max:20',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Depurar que pasó la validación
        // dd('Validación exitosa');

        DB::beginTransaction();

        try {
            // Preparar datos para crear el usuario
            $userData = [
                'username' => $request->username,
                'email' => $request->email,
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
                'estado' => true,
            ];

            // Depurar datos antes de crear el usuario
            // dd($userData);

            $user = User::create($userData);

            // Depurar usuario creado
            // dd($user);

            // Asignar roles
            foreach ($request->roles as $rolId) {
                DB::table('user_roles')->insert([
                    'usuario_id' => $user->id,
                    'rol_id' => $rolId,
                    'fecha_asignacion' => now(),
                    'asignado_por' => auth()->id()
                ]);
            }

            // Depurar después de asignar roles
            // dd('Roles asignados');

            // Guardar historial de contraseña
            DB::table('password_history')->insert([
                'usuario_id' => $user->id,
                'password_hash' => Hash::make($request->password),
                'fecha_cambio' => now(),
                // Elimina 'creado_por' si no existe este campo en la tabla
            ]);

            // Depurar después de guardar historial

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Depurar el error específico
            dd('Error: ' . $e->getMessage() . ' en la línea ' . $e->getLine() . ' del archivo ' . $e->getFile());

            return redirect()->back()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(User $usuario)
    {
        $roles = Role::all();
        $usuarioRoles = $usuario->roles->pluck('id')->toArray();
        return view('usuarios.edit', compact('usuario', 'roles', 'usuarioRoles'));
    }

    public function update(Request $request, User $usuario)
    {
        $validationRules = [
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($usuario->id)],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($usuario->id)],
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'tipo_documento' => 'required|string|max:20',
            'numero_documento' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($usuario->id)],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|string|max:20',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'estado' => 'nullable|boolean',
        ];

        // Si se proporciona una contraseña, validarla
        if ($request->filled('password')) {
            $validationRules['password'] = 'string|min:8|confirmed';
        }

        $request->validate($validationRules);

        DB::beginTransaction();

        try {
            $data = [
                'username' => $request->username,
                'email' => $request->email,
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'tipo_documento' => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'estado' => $request->has('estado'),
                'updated_at' => now(),
            ];

            // Actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $data['password_hash'] = Hash::make($request->password);

                // Guardar historial de contraseña
                DB::table('password_history')->insert([
                    'usuario_id' => $usuario->id,
                    'password_hash' => Hash::make($request->password),
                    'fecha_creacion' => now(),
                    'creado_por' => auth()->id()
                ]);
            }

            $usuario->update($data);

            // Actualizar roles
            DB::table('user_roles')->where('usuario_id', $usuario->id)->delete();
            foreach ($request->roles as $rolId) {
                DB::table('user_roles')->insert([
                    'usuario_id' => $usuario->id,
                    'rol_id' => $rolId,
                    'fecha_asignacion' => now(),
                    'asignado_por' => auth()->id()
                ]);
            }

            DB::commit();

            return redirect()->route('usuarios.index')
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(User $usuario)
    {
        // Prevenir que se desactive el propio usuario
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes desactivar tu propio usuario.');
        }

        // En lugar de eliminar, desactivamos el usuario
        $usuario->estado = false;
        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario desactivado exitosamente.');
    }
}
