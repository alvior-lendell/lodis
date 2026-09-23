<?php

namespace App\Notifications;

use App\Models\DeviceAuthorization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeviceSignInAttemptNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DeviceAuthorization $authorization
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Security Alert: New Workstation Sign-In',
            'message' => "Sign-in attempt detected from {$this->authorization->device_name} (IP: {$this->authorization->ip_address}).",
            'url' => route('device.review', $this->authorization->id),
            'type' => 'security_alert',
            'device_id' => $this->authorization->id,
            'ip_address' => $this->authorization->ip_address,
        ];
    }
}