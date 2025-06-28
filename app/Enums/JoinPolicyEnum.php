<?php

namespace App\Enums;

enum JoinPolicyEnum: string
{
    case OPEN = "OPEN";
    case APPROVAL_NEEDED = "APPROVAL_NEEDED";
    case CLOSED = "CLOSED";
}