<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\Employee;
use App\Models\OtpVerification;
use App\Models\SecuritySetting;
use App\Models\User;
use App\Models\UserDevice;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class RegisteredUserController extends Controller
{
    /**
     * Get active security settings directly from the database.
     */
    private function getSecuritySettings(): SecuritySetting
    {
        return SecuritySetting::instance();
    }

    /**
     * Display the registration view with active security settings.
     */
    public function create(): View
    {
        $securitySettings = $this->getSecuritySettings();

        return view('auth.register', compact('securitySettings'));
    }

    /**
     * Verify email against employee records without returning personal identification data.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $employee = Employee::where('email', $request->email)->first();

        if (! $employee) {
            return response()->json([
                'found' => false,
                'has_account' => false,
                'message' => 'Email address is not recognized in employee records.',
            ], 404);
        }

        $userExists = User::where('email', $request->email)
            ->orWhere('employee_id', $employee->employee_id)
            ->exists();

        if ($userExists) {
            return response()->json([
                'found' => true,
                'has_account' => true,
                'message' => 'An account already exists for this email. Please sign in.',
            ]);
        }

        return response()->json([
            'found' => true,
            'has_account' => false,
            'has_phone' => ! empty($employee->contact_number),
            'message' => 'Valid employee email. You may set your account password.',
        ]);
    }

    /**
     * Handle initial registration and store pending session payload.
     */
    public function store(Request $request): RedirectResponse
    {
        $settings = $this->getSecuritySettings();

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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', $passwordRule],
        ]);

        $employee = Employee::where('email', $request->email)->first();

        if (! $employee) {
            return back()->withInput()->withErrors([
                'email' => 'Registration failed. Email not found in active employee records.',
            ]);
        }

        $existingUser = User::where('email', $request->email)
            ->orWhere('employee_id', $employee->employee_id)
            ->first();

        if ($existingUser) {
            return back()->withInput()->withErrors([
                'email' => 'An account is already registered for this employee record.',
            ]);
        }

        $middle = $employee->middle_name ? ' '.$employee->middle_name : '';
        $suffix = $employee->suffix ? ' '.$employee->suffix : '';
        $fullName = trim("{$employee->first_name}{$middle} {$employee->last_name}{$suffix}");

        session([
            'pending_registration' => [
                'name' => $fullName,
                'email' => $request->email,
                'employee_id' => $employee->employee_id,
                'phone_number' => $employee->contact_number ?? null,
                'password' => Hash::make($request->password),
            ],
            'pending_otp_email' => $request->email,
            'active_otp_method' => 'email',
        ]);

        return redirect()->route('register.otp.select');
    }

    /**
     * Display verification method selection screen.
     */
    public function showSelectMethodForm(): View|RedirectResponse
    {
        $registration = session('pending_registration');

        if (! $registration) {
            return redirect()->route('register');
        }

        return view('auth.select-otp-method', [
            'email' => $registration['email'],
            'phoneNumber' => $registration['phone_number'] ?? null,
            'selectedMethod' => session('active_otp_method', 'email'),
        ]);
    }

    /**
     * Switch verification method channel dynamically (Email, SMS Number Check).
     */
    public function switchMethod(Request $request): RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:email,sms,authenticator'],
        ]);

        $registration = session('pending_registration');

        if (! $registration) {
            return redirect()->route('register');
        }

        $newMethod = $request->input('method');

        if ($newMethod === 'sms' && empty($registration['phone_number'])) {
            return back()->withErrors(['method' => 'No contact number is linked to your employee record for SMS verification.']);
        }

        session(['active_otp_method' => $newMethod]);

        if ($newMethod === 'email') {
            $this->dispatchOtpChallenge($registration['email'], $registration['name'], $registration['employee_id']);
        }

        return redirect()->route('register.otp');
    }

    /**
     * Display OTP or Phone Number verification screen.
     */
    public function showOtpForm(): View|RedirectResponse
    {
        $email = session('pending_otp_email');
        $registration = session('pending_registration');

        if (! $email || ! $registration) {
            return redirect()->route('register');
        }

        $method = session('active_otp_method', 'email');
        $cooldownSeconds = 0;
        $qrCodeUrl = null;
        $secretKey = null;

        if ($method === 'authenticator') {
            $google2fa = new Google2FA();
            if (! session()->has('pending_totp_secret')) {
                session(['pending_totp_secret' => $google2fa->generateSecretKey()]);
            }
            $secretKey = session('pending_totp_secret');

            $otpauthUrl = $google2fa->getQRCodeUrl('LODISv2', $email, $secretKey);

            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCodeUrl = 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($otpauthUrl));
        } else {
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

        return view('auth.verify-otp', [
            'email' => $email,
            'phoneNumber' => $registration['phone_number'] ?? null,
            'method' => $method,
            'cooldownSeconds' => $cooldownSeconds,
            'qrCodeUrl' => $qrCodeUrl,
            'secretKey' => $secretKey,
        ]);
    }

    /**
     * Verify input and create user, then automatically register trusted device.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $email = session('pending_otp_email');
        $payload = session('pending_registration');
        $method = session('active_otp_method', 'email');

        if (! $email || ! $payload) {
            return redirect()->route('register')->withErrors(['email' => 'Session expired. Please restart registration.']);
        }

        // Authenticator App TOTP Validation
        if ($method === 'authenticator') {
            $request->validate(['otp' => ['required', 'string', 'size:6']]);

            $secret = session('pending_totp_secret');
            $google2fa = new Google2FA();

            if (! $secret || ! $google2fa->verifyKey($secret, $request->otp)) {
                return back()->withErrors(['otp' => 'Invalid Authenticator app code. Please try again.']);
            }
        }
        // SMS Registered Phone Number Matching
        elseif ($method === 'sms') {
            $request->validate(['otp' => ['required', 'string']]);

            $expectedPhone = $payload['phone_number'] ?? null;
            $inputPhone = $this->normalizePhoneNumber($request->otp);
            $targetPhone = $this->normalizePhoneNumber($expectedPhone);

            if (empty($targetPhone) || empty($inputPhone) || $inputPhone !== $targetPhone) {
                return back()->withErrors(['otp' => 'The mobile number entered does not match our employee records.']);
            }
        }
        // Email OTP Code Validation
        else {
            $request->validate(['otp' => ['required', 'string', 'size:6']]);

            $otpRecord = OtpVerification::where('email', $email)
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if (! $otpRecord || ! Hash::check($request->otp, $otpRecord->otp)) {
                return back()->withErrors(['otp' => 'Invalid or expired OTP code. Please try again.']);
            }

            $otpRecord->update(['is_used' => true]);
        }

        // Provision User Account
        $user = User::where('email', $payload['email'])
            ->orWhere('employee_id', $payload['employee_id'])
            ->first();

        $totpSecret = ($method === 'authenticator') ? encrypt(session('pending_totp_secret')) : null;

        if (! $user) {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'employee_id' => $payload['employee_id'],
                'phone_number' => $payload['phone_number'] ?? null,
                'password' => $payload['password'],
                'role' => 'Employee',
                'is_active' => true,
                'preferred_2fa_method' => $method,
                'two_factor_secret' => $totpSecret,
                'email_verified_at' => now(),
            ]);
        } else {
            if (is_null($user->email_verified_at)) {
                $user->forceFill([
                    'email_verified_at' => now(),
                    'preferred_2fa_method' => $method,
                    'two_factor_secret' => $totpSecret ?? $user->two_factor_secret,
                ])->save();
            }
        }

        session()->forget(['pending_otp_email', 'pending_registration', 'active_otp_method', 'pending_totp_secret']);

        Auth::login($user);

        // Register initial device automatically upon completing registration
        $deviceKey = (string) Str::uuid();
        $agent = $request->userAgent() ?? '';

        $platform = 'Unknown OS';
        if (preg_match('/Windows/i', $agent)) {
            $platform = 'Windows PC';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $agent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android/i', $agent)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $agent)) {
            $platform = 'iOS';
        }

        $browser = 'Unknown Browser';
        if (preg_match('/Edg/i', $agent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/Chrome/i', $agent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Firefox/i', $agent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/Safari/i', $agent)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/Opera|OPR/i', $agent)) {
            $browser = 'Opera';
        }

        UserDevice::create([
            'user_id' => $user->id,
            'device_key' => $deviceKey,
            'device_name' => "{$platform} — {$browser}",
            'platform' => $platform,
            'browser' => $browser,
            'ip_address' => $request->ip(),
            'user_agent' => $agent,
            'is_trusted' => true,
            'last_active_at' => now(),
        ]);

        // Set persistent 1-year device cookie (525600 minutes)
        Cookie::queue('lodis_device_key', $deviceKey, 525600);

        return redirect()->route('dashboard');
    }

    /**
     * Resend verification OTP code (Email only).
     */
    public function resendOtp(): RedirectResponse
    {
        $email = session('pending_otp_email');
        $registration = session('pending_registration');
        $method = session('active_otp_method', 'email');

        if (! $email || ! $registration) {
            return redirect()->route('register');
        }

        if ($method !== 'email') {
            return back()->withErrors(['otp' => 'Resend is only applicable for Email OTP verification.']);
        }

        $otpRecord = OtpVerification::where('email', $email)
            ->where('is_used', false)
            ->latest()
            ->first();

        if ($otpRecord && $otpRecord->resend_attempts >= 3) {
            $otpRecord->update(['is_used' => true]);
            session()->forget(['pending_otp_email', 'pending_registration']);

            return redirect()->route('register')->withErrors([
                'email' => 'Maximum OTP resend limit reached. Please restart registration.',
            ]);
        }

        $this->dispatchOtpChallenge($email, $registration['name'], $registration['employee_id'], true);

        return back()->with('status', 'A new 6-digit code has been sent via EMAIL.');
    }

    /**
     * Helper to dispatch Email OTP codes.
     */
    private function dispatchOtpChallenge(string $email, string $fullName, string $employeeId, bool $isResend = false): void
    {
        $activeOtp = OtpVerification::where('email', $email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $isResend && $activeOtp) {
            return;
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $attempts = $activeOtp ? $activeOtp->resend_attempts + ($isResend ? 1 : 0) : 0;

        OtpVerification::where('email', $email)->where('is_used', false)->update(['is_used' => true]);

        OtpVerification::create([
            'email' => $email,
            'employee_id' => $employeeId,
            'otp' => Hash::make($otp),
            'payload' => ['name' => $fullName, 'email' => $email, 'employee_id' => $employeeId],
            'resend_attempts' => $attempts,
            'is_used' => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($email)->send(new SendOtpMail($otp, $fullName));
    }

    /**
     * Normalize phone numbers for flexible digit matching.
     */
    private function normalizePhoneNumber(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');

        if (str_starts_with($digits, '63')) {
            $digits = '0'.substr($digits, 2);
        }

        return $digits;
    }
}