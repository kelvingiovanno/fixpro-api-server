<?php

namespace Database\Factories;

use App\Models\Enums\ResponseLevelType;
use App\Models\Enums\TicketIssueType;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketDocument;
use App\Models\TicketLog;
use App\Models\User;
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
            'user_id' => User::factory(),
            'response_level_type_id' => ResponseLevelType::inRandomOrder()->value('id'),
            'location_id' => Location::factory(),
            'stated_issue' => $this->faker->sentence(rand(2,3)),
            'executive_summary' => $this->faker->sentence(rand(10,15)),
            'closed_on' => $this->faker->boolean(50)
                ? $this->faker->dateTimeBetween('-2 months', 'now')
                : null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Ticket $ticket) {
            
            $randomUserId = User::inRandomOrder()->first()->id;

            TicketLog::factory(rand(3,7))->create([
                'ticket_id' => $ticket->id,  
                'user_id' => $randomUserId,
            ]);

            $issueIds = TicketIssueType::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $ticket->issues()->attach($issueIds);

            TicketDocument::factory(rand(3,6))->create([
                'ticket_id' => $ticket->id,
            ]);

            $maintainerIds = User::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $ticket->maintainers()->attach($maintainerIds);
        });
    }
}
