<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


use App\Enums\UserRoleEnum;
use App\Enums\UserSpeciallityEnum;
use App\Enums\IssueTypeEnum;
use App\Enums\TikectStatusEnum;
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
use Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserRole::create(['id' => UserRoleEnum::MEMBER->value, 'label' => UserRoleEnum::MEMBER->label()]);
        UserRole::create(['id' => UserRoleEnum::CREW->value, 'label' => UserRoleEnum::CREW->label()]);
        UserRole::create(['id' => UserRoleEnum::MANAGEMENT->value,'label' => UserRoleEnum::MANAGEMENT->label()]);

        Speciality::create(['id' => UserSpeciallityEnum::PLUMBING->value, 'label' => UserSpeciallityEnum::PLUMBING->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::HOUSEKEEPING->value, 'label' => UserSpeciallityEnum::HOUSEKEEPING->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::SOCIAL->value, 'label' => UserSpeciallityEnum::SOCIAL->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::FACILITY->value, 'label' => UserSpeciallityEnum::FACILITY->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::ENGINEERING->value, 'label' => UserSpeciallityEnum::ENGINEERING->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::SECURITY->value, 'label' => UserSpeciallityEnum::SECURITY->label()]);

        ResponseLevelType::create(['id' => ResponLevelEnum::URGENT->value, 'label' => ResponLevelEnum::URGENT->label()]);
        ResponseLevelType::create(['id' => ResponLevelEnum::URGENT_EMERGENCY->value, 'label' => ResponLevelEnum::URGENT_EMERGENCY->label()]);
        ResponseLevelType::create(['id' => ResponLevelEnum::NORMAL->value, 'label' => ResponLevelEnum::NORMAL->label()]);

        TicketStatusType::create(['id' => TikectStatusEnum::OPEN->value, 'label' => TikectStatusEnum::OPEN->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::IN_ASSESSMENT->value, 'label' => TikectStatusEnum::IN_ASSESSMENT->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::ON_PROGRESS->value, 'label' => TikectStatusEnum::ON_PROGRESS->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::WORK_EVALUATION, 'label' => TikectStatusEnum::WORK_EVALUATION->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::CLOSED->value, 'label' => TikectStatusEnum::CLOSED->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::CANCELLED->value, 'label' => TikectStatusEnum::CANCELLED->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::REJECTED->value, 'label' => TikectStatusEnum::REJECTED->label()]);

        TicketIssueType::create(['id' => IssueTypeEnum::PLUMBING->value, 'label' => IssueTypeEnum::PLUMBING->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::HOUSEKEEPING->value, 'label' => IssueTypeEnum::HOUSEKEEPING->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::SOCIAL->value, 'label' => IssueTypeEnum::SOCIAL->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::FACILITY->value, 'label' => IssueTypeEnum::FACILITY->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::ENGINEERING->value, 'label' => IssueTypeEnum::ENGINEERING->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::SECURITY->value, 'label' => IssueTypeEnum::SECURITY->label()]);      

        ApplicantStatus::create(['id' => ApplicantStatusEnum::ACCEPTED->value, 'label' => ApplicantStatusEnum::ACCEPTED->label()]);
        ApplicantStatus::create(['id' => ApplicantStatusEnum::PENDING->value, 'label' => ApplicantStatusEnum::PENDING->label()]);
        ApplicantStatus::create(['id' => ApplicantStatusEnum::REJECTED->value, 'label' => ApplicantStatusEnum::REJECTED->label()]);

        TicketLogType::create(['id' => TicketLogTypeEnum::ASSESSMENT->value, 'label' => TicketLogTypeEnum::ASSESSMENT->label()]);
        TicketLogType::create(['id' => TicketLogTypeEnum::WORK_PROGRESS->value, 'label' => TicketLogTypeEnum::WORK_PROGRESS->label()]);
        TicketLogType::create(['id' => TicketLogTypeEnum::WORK_EVALUATION_REQUEST->value, 'label' => TicketLogTypeEnum::WORK_EVALUATION_REQUEST->label()]);
        TicketLogType::create(['id' => TicketLogTypeEnum::WORK_EVALUATION->value, 'label' => TicketLogTypeEnum::WORK_EVALUATION->label()]);
        TicketLogType::create(['id' => TicketLogTypeEnum::ACTIVITY->value, 'label' => TicketLogTypeEnum::ACTIVITY->label()]);
    }
}
