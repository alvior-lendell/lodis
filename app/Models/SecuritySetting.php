<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'min_password_length',
        'require_uppercase',
        'require_numeric',
        'require_special_char',
        'password_expiry_days',
        'max_login_attempts',
        'lockout_duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'min_password_length' => 'integer',
            'require_uppercase' => 'boolean',
            'require_numeric' => 'boolean',
            'require_special_char' => 'boolean',
            'password_expiry_days' => 'integer',
            'max_login_attempts' => 'integer',
            'lockout_duration_minutes' => 'integer',
        ];
    }

    /**
     * Helper to retrieve the single active security configuration record.
     */
    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}