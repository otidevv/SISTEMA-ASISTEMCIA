<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;
use App\Models\Solicitud;

/**
 * Notificación genérica del módulo de Solicitudes / Mesa de Partes.
 * Se envía por dos canales: push (FCM) y base de datos (campanita).
 */
class SolicitudNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $titulo,
        protected string $mensaje,
        protected Solicitud $solicitud,
        protected array $extra = []
    ) {}

    public function via($notifiable): array
    {
        // 'database' primero (campanita) para que se registre aunque el push (FCM) falle
        return ['database', FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => $this->titulo,
            'body' => $this->mensaje,
            'extra' => array_merge([
                'type' => 'solicitud',
                'solicitud_id' => (string) $this->solicitud->id,
                'codigo' => (string) $this->solicitud->codigo,
                'estado' => (string) $this->solicitud->estado,
                'click_action' => 'OPEN_SOLICITUD_DETAIL',
            ], array_map('strval', $this->extra)),
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'solicitud_id' => $this->solicitud->id,
            'codigo' => $this->solicitud->codigo,
            'estado' => $this->solicitud->estado,
            'link' => url('/solicitudes/' . $this->solicitud->id),
        ];
    }
}
