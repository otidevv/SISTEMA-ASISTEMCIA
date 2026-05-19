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
    protected $time;
    protected $location;

    public function __construct($student, $time, $location = 'Local Central')
    {
        $this->student = $student;
        // fecha_registro viene del servidor MySQL (NOW()), no del reloj del ZKTeco
        $this->time = Carbon::parse($time);
        $this->location = $location;
    }

    public function via($notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $nombreEstudiante = $this->student->nombre . ' ' . $this->student->apellido_paterno;
        $fecha = $this->time->format('d/m/Y');
        $hora = $this->time->format('h:i A'); // Formato 12h con AM/PM
        
        // Determinar si el destinatario es el propio estudiante o un padre
        // Comparamos IDs como strings y verificamos el rol explícitamente para mayor seguridad
        $esParaAlumno = ((string) $notifiable->id === (string) $this->student->id) && 
                        !$notifiable->hasRole('padre') && 
                        !$notifiable->hasRole('apoderado');
        
        // Lógica de rangos (minutos desde las 00:00)
        $minutosTotales = $this->time->hour * 60 + $this->time->minute;
        
        $turno = "";
        $situacion = "";
        $emoji = "📢";
        $mensaje = "";

        // Rangos según server.js de Node.js
        if ($minutosTotales >= (6*60+30) && $minutosTotales <= (14*60+0)) {
            $turno = "MAÑANA";
            if ($minutosTotales <= (7*60+25)) {
                $situacion = "ENTRADA NORMAL";
                $emoji = "✅";
                $mensaje = $esParaAlumno ? "has ingresado puntualmente a tus clases." : "ingresó puntualmente a sus clases.";
            } elseif ($minutosTotales <= (10*60+20)) {
                $situacion = "ENTRADA TARDE";
                $emoji = "⏰";
                $mensaje = $esParaAlumno ? "has ingresado tarde a tus clases." : "ingresó tarde a sus clases.";
            } else {
                $situacion = "SALIDA NORMAL";
                $emoji = "🏠";
                $mensaje = $esParaAlumno ? "has finalizado tus clases con normalidad." : "finalizó sus clases con normalidad.";
            }
        } elseif ($minutosTotales >= (14*60+30) && $minutosTotales <= (22*60+0)) {
            $turno = "TARDE";
            if ($minutosTotales <= (15*60+15)) {
                $situacion = "ENTRADA NORMAL";
                $emoji = "✅";
                $mensaje = $esParaAlumno ? "has ingresado puntualmente a tus clases." : "ingresó puntualmente a sus clases.";
            } elseif ($minutosTotales <= (19*60+29)) {
                $situacion = "ENTRADA TARDE";
                $emoji = "⏰";
                $mensaje = $esParaAlumno ? "has ingresado tarde a tus clases." : "ingresó tarde a sus clases.";
            } else {
                $situacion = "SALIDA NORMAL";
                $emoji = "🏠";
                $mensaje = $esParaAlumno ? "has finalizado tus clases con normalidad." : "finalizó sus clases con normalidad.";
            }
        } else {
            $turno = ($minutosTotales < 12*60) ? "MAÑANA" : "TARDE";
            $situacion = "REGISTRO GENERAL";
            $emoji = "📋";
            $mensaje = $esParaAlumno ? "has registrado tu asistencia." : "ha registrado su asistencia.";
        }

        $title = "{$emoji} Asistencia: {$this->student->nombre}";
        
        $intro = $esParaAlumno 
            ? "Hola {$this->student->nombre}, te informamos que " 
            : "Estimado(a) padre/madre de familia, le informamos que su hijo(a) {$nombreEstudiante} ";

        $body = "{$intro}{$mensaje}\n\n" .
                "🌅 Turno: {$turno}\n" .
                "🕒 Hora: {$hora}\n" .
                "📌 Estado: {$situacion}";

        return [
            'title' => $title,
            'body' => $body,
            'extra' => [
                'type' => 'student_attendance',
                'student_id' => (string) $this->student->id,
                'click_action' => 'OPEN_ATTENDANCE_HISTORY',
            ]
        ];
    }
}
