<?php

namespace App\Http\Controllers\Auth;

use App\Events\DeviceAuthorizationStatusChanged;
use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\DeviceAuthorization;
use App\Models\Employee;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class DeviceVerificationController extends Controller
{
    public function showWait(string $id): View|RedirectResponse
    {
        $auth = DeviceAuthorization::findOrFail($id);
        $user = User::findOrFail($auth->user_id);

        $employee = Employee::where('email', $user->email)
            ->orWhere('employee_id', $user->employee_id)
            ->first();

        $phoneNumber = $user->phone_number ?? $employee?->contact_number;

        $hasTrustedDevices = UserDevice::where('user_id', $user->id)->count() > 0;
        $hasSms = ! empty($phoneNumber);
        $hasAuthenticator = ! empty($user->two_factor_secret);

        return view('auth.device-wait', compact('auth', 'user', 'phoneNumber', 'hasTrustedDevices', 'hasSms', 'hasAuthenticator'));
    }

    public function startVerification(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:email,sms,authenticator'],
        ]);

        $auth = DeviceAuthorization::findOrFail($id);
        $user = User::findOrFail($auth->user_id);
        $method = $request->input('method');

        $employee = Employee::where('email', $user->email)
            ->orWhere('employee_id', $user->employee_id)
            ->first();

        $phoneNumber = $user->phone_number ?? $employee?->contact_number;

        if ($method === 'sms' && empty($phoneNumber)) {
            return back()->withErrors(['method' => 'No mobile number is linked to your employee profile for SMS verification.']);
        }

        if ($method === 'authenticator' && empty($user->two_factor_secret)) {
            return back()->withErrors(['method' => 'Authenticator App (2FA) is not enabled on your account.']);
        }

        session([
            'pending_device_otp_user_id' => $user->id,
            'pending_otp_email' => $user->email,
            'pending_device_auth_id' => $auth->id,
            'active_device_otp_method' => $method,
            'pending_phone_number' => $phoneNumber,
        ]);

        if ($method === 'email') {
            $this->dispatchEmailOtp($user);
        }

        return redirect()->route('device.otp');
    }

    public function switchMethod(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:email,sms,authenticator'],
        ]);

        $userId = session('pending_device_otp_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $method = $request->input('method');

        $employee = Employee::where('email', $user->email)
            ->orWhere('employee_id', $user->employee_id)
            ->first();

        $phoneNumber = $user->phone_number ?? $employee?->contact_number;

        if ($method === 'sms' && empty($phoneNumber)) {
            return back()->withErrors(['method' => 'No mobile number is linked to your profile for SMS verification.']);
        }

        if ($method === 'authenticator' && empty($user->two_factor_secret)) {
            return back()->withErrors(['method' => 'Authenticator App (2FA) is not enabled on your account.']);
        }

        session([
            'active_device_otp_method' => $method,
            'pending_phone_number' => $phoneNumber,
        ]);

        if ($method === 'email') {
            $this->dispatchEmailOtp($user);
        }

        return redirect()->route('device.otp');
    }

    public function showOtp(): View|RedirectResponse
    {
        $userId = session('pending_device_otp_user_id');
        $email = session('pending_otp_email');
        $authId = session('pending_device_auth_id');

        if (! $userId || ! $email) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $method = session('active_device_otp_method', 'email');
        $phoneNumber = session('pending_phone_number');

        $cooldownSeconds = 0;

        if ($method === 'email') {
            $otpRecord = OtpVerification::where('email', $email)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($otpRecord) {
                $secondsPassed = max(0, now()->timestamp - $otpRecord->updated_at->timestamp);
                $cooldownSeconds = (int) ceil(max(0, 60 - $secondsPassed));
            }
        }

        $hasSms = ! empty($phoneNumber);
        $hasAuthenticator = ! empty($user->two_factor_secret);

        return view('auth.device-otp', [
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'method' => $method,
            'authId' => $authId,
            'cooldownSeconds' => $cooldownSeconds,
            'hasSms' => $hasSms,
            'hasAuthenticator' => $hasAuthenticator,
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $userId = session('pending_device_otp_user_id');
        $email = session('pending_otp_email');
        $method = session('active_device_otp_method', 'email');
        $authId = session('pending_device_auth_id');

        if (! $userId || ! $email) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        // 1. Authenticator App TOTP Validation
        if ($method === 'authenticator') {
            $request->validate(['otp' => ['required', 'string', 'size:6']]);

            if (empty($user->two_factor_secret)) {
                return back()->withErrors(['otp' => 'Authenticator 2FA is not enabled on this account.']);
            }

            $google2fa = new Google2FA();
            $secret = decrypt($user->two_factor_secret);

            if (! $google2fa->verifyKey($secret, $request->otp)) {
                return back()->withErrors(['otp' => 'Invalid Authenticator app code. Please try again.']);
            }
        }
        // 2. SMS Registered Phone Number Matching Validation
        elseif ($method === 'sms') {
            $request->validate(['otp' => ['required', 'string']]);

            $employee = Employee::where('email', $user->email)
                ->orWhere('employee_id', $user->employee_id)
                ->first();

            $expectedPhone = $user->phone_number ?? $employee?->contact_number;
            $inputPhone = $this->normalizePhoneNumber($request->otp);
            $targetPhone = $this->normalizePhoneNumber($expectedPhone);

            if (empty($targetPhone) || empty($inputPhone) || $inputPhone !== $targetPhone) {
                return back()->withErrors(['otp' => 'The mobile number entered does not match our employee records.']);
            }
        }
        // 3. Email OTP Code Validation
        else {
            $request->validate(['otp' => ['required', 'string', 'size:6']]);

            $record = OtpVerification::where('email', $email)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (! $record || ! Hash::check($request->otp, $record->otp)) {
                return back()->withErrors(['otp' => 'Invalid or expired OTP code. Please try again.']);
            }

            $record->update(['is_used' => true]);
        }

        // Complete Device Registration & Session Provisioning
        if ($authId) {
            DeviceAuthorization::where('id', $authId)->update(['status' => 'approved']);
        }

        $deviceKey = Str::random(40);
        $agent = $request->userAgent() ?? '';
        $platform = preg_match('/Windows/i', $agent) ? 'Windows PC' : (preg_match('/Mac/i', $agent) ? 'macOS' : 'Mobile Device');
        $browser = preg_match('/Chrome/i', $agent) ? 'Chrome' : (preg_match('/Firefox/i', $agent) ? 'Firefox' : 'Browser');

        UserDevice::create([
            'user_id' => $user->id,
            'device_key' => $deviceKey,
            'device_name' => "{$platform} ({$browser})",
            'platform' => $platform,
            'browser' => $browser,
            'ip_address' => $request->ip(),
            'user_agent' => $agent,
            'last_active_at' => now(),
        ]);

        Auth::login($user, session('pending_auth_remember', false));
        $request->session()->forget([
            'pending_device_otp_user_id',
            'pending_otp_email',
            'pending_device_auth_id',
            'active_device_otp_method',
            'pending_phone_number',
            'pending_auth_user_id',
            'pending_auth_remember',
        ]);
        $request->session()->regenerate();

        Cookie::queue('lodis_device_key', $deviceKey, 525600);

        return redirect()->route('dashboard')->with('status', 'Workstation authorized successfully.');
    }

    public function approve(Request $request, string $id): RedirectResponse
    {
        $auth = DeviceAuthorization::findOrFail($id);
    
        if ($auth->user_id !== Auth::id()) {
            abort(403);
        }
    
        $auth->update(['status' => 'approved']);
    
        DeviceAuthorizationStatusChanged::dispatch($auth->id, 'approved');
    
        return back()->with('status', 'New device approved successfully.');
    }

    public function reject(Request $request, string $id): RedirectResponse
    {
        $auth = DeviceAuthorization::findOrFail($id);
    
        if ($auth->user_id !== Auth::id()) {
            abort(403);
        }
    
        $auth->update(['status' => 'rejected']);
    
        DeviceAuthorizationStatusChanged::dispatch($auth->id, 'rejected');
    
        return back()->with('status', 'Device authorization request rejected.');
    }

    public function finalize(Request $request, string $id): RedirectResponse
    {
        $auth = DeviceAuthorization::where('id', $id)->where('status', 'approved')->firstOrFail();
        $user = User::findOrFail($auth->user_id);

        $agent = $auth->user_agent ?? '';
        $platform = preg_match('/Windows/i', $agent) ? 'Windows PC' : (preg_match('/Mac/i', $agent) ? 'macOS' : 'Mobile Device');
        $browser = preg_match('/Chrome/i', $agent) ? 'Chrome' : (preg_match('/Firefox/i', $agent) ? 'Firefox' : 'Browser');

        UserDevice::create([
            'user_id' => $user->id,
            'device_key' => $auth->device_key,
            'device_name' => $auth->device_name,
            'platform' => $platform,
            'browser' => $browser,
            'ip_address' => $auth->ip_address,
            'user_agent' => $auth->user_agent,
            'last_active_at' => now(),
        ]);

        Auth::login($user, session('pending_auth_remember', false));
        $request->session()->forget(['pending_auth_user_id', 'pending_auth_remember']);
        $request->session()->regenerate();

        Cookie::queue('lodis_device_key', $auth->device_key, 525600);

        return redirect()->route('dashboard');
    }

    public function resendOtp(): RedirectResponse
    {
        $userId = session('pending_device_otp_user_id');
        $email = session('pending_otp_email');
        $method = session('active_device_otp_method', 'email');

        if (! $userId || ! $email) {
            return redirect()->route('login');
        }

        if ($method !== 'email') {
            return back()->withErrors(['otp' => 'Resend is only available for Email OTP verification.']);
        }

        $user = User::findOrFail($userId);
        $this->dispatchEmailOtp($user, true);

        return back()->with('status', 'A new 6-digit OTP code has been sent to your email.');
    }

    private function dispatchEmailOtp(User $user, bool $isResend = false): void
    {
        $activeOtp = OtpVerification::where('email', $user->email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $isResend && $activeOtp) {
            return;
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $attempts = $activeOtp ? $activeOtp->resend_attempts + ($isResend ? 1 : 0) : 0;

        OtpVerification::where('email', $user->email)->where('is_used', false)->update(['is_used' => true]);

        OtpVerification::create([
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'otp' => Hash::make($otp),
            'payload' => ['name' => $user->name, 'email' => $user->email],
            'resend_attempts' => $attempts,
            'is_used' => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(
            new SendOtpMail(
                otp: $otp,
                name: $user->name,
                title: 'Workstation Authorization OTP',
                description: 'Enter the 6-digit OTP code below to verify and authorize this workstation:'
            )
        );
    }

    private function normalizePhoneNumber(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');

        if (str_starts_with($digits, '63')) {
            $digits = '0'.substr($digits, 2);
        }

        return $digits;
    }
}