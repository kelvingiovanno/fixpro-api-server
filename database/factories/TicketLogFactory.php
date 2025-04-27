<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\TicketLogDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketLog>
 */
class TicketLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'ticket_log_type_id' => $this->faker->numberBetween(1, 5),
            'recorded_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'news' =>  $this->faker->sentence(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (TicketLog $ticketLog) {
            TicketLogDocument::factory(rand(3,6))->create([
                'ticket_log_id' => $ticketLog->id,
            ]);
        });
    }
}
