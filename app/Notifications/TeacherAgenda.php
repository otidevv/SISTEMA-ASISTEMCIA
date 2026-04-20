<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\FcmChannel;

class TeacherAgenda extends Notification
{
    use Queueable;

    public $classes;
    public $isTomorrow;

    public function __construct($classes, $isTomorrow = false)
    {
        $this->classes = $classes;
        $this->isTomorrow = $isTomorrow;
    }

    public function via($notifiable)
    {
        return ['database', FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $dayLabel = $this->isTomorrow ? 'Mañana' : 'Hoy';
        $title = "🗓️ Su Agenda para {$dayLabel}";
        
        $body = "Tiene " . count($this->classes) . " clases programadas:\n";
        foreach (array_slice($this->classes, 0, 3) as $class) {
            $body .= "• {$class['hora']} - {$class['curso']}\n";
        }
        
        if (count($this->classes) > 3) {
            $body .= "Y " . (count($this->classes) - 3) . " más...";
        }

        return [
            'title' => $title,
            'body' => $body,
            'extra' => [
                'type' => 'teacher_agenda',
                'is_tomorrow' => $this->isTomorrow,
                'click_action' => 'OPEN_SCHEDULE',
            ]
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => $this->isTomorrow ? 'Agenda de Mañana' : 'Agenda de Hoy',
            'classes' => $this->classes
        ];
    }
}
