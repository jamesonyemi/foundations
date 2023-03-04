<?php

namespace App\Providers;

use App\Events\KpiActivityApprovedEvent;
use App\Events\KpiActivityCreatedEvent;
use App\Events\KpiApprovedEvent;
use App\Events\KpiCreated;
use App\Events\LeaveApplicationEvent;
use App\Events\LeaveApproveEvent;
use App\Events\LoginEvent;
use App\Events\PostCommentCreatedEvent;
use App\Events\PostCreatedEvent;
use App\Listeners\KpiActivityApprovedListener;
use App\Listeners\KpiActivityCreatedListener;
use App\Listeners\KpiApprovedListener;
use App\Listeners\KpiCreatedListener;
use App\Listeners\LeaveApplicationListener;
use App\Listeners\LeaveApproveListener;
use App\Listeners\LoginListener;
use App\Listeners\PostCommentCreatedListener;
use App\Listeners\PostCreatedListener;
use App\Models\KpiComment;
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
        PostCreatedEvent::class => [
            PostCreatedListener::class,
        ],
        PostCommentCreatedEvent::class => [
            PostCommentCreatedListener::class,
        ],
        KpiCreated::class => [
            KpiCreatedListener::class,
        ],
        KpiApprovedEvent::class => [
            KpiApprovedListener::class,
        ],
        KpiActivityCreatedEvent::class => [
            KpiActivityCreatedListener::class,
        ],
        KpiActivityApprovedEvent::class => [
            KpiActivityApprovedListener::class,
        ],
        LoginEvent::class => [
            LoginListener::class,
        ],
        LeaveApproveEvent::class => [
            LeaveApproveListener::class,
        ],
        LeaveApplicationEvent::class => [
            LeaveApplicationListener::class,
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
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
