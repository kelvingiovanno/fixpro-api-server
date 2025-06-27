<?php

namespace Tests\Feature;

use App\Enums\MemberRoleEnum;

use App\Models\AuthenticationCode;

use App\Models\Enums\TicketIssueType;

use App\Services\AreaService;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class SlaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AreaService $areaService;

    public function setUp() : void 
    {
        parent::setUp();     

        $this->artisan('db:seed');

        $this->areaService = app(AreaService::class);

        $this->areaService->set_sla_response(1);
        $this->areaService->set_sla_close(1);
    }

    public function test_retrive_sla_informations()
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
                        'service_level_agreement_duration_hour',
                    ],
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['sla_to_respond']);
        $this->assertNotEmpty($data['sla_to_auto_close']);
        $this->assertNotEmpty($data['per_issue_types']);

        $this->assertSame($data['sla_to_respond'], 1);
        $this->assertSame($data['sla_to_auto_close'], 1);
 
        $expectedIssues = TicketIssueType::all()->map(function ($issue) {
            return [
                'id' => $issue->id,
                'name' => $issue->name,
                'service_level_agreement_duration_hour' => $issue->sla_hours,
            ];
        })->values()->toArray();

        $actualIssues = $data['per_issue_types'];

        $this->assertCount(count($expectedIssues), $actualIssues);

        foreach ($expectedIssues as $expectedIssue) {
            $this->assertContains($expectedIssue, $actualIssues);
        }

        foreach ($actualIssues as $actualIssue) {
            $this->assertContains($actualIssue, $expectedIssues);
        }
    }

    public function test_update_sla_informations()
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

        $updated_issues = TicketIssueType::all()->map(function ($issue){
            return [
                'id' => $issue->id,
                'duration' => (string) rand(1,4),
            ];
        })->values()->toArray();

        $payload = [
            'data' => [
                'sla_to_respond' => '2',
                'sla_to_auto_close' => '3',
                'per_issue_types' => $updated_issues,
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/sla', $payload);

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

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertSame('2', $data['sla_to_respond']);
        $this->assertSame('3', $data['sla_to_auto_close']);
        $this->assertNotEmpty($data['per_issue_types']);

        foreach ($updated_issues as $updatedIssue) {
            $matchedIssue = collect($data['per_issue_types'])->firstWhere('id', $updatedIssue['id']);
            $this->assertNotNull($matchedIssue);
            $this->assertSame($updatedIssue['duration'], (string) $matchedIssue['duration']);
        }
    }
}