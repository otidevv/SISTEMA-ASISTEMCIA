<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;

class TeacherAttendanceReminder extends Notification
{
    use Queueable;

    public $type; // 'entrada' o 'salida'
    public $curso;

    public function __construct($type, $curso)
    {
        $this->type = $type;
        $this->curso = $curso;
    }

    public function via($notifiable)
    {
        return ['database', FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $title = $this->type === 'entrada' ? 'Recordatorio de Ingreso' : 'Recordatorio de Salida';
        $body = $this->type === 'entrada' 
            ? "Profesor, su clase de {$this->curso} ha iniciado. ¿Ya marcó su ingreso?"
            : "Su clase de {$this->curso} ha terminado. No olvide marcar su salida biométrica.";

        return [
            'title' => $title,
            'body' => $body,
            'extra' => [
                'type' => 'attendance_reminder',
                'action' => 'MARK_ASSISTANCE',
            ]
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => $this->type,
            'curso' => $this->curso,
            'message' => "Recordatorio de {$this->type} para {$this->curso}"
        ];
    }
}
