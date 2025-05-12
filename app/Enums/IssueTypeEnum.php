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
        $record = TicketIssueType::where('name', $this->value)->first();
        return $record?->id;
    }

    public static function idFromName(string $name): ?string
    {
        return TicketIssueType::where('name', $name)->first()?->id;
    }   
}