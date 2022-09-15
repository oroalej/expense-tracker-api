<?php

namespace App\Models;

use App\Models\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountType extends Model
{
    use HasFactory;
    use UseUuid;

    protected $fillable = [
        'name', 'description',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function accountGroupType(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'group_type_id');
    }
}
