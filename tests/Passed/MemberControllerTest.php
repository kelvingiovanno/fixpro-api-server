<?php

namespace Tests\Feature;

use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;
use App\Models\AuthenticationCode;
use App\Models\Enums\MemberCapability;
use App\Models\Enums\MemberRole;
use App\Models\Enums\TicketIssueType;
use App\Models\Member;

use App\Services\AreaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AreaService $areaService;

    public function setUp() : void 
    {
        parent::setUp();     

        $this->artisan('db:seed');

        $this->areaService = app(AreaService::class);

        $this->areaService->set_join_form(['name', 'email', 'phone_number']);
    }

    public function test_retrieve_all_accepted_members()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        Member::factory(5)->create();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/area/members');

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

        $data = $response->json('data');

        foreach ($data as $member)
        {
            $this->assertNotEmpty($member['id']);
            $this->assertNotEmpty($member['name']);
            $this->assertNotEmpty($member['role']);
            $this->assertNotEmpty($member['title']);
            $this->assertNotEmpty($member['specialties']);
            $this->assertNotEmpty($member['member_since']);
            $this->assertNotEmpty($member['member_until']);

            $this->assertDatabaseHas('members', [
                'id' => $member['id'],
                'name' => $member['name'],
                'title' => $member['title'],
                'member_since' => Carbon::parse($member['member_since'])->format('Y-m-d H:i:s'),
                'member_until' => Carbon::parse($member['member_until'])->format('Y-m-d H:i:s'),
            ]);

            $this->assertDatabaseHas('member_roles', [
                'name' => $member['role'],
            ]);

            foreach ($member['specialties'] as $specialty)
            {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            
                $this->assertDatabaseHas('specialties', [
                    'member_id' => $member['id'],
                    'issue_id' => $specialty['id'],
                ]);
            }

            // dd($member['capabilities']);

            foreach ($member['capabilities'] as $capability_name) {
                
                $this->assertNotNull($capability_name);

                $this->assertDatabaseHas('capabilities', [
                    'member_id' => $member['id'],
                    'capability_id' => MemberCapabilityEnum::from($capability_name)->id(),
                ]);
            }
        }
    }

    public function test_retrieve_specific_accepted_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $member = Member::factory()->create();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/area/member/' . $member->id);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
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
            'errors',
        ]); 

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['name']);
        $this->assertNotEmpty($data['role']);
        $this->assertNotEmpty($data['title']);
        $this->assertNotEmpty($data['specialties']);
        $this->assertNotEmpty($data['member_since']);
        $this->assertNotEmpty($data['member_until']);

        $this->assertDatabaseHas('members', [
            'id' => $data['id'],
            'name' => $data['name'],
            'role_id' => MemberRoleEnum::from($data['role'])->id(),
            'title' => $data['title'],
            'member_since' => Carbon::parse($data['member_since'])->format('Y-m-d H:i:s'),
            'member_until' => Carbon::parse($data['member_until'])->format('Y-m-d H:i:s'),
        ]);

        foreach ($data['specialties'] as $specialty)
        {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        
            $this->assertDatabaseHas('specialties', [
                'member_id' => $data['id'],
                'issue_id' => $specialty['id'],
            ]);
        }

        $this->assertNotNull($data['capabilities']);

        foreach ($data['capabilities'] as $capabilityName) {
            $this->assertDatabaseHas('capabilities', [
                'member_id' => $member['id'],
                'capability_id' => MemberCapabilityEnum::from($capabilityName)->id(),
            ]);
        }
    }

    public function test_revoke_specific_accepted_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $member = Member::factory()->create();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/area/member/' . $member->id);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data',
            'errors',
        ]); 

        $this->assertSoftDeleted('members', [
            'id' => $member->id,
        ]);
    }

    public function test_renew_information_specific_accepted_member()
    {
        $authCode = AuthenticationCode::factory()->create();
        $authCode->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $member = Member::factory()->create();

        $exchangeResponse = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $authCode->id,
            ],
        ]);

        $exchangeResponse->assertStatus(200);
        $token = $exchangeResponse->json('data.access_token');

        $memberSince = now()->setTimezone('UTC');
        $memberUntil = now()->addWeek()->setTimezone('UTC');

        $specialties = TicketIssueType::inRandomOrder()
            ->take(rand(0, 3))
            ->get(['id', 'name', 'sla_hours'])
            ->map(function ($issue) {
                return [
                    'id' => $issue->id,
                    'name' => $issue->name,
                    'service_level_agreement_duration_hour' => $issue->sla_hours,
                ];
            });

        $capabilities = MemberCapability::inRandomOrder()
            ->take(rand(0, 2))
            ->pluck('name')
            ->toArray();

        $payload = [
            'data' => [
                'id' => $member->id,
                'name' => 'Kelvin Giovanno',
                'role' => MemberRoleEnum::MANAGEMENT->value,
                'title' => $this->faker->title(),
                'specialties' => $specialties,
                'capabilities' => $capabilities,
                'member_since' => $memberSince->toIso8601String(),
                'member_until' => $memberUntil->toIso8601String(),
            ],
        ];

        $response = $this->withToken($token)->putJson("/api/area/member/{$member->id}", $payload);
        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
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
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['name']);
        $this->assertNotEmpty($data['role']);
        $this->assertNotEmpty($data['title']);
        $this->assertNotEmpty($data['member_since']);
        $this->assertNotEmpty($data['member_until']);

        foreach ($data['specialties'] ?? [] as $specialty) {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        }

        $newMember = Member::find($member->id);

        $this->assertDatabaseHas('members', [
            'id' => $newMember->id,
            'name' => $payload['data']['name'],
            'role_id' => MemberRoleEnum::idFromName($payload['data']['role']),
            'title' => $payload['data']['title'],
            'member_since' => Carbon::parse($payload['data']['member_since'])->format('Y-m-d H:i:s'),
            'member_until' => Carbon::parse($payload['data']['member_until'])->format('Y-m-d H:i:s'),
        ]);

        foreach ($data['specialties'] ?? [] as $specialty) {
            $this->assertDatabaseHas('specialties', [
                'member_id' => $newMember->id,
                'issue_id' => $specialty['id'],
            ]);
        }

        foreach ($data['capabilities'] ?? [] as $capability) {
            $this->assertDatabaseHas('capabilities', [
                'member_id' => $newMember->id,
                'capability_id' => MemberCapabilityEnum::idFromName($capability),
            ]);
        }
    }
}
