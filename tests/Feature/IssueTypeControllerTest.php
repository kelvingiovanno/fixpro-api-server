<?php

namespace Tests\Feature;

use App\Enums\MemberRoleEnum;

use App\Models\AuthenticationCode;
use App\Models\Enums\TicketIssueType;
use App\Services\GoogleCalendarService;
use Dompdf\Adapter\GD;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class IssueTypeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private GoogleCalendarService $googleCalendarService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');

        $this->googleCalendarService = new GoogleCalendarService();
    }

    public function test_get_all_issue_type()
    {
        $auth_code = AuthenticationCode::factory()->create(); 

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/issue-types');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'service_level_agreement_duration_hour',
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');

        foreach ($data as $issue)
        {
            $this->assertNotEmpty($issue['id']);
            $this->assertNotEmpty($issue['name']);
            $this->assertNotEmpty($issue['service_level_agreement_duration_hour']);
        
            $this->assertDatabaseHas('ticket_issue_types', [
                'id' => $issue['id']
            ]);
            
            $this->assertDatabaseHas('ticket_issue_types', [
                'name' => $issue['name']
            ]);
            
            $this->assertDatabaseHas('ticket_issue_types', [
                'service_level_agreement_duration_hour' => $issue['service_level_agreement_duration_hour']
            ]);
        }
    }

    public function test_create_new_issue_type()
    {
        $auth_code = AuthenticationCode::factory()->create(); 

        $this->googleCalendarService->set_client_id(env('GOOGLE_CLIENT_ID'));
        $this->googleCalendarService->set_client_secret(env('GOOGLE_CLIENT_SECRET'));
        $this->googleCalendarService->set_redirect_uri(env('GOOGLE_REDIRECT_URI'));

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'name' => $this->faker->words(rand(1,2), true),
                'service_level_agreement_duration_hour' => rand(2,12),
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/issue-types', $payload);

        $response->assertStatus(201);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'service_level_agreement_duration_hour',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['name']);
        $this->assertNotEmpty($data['service_level_agreement_duration_hour']);

        $this->assertDatabaseHas('ticket_issue_types', [
            'id' => $data['id'],
            'name' => $data['name'],
            'sla_duration_hour' => $data['service_level_agreement_duration_hour'],
        ]);
    }

    public function test_delete_issue_types()
    {
        $auth_code = AuthenticationCode::factory()->create(); 

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $issue_id = TicketIssueType::inRandomOrder()->first()->id; 

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/issue-types/' . $issue_id);

        $response->assertStatus(200);
        
        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'service_level_agreement_duration_hour',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['name']);
        $this->assertNotEmpty($data['service_level_agreement_duration_hour']);

        $this->assertNull(TicketIssueType::find($data['id']));
    }
}
