<?php

namespace App\DataTransferObjects;

use App\Models\Account;
use App\Models\Category;

class TransactionData
{
    public function __construct(
        public readonly ?float $inflow,
        public readonly ?float $outflow,
        public readonly string $remarks,
        public readonly string $transaction_date,
        public readonly Category $category,
        public readonly Account $account,
        public readonly bool $is_approved = true,
        public readonly bool $is_cleared = true,
        public readonly bool $is_excluded = false
    ) {
    }

    public function toArray(): array
    {
        return [
            'inflow'           => $this->inflow,
            'outflow'          => $this->outflow,
            'remarks'          => $this->remarks,
            'transaction_date' => $this->transaction_date,
            'is_approved'      => $this->is_approved,
            'is_cleared'       => $this->is_cleared,
            'is_excluded'      => $this->is_excluded,
        ];
    }
}
