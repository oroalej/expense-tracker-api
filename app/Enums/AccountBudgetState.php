<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum AccountBudgetState: int
{
    use EnumHelpers;

    case Joint = 1001;
    case Savings = 1002;
    case Cash = 1003;
    case Payroll = 1004;
    case Checking = 1005;
}
