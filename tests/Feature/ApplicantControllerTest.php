<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatusEnum;
use App\Enums\IssueTypeEnum;
use App\Enums\MemberRoleEnum;

use App\Models\Applicant;

use App\Models\AuthenticationCode;
use App\Services\AreaService;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class ApplicantControllerTest extends TestCase
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

    public function test_retrieve_all_pending_members()
    {   

        Applicant::factory(5)->create([
            'status_id' => ApplicantStatusEnum::PENDING->id(),
        ]);

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
                    'submitted_on',
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');

        foreach($data as $pending_member)
        {
            $this->assertNotEmpty($pending_member['id']);

            $this->assertDatabaseHas('members', [
                'id' => $pending_member['id'],
            ]);

            $this->assertNotEmpty($pending_member['form_answer']);
            
            foreach ($pending_member['form_answer'] as $form)
            {
                $this->assertNotEmpty($form['field_label']);
                $this->assertNotEmpty($form['field_value']);
            }
        }
    }

    public function test_retrieve_specific_pending_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $applicant = Applicant::factory()->create([
            'status_id' => ApplicantStatusEnum::PENDING->id(),
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
        ])->getJson('/api/area/pending-membership/' . $applicant->id);

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
                'submitted_on',
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

        $this->assertDatabaseHas('members', [
            'id' => $data['id'],
        ]);
    }

    public function test_approve_pending_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $applicant = Applicant::factory()->create();

        $member = $applicant->member;   
        $member->specialities()->detach();

        $exhcange_response = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $exhcange_response->assertStatus(200);

        $token =  $exhcange_response->json('data')['access_token'];

        $specialization_ids = [IssueTypeEnum::ENGINEERING->id(), IssueTypeEnum::HSE->id()];

        $payload = [
            'data' => [
                'application_id' => $applicant->id,
                'name' => $this->faker->name(),
                'role' => MemberRoleEnum::CREW->value,
                'specialization' => $specialization_ids,
                'title' => 'Petani Ganja',
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

        $this->assertDatabaseHas('applicants', [
            'id' => $applicant->id,
            'status_id' => ApplicantStatusEnum::ACCEPTED->id(),
        ]);

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'role_id' => MemberRoleEnum::CREW->id(),
            'title' => 'Petani Ganja',
        ]);
        
        foreach ($specialization_ids as $speciality_id) {
            $this->assertDatabaseHas('specialties', [ 
                'member_id' => $member->id,
                'issue_id' => $speciality_id,
            ]);
        }
    }

    public function test_reject_pending_member()
    {
        $auth_code = AuthenticationCode::factory()->create();

        $auth_code->applicant->member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $applicant = Applicant::factory()->create([
            'status_id' => ApplicantStatusEnum::PENDING->id()
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
        ])->deleteJson('/api/area/pending-membership/' . $applicant->id);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data',
            'errors',
        ]);

        $this->assertDatabaseHas('applicants', [
            'id' => $applicant->id,
            'status_id' => ApplicantStatusEnum::REJECTED->id(),
        ]);
    }
}
