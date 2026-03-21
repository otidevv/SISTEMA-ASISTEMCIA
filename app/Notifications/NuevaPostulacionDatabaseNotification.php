<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevaPostulacionDatabaseNotification extends Notification
{
    use Queueable;

    protected $nombrePostulante;
    protected $carrera;

    /**
     * Create a new notification instance.
     */
    public function __construct($nombrePostulante, $carrera)
    {
        $this->nombrePostulante = $nombrePostulante;
        $this->carrera = $carrera;
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
            'type' => 'nueva_postulacion',
            'title' => 'Nueva Postulación Reverb',
            'message' => "Se ha registrado el estudiante {$this->nombrePostulante} en {$this->carrera}.",
            'link' => route('postulaciones.index'),
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
            'type' => 'nueva_postulacion',
            'title' => 'Nueva Postulación Reverb',
            'message' => "Se ha registrado el estudiante {$this->nombrePostulante} en {$this->carrera}.",
            'link' => route('postulaciones.index'),
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
        return [];
    }
}
