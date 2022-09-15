<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $ledger_id
 * @property string $notes
 * @property int $month
 * @property int $year
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static BudgetFactory factory()
 */
class Budget extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UseUuid;

    protected $fillable = [
        'notes', 'month', 'year',
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }
}
