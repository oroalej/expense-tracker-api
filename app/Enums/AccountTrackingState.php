<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum AccountTrackingState: int
{
    use EnumHelpers;

    case Crypto = 1021;
    case Stocks = 1022;
    case Bonds = 1023;
}
