<?php

namespace App\Observers;

use App\Models\System;
use App\Events\SystemUpdated;

class SystemObserver
{
    /**
     * Handle the System "created" event.
     */
    public function created(System $system): void
    {
        SystemUpdated::dispatch($system);
    }

    /**
     * Handle the System "updated" event.
     */
    public function updated(System $system): void
    {
        SystemUpdated::dispatch($system);
    }

    /**
     * Handle the System "deleted" event.
     */
    public function deleted(System $system): void
    {
        SystemUpdated::dispatch($system);
    }

    /**
     * Handle the System "restored" event.
     */
    public function restored(System $system): void
    {
        SystemUpdated::dispatch($system);
    }
}