<?php

namespace Tests\Feature;

use App\Enums\MemberCapabilityEnum;
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

    public function test_authentication_exchange(): void
    {
        $authentication_code = AuthenticationCode::factory()->create();

        $authentication_code->applicant->member->capabilities()
            ->sync(MemberCapabilityEnum::APPROVAL->id());

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
                'capabilities',
                'specialties' => [
                    '*' => [
                        'id',
                        'name',
                        'service_level_agreement_duration_hour',
                    ],
                ],
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
        $this->assertNotEmpty($data['capabilities']);
        $this->assertNotEmpty($data['specialties']);

        foreach($data['specialties'] as $specialty)
        {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        }

        $this->assertDatabaseHas('refresh_tokens', [
            'token' => $data['refresh_token'],
        ]);
    }

    public function test_authentication_refresh()
    {
        $refresh_token = RefreshToken::factory()->create();

        $refresh_token->member->capabilities()->sync(MemberCapabilityEnum::APPROVAL->id());

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
                'capabilities',
                'specialties' => [
                    '*' => [
                        'id',
                        'name',
                        'service_level_agreement_duration_hour',
                    ],
                ],
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
        $this->assertNotEmpty($data['capabilities']);
        $this->assertNotEmpty($data['specialties']);

        foreach($data['specialties'] as $specialty)
        {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        }

        $this->assertDatabaseHas('refresh_tokens', [
            'token' => $data['refresh_token'],
        ]);
    }
}
