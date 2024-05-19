<?php

namespace App\Providers;

use App\Events\ApiLogEvent;
use App\Events\SessionLogEvent;
use App\Listeners\ApiLogListener;
use App\Listeners\SessionLogListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
	    'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        SessionLogEvent::class => [
            SessionLogListener::class
        ],
        ApiLogEvent::class => [
            ApiLogListener::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
