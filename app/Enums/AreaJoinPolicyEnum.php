<?php

namespace App\Enums;

enum AreaJoinPolicyEnum: string
{
    case OPEN = "OPEN";
    case APROVAL_NEEDED = "APROVAL_NEEDED";
    case CLOSED = "CLOSED";
}