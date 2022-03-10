<?php

namespace App\Models;

use App\Enums\CategoryTypeState;
use App\Models\Traits\UseAuthenticateRestriction;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int               $id
 * @property int               $related_id
 * @property int               $user_id
 * @property string            $name
 * @property string            $description
 * @property CategoryTypeState $category_type
 *
 * @property Carbon|null       $created_at
 * @property Carbon|null       $updated_at
 * @property Carbon|null       $deleted_at
 * @method static CategoryFactory factory()
 */
class Category extends Model
{
	use HasFactory, SoftDeletes, UseAuthenticateRestriction;

	protected $fillable = ['name', 'description', 'category_type'];

	protected $casts = [
		'category_type' => CategoryTypeState::class,
	];

	public function parent(): BelongsTo
	{
		return $this->belongsTo(__CLASS__, 'parent_id');
	}

	public function children(): HasMany
	{
		return $this->hasMany(__CLASS__, 'parent_id');
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function transactions(): HasMany
	{
		return $this->hasMany(Transaction::class);
	}
}
