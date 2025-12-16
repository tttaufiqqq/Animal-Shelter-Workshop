<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationEvent;
use App\Models\Animal;
use App\Models\Medical;
use App\Models\Vaccination;
use App\Observers\AnimalObserver;
use App\Observers\MedicalObserver;
use App\Observers\VaccinationObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
        if (! app()->runningInConsole()) {
            @set_time_limit(120); // 2 minutes max execution time
        }

        // Register Model Observers for Audit Logging
        Animal::observe(AnimalObserver::class);
        Medical::observe(MedicalObserver::class);
        Vaccination::observe(VaccinationObserver::class);

        // Register Authentication Event Listeners for Audit Logging
        Event::listen(Login::class, [LogAuthenticationEvent::class, 'handleLogin']);
        Event::listen(Logout::class, [LogAuthenticationEvent::class, 'handleLogout']);
        Event::listen(Failed::class, [LogAuthenticationEvent::class, 'handleFailed']);
        Event::listen(Lockout::class, [LogAuthenticationEvent::class, 'handleLockout']);
    }
}
