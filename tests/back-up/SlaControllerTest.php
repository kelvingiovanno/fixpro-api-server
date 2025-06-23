<?php

namespace Tests\Feature;

use App\Models\AuthenticationCode;
use App\Models\Enums\TicketIssueType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SlaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp() : void 
    {
        parent::setUp();     

        $this->artisan('db:seed');
    }

    public function test_get_sla()
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
        ])->getJson('/api/sla');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'sla_to_respond', 
                'sla_to_auto_close',
                'per_issue_types' => [
                    '*' => [
                        'id',
                        'name',
                        'duration',
                    ],
                ],
            ],
            'errors',
        ]);
        
    }

    public function test_put_sla()
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

        $issue_ids = TicketIssueType::inRandomOrder()->limit(3)->pluck('id');


        $update_sla_payload = [
            'data' => [
                'sla_to_respond' => 12,
                'sla_to_auto_close' => 5,
                'per_issue_types' => $issue_ids->map(function ($id) {
                    return [
                        'id' => $id,
                        'name' => $this->faker->name(),
                        'duration' => rand(3,10),
                    ];
                })->values()->all(),
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/sla', $update_sla_payload);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'sla_to_respond', 
                'sla_to_auto_close',
                'per_issue_types' => [
                    '*' => [
                        'id',
                        'name',
                        'duration',
                    ],
                ],
            ],
            'errors',
        ]);
    }
}
