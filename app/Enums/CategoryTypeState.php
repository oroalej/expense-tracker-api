<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum CategoryTypeState: int
{
    use EnumHelpers;

    case Income = 1;
    case Expense = 2;
    case Debt = 3;
}
