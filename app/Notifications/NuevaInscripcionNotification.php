<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevaInscripcionNotification extends Notification
{
    use Queueable;

    protected $estudiante;
    protected $ciclo;

    /**
     * Create a new notification instance.
     */
    public function __construct($inscripcion)
    {
        $this->estudiante = $inscripcion->estudiante ? $inscripcion->estudiante->nombre_completo : 'Estudiante';
        $this->ciclo = $inscripcion->ciclo ? $inscripcion->ciclo->nombre : 'Ciclo';
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
            'type' => 'nueva_inscripcion',
            'title' => 'Nueva Inscripción',
            'message' => "Se ha registrado una nueva inscripción: {$this->estudiante} en el {$this->ciclo}.",
            'link' => route('inscripciones.index'),
            'icon' => 'uil-user-plus',
            'color' => 'success'
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage([
            'type' => 'nueva_inscripcion',
            'title' => 'Nueva Inscripción',
            'message' => "Se ha registrado una nueva inscripción: {$this->estudiante} en el {$this->ciclo}.",
            'link' => route('inscripciones.index'),
            'icon' => 'uil-user-plus',
            'color' => 'success'
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
