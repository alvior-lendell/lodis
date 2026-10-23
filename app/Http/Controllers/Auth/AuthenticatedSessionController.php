<?php

namespace App\Http\Controllers\Auth;

use App\Events\DeviceAuthorizationRequested;
use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\DeviceAuthorization;
use App\Models\OtpVerification;
use App\Models\System;
use App\Models\User;
use App\Models\UserDevice;
use App\Notifications\DeviceSignInAttemptNotification;
use App\Rules\Turnstile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        // 1. Capture and validate redirect_to domain to prevent open redirect vulnerabilities
        $redirectTo = $request->query('redirect_to');
        if ($redirectTo) {
            $host = parse_url($redirectTo, PHP_URL_HOST);
            if ($host && str_ends_with($host, 'lendell-local.xyz')) {
                session(['sso_redirect_to' => $redirectTo]);
            }
        }

        // 2. Resolve target subsystem brand configuration from database
        $system = System::resolveForSso($redirectTo, $request->query('system'));

        // 3. Auto-redirect if user is already authenticated on LODIS
        if (Auth::check()) {
            $destination = session()->pull('sso_redirect_to', route('dashboard'));
            return redirect()->away($destination);
        }

        // 4. Derive subsystem branding variables for welcome.blade.php
        $isSubsystem = $system && $system->slug !== 'lodis';
        $systemName = $system?->name ?? 'LODISv2';
        $systemLogo = $system?->logo_url ?? asset('images/LODISv2.png');
        $brandColor = $system?->brand_color ?? '#00687A';
        $brandHoverColor = $system?->brand_hover_color ?? '#004F5D';
        
        if (!empty($system->bg_gradient)) {
            $bgGradientStyle = str_contains($system->bg_gradient, 'gradient') 
                ? $system->bg_gradient 
                : "linear-gradient(135deg, {$system->bg_gradient})";
        } else {
            $bgGradientStyle = $isSubsystem 
                ? "linear-gradient(135deg, #020617 0%, #0f172a 50%, #022c22 100%)" 
                : "linear-gradient(135deg, #020617 0%, #0f172a 50%, #003843 100%)";
        }

        // 5. Fetch all active systems for the slideshow panel
        $systems = System::where('is_active', true)
            ->get()
            ->map(fn (System $sys) => $sys->toSlideshowArray());

        // 6. Resolve Facebook-style saved accounts linked to this workstation cookie
        $savedAccounts = [];
        $deviceKey = $request->cookie('lodis_device_key');
        if ($deviceKey) {
            $devices = UserDevice::where('device_key', $deviceKey)
                ->where('is_trusted', true)
                ->with(['user.employee'])
                ->get();

            foreach ($devices as $dev) {
                $payload = $this->resolveSavedAccountPayload($dev);
                if ($payload) {
                    $savedAccounts[] = $payload;
                }
            }
        }

        return view('welcome', compact(
            'system',
            'systems',
            'savedAccounts',
            'isSubsystem',
            'systemName',
            'systemLogo',
            'brandColor',
            'brandHoverColor',
            'bgGradientStyle'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];

        $deviceKey = $request->cookie('lodis_device_key');
        $isTrusted = $deviceKey && UserDevice::where('device_key', $deviceKey)->where('is_trusted', true)->exists();

        // Turnstile is ONLY required for unrecognized devices / public login attempts
        if (config('services.turnstile.key') && ! $isTrusted) {
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

        // 4. Safely Validate Password & Record Failures
        $passwordMatches = false;

        if ($user && ! empty($user->password)) {
            try {
                $passwordMatches = Hash::check($request->password, $user->password);
            } catch (\Throwable $e) {
                $passwordMatches = false;
            }
        }

        if (! $user || ! $passwordMatches) {
            RateLimiter::hit($throttleKey, 300);

            if ($user) {
                $user->recordFailedLoginAttempt();

                if ($user->isLockedOut()) {
                    $seconds = now()->diffInSeconds($user->locked_until);
                    $minutes = ceil($seconds / 60);

                    throw ValidationException::withMessages([
                        'login' => "Your account has been temporarily locked due to repeated failed login attempts. Please try again in {$minutes} minute(s).",
                    ]);
                }

                $usedOldPassword = false;
                try {
                    $usedOldPassword = $user->passwordHistories()
                        ->get()
                        ->contains(function ($history) use ($request) {
                            $hash = $history->password ?? $history->password_hash ?? null;
                            if (! $hash) {
                                return false;
                            }
                            try {
                                return Hash::check($request->password, $hash);
                            } catch (\Throwable $e) {
                                return false;
                            }
                        });
                } catch (\Throwable $e) {
                    $usedOldPassword = false;
                }

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
                        // Bug #6 fix: never store the password hash in OTP payload
                        'name'        => $user->name,
                        'email'       => $user->email,
                        'employee_id' => $user->employee_id,
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

        // 8. Recognized Trusted Device -> Grant immediate session access & redirect to target subsystem
        $deviceKey = $request->cookie('lodis_device_key');
        $isTrusted = $deviceKey && UserDevice::where('user_id', $user->id)->where('device_key', $deviceKey)->exists();

        if ($isTrusted) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            $updateData = [
                'last_active_at' => now(),
                'ip_address'     => $request->ip(),
            ];

            if ($request->has('enable_one_click')) {
                $updateData['one_click_enabled'] = $request->boolean('enable_one_click');
            }

            UserDevice::where('user_id', $user->id)
                ->where('device_key', $deviceKey)
                ->update($updateData);

            // Refresh cookie expiration to maintain 1-year window
            Cookie::queue('lodis_device_key', $deviceKey, 525600, '/');

            $destination = session()->pull('sso_redirect_to', route('dashboard'));
            session()->forget('sso_redirect_to');

            return redirect()->away($destination);
        }

        // 9. Unrecognized Device -> Stage session & route to device-wait (reuse existing browser cookie key if present)
        session([
            'pending_auth_user_id' => $user->id,
            'pending_auth_remember' => $request->boolean('remember'),
        ]);

        $agent = $request->userAgent() ?? '';
        $platform = preg_match('/Windows/i', $agent) ? 'Windows PC' : (preg_match('/Mac/i', $agent) ? 'macOS' : 'Mobile Device');
        $browser = preg_match('/Chrome/i', $agent) ? 'Chrome' : (preg_match('/Firefox/i', $agent) ? 'Firefox' : 'Browser');
        $deviceName = "{$platform} ({$browser})";

        $loc = \App\Services\DeviceVerificationService::resolveLocationDetails($request->ip(), $request);

        // Reuse existing browser cookie key if present; otherwise generate a new one
        $authDeviceKey = $deviceKey ?? Str::random(40);

        $authRequest = DeviceAuthorization::create([
            'id'          => (string) Str::uuid(),
            'user_id'     => $user->id,
            'device_key'  => $authDeviceKey,
            'device_name' => $deviceName,
            'ip_address'  => $request->ip(),
            'location'    => $loc['location'],
            'latitude'    => $loc['latitude'],
            'longitude'   => $loc['longitude'],
            'user_agent'  => $agent,
            'status'      => 'pending',
            'expires_at'  => now()->addMinutes(5),
        ]);

        $user->notify(new DeviceSignInAttemptNotification($authRequest));

        $existingDevicesCount = UserDevice::where('user_id', $user->id)->count();

        if ($existingDevicesCount > 0) {
            DeviceAuthorizationRequested::dispatch($authRequest);
        }

        return redirect()->route('device.wait', $authRequest->id);
    }

    /**
     * Resolve saved account identities linked to the current workstation cookie.
     */
    public function deviceIdentity(Request $request): JsonResponse
    {
        $deviceKey = $request->cookie('lodis_device_key');

        if (! $deviceKey) {
            return response()->json(['saved_accounts' => []]);
        }

        $devices = UserDevice::where('device_key', $deviceKey)
            ->where('is_trusted', true)
            ->with(['user.employee'])
            ->get();

        $savedAccounts = [];
        foreach ($devices as $dev) {
            $payload = $this->resolveSavedAccountPayload($dev);
            if ($payload) {
                $savedAccounts[] = $payload;
            }
        }

        return response()->json(['saved_accounts' => $savedAccounts]);
    }

    /**
     * Build display payload for saved workstation account with profile photo and redacted name/email.
     */
    private function resolveSavedAccountPayload(UserDevice $device): ?array
    {
        $user = $device->user;
        if (! $user || ! $user->is_active) {
            return null;
        }

        $employee = $user->employee;

        // Use Employee profile photo first, fallback to user photo if exists
        $photoPath = $employee?->profile_photo_path ?? ($user->profile_photo_path ?? null);
        $avatarUrl = $photoPath ? asset('storage/' . ltrim($photoPath, '/')) : null;

        $fullName = trim($employee?->full_name ?? $user->name);
        $firstName = trim($employee?->first_name ?? explode(' ', $fullName)[0]);

        // Redact Email or Employee ID: e.g. "aljhon@lendell.com" -> "a•••••n@l••••.com"
        $rawLogin = $user->email ?? $user->employee_id ?? '';
        $redactedLogin = $rawLogin;

        if (filter_var($rawLogin, FILTER_VALIDATE_EMAIL)) {
            [$local, $domain] = explode('@', $rawLogin, 2);
            $localLen = strlen($local);
            $maskedLocal = $localLen > 2
                ? substr($local, 0, 1) . str_repeat('•', max(3, min(5, $localLen - 2))) . substr($local, -1)
                : substr($local, 0, 1) . '•••';

            $dotPos = strrpos($domain, '.');
            if ($dotPos !== false) {
                $domainName = substr($domain, 0, $dotPos);
                $tld = substr($domain, $dotPos);
                $domainLen = strlen($domainName);
                $maskedDomain = $domainLen > 2
                    ? substr($domainName, 0, 1) . str_repeat('•', max(2, min(4, $domainLen - 1))) . $tld
                    : substr($domainName, 0, 1) . '••' . $tld;
            } else {
                $maskedDomain = substr($domain, 0, 1) . '•••';
            }

            $redactedLogin = "{$maskedLocal}@{$maskedDomain}";
        } elseif (strlen($rawLogin) > 3) {
            $redactedLogin = substr($rawLogin, 0, 1) . str_repeat('•', strlen($rawLogin) - 2) . substr($rawLogin, -1);
        }

        return [
            'id'                => $device->id,
            'user_id'           => $user->id,
            'name'              => $firstName,
            'first_name'        => $firstName,
            'login'             => $rawLogin,
            'redacted_login'    => $redactedLogin,
            'avatar_url'        => $avatarUrl,
            'initials'          => mb_strtoupper(mb_substr($firstName, 0, 1)),
            'one_click_enabled' => (bool) ($device->one_click_enabled ?? false),
        ];
    }

    /**
     * True 1-Click Login using verified trusted workstation cookie and one_click_enabled flag.
     */
    public function oneClickLogin(Request $request): RedirectResponse|JsonResponse
    {
        $deviceKey = $request->cookie('lodis_device_key');

        if (! $deviceKey) {
            throw ValidationException::withMessages([
                'login' => 'Workstation authorization not found. Please log in with your credentials.',
            ]);
        }

        $query = UserDevice::where('device_key', $deviceKey)
            ->where('is_trusted', true)
            ->where('one_click_enabled', true);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        } elseif ($request->filled('login')) {
            $targetUser = User::where('email', $request->login)->orWhere('employee_id', $request->login)->first();
            if ($targetUser) {
                $query->where('user_id', $targetUser->id);
            }
        }

        $device = $query->first();

        if (! $device) {
            throw ValidationException::withMessages([
                'login' => '1-Click login is not enabled for this account on this device. Please enter your password.',
            ]);
        }

        $user = $device->user;

        if (! $user || ! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'Your account is inactive or not found. Please contact an administrator.',
            ]);
        }

        if ($user->isLockedOut()) {
            $seconds = now()->diffInSeconds($user->locked_until);
            $minutes = ceil($seconds / 60);

            throw ValidationException::withMessages([
                'login' => "Your account is temporarily locked due to repeated failed attempts. Please try again in {$minutes} minute(s).",
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $device->update([
            'last_active_at' => now(),
            'ip_address'     => $request->ip(),
        ]);

        Cookie::queue('lodis_device_key', $deviceKey, 525600, '/');

        $destination = session()->pull('sso_redirect_to', route('dashboard'));
        session()->forget('sso_redirect_to');

        if ($request->wantsJson()) {
            return response()->json(['redirect' => $destination]);
        }

        return redirect()->away($destination);
    }

    /**
     * Remove a saved account or entire workstation cookie from this browser.
     */
    public function forgetDevice(Request $request): RedirectResponse|JsonResponse
    {
        $deviceKey = $request->cookie('lodis_device_key');
        $userId = $request->input('user_id');

        if ($deviceKey) {
            if ($userId) {
                // Delete device link for only this user
                UserDevice::where('device_key', $deviceKey)->where('user_id', $userId)->delete();

                // If no more devices remain for this cookie, expire the cookie
                $remaining = UserDevice::where('device_key', $deviceKey)->count();
                if ($remaining === 0) {
                    Cookie::expire('lodis_device_key');
                }
            } else {
                UserDevice::where('device_key', $deviceKey)->update([
                    'one_click_enabled' => false,
                ]);
                Cookie::expire('lodis_device_key');
            }
        } else {
            Cookie::expire('lodis_device_key');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('login')->with('status', 'Account removed from this device.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out successfully.');
    }
}