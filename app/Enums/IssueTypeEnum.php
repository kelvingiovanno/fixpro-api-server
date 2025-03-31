<?php

namespace App\Enums;

enum IssueTypeEnum : int
{
    case PLUMBING = 1;
    case HOUSEKEEPING = 2;
    case SOCIAL = 3;
    case FACILITY = 4;
    case ENGINEERING = 5;
    case SECURITY = 6;

    public function label() : string
    {
        return match($this) {
            self::PLUMBING => 'Plumbing',
            self::HOUSEKEEPING => 'Housekeeping',
            self::SOCIAL => 'Social',
            self::FACILITY => 'Facility',
            self::ENGINEERING => 'Engineering',
            self::SECURITY => 'Security',
        };
    }
}