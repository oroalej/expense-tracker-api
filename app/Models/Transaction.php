<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $account_id
 * @property int $category_id
 * @property float $inflow
 * @property float $outflow
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
 * @method static TransactionFactory factory()
 */
class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UseUuid;

    protected $fillable = [
        'remarks',
        'inflow',
        'outflow',
        'is_approved',
        'is_cleared',
        'is_excluded',
        'transaction_date',
        'approved_at',
        'rejected_at',
        'cleared_at',
    ];

    protected $dates = ['transaction_date', 'approved_at', 'rejected_at', 'cleared_at'];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_cleared'  => 'boolean',
        'is_excluded' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
