<?php

namespace App\Actions\Transaction;

use App\Actions\Account\RollbackAccountBalance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteTransaction
{
    public function __construct(protected Transaction $transaction)
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        DB::transaction(function () {
            $this->transaction->delete();

            if (
                $this->transaction->is_cleared &&
                $this->transaction->is_approved
            ) {
                (new RollbackAccountBalance(
                    $this->transaction->account,
                    $this->transaction->inflow,
                    $this->transaction->outflow
                ))->execute();
            }
        });
    }
}
