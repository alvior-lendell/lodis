<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password', [
            'selectedMethod' => request('method', 'email'),
        ]);
    }

    /**
     * Handle incoming password reset requests across selected channels (Email, Authenticator).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'  => ['required', 'email'],
            'method' => ['required', 'string', 'in:email,authenticator'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [__('passwords.user')],
            ]);
        }

        $method = $request->input('method', 'email');

        // 1. Authenticator App (TOTP) Method
        if ($method === 'authenticator') {
            if (empty($user->two_factor_secret) && empty($user->google2fa_secret)) {
                throw ValidationException::withMessages([
                    'method' => ['Authenticator app (TOTP) is not configured for this account. Please choose Email recovery.'],
                ]);
            }

            session([
                'pending_reset_user_id' => $user->id,
            ]);

            return redirect()->route('password.authenticator')->with('status', 'Please enter the code from your authenticator app to reset your password.');
        }

        // 2. Default Email Link Method
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Display Authenticator App (TOTP) verification entry form for password reset.
     */
    public function showAuthenticatorForm(): View|RedirectResponse
    {
        if (!session('pending_reset_user_id')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-authenticator-reset');
    }

    /**
     * Verify Authenticator (TOTP) code and generate reset token session.
     */
    public function verifyAuthenticatorCode(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $userId = session('pending_reset_user_id');
        if (!$userId) {
            return redirect()->route('password.request');
        }

        $user = User::findOrFail($userId);

        $valid = false;
        if (class_exists(\PragmaRX\Google2FA\Google2FA::class) && !empty($user->two_factor_secret)) {
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $valid = $google2fa->verifyKey(decrypt($user->two_factor_secret), $request->code);
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid authenticator verification code.']);
        }

        $token = Password::createToken($user);
        session()->forget('pending_reset_user_id');

        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }
}