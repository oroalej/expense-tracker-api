<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum CategoryTypeState: int
{
    use EnumHelpers;

    case OTHERS = 1;
    case INCOME = 2;
    case EXPENSE = 3;
}
