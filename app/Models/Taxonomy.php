<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taxonomy extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public $timestamps = false;

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }
}
