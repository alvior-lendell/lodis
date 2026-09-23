<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'user_id',
        'device_key',
        'device_name',
        'platform',
        'browser',
        'ip_address',
        'location',
        'latitude',   // <-- ADD THIS
        'longitude',
        'is_trusted',
        'user_agent',
        'last_active_at',
    ];

    protected $casts = [
        'is_trusted' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}