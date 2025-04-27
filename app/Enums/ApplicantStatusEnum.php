<?php

namespace App\Enums;

enum ApplicantStatusEnum : int
{
    case ACCEPTED = 1;
    case PENDING = 2;
    case REJECTED = 3;

    public function label() : string
    {
        return match($this) {
            self::ACCEPTED => 'Accepted',
            self::PENDING => 'Pending',
            self::REJECTED => 'Rejected',
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