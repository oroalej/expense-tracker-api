<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum CategoryTypeState: int
{
    use EnumHelpers;

    case Category = 21;
    case Group = 22;
}
