<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class System extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'url',
        'description',
        'logo',
        'is_active',
        'is_online',
        'last_ping_at',
        'default_sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_ping_at' => 'datetime',
        'default_sort_order' => 'integer',
    ];

    /**
     * Users/Employees assigned to this system.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'system_user')
            ->withPivot(['role', 'has_access', 'custom_order'])
            ->withTimestamps();
    }
}