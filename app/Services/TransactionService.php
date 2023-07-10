<?php

namespace App\Services;

use App\DTO\TransactionData;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionService
{
    public function store(TransactionData $attributes): Transaction
    {
        $transaction = new Transaction($attributes->toArray());

        $transaction->category()->associate($attributes->category);
        $transaction->account()->associate($attributes->account);
        $transaction->ledger()->associate($attributes->ledger);

        if ($attributes->transfer) {
            $transaction->transfer()->associate($attributes->transfer);
        }

        $transaction->save();

        return $transaction;
    }

    public function update(Transaction $transaction, TransactionData $attributes): Transaction
    {
        $transaction->fill([
            'remarks'          => $attributes->remarks,
            'amount'           => $attributes->amount,
            'transaction_date' => $attributes->transaction_date,
        ]);
        $transaction->category()->associate($attributes->category);
        $transaction->account()->associate($attributes->account);

        if ($attributes->transfer) {
            $transaction->transfer()->associate($attributes->transfer);
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
        $transaction->delete();

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

        if (! $transaction->is_approved) {
            $attributes['is_approved'] = true;
            $attributes['approved_at'] = Carbon::now();
        }

        $transaction->fill($attributes);
        $transaction->save();

        return $transaction;
    }

    public function approve(Transaction $transaction): Transaction
    {
        $transaction->fill([
            'is_approved' => true,
            'approved_at' => Carbon::now(),
            'is_cleared'  => $transaction->is_cleared,
            'cleared_at'  => $transaction->cleared_at,
        ]);

        $transaction->save();

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

        return $transaction;
    }
}
