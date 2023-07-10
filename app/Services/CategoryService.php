<?php

namespace App\Services;

use App\DTO\BudgetCategoryData;
use App\DTO\CategoryData;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Services\Transaction\TransactionService;

class CategoryService
{
    public function store(CategoryData $attributes): Category
    {
        $category = new Category($attributes->toArray());

        if ($attributes->id) {
            $category->id = $attributes->id;
        }

        if (is_null($attributes->order)) {
            $category->order = Category::getLastOrder(
                ledgerId: $attributes->ledger->id,
                categoryType: $attributes->category_type->value,
                parentId: $attributes->parent?->id
            );
        }

        if ($attributes->parent) {
            $category->parent()->associate($attributes->parent);
        }

        if ($attributes->ledger) {
            $category->ledger()->associate($attributes->ledger);
        }

        $category->save();

        return $category;
    }

    public function update(Category $category, CategoryData $attributes): Category
    {
        $data = $attributes->toArray();

        if ($category->transactions()->doesntExist()) {
            $data['category_type'] = $category->category_type->value;
        }

        if ($attributes->parent) {
            $category->parent()->associate($attributes->parent);
        }

        if ($category->isDirty('parent_id')) {
            $data['order'] = Category::getLastOrder(
                ledgerId: $category->ledger_id,
                categoryType: $data['category_type'],
                parentId: $attributes->parent?->id
            );
        }

        $category->fill($data);
        $category->save();

        return $category;
    }

    public function delete(Category $category, int $targetCategoryId = null): Category
    {
        if ($targetCategoryId && $category->transactions()->exists()) {
            $targetCategory = Category::find($targetCategoryId);

            (new TransactionService())->massAssignToAnotherCategory(
                originalCategory: $category,
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
     * @param  bool  $status
     * @return Category
     */
    public function budgetable(Category $category, bool $status = false): Category
    {
        $category->fill([
            'is_budgetable' => $status
        ]);
        $category->save();

        return $category;
    }

    /**
     * @param  Category  $category
     * @param  bool  $status
     * @return Category
     */
    public function reportable(Category $category, bool $status = false): Category
    {
        $category->fill([
            'is_reportable' => $status
        ]);
        $category->save();

        return $category;
    }

    /**
     * @param  Category  $category
     * @param  bool  $status
     * @return Category
     */
    public function visible(Category $category, bool $status = true): Category
    {
        $category->fill([
            'is_visible' => $status,
        ]);
        $category->save();

        return $category;
    }
}
