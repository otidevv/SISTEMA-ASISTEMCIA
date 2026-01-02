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
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $login = $request->input('email');
        
        // Determinar si es email o username (DNI)
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $attemptCredentials = [
            $field => $login,
            'password' => $request->password,
        ];

        if (Auth::attempt($attemptCredentials, $request->filled('remember'))) {
            $user = Auth::user();
            
            // Verificar el estado de la cuenta (0 = inactivo/pendiente, 1 = activo)
            if (!$user->estado) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Su cuenta está pendiente de verificación o ha sido desactivada. Por favor, revise su correo electrónico o contacte al administrador.',
                ])->onlyInput('email');
            }
            
            $request->session()->regenerate();

            // Registrar la sesión del usuario
            $this->logUserSession($request);

            // Actualizar último acceso
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
