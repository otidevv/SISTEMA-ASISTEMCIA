<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Session\TokenMismatchException;

class Handle419Errors
{
    /**
     * Handle an incoming request and intercept 419 errors.
     * 
     * This middleware prevents the "stuck on 419 page" issue by:
     * 1. Catching TokenMismatchException before it reaches the error page
     * 2. Using 303 redirect to force browser to use GET instead of POST
     * 3. Preventing the "confirm form resubmission" dialog
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (TokenMismatchException $e) {
            // Log the 419 error for debugging
            \Log::warning('419 CSRF Token Mismatch detectado', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Si es una petición AJAX, devolver JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Tu sesión ha expirado. Por favor, recarga la página.',
                    'error' => 'token_expired',
                    'redirect' => url()->previous() ?: route('dashboard')
                ], 419);
            }

            // Para peticiones normales, usar redirect 303 (See Other)
            // Esto FUERZA al navegador a usar GET en lugar de POST
            // Previene el diálogo "confirmar reenvío del formulario"
            $redirectUrl = url()->previous() ?: route('dashboard');
            
            return response()
                ->redirectTo($redirectUrl, 303)
                ->with('warning', 'Tu sesión ha expirado. Por favor, intenta nuevamente.')
                ->with('csrf_expired', true);
        }
    }
}
