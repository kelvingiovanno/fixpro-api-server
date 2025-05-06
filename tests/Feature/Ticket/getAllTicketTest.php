<?php 

namespace Tests\Feature\Ticket;

use Tests\TestCase;

use App\Models\Applicant;
use App\Models\AuthenticationCode;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

class GetAllTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_all_tickets()
    {
        $applicant = Applicant::factory()->create();
        $user = User::factory()->create();

        $auth_code = AuthenticationCode::create([
            'user_id' => $user->id,
            'applicant_id' => $applicant->id,
        ]);

        $exchangeResponse = $this->postJson('/api/auth/exchange', [
            'authentication_code' => $auth_code->id, 
        ]);

        $token = $exchangeResponse->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/tickets');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'ticket_id',
                    'issue_type',
                    'response_level',
                    'raised_on',
                    'status',
                    'executive_summary',
                    'closed_on',
                ]
            ]
        ]);
    }
}
