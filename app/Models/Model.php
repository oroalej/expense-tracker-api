<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as LaravelModel;

/**
 * App\Models\Model
 *
 * @property int $id
 * @mixin Builder
 * @mixin Eloquent
 */
class Model extends LaravelModel
{
}
