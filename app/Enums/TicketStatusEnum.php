<?php

namespace App\Enums;

enum TicketStatusEnum : int 
{
    case OPEN = 1;
    case IN_ASSESSMENT = 2;
    case ON_PROGRESS = 3;
    case WORK_EVALUATION = 4;
    case CLOSED = 5;
    case CANCELLED = 6;
    case REJECTED = 7;

    public function label() : string 
    {
        return match($this) {
            self::OPEN => 'Open',
            self::IN_ASSESSMENT => 'In Assessment',
            self::ON_PROGRESS => 'On Progress',
            self::WORK_EVALUATION => 'Work Evaluation',
            self::CLOSED => 'Closed',
            self::CANCELLED => 'Cancelled',
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
