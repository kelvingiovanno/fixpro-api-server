<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Location;
use App\Models\Enums\Speciality;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($user) {
            Ticket::factory(rand(3, 5))->create([
                'user_id' => $user->id,
                'location_id' => Location::factory(),
            ]);
            
            $specialityIds = Speciality::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $user->specialities()->attach($specialityIds);
        });
    }
}
