<?php

namespace App\Enums;

enum UserRoleEnum: int
{
    case MEMBER = 1;
    case CREW = 2;
    case MANAGEMENT = 3;
    
    public function label(): string
    {
        return match($this) {
            self::MEMBER => 'Member',
            self::CREW => 'Crew',
            self::MANAGEMENT => 'Management',
        };
    }
}