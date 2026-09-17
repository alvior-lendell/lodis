<?php

namespace App\Observers;

use App\Models\Event;
use App\Events\EventScheduleUpdated;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        EventScheduleUpdated::dispatch($event);
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        EventScheduleUpdated::dispatch($event);
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        EventScheduleUpdated::dispatch($event);
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        EventScheduleUpdated::dispatch($event);
    }
}