<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
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

        // Note: Do not manually declare Event::listen for LogPasswordReset here.
        // Laravel Auto-Discovery handles listeners inside app/Listeners automatically.
    }
}