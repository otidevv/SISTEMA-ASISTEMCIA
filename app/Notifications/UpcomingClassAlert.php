<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;

class UpcomingClassAlert extends Notification
{
    use Queueable;

    public $curso;
    public $hora;
    public $aula;

    public function __construct($curso, $hora, $aula = null)
    {
        $this->curso = $curso;
        $this->hora = $hora;
        $this->aula = $aula;
    }

    public function via($notifiable)
    {
        return ['database', FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $body = "Su clase de {$this->curso} inicia a las {$this->hora}";
        if ($this->aula) {
            $body .= " en el Aula {$this->aula}";
        }

        return [
            'title' => '🔔 Recordatorio de Clase',
            'body' => $body,
            'extra' => [
                'type' => 'upcoming_class',
                'curso' => $this->curso,
                'hora' => $this->hora,
                'click_action' => 'OPEN_CURRENT_CLASS',
            ]
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'curso' => $this->curso,
            'hora' => $this->hora,
            'aula' => $this->aula
        ];
    }
}
