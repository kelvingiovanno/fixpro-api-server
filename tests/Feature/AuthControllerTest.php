<?php

namespace Tests\Feature;

use App\Models\AuthenticationCode;
use App\Models\RefreshToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_exchange(): void
    {
       $authentication_code = AuthenticationCode::factory()->create();

        $payload = [
            'data' => [
                'authentication_code' => $authentication_code->id,
            ],
        ];

       $response = $this->postJson('/api/auth/exchange', $payload);

       $response->assertStatus(200);

       $response->assertExactJsonStructure([
            'message',
            'data' => [
                'access_token',
                'access_expiry_interval',
                'refresh_token',
                'refresh_expiry_interval',
                'token_type',
                'role_scope',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['access_token']);
        $this->assertNotEmpty($data['access_expiry_interval']);
        $this->assertNotEmpty($data['refresh_token']);
        $this->assertNotEmpty($data['refresh_expiry_interval']);
        $this->assertNotEmpty($data['token_type']);
        $this->assertNotEmpty($data['role_scope']);

        $this->assertDatabaseHas('refresh_tokens', [
            'token' => $data['refresh_token'],
        ]);
    }

    public function test_refresh()
    {
        $refresh_token = RefreshToken::factory()->create();

        $payload = [
            'data' => [
                'refresh_token' => $refresh_token->token,
            ],
        ];

        $response = $this->postJson('api/auth/refresh', $payload);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'access_token',
                'access_expiry_interval',
                'refresh_token',
                'refresh_expiry_interval',
                'token_type',
                'role_scope',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['access_token']);
        $this->assertNotEmpty($data['access_expiry_interval']);
        $this->assertNotEmpty($data['refresh_token']);
        $this->assertNotEmpty($data['refresh_expiry_interval']);
        $this->assertNotEmpty($data['token_type']);
        $this->assertNotEmpty($data['role_scope']);

        $this->assertDatabaseHas('refresh_tokens', [
            'token' => $data['refresh_token'],
        ]);
    }
}
