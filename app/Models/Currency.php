<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $abbr
 * @property string $code
 * @property string $locale
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'abbr', 'code', 'locale', 'name',
    ];

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class);
    }
}
