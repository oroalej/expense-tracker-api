<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelpers;

enum OperationState: string
{
    use EnumHelpers;

    case GTE = '>=';
    case GT = '>';
    case LTE = '<=';
    case LT = '<';
    case EQUAL = '=';
}
