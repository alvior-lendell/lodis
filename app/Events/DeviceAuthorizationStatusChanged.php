<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceAuthorizationStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $authorizationId;
    public string $status;

    public function __construct(string $authorizationId, string $status)
    {
        $this->authorizationId = $authorizationId;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('device-auth.' . $this->authorizationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'device.status.changed';
    }
}