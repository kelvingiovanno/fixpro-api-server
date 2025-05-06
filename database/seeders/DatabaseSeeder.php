<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


use App\Enums\UserRoleEnum;
use App\Enums\UserSpeciallityEnum;
use App\Enums\IssueTypeEnum;
use App\Enums\TicketStatusEnum;
use App\Enums\ResponLevelEnum;
use App\Enums\ApplicantStatusEnum;
use App\Enums\TicketLogTypeEnum;

use App\Models\Enums\UserRole;
use App\Models\Enums\Speciality;
use App\Models\Enums\ResponseLevelType;
use App\Models\Enums\TicketStatusType;
use App\Models\Enums\TicketIssueType;
use App\Models\Enums\ApplicantStatus;
use App\Models\Enums\TicketLogType;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserRole::create(['label' => UserRoleEnum::MEMBER->value]);
        UserRole::create([ 'label' => UserRoleEnum::CREW->value]);
        UserRole::create(['label' => UserRoleEnum::MANAGEMENT->value]);

        Speciality::create(['label' => UserSpeciallityEnum::PLUMBING->value]);
        Speciality::create([ 'label' => UserSpeciallityEnum::HOUSEKEEPING->value]);
        Speciality::create([ 'label' => UserSpeciallityEnum::SOCIAL->value]);
        Speciality::create(['label' => UserSpeciallityEnum::FACILITY->value]);
        Speciality::create(['label' => UserSpeciallityEnum::ENGINEERING->value]);
        Speciality::create(['label' => UserSpeciallityEnum::SECURITY->value]);

        ResponseLevelType::create(['label' => ResponLevelEnum::URGENT->value, 'sla_modifier' => 0.8]); 
        ResponseLevelType::create(['label' => ResponLevelEnum::EMERGENCY->value, 'sla_modifier' => 0.6]);
        ResponseLevelType::create(['label' => ResponLevelEnum::NORMAL->value, 'sla_modifier' => 0.1]);

        TicketStatusType::create(['label' => TicketStatusEnum::OPEN->value]);
        TicketStatusType::create(['label' => TicketStatusEnum::IN_ASSESSMENT->value]);
        TicketStatusType::create(['label' => TicketStatusEnum::ON_PROGRESS->value]);
        TicketStatusType::create(['label' => TicketStatusEnum::WORK_EVALUATION->value]);
        TicketStatusType::create(['label' => TicketStatusEnum::CLOSED->value]);
        TicketStatusType::create(['label' => TicketStatusEnum::CANCELLED->value]);
        TicketStatusType::create(['label' => TicketStatusEnum::REJECTED->value]);

        TicketIssueType::create(['label' => IssueTypeEnum::PLUMBING->value]);
        TicketIssueType::create(['label' => IssueTypeEnum::HOUSEKEEPING->value]);
        TicketIssueType::create(['label' => IssueTypeEnum::SOCIAL->value]);
        TicketIssueType::create(['label' => IssueTypeEnum::FACILITY->value]);
        TicketIssueType::create(['label' => IssueTypeEnum::ENGINEERING->value]);
        TicketIssueType::create(['label' => IssueTypeEnum::SECURITY->value]);      

        ApplicantStatus::create(['label' => ApplicantStatusEnum::ACCEPTED->value]);
        ApplicantStatus::create(['label' => ApplicantStatusEnum::PENDING->value]);
        ApplicantStatus::create(['label' => ApplicantStatusEnum::REJECTED->value]);

        TicketLogType::create(['label' => TicketLogTypeEnum::ASSESSMENT->value]);
        TicketLogType::create(['label' => TicketLogTypeEnum::WORK_PROGRESS->value]);
        TicketLogType::create(['label' => TicketLogTypeEnum::WORK_EVALUATION_REQUEST->value]);
        TicketLogType::create(['label' => TicketLogTypeEnum::WORK_EVALUATION->value]);
        TicketLogType::create(['label' => TicketLogTypeEnum::ACTIVITY->value]);
    }
}
