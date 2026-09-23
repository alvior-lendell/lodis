<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Event as EventModel;
use App\Observers\EventObserver;
use App\Models\System;
use App\Observers\SystemObserver;
use App\Models\Holiday;
use App\Observers\HolidayObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS URL generation behind proxies or non-local environments
        if (config('app.env') !== 'local' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            URL::forceScheme('https');
        }

        // Eloquent Model Observers
        EventModel::observe(EventObserver::class);
        System::observe(SystemObserver::class);
        Holiday::observe(HolidayObserver::class);

        // View Composer for Header Partial
        View::composer('partials.header', function ($view) {
            $authUser = auth()->user();
            $userRole = $authUser?->role ?? 'Employee';

            // Resolve employee relation
            $employee = $authUser?->employee;

            // Resolve Name
            $firstName = $employee?->first_name ?? $authUser?->first_name ?? '';
            $lastName = $employee?->last_name ?? $authUser?->last_name ?? '';
            $fullName = trim("{$firstName} {$lastName}") ?: ($authUser?->name ?? 'User');

            // Resolve Profile Photo using Employee's avatar_url attribute or User attributes
            $userPhoto = $employee?->avatar_url 
                ?? $authUser?->avatar_url 
                ?? $authUser?->profile_photo_url
                ?? ($authUser?->profile_photo_path ? asset('storage/' . ltrim($authUser->profile_photo_path, '/')) : null);

            // Notifications resolution
            $notifications = $authUser?->unreadNotifications()->take(5)->get() ?? collect();
            $unreadCount = $authUser?->unreadNotifications()->count() ?? 0;

            $view->with([
                'authUser'      => $authUser,
                'userRole'      => $userRole,
                'firstName'     => $firstName,
                'fullName'      => $fullName,
                'userPhoto'     => $userPhoto,
                'notifications' => $notifications,
                'unreadCount'   => $unreadCount,
            ]);
        });
        
        View::composer('partials.footer', function ($view) {
            $cachedHealth = Cache::get('system_health_status', [
                'status' => 'operational',
                'label' => 'All Systems Operational',
            ]);
    
            $view->with([
                'footerIsOperational' => ($cachedHealth['status'] ?? 'operational') === 'operational',
                'footerStatusLabel' => $cachedHealth['label'] ?? 'All Systems Operational',
            ]);
        });
    }
}