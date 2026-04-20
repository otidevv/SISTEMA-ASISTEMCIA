<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;

class PostulacionStatusNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $status;
    protected $postulacion;

    public function __construct($status, $postulacion)
    {
        $this->status = $status;
        $this->postulacion = $postulacion;

        if ($status === 'aprobado') {
            $this->title = "¡Felicidades! Inscripción Aprobada";
            $this->body = "Tu expediente ha sido verificado y aprobado. Ya puedes continuar con tu proceso.";
        } elseif ($status === 'observado') {
            $this->title = "Atención: Expediente con Observaciones";
            $this->body = "Tu inscripción ha sido observada. Por favor, revisa los detalles y corrige los documentos.";
        } else {
            $this->title = "Actualización de Postulación";
            $this->body = "El estado de tu postulación ha cambiado a: " . ucfirst($status);
        }
    }

    public function via($notifiable): array
    {
        return [FcmChannel::class, 'database'];
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'extra' => [
                'type' => 'postulacion_status',
                'status' => $this->status,
                'postulacion_id' => (string) $this->postulacion->id,
                'click_action' => 'OPEN_POSTULACION_DETAIL',
            ]
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            'titulo' => $this->title,
            'mensaje' => $this->body,
            'status' => $this->status,
            'postulacion_id' => $this->postulacion->id,
        ];
    }
}
