<?php

namespace App\Actions\Transaction;

use App\Actions\Account\AddCurrentBalanceAction;
use App\Actions\Account\DeductCurrentBalanceAction;
use App\DataTransferObjects\TransactionData;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateTransactionAction
{
    /**
     * @throws Throwable
     */
    public function execute(Transaction $transaction, TransactionData $attributes): Transaction
    {
        return DB::transaction(static function () use ($transaction, $attributes) {
            $originalTransaction = $transaction->replicate(['id']);

            $transaction->fill([
                'remarks'          => $attributes->remarks,
                'inflow'           => $attributes->inflow,
                'outflow'          => $attributes->outflow,
                'transaction_date' => $attributes->transaction_date,
            ]);

            $transaction->save();

            if ($transaction->wasChanged(['inflow', 'account_id'])) {
                (new DeductCurrentBalanceAction())->execute(
                    $originalTransaction->account->refresh(),
                    $originalTransaction->inflow
                );

                (new AddCurrentBalanceAction())->execute(
                    $attributes->account->refresh(),
                    $attributes->inflow
                );
            }

            if ($transaction->wasChanged(['outflow', 'account_id'])) {
                (new AddCurrentBalanceAction())->execute(
                    $originalTransaction->account->refresh(),
                    $originalTransaction->outflow
                );

                (new DeductCurrentBalanceAction())->execute(
                    $attributes->account->refresh(),
                    $attributes->outflow
                );
            }

            return $transaction->refresh();
        });
    }
}
