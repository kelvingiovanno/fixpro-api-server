<?php

namespace App\Enums;

use App\Models\Enums\ResponseLevelType;

enum ResponLevelEnum : string
{
    case EMERGENCY = "Emergency";
    case URGENT = "Urgent";
    case NORMAL = "Normal";

    public function id(): ?string
    {
        $record = ResponseLevelType::where('label', $this->value)->first();
        return $record?->id;
    }

    public static function idFromLabel(string $label): ?string
    {
        return ResponseLevelType::where('label', $label)->first()?->id;
    }
}