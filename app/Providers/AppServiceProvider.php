<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Set max execution time to prevent timeout errors
        // Especially important for distributed database architecture
        if (!app()->runningInConsole()) {
            @set_time_limit(120); // 2 minutes max execution time
        }
    }
}
