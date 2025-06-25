<?php

namespace Tests\Feature;

use App\Enums\MemberRoleEnum;

use App\Models\AuthenticationCode;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class JoinBarcodeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    public function test_get_join_barcode()
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
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/area/join');

        $response->assertStatus(200);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertNotEmpty($response->getContent());
    }   

}
