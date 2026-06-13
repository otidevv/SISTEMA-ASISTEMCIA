<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;
use Carbon\Carbon;

class TeacherFingerprintNotification extends Notification
{
    use Queueable;

    protected $teacher;
    protected $time;
    protected $type; // 'entrada' o 'salida'
    protected $curso;

    public function __construct($teacher, $time, $type, $curso = null)
    {
        $this->teacher = $teacher;
        $this->time = Carbon::parse($time);
        $this->type = $type;
        $this->curso = $curso;
    }

    public function via($notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $hora = $this->time->format('h:i A');
        $emoji = $this->type === 'entrada' ? '✅' : '🏠';
        $tipoRegistro = $this->type === 'entrada' ? 'Ingreso registrado' : 'Salida registrada';
        
        $title = "{$emoji} Asistencia: {$tipoRegistro}";
        
        $body = "Hola Prof. {$this->teacher->nombre},\n" .
                "Se ha registrado tu " . ($this->type === 'entrada' ? 'entrada' : 'salida') . " correctamente.\n\n" .
                ($this->curso ? "📚 Curso: {$this->curso}\n" : "") .
                "🕒 Hora: {$hora}";

        return [
            'title' => $title,
            'body' => $body,
            'extra' => [
                'type' => 'teacher_fingerprint_attendance',
                'teacher_id' => (string) $this->teacher->id,
                'click_action' => 'OPEN_DASHBOARD',
            ]
        ];
    }
}
