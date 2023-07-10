<?php

namespace App\Services\Transaction\Contracts;

use App\Models\Transaction;

interface TransactionInterface
{
    public function store(Transaction $transaction): Transaction;

    public function update(Transaction $originalTransaction, Transaction $transaction): Transaction;

    public function delete(Transaction $transaction): Transaction;

    public function clear(Transaction $transaction): Transaction;

    public function unclear(Transaction $transaction): Transaction;

    public function approve(Transaction $transaction): Transaction;

    public function reject(Transaction $transaction): Transaction;
}
