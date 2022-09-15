<?php

namespace App\Actions\Transaction;

use App\Actions\Account\RollbackAccountBalance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class UnclearedTransaction
{
    public function __construct(public Transaction $transaction)
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        DB::transaction(function () {
            $this->transaction->update([
                'is_approved' => true,
                'is_cleared' => false,
            ]);

            if (
                ! $this->transaction->is_cleared &&
                $this->transaction->wasChanged('is_cleared')
            ) {
                $this->transaction->loadMissing('account');

                (new RollbackAccountBalance(
                    $this->transaction->account,
                    $this->transaction->inflow,
                    $this->transaction->outflow
                ))->execute();
            }
        });
    }
}
