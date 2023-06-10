<?php

namespace App\Services;

use App\DTO\BudgetCategoryData;
use App\Models\BudgetCategory;
use DB;

class BudgetCategoryService
{
    /**
     * @param  BudgetCategoryData  $attributes
     * @return BudgetCategory
     */
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

    /**
     * @param  BudgetCategory  $budgetCategory
     * @param  int  $amount
     * @return BudgetCategory
     */
    public function adjustAssigned(
        BudgetCategory $budgetCategory,
        int $amount
    ): BudgetCategory {
        $budgetCategory->fill([
            'assigned'  => $amount,
            'available' => $amount - $budgetCategory->activity
        ]);

        $budgetCategory->save();

        return $budgetCategory;
    }

    /**
     * @param  BudgetCategory  $budgetCategory
     * @param  int  $inflow
     * @param  int  $outflow
     * @return BudgetCategory
     */
    public function adjustActivity(
        BudgetCategory $budgetCategory,
        int $inflow = 0,
        int $outflow = 0
    ): BudgetCategory {
        $activityAmount = $inflow + $budgetCategory->activity - $outflow;

        $budgetCategory->fill([
            'activity'  => $activityAmount,
            'available' => DB::raw("assigned + $activityAmount")
        ]);
        $budgetCategory->save();

        return $budgetCategory;
    }

    /**
     * @param  BudgetCategory  $budgetCategory
     * @param  int  $inflow
     * @param  int  $outflow
     * @return BudgetCategory
     */
    public function rollbackActivity(
        BudgetCategory $budgetCategory,
        int $inflow,
        int $outflow
    ): BudgetCategory {
        $activityAmount = $outflow + $budgetCategory->activity - $inflow;

        $budgetCategory->fill([
            'activity'  => $activityAmount,
            'available' => DB::raw("assigned + $activityAmount")
        ]);
        $budgetCategory->save();

        return $budgetCategory;
    }
}
