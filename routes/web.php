<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
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
    try {
        $healthData = Cache::remember('system_health_status', 30, function () {
            if (!class_exists(System::class)) {
                return [
                    'status' => 'operational',
                    'label' => 'All Systems Operational',
                    'systems' => []
                ];
            }

            $systems = System::where('is_active', 1)->get();

            if ($systems->isEmpty()) {
                return [
                    'status' => 'operational',
                    'label' => 'All Systems Operational',
                    'systems' => []
                ];
            }

            $total = $systems->count();
            $online = 0;
            $systemStatuses = [];

            foreach ($systems as $system) {
                $url = $system->url ?? $system->app_url ?? $system->system_url ?? null;

                if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                    $systemStatuses[$system->id] = 'operational';
                    $online++;
                    continue;
                }

                try {
                    $response = Http::timeout(2)->withoutVerifying()->get($url);
                    if ($response->successful()) {
                        $systemStatuses[$system->id] = 'operational';
                        $online++;
                    } else {
                        $systemStatuses[$system->id] = 'outage';
                    }
                } catch (\Throwable $e) {
                    $systemStatuses[$system->id] = 'outage';
                }
            }

            if ($online === $total) {
                $status = 'operational';
                $label = 'All Systems Operational';
            } elseif ($online > 0) {
                $status = 'degraded';
                $label = "Partial Outage ({$online}/{$total} Active)";
            } else {
                $status = 'outage';
                $label = 'Major System Outage';
            }

            return [
                'status' => $status,
                'label' => $label,
                'online' => $online,
                'total' => $total,
                'systems' => $systemStatuses,
            ];
        });

        return response()->json($healthData);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'operational',
            'label' => 'All Systems Operational',
            'systems' => [],
        ]);
    }
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

    // System & User Management Resources
    Route::resource('systems', SystemController::class);
    Route::resource('users', UserController::class);

    // Holiday Management Grid & One-Click MCP Sync[cite: 2]
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays/sync', [HolidayController::class, 'sync'])->name('holidays.sync');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{event}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

    // Notification Center
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'readAndRedirect'])->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| Authentication & Identity Pipelines Import
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';