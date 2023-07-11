<?php

namespace App\Models;

use App\Enums\CategoryTypeState;
use App\Models\Traits\UseHashIds;
use Database\Factories\CategoryFactory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $ledger_id
 * @property CategoryTypeState $category_type
 * @property string $name
 * @property string $notes
 * @property int $order
 * @property bool $is_visible
 * @property bool $is_budgetable
 * @property bool $is_reportable
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
        'category_type',
        'is_visible',
        'is_budgetable',
        'is_reportable',
        'is_editable'
    ];

    protected $touches = ['ledger'];

    protected $casts = [
        'is_visible'    => 'boolean',
        'is_budgetable' => 'boolean',
        'is_reportable' => 'boolean',
        'category_type' => CategoryTypeState::class,
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_id');
    }

    public function child(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @param  int  $categoryType
     * @param  int|null  $ledgerId
     * @param  int|null  $parentId
     * @return int
     */
    public static function getLastOrder(
        int $categoryType,
        int|null $ledgerId = null,
        int|null $parentId = null
    ): int {
        return Category::where('ledger_id', $ledgerId)
                ->when($parentId, static function (Builder $builder) use ($parentId) {
                    $builder->where('parent_id', $parentId);
                }, static function (Builder $builder) use ($categoryType) {
                    $builder->where('category_type', $categoryType);
                })
                ->count() + 1;
    }
}
