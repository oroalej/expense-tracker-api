<?php

namespace App\Models\Traits;

use App\Models\Scopes\UserAuthenticated;

trait UseAuthenticateRestriction
{
    protected static function booted(): void
    {
        static::addGlobalScope(new UserAuthenticated());
    }
}
