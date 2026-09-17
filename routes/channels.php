<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user-security.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('device-auth.{authId}', function ($user, $authId) {
    return true; // Token-guarded channel access for pending authorization tab
});