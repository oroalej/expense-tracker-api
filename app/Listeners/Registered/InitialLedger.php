<?php

namespace App\Listeners\Registered;

use App\DTO\LedgerData;
use App\Enums\DateFormatState;
use App\Models\Currency;
use App\Services\LedgerService;
use Illuminate\Auth\Events\Registered;
use Throwable;

class InitialLedger
{
    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     * @throws Throwable
     */
    public function handle(Registered $event): void
    {
        (new LedgerService())->store(
            new LedgerData(
                name: "My Default",
                date_format: DateFormatState::MMDDYYYY_Slash->value,
                user: $event->user,
                currency: Currency::first()
            )
        );
    }
}
