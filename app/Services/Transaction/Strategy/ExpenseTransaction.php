<?php

namespace App\Services\Transaction\Strategy;

use App\Models\BudgetCategory;
use App\Models\Transaction;
use App\Services\AccountService;
use App\Services\BudgetCategoryService;
use App\Services\Transaction\Contracts\TransactionInterface;
use App\Services\Transaction\Factory\TransactionFactory;
use App\Services\TransactionService;

class ExpenseTransaction implements TransactionInterface
{
    protected AccountService        $accountService;
    protected BudgetCategoryService $budgetCategoryService;
    protected TransactionService    $transactionService;

    public function __construct()
    {
        $this->accountService        = new AccountService();
        $this->transactionService    = new TransactionService();
        $this->budgetCategoryService = new BudgetCategoryService();
    }

    public function store(Transaction $transaction): Transaction
    {
        $this->budgetCategoryService->deductActivity(
            budgetCategory: BudgetCategory::getByTransaction($transaction),
            amount: $transaction->amount
        );

        if ($transaction->is_approved && $transaction->is_cleared) {
            $this->accountService->deductAccountBalance(
                account: $transaction->account,
                amount: $transaction->amount
            );
        }

        return $transaction;
    }

    public function update(Transaction $originalTransaction, Transaction $transaction): Transaction
    {
        if (
            $transaction->wasChanged(['category_id', 'amount']) ||
            (
                $transaction->wasChanged('transaction_date') &&
                $originalTransaction->transaction_date->diffInMonths($transaction->transaction_date) !== 0
            )
        ) {
            $this->budgetCategoryService->addActivity(
                budgetCategory: BudgetCategory::getByTransaction($originalTransaction),
                amount: $originalTransaction->amount
            );

            if ($transaction->category->category_type === $originalTransaction->category->category_type) {
                $this->budgetCategoryService->deductActivity(
                    budgetCategory: BudgetCategory::getByTransaction($transaction),
                    amount: $transaction->amount
                );
            }
        }

        if ($transaction->is_cleared && $transaction->is_approved) {
            if (
                $transaction->wasChanged('category_id') &&
                $transaction->category->category_type !== $originalTransaction->category->category_type
            ) {
                $this->accountService->addAccountBalance(
                    account: $originalTransaction->account,
                    amount: $originalTransaction->amount
                );

                (TransactionFactory::getStrategy($transaction->category))->store($transaction);
            } elseif ($transaction->wasChanged(['amount', 'account_id'])) {
                $this->accountService->addAccountBalance(
                    account: $originalTransaction->account,
                    amount: $originalTransaction->amount
                );

                $this->accountService->deductAccountBalance(
                    account: $transaction->account,
                    amount: $transaction->amount
                );
            }
        }

        return $transaction;
    }

    public function delete(Transaction $transaction): Transaction
    {
        $this->budgetCategoryService->addActivity(
            budgetCategory: BudgetCategory::getByTransaction($transaction),
            amount: $transaction->amount
        );

        if ($transaction->is_approved && $transaction->is_cleared) {
            $this->accountService->addAccountBalance(
                account: $transaction->account,
                amount: $transaction->amount
            );
        }

        return $transaction;
    }

    public function clear(Transaction $transaction): Transaction
    {
        $this->accountService->deductAccountBalance(
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
            $this->accountService->addAccountBalance(
                account: $transaction->account,
                amount: $transaction->amount
            );
        }

        return $transaction;
    }

    public function approve(Transaction $transaction): Transaction
    {
        if ($transaction->is_cleared && $transaction->is_approved) {
            $this->accountService->deductAccountBalance(
                account: $transaction->account,
                amount: $transaction->amount
            );
        }

        return $transaction;
    }

    public function reject(Transaction $transaction): Transaction
    {
        $this->budgetCategoryService->addActivity(
            budgetCategory: BudgetCategory::getByTransaction($transaction),
            amount: $transaction->amount
        );

        return $transaction;
    }
}
