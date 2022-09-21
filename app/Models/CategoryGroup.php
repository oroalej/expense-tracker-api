<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Database\Factories\CategoryGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property int $order
 * @property bool $is_hidden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static CategoryGroupFactory factory()
 */
class CategoryGroup extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UseUuid;

    protected $fillable = [
        'name',
        'notes',
        'is_hidden',
        'order'
    ];

    protected $casts = [
        'is_hidden' => 'boolean'
    ];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
