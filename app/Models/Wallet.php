<?php

namespace App\Models;

use App\Enums\WalletTypeState;
use App\Models\Traits\UseAuthenticateRestriction;
use App\Models\Traits\UseUuid;
use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property double $current_balance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property WalletTypeState $wallet_type
 *
 * @property-read UserWallet|null $access
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static WalletFactory factory()
 */
class Wallet extends Model
{
    use HasFactory, SoftDeletes, UseUuid, UseAuthenticateRestriction;

    /**
     * Wallet Types
     */
    public const WT_CASH_ID = 6;

    protected $fillable = [
        'name',
        'description',
        'current_balance',
        'wallet_type',
    ];

    protected $attributes = [
        'current_balance' => 0,
    ];

    protected $casts = [
        'current_balance' => 'double',
        'type' => WalletTypeState::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(UserWallet::class)
            ->withPivot('start_date', 'end_date', 'access_type')
            ->as('permissions');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
