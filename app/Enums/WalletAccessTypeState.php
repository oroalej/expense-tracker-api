<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum WalletAccessTypeState: int
{
	use EnumHelpers;

	case Owner = 1;
	case Edit  = 2;
	case view  = 3;
}
