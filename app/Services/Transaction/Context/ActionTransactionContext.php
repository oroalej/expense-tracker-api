<?php

namespace App\Services\Transaction\Context;

use App\Models\Transaction;
use App\Services\Transaction\Contracts\TransactionInterface;
use App\Services\Transaction\Factory\TransactionFactory;
use App\Services\Transaction\TransactionService;

class ActionTransactionContext
{
    protected TransactionInterface $transactionStrategy;
    protected Transaction          $transaction;
    protected TransactionService   $transactionService;

    public function __construct(Transaction $transaction)
    {
        $this->transaction         = $transaction;
        $this->transactionService  = new TransactionService();
        $this->transactionStrategy = TransactionFactory::getStrategy(
            $transaction->category
        );
    }

    public function delete(): Transaction
    {
        $this->transaction = $this->transactionService->delete($this->transaction);

        return $this->transactionStrategy->delete($this->transaction);
    }

    public function clear(): Transaction
    {
        $this->transaction = $this->transactionService->clear($this->transaction);

        return $this->transactionStrategy->clear($this->transaction);
    }

    public function unclear(): Transaction
    {
        $this->transaction = $this->transactionService->unclear($this->transaction);

        return $this->transactionStrategy->unclear($this->transaction);
    }

    public function approve(): Transaction
    {
        $this->transaction = $this->transactionService->approve($this->transaction);

        return $this->transactionStrategy->approve($this->transaction);
    }

    public function reject(): Transaction
    {
        $this->transaction = $this->transactionService->reject($this->transaction);

        return $this->transactionStrategy->reject($this->transaction);
    }
}
