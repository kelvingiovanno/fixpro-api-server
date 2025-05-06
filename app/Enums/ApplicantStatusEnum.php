<?php

namespace App\Enums;

use App\Models\Enums\ApplicantStatus;

enum ApplicantStatusEnum : string
{
    case ACCEPTED = "Accepted";
    case PENDING = "Pending";
    case REJECTED = "Rejected";

    public function id(): ?string
    {
        $record = ApplicantStatus::where('label', $this->value)->first();
        return $record?->id;
    }

    public static function idFromLabel(string $label): ?string
    {
        return ApplicantStatus::where('label', $label)->first()?->id;
    }
}