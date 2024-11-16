<?php

namespace Aireset\Providers;

use Aireset\Events\User\Banned;
use Aireset\Events\User\LoggedIn;
use Aireset\Events\User\Registered;
use Aireset\Listeners\Users\InvalidateSessionsAndTokens;
use Aireset\Listeners\Login\UpdateLastLoginTimestamp;
use Aireset\Listeners\Registration\SendConfirmationEmail;
use Aireset\Listeners\PermissionEventsSubscriber;
use Aireset\Listeners\RoleEventsSubscriber;
use Aireset\Listeners\UserEventsSubscriber;
use Aireset\Listeners\ShopEventsSubscriber;
use Aireset\Listeners\ReturnEventsSubscriber;
use Aireset\Listeners\JackpotEventsSubscriber;
use Aireset\Listeners\GameEventsSubscriber;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendConfirmationEmail::class,
        ],
        LoggedIn::class => [
            UpdateLastLoginTimestamp::class
        ],
        Banned::class => [
            InvalidateSessionsAndTokens::class
        ],

    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        UserEventsSubscriber::class,
        RoleEventsSubscriber::class,
        PermissionEventsSubscriber::class,
        ShopEventsSubscriber::class,
        ReturnEventsSubscriber::class,
        JackpotEventsSubscriber::class,
        GameEventsSubscriber::class,
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
