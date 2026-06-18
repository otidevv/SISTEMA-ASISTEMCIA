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
    protected $esTardanza;
    protected $minutosTardanza;

    public function __construct($teacher, $time, $type, $curso = null, $esTardanza = false, $minutosTardanza = 0)
    {
        $this->teacher = $teacher;
        $this->time = Carbon::parse($time);
        $this->type = $type;
        $this->curso = $curso;
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

        if ($this->type === 'entrada') {
            if ($this->esTardanza) {
                $emoji = '⏰';
                $tipoRegistro = 'Ingreso con tardanza';
                $detalle = "Registraste tu entrada con {$this->minutosTardanza} min de tardanza.";
            } else {
                $emoji = '✅';
                $tipoRegistro = 'Ingreso puntual';
                $detalle = 'Se ha registrado tu entrada correctamente. ¡Puntual!';
            }
        } else {
            $emoji = '🏠';
            $tipoRegistro = 'Salida registrada';
            $detalle = 'Se ha registrado tu salida correctamente.';
        }

        $title = "{$emoji} Asistencia: {$tipoRegistro}";

        $body = "Hola Prof. {$this->teacher->nombre},\n" .
                "{$detalle}\n\n" .
                ($this->curso ? "📚 Curso: {$this->curso}\n" : "") .
                "🕒 Hora: {$hora}";

        // Foto del docente para mostrarla en la notificación push.
        $fotoUrl = !empty($this->teacher->foto_perfil)
            ? asset('storage/' . $this->teacher->foto_perfil)
            : null;

        return [
            'title' => $title,
            'body' => $body,
            'image' => $fotoUrl,
            'extra' => [
                'type' => 'teacher_fingerprint_attendance',
                'teacher_id' => (string) $this->teacher->id,
                'student_photo' => $fotoUrl ?? '',
                'es_tardanza' => $this->esTardanza ? '1' : '0',
                'click_action' => 'OPEN_DASHBOARD',
            ]
        ];
    }
}
