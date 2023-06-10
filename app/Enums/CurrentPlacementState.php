<?php

namespace App\Enums;

enum CurrentPlacementState: int
{
    case Hidden = 0;
    case Beginning = 1;
    case End = 2;
}
