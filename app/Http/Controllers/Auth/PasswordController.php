<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthenticationLog;
use App\Models\PasswordHistory;
use App\Models\SecuritySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password with password reuse enforcement.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Fetch application security settings
        $securitySettings = SecuritySetting::first();
        $reuseCount = $securitySettings?->prevent_password_reuse_count ?? 5;

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                Password::defaults(),
                'confirmed',
                function ($attribute, $value, $fail) use ($user, $reuseCount) {
                    // 1. Prevent using current password
                    if (Hash::check($value, $user->password)) {
                        $fail('Your new password cannot be the same as your current password.');
                        return;
                    }

                    // 2. Prevent reusing recent historical passwords
                    $historyItems = PasswordHistory::where('user_id', $user->id)
                        ->latest()
                        ->take($reuseCount)
                        ->get();

                    foreach ($historyItems as $history) {
                        $historicalHash = $history->password_hash ?? $history->password;
                        if ($historicalHash && Hash::check($value, $historicalHash)) {
                            $fail("You cannot reuse any of your last {$reuseCount} passwords.");
                            return;
                        }
                    }
                },
            ],
        ]);

        // Update active user password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Record new password into history log
        PasswordHistory::create([
            'user_id' => $user->id,
            'password_hash' => $user->password,
            'ip_address' => $request->ip(),
            'event' => 'password_change',
        ]);

        // Log security audit event
        AuthenticationLog::create([
            'user_id' => $user->id,
            'event' => 'password_change',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('status', 'password-updated');
    }
}