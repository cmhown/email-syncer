<?php

namespace App\Providers;

use App\Events\ImapDeleteMessageEvent;
use App\Events\ImapFlagMessageEvent;
use App\Events\ImapNewMessageEvent;
use App\Listeners\ImapDeleteMessageHandler;
use App\Listeners\ImapNewMessageHandler;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ImapNewMessageEvent::class => [
            ImapNewMessageHandler::class
        ],
        ImapFlagMessageEvent::class => [
            ImapNewMessageHandler::class
        ],
        ImapDeleteMessageEvent::class => [
            ImapDeleteMessageHandler::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        \Socialite::extend('microsoft', function ($app) {
            $config = $app['config']['services.microsoft'];
            return \Socialite::buildProvider(\SocialiteProviders\Microsoft\Provider::class, $config);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
