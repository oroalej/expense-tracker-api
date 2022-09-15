<?php

namespace App\Actions\Ledger;

use App\DataTransferObjects\LedgerData;
use App\Models\Ledger;

class UpdateLedger
{
    public function execute(Ledger $ledger, LedgerData $attributes): Ledger
    {
        $ledger->fill([
            'name' => $attributes->name,
        ]);
        $ledger->save();

        return $ledger->refresh();
    }
}
