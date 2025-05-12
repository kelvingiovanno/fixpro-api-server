<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketDocument>
 */
class TicketDocumentFactory extends Factory
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
            'resource_type' => $this->faker->randomElement(['image', 'video', 'pdf', 'doc']),
            'resource_name' => $this->faker->word . '.' . $this->faker->fileExtension,
            'resource_size' => $this->faker->numberBetween(100, 5000), 
            'previewable_on' => $this->faker->url, 
        ];
    }
}
