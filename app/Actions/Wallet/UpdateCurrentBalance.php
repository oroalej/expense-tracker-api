<?php

namespace App\Actions\Wallet;

use App\Enums\CategoryTypeState;
use App\Models\Wallet;

class UpdateCurrentBalance
{
    public function __construct(
        protected CategoryTypeState $categoryType,
        protected Wallet $wallet,
        protected float $amount
    ) {
    }

    public function execute(): void
    {
        switch ($this->categoryType->value) {
            case CategoryTypeState::Income->value:
                (new AddCurrentBalance(
                    $this->wallet,
                    $this->amount
                ))->execute();
                break;
            case CategoryTypeState::Expense->value:
                (new DeductCurrentBalance(
                    $this->wallet,
                    $this->amount
                ))->execute();
                break;
        }
    }
}
