<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Models\PasswordHistory;

class ResetPasswordController extends Controller
{
    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Buscar usuario
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No podemos encontrar un usuario con esa dirección de correo electrónico.']);
        }

        // Buscar token
        $resetToken = PasswordResetToken::where('token', $request->token)
            ->where('usuario_id', $user->id)
            ->where('utilizado', false)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$resetToken) {
            return back()->withErrors(['token' => 'Este token de restablecimiento no es válido o ya ha expirado.']);
        }

        // Verificar si la contraseña ha sido usada recientemente (opcional)
        if (PasswordHistory::wasPasswordUsedBefore($user->id, $request->password, 5)) {
            return back()->withErrors(['password' => 'Esta contraseña ya ha sido utilizada recientemente. Por favor, elija una nueva.']);
        }

        // Actualizar contraseña
        $user->password_hash = Hash::make($request->password);
        $user->save();

        // Marcar token como utilizado
        $resetToken->markAsUsed();

        // Registrar cambio de contraseña (ya se registrará automáticamente mediante el trigger)

        return redirect()->route('login')->with('status', 'Su contraseña ha sido restablecida con éxito.');
    }
}
