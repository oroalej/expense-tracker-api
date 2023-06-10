<?php

namespace App\Enums;

enum ApiStatusState: int
{
    case Success = 1;
    case Created = 2;
    case Updated = 3;
    case Deleted = 4;
    case Error = 5;
    case NoContent = 6;
}
