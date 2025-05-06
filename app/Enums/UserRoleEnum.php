<?php

namespace App\Enums;

use App\Models\Enums\UserRole;

enum UserRoleEnum: string
{
    case MEMBER = "Member";
    case CREW = "Crew";
    case MANAGEMENT = "Management";

    public function id(): ?string
    {
        $record = UserRole::where('label', $this->value)->first();
        return $record?->id;
    }

    public static function idFromLabel(string $label): ?string
    {
        return UserRole::where('label', $label)->first()?->id;
    }
}