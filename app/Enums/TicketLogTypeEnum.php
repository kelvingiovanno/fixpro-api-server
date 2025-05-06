<?php

namespace App\Enums;

use App\Models\Enums\TicketLogType;

enum TicketLogTypeEnum : string
{
    case ASSESSMENT = "Assessment";
    case WORK_PROGRESS = "Work Progress";
    case WORK_EVALUATION_REQUEST = "Work Evaluation Request";
    case WORK_EVALUATION = "Work Evaluation";
    case ACTIVITY = "Activity";

    public function id(): ?string
    {
        $record = TicketLogType::where('label', $this->value)->first();
        return $record?->id;
    }

    public static function idFromLabel(string $label): ?string
    {
        return TicketLogType::where('label', $label)->first()?->id;
    }
}