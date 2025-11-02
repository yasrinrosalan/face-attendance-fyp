<?php
// path: laravel_backend/app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- ADD THIS IMPORT

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
        // --- ADD THIS BLOCK ---
        // This forces Laravel to trust the 'X-Forwarded-Proto' header
        // which ngrok sends. This tells Laravel it's running over HTTPS,
        // so it will generate https:// links for CSS, JS, and routes.
        if ($this->app->environment('production') || $this->app->environment('local')) {
            URL::forceScheme('https');
        }
        // --- END OF ADDED BLOCK ---
    }
}
