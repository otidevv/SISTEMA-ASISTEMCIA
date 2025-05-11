<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;


class UserController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios para DataTables.
     */
    public function index()
    {
        $users = User::with('roles')->get();

        // Formatear los datos para DataTables
        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->nombre . ' ' . $user->apellido_paterno . ' ' . $user->apellido_materno,
                'email' => $user->email,
                'roles' => $user->roles->pluck('nombre')->toArray(),
                'estado' => $user->estado,
                'numero_documento' => $user->numero_documento,
                'actions' => $this->getActionButtons($user)
            ];
        });

        return response()->json([
            'draw' => request()->input('draw', 1),
            'recordsTotal' => $users->count(),
            'recordsFiltered' => $users->count(),
            'data' => $data
        ]);
    }

    /**
     * Genera los botones de acción para cada usuario.
     */
    private function getActionButtons($user)
    {
        $buttons = '';

        // Botón de editar
        $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-primary edit-user" data-id="' . $user->id . '" title="Editar"><i class="uil uil-edit"></i></a> ';

        // Botón de cambiar estado
        $statusIcon = $user->estado ? 'uil-ban' : 'uil-check';
        $statusTitle = $user->estado ? 'Desactivar' : 'Activar';
        $statusClass = $user->estado ? 'warning' : 'success';

        $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-' . $statusClass . ' change-status" data-id="' . $user->id . '" title="' . $statusTitle . '"><i class="uil ' . $statusIcon . '"></i></a> ';

        // Botón de eliminar
        $buttons .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger delete-user" data-id="' . $user->id . '" title="Eliminar"><i class="uil uil-trash-alt"></i></a>';

        return $buttons;
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8',
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'tipo_documento' => 'required|string|max:20',
            'numero_documento' => 'required|string|max:20|unique:users',
            'telefono' => 'nullable|string|max:20',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear el usuario
        $userData = $request->except('password', 'roles');
        $userData['password_hash'] = Hash::make($request->password);
        $userData['estado'] = true; // Usuario activo por defecto

        $user = User::create($userData);

        // Asignar roles si se proporcionaron
        if ($request->has('roles') && !empty($request->roles)) {
            $user_id = auth()->id(); // ID del usuario actual que está creando este usuario

            foreach ($request->roles as $role_id) {
                UserRole::create([
                    'usuario_id' => $user->id,
                    'rol_id' => $role_id,
                    'fecha_asignacion' => now(),
                    'asignado_por' => $user_id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => $user
        ], 201);
    }

    /**
     * Muestra el usuario especificado.
     */
    public function show($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Esto es crucial - añadir los IDs de roles como una propiedad separada
        $user->role_ids = $user->roles->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Actualiza el usuario especificado en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|required|string|max:50|unique:users,username,' . $id,
            'email' => 'sometimes|required|string|email|max:100|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'nombre' => 'sometimes|required|string|max:100',
            'apellido_paterno' => 'sometimes|required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'tipo_documento' => 'sometimes|required|string|max:20',
            'numero_documento' => 'sometimes|required|string|max:20|unique:users,numero_documento,' . $id,
            'telefono' => 'nullable|string|max:20',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'estado' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Actualizar campos básicos
        $user->fill($request->except('password', 'roles'));

        // Manejar el password de forma especial
        if ($request->filled('password')) {
            $user->password_hash = Hash::make($request->password);
        }

        $user->save();

        // Actualizar roles si se proporcionaron
        if ($request->has('roles')) {
            // Eliminar roles actuales
            UserRole::where('usuario_id', $user->id)->delete();

            // Asignar nuevos roles
            $user_id = auth()->id(); // ID del usuario actual

            foreach ($request->roles as $role_id) {
                UserRole::create([
                    'usuario_id' => $user->id,
                    'rol_id' => $role_id,
                    'fecha_asignacion' => now(),
                    'asignado_por' => $user_id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'data' => $user
        ]);
    }

    /**
     * Elimina el usuario especificado de la base de datos.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    /**
     * Cambia el estado del usuario (activo/inactivo).
     */
    public function changeStatus($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $user->estado = !$user->estado;
        $user->save();

        $status = $user->estado ? 'activado' : 'desactivado';

        return response()->json([
            'success' => true,
            'message' => "Usuario {$status} exitosamente",
            'data' => $user
        ]);
    }

    /**
     * Obtiene todos los roles disponibles.
     */
    public function getRoles()
    {
        $roles = Role::all(['id', 'nombre', 'descripcion']);

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }
    // En App\Http\Controllers\Api\UserController.php

    /**
     * Lista todos los usuarios con rol de estudiante para selectores.
     */
    public function listarEstudiantes()
    {
        // Obtener usuarios con rol de estudiante
        $estudiantes = User::whereHas('roles', function ($query) {
            $query->where('nombre', 'Estudiante');
        })->select('id', 'nombre', 'apellido_paterno', 'apellido_materno')->get();

        return response()->json([
            'success' => true,
            'data' => $estudiantes
        ]);
    }

    /**
     * Lista todos los usuarios que pueden ser padres/tutores para selectores.
     */
    public function listarPadres()
    {
        // Obtener usuarios con roles que pueden ser padres/tutores
        $padres = User::whereHas('roles', function ($query) {
            $query->whereIn('nombre', ['Padre', 'Madre', 'Tutor', 'Acudiente']);
        })->select('id', 'nombre', 'apellido_paterno', 'apellido_materno')->get();

        return response()->json([
            'success' => true,
            'data' => $padres
        ]);
    }
}
