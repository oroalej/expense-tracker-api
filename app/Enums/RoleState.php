<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum RoleState: int
{
    use EnumHelpers;

    case Admin = 1;
    case Free = 2;
    case Subscriber = 3;
}
