<?php

namespace Database\Factories;

use App\Models\Inbox;
use App\Models\Member;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Inbox>
 */
class InboxFactory extends Factory
{
    protected $model = Inbox::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::inRandomOrder()->value('id'),
            'ticket_id' => Ticket::inRandomOrder()->value('id'),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(2),
            'sent_on' => now(),
        ];
    }
}
