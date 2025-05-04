<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    /**
     * Mostrar el perfil del usuario.
     */
    public function index()
    {
        $user = Auth::user();
        return view('perfil.index', compact('user'));
    }

    /**
     * Mostrar la configuración del perfil.
     * Nota: Esta función podría no ser necesaria si usamos pestañas en lugar de páginas separadas
     */
    public function configuracion()
    {
        $user = Auth::user();
        return view('perfil.configuracion', compact('user'));
    }

    /**
     * Actualizar la información del perfil.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'telefono' => 'nullable|string|max:20',
        ]);

        $user->email = $request->email;
        $user->telefono = $request->telefono;
        $user->save();

        return redirect()->back()->with('info_success', 'Información actualizada exitosamente.');
    }

    /**
     * Actualizar la contraseña del usuario.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return back()->with('password_error', 'La contraseña actual no es correcta.');
        }

        $user->password_hash = Hash::make($request->password);
        $user->save();

        return back()->with('password_success', 'Contraseña actualizada exitosamente.');
    }

    /**
     * Actualizar foto de perfil del usuario.
     */
    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto_perfil' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        // Eliminar foto anterior si existe
        if ($user->foto_perfil && Storage::exists('public/' . $user->foto_perfil)) {
            Storage::delete('public/' . $user->foto_perfil);
        }

        // Guardar nueva foto
        $path = $request->file('foto_perfil')->store('perfiles', 'public');

        $user->foto_perfil = $path;
        $user->save();

        return back()->with('foto_success', 'Foto de perfil actualizada correctamente.');
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

        return back()->with('foto_success', 'Foto de perfil eliminada correctamente.');
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

        return back()->with('pref_success', 'Preferencias actualizadas correctamente.');
    }
}
