<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->recordAudit('created', null, $model->getAuditAttributes());
        });

        static::updated(function ($model) {
            $changedKeys = array_keys($model->getChanges());
            // Filter out updated_at column from noise
            $changedKeys = array_diff($changedKeys, ['updated_at']);

            if (!empty($changedKeys)) {
                $oldValues = array_intersect_key($model->getOriginal(), array_flip($changedKeys));
                $newValues = array_intersect_key($model->getAttributes(), array_flip($changedKeys));

                $model->recordAudit('updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            $model->recordAudit('deleted', $model->getAuditAttributes(), null);
        });
    }

    protected function recordAudit(string $event, ?array $oldValues, ?array $newValues): void
    {
        // Strip sensitive fields (like passwords) defined in $hidden
        $hidden = array_flip($this->getHidden());
        if ($oldValues) $oldValues = array_diff_key($oldValues, $hidden);
        if ($newValues) $newValues = array_diff_key($newValues, $hidden);

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    protected function getAuditAttributes(): array
    {
        return array_diff_key($this->getAttributes(), array_flip($this->getHidden()));
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }
}