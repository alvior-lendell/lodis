<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'employee_id',
        'otp',
        'payload',
        'resend_attempts',
        'is_used',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
            'resend_attempts' => 'integer',
        ];
    }
}