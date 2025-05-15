<?php

namespace App\Enums;

use App\Models\Enums\TicketLogType;

enum TicketLogTypeEnum : string
{
    case ASSESSMENT = "Assessment";
    case WORK_PROGRESS = "Work Progress";
    case WORK_EVALUATION_REQUEST = "Work Evaluation Request";
    case WORK_EVALUATION = "Work Evaluation";
    case CLIENT_EVALUATION = "Client Evaluation";
    case TIME_EXTENSION = "Time Extension";
    case ACTIVITY = "Activity";
    case INVITATION = "Invitation";

    public function id(): ?string
    {
        $record = TicketLogType::where('name', $this->value)->first();
        return $record?->id;
    }

    public static function idFromName(string $name): ?string
    {
        return TicketLogType::where('name', $name)->first()?->id;
    }
}