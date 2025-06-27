<?php

namespace Tests\Feature;

use App\Enums\MemberRoleEnum;

use App\Models\AuthenticationCode;

use App\Models\Enums\TicketLogType;

use App\Models\Ticket;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class TicketLogControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp() : void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_retrive_ticket_logs()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        

        $ticket = Ticket::first();

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
        ])->getJson('/api/ticket/' . $ticket->id . '/logs');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'owning_ticket_id',
                    'type',
                    'issuer' => [
                        'id',
                        'name',
                        'role',
                        'title',
                        'specialities' => [
                            '*' => [
                                'id',
                                'name',
                                'service_level_agreement_duration_hour',
                            ],
                        ],
                        'capabilities',
                    ],
                    'recorded_on',
                    'news',
                    'attachments',
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');


        foreach ($data as $logs)
        {
            $this->assertNotEmpty($logs['id']);
            $this->assertNotEmpty($logs['owning_ticket_id']);
            $this->assertNotEmpty($logs['type']);
            $this->assertNotEmpty($logs['recorded_on']);
            $this->assertNotEmpty($logs['news']);

            $issuer = $logs['issuer'];
            $this->assertNotEmpty($issuer['id']);
            $this->assertNotEmpty($issuer['name']);
            $this->assertNotEmpty($issuer['role']);
            $this->assertNotEmpty($issuer['title']);
            $this->assertNotEmpty($issuer['specialities']);
            
            foreach ($issuer['specialities'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
            
            $this->assertNotEmpty($logs['recorded_on']);
            $this->assertNotEmpty($logs['news']);
            $this->assertNotEmpty($logs['attachments']);

        }
    }

    public function test_create_ticket_logs()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $ticket = Ticket::first();

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
                'type' => TicketLogType::inRandomOrder()->first()->name,
                'news' => $this->faker->sentence(),
                'supportive_documents' => [
                    [
                        'resource_type' => $this->faker->fileExtension, 
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0), 
                        'resource_content' => base64_encode($this->faker->text(100)), 
                    ],
                    [
                        'resource_type' => $this->faker->fileExtension,
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0),
                        'resource_content' => base64_encode($this->faker->text(100)),
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id . '/logs', $payload);

        $response->assertStatus(201);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'owning_ticket_id',
                'type',
                'issuer' => [
                    'id',
                    'name',
                    'role',
                    'title',
                    'specialities' => [
                        '*' => [
                            'id',
                            'name',
                            'service_level_agreement_duration_hour',
                        ],
                    ],
                    'capabilities',
                ],
                'recorded_on',
                'news',
                'attachments' => [
                    '*' => [
                        'resource_type',
                        'resource_name',
                        'resource_size',
                        'previewable_on',
                    ],
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');
        
        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['owning_ticket_id']);
        $this->assertNotEmpty($data['type']);
        $this->assertNotEmpty($data['recorded_on']);
        $this->assertNotEmpty($data['news']);

        $issuer = $data['issuer'];
        $this->assertNotEmpty($issuer['id']);
        $this->assertNotEmpty($issuer['name']);
        $this->assertNotEmpty($issuer['role']);
        $this->assertNotEmpty($issuer['title']);
        $this->assertNotEmpty($issuer['specialities']);
        
        foreach ($issuer['specialities'] as $specialty) {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        }
        
        $this->assertNotEmpty($data['recorded_on']);
        $this->assertNotEmpty($data['news']);
        $this->assertNotEmpty($data['attachments']);

        $this->assertDatabaseHas('ticket_logs', [
            'id' => $data['id'],
        ]);
    }

}
