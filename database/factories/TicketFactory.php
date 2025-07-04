<?php

namespace Database\Factories;

use App\Enums\MemberRoleEnum;
use App\Enums\TicketLogTypeEnum;
use App\Enums\TicketStatusEnum;
use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketResponseType;

use App\Models\Member;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketDocument;
use App\Models\TicketIssue;
use App\Models\TicketLog;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::where('role_id', MemberRoleEnum::MEMBER->id())->inRandomOrder()->value('id'),
            'status_id' => TicketStatusEnum::OPEN->id(),
            'assessed_by' => Member::where('role_id', MemberRoleEnum::MANAGEMENT->id())->inRandomOrder()->value('id'),
            'evaluated_by'=> Member::where('role_id', MemberRoleEnum::MANAGEMENT->id())->inRandomOrder()->value('id'),
            'response_id' => TicketResponseType::inRandomOrder()->first()?->id,
            'stated_issue' => $this->faker->sentence(10),
            'raised_on' => now(),
        ];   
    }

    /**
     * Configure the factory's model state.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Ticket $ticket) {

            $docCount = $this->faker->numberBetween(3, 5);
            for ($i = 0; $i < $docCount; $i++) {
                TicketDocument::factory()->create([
                    'ticket_id' => $ticket->id,
                ]);
            }

            Location::factory()->create([
                'ticket_id' => $ticket->id
            ]);

            $issueCount = $this->faker->numberBetween(1, 4);
            $issueTypes = TicketIssueType::inRandomOrder()->limit($issueCount)->pluck('id');

            foreach ($issueTypes as $issueId) {
                TicketIssue::factory()->create([
                    'ticket_id' => $ticket->id,
                    'issue_id' => $issueId,
                ]);
            }

            $startTime = $ticket->raised_on;

            // ASSESSMENT
            $startTime = $startTime->copy()->addHours(rand(10, 30))->addMinutes(rand(0, 59));
            TicketLog::factory()->create([
                'ticket_id' => $ticket->id,
                'type_id' => TicketLogTypeEnum::ASSESSMENT->id(),
                'member_id' => $ticket->assessed_by,
                'recorded_on' => $startTime,
            ]);

            // INVITATION
            $startTime = $startTime->copy()->addHours(1)->addMinutes(rand(0, 59));
            TicketLog::factory()->create([
                'ticket_id' => $ticket->id,
                'type_id' => TicketLogTypeEnum::INVITATION->id(),
                'member_id' => Member::where('role_id', MemberRoleEnum::CREW->id())->inRandomOrder()->value('id'),
                'recorded_on' => $startTime,
            ]);

            // WORK_PROGRESS and TIME_EXTENSION
            for ($i = 0; $i < rand(1, 3); $i++) {
                $startTime = $startTime->copy()->addHours(rand(1, 4))->addMinutes(rand(0, 59));
                TicketLog::factory()->create([
                    'ticket_id' => $ticket->id,
                    'type_id' => TicketLogTypeEnum::WORK_PROGRESS->id(),
                    'member_id' => Member::where('role_id', MemberRoleEnum::CREW->id())->inRandomOrder()->value('id'),
                    'recorded_on' => $startTime,
                ]);

                $startTime = $startTime->copy()->addHours(rand(1, 4))->addMinutes(rand(0, 59));
                TicketLog::factory()->create([
                    'ticket_id' => $ticket->id,
                    'type_id' => TicketLogTypeEnum::TIME_EXTENSION->id(),
                    'member_id' => Member::where('role_id', MemberRoleEnum::CREW->id())->inRandomOrder()->value('id'),
                    'recorded_on' => $startTime,
                ]);
            }

            // WORK_EVALUATION_REQUEST
            for ($i = 0; $i < $issueCount; $i++) {
                $startTime = $startTime->copy()->addMinutes(rand(5, 10));
                TicketLog::factory()->create([
                    'ticket_id' => $ticket->id,
                    'type_id' => TicketLogTypeEnum::WORK_EVALUATION_REQUEST->id(),
                    'member_id' => Member::where('role_id', MemberRoleEnum::CREW->id())->inRandomOrder()->value('id'),
                    'recorded_on' => $startTime,
                ]);
            }

            // WORK_EVALUATION
            foreach ($issueTypes as $issueId) {
                $startTime = $startTime->copy()->addMinutes(rand(5, 10));
                TicketLog::factory()->create([
                    'ticket_id' => $ticket->id,
                    'type_id' => TicketLogTypeEnum::WORK_EVALUATION->id(),
                    'member_id' => Member::where('role_id', MemberRoleEnum::CREW->id())->inRandomOrder()->value('id'),
                    'recorded_on' => $startTime,
                ]);
            }

            // RANDOMLY DECIDE: Should this ticket proceed to OWNER_EVALUATION & CLOSE?
            $chance = rand(1, 100);

            if ($chance <= 60) {
                // 60% chance to go to OWNER_EVALUATION_REQUEST
                $startTime = $startTime->copy()->addMinutes(rand(5, 20));
                TicketLog::factory()->create([
                    'ticket_id' => $ticket->id,
                    'type_id' => TicketLogTypeEnum::OWNER_EVALUATION_REQUEST->id(),
                    'member_id' => Member::where('role_id', MemberRoleEnum::CREW->id())->inRandomOrder()->value('id'),
                    'recorded_on' => $startTime,
                ]);
            }

            if ($chance <= 40) {
                // 40% chance to be approved and closed
                $startTime = $startTime->copy()->addMinutes(rand(5, 30));
                TicketLog::factory()->create([
                    'ticket_id' => $ticket->id,
                    'type_id' => TicketLogTypeEnum::APPROVAL->id(),
                    'member_id' => Member::where('role_id', MemberRoleEnum::CREW->id())->inRandomOrder()->value('id'),
                    'recorded_on' => $startTime,
                ]);

                $ticket->update([
                    'closed_on' => $startTime,
                    'status_id' => TicketStatusEnum::CLOSED->id(),
                ]);
            } elseif ($chance <= 70) {
                // 30% chance to be in progress
                $ticket->update([
                    'status_id' => TicketStatusEnum::ON_PROGRESS->id(),
                ]);
            } else {
                // 30% chance to stay open (default)
                // No status change needed if default is OPEN
            }
        });

    }
}
