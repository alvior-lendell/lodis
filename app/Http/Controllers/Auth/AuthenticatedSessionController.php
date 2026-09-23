<?php

namespace App\Http\Controllers\Auth;

use App\Events\DeviceAuthorizationRequested;
use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\DeviceAuthorization;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\UserDevice;
use App\Notifications\DeviceSignInAttemptNotification;
use App\Rules\Turnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('welcome');
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];

        if (config('services.turnstile.key')) {
            $rules['cf-turnstile-response'] = ['required', new Turnstile()];
        }

        $request->validate($rules, [
            'cf-turnstile-response.required' => 'Please complete the Cloudflare security challenge.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('login')) . '|' . $request->ip());

        // 1. IP & Rate Limiter Throttle Check
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // 2. Locate user record
        $user = User::where('email', $request->login)
            ->orWhere('employee_id', $request->login)
            ->first();

        // 3. Database-Level Account Lockout Check
        if ($user && $user->isLockedOut()) {
            $seconds = now()->diffInSeconds($user->locked_until);
            $minutes = ceil($seconds / 60);

            throw ValidationException::withMessages([
                'login' => "Your account is temporarily locked due to repeated failed login attempts. Please try again in {$minutes} minute(s).",
            ]);
        }

        // 4. Validate Password & Record Failures
        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 300);
        
            if ($user) {
                $user->recordFailedLoginAttempt();
        
                // Immediately throw lockout exception if the 5th attempt triggered the lock
                if ($user->isLockedOut()) {
                    $seconds = now()->diffInSeconds($user->locked_until);
                    $minutes = ceil($seconds / 60);
        
                    throw ValidationException::withMessages([
                        'login' => "Your account has been temporarily locked due to repeated failed login attempts. Please try again in {$minutes} minute(s).",
                    ]);
                }
        
                // Check if input matches any previously recorded password in PasswordHistory
                $usedOldPassword = $user->passwordHistories()
                    ->get()
                    ->contains(fn ($history) => Hash::check($request->password, $history->password));
        
                if ($usedOldPassword) {
                    throw ValidationException::withMessages([
                        'login' => 'You entered an old password. Please use your current password or request a password reset.',
                    ]);
                }
            }
        
            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        // 5. Successful Password Authentication -> Reset Counters
        RateLimiter::clear($throttleKey);
        $user->resetLockout();

        // 6. Email verification check
        if (is_null($user->email_verified_at)) {
            session(['pending_otp_email' => $user->email]);

            $activeOtp = OtpVerification::where('email', $user->email)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (! $activeOtp) {
                OtpVerification::where('email', $user->email)
                    ->where('is_used', false)
                    ->update(['is_used' => true]);

                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                OtpVerification::create([
                    'email' => $user->email,
                    'employee_id' => $user->employee_id,
                    'otp' => Hash::make($otp),
                    'payload' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'employee_id' => $user->employee_id,
                        'password' => $user->password,
                    ],
                    'resend_attempts' => 0,
                    'is_used' => false,
                    'expires_at' => now()->addMinutes(10),
                ]);

                Mail::to($user->email)->send(new SendOtpMail($otp, $user->name));
            }

            return redirect()->route('register.otp')->with(
                'status',
                'Your email is not verified yet. We have directed you to complete OTP verification.'
            );
        }

        // 7. Active account check
        if (! $user->is_active) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'Your account is currently inactive. Please contact your system administrator.',
                ]);
        }

        // 8. Recognized Trusted Device -> Grant immediate session access
        $deviceKey = $request->cookie('lodis_device_key');
        $isTrusted = $deviceKey && UserDevice::where('user_id', $user->id)->where('device_key', $deviceKey)->exists();

        if ($isTrusted) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            UserDevice::where('user_id', $user->id)
                ->where('device_key', $deviceKey)
                ->update([
                    'last_active_at' => now(),
                    'ip_address' => $request->ip(),
                ]);

            return redirect()->intended(route('dashboard'));
        }

        // 9. Unrecognized Device -> Stage session & route to device-wait
        session([
            'pending_auth_user_id' => $user->id,
            'pending_auth_remember' => $request->boolean('remember'),
        ]);

        $agent = $request->userAgent() ?? '';
        $platform = preg_match('/Windows/i', $agent) ? 'Windows PC' : (preg_match('/Mac/i', $agent) ? 'macOS' : 'Mobile Device');
        $browser = preg_match('/Chrome/i', $agent) ? 'Chrome' : (preg_match('/Firefox/i', $agent) ? 'Firefox' : 'Browser');
        $deviceName = "{$platform} ({$browser})";
        
        $loc = \App\Services\DeviceVerificationService::resolveLocationDetails($request->ip(), $request);

        $authRequest = DeviceAuthorization::create([
            'id'          => (string) Str::uuid(),
            'user_id'     => $user->id,
            'device_key'  => Str::random(40),
            'device_name' => $deviceName,
            'ip_address'  => $request->ip(),
            'location'   => $loc['location'],
            'latitude'   => $loc['latitude'],
            'longitude'  => $loc['longitude'],
            'user_agent' => $agent,
            'status'     => 'pending',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Populate database notifications table for review later
        $user->notify(new DeviceSignInAttemptNotification($authRequest));

        $existingDevicesCount = UserDevice::where('user_id', $user->id)->count();

        if ($existingDevicesCount > 0) {
            DeviceAuthorizationRequested::dispatch($authRequest);
        }

        return redirect()->route('device.wait', $authRequest->id);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out successfully.');
    }
}