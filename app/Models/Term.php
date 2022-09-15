<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $name
 */
class Term extends Model
{
    use HasFactory;
    use UseUuid;

    protected $fillable = [
        'name',
    ];

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
