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
        public readonly int $amount,
        public readonly ?Account $transfer = null,
        public readonly bool $is_approved = true,
        public readonly bool $is_cleared = true,
        public readonly bool $is_excluded = false
    ) {
    }

    public function toArray(): array
    {
        return [
            'amount'           => $this->amount,
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
        $transferAccount = null;

        if ($request->filled('transfer_id')) {
            $transferAccount = Account::find($request->validated('transfer_id'));
        }

        return new self(
            remarks: $request->validated('remarks'),
            transaction_date: $request->validated('transaction_date'),
            category: Category::find($request->validated('category_id')),
            account: Account::find($request->validated('account_id')),
            ledger: $request->ledger,
            amount: (int) $request->validated('amount', 0),
            transfer: $transferAccount,
            is_approved: $request->validated('is_approved', true),
            is_cleared: $request->validated('is_cleared', true),
            is_excluded: $request->validated('is_excluded', false),
        );
    }
}
