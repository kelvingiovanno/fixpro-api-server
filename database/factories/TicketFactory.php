<?php

namespace Database\Factories;

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
            'ticket_issue_type_id' => rand(1, 5),
            'response_level_type_id' => rand(1, 3),
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
            // Ensure we have a valid ticket_id before creating related TicketLogs
            $randomUserId = User::inRandomOrder()->first()->id;

            // Create TicketLogs, ensuring the ticket_id exists in the database
            TicketLog::factory(rand(3,7))->create([
                'ticket_id' => $ticket->id,  // Ensure that ticket_id is from the created ticket
                'user_id' => $randomUserId,
            ]);

            // Create TicketDocuments for the ticket
            TicketDocument::factory(rand(3,6))->create([
                'ticket_id' => $ticket->id,
            ]);

            // Attach maintainers to the ticket
            $maintainerIds = User::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $ticket->maintainers()->attach($maintainerIds);
        });
    }
}
