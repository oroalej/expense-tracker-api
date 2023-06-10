<?php

namespace App\Services;

use App\DTO\TransactionData;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TransactionService
{
    /**
     * @param  TransactionData  $attributes
     * @return Transaction
     */
    public function store(TransactionData $attributes): Transaction
    {
        $transaction = new Transaction($attributes->toArray());

        $transaction->category()->associate($attributes->category);
        $transaction->account()->associate($attributes->account);
        $transaction->ledger()->associate($attributes->ledger);
        $transaction->save();

        (new BudgetCategoryService())->adjustActivity(
            budgetCategory: BudgetCategory::getByTransaction($transaction),
            inflow: $transaction->inflow,
            outflow: $transaction->outflow,
        );

        if ($transaction->is_approved && $transaction->is_cleared) {
            (new AccountService())->adjustAccountBalance(
                account: $transaction->account,
                inflow: $transaction->inflow,
                outflow: $transaction->outflow
            );
        }

        return $transaction;
    }

    /**
     * @param  Transaction  $transaction
     * @param  TransactionData  $attributes
     * @return Transaction
     */
    public function update(Transaction $transaction, TransactionData $attributes): Transaction
    {
        $originalTransaction = $transaction->replicateQuietly(['id']);

        $transaction->fill([
            'remarks'          => $attributes->remarks,
            'inflow'           => $attributes->inflow,
            'outflow'          => $attributes->outflow,
            'transaction_date' => $attributes->transaction_date,
        ]);

        $transaction->category()->associate($attributes->category);
        $transaction->account()->associate($attributes->account);

        if (
            $transaction->isDirty(['category_id', 'inflow', 'outflow']) ||
            (
                $transaction->isDirty('transaction_date') &&
                $originalTransaction->transaction_date->diffInMonths($attributes->transaction_date) !== 0
            )
        ) {
            $budgetCategoryService = (new BudgetCategoryService());

            $budgetCategoryService->rollbackActivity(
                budgetCategory: BudgetCategory::getByTransaction($originalTransaction),
                inflow: $originalTransaction->inflow,
                outflow: $originalTransaction->outflow,
            );

            $budgetCategoryService->adjustActivity(
                budgetCategory: BudgetCategory::getByTransaction($transaction),
                inflow: $attributes->inflow,
                outflow: $attributes->outflow,
            );
        }

        if ($transaction->is_cleared && $transaction->is_approved) {
            if ($transaction->isDirty(['inflow', 'outflow', 'account_id'])) {
                $accountService = (new AccountService());

                $accountService->rollbackAccountBalance(
                    account: $originalTransaction->account,
                    inflow: $originalTransaction->inflow,
                    outflow: $originalTransaction->outflow
                );

                $accountService->adjustAccountBalance(
                    account: $attributes->account,
                    inflow: $attributes->inflow,
                    outflow: $attributes->outflow
                );
            }
        } else {
            if ($transaction->isDirty(['is_cleared', 'is_approved'])) {
                (new AccountService())->rollbackAccountBalance(
                    account: $attributes->account,
                    inflow: $originalTransaction->inflow,
                    outflow: $originalTransaction->outflow
                );
            }
        }

        $transaction->save();

        return $transaction;
    }

    /**
     * @param  Transaction  $transaction
     * @return Transaction
     */
    public function delete(Transaction $transaction): Transaction
    {
        (new BudgetCategoryService())->adjustActivity(
            budgetCategory: BudgetCategory::getByTransaction($transaction),
            inflow: $transaction->inflow,
            outflow: $transaction->outflow,
        );

        if ($transaction->is_approved && $transaction->is_cleared) {
            $transaction->loadMissing('account');

            (new AccountService())->rollbackAccountBalance(
                account: $transaction->account,
                inflow: $transaction->inflow,
                outflow: $transaction->outflow
            );
        }

        $transaction->delete();

        return $transaction;
    }

    /**
     * @param  Collection  $transactions
     * @param  Category  $category
     * @return array
     */
    public function massAssignCategoryId(Collection $transactions, Category $category): array
    {
        $transactions->each(static function (Transaction $transaction) use ($category) {
            $transaction->category()->associate($category);
            $transaction->save();
        });

        return $transactions->pluck('id')->toArray();
    }

    /**
     * @param  Transaction  $transaction
     * @return Transaction
     */
    public function approve(Transaction $transaction): Transaction
    {
        $transaction->fill([
            'is_approved' => true,
            'approved_at' => Carbon::now(),
            'is_cleared'  => $transaction->is_cleared,
            'cleared_at'  => $transaction->cleared_at,
        ]);

        $transaction->save();

        if ($transaction->is_cleared && $transaction->is_approved) {
            (new AccountService())->adjustAccountBalance(
                account: $transaction->account,
                inflow: $transaction->inflow,
                outflow: $transaction->outflow
            );
        }

        return $transaction;
    }

    /**
     * @param  Transaction  $transaction
     * @return Transaction
     */
    public function reject(Transaction $transaction): Transaction
    {
        $transaction->fill([
            'is_approved' => false,
            'rejected_at' => now(),
        ]);
        $transaction->save();
        $transaction->delete();

        (new BudgetCategoryService())->rollbackActivity(
            budgetCategory: BudgetCategory::getByTransaction($transaction),
            inflow: $transaction->inflow,
            outflow: $transaction->outflow
        );

        return $transaction;
    }

    /**
     * @param  Transaction  $transaction
     * @return Transaction
     */
    public function clear(Transaction $transaction): Transaction
    {
        $now = Carbon::now();

        $transaction->loadMissing('account');
        $transaction->fill([
            'is_approved' => true,
            'is_cleared'  => true,
            'approved_at' => $now,
            'cleared_at'  => $now,
        ]);
        $transaction->save();

        (new AccountService())->rollbackAccountBalance(
            account: $transaction->account,
            inflow: $transaction->inflow,
            outflow: $transaction->outflow
        );

        return $transaction;
    }

    /**
     * @param  Transaction  $transaction
     * @return Transaction
     */
    public function unclear(Transaction $transaction): Transaction
    {
        $transaction->loadMissing('account');
        $attributes = [
            'is_cleared' => false,
            'cleared_at' => null
        ];

        if (!$transaction->is_approved) {
            $attributes['is_approved'] = true;
            $attributes['approved_at'] = Carbon::now();
        }

        $transaction->fill($attributes);
        $transaction->save();

        (new AccountService())->rollbackAccountBalance(
            account: $transaction->account,
            inflow: $transaction->inflow,
            outflow: $transaction->outflow
        );

        return $transaction;
    }
}
