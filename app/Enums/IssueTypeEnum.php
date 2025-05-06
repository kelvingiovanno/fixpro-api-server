<?php

namespace App\Enums;

use App\Models\Enums\TicketIssueType;

enum IssueTypeEnum : string
{
    case PLUMBING = "Plumbing";
    case HOUSEKEEPING = "Housekeeping";
    case SOCIAL = "Social";
    case FACILITY = "Facility";
    case ENGINEERING = "Engineering";
    case SECURITY = "Security";
    
    public function id(): ?string
    {
        $record = TicketIssueType::where('label', $this->value)->first();
        return $record?->id;
    }

    public static function idFromLabel(string $label): ?string
    {
        return TicketIssueType::where('label', $label)->first()?->id;
    }   
}