<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Muestra la vista con todas las notificaciones del usuario autenticado.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(15); // Paginar para no cargar todo de golpe

        // Marcar todas las notificaciones no leídas como leídas al visitar la página
        $user->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Devuelve las últimas notificaciones no leídas en formato JSON.
     * Ideal para un dropdown de notificaciones.
     */
    public function fetch()
    {
        $user = Auth::user();
        $unreadNotifications = $user->unreadNotifications()->take(5)->get();
        $unreadCount = $user->unreadNotifications->count();

        return response()->json([
            'notifications' => $unreadNotifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Marca una notificación específica como leída y redirige.
     */
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        // Redirigir al link de la notificación si existe, si no, a la lista de notificaciones
        $redirectUrl = $notification->data['link'] ?? route('notifications.index');

        return redirect($redirectUrl);
    }
} 