<?php

namespace App\Http\Resources;

use Eloquent;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;

/**
 * @mixin Eloquent
 */
class JsonResource extends BaseJsonResource
{
    protected function whenColumnLoaded($column, $default = null)
    {
        $arguments = func_num_args() === 1 ? [$this->resource->{$column}] : [$this->resource->{$column}, $default];

        return $this->when(isset($this->resource->getAttributes()[$column]), ...$arguments);
    }
}
