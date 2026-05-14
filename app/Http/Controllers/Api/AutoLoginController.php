<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Cache;

class AutoLoginController extends Controller
{
    /**
     * Iniciar sesión automáticamente mediante un token temporal en Cache
     */
    public function autoLogin(Request $request)
    {
        $token = $request->query('token');
        $redirect = $request->query('redirect', '/dashboard');

        if (!$token) {
            return redirect('/login')->with('error', 'Token de acceso no proporcionado');
        }

        // Buscar los datos asociados al token en el Cache
        $data = Cache::get('mobile_auth_' . $token);
        
        // Validar si el token existe y si la IP coincide
        if (!$data || !is_array($data) || $data['ip'] !== $request->ip()) {
            return redirect('/login')->with('error', 'El enlace de acceso es inválido, ha expirado o se está usando desde una conexión no autorizada');
        }

        // Buscar al usuario
        $user = User::find($data['user_id']);

        if (!$user) {
            return redirect('/login')->with('error', 'Usuario no encontrado');
        }

        // Borrar el token inmediatamente (One-time use)
        Cache::forget('mobile_auth_' . $token);

        // Iniciar sesión
        Auth::login($user);

        return redirect($redirect);
    }
}
