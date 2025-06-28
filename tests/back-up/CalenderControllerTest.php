<?php

namespace Tests\Feature;

use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;
use App\Models\AuthenticationCode;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class CalenderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp() : void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }
    
    public function test_retrive_all_events_from_google_calender(): void
    {
        $auth_code = AuthenticationCode::factory()->create(); 

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::CREW->id(),
        ]);

        $response_exchange = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/calender');
        
        $response->assertStatus(200);

        dd($response->json());
    }
}
