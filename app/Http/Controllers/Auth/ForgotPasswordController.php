<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Mail\ResetPasswordMail;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No podemos encontrar un usuario con esa dirección de correo electrónico.']);
        }

        // Generar token
        $token = Str::random(64);

        // Guardar token en la base de datos
        PasswordResetToken::where('usuario_id', $user->id)->update(['utilizado' => true]);

        PasswordResetToken::create([
            'usuario_id' => $user->id,
            'token' => $token,
            'fecha_creacion' => now(),
            'fecha_expiracion' => now()->addHours(24),
            'utilizado' => false
        ]);

        // Enviar correo con el enlace de restablecimiento
        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $request->email], false));

        Mail::to($user->email)->send(new ResetPasswordMail($resetUrl));

        return back()->with('status', 'Hemos enviado un enlace para restablecer su contraseña por correo electrónico.');
    }
}
