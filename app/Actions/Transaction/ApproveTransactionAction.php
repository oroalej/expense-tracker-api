<?php

namespace App\Actions\Transaction;

use App\Actions\Account\AdjustAccountBalanceAction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApproveTransactionAction
{
    /**
     * @throws Throwable
     */
    public function execute(Transaction $transaction)
    {
        return DB::transaction(static function () use ($transaction) {
            $transaction->update([
                'is_approved' => true,
                'is_cleared'  => false,
                'approved_at' => Carbon::now(),
                'cleared_at'  => null
            ]);

            if (
                $transaction->is_cleared &&
                $transaction->is_approved
            ) {
                $transaction->loadMissing('account');

                (new AdjustAccountBalanceAction(
                    $transaction->account->refresh(),
                    $transaction->inflow,
                    $transaction->outflow
                ))->execute();
            }

            return $transaction->refresh();
        });
    }
}
