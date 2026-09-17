<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'type',
        'day_of_week',
        'movable',
        'double_holiday',
        'double_holiday_names',
        'eid_confirmed',
        'estimated_date',
        'confirmed_date',
        'proclamation_ref',
        'is_part_of_long_weekend',
        'long_weekend_details',
        'source_info',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'date'                    => 'date',
        'estimated_date'          => 'date',
        'confirmed_date'          => 'date',
        'movable'                 => 'boolean',
        'double_holiday'          => 'boolean',
        'eid_confirmed'           => 'boolean',
        'is_part_of_long_weekend' => 'boolean',
        'is_active'               => 'boolean',
        'double_holiday_names'    => 'array',
        'long_weekend_details'    => 'array',
        'source_info'             => 'array',
    ];
}