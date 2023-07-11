<?php

namespace App\Services;

use App\DTO\BudgetCategoryData;
use App\DTO\Category\CategoryActionsData;
use App\DTO\Category\CategoryData;
use App\DTO\Category\DefaultCategoryData;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Services\Transaction\TransactionService;

class CategoryService
{
    public function store(CategoryData|DefaultCategoryData $attributes): Category
    {
        $category = new Category($attributes->toArray());

        if (is_null($attributes->order)) {
            $category->order = Category::getLastOrder(
                categoryType: $attributes->category_type->value,
                ledgerId: $attributes->ledger?->id,
                parentId: $attributes->parent?->id
            );
        }

        if ($attributes->parent) {
            $category->parent()->associate($attributes->parent);
        }

        if (array_key_exists('ledger', $attributes->toArray()) && $attributes->ledger) {
            $category->ledger()->associate($attributes->ledger);
        }

        $category->save();

        return $category;
    }

    public function update(Category $category, CategoryData $attributes): Category
    {
        $category->fill($attributes->toArray());

        if ($category->isDirty('parent_id')) {
            $category->order = Category::getLastOrder(
                categoryType: $attributes->category_type->value,
                ledgerId: $category->ledger_id,
                parentId: $attributes->parent?->id
            );
        }

        if ($attributes->parent) {
            $category->parent()->associate($attributes->parent);
        }

        $category->save();

        return $category;
    }

    public function delete(Category $category, int $targetCategoryId = null): Category
    {
        if ($targetCategoryId && $category->transactions()->exists()) {
            $targetCategory = Category::find($targetCategoryId);

            (new TransactionService())->massAssignToAnotherCategory(
                originCategory: $category,
                targetCategory: $targetCategory
            );

            BudgetCategory::getSummaryFilteredByCategoryIds([$category->id, $targetCategoryId])
                ->each(static function ($item) use ($targetCategory) {
                    $budget             = Budget::find($item->budget_id);
                    $budgetCategoryData = new BudgetCategoryData(
                        category: $targetCategory,
                        budget: $budget,
                        assigned: $item->assigned,
                        available: $item->available,
                        activity: $item->activity
                    );

                    $budgetCategory = BudgetCategory::getDataByBudgetAndCategory(
                        budget: $budget,
                        category: $targetCategory,
                    );

                    if ($budgetCategory) {
                        (new BudgetCategoryService())->update(
                            $budgetCategory,
                            $budgetCategoryData
                        );
                    } else {
                        (new BudgetCategoryService())->store($budgetCategoryData);
                    }
                });
        }

        $category->delete();

        return $category;
    }

    /**
     * @param  Category  $category
     * @param  Category  $parentCategory
     * @return Category
     */
    public function changeParentCategory(Category $category, Category $parentCategory): Category
    {
        $category->parent()->associate($parentCategory);
        $category->save();

        return $category;
    }

    /**
     * @param  Category  $category
     * @param  CategoryActionsData  $attributes
     * @return Category
     *
     * Reportable, Budgetable, Visible
     */
    public function actions(Category $category, CategoryActionsData $attributes): Category
    {
        $category->fill([
            'is_visible'    => $attributes->is_visible ?? $category->is_visible,
            'is_budgetable' => $attributes->is_budgetable ?? $category->is_budgetable,
            'is_reportable' => $attributes->is_reportable ?? $category->is_reportable,
        ]);

        $category->save();

        return $category;
    }
}
