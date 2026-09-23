<?php

namespace App\Events;

use App\Models\DeviceAuthorization;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceAuthorizationRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $requestData;

    public function __construct(DeviceAuthorization $authorization)
    {
        $this->requestData = [
            'id' => $authorization->id,
            'user_id' => $authorization->user_id,
            'device_name' => $authorization->device_name,
            'ip_address' => $authorization->ip_address,
            'location' => $authorization->location ?? 'Unknown Location',
            'created_at' => $authorization->created_at->diffForHumans(),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user-security.' . $this->requestData['user_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'device.approval.requested';
    }
}