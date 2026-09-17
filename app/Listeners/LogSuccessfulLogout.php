<?php

namespace App\Listeners;

use App\Models\AuthenticationLog;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogSuccessfulLogout
{
    public function __construct(protected Request $request) {}

    public function handle(Logout $event): void
    {
        if ($event->user) {
            AuthenticationLog::create([
                'user_id'    => $event->user->id,
                'event'      => 'logout',
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'created_at' => now(),
            ]);
        }
    }
}
