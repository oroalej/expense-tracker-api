<?php

namespace App\Enums;

enum DateFormatState: string
{
    case MMDDYYYY_Slash = "MM/DD/YYYY";
    case YYYYMMDD_Slash = "YYYY/MM/DD";
    case YYYYMMDD_Dash = "YYYY-MM-DD";

    case MMDDYYYY_Dash = "MM-DD-YYYY";
}
