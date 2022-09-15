<?php

namespace App\Listeners\Registered;

use Database\Seeders\CategorySeeder;
use Illuminate\Auth\Events\Registered;

class SeedCategories
{
    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event): void
    {
        $categorySeeder = new CategorySeeder();
        $categorySeeder->run($event->user);
    }
}
