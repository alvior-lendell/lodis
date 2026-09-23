<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RegistrationSuccessfulNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Welcome to LODISv2!',
            'message' => 'Your account registration has been successfully verified and completed.',
            'url' => route('dashboard'),
            'type' => 'welcome',
        ];
    }
}