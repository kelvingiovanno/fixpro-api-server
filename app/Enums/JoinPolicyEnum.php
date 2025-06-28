<?php

namespace App\Enums;

enum JoinPolicyEnum: string
{
    case OPEN = "OPEN";
    case APPROVAL_NEEDED = "APROVAL-NEEDED";
    case CLOSED = "CLOSED";
}