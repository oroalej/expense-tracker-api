<?php

namespace App\Models;

use App\Contracts\TaggableInterface;
use App\Models\Traits\UseAuthenticateRestriction;
use App\Models\Traits\UseUuid;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;

/**
 * @property int         $id
 * @property int         $user_id
 * @property int         $related_id
 * @property int         $wallet_id
 * @property int         $category_id
 * @property string      $amount
 * @property string      $remarks
 * @property Carbon      $transaction_date
 * @property Category    $category
 * @property Wallet      $wallet
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static TransactionFactory factory()
 */
class Transaction extends Model implements TaggableInterface
{
	use HasFactory, SoftDeletes, UseUuid, UseAuthenticateRestriction;

	protected $fillable = ['amount', 'remarks', 'transaction_date', 'uuid'];

	protected $dates = ['transaction_date'];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function category(): BelongsTo
	{
		return $this->belongsTo(Category::class);
	}

	public function wallet(): BelongsTo
	{
		return $this->belongsTo(Wallet::class);
	}

	public function tags(): BelongsToMany
	{
		return $this->belongsToMany(Tag::class);
	}

	public function parent(): BelongsTo
	{
		return $this->belongsTo(__CLASS__, 'related_id');
	}

	public function children(): HasMany
	{
		return $this->hasMany(__CLASS__, 'related_id');
	}
}
