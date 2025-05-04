<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /**
     * Mostrar el formulario de inicio de sesión.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Manejar una solicitud de inicio de sesión.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // En nuestra tabla de usuarios el campo de contraseña se llama 'password_hash'
        // así que debemos ajustar las credenciales
        $attemptCredentials = [
            'email' => $credentials['email'],
            'password' => $credentials['password'], // Laravel buscará en el campo definido por getAuthPassword()
        ];
        // dd('no autenticado gagag');

        if (Auth::attempt($attemptCredentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Registrar la sesión del usuario
            $this->logUserSession($request);

            // Actualizar último acceso
            $user = Auth::user();
            $user->ultimo_acceso = now();
            $user->save();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar la sesión del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Cerrar la sesión activa en la base de datos
        if (Auth::check() && session()->has('session_id')) {
            $sessionId = session()->get('session_id');
            $userSession = UserSession::find($sessionId);

            if ($userSession && $userSession->isActive()) {
                $userSession->endSession();
            }
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Registrar la sesión del usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    private function logUserSession(Request $request)
    {
        $userSession = new UserSession();
        // No asignar el ID manualmente, se generará automáticamente
        $userSession->usuario_id = Auth::id();
        $userSession->ip_address = $request->ip();
        $userSession->user_agent = $request->userAgent();
        $userSession->estado = 'activa';
        $userSession->save();

        // Guardar el ID de sesión en la sesión actual para poder cerrarla después
        session(['session_id' => $userSession->id]);
    }
}
