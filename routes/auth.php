<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\DeviceVerificationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SmsVerificationController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Registration Routes
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register/check-email', [RegisteredUserController::class, 'checkEmail'])
        ->name('register.check-email');

    Route::get('register/check-email', function () {
        return redirect()->route('register');
    });

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('register/select-otp-method', [RegisteredUserController::class, 'showSelectMethodForm'])
        ->name('register.otp.select');

    Route::get('register/verify-otp', [RegisteredUserController::class, 'showOtpForm'])
        ->name('register.otp');

    Route::post('register/verify-otp', [RegisteredUserController::class, 'verifyOtp'])
        ->name('register.otp.verify');

    Route::post('register/resend-otp', [RegisteredUserController::class, 'resendOtp'])
        ->name('register.otp.resend');

    Route::post('register/verify-otp/switch-method', [RegisteredUserController::class, 'switchMethod'])
        ->name('register.otp.switch-method');

    // Authentication Routes (Root & /login mapping)
    Route::get('/', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/', [AuthenticatedSessionController::class, 'store']);

    Route::get('login', function () {
        return redirect('/');
    });

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // SMS Phone Number Verification Routes
    Route::get('verify-sms', [SmsVerificationController::class, 'create'])
        ->name('verification.sms');

    Route::post('verify-sms', [SmsVerificationController::class, 'store'])
        ->name('verification.sms.verify');

    Route::post('verify-sms/resend', [SmsVerificationController::class, 'resend'])
        ->name('verification.sms.resend');

    // TOTP / Authenticator App Verification Routes
    Route::get('verify-authenticator', [TwoFactorAuthenticationController::class, 'create'])
        ->name('2fa.authenticator');

    Route::post('verify-authenticator', [TwoFactorAuthenticationController::class, 'store'])
        ->name('2fa.authenticator.verify');

    // Workstation & Device Verification Wait/Finalize
    Route::get('/auth/device-wait/{id}', [DeviceVerificationController::class, 'showWait'])
        ->name('device.wait');

    Route::post('/auth/device-verify-start/{id}', [DeviceVerificationController::class, 'startVerification'])
        ->name('device.start-verification');

    Route::post('/auth/device-finalize/{id}', [DeviceVerificationController::class, 'finalize'])
        ->name('device.finalize');

    // Dynamic Multi-Method Device Verification Routes (Email, SMS, Authenticator)
    Route::get('/auth/device-otp', [DeviceVerificationController::class, 'showOtp'])
        ->name('device.otp');

    Route::post('/auth/device-otp', [DeviceVerificationController::class, 'verifyOtp'])
        ->name('device.otp.verify');

    Route::post('/auth/device-otp/switch', [DeviceVerificationController::class, 'switchMethod'])
        ->name('device.otp.switch');

    Route::post('/auth/device-otp/resend', [DeviceVerificationController::class, 'resendOtp'])
        ->name('device.otp.resend');
    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Dedicated SMS Password Reset Verification Routes
    Route::get('forgot-password/sms', [PasswordResetLinkController::class, 'showSmsForm'])
        ->name('password.sms');

    Route::post('forgot-password/sms', [PasswordResetLinkController::class, 'sendSmsCode'])
        ->name('password.sms.send');

    Route::post('forgot-password/sms/verify', [PasswordResetLinkController::class, 'verifySmsCode'])
        ->name('password.sms.verify');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    
    // Register "Mark All Read" Route
    Route::post('/notifications/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('status', 'All notifications marked as read.');
    })->name('notifications.markAllRead');
    
    // Email Verification Routes
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Password Confirmation & Updates
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    // Active Session Management (Revoke Specific & All Other Browser Sessions)
    Route::post('/profile/sessions/revoke-others', [ProfileController::class, 'revokeOtherSessions'])
        ->name('sessions.revoke-others');

    Route::delete('/profile/sessions/{sessionId}', [ProfileController::class, 'revokeSession'])
        ->name('sessions.revoke');

    // Trusted Device Authorization & Revocation
    Route::post('/auth/device-approve/{id}', [DeviceVerificationController::class, 'approve'])
        ->name('device.approve');

    Route::post('/auth/device-reject/{id}', [DeviceVerificationController::class, 'reject'])
        ->name('device.reject');

    Route::delete('/profile/devices/{device}', [ProfileController::class, 'revokeDevice'])
        ->name('devices.revoke');

    // Session Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});