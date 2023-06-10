<?php

namespace App\Models;

use App\Models\Traits\UseHashIds;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $hashid
 * @property int $category_group_id
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property int $order
 * @property bool $is_hidden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static CategoryFactory factory()
 */
class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UseHashIds;

    protected $fillable = [
        'name',
        'notes',
        'order',
        'is_hidden',
    ];

    protected $touches = ['ledger'];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleted(static function (Category $category) {
            $category->budgetCategories()->delete();
        });
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }

    public function categoryGroup(): BelongsTo
    {
        return $this->belongsTo(CategoryGroup::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
