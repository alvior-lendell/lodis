<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthenticationLog;
use App\Models\PasswordHistory;
use App\Models\SecuritySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password with password reuse enforcement.
     */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $securitySettings = class_exists(SecuritySetting::class) && method_exists(SecuritySetting::class, 'instance')
            ? SecuritySetting::instance()
            : SecuritySetting::first();

        $reuseCount = $securitySettings?->prevent_password_reuse_count ?? 5;

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    try {
                        if (! Hash::check($value, $user->password)) {
                            $fail('The password is incorrect.');
                        }
                    } catch (\Throwable $e) {
                        $fail('The password is incorrect.');
                    }
                },
            ],
            'password' => [
                'required',
                Password::defaults(),
                'confirmed',
                function ($attribute, $value, $fail) use ($user, $reuseCount) {
                    // 1. Prevent using current password
                    try {
                        if (Hash::check($value, $user->password)) {
                            $fail('Your new password cannot be the same as your current password.');
                            return;
                        }
                    } catch (\Throwable $e) {
                        // Skip if active hash is invalid
                    }

                    // 2. Prevent reusing recent historical passwords
                    try {
                        $historyItems = PasswordHistory::where('user_id', $user->id)
                            ->latest()
                            ->take($reuseCount)
                            ->get();

                        foreach ($historyItems as $history) {
                            $historicalHash = $history->password ?? $history->password_hash ?? null;
                            if ($historicalHash) {
                                try {
                                    if (Hash::check($value, $historicalHash)) {
                                        $fail("You cannot reuse any of your last {$reuseCount} passwords.");
                                        return;
                                    }
                                } catch (\Throwable $e) {
                                    // Skip invalid historical entries
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Password history evaluation skipped: ' . $e->getMessage());
                    }
                },
            ],
        ]);

        // Assign plain password (Eloquent's 'password' => 'hashed' cast hashes it ONCE automatically)
        $user->password = $validated['password'];
        $user->password_changed_at = now();
        $user->save();

        // Record the resulting hash into history log
        try {
            PasswordHistory::create([
                'user_id'    => $user->id,
                'password'   => $user->password, // Saves the newly generated Bcrypt hash
                'ip_address' => $request->ip(),
                'event'      => 'password_change',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to record password history: ' . $e->getMessage());
        }

        // Log security audit event
        try {
            if (class_exists(AuthenticationLog::class)) {
                AuthenticationLog::create([
                    'user_id'          => $user->id,
                    'ip_address'       => $request->ip(),
                    'user_agent'       => $request->userAgent(),
                    'login_successful' => true,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to record authentication audit log: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Password updated successfully.',
                'status'  => 'password-updated',
            ]);
        }

        return back()->with('status', 'password-updated');
    }
}