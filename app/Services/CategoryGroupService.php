<?php

namespace App\Services;

use App\DTO\BudgetCategoryData;
use App\DTO\CategoryGroupData;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;

class CategoryGroupService
{
    /**
     * @param  CategoryGroupData  $attributes
     * @return CategoryGroup
     */
    public function store(CategoryGroupData $attributes): CategoryGroup
    {
        $categoryGroup = new CategoryGroup($attributes->toArray());
        $categoryGroup->ledger()->associate($attributes->ledger);

        if (is_null($attributes->order)) {
            $order = CategoryGroup::where('ledger_id', $attributes->ledger->id)
                    ->count() + 1;

            $categoryGroup->order = $order;
        }

        $categoryGroup->save();

        return $categoryGroup;
    }

    /**
     * @param  CategoryGroup  $categoryGroup
     * @param  CategoryGroupData  $attributes
     * @return CategoryGroup
     */
    public function update(CategoryGroup $categoryGroup, CategoryGroupData $attributes): CategoryGroup
    {
        $categoryGroup->fill($attributes->toArray());
        $categoryGroup->save();

        return $categoryGroup;
    }

    /**
     * @param  CategoryGroup  $categoryGroup
     * @param  int|null  $destinationCategoryId
     * @return CategoryGroup
     */
    public function delete(CategoryGroup $categoryGroup, int $destinationCategoryId = null): CategoryGroup
    {
        $transactionExists = $categoryGroup->categories()
            ->has('transactions')
            ->exists();

        if ($transactionExists && $destinationCategoryId) {
            $categoryGroup->loadMissing('ledger');
            $destinationCategory = Category::find($destinationCategoryId);

            $toTransferCategoryIds = $categoryGroup
                ->categories()
                ->pluck('id')
                ->toArray();

            BudgetCategory::getSummaryFilteredByCategoryIds([
                ...$toTransferCategoryIds, $destinationCategoryId
            ])
                ->each(static function (BudgetCategory $budgetCategory) use (
                    $destinationCategoryId,
                    $categoryGroup,
                    $destinationCategory
                ) {
                    (new BudgetCategoryService())->update(
                        $budgetCategory,
                        new BudgetCategoryData(
                            category: $destinationCategory,
                            budget: Budget::find($budgetCategory->budget_id),
                            assigned: $budgetCategory->assigned,
                            available: $budgetCategory->available,
                            activity: $budgetCategory->activity,
                        )
                    );
                });


            $transactions =  Transaction::whereIn('category_id', $toTransferCategoryIds)
                ->get();

            (new TransactionService())->massAssignCategoryId(
                $transactions,
                $destinationCategory
            );
        }

        $categoryGroup->delete();

        return $categoryGroup;
    }

    /**
     * @param  CategoryGroup  $categoryGroup
     * @return CategoryGroup
     */
    public function hide(CategoryGroup $categoryGroup): CategoryGroup
    {
        $categoryGroup->fill([
            'is_hidden' => true,
        ]);

        $categoryGroup->save();

        return $categoryGroup;
    }

    /**
     * @param  CategoryGroup  $categoryGroup
     * @return CategoryGroup
     */
    public function unhide(CategoryGroup $categoryGroup): CategoryGroup
    {
        $categoryGroup->fill([
            'is_hidden' => false,
        ]);

        $categoryGroup->save();

        return $categoryGroup;
    }
}
