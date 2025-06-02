<?php

namespace Tests\Feature;

use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;
use App\Enums\TicketLogTypeEnum;
use App\Enums\TicketStatusEnum;
use App\Models\AuthenticationCode;
use App\Models\Enums\MemberRole;
use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketLogType;
use App\Models\Enums\TicketResponseType;
use App\Models\Enums\TicketStatusType;
use App\Models\Member;
use App\Models\Ticket;
use App\Models\TicketLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp() : void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    public function test_get_tickets()
    {
        Ticket::factory(10)->create();

        $auth_code = AuthenticationCode::factory()->create(); 

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
        ])->getJson('/api/tickets');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'issue_types' => [
                        '*' => [
                            'id',
                            'name',
                            'service_level_agreement_duration_hour',
                        ],
                    ],
                    'response_level',
                    'raised_on',
                    'status',
                    'closed_on',
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');

        foreach ($data as $ticket)
        {
            $this->assertNotEmpty($ticket['id']);
            $this->assertNotEmpty($ticket['issue_types']);

            foreach ($ticket['issue_types'] as $issue) 
            {
                $this->assertNotEmpty($issue['id']);
                $this->assertNotEmpty($issue['name']);
                $this->assertNotEmpty($issue['service_level_agreement_duration_hour']);
            }

            $this->assertNotEmpty($ticket['response_level']);
            $this->assertNotEmpty($ticket['raised_on']);
            $this->assertNotEmpty($ticket['status']);
            $this->assertNotEmpty($ticket['closed_on']);
        }
    }

    public function test_post_ticket()
    {
        $auth_code = AuthenticationCode::factory()->create(); 

        $response_exchange = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'issue_type_ids' => TicketIssueType::inRandomOrder()->take(rand(1,4))->pluck('id')->toArray(),
                'response_level' => TicketResponseType::inRandomOrder()->first()->name,
                'stated_issue' => $this->faker->sentence(rand(1,3)),
                'executive_summary' => $this->faker->paragraph(),
                'location' => [
                    'stated_location' => $this->faker->address(),
                    'gps_location' => [
                        'latitude' => -6.201637,
                        'longitude' => 106.782636,
                    ],
                ],
                'supportive_documents' => [
                    [
                        'resource_type' => $this->faker->fileExtension, 
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0), 
                        'resource_content' => base64_encode($this->faker->text(100)), 
                    ],
                    [
                        'resource_type' => $this->faker->fileExtension,
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0),
                        'resource_content' => base64_encode($this->faker->text(100)),
                    ],
                ],
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets', $payload);
        
        $response->assertStatus(201);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'issue_types' => [
                    '*' => [
                        'id',
                        'name',
                        'service_level_agreement_duration_hour',
                    ],
                ],
                'response_level',
                'raised_on',
                'status',
                'executive_summary',
                'closed_on',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['issue_types']);

        foreach ($data['issue_types'] as $issue) 
        {
            $this->assertNotEmpty($issue['id']);
            $this->assertNotEmpty($issue['name']);
            $this->assertNotEmpty($issue['service_level_agreement_duration_hour']);
        }

        $this->assertNotEmpty($data['response_level']);
        $this->assertNotEmpty($data['raised_on']);
        $this->assertNotEmpty($data['status']);
        $this->assertNotEmpty($data['closed_on']);

        $this->assertDatabaseHas('tickets', [
            'id' => $data['id'],
        ]);
    }

    public function test_get_ticket()
    {
        $ticket = Ticket::factory()->create();

        $auth_code = AuthenticationCode::factory()->create(); 

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
        ])->getJson('/api/ticket/' . $ticket->id);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'issue_type' => [
                    '*' => [
                        'id',
                        'name',
                        'service_level_agreement_duration_hour',
                    ],
                ], 
                'response_level',
                'raised_on',
                'status',
                'executive_summary',
                'stated_issue',
                'location' => [
                    'stated_location',
                    'gps_location' => [
                        'latitude',
                        'longitude',
                    ],
                ],
                'supportive_documents' => [
                    '*' => [
                        'resource_type',
                        'resource_name',
                        'resource_size',
                        'previewable_on',
                    ],
                ],
                'issuer' => [
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
                ],
                'logs' => [
                    '*' => [
                        'id',
                        'owning_ticket_id',
                        'type',
                        'issuer' => [
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
                        ],
                        'recorded_on',
                        'news',
                        'attachments' => [
                            '*' => [
                                'resource_type',
                                'resource_name',
                                'resource_size',
                                'previewable_on',
                            ],
                        ],
                    ],
                ],
                'handlers' => [
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
                    ],
                ],
                'closed_on',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['issue_type']);

        foreach($data['issue_type'] as $issue)
        {
            $this->assertNotEmpty($issue['id']);
            $this->assertNotEmpty($issue['name']);
            $this->assertNotEmpty($issue['service_level_agreement_duration_hour']);
        }

        $this->assertNotEmpty($data['response_level']);
        $this->assertNotEmpty($data['raised_on']);
        $this->assertNotEmpty($data['status']);
        $this->assertNotEmpty($data['executive_summary']);
        $this->assertNotEmpty($data['stated_issue']);
        $this->assertNotEmpty($data['location']['stated_location']);
        $this->assertNotEmpty($data['location']['gps_location']['latitude']);
        $this->assertNotEmpty($data['location']['gps_location']['longitude']);
        $this->assertNotEmpty($data['supportive_documents']);
        $this->assertNotEmpty($data['closed_on']);

        $this->assertNotEmpty($data['issuer']['id']);
        $this->assertNotEmpty($data['issuer']['name']);
        $this->assertNotEmpty($data['issuer']['role']);
        $this->assertNotEmpty($data['issuer']['title']);
        $this->assertNotEmpty($data['issuer']['specialties']);

        foreach($data['issuer']['specialties'] as $specialty)
        {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        } 
    
        foreach ($data['logs'] as $log) {
            $this->assertNotEmpty($log['id']);
            $this->assertNotEmpty($log['owning_ticket_id']);
            $this->assertNotEmpty($log['type']);
            $this->assertNotEmpty($log['recorded_on']);
            $this->assertNotEmpty($log['news']);

            $issuer = $log['issuer'];
            $this->assertNotEmpty($issuer['id']);
            $this->assertNotEmpty($issuer['name']);
            $this->assertNotEmpty($issuer['role']);
            $this->assertNotEmpty($issuer['title']);
            $this->assertNotEmpty($issuer['specialties']);
            
            foreach ($issuer['specialties'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            
            }
            
            $this->assertNotEmpty($log['recorded_on']);
            $this->assertNotEmpty($log['news']);
            $this->assertNotEmpty($log['attachments']);
        }
        
        foreach ($data['handlers'] as $handler) 
        {
            $this->assertNotEmpty($handler['id']);
            $this->assertNotEmpty($handler['name']);
            $this->assertNotEmpty($handler['role']);
            $this->assertNotEmpty($handler['title']);
            $this->assertNotEmpty($handler['specialties']);

            foreach ($handler['specialties'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }

        }

        $this->assertNotEmpty($data['closed_on']);
    }

    public function test_patch_ticket()
    {
        $ticket = Ticket::factory()->create();

        $auth_code = AuthenticationCode::factory()->create(); 

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'issue_type' => TicketIssueType::inRandomOrder()->take(rand(1,4))->pluck('id')->toArray(),
                'status' => TicketStatusType::inRandomOrder()->first()->name,
                'stated_issue' => $this->faker->sentence(),
                'executive_summary' => $this->faker->paragraph(),
                'location' => [
                    'stated_location' => $this->faker->address,
                    'gps_location' => [
                        'latitude' => $this->faker->latitude,
                        'longitude' => $this->faker->longitude,
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/ticket/' . $ticket->id, $payload);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'issue_type' => [
                    '*' => [
                        'id',
                        'name',
                        'service_level_agreement_duration_hour',
                    ],
                ], 
                'response_level',
                'raised_on',
                'status',
                'executive_summary',
                'stated_issue',
                'location' => [
                    'stated_location',
                    'gps_location' => [
                        'latitude',
                        'longitude',
                    ],
                ],
                'supportive_documents' => [
                    '*' => [
                        'resource_type',
                        'resource_name',
                        'resource_size',
                        'previewable_on',
                    ],
                ],
                'issuer' => [
                    'id',
                    'name',
                    'role',
                    'title',
                    'specialities' => [
                        '*' => [
                            'id',
                            'name',
                            'service_level_agreement_duration_hour',
                        ],
                    ],
                    'capabilities',
                ],
                'logs' => [
                    '*' => [
                        'id',
                        'owning_ticket_id',
                        'type',
                        'issuer' => [
                            'id',
                            'name',
                            'role',
                            'title',
                            'specialities' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'service_level_agreement_duration_hour',
                                ],
                            ],
                            'capabilities',
                        ],
                        'recorded_on',
                        'news',
                        'attachments' => [
                            '*' => [
                                'resource_type',
                                'resource_name',
                                'resource_size',
                                'previewable_on',
                            ],
                        ],
                    ],
                ],
                'handlers' => [
                    '*' => [
                        'id',
                        'name',
                        'role',
                        'title',
                        'specialities' => [
                            '*' => [
                                'id',
                                'name',
                                'service_level_agreement_duration_hour',
                            ],
                        ],
                        'capabilities',
                    ],
                ],
                'closed_on',
            ],
            'errors',
        ]);

        $data = $response->json('data');

        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['issue_type']);

        foreach($data['issue_type'] as $issue)
        {
            $this->assertNotEmpty($issue['id']);
            $this->assertNotEmpty($issue['name']);
            $this->assertNotEmpty($issue['service_level_agreement_duration_hour']);
        }

        $this->assertNotEmpty($data['response_level']);
        $this->assertNotEmpty($data['raised_on']);
        $this->assertNotEmpty($data['status']);
        $this->assertNotEmpty($data['executive_summary']);
        $this->assertNotEmpty($data['stated_issue']);
        $this->assertNotEmpty($data['location']['stated_location']);
        $this->assertNotEmpty($data['location']['gps_location']['latitude']);
        $this->assertNotEmpty($data['location']['gps_location']['longitude']);
        $this->assertNotEmpty($data['supportive_documents']);
        $this->assertNotEmpty($data['closed_on']);

        $this->assertNotEmpty($data['issuer']['id']);
        $this->assertNotEmpty($data['issuer']['name']);
        $this->assertNotEmpty($data['issuer']['role']);
        $this->assertNotEmpty($data['issuer']['title']);
        $this->assertNotEmpty($data['issuer']['specialities']);

        foreach($data['issuer']['specialities'] as $specialty)
        {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        } 
        
        foreach ($data['logs'] as $log) {
            $this->assertNotEmpty($log['id']);
            $this->assertNotEmpty($log['owning_ticket_id']);
            $this->assertNotEmpty($log['type']);
            $this->assertNotEmpty($log['recorded_on']);
            $this->assertNotEmpty($log['news']);

            $issuer = $log['issuer'];
            $this->assertNotEmpty($issuer['id']);
            $this->assertNotEmpty($issuer['name']);
            $this->assertNotEmpty($issuer['role']);
            $this->assertNotEmpty($issuer['title']);
            $this->assertNotEmpty($issuer['specialities']);
            
            foreach ($issuer['specialities'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
            }
            
            $this->assertNotEmpty($log['recorded_on']);
            $this->assertNotEmpty($log['news']);
            $this->assertNotEmpty($log['attachments']);
        }
        
        foreach ($data['handlers'] as $handler) 
        {
            $this->assertNotEmpty($handler['id']);
            $this->assertNotEmpty($handler['name']);
            $this->assertNotEmpty($handler['role']);
            $this->assertNotEmpty($handler['title']);

            foreach ($handler['specialities'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
        }

        $this->assertNotEmpty($data['closed_on']);
    }

    public function test_reject_ticket()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;
        $member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
        ]);

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'reason' => $this->faker->sentence(),
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id .'/reject', $payload);

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data',
            'errors',
        ]);        

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status_id' => TicketStatusEnum::REJECTED->id(),
        ]);
    }

    public function test_cancel_ticket()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
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
        ])->postJson('/api/ticket/' . $ticket->id .'/cancel');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data',
            'errors',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status_id' => TicketStatusEnum::CANCELLED->id(),
        ]);
    }

    public function test_evaluate_request()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;

        $member->update([
            'role_id' => MemberRoleEnum::CREW->id(),
        ]);

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
        ]);

        $ticket->logs()->delete();

        $ticket->update([
            'status_id' => TicketStatusEnum::ON_PROGRESS->id(),
        ]);

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'remark' => 'ini remark',
                'supportive_documents' => [
                    [
                        'resource_type' => $this->faker->fileExtension, 
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0), 
                        'resource_content' => base64_encode($this->faker->text(100)), 
                    ],
                    [
                        'resource_type' => $this->faker->fileExtension,
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0),
                        'resource_content' => base64_encode($this->faker->text(100)),
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id .'/evaluate/request', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tickets', [
            'status_id' => TicketStatusEnum::WORK_EVALUATION->id(),
        ]);
    }

    public function test_evaluate()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;

        $member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
        ]);

        $ticket->update([
            'status_id' => TicketStatusEnum::WORK_EVALUATION->id(),
        ]);

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'resolveToApprove' => $this->faker->boolean(),
                'reason' => $this->faker->sentence(),
                'supportive_documents' => [
                    [
                        'resource_type' => $this->faker->fileExtension, 
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0), 
                        'resource_content' => base64_encode($this->faker->text(100)), 
                    ],
                    [
                        'resource_type' => $this->faker->fileExtension,
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0),
                        'resource_content' => base64_encode($this->faker->text(100)),
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id .'/evaluate', $payload);

        $response->assertStatus(201);
    }

    public function test_close() 
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;

        $member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
        ]);

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'reason' => $this->faker->sentence(),
                'supportive_documents' => [
                    [
                        'resource_type' => $this->faker->fileExtension, 
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0), 
                        'resource_content' => base64_encode($this->faker->text(100)), 
                    ],
                    [
                        'resource_type' => $this->faker->fileExtension,
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0),
                        'resource_content' => base64_encode($this->faker->text(100)),
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id .'/close', $payload);

        $response->assertStatus(201);
    }

    public function test_force_close()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;

        $member->update([
            'role_id' => MemberRoleEnum::MANAGEMENT->id(),
        ]);

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
        ]);

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'reason' => $this->faker->sentence(),
                'supportive_documents' => [
                    [
                        'resource_type' => $this->faker->fileExtension, 
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0), 
                        'resource_content' => base64_encode($this->faker->text(100)), 
                    ],
                    [
                        'resource_type' => $this->faker->fileExtension,
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0),
                        'resource_content' => base64_encode($this->faker->text(100)),
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id .'/force-close', $payload);

        $response->assertStatus(201);
    }

    public function test_get_ticket_logs()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
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
        ])->getJson('/api/ticket/' . $ticket->id . '/logs');

        $response->assertStatus(200);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                '*' => [
                    'id',
                    'owning_ticket_id',
                    'type',
                    'issuer' => [
                        'id',
                        'name',
                        'role',
                        'title',
                        'specialities' => [
                            '*' => [
                                'id',
                                'name',
                                'service_level_agreement_duration_hour',
                            ],
                        ],
                        'capabilities',
                    ],
                    'recorded_on',
                    'news',
                    'attachments',
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');

        foreach ($data as $logs)
        {
            $this->assertNotEmpty($logs['id']);
            $this->assertNotEmpty($logs['owning_ticket_id']);
            $this->assertNotEmpty($logs['type']);
            $this->assertNotEmpty($logs['recorded_on']);
            $this->assertNotEmpty($logs['news']);

            $issuer = $logs['issuer'];
            $this->assertNotEmpty($issuer['id']);
            $this->assertNotEmpty($issuer['name']);
            $this->assertNotEmpty($issuer['role']);
            $this->assertNotEmpty($issuer['title']);
            $this->assertNotEmpty($issuer['specialities']);
            
            foreach ($issuer['specialities'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
            
            $this->assertNotEmpty($logs['recorded_on']);
            $this->assertNotEmpty($logs['news']);
            $this->assertNotEmpty($logs['attachments']);

            $this->assertDatabaseHas('ticket_logs', [
                'id' => $logs['id'],
            ]);
        }
    }

    public function test_post_ticket_logs()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
        ]);

        $payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $payload);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                'type' => TicketLogType::inRandomOrder()->first()->name,
                'news' => $this->faker->sentence(),
                'supportive_documents' => [
                    [
                        'resource_type' => $this->faker->fileExtension, 
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0), 
                        'resource_content' => base64_encode($this->faker->text(100)), 
                    ],
                    [
                        'resource_type' => $this->faker->fileExtension,
                        'resource_name' => $this->faker->name(),
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0),
                        'resource_content' => base64_encode($this->faker->text(100)),
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id . '/logs', $payload);

        $response->assertStatus(201);

        $response->assertExactJsonStructure([
            'message',
            'data' => [
                'id',
                'owning_ticket_id',
                'type',
                'issuer' => [
                    'id',
                    'name',
                    'role',
                    'title',
                    'specialities' => [
                        '*' => [
                            'id',
                            'name',
                            'service_level_agreement_duration_hour',
                        ],
                    ],
                    'capabilities',
                ],
                'recorded_on',
                'news',
                'attachments' => [
                    '*' => [
                        'resource_type',
                        'resource_name',
                        'resource_size',
                        'previewable_on',
                    ],
                ],
            ],
            'errors',
        ]);

        $data = $response->json('data');
        
        $this->assertNotEmpty($data['id']);
        $this->assertNotEmpty($data['owning_ticket_id']);
        $this->assertNotEmpty($data['type']);
        $this->assertNotEmpty($data['recorded_on']);
        $this->assertNotEmpty($data['news']);

        $issuer = $data['issuer'];
        $this->assertNotEmpty($issuer['id']);
        $this->assertNotEmpty($issuer['name']);
        $this->assertNotEmpty($issuer['role']);
        $this->assertNotEmpty($issuer['title']);
        $this->assertNotEmpty($issuer['specialities']);
        
        foreach ($issuer['specialities'] as $specialty) {
            $this->assertNotEmpty($specialty['id']);
            $this->assertNotEmpty($specialty['name']);
            $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
        }
        
        $this->assertNotEmpty($data['recorded_on']);
        $this->assertNotEmpty($data['news']);
        $this->assertNotEmpty($data['attachments']);

        $this->assertDatabaseHas('ticket_logs', [
            'id' => $data['id'],
        ]);
    }

    public function test_get_handlers()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;

        $ticket = Ticket::factory()->create([
            'member_id' => $member,
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
        ])->getJson('/api/ticket/' . $ticket->id . '/handlers');

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
                ],
            ],
            'errors',
        ]);

        foreach ($response->json('data') as $handler) 
        {
            $this->assertNotEmpty($handler['id']);
            $this->assertNotEmpty($handler['name']);
            $this->assertNotEmpty($handler['role']);
            $this->assertNotEmpty($handler['title']);
            $this->assertNotEmpty($handler['specialties']);

            foreach ($handler['specialties'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
        }
    }

    public function test_post_handlers()
    {
        $auth_code = AuthenticationCode::factory()->create(); 
        
        $member = $auth_code->applicant->member;
        $member->capabilities()->syncWithoutDetaching(MemberCapabilityEnum::INVITE->id());

        $ticket = Ticket::factory()->create([
            'member_id' => $member->id,
        ]);

        $ticket->ticket_issues()->delete();

        $issue_type_1 = TicketIssueType::inRandomOrder()->first();
        $issue_type_2 = TicketIssueType::inRandomOrder()->first();

        $ticket->ticket_issues()->create([
            'issue_id' => $issue_type_1->id,
        ]);

        $ticket->ticket_issues()->create([
            'issue_id' => $issue_type_2->id,
        ]);

        $exchange_payload = [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ];

        $response_exchange = $this->postJson('/api/auth/exchange', $exchange_payload);
        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $payload = [
            'data' => [
                [
                    'appointed_member_ids' => Member::factory(rand(2, 3))->create()->pluck('id')->toArray(),
                    'work_description' => $this->faker->paragraph(),
                    'issue_type' => $issue_type_1->id,
                    'supportive_documents' => [
                        [
                            'resource_type' => 'application/pdf',
                            'resource_name' => 'manual.pdf',
                            'resource_size' => 123.45,
                            'resource_content' => base64_encode('dummy-content'),
                        ],
                        [
                            'resource_type' => 'image/png',
                            'resource_name' => 'screenshot.png',
                            'resource_size' => 456.78,
                            'resource_content' => base64_encode('another-content'),
                        ],
                    ],
                ],
                [
                    'appointed_member_ids' => Member::factory(rand(2, 3))->create()->pluck('id')->toArray(),
                    'work_description' => $this->faker->paragraph(),
                    'issue_type' => $issue_type_2->id,
                    'supportive_documents' => [
                        [
                            'resource_type' => 'application/pdf',
                            'resource_name' => 'manual.pdf',
                            'resource_size' => 123.45,
                            'resource_content' => base64_encode('dummy-content'),
                        ],
                        [
                            'resource_type' => 'image/png',
                            'resource_name' => 'screenshot.png',
                            'resource_size' => 456.78,
                            'resource_content' => base64_encode('another-content'),
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/ticket/' . $ticket->id . '/handlers', $payload);

        $response->assertStatus(200);

        $response->assertJsonStructure([
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
                ],
            ],
            'errors',
        ]);

        foreach ($response->json('data') as $handler) {
            $this->assertNotEmpty($handler['id']);
            $this->assertNotEmpty($handler['name']);
            $this->assertNotEmpty($handler['role']);
            $this->assertNotEmpty($handler['title']);
            $this->assertIsArray($handler['specialties']);

            foreach ($handler['specialties'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
        }
    }
}
