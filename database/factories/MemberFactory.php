<?php

namespace Database\Factories;

use App\Models\Enums\MemberCapability;
use App\Models\Enums\MemberRole;
use App\Models\Enums\MemberStatus;
use App\Models\Enums\TicketIssueType;
use App\Models\Member;
use App\Models\TicketIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => MemberRole::inRandomOrder()->first()->id,
            'name' => $this->faker->name(),
            'title' => $this->faker->jobTitle(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
        ];
    }

    /**
     * Configure the factory's model state.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Member $member) {
            $issueTypeIds = TicketIssueType::inRandomOrder()->take(rand(1, 3))->pluck('id')->toArray(); 
            $member->specialities()->attach($issueTypeIds);

            $ticketIssueIds = TicketIssue::inRandomOrder()->take(rand(1, 3))->pluck('id')->toArray(); 
            $member->maintained_tickets()->attach($ticketIssueIds);

            $capabilityIds = MemberCapability::inRandomOrder()->take(rand(0, 2))->pluck('id')->toArray();
            $member->capabilities()->attach($capabilityIds);
        });
    }
}
