<?php

namespace App\Http\Controllers\BudgetCategory;

use App\DTO\BudgetCategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreBudgetCategoryRequest;
use App\Http\Requests\Update\UpdateBudgetCategoryRequest;
use App\Http\Resources\BudgetCategoryResource;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use App\Services\BudgetCategoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BudgetCategoryController extends Controller
{
    public function index(Budget $budget): JsonResponse
    {
        $transactions = Transaction::defaultSelect()
            ->filterByLedgerTransactionDateAndOptionalCategoryId(
                ledgerId: $budget->ledger_id,
                month: $budget->month,
                year: $budget->year,
            )
            ->orderBy('category_id')
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->groupBy('category_id');

        $categoryGroupSummary = CategoryGroup::selectRaw("
                category_groups.id,
                CAST(COALESCE(SUM(assigned), 0) AS INT) AS assigned,
                CAST(COALESCE(SUM(available), 0) AS INT) AS available,
                CAST(COALESCE(SUM(activity), 0) AS INT) AS activity
            ")
            ->join('categories', 'category_groups.id', '=', 'categories.category_group_id')
            ->join('budget_categories', 'categories.id', '=', 'budget_categories.category_id')
            ->where('budget_id', $budget->id)
            ->groupBy('category_groups.id')
            ->get();

        $budgetCategories = BudgetCategory::where('budget_id', $budget->id)
            ->get()
            ->each(static function ($budgetCategory) use ($transactions, $budget) {
                $budgetCategory->transactions = $transactions->get($budgetCategory->category_id) ?? [];
            });

        return $this->apiResponse([
            'data' => [
                'category_groups' => BudgetCategoryResource::collection($categoryGroupSummary),
                'categories'      => BudgetCategoryResource::collection($budgetCategories)
            ]
        ]);
    }

    public function show(Budget $budget, BudgetCategory $budgetCategory): JsonResponse
    {
        $transactions = Transaction::getTransactionsByDateLedgerIdAndCategoryId(
            transactionDate: $budget->date,
            ledgerId: $budget->ledger_id,
            categoryId: $budgetCategory->category_id
        );

        $budgetCategory->setAttribute('transactions', $transactions);

        return $this->apiResponse([
            'data' => new BudgetCategoryResource($budgetCategory)
        ]);
    }

    /**
     * @param  Budget  $budget
     * @param  StoreBudgetCategoryRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Budget $budget, StoreBudgetCategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $category = Category::find($request->get('category_id'));

            $budgetCategory = (new BudgetCategoryService())->store(
                new BudgetCategoryData(
                    category: $category,
                    budget: $budget,
                    assigned: $request->validated('amount', 0)
                )
            );

            $transactions = Transaction::getTransactionsByDateLedgerIdAndCategoryId(
                transactionDate: $budget->date,
                ledgerId: $budget->ledger_id,
                categoryId: $request->get('category_id')
            );

            $budgetCategory->setAttribute('transactions', $transactions);

            DB::commit();

            return $this->apiResponse([
                'data' => new BudgetCategoryResource($budgetCategory)
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  Budget  $budget
     * @param  BudgetCategory  $budgetCategory
     * @param  UpdateBudgetCategoryRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(
        Budget $budget,
        BudgetCategory $budgetCategory,
        UpdateBudgetCategoryRequest $request
    ): JsonResponse {
        DB::beginTransaction();

        try {
            (new BudgetCategoryService())->adjustAssigned(
                budgetCategory: $budgetCategory,
                amount: $request->validated('amount', 0)
            );

            DB::commit();

            return $this->apiResponse([
                'data' => new BudgetCategoryResource($budgetCategory)
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
