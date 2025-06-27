<?php

namespace App\Enums;

use App\Models\Enums\TicketLogType;

enum TicketLogTypeEnum : string
{
    case ASSESSMENT = "Assessment";
    case INVITATION = "Invitation";
    case WORK_PROGRESS = "Work Progress";
    case ACTIVITY = "Activity";
    case WORK_EVALUATION = "Work Evaluation";
    case TIME_EXTENSION = "Time Extension";
    case WORK_EVALUATION_REQUEST = "Work Evaluation Request";
    case OWNER_EVALUATION_REQUEST = "Owner Evaluation Request";
    case REJECTION = "Rejection";
    case APPROVAL = "Approval";
    case FORCE_CLOSURE = "Force Closure";
    case AUTO_CLOSE = 'Auto Close';

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