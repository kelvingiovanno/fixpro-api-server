<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatusEnum;
use App\Enums\JoinPolicyEnum;
use App\Enums\MemberRoleEnum;

use App\Models\Enums\TicketIssueType;

use App\Models\Applicant;
use App\Models\AuthenticationCode;

use App\Services\AreaService;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class AreaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AreaService $areaService;

    public function setUp() : void 
    {
        parent::setUp();     

        $this->artisan('db:seed');

        $this->areaService = app(AreaService::class);
    }

    public function test_get_area_information(): void
    {
        $area_name = $this->areaService->set_name('Binus Kemanggisan');
        $area_join_policy = $this->areaService->set_join_policy(JoinPolicyEnum::APPROVAL_NEEDED);

        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/area');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'name',
                'join_policy',
                'member_count',
                'pending_member_count',
                'issue_type_count',
            ],
            'errors', 
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['name']);
        $this->assertNotEmpty($data['join_policy']);
        $this->assertNotNull($data['member_count']);
        $this->assertNotNull($data['pending_member_count']);
        $this->assertNotNull($data['issue_type_count']);

        $this->assertSame($data['name'], $area_name);
        $this->assertSame($data['join_policy'], $area_join_policy);
        $this->assertSame($data['member_count'], Applicant::where('status_id', ApplicantStatusEnum::ACCEPTED->id())->count());
        $this->assertSame($data['pending_member_count'], Applicant::where('status_id', ApplicantStatusEnum::PENDING->id())->count());
        $this->assertSame($data['issue_type_count'], TicketIssueType::all()->count());
    }

    public function test_get_area_join_policy()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/area/join-policy');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'join_policy',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['join_policy']);

        $this->assertSame($data['join_policy'], $this->areaService->get_join_policy());
    }

    public function test_update_area_join_policy()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $payload = [
            'data' => [
                'join_policy' => 'OPEN',
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/area/join-policy', $payload);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'join_policy',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['join_policy']);

        $this->assertSame($data['join_policy'], $this->areaService->get_join_policy());
    }

}
