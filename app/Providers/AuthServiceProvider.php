<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Configurar el nombre de la columna de contraseña para restablecer contraseñas
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return url(route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], false));
        });

        // Definir permisos basados en roles
        $this->defineRoleBasedPermissions();
    }

    /**
     * Define los permisos basados en roles usando Gates.
     *
     * @return void
     */
    protected function defineRoleBasedPermissions()
    {
        // Verificar si el usuario tiene un rol específico
        Gate::define('has-role', function ($user, $role) {
            return $user->hasRole($role);
        });

        // Verificar si el usuario tiene un permiso específico
        Gate::define('has-permission', function ($user, $permission) {
            return $user->hasPermission($permission);
        });

        // Verificar si el usuario es administrador
        Gate::define('is-admin', function ($user) {
            return $user->hasRole('admin');
        });

        // Verificar si el usuario es profesor
        Gate::define('is-profesor', function ($user) {
            return $user->hasRole('profesor');
        });

        // Verificar si el usuario es estudiante
        Gate::define('is-estudiante', function ($user) {
            return $user->hasRole('estudiante');
        });
    }
}
