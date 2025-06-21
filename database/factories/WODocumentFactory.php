<?php

namespace Database\Factories;

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
            'resource_type' => $this->faker->randomElement(['pdf', 'doc']),
            'resource_name' => $this->faker->word . '.' . $this->faker->fileExtension,
            'resource_size' => $this->faker->numberBetween(100, 5000), 
            'previewable_on' => public_path('/storage/dummy/dummy_photo.jpg'), 
        ];
    }
}
