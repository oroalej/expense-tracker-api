<?php

namespace App\Services\Transaction\Context;

use App\DTO\TransactionData;
use App\Models\Transaction;
use App\Services\Transaction\Contracts\TransactionInterface;
use App\Services\Transaction\Factory\TransactionFactory;
use App\Services\TransactionService;

class StoreTransactionContext
{
    protected TransactionInterface $transactionStrategy;
    protected TransactionData      $attributes;

    public function __construct(TransactionData $attributes)
    {
        $this->attributes          = $attributes;
        $this->transactionStrategy = TransactionFactory::getStrategy(
            $attributes->category
        );
    }

    public function execute(): Transaction
    {
        $transaction = (new TransactionService())->store($this->attributes);

        return $this->transactionStrategy->store($transaction);
    }
}
