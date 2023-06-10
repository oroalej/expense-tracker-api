<?php

namespace App\Models\Traits;

use Vinkla\Hashids\Facades\Hashids;

/**
 * @property string $uuid
 */
trait UseHashIds
{
    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKey(): string
    {
        return Hashids::encode($this->getKey());
    }
}
