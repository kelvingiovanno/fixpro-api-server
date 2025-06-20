<?php

namespace App\Enums;

use App\Models\Enums\TicketIssueType;

enum IssueTypeEnum : string
{
    case ENGINEERING = "Engineering";
    case HOUSEKEEPING = "Housekeeping";
    case HSE = "HSE";
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