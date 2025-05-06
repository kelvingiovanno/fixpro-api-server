<?php

namespace App\Enums;

use App\Models\Enums\Speciality;

enum UserSpeciallityEnum : string
{
    case PLUMBING = "Plumbing";
    case HOUSEKEEPING = "Housekeeping";
    case SOCIAL = "Social";
    case FACILITY = "Facility";
    case ENGINEERING = "Engineering";
    case SECURITY = "Security";

    public function id(): ?string
    {
        $record = Speciality::where('label', $this->value)->first();
        return $record?->id;
    }

    public static function idFromLabel(string $label): ?string
    {
        return Speciality::where('label', $label)->first()?->id;
    }
}