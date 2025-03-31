<?php

namespace App\Enums;

enum UserStatusEnum: int
{
    case PENDING = 1;
    case ACCEPTED = 2;
    case REJECTED = 3;

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
        };
    }
}