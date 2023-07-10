<?php

namespace App\Providers;

use App\Events\LedgerCreated;
use App\Listeners\GenerateDefaultCategories;
use App\Listeners\Registered\InitialLedger;
use App\Models\Ledger;
use App\Observers\LedgerObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class    => [
            SendEmailVerificationNotification::class,
            InitialLedger::class
        ],
        LedgerCreated::class => [
            GenerateDefaultCategories::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        Ledger::observe(LedgerObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
