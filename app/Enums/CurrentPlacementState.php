<?php

namespace App\Enums;

enum CurrentPlacementState: int
{
    case Beginning = 1;
    case End = 2;
    case Hidden = 3;
}
