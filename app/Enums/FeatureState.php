<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum FeatureState: int {
	use EnumHelpers;

	case Transaction = 12;
	case Budget 		 = 13;
	case Debt 			 = 14;
	case Goal 			 = 15;
}
