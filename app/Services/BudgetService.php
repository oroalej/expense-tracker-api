<?php

namespace App\Services;

use App\DTO\BudgetData;
use App\Models\Budget;
use Carbon\Carbon;

class BudgetService
{
    /**
     * @param  BudgetData  $attributes
     * @return Budget
     */
    public function store(BudgetData $attributes): Budget
    {
        $budget = new Budget($attributes->toArray());

        $budget->date = Carbon::create($attributes->year, $attributes->month)->format('Y-m-d');

        $budget->ledger()->associate($attributes->ledger);
        $budget->save();

        return $budget;
    }

    /**
     * @param  Budget  $budget
     * @param  BudgetData  $attributes
     * @return Budget
     */
    public function update(Budget $budget, BudgetData $attributes): Budget
    {
        $budget->fill($attributes->toArray());
        $budget->save();

        return $budget;
    }
}
