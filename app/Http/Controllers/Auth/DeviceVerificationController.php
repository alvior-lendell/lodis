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
use App\Services\DeviceVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
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

        $routes = [
            'finalize'          => Route::has('device.finalize') ? route('device.finalize', $auth->id) : route('device.wait', $auth->id) . '/finalize',
            'startVerification' => Route::has('device.start-verification') ? route('device.start-verification', $auth->id) : route('device.wait', $auth->id) . '/start',
            'login'             => Route::has('login') ? route('login') : '/login',
        ];

        return view('auth.device-wait', compact(
            'auth',
            'user',
            'phoneNumber',
            'hasTrustedDevices',
            'hasSms',
            'hasAuthenticator',
            'routes'
        ));
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
        $expiresAt = null;

        // 1. Resolve expiration time from DeviceAuthorization record if present
        if ($authId) {
            $auth = DeviceAuthorization::find($authId);
            $expiresAt = $auth?->expires_at;
        }

        if ($method === 'email') {
            $otpRecord = OtpVerification::where('email', $email)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($otpRecord) {
                $secondsPassed = max(0, now()->timestamp - $otpRecord->updated_at->timestamp);
                $cooldownSeconds = (int) ceil(max(0, 60 - $secondsPassed));

                // Fallback to active OTP expiration if no device authorization record exists
                if (! $expiresAt) {
                    $expiresAt = $otpRecord->expires_at;
                }
            }
        }

        $hasSms = ! empty($phoneNumber);
        $hasAuthenticator = ! empty($user->two_factor_secret);

        $routes = [
            'switchMethod' => Route::has('device.otp.switch') ? route('device.otp.switch') : '/auth/device-otp/switch',
            'verify'       => Route::has('device.otp.verify') ? route('device.otp.verify') : '/auth/device-otp/verify',
            'resend'       => Route::has('device.otp.resend') ? route('device.otp.resend') : '/auth/device-otp/resend',
            'wait'         => $authId && Route::has('device.wait') ? route('device.wait', $authId) : ($authId ? "/auth/device-wait/{$authId}" : null),
        ];

        return view('auth.device-otp', compact(
            'email',
            'phoneNumber',
            'method',
            'authId',
            'cooldownSeconds',
            'expiresAt', // <-- Added variable to compact array
            'hasSms',
            'hasAuthenticator',
            'routes'
        ));
    }

    /**
     * Resend Email OTP with strict 60-second cooldown enforcement.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $email = session('pending_otp_email');
        $userId = session('pending_device_otp_user_id');

        if (! $userId || ! $email) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        // Server-side Cooldown Check
        $otpRecord = OtpVerification::where('email', $email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($otpRecord) {
            $secondsPassed = max(0, now()->timestamp - $otpRecord->updated_at->timestamp);
            if ($secondsPassed < 60) {
                $remaining = 60 - $secondsPassed;
                return back()->withErrors(['otp' => "Please wait {$remaining} seconds before requesting a new OTP code."]);
            }
        }

        $this->dispatchEmailOtp($user, isResend: true);

        return back()->with('status', 'A new OTP code has been sent to your email.');
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
        } elseif ($method === 'sms') {
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
        } else {
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

        $auth = $authId ? DeviceAuthorization::find($authId) : null;
        if ($auth) {
            $auth->update(['status' => 'approved']);
        }

        // Reuse the key attached to the authorization request or existing cookie
        $deviceKey = $auth?->device_key 
            ?? $request->cookie('lodis_device_key') 
            ?? Str::random(40);

        $agent = $request->userAgent() ?? '';
        $platform = preg_match('/Windows/i', $agent) ? 'Windows PC' : (preg_match('/Mac/i', $agent) ? 'macOS' : 'Mobile Device');
        $browser = preg_match('/Chrome/i', $agent) ? 'Chrome' : (preg_match('/Firefox/i', $agent) ? 'Firefox' : 'Browser');
        $location = DeviceVerificationService::resolveLocation($request->ip(), $request);

        UserDevice::updateOrCreate(
            ['user_id' => $user->id, 'device_key' => $deviceKey],
            [
                'device_name' => "{$platform} ({$browser})",
                'platform' => $platform,
                'browser' => $browser,
                'ip_address' => $request->ip(),
                'location' => $location,
                'user_agent' => $agent,
                'last_active_at' => now(),
            ]
        );

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

        // Expire old instances to avoid duplicate cookie headers
        Cookie::expire('lodis_device_key');
        
        // Explicitly scope path to '/' and set duration to 1 year
        Cookie::queue('lodis_device_key', $deviceKey, 525600, '/');

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
        
        $location = $auth->location ?? DeviceVerificationService::resolveLocation($auth->ip_address, $request);

        UserDevice::create([
            'user_id'        => $user->id,
            'device_key'     => $auth->device_key,
            'device_name'    => $auth->device_name,
            'platform'       => $platform,
            'browser'        => $browser,
            'ip_address'     => $auth->ip_address,
            'location'       => $location,
            'latitude'       => $auth->latitude,
            'longitude'      => $auth->longitude,
            'user_agent'     => $auth->user_agent,
            'last_active_at' => now(),
        ]);

        Auth::login($user, session('pending_auth_remember', false));
        $request->session()->forget(['pending_auth_user_id', 'pending_auth_remember']);
        $request->session()->regenerate();

        Cookie::queue('lodis_device_key', $auth->device_key, 525600);

        return redirect()->route('dashboard');
    }

    /**
     * Review a device authorization request.
     */
    public function review(string $id): View
    {
        $auth = DeviceAuthorization::findOrFail($id);

        if ($auth->user_id !== Auth::id()) {
            abort(403);
        }

        $authFormatted = [
            'id'                   => $auth->id,
            'device_name'          => $auth->device_name ?? 'Unknown Device',
            'ip_address'           => $auth->ip_address,
            'status'               => $auth->status,
            'is_expired'           => $auth->status === 'expired' || ($auth->created_at && $auth->created_at->addMinutes(15)->isPast()),
            'created_at_formatted' => $auth->created_at?->format('F j, Y g:i A'),
            'created_at_human'     => $auth->created_at?->diffForHumans(),
            'location'             => $auth->location ?? 'Unknown Location',
            'latitude'             => $auth->latitude,
            'longitude'            => $auth->longitude,
        ];

        $routes = [
            'approve'   => Route::has('device.approve') ? route('device.approve', $auth->id) : "/auth/device-approve/{$auth->id}",
            'reject'    => Route::has('device.reject') ? route('device.reject', $auth->id) : "/auth/device-reject/{$auth->id}",
            'dashboard' => Route::has('dashboard') ? route('dashboard') : '/dashboard',
        ];

        return view('auth.device-approve', compact('auth', 'authFormatted', 'routes'));
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

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        OtpVerification::create([
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'otp' => $otpCode,
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