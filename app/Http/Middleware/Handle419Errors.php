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
     * 2. Redirecting back with a flash message instead of showing error
     * 3. Preventing the browser from trying to resubmit the POST request
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

            // Para peticiones normales, redirigir con mensaje
            // Esto previene que el navegador intente reenviar el POST
            return redirect()
                ->to(url()->previous() ?: route('dashboard'))
                ->with('warning', 'Tu sesión ha expirado. Por favor, intenta nuevamente.')
                ->with('csrf_expired', true);
        }
    }
}
