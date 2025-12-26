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
        // Override Cloudinary binding to disable SSL verification for Windows development
        if (env('CLOUDINARY_VERIFY_SSL') === false || env('CLOUDINARY_VERIFY_SSL') === 'false') {
            $this->app->singleton(\Cloudinary\Cloudinary::class, function ($app) {
                // Create Cloudinary instance with URL
                $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));

                // Set Guzzle HTTP client options to disable SSL verification
                // This is a workaround for Windows cURL SSL certificate issues
                $reflection = new \ReflectionClass($cloudinary);
                if ($reflection->hasProperty('httpClient')) {
                    $httpClientProperty = $reflection->getProperty('httpClient');
                    $httpClientProperty->setAccessible(true);

                    $httpClient = new \GuzzleHttp\Client(['verify' => false]);
                    $httpClientProperty->setValue($cloudinary, $httpClient);
                }

                return $cloudinary;
            });
        }
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
