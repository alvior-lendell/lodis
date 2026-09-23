<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $newUser
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New User Registration',
            'message' => "{$this->newUser->name} ({$this->newUser->employee_id}) has registered a new account.",
            'url' => route('dashboard'),
            'type' => 'user_registered',
            'user_id' => $this->newUser->id,
            'employee_id' => $this->newUser->employee_id,
        ];
    }
}