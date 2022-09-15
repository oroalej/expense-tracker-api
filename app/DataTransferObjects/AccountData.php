<?php

namespace App\DataTransferObjects;

use App\Models\AccountType;
use App\Models\Ledger;

class AccountData
{
    public function __construct(
        public readonly string $name,
        public readonly float $current_balance,
        public readonly AccountType $account_type,
        public readonly Ledger $ledger,
        public readonly bool $is_archived = false
    ) {
    }

    public function toArray(): array
    {
        return [
            'name'            => $this->name,
            'current_balance' => $this->current_balance,
            'is_archived'     => $this->is_archived,
        ];
    }
}
