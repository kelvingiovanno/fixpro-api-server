<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


use App\Enums\UserRoleEnum;
use App\Enums\UserSpeciallityEnum;
use App\Enums\IssueTypeEnum;
use App\Enums\TikectStatusEnum;
use App\Enums\ResponLevelEnum;

use App\Models\Enums\UserRole;
use App\Models\Enums\Speciality;
use App\Models\Enums\ResponseLevelType;
use App\Models\Enums\TicketStatusType;
use App\Models\Enums\TicketIssueType;

use App\Models\User;
use App\Models\Location;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserRole::create(['id' => UserRoleEnum::MEMBER, 'label' => UserRoleEnum::MEMBER->label()]);
        UserRole::create(['id' => UserRoleEnum::CREW, 'label' => UserRoleEnum::CREW->label()]);
        UserRole::create(['id' => UserRoleEnum::MANAGEMENT,'label' => UserRoleEnum::MANAGEMENT->label()]);

        Speciality::create(['id' => UserSpeciallityEnum::PLUMBING, 'label' => UserSpeciallityEnum::PLUMBING->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::HOUSEKEEPING, 'label' => UserSpeciallityEnum::HOUSEKEEPING->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::SOCIAL, 'label' => UserSpeciallityEnum::SOCIAL->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::FACILITY, 'label' => UserSpeciallityEnum::FACILITY->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::ENGINEERING, 'label' => UserSpeciallityEnum::ENGINEERING->label()]);
        Speciality::create(['id' => UserSpeciallityEnum::SECURITY, 'label' => UserSpeciallityEnum::SECURITY->label()]);

        ResponseLevelType::create(['id' => ResponLevelEnum::URGENT, 'label' => ResponLevelEnum::URGENT->label()]);
        ResponseLevelType::create(['id' => ResponLevelEnum::URGENT_EMERGENCY, 'label' => ResponLevelEnum::URGENT_EMERGENCY->label()]);
        ResponseLevelType::create(['id' => ResponLevelEnum::NORMAL, 'label' => ResponLevelEnum::NORMAL->label()]);

        TicketStatusType::create(['id' => TikectStatusEnum::OPEN, 'label' => TikectStatusEnum::OPEN->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::IN_ASSESSMENT, 'label' => TikectStatusEnum::IN_ASSESSMENT->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::ON_PROGRESS, 'label' => TikectStatusEnum::ON_PROGRESS->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::WORK_EVALUATION, 'label' => TikectStatusEnum::WORK_EVALUATION->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::CLOSED, 'label' => TikectStatusEnum::CLOSED->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::CANCELLED, 'label' => TikectStatusEnum::CANCELLED->label()]);
        TicketStatusType::create(['id' => TikectStatusEnum::REJECTED, 'label' => TikectStatusEnum::REJECTED->label()]);

        TicketIssueType::create(['id' => IssueTypeEnum::PLUMBING, 'label' => IssueTypeEnum::PLUMBING->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::HOUSEKEEPING, 'label' => IssueTypeEnum::HOUSEKEEPING->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::SOCIAL, 'label' => IssueTypeEnum::SOCIAL->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::FACILITY, 'label' => IssueTypeEnum::FACILITY->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::ENGINEERING, 'label' => IssueTypeEnum::ENGINEERING->label()]);
        TicketIssueType::create(['id' => IssueTypeEnum::SECURITY, 'label' => IssueTypeEnum::SECURITY->label()]);      
    }
}
