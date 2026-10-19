<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OtpVerification extends Model
{
    use HasFactory, Auditable;

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
            'payload'         => 'array',
            'is_used'         => 'boolean',
            'expires_at'      => 'datetime',
            'resend_attempts' => 'integer',
        ];
    }

    /**
     * Safely decrypt the OTP code or return a fallback badge for legacy hashes.
     */
    public function getDecryptedOtpAttribute(): string
    {
        if (empty($this->otp)) {
            return '------';
        }

        try {
            return Crypt::decryptString($this->otp);
        } catch (DecryptException $e) {
            // Fallback display for legacy Bcrypt hashed records
            return '******';
        }
    }

    /**
     * Automatically encrypt the OTP code upon creation/update.
     */
    public function setOtpAttribute($value): void
    {
        $this->attributes['otp'] = Crypt::encryptString($value);
    }
}