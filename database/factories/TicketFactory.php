<?php

namespace Database\Factories;

use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketResponseType;
use App\Models\Enums\TicketStatusType;

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
            'member_id' => Member::factory(),
            'status_id' => TicketStatusType::inRandomOrder()->first()->id,
            'response_id' => TicketResponseType::inRandomOrder()->first()?->id,
            'location_id' => Location::factory(),
            'stated_issue' => $this->faker->sentence(10),'closed_on' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('+1 days', '+1 month') : null,
            'executive_summary' => $this->faker->paragraph(),
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
            
            $logCount = $this->faker->numberBetween(7, 10);
            for ($i = 0; $i < $logCount; $i++) {
                TicketLog::factory()->create([
                    'ticket_id' => $ticket->id,
                    'member_id' => Member::factory(),
                ]);
            }

            $issueCount = $this->faker->numberBetween(1, 4);
            $issueTypes = TicketIssueType::inRandomOrder()->limit($issueCount)->pluck('id');
            foreach ($issueTypes as $issueId) {
                TicketIssue::factory()->create([
                    'ticket_id' => $ticket->id,
                    'issue_id' => $issueId,
                ]);
            }

            $docCount = $this->faker->numberBetween(3, 5);
            for ($i = 0; $i < $docCount; $i++) {
                TicketDocument::factory()->create([
                    'ticket_id' => $ticket->id,
                ]);
            }            
        });
    }
}
