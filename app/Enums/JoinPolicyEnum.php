<?php

namespace App\Enums;

enum JoinPolicyEnum: string
{
    case OPEN = "OPEN";
    case APPROVAL_NEEDED = "APROVAL_NEEDED";
    case CLOSED = "CLOSED";
}