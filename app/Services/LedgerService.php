<?php

namespace App\Services;

use App\DTO\LedgerData;
use App\Events\LedgerCreated;
use App\Models\Ledger;

class LedgerService
{
    /**
     * @param  LedgerData  $attributes
     * @return Ledger
     */
    public function store(LedgerData $attributes): Ledger
    {
        $ledger = new Ledger([
            'name'        => $attributes->name,
            'date_format' => $attributes->date_format
        ]);

        $ledger->user()->associate($attributes->user);
        $ledger->currency()->associate($attributes->currency);
        $ledger->save();

        event(new LedgerCreated($ledger));

        return $ledger;
    }

    /**
     * @param  Ledger  $ledger
     * @param  LedgerData  $attributes
     * @return Ledger
     */
    public function update(Ledger $ledger, LedgerData $attributes): Ledger
    {
        $ledger->fill([
            'name'        => $attributes->name,
            'date_format' => $attributes->date_format
        ]);

        $ledger->currency()->associate($attributes->currency);
        $ledger->save();

        return $ledger;
    }

    public function delete(Ledger $ledger): Ledger
    {
        $ledger->delete();

        return $ledger;
    }
}
