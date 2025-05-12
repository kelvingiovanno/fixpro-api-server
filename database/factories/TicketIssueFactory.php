<?php

namespace Database\Factories;

use App\Models\Enums\TicketIssueType;
use App\Models\Member;
use App\Models\Ticket;
use App\Models\TicketIssue;
use App\Models\WODocument;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketIssue>
 */
class TicketIssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'issue_id' => TicketIssueType::inRandomOrder()->first()->id,
            'ticket_id' => Ticket::factory(),
            'wo_id' => WODocument::factory(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (TicketIssue $ticketIssue) {
            
            $memberIds = Member::inRandomOrder()->take(rand(2, 5))->pluck('id')->toArray();
            $ticketIssue->maintainers()->attach($memberIds);
        });
    }      
}
