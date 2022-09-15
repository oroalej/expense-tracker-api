<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 */
class BudgetCategory extends Model
{
    use HasFactory;
    use UseUuid;

    protected $fillable = [
        'assigned', 'available', 'activity',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
