<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'holiday_type',
        'event_date',
        'is_recurring',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope query to fetch upcoming active events from today onward.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereDate('event_date', '>=', now()->toDateString())
            ->orderBy('event_date', 'asc');
    }
}