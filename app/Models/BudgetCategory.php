<?php

namespace App\Models;

use App\DTO\BudgetCategoryData;
use App\Services\BudgetCategoryService;
use Database\Factories\BudgetCategoryFactory;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ledger_id
 * @property int $category_id
 * @property int $assigned
 * @property int $available
 * @property int $activity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static BudgetCategoryFactory factory()
 */
class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned', 'available', 'activity',
    ];

    public $timestamps = false;

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param  Budget  $budget
     * @param  Category  $category
     * @return BudgetCategory|null
     */
    public static function getDataByBudgetAndCategory(Budget $budget, Category $category): BudgetCategory|null
    {
        return BudgetCategory::where('budget_id', $budget->id)
            ->where('category_id', $category->id)
            ->first();
    }

    /**
     * @param  Budget  $budget
     * @param  Category  $category
     * @return BudgetCategory
     */
    public static function getOrCreateByBudgetAndCategory(Budget $budget, Category $category): BudgetCategory
    {
        $budgetCategory = BudgetCategory::getDataByBudgetAndCategory($budget, $category);

        if (is_null($budgetCategory)) {
            $budgetCategory = (new BudgetCategoryService())->store(
                new BudgetCategoryData(
                    category: $category,
                    budget: $budget,
                )
            );
        }

        return $budgetCategory;
    }

    /**
     * @param  Transaction  $transaction
     * @return BudgetCategory
     */
    public static function getByTransaction(Transaction $transaction): BudgetCategory
    {
        $transaction->loadMissing('ledger', 'category');

        $budget = Budget::getDataByDateAndLedger(
            date: $transaction->transaction_date,
            ledger: $transaction->ledger
        );

        return BudgetCategory::getOrCreateByBudgetAndCategory(
            budget: $budget,
            category: $transaction->category
        );
    }

    /**
     * @param  Budget  $budget
     * @param  Category  $category
     * @return BudgetCategory|null
     */
    public static function getLastMonthByBudgetAndCategory(Budget $budget, Category $category): null|BudgetCategory
    {
        return BudgetCategory::whereHas('budget', function (Builder $builder) use ($budget) {
            $lastMonth = $budget->date->subMonth();

            $builder->filterByMonthYearAndLedgerId(
                $lastMonth->month,
                $lastMonth->year,
                $budget->ledger_id
            );
        })
            ->where('category_id', $category->id)
            ->first();
    }

    public static function getAverageByBudgetAndCategory(Budget $budget, Category $category): BudgetCategory
    {
        return BudgetCategory::whereHas('budget', function (Builder $builder) use ($budget) {
            $builder->where('date', '<', $budget->date->format('Y-m-d'))
                ->where('ledger_id', $budget->ledger_id);
        })
            ->where('category_id', $category->id)
            ->selectRaw("
                CAST(ABS(ROUND(AVG(assigned), 0)) AS INT) AS assigned,
                CAST(ABS(ROUND(AVG(activity), 0)) AS INT) AS activity
            ")
            ->first();
    }

    /**
     * @param  Budget  $budget
     * @return Collection
     */
    public static function getAutoAssignListByBudget(Budget $budget): Collection
    {
        $lastMonthBudgetSubQuery = BudgetCategory::selectRaw("
            category_id AS id,
            assigned as assigned_last_month,
            activity as spent_last_month
        ")->whereHas('budget', function (Builder $builder) use ($budget) {
            $lastMonth = $budget->date->subMonth();

            $builder->filterByMonthYearAndLedgerId(
                $lastMonth->month,
                $lastMonth->year,
                $budget->ledger_id
            );
        });

        $averageSubQuery = BudgetCategory::selectRaw("
            category_id AS id,
            ROUND(AVG(assigned), 0) AS average_assigned,
            ROUND(AVG(activity), 0) AS average_spent
        ")->whereHas('budget', function (Builder $builder) use ($budget) {
            $builder->where('date', '<', $budget->date->format('Y-m-d'))
                ->where('ledger_id', $budget->ledger_id);
        })->groupBy('category_id');

        return Category::selectRaw("
            categories.id,
            CAST(COALESCE(ABS(average_assigned), 0) AS INT) AS average_assigned,
            CAST(COALESCE(ABS(average_spent), 0) AS INT) AS average_spent,
            COALESCE(ABS(assigned_last_month), 0) AS assigned_last_month,
            COALESCE(ABS(spent_last_month), 0) AS spent_last_month
        ")
            ->leftJoinSub($averageSubQuery, 'average_budgets', 'categories.id', '=', 'average_budgets.id')
            ->leftJoinSub($lastMonthBudgetSubQuery, 'last_month_budgets', 'categories.id', '=', 'last_month_budgets.id')
            ->get();
    }

    /**
     * @param  array  $categories
     * @return Collection<BudgetCategory>
     */
    public static function getSummaryFilteredByCategoryIds(array $categories): Collection
    {
        return BudgetCategory::select([
            'budget_categories.id',
            'category_id',
            'budget_id',
            DB::raw('CAST(COALESCE(SUM(assigned), 0) AS INT) AS assigned'),
            DB::raw('CAST(COALESCE(SUM(available), 0) AS INT) AS available'),
            DB::raw('CAST(COALESCE(SUM(activity), 0) AS INT) AS activity'),
        ])
            ->join('budgets', 'budget_categories.budget_id', '=', 'budgets.id')
            ->whereIn('category_id', $categories)
            ->groupBy('budget_id')
            ->orderBy('month')
            ->orderBy('year')
            ->get();
    }
}
