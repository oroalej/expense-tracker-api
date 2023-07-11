<?php

namespace App\Models;

use App\Models\Traits\UseHashIds;
use Carbon\Carbon;
use Database\Factories\LedgerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $number_format
 * @property int $currency_id
 * @property string $name
 * @property bool $is_archived
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static LedgerFactory factory()
 */
class Ledger extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UseHashIds;

    protected $fillable = [
        'name',
        'is_archived',
        'date_format'
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleted(static function (Ledger $ledger) {
            $ledger->transactions()->delete();
            $ledger->accounts()->delete();
            $ledger->categories()->delete();
            $ledger->categoryGroups()->delete();
            $ledger->budgets()->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
