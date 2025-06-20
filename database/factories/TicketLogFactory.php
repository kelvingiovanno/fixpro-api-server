<?php

namespace Database\Factories;

use App\Models\Enums\TicketLogType;
use App\Models\Ticket;
use App\Models\Member;
use App\Models\TicketLog;
use App\Models\TicketLogDocument;
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
            'member_id' => Member::factory(),
            'type_id' => TicketLogType::inRandomOrder()->first()->id,
            'news' => $this->faker->sentence(rand(6,10)),
        ];
    }

    /**
     * Configure the factory's model state.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (TicketLog $ticketLog) {

            $docCount = $this->faker->numberBetween(3, 5);
            for ($i = 0; $i < $docCount; $i++) {
                TicketLogDocument::factory()->create([
                    'log_id' => $ticketLog->id,
                ]);
            }
        });
    }
}
