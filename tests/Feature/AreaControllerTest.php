<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatusEnum;
use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;
use App\Models\Applicant;
use App\Models\AuthenticationCode;
use App\Models\Enums\MemberCapability;
use App\Models\Enums\MemberRole;
use App\Models\Enums\TicketIssueType;
use App\Models\Member;
use App\Models\SystemSetting;
use App\Services\ReferralCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AreaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp() : void 
    {
        parent::setUp();     

        $this->artisan('db:seed');
    }

    public function test_area(): void
    {
        SystemSetting::put('area_name', 'Binus Kemanggisas');
        SystemSetting::put('area_join_policy', 'approval-needed');   

        $auth_code = AuthenticationCode::factory()->create();

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

        $this->assertSame($data['name'], SystemSetting::get('area_name'));
        $this->assertSame($data['join_policy'], SystemSetting::get('area_join_policy'));
        $this->assertSame($data['member_count'], Applicant::where('status_id', ApplicantStatusEnum::ACCEPTED->id())->count());
        $this->assertSame($data['pending_member_count'], Applicant::where('status_id', ApplicantStatusEnum::PENDING->id())->count());
        $this->assertSame($data['issue_type_count'], TicketIssueType::all()->count());
    }

    public function test_get_join_policy()
    {
        SystemSetting::put('area_join_policy', 'approval-needed');   

        $auth_code = AuthenticationCode::factory()->create();

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

        $this->assertSame($data['join_policy'], SystemSetting::get('area_join_policy'));
    }

    public function test_put_join_policy()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $payload = [
            'data' => [
                'join_policy' => 'open',
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

        $this->assertSame($data['join_policy'], SystemSetting::get('area_join_policy'));
    }

    public function test_get_join_code() 
    {
        $auth_code = AuthenticationCode::factory()->create();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/area/join-code');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'endpoint',
                'referral_tracking_identifier',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['endpoint']);
        $this->assertNotEmpty($data['referral_tracking_identifier']);

        $referralCodeService = new ReferralCodeService();

        $this->assertSame($data['endpoint'], env('APP_URL'));
        $this->assertSame($data['referral_tracking_identifier'], $referralCodeService->getReferral());
    }

    public function test_delete_join_code()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/area/join-code');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data',
            'errors',
        ]);
    }

    public function test_get_all_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

        Applicant::factory(10)->create();

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

            foreach ($member['specialties'] as $specialty)
            {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }

            $this->assertNotNull($member['capabilities']);
            $this->assertNotEmpty($member['member_since']);
            $this->assertNotEmpty($member['member_until']);
        }
    }

    public function test_get_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

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

        foreach ($data['specialties'] as $specialty)
        {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        }

        $this->assertNotNull($data['capabilities']);
        $this->assertNotEmpty($data['member_since']);
        $this->assertNotEmpty($data['member_until']);
    }

    public function test_delete_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

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

    public function test_put_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $member = Member::factory()->create();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $payload = [
            'data' => [
                'id' => $member->id,
                'name' => 'Kelvin Giovanno',
                'role' => MemberRole::inRandomOrder()->first()->name,
                'title' => $this->faker->title(),
                'specialties' => TicketIssueType::inRandomOrder()->take(rand(0, 3))->get(['id', 'name', 'sla_duration_hour'])->map(function ($speciality) {
                    return [
                        'id' => $speciality->id,
                        'name' => $speciality->name,
                        'service_level_agreement_duration_hour' => rand(4,12),
                    ];
                }),
                'capabilities' => MemberCapability::inRandomOrder()->take(rand(0, 2))->pluck('name')->toArray(),
                'member_since' => now(),
                'member_until' => now()->addWeek(),
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/area/member/' . $member->id, $payload);

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
        
        if($data['specialties'])
        {
            foreach ($data['specialties'] as $specialty)
            {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
        }
        
        $this->assertNotEmpty($data['member_since']);
        $this->assertNotEmpty($data['member_until']);

        $new_member = Member::find($member->id);
        
        $this->assertDatabaseHas('members', [
            'id' => $new_member->id,
            'name' => $payload['data']['name'],
            'role_id' => MemberRoleEnum::idFromName($payload['data']['role']),
            'title' => $payload['data']['title'],
            'member_since' => $payload['data']['member_since'],
            'member_until' => $payload['data']['member_until'],
        ]);

        if($data['specialties'])
        {
            foreach ($data['specialties'] as $specialty)
            {
                $this->assertDatabaseHas('specialties', [
                    'member_id' => $new_member->id,
                    'issue_id' => $specialty['id'],
                ]);
            }
        }

        if($data['capabilities'])
        {
            foreach ($data['capabilities'] as $capability)
            {
                $this->assertDatabaseHas('capabilities', [
                    'member_id' => $new_member->id,
                    'capability_id' => MemberCapabilityEnum::idFromName($capability),
                ]);
            }
        }   
    }

    public function test_get_all_pending_member()
    {   
        SystemSetting::put('area_join_form', json_encode(['name','email', 'phone_number']));

        $auth_code = AuthenticationCode::factory()->create();

        Applicant::factory(20)->create();


        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/area/pending-memberships');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'form_answer' => [
                        '*' => [
                            'field_label',
                            'field_value',
                        ],
                    ],
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');

        foreach($data as $pending_member)
        {
            $this->assertNotEmpty($pending_member['id']);
            $this->assertNotEmpty($pending_member['form_answer']);
            
            foreach ($pending_member['form_answer'] as $form)
            {
                $this->assertNotEmpty($form['field_label']);
                $this->assertNotEmpty($form['field_value']);
            }
        }
    }

    public function test_post_pending_member()
    {
        SystemSetting::put('area_join_form', json_encode(['name','email', 'phone_number']));

        $auth_code = AuthenticationCode::factory()->create();

        $applicant = Applicant::factory()->create();

        $applicant->member->specialities()->detach();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $payload = [
            'data' => [
                'application_id' => $applicant->id,
                'name' => $this->faker->name(),
                'role' => MemberRole::inRandomOrder()->first()->name,
                'specialization' => TicketIssueType::inRandomOrder()->take(rand(0, 3))->pluck('name')->toArray(),
                'title' => $this->faker->title(),
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/area/pending-memberships', $payload);

        $response->assertStatus(201);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'form_answer' => [
                    '*' => [
                        'field_label',
                        'field_value',
                    ],
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['form_answer']);
        
        foreach ($data['form_answer'] as $form)
        {
            $this->assertNotEmpty($form['field_label']);
            $this->assertNotEmpty($form['field_value']);
        }
    }

    public function test_get_applicant()
    {
        SystemSetting::put('area_join_form', json_encode(['name','email', 'phone_number']));

        $auth_code = AuthenticationCode::factory()->create();

        Applicant::factory(20)->create();

        $applicant = Applicant::where('status_id', ApplicantStatusEnum::PENDING->id())->first();


        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/area/pending-memberships/' . $applicant->id);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'form_answer' => [
                    '*' => [
                        'field_label',
                        'field_value',
                    ],
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['form_answer']);
        
        foreach ($data['form_answer'] as $form)
        {
            $this->assertNotEmpty($form['field_label']);
            $this->assertNotEmpty($form['field_value']);
        }
    }

    public function test_delete_applicant()
    {
        SystemSetting::put('area_join_form', json_encode(['name','email', 'phone_number']));

        $auth_code = AuthenticationCode::factory()->create();

        Applicant::factory(20)->create();

        $applicant = Applicant::where('status_id', ApplicantStatusEnum::PENDING->id())->first();


        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/area/pending-memberships/' . $applicant->id);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data',
            'errors',
        ]);
        
        $updated_applicant = Applicant::find($applicant->id);

        $this->assertEquals(
            ApplicantStatusEnum::REJECTED->id(),
            $updated_applicant->status_id,
        );
    }
}
