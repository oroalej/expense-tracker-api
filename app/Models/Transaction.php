<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $account_id
 * @property int $category_id
 * @property int $transfer_id
 * @property int $amount
 * @property string $remarks
 * @property Carbon $transaction_date
 * @property bool $is_approved
 * @property bool $is_cleared
 * @property Category $category
 * @property Account $account
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $cleared_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Transaction defaultSelect()
 * @method static Builder|Transaction basicSelect()
 * @method static Builder| \Illuminate\Database\Query\Builder|Transaction filterByLedgerTransactionDateAndOptionalCategoryId(int $ledgerId, int $month, int $year, int $categoryId = null)
 * @method static Builder| \Illuminate\Database\Query\Builder|Transaction filterByAccountOrCategory(int $accountId = null, int $categoryId = null)
 * @method static TransactionFactory factory()
 */
class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'remarks',
        'amount',
        'is_approved',
        'is_cleared',
        'is_excluded',
        'transaction_date',
        'approved_at',
        'rejected_at',
        'cleared_at'
    ];

    protected $dates = ['transaction_date', 'approved_at', 'rejected_at', 'cleared_at'];

    protected $touches = ['ledger'];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_cleared'  => 'boolean',
        'is_excluded' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_id');
    }

    public function scopeDefaultSelect(Builder $builder)
    {
        $builder->select([
            'id',
            'account_id',
            'category_id',
            'ledger_id',
            'is_approved',
            'is_cleared',
            'remarks',
            'outflow',
            'inflow',
            'transaction_date'
        ]);
    }

    public function scopeBasicSelect(Builder $builder)
    {
        $builder->select([
            'id',
            'account_id',
            'category_id',
            'transaction_date',
            'remarks',
            'inflow',
            'outflow'
        ]);
    }

    public function scopeFilterByLedgerTransactionDateAndOptionalCategoryId(
        Builder $builder,
        int $ledgerId,
        int $month,
        int $year,
        ?int $categoryId = null
    ) {
        $builder->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->where('ledger_id', $ledgerId)
            ->when($categoryId, function (Builder $builder) use ($categoryId) {
                $builder->where('category_id', $categoryId);
            });
    }

    public function scopeFilterByAccountOrCategory(
        Builder $builder,
        int $accountId = null,
        int $categoryId = null,
    ) {
        $builder->when($accountId, static function (Builder $builder) use ($accountId) {
            $builder->where('account_id', (int) $accountId);
        })
            ->when($categoryId, static function (Builder $builder) use ($categoryId) {
                $builder->where('category_id', (int) $categoryId);
            });
    }

    /**
     * @param  int|null  $accountId
     * @param  int|null  $categoryId
     * @return array
     */
    public static function getBalanceSummary(int $accountId = null, int $categoryId = null): array
    {
        $summary = Transaction::selectRaw("
                COALESCE(SUM(inflow) - SUM(outflow), 0) as cleared_balance,
                COALESCE(SUM(inflow) - SUM(outflow), 0) as uncleared_balance
            ")
            ->filterByAccountOrCategory($accountId, $categoryId)
            ->where('is_cleared', false)
            ->get();

        $workingBalance = (int) $summary->value('cleared_balance') + $summary->value('uncleared_balance');

        return [
            'uncleared_balance' => $summary->value('uncleared_balance'),
            'cleared_balance'   => $summary->value('cleared_balance'),
            'working_balance'   => $workingBalance,
        ];
    }

    /**
     * @param  Carbon  $transactionDate
     * @param  int  $ledgerId
     * @param  int  $categoryId
     * @return int
     */
    public static function getTotalActivityByDateCategoryIdAndLedgerId(
        Carbon $transactionDate,
        int $ledgerId,
        int $categoryId
    ): int {
        return Transaction::selectRaw("COALESCE((SUM(inflow) - SUM(outflow)), 0) AS activity")
            ->filterByLedgerTransactionDateAndOptionalCategoryId(
                ledgerId: $ledgerId,
                month: $transactionDate->get('month'),
                year: $transactionDate->get('year'),
                categoryId: $categoryId
            )
            ->get()
            ->value('activity', 0);
    }

    /**
     * Get the transactions, filter it by month and year of the transaction_date, ledger_id, and category_id
     *
     * @param  Carbon  $transactionDate
     * @param  int  $ledgerId
     * @param  int  $categoryId
     * @return Collection
     */
    public static function getTransactionsByDateLedgerIdAndCategoryId(
        Carbon $transactionDate,
        int $ledgerId,
        int $categoryId
    ): Collection {
        return Transaction::basicSelect()
            ->filterByLedgerTransactionDateAndOptionalCategoryId(
                ledgerId: $ledgerId,
                month: $transactionDate->get('month'),
                year: $transactionDate->get('year'),
                categoryId: $categoryId
            )
            ->orderBy('category_id')
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * @param  int  $ledgerId
     * @param  int|null  $accountId
     * @param  int|null  $categoryId
     * @param  int  $perPage
     * @param  string  $sortBy
     * @param  string  $orderBy
     * @return LengthAwarePaginator
     */
    public static function getPaginatedData(
        int $ledgerId,
        int $accountId = null,
        int $categoryId = null,
        int $perPage = 15,
        string $sortBy = 'transaction_date',
        string$orderBy = 'desc'
    ): LengthAwarePaginator {
        return Transaction::defaultSelect()
            ->where('ledger_id', $ledgerId)
            ->where('account_id', $accountId)
            ->orderBy('is_cleared')
            ->orderBy('is_approved')
            ->orderBy($sortBy, $orderBy)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
