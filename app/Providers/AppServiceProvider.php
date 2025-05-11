<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Opcional: establecer longitud predeterminada de cadena para compatibilidad con MySQL
        Schema::defaultStringLength(191);

        // Compartir la URL base con todas las vistas
        // Detecta automáticamente si estás en artisan serve o Apache
        View::share('default_server', url('/'));
    }
}
