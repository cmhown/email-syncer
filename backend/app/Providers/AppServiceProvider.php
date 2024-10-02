<?php

namespace App\Providers;

use App\Services\Contracts\TokenServiceContract;
use App\Services\GoogleTokenService;
use App\Services\MicrosoftTokenService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TokenServiceContract::class, function ($app, $parameters) {
            switch ($parameters['provider']) {
                case 'microsoft':
                    return new MicrosoftTokenService();
                case 'google':
                    return new GoogleTokenService();
                default:
                    return new MicrosoftTokenService();
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
