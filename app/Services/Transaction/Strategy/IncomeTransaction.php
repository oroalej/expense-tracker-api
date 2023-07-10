<?php

namespace App\Services\Transaction\Strategy;

use App\Models\Transaction;
use App\Services\AccountService;
use App\Services\Transaction\Contracts\TransactionInterface;
use App\Services\Transaction\Factory\TransactionFactory;

class IncomeTransaction implements TransactionInterface
{
    protected AccountService $accountService;

    public function __construct()
    {
        $this->accountService = new AccountService();
    }

    public function store(Transaction $transaction): Transaction
    {
        if ($transaction->is_approved && $transaction->is_cleared) {
            $this->accountService->addAccountBalance(
                account: $transaction->account,
                amount: $transaction->amount
            );
        }

        return $transaction;
    }

    public function update(Transaction $originalTransaction, Transaction $transaction): Transaction
    {
        if ($transaction->is_cleared && $transaction->is_approved) {
            if (
                $transaction->wasChanged('category_id') &&
                $transaction->category->category_type !== $originalTransaction->category->category_type
            ) {
                $this->accountService->deductAccountBalance(
                    account: $originalTransaction->account,
                    amount: $originalTransaction->amount
                );

                (TransactionFactory::getStrategy($transaction->category))
                    ->store($transaction);
            } elseif ($transaction->wasChanged(['amount', 'account_id'])) {
                $this->accountService->deductAccountBalance(
                    account: $originalTransaction->account,
                    amount: $originalTransaction->amount
                );

                $this->accountService->addAccountBalance(
                    account: $transaction->account,
                    amount: $transaction->amount
                );
            }
        }

        return $transaction;
    }

    public function delete(
        Transaction $transaction
    ): Transaction {
        if ($transaction->is_approved && $transaction->is_cleared) {
            $this->accountService->deductAccountBalance(
                account: $transaction->account,
                amount: $transaction->amount
            );
        }

        return $transaction;
    }

    public function clear(
        Transaction $transaction
    ): Transaction {
        $this->accountService->addAccountBalance(
            account: $transaction->account,
            amount: $transaction->amount
        );

        return $transaction;
    }

    public function unclear(Transaction $transaction): Transaction
    {
        if (
            ! $transaction->wasChanged('is_approved') &&
            ! $transaction->is_cleared &&
            $transaction->wasChanged('is_cleared')
        ) {
            $this->accountService->deductAccountBalance(
                account: $transaction->account,
                amount: $transaction->amount
            );
        }

        return $transaction;
    }

    public function approve(Transaction $transaction): Transaction
    {
        if ($transaction->is_cleared && $transaction->is_approved) {
            $this->accountService->addAccountBalance(
                account: $transaction->account,
                amount: $transaction->amount
            );
        }

        return $transaction;
    }

    public function reject(Transaction $transaction): Transaction
    {
        return $transaction;
    }
}
