<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Interceptar errores 419 CSRF y redirigir en lugar de mostrar página de error
        // Esto previene que el navegador se quede atascado intentando reenviar el POST
        $middleware->append(\App\Http\Middleware\Handle419Errors::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
