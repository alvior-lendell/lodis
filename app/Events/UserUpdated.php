<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ?int $userId = null,
        public string $action = 'updated'
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('dashboard-events'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'system.updated';
    }
}