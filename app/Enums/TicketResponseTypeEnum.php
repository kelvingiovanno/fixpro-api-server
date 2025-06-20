<?php

namespace App\Enums;

use App\Models\Enums\TicketResponseType;

enum TicketResponseTypeEnum : string
{
    case EMERGENCY = "Emergency";
    case URGENT = "Urgent";
    case NORMAL = "Normal";

    public function slaModifier(): float
    {
        return match($this) {
            self::EMERGENCY => 0.6,
            self::URGENT => 0.8,
            self::NORMAL => 0.1,
        };
    }

    public function id(): ?string
    {
        $record = TicketResponseType::where('name', $this->value)->first();
        return $record?->id;
    }

    public static function idFromName(string $name): ?string
    {
        return TicketResponseType::where('name', $name)->first()?->id;
    }
}