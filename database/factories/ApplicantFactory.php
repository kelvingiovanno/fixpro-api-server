<?php

namespace Database\Factories;

use App\Enums\ApplicantStatusEnum;

use App\Models\Member;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Applicant>
 */
class ApplicantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'status_id' => ApplicantStatusEnum::ACCEPTED->id(),
        ];
    }
}
