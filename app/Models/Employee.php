<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'address',
        'birthdate',
        'contact_number',
        'email',
        'gender',
        'department',
        'position',
        'employment_status',
        'profile_photo_path',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    /**
     * Relationship to the underlying User account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor to combine full name with proper fallbacks.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        $name = trim(implode(' ', $parts));

        return $name ?: ($this->user->name ?? 'Employee User');
    }

    /**
     * Accessor for public avatar URL storage path.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->profile_photo_path ?? $this->user?->profile_photo_path;

        return $path ? Storage::url($path) : null;
    }

    /**
     * Display formatted birthdate (e.g. "August 01, 1999").
     */
    public function getFormattedBirthdateAttribute(): string
    {
        return $this->birthdate ? $this->birthdate->format('F d, Y') : 'Not Set';
    }

    /**
     * HTML input-compatible birthdate format (Y-m-d).
     */
    public function getRawBirthdateAttribute(): string
    {
        return $this->birthdate ? $this->birthdate->format('Y-m-d') : '';
    }
}