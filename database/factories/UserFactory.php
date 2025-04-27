<?php

namespace Database\Factories;

use App\Models\AuthenticationCode;
use App\Models\Ticket;
use App\Models\Location;
use App\Models\Enums\Speciality;
use App\Models\RefreshToken;
use App\Models\User;
use App\Models\UserData;
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
            'name' => $this->faker->name(),
            'role_id' => 1,
            'title' => $this->faker->jobTitle(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {

            Ticket::factory(rand(3, 4))->create([
                'user_id' => $user->id,
            ]);

            UserData::factory()->create([
                'user_id' => $user->id,
            ]);

            RefreshToken::factory()->create([
                'user_id' => $user->id,
            ]);

            AuthenticationCode::factory()->create([
                'user_id' => $user->id,
            ]);

            $specialityIds = Speciality::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $user->specialities()->attach($specialityIds);

            
        });
    }
}
