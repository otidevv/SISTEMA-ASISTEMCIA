<?php

// app/Http/Controllers/RoleController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles',
            'descripcion' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        $role = new Role();
        $role->nombre = $request->nombre;
        $role->descripcion = $request->descripcion;
        $role->is_default = $request->has('is_default');
        $role->fecha_creacion = now();
        $role->save();

        return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        // Cargar los permisos asociados al rol
        $role->load('permissions');
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre,' . $role->id,
            'descripcion' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $role->nombre = $request->nombre;
            $role->descripcion = $request->descripcion;
            $role->is_default = $request->has('is_default');
            $role->save();

            // Si este rol se marca como predeterminado, desmarcar otros roles predeterminados
            if ($role->is_default) {
                Role::where('id', '!=', $role->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            DB::commit();
            return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar rol: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar el rol. Por favor, inténtelo de nuevo.')->withInput();
        }
    }

    public function destroy(Role $role)
    {
        try {
            // Verificar si hay usuarios con este rol
            $usersCount = DB::table('user_roles')->where('rol_id', $role->id)->count();

            if ($usersCount > 0) {
                return redirect()->route('roles.index')
                    ->with('error', 'No se puede eliminar el rol porque está asignado a ' . $usersCount . ' usuario(s).');
            }

            // Verificar si es el único rol en el sistema
            $rolesCount = Role::count();
            if ($rolesCount <= 1) {
                return redirect()->route('roles.index')
                    ->with('error', 'No se puede eliminar el rol porque es el único rol en el sistema.');
            }

            // Eliminar los permisos asociados al rol
            DB::table('role_permissions')->where('rol_id', $role->id)->delete();

            // Eliminar el rol
            $role->delete();

            return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar rol: ' . $e->getMessage());
            return redirect()->route('roles.index')
                ->with('error', 'Error al eliminar el rol. Por favor, inténtelo de nuevo.');
        }
    }

    public function permisosIndex()
    {
        $roles = Role::all();
        $permissions = Permission::orderBy('modulo')->get();

        // Agrupar permisos por módulo
        $modulos = $permissions->groupBy('modulo');

        // Obtener los permisos asignados a cada rol
        $rolesPermissions = [];
        foreach ($roles as $role) {
            $rolesPermissions[$role->id] = $role->permissions->pluck('id')->toArray();
        }

        return view('roles.permisos', compact('roles', 'modulos', 'rolesPermissions'));
    }

    public function permisosUpdate(Request $request)
    {
        $request->validate([
            'permisos' => 'array',
        ]);

        try {
            DB::beginTransaction();

            $permisosRoles = $request->input('permisos', []);

            // Obtener todos los roles
            $roles = Role::all();

            // Actualizar permisos para cada rol
            foreach ($roles as $role) {
                // Eliminar todos los permisos existentes
                DB::table('role_permissions')->where('rol_id', $role->id)->delete();

                // Asignar los nuevos permisos si existen
                if (isset($permisosRoles[$role->id]) && is_array($permisosRoles[$role->id])) {
                    foreach ($permisosRoles[$role->id] as $permisoId) {
                        DB::table('role_permissions')->insert([
                            'rol_id' => $role->id,
                            'permiso_id' => $permisoId
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('roles.permisos')->with('success', 'Permisos actualizados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar permisos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar los permisos. Por favor, inténtelo de nuevo.');
        }
    }

    /**
     * Get the count of users with a specific role.
     *
     * @param int $roleId
     * @return int
     */
    private function getUserCountByRole($roleId)
    {
        return DB::table('user_roles')->where('rol_id', $roleId)->count();
    }
}
