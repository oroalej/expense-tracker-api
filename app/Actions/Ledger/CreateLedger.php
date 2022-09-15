<?php

namespace App\Actions\Ledger;

use App\DataTransferObjects\LedgerData;
use App\Models\Ledger;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateLedger
{
    /**
     * @throws Throwable
     */
    public function execute(LedgerData $data): Ledger
    {
        return DB::transaction(static function () use ($data) {
            $ledger = new Ledger($data->toArray());

            $ledger->user()->associate($data->user);
            $ledger->save();

            return $ledger;
        });
    }
}
