<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        // Check if the password field is being modified
        if ($user->isDirty('password') && $user->getOriginal('password')) {
            // Insert the OLD password hash into password_histories before the record is overwritten
            $user->passwordHistories()->create([
                'password'   => $user->getOriginal('password'),
                'created_at' => now(),
            ]);
        }
    }
}