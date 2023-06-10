<?php

namespace App\DTO;

use App\Http\Requests\Store\StoreAccountRequest;
use App\Http\Requests\Update\UpdateAccountRequest;
use App\Models\AccountType;
use App\Models\Ledger;

class AccountData
{
    public function __construct(
        public readonly string $name,
        public readonly int $current_balance,
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

    /**
     * @param  StoreAccountRequest|UpdateAccountRequest  $request
     * @return AccountData
     */
    public static function fromRequest(StoreAccountRequest|UpdateAccountRequest $request): AccountData
    {
        return new self(
            name: $request->validated('name'),
            current_balance: $request->validated('current_balance'),
            account_type: AccountType::find($request->get('account_type_id')),
            ledger: $request->ledger
        );
    }
}
