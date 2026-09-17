<?php

namespace App\Listeners;

use App\Models\AuthenticationLog;
use Illuminate\Auth\Events\PasswordReset;

class LogPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        AuthenticationLog::log($event->user->id, AuthenticationLog::EVENT_PASSWORD_RESET);
    }
}