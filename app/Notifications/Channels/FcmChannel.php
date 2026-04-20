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

        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $data = $notification->toFcm($notifiable);
        
        $fcmService = app(\App\Services\FcmService::class);
        
        $fcmService->sendNotification(
            $fcmToken,
            $data['title'] ?? 'Notificación CEPRE',
            $data['body'] ?? '',
            $data['extra'] ?? [],
            $data['image'] ?? null
        );
    }
}
