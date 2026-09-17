<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecuritySetting;
use App\Models\AuthenticationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpiredPasswordController extends Controller
{
    /**
     * Display the expired password update view.
     */
    public function show(): View
    {
        $securitySettings = SecuritySetting::instance();

        return view('auth.expired-password', compact('securitySettings'));
    }

    /**
     * Handle an incoming expired password update request.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $securitySettings = SecuritySetting::instance();

        // Build password rules dynamically based on SecuritySettings
        $passwordRule = Rules\Password::min($securitySettings->min_password_length ?? 8);
        if ($securitySettings->require_uppercase) {
            $passwordRule->mixedCase();
        }
        if ($securitySettings->require_numeric) {
            $passwordRule->numbers();
        }
        if ($securitySettings->require_special_char) {
            $passwordRule->symbols();
        }

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', $passwordRule],
        ]);

        // 1. Verify current password credentials
        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password does not match our records.'],
            ]);
        }

        // 2. Enforce Anti-Reuse Policy (checks active password AND historical hashes)
        $historyLimit = (int) ($securitySettings->password_history_limit ?? 3);
        if ($user->isPasswordReused($request->password, $historyLimit)) {
            throw ValidationException::withMessages([
                'password' => ["Security policy violation: You cannot reuse your current password or any of your last {$historyLimit} passwords."],
            ]);
        }

        // 3. Save existing active password hash to history before changing
        $user->recordPasswordHistory();

        // 4. Update user password and refresh password_changed_at timestamp
        $user->forceFill([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ])->save();

        AuthenticationLog::log($request->user()->id, AuthenticationLog::EVENT_PASSWORD_EXPIRED, $request);
        
        return redirect()->route('dashboard')
            ->with('status', 'Your password has been successfully updated.');
    }
}