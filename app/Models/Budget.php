<?php

namespace App\Models;

use App\DTO\BudgetData;
use App\Services\BudgetService;
use Carbon\Carbon;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $ledger_id
 * @property int $month
 * @property int $year
 * @property Carbon $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static filterByMonthYearAndLedgerId(int $month, int $year, int $ledgerId)
 * @method static BudgetFactory factory()
 */
class Budget extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'month', 'year', 'date'
    ];

    protected $dates = [
        'date'
    ];

    protected $touches = ['ledger'];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }

    public function scopeFilterByMonthYearAndLedgerId(Builder $builder, int $month, int $year, int $ledgerId)
    {
        $builder->where('month', $month)
            ->where('year', $year)
            ->where('ledger_id', $ledgerId);
    }

    /**
     * @param  Carbon  $date
     * @param  Ledger  $ledger
     * @return Budget
     */
    public static function getDataByDateAndLedger(
        Carbon $date,
        Ledger $ledger
    ): Budget {
        return Budget::filterByMonthYearAndLedgerId(
            month: $date->get('month'),
            year: $date->get('year'),
            ledgerId: $ledger->id
        )
            ->firstOr(function () use ($date, $ledger) {
                return (new BudgetService())->store(
                    new BudgetData(
                        month: $date->get('month'),
                        year: $date->get('year'),
                        ledger: $ledger
                    )
                );
            });
    }
}
