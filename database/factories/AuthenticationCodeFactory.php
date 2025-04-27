<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuthenticationCode>
 */
class AuthenticationCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'user_id' => User::factory(),
            'expires_at' => now()->addMonth(),
        ];
    }
}
