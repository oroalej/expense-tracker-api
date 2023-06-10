<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Index\IndexBudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  IndexBudgetRequest  $request
     * @return JsonResponse
     */
    public function index(IndexBudgetRequest $request): JsonResponse
    {
        $budgets = Budget::select(['id', 'year', 'month'])
            ->where('ledger_id', $request->ledger->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(5)
            ->get();

        return $this->apiResponse([
            'data' => BudgetResource::collection($budgets)
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Budget  $budget
     * @return JsonResponse
     */
    public function show(Budget $budget): JsonResponse
    {
        return $this->apiResponse([
            'data' => new BudgetResource($budget),
        ]);
    }
}
