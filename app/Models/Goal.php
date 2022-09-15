<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property float $current_balance
 * @property float $interest_rate
 * @property float $min_payment_amount
 * @property Carbon|null $closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static GoalFactory factory()
 */
class Goal extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UseUuid;

    protected $fillable = [
        'name',
        'target_amount',
        'current_balance',
        'month',
        'year',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }
}
