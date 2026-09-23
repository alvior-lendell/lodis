<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AuthenticationLog extends Model
{
    use Auditable;
    
    public $timestamps = false;

    public const EVENT_LOGIN = 'login';
    public const EVENT_LOGOUT = 'logout';
    public const EVENT_NEW_DEVICE = 'new_device_login';
    public const EVENT_PASSWORD_CHANGE = 'password_change';
    public const EVENT_PASSWORD_RESET = 'password_reset';
    public const EVENT_PASSWORD_EXPIRED = 'password_expired';

    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to log any security event directly.
     */
    public static function log(int $userId, string $event, ?Request $request = null): self
    {
        $request = $request ?? request();

        return self::create([
            'user_id'    => $userId,
            'event'      => $event,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}