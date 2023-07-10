<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum DefaultCategoryIDs: int
{
    use EnumHelpers;

    case TRANSFER = 1001;
    case DEBT = 1002;
    case LOAN =  1003;
}
