<?php

namespace App\Enums;

use App\Models\Enums\MemberRole;

enum MemberRoleEnum: string
{
    case MEMBER = "Member";
    case CREW = "Crew";
    case MANAGEMENT = "Management";

    public function id(): ?string
    {
        $record = MemberRole::where('name', $this->value)->first();
        return $record?->id;
    }

    public static function idFromName(string $name): ?string
    {
        return MemberRole::where('name', $name)->first()?->id;
    }
}