<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Broadcasts instantly via Reverb
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

abstract class BaseBroadcastNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * Always persist to database and broadcast via Reverb.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Enforce a standardized payload schema across all future notifications.
     */
    abstract public function buildPayload(object $notifiable): array;

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'notification_class' => static::class,
        ], $this->buildPayload($notifiable));
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}