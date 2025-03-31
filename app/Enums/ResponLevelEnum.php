<?php

namespace App\Enums;

enum ResponLevelEnum : int
{
    case URGENT_EMERGENCY = 1;
    case URGENT = 2;
    case NORMAL = 3;

    public function label() : string
    {
        return match($this) {
            self::URGENT_EMERGENCY => 'Urgent, Emergency',
            self::URGENT => 'Urgent',
            self::NORMAL => 'Normal',
        };
    }
}