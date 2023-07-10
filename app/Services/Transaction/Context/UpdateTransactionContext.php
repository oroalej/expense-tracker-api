<?php

namespace App\Services\Transaction\Context;

use App\DTO\TransactionData;
use App\Models\Transaction;
use App\Services\Transaction\Contracts\TransactionInterface;
use App\Services\Transaction\Factory\TransactionFactory;
use App\Services\Transaction\TransactionService;

class UpdateTransactionContext
{
    protected TransactionInterface $transactionStrategy;
    protected Transaction          $transaction;
    protected TransactionData      $attributes;

    public function __construct(Transaction $transaction, TransactionData $attributes)
    {
        $this->transaction = $transaction;
        $this->attributes  = $attributes;

        $this->transactionStrategy = TransactionFactory::getStrategy(
            $transaction->category
        );
    }

    public function execute(): Transaction
    {
        $originalTransaction = $this->transaction->replicateQuietly(['id']);
        $this->transaction   = (new TransactionService())->update($this->transaction, $this->attributes);

        return $this->transactionStrategy->update($originalTransaction, $this->transaction);
    }
}
