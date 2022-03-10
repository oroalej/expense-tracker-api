<?php

namespace App\Models\Traits;

use App\Models\Scopes\UserAuthenticated;

trait UseAuthenticateRestriction
{
	protected static function booted(): void
	{
		parent::booted();

		static::addGlobalScope(new UserAuthenticated());
	}
}
