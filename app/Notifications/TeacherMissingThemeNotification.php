<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;

class TeacherMissingThemeNotification extends Notification
{
    use Queueable;

    protected $curso;
    protected $horario;

    public function __construct($curso, $horario = null)
    {
        $this->curso = $curso;
        $this->horario = $horario;
    }

    public function via($notifiable): array
    {
        return [FcmChannel::class, 'database'];
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => "📝 Registro de Tema Pendiente",
            'body' => "Profesor, tiene un registro de tema pendiente para la clase de {$this->curso} de hoy.",
            'extra' => [
                'type' => 'missing_theme_reminder',
                'curso' => $this->curso,
                'click_action' => 'OPEN_THEME_REGISTRATION',
            ]
        ];
    }

    public function toArray($notifiable): array
    {
        return [
            'titulo' => "Tema Pendiente",
            'mensaje' => "Falta registrar el tema desarrollado en la clase de {$this->curso}.",
        ];
    }
}
