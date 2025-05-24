<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    /**
     * Obtener el perfil del usuario autenticado.
     */
    public function index()
    {
        $user = Auth::user()->load('roles');

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Actualizar la información del perfil.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'telefono' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user->email = $request->email;
        $user->telefono = $request->telefono;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Información actualizada exitosamente.',
            'data' => $user
        ]);
    }

    /**
     * Actualizar la contraseña del usuario.
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual no es correcta.'
            ], 422);
        }

        $user->password_hash = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente.'
        ]);
    }

    /**
     * Actualizar foto de perfil del usuario.
     */
    public function updateFoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'foto_perfil' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Eliminar foto anterior si existe
        if ($user->foto_perfil && Storage::exists('public/' . $user->foto_perfil)) {
            Storage::delete('public/' . $user->foto_perfil);
        }

        // Guardar nueva foto
        $path = $request->file('foto_perfil')->store('perfiles', 'public');

        $user->foto_perfil = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto de perfil actualizada correctamente.',
            'data' => [
                'foto_perfil' => $user->foto_perfil,
                'foto_url' => asset('storage/' . $user->foto_perfil)
            ]
        ]);
    }

    /**
     * Eliminar foto de perfil del usuario.
     */
    public function eliminarFoto()
    {
        $user = Auth::user();

        if ($user->foto_perfil && Storage::exists('public/' . $user->foto_perfil)) {
            Storage::delete('public/' . $user->foto_perfil);
        }

        $user->foto_perfil = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto de perfil eliminada correctamente.'
        ]);
    }

    /**
     * Actualizar preferencias del usuario.
     */
    public function updatePreferencias(Request $request)
    {
        $user = Auth::user();

        $user->notif_email = $request->has('notif_email');
        $user->notif_sistema = $request->has('notif_sistema');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferencias actualizadas correctamente.',
            'data' => [
                'notif_email' => $user->notif_email,
                'notif_sistema' => $user->notif_sistema
            ]
        ]);
    }
}
