<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;
use Carbon\Carbon;

class AttendanceNotification extends Notification
{
    use Queueable;

    protected $student;
    protected $type; // 'entrada' or 'salida'
    protected $time;
    protected $location;

    public function __construct($student, $type, $time, $location = 'Local Central')
    {
        $this->student = $student;
        $this->type = $type;
        $this->time = Carbon::parse($time);
        $this->location = $location;
    }

    public function via($notifiable): array
    {
        return [FcmChannel::class]; // Generalmente solo Push para padres
    }

    public function toFcm($notifiable)
    {
        $verb = ($this->type === 'entrada') ? 'INGRESADO al' : 'SALIDO del';
        $title = "Aviso de Asistencia: " . $this->student->nombre;
        $body = "Su hijo(a) {$this->student->nombre} ha {$verb} {$this->location} a las " . $this->time->format('H:i A') . ".";

        return [
            'title' => $title,
            'body' => $body,
            'extra' => [
                'type' => 'student_attendance',
                'student_id' => (string) $this->student->id,
                'attendance_type' => $this->type,
                'click_action' => 'OPEN_ATTENDANCE_HISTORY',
            ]
        ];
    }
}
