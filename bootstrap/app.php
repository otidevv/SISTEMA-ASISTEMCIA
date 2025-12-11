<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Manejar errores 419 (CSRF Token Mismatch)
        $exceptions->render(function (TokenMismatchException $e, $request) {
            // Log para debugging
            \Log::warning('419 CSRF Token Mismatch detectado', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            // Si es AJAX, devolver JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Tu sesión ha expirado. Por favor, recarga la página.',
                    'error' => 'token_expired',
                ], 419);
            }

            // Para peticiones normales, redirigir con código 303
            $previousUrl = url()->previous();
            
            // Si es login, redirigir a login fresco
            if (!$previousUrl || str_contains($previousUrl, '/login')) {
                $redirectUrl = route('login');
            } else {
                $redirectUrl = $previousUrl;
            }

            return response()
                ->redirectTo($redirectUrl, 303)
                ->with('warning', 'Tu sesión ha expirado. Por favor, intenta nuevamente.');
        });
    })->create();
