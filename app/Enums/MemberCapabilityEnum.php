<?php

namespace App\Enums;

use App\Models\Enums\MemberCapability;

enum MemberCapabilityEnum : string
{
    case INVITE = "InvitePeopleToTicket";
    case APPROVAL = "IssueSupervisorApproval";
    
    public function id(): ?string
    {
        $record = MemberCapability::where('name', $this->value)->first();
        return $record?->id;
    }

    public static function idFromName(string $name): ?string
    {
        return MemberCapability::where('name', $name)->first()?->id;
    }   
}