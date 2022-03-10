<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum WalletTypeState: int
{
	use EnumHelpers;

	case Joint    = 1;
	case Savings  = 2;
	case Cash     = 3;
	case Payroll  = 4;
	case Checking = 5;
}
