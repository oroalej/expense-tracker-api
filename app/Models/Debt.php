<?php

namespace App\Models;

use Database\Factories\DebtFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
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
 * @method static DebtFactory factory()
 */
class Debt extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        '$name',
        'notes',
        'current_balance',
        'interest_rate',
        'min_payment_amount',
    ];

    protected $dates = ['closed_at'];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function paymentInterval(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'payment_interval_id');
    }
}
