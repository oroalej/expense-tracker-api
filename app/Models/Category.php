<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $category_group_id
 * @property string $name
 * @property string $notes
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
    use UseUuid;

    protected $fillable = [
        'name',
        'notes',
        'order',
        'is_hidden'
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
    ];

    public function categoryGroup(): BelongsTo
    {
        return $this->belongsTo(CategoryGroup::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
