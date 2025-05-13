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
        MemberRole::create(['name' => MemberRoleEnum::MEMBER->value]);
        MemberRole::create(['name' => MemberRoleEnum::CREW->value]);
        MemberRole::create(['name' => MemberRoleEnum::MANAGEMENT->value]);

        ApplicantStatus::create(['name' => ApplicantStatusEnum::ACCEPTED->value]);
        ApplicantStatus::create(['name' => ApplicantStatusEnum::PENDING->value]);
        ApplicantStatus::create(['name' => ApplicantStatusEnum::REJECTED->value]);
        
        MemberCapability::create(['name' => MemberCapabilityEnum::INVITE->value]);
        MemberCapability::create(['name' => MemberCapabilityEnum::APPROVAL->value]);

        TicketResponseType::create(['name' => TicketResponseTypeEnum::URGENT->value, 'sla_modifier' => 0.8]); 
        TicketResponseType::create(['name' => TicketResponseTypeEnum::EMERGENCY->value, 'sla_modifier' => 0.6]);
        TicketResponseType::create(['name' => TicketResponseTypeEnum::NORMAL->value, 'sla_modifier' => 0.1]);

        TicketStatusType::create(['name' => TicketStatusEnum::OPEN->value]);
        TicketStatusType::create(['name' => TicketStatusEnum::IN_ASSESSMENT->value]);
        TicketStatusType::create(['name' => TicketStatusEnum::ON_PROGRESS->value]);
        TicketStatusType::create(['name' => TicketStatusEnum::WORK_EVALUATION->value]);
        TicketStatusType::create(['name' => TicketStatusEnum::CLOSED->value]);
        TicketStatusType::create(['name' => TicketStatusEnum::CANCELLED->value]);
        TicketStatusType::create(['name' => TicketStatusEnum::REJECTED->value]);

        TicketIssueType::create(['name' => IssueTypeEnum::PLUMBING->value]);
        TicketIssueType::create(['name' => IssueTypeEnum::HOUSEKEEPING->value]);
        TicketIssueType::create(['name' => IssueTypeEnum::SOCIAL->value]);
        TicketIssueType::create(['name' => IssueTypeEnum::FACILITY->value]);
        TicketIssueType::create(['name' => IssueTypeEnum::ENGINEERING->value]);
        TicketIssueType::create(['name' => IssueTypeEnum::SECURITY->value]);      

        TicketLogType::create(['name' => TicketLogTypeEnum::ASSESSMENT->value]);
        TicketLogType::create(['name' => TicketLogTypeEnum::WORK_PROGRESS->value]);
        TicketLogType::create(['name' => TicketLogTypeEnum::WORK_EVALUATION_REQUEST->value]);
        TicketLogType::create(['name' => TicketLogTypeEnum::WORK_EVALUATION->value]);
        TicketLogType::create(['name' => TicketLogTypeEnum::ACTIVITY->value]);
    }
}
