<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralAnnouncement extends Notification implements ShouldQueue
{
    use Queueable;

    public $title;
    public $message;
    public $image;
    public $fileUrl;
    public $fileType;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $image = null, $fileUrl = null, $fileType = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->image = $image;
        $this->fileUrl = $fileUrl;
        $this->fileType = $fileType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', \App\Notifications\Channels\FcmChannel::class];
    }

    /**
     * FCM representation.
     */
    public function toFcm($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->message,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'extra' => [
                'type' => 'general_announcement',
                'file_url' => $this->fileUrl ? asset('storage/' . $this->fileUrl) : null,
                'file_type' => $this->fileType, // pdf, mp4, docx, etc.
                'click_action' => 'OPEN_ANNOUNCEMENT_DETAIL',
            ]
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'image' => $this->image,
            'file_url' => $this->fileUrl,
            'file_type' => $this->fileType,
        ];
    }
}
