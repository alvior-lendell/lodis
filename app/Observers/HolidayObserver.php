<?php

namespace App\Observers;

use App\Models\Holiday;
use App\Events\EventScheduleUpdated;

class HolidayObserver
{
    public function created(Holiday $holiday): void
    {
        EventScheduleUpdated::dispatch(['type' => 'holiday', 'action' => 'created', 'id' => $holiday->id]);
    }

    public function updated(Holiday $holiday): void
    {
        EventScheduleUpdated::dispatch(['type' => 'holiday', 'action' => 'updated', 'id' => $holiday->id]);
    }

    public function deleted(Holiday $holiday): void
    {
        EventScheduleUpdated::dispatch(['type' => 'holiday', 'action' => 'deleted', 'id' => $holiday->id]);
    }
}