<?php

namespace App\Services;

use App\DTO\BudgetCategoryData;
use App\Enums\CategoryTypeState;
use App\Models\BudgetCategory;
use DB;

class BudgetCategoryService
{
    public function store(BudgetCategoryData $attributes): BudgetCategory
    {
        $budgetCategory = new BudgetCategory([
            'assigned'  => $attributes->assigned,
            'available' => $attributes->available,
            'activity'  => $attributes->activity,
        ]);

        $budgetCategory->budget()->associate($attributes->budget);
        $budgetCategory->category()->associate($attributes->category);
        $budgetCategory->save();

        return $budgetCategory;
    }

    public function update(BudgetCategory $budgetCategory, BudgetCategoryData $attributes): BudgetCategory
    {
        $budgetCategory = $budgetCategory->fill([
            'assigned'  => $attributes->assigned,
            'available' => $attributes->available,
            'activity'  => $attributes->activity,
        ]);

        $budgetCategory->budget()->associate($attributes->budget);
        $budgetCategory->category()->associate($attributes->category);
        $budgetCategory->save();

        return $budgetCategory;
    }

    public function adjustAssigned(
        BudgetCategory $budgetCategory,
        int $amount
    ): BudgetCategory {
        $budgetCategory->fill([
            'assigned'  => $amount,
            'available' => $amount - $budgetCategory->activity,
        ]);

        $budgetCategory->save();

        return $budgetCategory;
    }

    public function addActivity(BudgetCategory $budgetCategory, int $amount): void
    {
        $budgetCategory->fill([
            'activity'  => DB::raw("activity + $amount"),
            'available' => DB::raw("available + $amount"),
        ]);
        $budgetCategory->save();
    }

    public function deductActivity(BudgetCategory $budgetCategory, int $amount): void
    {
        $budgetCategory->fill([
            'activity'  => DB::raw("activity - $amount"),
            'available' => DB::raw("available - $amount"),
        ]);
        $budgetCategory->save();
    }

    public function adjustActivity(
        BudgetCategory $budgetCategory,
        CategoryTypeState $categoryType,
        int $amount,
    ): BudgetCategory {
        switch ($categoryType) {
            case CategoryTypeState::INCOME:
                $this->addActivity($budgetCategory, $amount);
                break;
            case CategoryTypeState::EXPENSE:
                $this->deductActivity($budgetCategory, $amount);
                break;
        }

        return $budgetCategory->refresh();
    }

    public function rollbackActivity(
        BudgetCategory $budgetCategory,
        CategoryTypeState $categoryType,
        int $amount,
    ): BudgetCategory {
        switch ($categoryType) {
            case CategoryTypeState::INCOME:
                $this->deductActivity($budgetCategory, $amount);
                break;
            case CategoryTypeState::EXPENSE:
                $this->addActivity($budgetCategory, $amount);
                break;
        }

        return $budgetCategory->refresh();
    }
}
