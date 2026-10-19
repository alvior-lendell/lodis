<?php

namespace App\Notifications;

use App\Models\DeviceAuthorization;

class DeviceSignInAttemptNotification extends BaseBroadcastNotification
{
    public function __construct(public DeviceAuthorization $authorization) {}

    public function buildPayload(object $notifiable): array
    {
        return [
            'title'     => 'Security Alert: New Sign-In',
            'message'   => "Sign-in attempt detected from {$this->authorization->device_name} ({$this->authorization->ip_address}).",
            'url'       => route('device.review', $this->authorization->id),
            'category'  => 'security',
            'device_id' => $this->authorization->id,
        ];
    }
}