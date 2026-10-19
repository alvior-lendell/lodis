<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use App\Models\Event as EventModel;
use App\Observers\EventObserver;
use App\Models\System;
use App\Observers\SystemObserver;
use App\Models\Holiday;
use App\Observers\HolidayObserver;
use App\Models\User;
use App\Observers\UserObserver;

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

        // Rate Limiter Configurations
        $this->configureRateLimiting();

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

            // Resolve Profile Photo
            $userPhoto = $employee?->avatar_url 
                ?? $authUser?->avatar_url 
                ?? $authUser?->profile_photo_url
                ?? ($authUser?->profile_photo_path ? asset('storage/' . ltrim($authUser->profile_photo_path, '/')) : null);

            // Notifications resolution & formatting
            $rawNotifications = $authUser?->unreadNotifications()->take(5)->get() ?? collect();
            $unreadCount = $authUser?->unreadNotifications()->count() ?? 0;

            $notifications = $rawNotifications->map(function ($notification) {
                return [
                    'id'               => $notification->id,
                    'title'            => $notification->data['title'] ?? 'Notification',
                    'message'          => $notification->data['message'] ?? '',
                    'read_url'         => Route::has('notifications.read') ? route('notifications.read', $notification->id) : '#',
                    'url'              => $notification->data['url'] ?? '#',
                    'created_at_human' => $notification->created_at?->diffForHumans() ?? '',
                    'is_new'           => true,
                ];
            });

            // Global routes required by header navigation
            $routes = [
                'dashboard'          => Route::has('dashboard') ? route('dashboard') : '/dashboard',
                'systemsIndex'       => Route::has('systems.index') ? route('systems.index') : null,
                'usersIndex'         => Route::has('users.index') ? route('users.index') : null,
                'holidaysIndex'      => Route::has('holidays.index') ? route('holidays.index') : null,
                'markAllRead'        => Route::has('notifications.markAllRead') ? route('notifications.markAllRead') : null,
                'notificationsIndex' => Route::has('notifications.index') ? route('notifications.index') : null,
                'profileEdit'        => Route::has('profile.edit') ? route('profile.edit') : '#',
                'logout'             => Route::has('logout') ? route('logout') : '#',
            ];

            $view->with([
                'authUser'      => $authUser,
                'userRole'      => $userRole,
                'firstName'     => $firstName,
                'fullName'      => $fullName,
                'userPhoto'     => $userPhoto,
                'notifications' => $notifications,
                'unreadCount'   => $unreadCount,
                'routes'        => $routes,
            ]);
        });
        
        View::composer('partials.footer', function ($view) {
            $cachedHealth = Cache::get('system_health_status', [
                'status' => 'operational',
                'label'  => 'All Systems Operational',
            ]);
    
            $view->with([
                'footerIsOperational' => ($cachedHealth['status'] ?? 'operational') === 'operational',
                'footerStatusLabel'   => $cachedHealth['label'] ?? 'All Systems Operational',
            ]);
        });

        User::observe(UserObserver::class);
    }

    /**
     * Configure application rate limiters.
     */
    protected function configureRateLimiting(): void
    {
        // Global API Rate Limiter
        RateLimiter::for('api', function (Request $request) {
            if ($request->userAgent() === 'SystemHealthChecker/1.0') {
                return Limit::none();
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}