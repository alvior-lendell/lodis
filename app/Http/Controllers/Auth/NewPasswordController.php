<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SecuritySetting;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        $securitySettings = SecuritySetting::instance();

        return view('auth.reset-password', [
            'request' => $request,
            'securitySettings' => $securitySettings,
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        $settings = SecuritySetting::instance();

        $passwordRule = Rules\Password::min($settings->min_password_length);
        if ($settings->require_uppercase) {
            $passwordRule->mixedCase();
        }
        if ($settings->require_numeric) {
            $passwordRule->numbers();
        }
        if ($settings->require_special_char) {
            $passwordRule->symbols();
        }

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', $passwordRule],
        ]);

        $user = User::where('email', $request->email)->first();

        // Enforce Password Anti-Reuse Policy
        if ($user && $user->isPasswordReused($request->password, $settings->password_history_limit ?? 3)) {
            throw ValidationException::withMessages([
                'password' => ["Security policy violation: You cannot reuse your current password or any of your last {$settings->password_history_limit} passwords."],
            ]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                // Save current password hash to history table BEFORE updating
                $user->recordPasswordHistory();

                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}