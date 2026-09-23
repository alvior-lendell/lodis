<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\DeviceVerificationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\ExpiredPasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SmsVerificationController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Security & Authentication Pipelines
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Guest Landing & Credential Sign-In
    Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');

    Route::get('login', function () {
        return redirect()->route('login');
    });
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');

    // User Registration Pipeline
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    
    Route::post('register/check-email', [RegisteredUserController::class, 'checkEmail'])
        ->middleware('throttle:10,1')
        ->name('register.check-email');

    Route::get('register/check-email', function () {
        return redirect()->route('register');
    });

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::get('register/select-otp-method', [RegisteredUserController::class, 'showSelectMethodForm'])
        ->name('register.otp.select');

    Route::get('register/verify-otp', [RegisteredUserController::class, 'showOtpForm'])
        ->name('register.otp');

    Route::post('register/verify-otp', [RegisteredUserController::class, 'verifyOtp'])
        ->middleware('throttle:6,1')
        ->name('register.otp.verify');

    Route::post('register/resend-otp', [RegisteredUserController::class, 'resendOtp'])
        ->middleware('throttle:3,1')
        ->name('register.otp.resend');

    Route::post('register/verify-otp/switch-method', [RegisteredUserController::class, 'switchMethod'])
        ->name('register.otp.switch-method');

    // SMS Phone Number Verification
    Route::get('verify-sms', [SmsVerificationController::class, 'create'])
        ->name('verification.sms');

    Route::post('verify-sms', [SmsVerificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.sms.verify');

    Route::post('verify-sms/resend', [SmsVerificationController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('verification.sms.resend');

    // TOTP / Authenticator App Verification
    Route::get('verify-authenticator', [TwoFactorAuthenticationController::class, 'create'])
        ->name('2fa.authenticator');

    Route::post('verify-authenticator', [TwoFactorAuthenticationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('2fa.authenticator.verify');

    // Workstation & Device Verification Streams
    Route::get('/auth/device-wait/{id}', [DeviceVerificationController::class, 'showWait'])
        ->name('device.wait');

    Route::post('/auth/device-verify-start/{id}', [DeviceVerificationController::class, 'startVerification'])
        ->middleware('throttle:6,1')
        ->name('device.start-verification');

    Route::post('/auth/device-finalize/{id}', [DeviceVerificationController::class, 'finalize'])
        ->middleware('throttle:6,1')
        ->name('device.finalize');

    Route::post('/auth/device-fallback-otp/{id}', [DeviceVerificationController::class, 'fallbackOtp'])
        ->middleware('throttle:5,1')
        ->name('device.fallback-otp');

    // Dedicated Device Authorization OTP
    Route::get('/auth/device-otp', [DeviceVerificationController::class, 'showOtp'])
        ->name('device.otp');

    Route::post('/auth/device-otp', [DeviceVerificationController::class, 'verifyOtp'])
        ->middleware('throttle:6,1')
        ->name('device.otp.verify');

    Route::post('/auth/device-otp/switch', [DeviceVerificationController::class, 'switchMethod'])
        ->name('device.otp.switch');

    Route::post('/auth/device-otp/resend', [DeviceVerificationController::class, 'resendOtp'])
        ->middleware('throttle:3,1')
        ->name('device.otp.resend');

    // Credential Recovery & Password Reset
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('forgot-password/authenticator', [PasswordResetLinkController::class, 'showAuthenticatorForm'])
        ->name('password.authenticator');

    Route::post('forgot-password/authenticator/verify', [PasswordResetLinkController::class, 'verifyAuthenticatorCode'])
        ->middleware('throttle:6,1')
        ->name('password.authenticator.verify');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Identity & Security Controls
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Email Verification Prompts & Callbacks
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Password Re-confirmation, Expiry, & Modification[cite: 6]
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    Route::get('password/expired', [ExpiredPasswordController::class, 'show'])
        ->name('password.expired');

    Route::post('password/expired', [ExpiredPasswordController::class, 'update'])
        ->name('password.expired.update');

    // Active Browser Session Management[cite: 7]
    Route::post('/profile/sessions/revoke-others', [ProfileController::class, 'revokeOtherSessions'])
        ->name('sessions.revoke-others');

    Route::delete('/profile/sessions/{sessionId}', [ProfileController::class, 'revokeSession'])
        ->name('sessions.revoke');

    // Trusted Device Authorizations & Revocations
    Route::get('/auth/device-review/{id}', [DeviceVerificationController::class, 'review'])
        ->name('device.review');

    Route::post('/auth/device-approve/{id}', [DeviceVerificationController::class, 'approve'])
        ->name('device.approve');

    Route::post('/auth/device-reject/{id}', [DeviceVerificationController::class, 'reject'])
        ->name('device.reject');

    Route::delete('/profile/devices/{device}', [ProfileController::class, 'revokeDevice'])
        ->name('devices.revoke');

    // Session Termination
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});