<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Update this import
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventScheduleUpdated implements ShouldBroadcastNow // Implement ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eventData;

    public function __construct($eventData)
    {
        $this->eventData = $eventData;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('dashboard-events'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'schedule.updated';
    }
}