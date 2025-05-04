<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Mostrar el panel de control principal.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Obtener los módulos a los que tiene acceso el usuario
        $modules = $this->getUserAccessibleModules($user);

        // Más datos para el dashboard según el rol del usuario
        $data = [];

        if ($user->hasRole('admin')) {
            // Datos para administradores
            $data['totalUsuarios'] = User::count();
            $data['totalRoles'] = Role::count();
            $data['totalPermisos'] = Permission::count();
        }

        if ($user->hasRole('profesor')) {
            // Datos para profesores
            // Por ejemplo, número de grupos asignados, etc.
        }

        if ($user->hasRole('estudiante')) {
            // Datos para estudiantes
            // Por ejemplo, resumen de asistencia personal, etc.
        }

        return view('admin.dashboard', compact('user', 'modules', 'data'));
    }

    /**
     * Obtener módulos a los que tiene acceso el usuario.
     *
     * @param \App\Models\User $user
     * @return array
     */
    private function getUserAccessibleModules($user)
    {
        $modules = [];

        // Verificar acceso al módulo de usuarios
        if ($user->hasPermission('users.view')) {
            $modules[] = [
                'name' => 'Usuarios',
                'icon' => 'users',
                'route' => 'usuarios.index',
                'permissions' => [
                    'view' => $user->hasPermission('users.view'),
                    'create' => $user->hasPermission('users.create'),
                    'edit' => $user->hasPermission('users.edit'),
                    'delete' => $user->hasPermission('users.delete')
                ]
            ];
        }

        // Verificar acceso al módulo de roles
        if ($user->hasPermission('roles.view')) {
            $modules[] = [
                'name' => 'Roles y Permisos',
                'icon' => 'shield',
                'route' => 'roles.index',
                'permissions' => [
                    'view' => $user->hasPermission('roles.view'),
                    'create' => $user->hasPermission('roles.create'),
                    'edit' => $user->hasPermission('roles.edit'),
                    'delete' => $user->hasPermission('roles.delete')
                ]
            ];
        }

        // Verificar acceso al módulo de asistencia
        if (
            $user->hasPermission('attendance.view') ||
            $user->hasPermission('attendance.register') ||
            $user->hasPermission('attendance.edit')
        ) {
            $modules[] = [
                'name' => 'Asistencia',
                'icon' => 'calendar',
                'route' => 'asistencia.index',
                'permissions' => [
                    'view' => $user->hasPermission('attendance.view'),
                    'register' => $user->hasPermission('attendance.register'),
                    'edit' => $user->hasPermission('attendance.edit'),
                    'export' => $user->hasPermission('attendance.export'),
                    'reports' => $user->hasPermission('attendance.reports')
                ]
            ];
        }

        return $modules;
    }
}
