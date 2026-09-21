<?php

namespace App\Models;

use App\Models\AuthenticationLog;
use App\Models\Employee;
use App\Models\PasswordHistory;
use App\Models\ProfileUpdateRequest;
use App\Models\System;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'employee_id',
        'password',
        'password_changed_at',
        'role',
        'is_active',
        'profile_photo_path',
        'email_verified_at',
        'failed_login_attempts',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'locked_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if the account is currently locked out.
     */
    public function isLockedOut(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Increment failed login counter and apply lockout if threshold is reached.
     */
    public function recordFailedLoginAttempt(int $maxAttempts = 5, int $lockoutMinutes = 15): void
    {
        $this->increment('failed_login_attempts');

        if ($this->failed_login_attempts >= $maxAttempts) {
            $this->update([
                'locked_until' => now()->addMinutes($lockoutMinutes),
                'failed_login_attempts' => 0,
            ]);
        }
    }

    /**
     * Clear failed attempts and remove active account lockouts.
     */
    public function resetLockout(): void
    {
        if ($this->failed_login_attempts > 0 || $this->locked_until !== null) {
            $this->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);
        }
    }

    /**
     * Get the employee profile associated with the user.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Get password history records ordered by newest first.
     */
    public function passwordHistories(): HasMany
    {
        return $this->hasMany(PasswordHistory::class)->latest();
    }

    /**
     * Check if a plain text password matches current or past passwords.
     */
    public function isPasswordReused(string $plainPassword, int $historyLimit = 3): bool
    {
        if ($this->password && Hash::check($plainPassword, $this->password)) {
            return true;
        }

        $pastPasswords = $this->passwordHistories()
            ->take($historyLimit)
            ->pluck('password');

        foreach ($pastPasswords as $hash) {
            if (Hash::check($plainPassword, $hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record current password hash into history before updating.
     */
    public function recordPasswordHistory(): void
    {
        if ($this->password) {
            $this->passwordHistories()->create([
                'password' => $this->password,
            ]);
        }
    }

    /**
     * Check if user has Superadmin role.
     */
    public function isSuperadmin(): bool
    {
        return $this->role === 'Superadmin';
    }

    /**
     * Check if user has Admin or higher role.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['Superadmin', 'Admin']);
    }

    /**
     * Send the custom branded password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Systems assigned to this user.
     */
    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(System::class, 'system_user')
            ->withPivot(['role', 'has_access', 'custom_order'])
            ->withTimestamps();
    }

    /**
     * Track user login and logout activity.
     */
    public function authenticationLogs(): HasMany
    {
        return $this->hasMany(AuthenticationLog::class)->latest();
    }

    /**
     * Profile update change requests submitted by this user.
     */
    public function profileUpdateRequests(): HasMany
    {
        return $this->hasMany(ProfileUpdateRequest::class)->latest();
    }

    /**
     * Profile update requests reviewed by this user (HR / Admin).
     */
    public function reviewedProfileRequests(): HasMany
    {
        return $this->hasMany(ProfileUpdateRequest::class, 'reviewed_by');
    }
    
    public function userDevices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }
    
    public function deviceAuthorizations(): HasMany
    {
        return $this->hasMany(DeviceAuthorization::class);
    }
}