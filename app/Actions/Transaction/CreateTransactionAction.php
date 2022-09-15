<?php

namespace App\Actions\Transaction;

use App\Actions\Account\AdjustAccountBalanceAction;
use App\DataTransferObjects\TransactionData;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTransactionAction
{
    /**
     * @throws Throwable
     */
    public function execute(TransactionData $attributes): Transaction
    {
        return DB::transaction(static function () use ($attributes) {
            $transaction = new Transaction($attributes->toArray());

            $transaction->category()->associate($attributes->category);
            $transaction->account()->associate($attributes->account);
            $transaction->save();

            if ($transaction->is_cleared && $transaction->is_approved) {
                $transaction->loadMissing('account');

                (new AdjustAccountBalanceAction(
                    $transaction->account,
                    $transaction->inflow,
                    $transaction->outflow
                ))->execute();
            }

            return $transaction;
        });
    }
}
