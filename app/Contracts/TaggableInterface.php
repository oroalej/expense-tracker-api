<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface TaggableInterface
{
	public function tags(): BelongsToMany;
}
