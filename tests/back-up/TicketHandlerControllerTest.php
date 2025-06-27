<?php

namespace Tests\Feature;

use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;
use App\Models\AuthenticationCode;
use App\Models\Enums\TicketIssueType;
use App\Models\Member;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketHandlerControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp() : void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_retrive_ticket_handlers()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $member = $auth_code->applicant->member;

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
        ]);

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
        ])->getJson('/api/ticket/' . $ticket->id . '/handlers');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'role',
                    'title',
                    'specialties' => [
                        '*' => [
                            'id',
                            'name',
                            'service_level_agreement_duration_hour',
                        ],
                    ],
                    'capabilities',
                    'member_since',
                    'member_until',
                ],
            ],
            'errors',
        ]);

        foreach ($response->json('data') as $handler) 
        {
            $this->assertNotEmpty($handler['id']);
            $this->assertNotEmpty($handler['name']);
            $this->assertNotEmpty($handler['role']);
            $this->assertNotEmpty($handler['title']);
            $this->assertNotEmpty($handler['member_since']);
            $this->assertNotEmpty($handler['member_until']);

            foreach ($handler['specialties'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
        }
    }

    public function test_assign_tiket_handlers()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::CREW->id(),
        ]);

        $member = $auth_code->applicant->member;
        $member->capabilities()->syncWithoutDetaching(MemberCapabilityEnum::INVITE->id());

        $ticket = Ticket::factory()->create([
            'member_id' => $member->id,
        ]);

        $ticket->ticket_issues()->delete();

        $issue_type_1 = TicketIssueType::inRandomOrder()->first();
        $issue_type_2 = TicketIssueType::inRandomOrder()->first();
        

        $ticket->ticket_issues()->create([
            'issue_id' => $issue_type_1->id,
        ]);

        $ticket->ticket_issues()->create([
            'issue_id' => $issue_type_2->id,
        ]);

        $exchange_payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $exchange_payload);
        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                [
                    'appointed_member_ids' => Member::factory(rand(2, 3))->create()->pluck('id')->toArray(),
                    'work_description' => $this->faker->paragraph(),
                    'issue_type' => $issue_type_1->id,
                    'supportive_documents' => [
                        [
                            'resource_type' => 'application/pdf',
                            'resource_name' => 'manual.pdf',
                            'resource_size' => 123.45,
                            'resource_content' => base64_encode('dummy-content'),
                        ],
                        [
                            'resource_type' => 'image/png',
                            'resource_name' => 'screenshot.png',
                            'resource_size' => 456.78,
                            'resource_content' => base64_encode('another-content'),
                        ],
                    ],
                ],
                [
                    'appointed_member_ids' => Member::factory(rand(2, 3))->create()->pluck('id')->toArray(),
                    'work_description' => $this->faker->paragraph(),
                    'issue_type' => $issue_type_2->id,
                    'supportive_documents' => [
                        [
                            'resource_type' => 'application/pdf',
                            'resource_name' => 'manual.pdf',
                            'resource_size' => 123.45,
                            'resource_content' => base64_encode('dummy-content'),
                        ],
                        [
                            'resource_type' => 'image/png',
                            'resource_name' => 'screenshot.png',
                            'resource_size' => 456.78,
                            'resource_content' => base64_encode('another-c ontent'),
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id . '/handlers', $payload);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'role',
                    'title',
                    'specialties' => [
                        '*' => [
                            'id',
                            'name',
                            'service_level_agreement_duration_hour',
                        ],
                    ],
                    'capabilities',
                ],
            ],
            'errors',
        ]);

        foreach ($response->json('data') as $handler) {
            $this->assertNotEmpty($handler['id']);
            $this->assertNotEmpty($handler['name']);
            $this->assertNotEmpty($handler['role']);
            $this->assertNotEmpty($handler['title']);
            $this->assertIsArray($handler['specialties']);

            foreach ($handler['specialties'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
        }
    }

}
