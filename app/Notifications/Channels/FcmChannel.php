<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        $fcmToken = $notifiable->fcm_token;

        if (!$fcmToken) {
            return;
        }

        // Obtener los datos para Firebase de la notificación
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $data = $notification->toFcm($notifiable);

        // NOTA: Para producción, aquí se implementaría la llamada a la Firebase HTTP v1 API
        // usando el archivo de credenciales de service account.
        // Por ahora lo simulamos en logs o con una llamada genérica.
        
        Log::info("FCM Sent to {$notifiable->email}: " . json_encode($data));
        
        /* 
        // Ejemplo de implementación real:
        $response = Http::withToken($this->getAccessToken())
            ->post('https://fcm.googleapis.com/v1/projects/YOUR_PROJECT_ID/messages:send', [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $data['title'],
                        'body' => $data['body'],
                    ],
                    'data' => $data['extra'] ?? [],
                ]
            ]);
        */
    }
}
