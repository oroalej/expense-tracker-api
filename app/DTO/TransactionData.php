<?php

namespace App\DTO;

use App\Http\Requests\Store\StoreTransactionRequest;
use App\Http\Requests\Update\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\Ledger;

class TransactionData
{
    public function __construct(
        public readonly ?string $remarks,
        public readonly string $transaction_date,
        public readonly Category $category,
        public readonly Account $account,
        public readonly Ledger $ledger,
        public readonly ?int $inflow = 0,
        public readonly ?int $outflow = 0,
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

    /**
     * @param  StoreTransactionRequest|UpdateTransactionRequest  $request
     * @return TransactionData
     */
    public static function fromRequest(
        UpdateTransactionRequest|StoreTransactionRequest $request
    ): TransactionData {
        return new self(
            remarks: $request->validated('remarks'),
            transaction_date: $request->validated('transaction_date'),
            category: Category::find($request->get('category_id')),
            account: Account::find($request->get('account_id')),
            ledger: $request->ledger,
            inflow: (int) $request->validated('inflow', 0),
            outflow: (int) $request->validated('outflow', 0),
            is_approved: $request->validated('is_approved', true),
            is_cleared: $request->validated('is_cleared', false),
            is_excluded: $request->validated('is_excluded', false)
        );
    }
}
