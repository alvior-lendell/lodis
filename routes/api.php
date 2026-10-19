<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes registered here run under the 'api' middleware group.
| They are completely stateless: no session cookies, no 'sessions' table
| writes, and no session middleware overhead.
|
*/

// Lightweight ping endpoint for internal system health checks
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
})->name('api.ping');

// Public system health status endpoint read directly from Cache
Route::get('/health-check', function () {
    $healthData = Cache::get('system_health_status', [
        'status' => 'operational',
        'label' => 'All Systems Operational',
        'online' => 0,
        'total' => 0,
        'systems' => [],
    ]);

    return response()->json($healthData);
})->name('api.health.check');