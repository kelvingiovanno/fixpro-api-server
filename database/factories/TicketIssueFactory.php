<?php

namespace Database\Factories;

use App\Enums\MemberRoleEnum;
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
            
            $members = Member::where('role_id', MemberRoleEnum::CREW->id())
                ->inRandomOrder()
                ->limit(rand(1,3))
                ->get();
            $ticketIssue->maintainers()->syncWithoutDetaching($members->pluck('id')->toArray());
        });
    }      
}
