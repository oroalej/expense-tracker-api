<?php

namespace App\Enums;

enum AccountGroupTypeState: int
{
    case Budget = 101;
    case Debt = 102;
    case Tracking = 103;
}
