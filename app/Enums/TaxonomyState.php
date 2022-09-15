<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum TaxonomyState: int
{
    use EnumHelpers;

    case AccountGroupTypes = 1;
    case CategoryTypes = 2;
    case Rights = 3;
    case Roles = 4;
    case DateFormats = 5;
    case CurrencyPlacements = 6;
    case NumberFormats = 7;
}
