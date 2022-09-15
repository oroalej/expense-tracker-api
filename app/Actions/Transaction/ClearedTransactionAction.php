<?php

namespace App\Actions\Transaction;

use App\Actions\Account\AdjustAccountBalanceAction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClearedTransactionAction
{
    /**
     * @throws Throwable
     */
    public function execute(Transaction $transaction): void
    {
        DB::transaction(static function () use ($transaction) {
            $now = Carbon::now();

            $transaction->update([
                'is_approved' => true,
                'is_cleared'  => true,
                'approved_at' => $now,
                'cleared_at'  => $now
            ]);

            $transaction->loadMissing('account');

            (new AdjustAccountBalanceAction(
                $transaction->account,
                $transaction->inflow,
                $transaction->outflow
            ))->execute();
        });
    }
}
