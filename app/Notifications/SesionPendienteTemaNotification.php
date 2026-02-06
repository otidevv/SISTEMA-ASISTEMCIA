<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SesionPendienteTemaNotification extends Notification
{
    use Queueable;

    protected $horario;
    protected $curso;
    protected $aula;

    /**
     * Create a new notification instance.
     */
    public function __construct($horario)
    {
        $this->horario = $horario;
        $this->curso = $horario->curso ? $horario->curso->nombre : 'Sin curso';
        $this->aula = $horario->aula ? $horario->aula->nombre : 'Sin aula';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for database.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'tema_pendiente',
            'title' => 'Tema de Clase Pendiente',
            'message' => "No has registrado el tema de tu clase de {$this->curso} en el aula {$this->aula}.",
            'link' => route('asistencia-docente.index'),
            'icon' => 'uil-comment-message',
            'color' => 'warning'
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'type' => 'tema_pendiente',
            'title' => 'Tema de Clase Pendiente',
            'message' => "No has registrado el tema de tu clase de {$this->curso} en el aula {$this->aula}.",
            'link' => route('asistencia-docente.index'),
            'icon' => 'uil-comment-message',
            'color' => 'warning'
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
