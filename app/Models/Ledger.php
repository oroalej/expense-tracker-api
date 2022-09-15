<?php

namespace App\Models;

use App\Enums\TaxonomyState;
use App\Models\Traits\UseAuthenticateRestriction;
use App\Models\Traits\UseUuid;
use Carbon\Carbon;
use Database\Factories\LedgerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $date_format_id
 * @property int $currency_placement_id
 * @property int $number_format_id
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
    use UseUuid;
    use SoftDeletes;
    use UseAuthenticateRestriction;

    protected $fillable = [
        'name',
        'uuid',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryGroups(): HasMany
    {
        return $this->hasMany(CategoryGroup::class);
    }

    public function dateFormat(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'date_format_id')
            ->where('taxonomy_id', TaxonomyState::DateFormats->value);
    }

    public function currencyPlacement(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'currency_placement_id')
            ->where('taxonomy_id', TaxonomyState::CurrencyPlacements->value);
    }

    public function numberFormat(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'number_format_id')
            ->where('taxonomy_id', TaxonomyState::NumberFormats->value);
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
