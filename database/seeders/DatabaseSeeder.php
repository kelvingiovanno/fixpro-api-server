<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Enums\UserRole;
use App\Models\Enums\ResponseLevelType;
use App\Models\Enums\TicketStatusType;
use App\Models\Enums\TicketIssueType;

use App\Enums\UserRoleEnum;
use App\Enums\IssueTypeEnum;
use App\Enums\TikectStatusEnum;
use App\Enums\ResponLevelEnum;

use App\Models\User;
use App\Models\Ticket;
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
        
        Location::factory()->count(10)->create();

        User::factory(10)->create()->each(function ($user) {
            Ticket::factory(rand(3, 5))->create([
                'user_id' => $user->id,
                'location_id' => Location::inRandomOrder()->first()->id,
            ]);
        });
        
    }
}
