<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use App\Models\Permission;
use Illuminate\Support\Facades\Schema;

class PermissionsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            // Registrar dinámicamente todos los permisos como Gates
            // Solo si la tabla 'permissions' existe en la base de datos
            if (Schema::hasTable('permissions')) {
                $permissions = Permission::all();

                foreach ($permissions as $permission) {
                    Gate::define($permission->codigo, function ($user) use ($permission) {
                        return $user->hasPermission($permission->codigo);
                    });
                }
            }
        } catch (\Exception $e) {
            // Capturar cualquier error al cargar permisos
            // Esto previene errores durante las migraciones iniciales
            report($e);
        }

        // Crear directiva Blade para verificar permisos
        Blade::directive('permission', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$permission})): ?>";
        });

        Blade::directive('endpermission', function () {
            return "<?php endif; ?>";
        });

        // Crear directiva Blade para verificar roles
        Blade::directive('role', function ($role) {
            return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
        });

        Blade::directive('endrole', function () {
            return "<?php endif; ?>";
        });

        // Crear directiva Blade para verificar si tiene cualquiera de los roles
        Blade::directive('anyrole', function ($roles) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyRole({$roles})): ?>";
        });

        Blade::directive('endanyrole', function () {
            return "<?php endif; ?>";
        });
    }
}
