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

    public static function id(string $label): ?int
    {
        foreach (self::cases() as $case) {
            if (strcasecmp($case->label(), $label) === 0) {
                return $case->value;
            }
        }

        return null; 
    }
}