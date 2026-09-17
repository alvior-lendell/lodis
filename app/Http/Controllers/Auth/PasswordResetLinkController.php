<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming email password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Dispatch SMS verification code for password reset.
     */
    public function sendSmsCode(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $user = User::where('phone_number', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'No account associated with this mobile phone number.']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::updateOrCreate(
            ['email' => $user->email],
            [
                'employee_id' => $user->employee_id,
                'otp' => Hash::make($code),
                'is_used' => false,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        session([
            'pending_reset_user_id' => $user->id,
            'masked_phone' => substr($user->phone_number, -4),
        ]);

        return redirect()->route('password.sms')->with('status', 'A 6-digit security code has been sent to your mobile phone via SMS.');
    }

    /**
     * Display SMS verification entry form.
     */
    public function showSmsForm(): View|RedirectResponse
    {
        if (!session('pending_reset_user_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-sms');
    }

    /**
     * Verify SMS code and generate reset token session.
     */
    public function verifySmsCode(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $userId = session('pending_reset_user_id');
        $user = User::findOrFail($userId);

        $record = OtpVerification::where('email', $user->email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record || !Hash::check($request->code, $record->otp)) {
            return back()->withErrors(['code' => 'Invalid or expired SMS verification code.']);
        }

        $record->update(['is_used' => true]);

        $token = Password::createToken($user);
        session()->forget(['pending_reset_user_id', 'masked_phone']);

        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }
}