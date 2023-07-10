<?php

namespace App\Events;

use App\Models\Ledger;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LedgerCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Ledger $ledger)
    {
    }
}
