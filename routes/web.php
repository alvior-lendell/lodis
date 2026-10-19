<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Middleware\EnsurePasswordNotExpired;
use App\Models\System;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Local Environment / Developer Tools
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::get('/preview-error/{code}', function ($code) {
        if (view()->exists("errors.{$code}")) {
            return view("errors.{$code}");
        }

        return "No error view found for code: {$code}";
    });
}

/*
|--------------------------------------------------------------------------
| System Diagnostics & Public Utilities
|--------------------------------------------------------------------------
*/
Route::get('/health-check', function () {
    $healthData = Cache::get('system_health_status', [
        'status' => 'operational',
        'label' => 'All Systems Operational',
        'systems' => [],
    ]);

    return response()->json($healthData);
})->name('health.check');

/*
|--------------------------------------------------------------------------
| Core Application & Business Feature Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', EnsurePasswordNotExpired::class])->group(function () {
    // Dynamic Application Launcher
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Account Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/devices/others', [ProfileController::class, 'revokeOtherDevices'])->name('profile.devices.revokeOthers');
    Route::post('/profile/devices/bulk-delete', [ProfileController::class, 'bulkRevokeDevices'])->name('profile.devices.bulkRevoke');
    Route::delete('/profile/devices/{device}', [ProfileController::class, 'revokeDevice'])->name('profile.devices.revoke');

    // System Management Resources
    Route::resource('systems', SystemController::class);
    
    // User Management Sub-Routes (Must be declared BEFORE the resource/wildcard route)
    Route::get('/users/system-access', [UserController::class, 'systemAccess'])->name('users.system-access');
    Route::put('/users/{user}/system-access', [UserController::class, 'updateSystemAccess'])->name('users.system-access.update');

    // OTP Audit Logs Routes
    Route::get('/users/otp-logs', [OtpVerificationController::class, 'index'])->name('users.otp-logs');
    Route::delete('/users/otp-logs/{id}', [OtpVerificationController::class, 'destroy'])->name('users.otp-logs.destroy');

    // User Management Resource Route
    Route::resource('users', UserController::class)->except(['show']);

    // Holiday Management Grid & One-Click MCP Sync
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays/sync', [HolidayController::class, 'sync'])->name('holidays.sync');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{event}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

    // Notification Center
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');
});

/*
|--------------------------------------------------------------------------
| Authentication & Identity Pipelines Import
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';