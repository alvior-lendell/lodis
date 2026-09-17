<?php

namespace App\Listeners;

use App\Models\AuthenticationLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    public function __construct(protected Request $request) {}

    public function handle(Login $event): void
    {
        AuthenticationLog::create([
            'user_id'    => $event->user->id,
            'event'      => 'login',
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'created_at' => now(),
        ]);
    }
}