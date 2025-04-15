<?php

namespace App\Enums;

enum UserSpeciallityEnum : int 
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