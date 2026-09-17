<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class System extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'description',
        'logo',
        'is_active',
        'default_sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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