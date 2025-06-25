<?php

namespace App\Enums;

use App\Models\Enums\MemberRole;

enum StorageTypeEnum: string
{
    case LOCAL = "Local";
    case GOOGLE_CLOUD = "Google Cloud";
}