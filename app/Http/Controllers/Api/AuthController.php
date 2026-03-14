<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $login = $request->input('username');
        
        // Determinar si es email o username (DNI) como en la web
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $login)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Verificar el estado de la cuenta (0 = inactivo/pendiente, 1 = activo)
        if (!$user->estado) {
            return response()->json([
                'success' => false,
                'message' => 'Su cuenta está pendiente de verificación o ha sido desactivada.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nombre' => $user->nombre,
                'apellido_paterno' => $user->apellido_paterno,
                'apellido_materno' => $user->apellido_materno,
                'email' => $user->email,
                'foto_perfil' => $user->foto_perfil ? asset(\Illuminate\Support\Facades\Storage::url($user->foto_perfil)) : null,
                'fcm_token' => $user->fcm_token,
                'theme_preference' => $user->theme_preference ?? 'system',
                'roles' => $user->roles->pluck('nombre')->toArray(),
                'permissions' => \App\Models\Permission::whereHas('roles', function($q) use ($user) {
                    $q->whereIn('roles.id', $user->roles->pluck('id'));
                })->pluck('codigo')->unique()->values()->toArray(),
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->nombre,
            'apellido_paterno' => $user->apellido_paterno,
            'apellido_materno' => $user->apellido_materno,
            'email' => $user->email,
            'username' => $user->username,
            'numero_documento' => $user->numero_documento,
            'telefono' => $user->telefono,
            'roles' => $user->roles->pluck('nombre'),
        ]);
    }
}
