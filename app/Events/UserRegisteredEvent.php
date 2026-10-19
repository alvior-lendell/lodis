<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegisteredEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $newUser,
        public int $adminId,
        public ?string $notificationId = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user-security.{$this->adminId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.registered';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->newUser->id,
            'notification_id' => $this->notificationId, // Database Notification UUID
            'title'           => 'New User Registration',
            'message'         => "{$this->newUser->name} ({$this->newUser->employee_id}) registered a new account.",
            'url'             => route('users.index'),
            'type'            => 'user_registered',
        ];
    }
}