<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    /**
     * Verificar el email del usuario
     */
    public function verify(Request $request, $id, $token)
    {
        // Buscar el usuario
        $user = User::findOrFail($id);

        // Verificar que el token coincida
        if ($user->email_verification_token !== $token) {
            return redirect()->route('login')
                ->withErrors(['error' => 'El enlace de verificación no es válido.']);
        }

        // Verificar que la cuenta esté pendiente (estado = 0)
        if ($user->estado) {
            return redirect()->route('login')
                ->with('info', 'Su cuenta ya ha sido verificada anteriormente.');
        }

        // Verificar el email
        $user->email_verified_at = Carbon::now();
        $user->email_verification_token = null;
        $user->estado = true; // Cambiar estado a 1 (activo) cuando verifica el email
        $user->save();

        return redirect()->route('login')
            ->with('success', '¡Su cuenta ha sido verificada exitosamente! Ya puede iniciar sesión.');
    }

    /**
     * Reenviar el email de verificación
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        // Verificar que la cuenta esté pendiente (estado = 0)
        if ($user->estado) {
            return redirect()->back()
                ->with('info', 'Su cuenta ya ha sido verificada.');
        }

        // Generar nuevo token si no existe
        if (!$user->email_verification_token) {
            $user->email_verification_token = \Str::random(60);
            $user->save();
        }

        // Reenviar el email
        try {
            $user->notify(new \App\Notifications\VerifyEmailNotification($user->email_verification_token));
            
            return redirect()->back()
                ->with('success', 'Se ha reenviado el correo de verificación a ' . $user->email);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error al enviar el correo de verificación. Por favor, intente más tarde.']);
        }
    }

    /**
     * Mostrar página de verificación pendiente
     */
    public function notice()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->estado) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email', compact('user'));
    }
}