<?php

namespace App\Actions\Transaction;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class RejectTransaction
{
    public function __construct(public Transaction $transaction)
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        DB::transaction(function () {
            $this->transaction->update([
                'is_approved' => false,
            ]);

            (new DeleteTransaction($this->transaction))->execute();
        });
    }
}
