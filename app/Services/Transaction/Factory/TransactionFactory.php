<?php

namespace App\Services\Transaction\Factory;

use App\Enums\CategoryTypeState;
use App\Enums\DefaultCategoryIDs;
use App\Models\Category;
use App\Services\Transaction\Contracts\TransactionInterface;
use App\Services\Transaction\Strategy\ExpenseTransaction;
use App\Services\Transaction\Strategy\IncomeTransaction;
use App\Services\Transaction\Strategy\TransferTransaction;
use InvalidArgumentException;

class TransactionFactory
{
    public static function getStrategy(Category $category): TransactionInterface
    {
        $transactionStrategy = match ($category->category_type) {
            CategoryTypeState::INCOME => new IncomeTransaction(),
            CategoryTypeState::EXPENSE => new ExpenseTransaction(),
            default => null,
        };

        if (is_null($transactionStrategy) && $category->category_type === CategoryTypeState::OTHERS) {
            $transactionStrategy = match ($category->id) {
                DefaultCategoryIDs::TRANSFER->value => new TransferTransaction(),
                default => null,
            };
        }

        if (is_null($transactionStrategy)) {
            throw new InvalidArgumentException("Invalid category provided.");
        }

        return $transactionStrategy;
    }
}
