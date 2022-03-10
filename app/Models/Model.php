<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as LaravelModel;

/**
 * App\Models\Model
 *
 * @property int $id
 * @method static Builder newModelQuery()
 * @method static Builder newQuery()
 * @method static Builder query()
 * @mixin Eloquent
 */
class Model extends LaravelModel
{
}
