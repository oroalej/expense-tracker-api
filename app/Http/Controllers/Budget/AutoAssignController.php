<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Resources\AutoAssignResource;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Vinkla\Hashids\Facades\Hashids;

class AutoAssignController extends Controller
{
    /**
     * @param  Budget  $budget
     * @return JsonResponse
     */
    public function index(Budget $budget): JsonResponse
    {
        $autoAssignList = BudgetCategory::getAutoAssignListByBudget($budget);

        return $this->apiResponse([
            'data' => AutoAssignResource::collection($autoAssignList)
        ]);
    }

    /**
     * @param  Budget  $budget
     * @param  Category  $category
     * @return JsonResponse
     */
    public function show(Budget $budget, Category $category): JsonResponse
    {
        $lastMonthBudgetCategory = BudgetCategory::getLastMonthByBudgetAndCategory(
            $budget,
            $category
        );

        $averageBudget = BudgetCategory::getAverageByBudgetAndCategory(
            $budget,
            $category
        );

        return $this->apiResponse([
            'data' => [
                'id'                  => Hashids::encode($category->id),
                'assigned_last_month' => $lastMonthBudgetCategory->assigned ?? 0,
                'spent_last_month'    => abs($lastMonthBudgetCategory->activity) ?? 0,
                'average_assigned'    => $averageBudget->assigned ?? 0,
                'average_spent'       => $averageBudget->activity ?? 0
            ]
        ]);
    }
}
