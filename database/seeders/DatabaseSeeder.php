<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Enums\IssueTypeEnum;
use App\Enums\MemberRoleEnum;
use App\Enums\ApplicantStatusEnum;
use App\Enums\MemberCapabilityEnum;
use App\Enums\TicketStatusEnum;
use App\Enums\TicketResponseTypeEnum;
use App\Enums\TicketLogTypeEnum;

use App\Models\Enums\MemberRole;
use App\Models\Enums\ApplicantStatus;
use App\Models\Enums\MemberCapability;
use App\Models\Enums\TicketResponseType;
use App\Models\Enums\TicketStatusType;
use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketLogType;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (MemberRoleEnum::cases() as $case) {
            MemberRole::create(['name' => $case->value]);
        }

        foreach (ApplicantStatusEnum::cases() as $case) {
            ApplicantStatus::create(['name' => $case->value]);
        }

        foreach (MemberCapabilityEnum::cases() as $case) {
            MemberCapability::create(['name' => $case->value]);
        }
        
        foreach (TicketResponseTypeEnum::cases() as $case) {
            TicketResponseType::create([
                'name' => $case->value,
                'sla_modifier' => $case->slaModifier(),
            ]);
        }

        foreach (TicketStatusEnum::cases() as $case) {
            TicketStatusType::create(['name' => $case->value]);
        }

        foreach (IssueTypeEnum::cases() as $case) {
            TicketIssueType::create(['name' => $case->value]);
        }

        foreach (TicketLogTypeEnum::cases() as $case) {
            TicketLogType::create(['name' => $case->value]);
        }
    }

}
