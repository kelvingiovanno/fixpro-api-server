<?php

namespace App\Enums;

use App\Models\Enums\ApplicantStatus;

enum ApplicantStatusEnum: string
{
    case PENDING = "Pending";
    case ACCEPTED = "Accepted";
    case REJECTED = "Rejected";

    public function id(): ?string
    {
        $record = ApplicantStatus::where('name', $this->value)->first();
        return $record?->id;
    }

    public static function idFromName(string $name): ?string
    {
        return ApplicantStatus::where('name', $name)->first()?->id;
    }
}