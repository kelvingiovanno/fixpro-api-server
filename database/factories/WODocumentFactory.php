<?php

namespace Database\Factories;

use App\Models\TicketIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WODocument>
 */
class WODocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_issue_id' => TicketIssue::factory(),
            'resource_type' => $this->faker->randomElement(['pdf', 'doc']),
            'resource_name' => $this->faker->word . '.' . $this->faker->fileExtension,
            'resource_size' => $this->faker->numberBetween(100, 5000), 
            'previewable_on' => url('/storage/dummy/dummy_photo.jpg'), 
        ];
    }
}
