<?php

namespace App\Enums;

use App\Models\Enums\TicketStatusType;

enum TicketStatusEnum: string
{
    case OPEN = "Open";
    case IN_ASSESSMENT = "In Assessment";
    case ON_PROGRESS = "On Progress";
    case WORK_EVALUATION = "Work Evaluation";
    case QUIALITY_CONTROL = "Quality Control";
    case REPOTER_EVALUATION = "Repoter Evaluation";
    case CLOSED = "Closed";
    case CANCELLED = "Cancelled";
    case REJECTED = "Rejected";

    public function id(): ?string
    {
        $record = TicketStatusType::where('name', $this->value)->first();
        return $record?->id;
    }

    public static function idFromName(string $name): ?string
    {
        return TicketStatusType::where('name', $name)->first()?->id;
    }
}
