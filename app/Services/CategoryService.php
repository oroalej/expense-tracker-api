<?php

namespace App\Services;

use App\DTO\BudgetCategoryData;
use App\DTO\CategoryData;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\CategoryGroup;

class CategoryService
{
    public function store(CategoryData $attributes): Category
    {
        $category = new Category($attributes->toArray());

        if (is_null($attributes->order)) {
            $category->order = $attributes->category_group
                    ->categories()
                    ->count() + 1;
        }

        $category->categoryGroup()->associate($attributes->category_group);
        $category->ledger()->associate($attributes->ledger);
        $category->save();

        return $category;
    }

    public function update(Category $category, CategoryData $attributes): Category
    {
        $category->fill($attributes->toArray());
        $category->categoryGroup()->associate($attributes->category_group);

        $category->save();

        return $category;
    }

    public function delete(Category $category, int $targetCategoryId = null): Category
    {
        if ($targetCategoryId && $category->transactions()->exists()) {
            $targetCategory = Category::find($targetCategoryId);

            (new TransactionService())->massAssignCategoryId(
                $category->transactions,
                $targetCategory
            );

            $category->transactions()->update([
                'category_id' => $targetCategoryId,
            ]);

            BudgetCategory::getSummaryFilteredByCategoryIds([$category->id, $targetCategoryId])
                ->each(static function ($item) use ($targetCategory) {
                    $budget         = Budget::find($item->budget_id);
                    $budgetCategory = BudgetCategory::getDataByBudgetAndCategory(
                        budget: $budget,
                        category: $targetCategory,
                    );

                    if ($budgetCategory) {
                        $budgetCategory->update([
                            'assigned'  => $item->assigned,
                            'activity'  => $item->activity,
                            'available' => $item->available,
                        ]);
                    } else {
                        (new BudgetCategoryService())->store(
                            new BudgetCategoryData(
                                category: $targetCategory,
                                budget: $budget,
                                assigned: $item->assigned,
                                available: $item->available,
                                activity: $item->activity
                            )
                        );
                    }
                });
        }

        $category->delete();

        return $category;
    }

    /**
     * @param  Category  $category
     * @param  CategoryGroup  $categoryGroup
     * @return Category
     */
    public function changeCategoryGroup(Category $category, CategoryGroup $categoryGroup): Category
    {
        $category->categoryGroup()->associate($categoryGroup);
        $category->save();

        return $category;
    }

    /**
     * @param  Category  $category
     * @return Category
     */
    public function hide(Category $category): Category
    {
        $category->fill([
            'is_hidden' => true,
        ]);
        $category->save();

        return $category;
    }

    /**
     * @param  Category  $category
     * @return Category
     */
    public function unhide(Category $category): Category
    {
        $category->fill([
            'is_hidden' => false,
        ]);
        $category->save();

        return $category;
    }
}
