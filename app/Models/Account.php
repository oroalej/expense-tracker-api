<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $account_type_id
 * @property int $ledger_id
 * @property string $name
 * @property bool $is_archived
 * @property float $current_balance
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property AccountType accountType
 *
 * @method static AccountFactory factory()
 */
class Account extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UseUuid;

    protected $fillable = [
        'uuid',
        'name',
        'current_balance',
        'is_archived',
        'archived_at'
    ];

    protected $attributes = [
        'current_balance' => 0,
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    protected $dates = [
        'archived_at',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }
}
