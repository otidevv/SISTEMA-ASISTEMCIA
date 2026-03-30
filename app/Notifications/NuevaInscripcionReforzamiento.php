<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NuevaInscripcionReforzamiento extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $nombreAlumno;
    public $inscripcionId;

    public function __construct($nombreAlumno, $inscripcionId)
    {
        $this->nombreAlumno = $nombreAlumno;
        $this->inscripcionId = $inscripcionId;
    }

    public function via(object $notifiable): array
    {
        // Guardar en DB y enviar por Broadcast (Tiempo Real)
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => '¡Nueva Inscripción Reforzamiento!',
            'message' => "El alumno {$this->nombreAlumno} se ha inscrito correctamente.",
            'icon' => 'uil-book-open',
            'color' => 'success',
            'link' => route('admin.reforzamiento.index'),
            'alumno_nombre' => $this->nombreAlumno,
            'inscripcion_id' => $this->inscripcionId,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => '¡Nueva Inscripción Reforzamiento!',
            'message' => "El alumno {$this->nombreAlumno} se ha inscrito correctamente.",
            'alumno_nombre' => $this->nombreAlumno,
            'inscripcion_id' => $this->inscripcionId,
        ]);
    }
}
