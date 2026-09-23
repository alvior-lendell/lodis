<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ProfileUpdateRequest extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'user_id',
        'employee_id',
        'payload',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /**
     * User who submitted the request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Employee record targeted by this request.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * User (HR / Admin) who reviewed the request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Apply approved profile changes directly to the employee record.
     */
    public function approve(int $reviewerId): bool
    {
        return DB::transaction(function () use ($reviewerId) {
            if ($this->employee) {
                $this->employee->update($this->payload);
            }

            return $this->update([
                'status' => 'approved',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);
        });
    }
}