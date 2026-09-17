<?php

namespace App\Http\Middleware;

use App\Models\SecuritySetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Exempted named routes
        $exceptRoutes = [
            'password.expired',
            'password.expired.update',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $exceptRoutes, true)) {
            return $next($request);
        }

        $securitySettings = SecuritySetting::instance();
        $expiryDays = (int) ($securitySettings->password_expiry_days ?? 0);

        if ($expiryDays > 0) {
            $lastChanged = $user->password_changed_at ?? $user->created_at;

            // Check if the timestamp plus expiry days is in the past
            if ($lastChanged && $lastChanged->copy()->addDays($expiryDays)->isPast()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your password has expired. Please update your password to proceed.',
                    ], 403);
                }

                return redirect()->route('password.expired')
                    ->with('warning', "Your security policy requires a password reset every {$expiryDays} days.");
            }
        }

        return $next($request);
    }
}