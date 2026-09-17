<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\EnsurePasswordNotExpired;
use Illuminate\Support\Facades\Route;

// Guest Landing / Redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Dynamic Launcher Dashboard
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', EnsurePasswordNotExpired::class])
    ->name('dashboard');

// Protected User & Management Resource Routes
Route::middleware(['auth', EnsurePasswordNotExpired::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('systems', SystemController::class);
    Route::resource('users', UserController::class);
});

// Authenticated Utilities & Holiday Management
Route::middleware('auth')->group(function () {
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays/sync', [HolidayController::class, 'sync'])->name('holidays.sync');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{event}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

require __DIR__.'/auth.php';