<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum CategoryTypeState: int
{
	use EnumHelpers;

	case Income = 9;
	case Expense = 10;
	case Debt = 11;
}
