<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;
use Carbon\Carbon;

class AdminTeacherAttendanceAlertNotification extends Notification
{
    use Queueable;

    protected $teacher;
    protected $time;
    protected $type; // 'entrada' o 'salida'
    protected $curso;
    protected $aula;
    protected $esTardanza;
    protected $minutosTardanza;

    public function __construct($teacher, $time, $type, $curso = null, $aula = null, $esTardanza = false, $minutosTardanza = 0)
    {
        $this->teacher = $teacher;
        $this->time = Carbon::parse($time);
        $this->type = $type;
        $this->curso = $curso;
        $this->aula = $aula;
        $this->esTardanza = $esTardanza;
        $this->minutosTardanza = $minutosTardanza;
    }

    public function via($notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $hora = $this->time->format('h:i A');
        $nombreDocente = trim("{$this->teacher->nombre} {$this->teacher->apellido_paterno}");

        if ($this->type === 'entrada') {
            if ($this->esTardanza) {
                $emoji = '⚠️';
                $title = "Monitoreo: Ingreso con Tardanza";
                $body = "El docente {$nombreDocente} registró su INGRESO con {$this->minutosTardanza} min de tardanza a las {$hora}.\n" .
                        "📚 Curso: " . ($this->curso ?? 'N/A') . "\n" .
                        "🚪 Aula: " . ($this->aula ?? 'N/A');
            } else {
                $emoji = '🔔';
                $title = "Monitoreo: Ingreso Registrado";
                $body = "El docente {$nombreDocente} registró su INGRESO puntual a las {$hora}.\n" .
                        "📚 Curso: " . ($this->curso ?? 'N/A') . "\n" .
                        "🚪 Aula: " . ($this->aula ?? 'N/A');
            }
        } else {
            $emoji = '🚪';
            $title = "Monitoreo: Salida Registrada";
            $body = "El docente {$nombreDocente} registró su SALIDA a las {$hora}.\n" .
                    "📚 Curso: " . ($this->curso ?? 'N/A') . "\n" .
                    "🚪 Aula: " . ($this->aula ?? 'N/A');
        }

        // Imagen de perfil del docente para hacer la notificación premium
        $fotoUrl = !empty($this->teacher->foto_perfil)
            ? asset('storage/' . $this->teacher->foto_perfil)
            : null;

        return [
            'title' => "{$emoji} {$title}",
            'body' => $body,
            'image' => $fotoUrl,
            'extra' => [
                'type' => 'admin_teacher_attendance_alert',
                'teacher_id' => (string) $this->teacher->id,
                'student_photo' => $fotoUrl ?? '',
                'click_action' => 'OPEN_MONITORING',
            ]
        ];
    }
}
