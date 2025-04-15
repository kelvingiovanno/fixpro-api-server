<?php

namespace Database\Factories;

use App\Models\Location;

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
            'user_id' => 1,
            'ticket_issue_type_id' => rand(1, 5),
            'response_level_type_id' => rand(1, 3),
            'location_id' => Location::factory(),
            'stated_issue' => $this->faker->sentence(rand(5, 10)),
            'closed_on' => $this->faker->boolean(50)
                ? $this->faker->dateTimeBetween('-2 months', 'now')
                : null,
        ];
    }
}
