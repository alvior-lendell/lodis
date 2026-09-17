<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAuthorization extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'device_key',
        'device_name',
        'ip_address',
        'user_agent',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function fallbackOtp(Request $request, string $id): RedirectResponse
    {
        $auth = DeviceAuthorization::findOrFail($id);
        $user = User::findOrFail($auth->user_id);
    
        // Cancel pending real-time authorization request
        $auth->update(['status' => 'rejected']);
    
        // Generate & dispatch OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
        OtpVerification::updateOrCreate(
            ['email' => $user->email],
            [
                'employee_id' => $user->employee_id,
                'otp' => Hash::make($otp),
                'is_used' => false,
                'expires_at' => now()->addMinutes(10),
            ]
        );
    
        Mail::to($user->email)->send(
            new SendOtpMail(
                otp: $otp,
                name: $user->name,
                title: 'Workstation Authorization OTP',
                description: 'You requested to authorize this new workstation via Email OTP. Enter the code below to complete sign-in:'
            )
        );
    
        session([
            'pending_device_otp_user_id' => $user->id,
            'pending_otp_email' => $user->email,
        ]);
    
        return redirect()->route('device.otp')->with(
            'status',
            'We sent a 6-digit OTP code to your email to verify your identity.'
        );
    }
    
    public function showOtp(): View|RedirectResponse
    {
        if (! session('pending_device_otp_user_id') || ! session('pending_otp_email')) {
            return redirect()->route('login');
        }
    
        return view('auth.device-otp', [
            'email' => session('pending_otp_email'),
        ]);
    }
    
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'string', 'size:6']]);
    
        $userId = session('pending_device_otp_user_id');
        $email = session('pending_otp_email');
    
        if (! $userId || ! $email) {
            return redirect()->route('login');
        }
    
        $user = User::findOrFail($userId);
    
        $record = OtpVerification::where('email', $email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    
        if (! $record || ! Hash::check($request->otp, $record->otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP code.']);
        }
    
        $record->update(['is_used' => true]);
    
        // Create device fingerprint
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
        $request->session()->forget(['pending_device_otp_user_id', 'pending_otp_email', 'pending_auth_user_id', 'pending_auth_remember']);
        $request->session()->regenerate();
    
        // Queue 1-year persistent device cookie
        Cookie::queue('lodis_device_key', $deviceKey, 525600);
    
        return redirect()->route('dashboard')->with('status', 'Workstation authorized successfully.');
    }
}

