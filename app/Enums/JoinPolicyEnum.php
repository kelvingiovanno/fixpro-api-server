<?php

namespace App\Enums;

enum JoinPolicyEnum: string
{
    case OPEN = "OPEN";
    case APPROVAL_NEEDED = "APPROVAL-NEEDED";
    case CLOSED = "CLOSED";
}