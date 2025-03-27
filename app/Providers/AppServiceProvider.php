<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\TrackLastActive;
use App\Http\Middleware\LogPageVisit;

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
        // Register custom middleware
        $this->app['router']->aliasMiddleware('track_last_active', TrackLastActive::class);
        $this->app['router']->aliasMiddleware('log_page_visit', LogPageVisit::class);
    }
}

