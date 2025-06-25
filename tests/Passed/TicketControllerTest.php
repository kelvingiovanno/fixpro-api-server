<?php

namespace Tests\Feature;

use App\Enums\MemberRoleEnum;
use App\Enums\TicketStatusEnum;

use App\Models\AuthenticationCode;

use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketResponseType;
use App\Models\Enums\TicketStatusType;

use App\Models\Ticket;

use App\Services\AreaService;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AreaService $areaService;

    public function setUp() : void
    {
        parent::setUp();
        $this->artisan('db:seed');
    
        $this->areaService = app(AreaService::class);
    }

    public function test_retrive_all_tickets()
    {
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

    public function test_create_new_ticket()
    {
        $auth_code = AuthenticationCode::factory()->create(); 

        $response_exchange = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $image = Storage::disk('public')->get('dummy/dummy_photo.jpg');

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
                        'resource_type' => 'image/jpg', 
                        'resource_name' => 'photo.jpg',
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0), 
                        'resource_content' => base64_encode($image), 
                    ],
                    [
                        'resource_type' => 'image/jpg',
                        'resource_name' => 'photo2.jpg',
                        'resource_size' => $this->faker->randomFloat(2, 0.1, 10.0),
                        'resource_content' => base64_encode($image),
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

        $this->assertDatabaseHas('tickets', [
            'id' => $data['id'],
        ]);
    }

    public function test_update_ticket_information()
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
        $this->assertNotEmpty($data['stated_issue']);
        $this->assertNotEmpty($data['location']['stated_location']);
        $this->assertNotEmpty($data['location']['gps_location']['latitude']);
        $this->assertNotEmpty($data['location']['gps_location']['longitude']);
        $this->assertNotEmpty($data['supportive_documents']);

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
            }
            
            $this->assertNotEmpty($log['recorded_on']);
            $this->assertNotEmpty($log['news']);
        }
        
        foreach ($data['handlers'] as $handler) 
        {
            $this->assertNotEmpty($handler['id']);
            $this->assertNotEmpty($handler['name']);
            $this->assertNotEmpty($handler['role']);
            $this->assertNotEmpty($handler['title']);

            foreach ($handler['specialties'] as $specialty) {
                $this->assertNotEmpty($specialty['id']);
                $this->assertNotEmpty($specialty['name']);
                $this->assertNotEmpty($specialty['service_level_agreement_duration_hour']);
            }
        }
    }

    public function test_evaluate_request_ticket()
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

    public function test_evaluate_ticket()
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
    
    public function test_close_ticket() 
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
            'status_id' => TicketStatusEnum::IN_ASSESSMENT->id(),
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

    public function test_force_close_ticket()
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

    public function test_print_view_ticket()
    {
     
        $auth_code = AuthenticationCode::factory()->create(); 

        $response_exchange = $this->postJson('/api/auth/exchange', [
            'data' => [
                'authentication_code' => $auth_code->id,
            ],
        ]);

        $ticket = Ticket::first();

        $response_exchange->assertStatus(200);

        $token = $response_exchange->json('data')['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/ticket/' . $ticket->id . '/print-view');
        
        $response->assertStatus(200);

        $response->assertHeader('Content-Type', 'application/pdf');
        
        $this->assertStringStartsWith('%PDF', $response->getContent());

        Storage::disk('local')->put('test-outputs/print-ticket.pdf', $response->getContent());
    }
}
