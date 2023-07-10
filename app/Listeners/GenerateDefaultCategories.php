<?php

namespace App\Listeners;

use App\Events\LedgerCreated;
use Database\Seeders\CategorySeeder;

class GenerateDefaultCategories
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  LedgerCreated  $event
     * @return void
     */
    public function handle(LedgerCreated $event): void
    {
        (new CategorySeeder($event->ledger))->run();
    }
}
