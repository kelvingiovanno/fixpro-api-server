<?php

namespace Tests\Feature;

use App\Enums\MemberRoleEnum;
use App\Models\AuthenticationCode;
use App\Models\Ticket;
use App\Services\AreaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketReportControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AreaService $areaService;

    public function setUp() : void
    {
        parent::setUp();
        $this->artisan('db:seed');
    
        $this->areaService = app(AreaService::class);
    }

    public function test_generate_periodic_report()
    {
     
        $auth_code = AuthenticationCode::factory()->create(); 

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $response_exchange = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];
        
        $month = strtolower(Carbon::now()->format('F'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/statistics/' . $month . '/report');
        
        $response->assertStatus(200);

        $response->assertHeader('Content-Type', 'application/pdf');
        
        $this->assertStringStartsWith('%PDF', $response->getContent());

        Storage::disk('local')->put('test-outputs/periodic_report.pdf', $response->getContent());
    }

    public function test_generate_ticket_report()
    {
     
        $auth_code = AuthenticationCode::factory()->create(); 

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $response_exchange = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];
        
        $month = strtolower(Carbon::now()->format('F'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/statistics/' . $month . '/tickets');
        
        $response->assertStatus(200);

        $response->assertHeader('Content-Type', 'application/pdf');
        
        $this->assertStringStartsWith('%PDF', $response->getContent());

        Storage::disk('local')->put('test-outputs/ticket_report.pdf', $response->getContent());
    }
}
