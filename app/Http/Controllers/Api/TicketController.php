<?php

namespace App\Http\Controllers\Api;

use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;
use App\Http\Controllers\Controller;

use App\Enums\TicketResponseTypeEnum;
use App\Enums\TicketLogTypeEnum;
use App\Enums\TicketStatusEnum;

use App\Models\Ticket;
use App\Models\Location;
use App\Models\Member;
use App\Models\TicketDocument;
use App\Models\TicketIssue;
use App\Models\TicketLog;
use App\Models\TicketLogDocument;

use App\Services\ApiResponseService;
use App\Services\StorageService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Throwable;

class TicketController extends Controller   
{
    private ApiResponseService $apiResponseService;
    private StorageService $storageService;

    public function __construct (
        ApiResponseService $_apiResponseService, 
        StorageService $_storageService
    ) {
        $this->apiResponseService = $_apiResponseService; 
        $this->storageService = $_storageService;
    }

    public function getTickets()
    {
        try 
        {
            $tickets = Ticket::with(['ticket_issues', 'status', 'response'])->get();
            
            $response_data = $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'issue_types' => $ticket->ticket_issues->map(function ($ticket_issue) {
                        return [
                            'id' => $ticket_issue->issue->id,
                            'name' => $ticket_issue->issue->name,
                            'service_level_agreement_duration_hour' => $ticket_issue->issue->sla_duration_hour ?? 'Not assigned yet',
                        ];
                    }),
                    'response_level' => $ticket->response->name, 
                    'raised_on' => $ticket->raised_on,
                    'status' => $ticket->status->name,  
                    'closed_on' => $ticket->closed_on ?? 'Not closed yet',  
                ];
            });
    
            return $this->apiResponseService->ok($response_data, 'Tickets retrieved successfully');
        } 
        catch (Throwable $e) 
        {
            Log::error('Error retrieving tickets: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('An error occurred while retrieving issue types');
        }
    }

    public function postTicket(Request $_request)
    {
        $data = $_request->input('data');

        if(!$data)
        {
            return $this->apiResponseService->unprocessableEntity('Missing required data payload');
        }

        $validator = Validator::make($data, [
            'issue_type_ids' => 'required|array',
            'issue_type_ids.*' => 'required|uuid|exists:ticket_issue_types,id',
            'response_level' => 'required|string',
            'stated_issue' => 'required|string',
            'executive_summary' => 'required|string',
            'location.stated_location' => 'required|string',
            'location.gps_location.latitude' => 'required|numeric',
            'location.gps_location.longitude' => 'required|numeric',
            'supportive_documents' => 'nullable|array',
            'supportive_documents.*.resource_type' => 'required_with:supportive_documents|string',
            'supportive_documents.*.resource_name' => 'required_with:supportive_documents|string',
            'supportive_documents.*.resource_size' => 'required_with:supportive_documents|numeric',
            'supportive_documents.*.resource_content' => 'required_with:supportive_documents|string',
        ]);
    
        if ($validator->fails()) {
            return $this->apiResponseService->unprocessableEntity('Validation failed', $validator->errors());
        }
    
        try 
        {
            $locationData = $data['location'];
            $member_id = $_request->input('jwt_payload')['member_id'];
            
            $ticket_issue_types = $data['issue_type_ids'];
            $ticket_response_level = TicketResponseTypeEnum::idFromName($data['response_level']);
    
            $location = Location::create([
                'stated_location' => $locationData['stated_location'],
                'latitude' => $locationData['gps_location']['latitude'],
                'longitude' => $locationData['gps_location']['longitude'],
            ]);
    
            $ticket = Ticket::create([
                'member_id' => $member_id,
                'response_id' => $ticket_response_level,
                'location_id' => $location->id,
                'stated_issue' => $data['stated_issue'],
            ]);

            foreach ($ticket_issue_types as $issue)
            {
                TicketIssue::create([
                    'ticket_id' => $ticket->id,
                    'issue_id' => $issue,
                ]);
            }
    
            $documents = $data['supportive_documents'] ?? [];
           
            foreach ($documents as $doc) {

                $filePath = $this->storageService->storeTicketDocument(
                    $doc['resource_content'],
                    $doc['resource_name'],
                    $ticket->id
                );
    
                TicketDocument::create([
                    'ticket_id' => $ticket->id,
                    'resource_type' => $doc['resource_type'],
                    'resource_name' => $doc['resource_name'],
                    'resource_size' => $doc['resource_size'],
                    'previewable_on' => $filePath,
                ]);
            }

            $reponse_data = [
                'id' => $ticket->id,
                'issue_types' => $ticket->ticket_issues->map(function ($ticket_issue) {
                    return [
                        'id' => $ticket_issue->issue->id,
                        'name' => $ticket_issue->issue->name,
                        'service_level_agreement_duration_hour' => $ticket_issue->issue->sla_duration_hour ?? 'Not assigned yet',
                    ];
                }),
                'response_level' => $ticket->response->name,
                'raised_on' => $ticket->raised_on,
                'status' => $ticket->status->name,
                'executive_summary' =>  $ticket->executive_summary,
                'closed_on' => $ticket->closed_on ?? 'Not closed yet',
            ];
    
            return $this->apiResponseService->created($reponse_data, 'Ticket created successfully.');
    
        } 
        catch (Throwable $e) 
        {
            Log::error('Error creating ticket', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('An error occurred while creating the ticket. Please try again later.');
        }
    }

    public function getTicket(string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found'); 
        }

        try 
        {
            $ticket = Ticket::with(['issuer','ticket_issues', 'location', 'documents', 'status', 'response', 'logs'])->find($_ticketId);

            if (!$ticket) {
                return $this->apiResponseService->notFound('Ticket not found');
            }

            $response_data = [
                'id' => $ticket->id,
                'issue_type' => $ticket->ticket_issues->map(function ($ticket_issue) {
                    return [
                        'id' => $ticket_issue->issue->id,
                        'name' => $ticket_issue->issue->name,
                        'service_level_agreement_duration_hour' => $ticket_issue->issue->sla_duration_hour ?? 'Not assigned yet',
                    ];
                }),
                'response_level' => $ticket->response->name,
                'raised_on' => $ticket->raised_on,
                'status' => $ticket->status->name,
                'executive_summary' => $ticket->executive_summary,
                'stated_issue' => $ticket->stated_issue,
                'location' => [
                    'stated_location' => $ticket->location->stated_location,
                    'gps_location' => [
                        'latitude' => $ticket->location->latitude,
                        'longitude' => $ticket->location->longitude
                    ],
                ],
                'supportive_documents' => $ticket->documents,
                'issuer' => [
                    'id' => $ticket->issuer->id,
                    'name' => $ticket->issuer->name,
                    'role' => $ticket->issuer->role->name,
                    'title' => $ticket->issuer->title,
                    'specialties' => $ticket->issuer->specialities->map(function ($specialty){
                        return [
                            'id' => $specialty->id,
                            'name' => $specialty->name,
                            'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                        ];
                    }),
                    'capabilities' => $ticket->issuer->capabilities->map(function ($capability) {
                        return $capability->name;
                    }),
                ],
                'logs' => $ticket->logs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'owning_ticket_id' => $log->ticket_id,
                        'type' => $log->type->name,
                        'issuer' => [
                            'id' => $log->issuer->id,
                            'name' => $log->issuer->name,
                            'role' => $log->issuer->role->name,
                            'title' => $log->issuer->title,
                            'specialties' => $log->issuer->specialities->map(function ($specialty){
                                return [
                                    'id' => $specialty->id,
                                    'name' => $specialty->name,
                                    'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                                ];
                            }),
                            'capabilities' => $log->issuer->capabilities->map(function ($capability) {
                                return $capability->name;
                            }),
                        ],
                        'recorded_on' => $log->recorded_on,
                        'news' => $log->news,
                        'attachments' => $log->documents,
                    ];
                }),
                'handlers' => $ticket->ticket_issues->flatMap(function ($issue) {
                    return $issue->maintainers->map(function ($maintainer) {
                        return [
                            'id' => $maintainer->id,
                            'name' => $maintainer->name,
                            'role' => $maintainer->role,
                            'title' => $maintainer->title,
                            'specialties' => $maintainer->specialities->map(function ($specialty){
                                return [
                                    'id' => $specialty->id,
                                    'name' => $specialty->name,
                                    'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                                ];
                            }),
                            'capabilities' => $maintainer->capabilities->map(function ($capability) {
                                return $capability->name;
                            }),
                        ];
                    });
                }),
                'closed_on' => $ticket->closed_on ?? 'Not closed yet',
            ];
            
            return $this->apiResponseService->ok($response_data, 'Ticket retrieved successfully');
        } 
        catch (Throwable $e) 
        {
            Log::error('Error retrieving ticket', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while retrieving the ticket. Please try again later.');
        }
    }
    
    public function patchTicket(Request $_request ,string $_ticketId)
    {
        if(!Str::uuid($_ticketId))
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }  

        $data = $_request->input('data');

        if(!$data) 
        {
            return $this->apiResponseService->badRequest('Missing required data payload');
        }

        $validator = Validator::make($data, [
            'issue_type' => 'required|array|min:1',
            'issue_type.*' => 'required|uuid|exists:ticket_issue_types,id',
            'status' => 'required|string|exists:ticket_status_types,name',
            'stated_issue' => 'required|string',
            'executive_summary' => 'required|string',
            'location' => 'required|array',
            'location.stated_location' => 'required|string',
            'location.gps_location' => 'required|array',
            'location.gps_location.latitude' => 'required|numeric|between:-90,90',
            'location.gps_location.longitude' => 'required|numeric|between:-180,180',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        $ticket_status_id = TicketStatusEnum::idFromName($data['status']);

        if(!$ticket_status_id)
        {
            return $this->apiResponseService->badRequest('Invalid ticket status.');
        }
        
        if($_request->input('jwt_payload')['role_id'] == MemberRoleEnum::CREW->id())
        {
            return $this->apiResponseService->forbidden('Client are not allowed to update tickets.');
        }

        $ticket_updated_data = [
            'member_id' => $_request->input('jwt_payload')['member_id'],
            'status_id' => $ticket_status_id,
            'stated_issue' => $data['stated_issue'],
            'executive_summary' => $data['executive_summary'],
        ];

        $updated_ticket_issue = $data['issue_type'];

        $ticket_location_updated_data = [
            'stated_location' => $data['location']['stated_location'],
            'latitude' => $data['location']['gps_location']['latitude'],
            'longitude' => $data['location']['gps_location']['longitude'],
        ];

        try
        {
            $ticket = Ticket::find($_ticketId);
            
            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found.');
            }
            
            $ticket->update($ticket_updated_data);
            

            foreach ($updated_ticket_issue as $issueId) {
                $exists = $ticket->ticket_issues()
                    ->where('issue_id', $issueId)
                    ->exists();

                if (! $exists) {
                    $ticket->ticket_issues()->create([
                        'issue_id' => $issueId,
                    ]);
                }
            }

            $ticket->location->update($ticket_location_updated_data);

            $updated_ticket = Ticket::find($_ticketId);

            $response_data = [
                'id' => $updated_ticket->id,
                'issue_type' => $updated_ticket->ticket_issues->map(function ($ticket_issue) {
                    return [
                        'id' => $ticket_issue->issue->id,
                        'name' => $ticket_issue->issue->name,
                        'service_level_agreement_duration_hour' => $ticket_issue->issue->sla_duration_hour ?? 'Not assigned yet', 
                    ];
                }),
                'response_level' => $updated_ticket->response-> name,
                'raised_on' => $updated_ticket->raised_on,
                'status' => $updated_ticket->status->name,
                'executive_summary' => $updated_ticket->executive_summary,
                'stated_issue' => $updated_ticket->stated_issue,
                'location' => [
                    'stated_location' => $updated_ticket->location->stated_location,
                    'gps_location' => [
                        'latitude' => $updated_ticket->location->latitude,
                        'longitude' => $updated_ticket->location->longitude
                    ],
                ],
                'supportive_documents' => $updated_ticket->documents,
                'issuer' => [
                    'id' => $updated_ticket->issuer->id,
                    'name' => $updated_ticket->issuer->name,
                    'role' => $updated_ticket->issuer->role->name,
                    'title' => $updated_ticket->issuer->title,
                    'specialities' => $updated_ticket->issuer->specialities->map(function ($specialty) {
                        return [
                            'id' => $specialty->id,
                            'name' => $specialty->name,
                            'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                        ];
                    }),
                    'capabilities' => $updated_ticket->issuer->capabilities->map(function ($capability){
                        return $capability->name;
                    }),
                ],
                'logs' => $updated_ticket->logs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'owning_ticket_id' => $log->ticket_id,
                        'type' => $log->type->name,
                        'issuer' => [
                            'id' => $log->issuer->id,
                            'name' => $log->issuer->name,
                            'role' => $log->issuer->role->name,
                            'title' => $log->issuer->title,
                            'specialities' => $log->issuer->specialities->map(function ($specialty) {
                                return [
                                    'id' => $specialty->id,
                                    'name' => $specialty->name,
                                    'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                                ];
                            }),
                            'capabilities' => $log->issuer->capabilities->map(function ($capability){
                                return $capability->name;
                            }),
                        ],
                        'recorded_on' => $log->recorded_on, 
                        'news' => $log->news,
                        'attachments' => $log->documents,
                    ];
                }),
                'handlers' => $updated_ticket->ticket_issues->flatMap(function ($issue) {
                    return $issue->maintainers->map(function ($maintainer) {
                        return [
                            'id' => $maintainer->id,
                            'name' => $maintainer->name,
                            'role' => $maintainer->role,
                            'title' => $maintainer->title,
                            'specialities' => $maintainer->specialities->map(function ($specialty){
                                return [
                                    'id' => $specialty->id,
                                    'name' => $specialty->name,
                                    'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                                ];
                            }),
                            'capabilities' => $maintainer->capabilities->map(function ($capability){
                                return $capability->name;
                            }),
                        ];
                    });
                }),
                'closed_on' => $updated_ticket->closed_on ?? 'Not closed yet',
            ];

            return $this->apiResponseService->ok($response_data, 'Ticket updated successfully.');

        }
        catch (Throwable $e)
        {
            Log::error('Error updating ticket.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('An unexpected error occurred while updating the ticket.');
        }

    }

    public function rejectTicket(Request $_request ,string $_ticketId)
    {
        if(!Str::isUuid($_ticketId))
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }

        $rejected_reason = $_request->input('data');

        $validator = Validator::make($rejected_reason, [
            'reason' => 'required|string',
        ]);

        if($validator->fails()) {
            return $this->apiResponseService->badRequest('', $validator->errors());
        }

        $member_id = $_request->input('jwt_payload')['member_id'];
        $role = $_request->input('jwt_payload')['role_id'];

        if($role != MemberRoleEnum::MANAGEMENT->id())
        {
            return $this->apiResponseService->forbidden('Client are not allowed to reject tickets.');
        }

        try
        {
            $ticket = Ticket::find($_ticketId);

            if(!$ticket) {
                return $this->apiResponseService->notFound('Ticket not found.');
            }

            $ticket->update([
                'status_id' => TicketStatusEnum::REJECTED->id(),
            ]);

            TicketLog::create([
                'ticket_id' => $_ticketId,
                'member_id' => $member_id,
                'type' => TicketLogTypeEnum::ACTIVITY->id(),
                'news' => $rejected_reason['reason'],
            ]);

            return $this->apiResponseService->ok('Ticket successfully rejected.');
        }
        catch (Throwable $e)
        {
            Log::error('Error rejecting ticket.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('An unexpected error occurred while rejecting the ticket.');
        }
    }

    public function cancelTicket(Request $_request ,string $_ticketId)
    {   
        if(!Str::uuid($_ticketId))
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }

        $member_id = $_request->input('jwt_payload')['member_id'];

        try
        {
            $ticket = Ticket::find($_ticketId);

            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found.');
            }

            if($ticket->issuer->id != $member_id)
            {
                return $this->apiResponseService->forbidden('Client are not allowed to cancel tickets.');
            }

            $ticket->update([
                'status_id' => TicketStatusEnum::CANCELLED->id(),
            ]);

            TicketLog::create([
                'ticket_id' => $_ticketId,
                'member_id' => $member_id,
                'type' => TicketLogTypeEnum::ACTIVITY->id(),
                'news' => 'Ticket cancelled',
            ]);

            return $this->apiResponseService->ok('Ticket Successfully Cancelled.');
        }
        catch (Throwable $e)
        {
            Log::error('Error cenceling ticket.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('An unexpected error occurred while cenceling the ticket.');
        }
    }

    public function getLogs(string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID'); 
        }

        try
        {
            $ticket = Ticket::find($_ticketId);

            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
            }
            
            $response_data = $ticket->logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'owning_ticket_id' => $log->ticket_id,
                    'type' => $log->type->name,
                    'issuer' => [
                        'id' => $log->issuer->id,
                        'name' => $log->issuer->name,
                        'role' => $log->issuer->role->name,
                        'title' => $log->issuer->title,
                        'specialities' => $log->issuer->specialities->map(function ($specialty) {
                            return [
                                'id' => $specialty->id,
                                'name' => $specialty->name,
                                'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                            ];
                        }),
                        'capabilities' => $log->issuer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                    ],
                    'recorded_on' => $log->recorded_on,
                    'news' => $log->news,
                    'attachments' => $log->documents,
                ];
            });

            return $this->apiResponseService->ok($response_data, 'Ticket logs retrieved successfully');
        }
        catch (Throwable $e)
        {
            Log::error('Error retrieving ticket logs',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An unexpected error occurred while retrieving ticket logs. Please try again later.');
        }   
    }

    public function postLog(Request $_request, string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID'); 
        }

        $data = $_request->input('data');

        if (!$data) 
        {
            return $this->apiResponseService->badRequest('Missing required data payload');
        }
        
        $validator = Validator::make($data, [
            'type' => 'required|string|exists:ticket_log_types,name',
            'news' => 'required|string',
            'supportive_documents' => 'nullable|array',
            'supportive_documents.*.resource_type' => 'required_with:supportive_documents|string',
            'supportive_documents.*.resource_name' => 'required_with:supportive_documents|string',
            'supportive_documents.*.resource_size' => 'required_with:supportive_documents|numeric',
            'supportive_documents.*.previewable_on' => 'required_with:supportive_documents|string',
        ]);

        if ($validator->fails()) {
            return $this->apiResponseService->unprocessableEntity('Validation failed, please check the provided data', $validator->errors());
        }

        try
        {
            $ticket = Ticket::find($_ticketId);
            
            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
            }

            $member_id = $_request->input('jwt_payload')['member_id'];
            $log_type_id = TicketLogTypeEnum::idFromName($data['type']);
            
            $ticket_log = TicketLog::create([
                'ticket_id' => $_ticketId,
                'member_id' => $member_id,
                'type_id' => $log_type_id,
                'news' => $data['news'],
            ]);

            if($log_type_id == TicketLogTypeEnum::ASSESSMENT->id())
            {
                $ticket->update(['status_id' => TicketStatusEnum::IN_ASSESSMENT->id()]);
            }

            if($log_type_id == TicketLogTypeEnum::WORK_PROGRESS->id())
            {
                $ticket->update(['status_id' => TicketStatusEnum::ON_PROGRESS->id()]);
            }

            if($log_type_id == TicketLogTypeEnum::WORK_EVALUATION_REQUEST->id())
            {
                $ticket->update(['status_id' => TicketStatusEnum::WORK_EVALUATION->id()]);
            }

            if($log_type_id == TicketLogTypeEnum::WORK_EVALUATION->id())
            {
                $ticket->update(['status_id' => TicketStatusEnum::CLOSED->id()]);
            }

            $documents = $data['supportive_documents'] ?? [];
           
            foreach ($documents as $document) {
                
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['previewable_on'],
                    $document['resource_name'],
                    $ticket->id
                );
    
                TicketLogDocument::create([
                    'log_id' => $ticket_log->id,
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => $document['resource_size'],
                    'previewable_on' => $filePath,
                ]);
            }

            $reponse_data = [
                'id' => $ticket_log->id,
                'owning_ticket_id' => $ticket_log->ticket_id,
                'type' => $ticket_log->type->name,
                'issuer' => [
                    'id' => $ticket_log->issuer->id,
                    'name' => $ticket_log->issuer->name,
                    'role' => $ticket_log->issuer->role,
                    'title' => $ticket_log->issuer->title,
                    'specialities' => $ticket_log->issuer->specialities->map(function ($specialty){
                        return [
                            'id' => $specialty->id,
                            'name' => $specialty->name,
                            'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                        ]; 
                    }),
                    'capabilities' => $ticket_log->issuer->capabilities->map(function ($capability) {
                        return $capability->name;
                    }),
                ],
                'recorded_on' => $ticket_log->recorded_on,
                'news' => $ticket_log->news,
                'attachments' => $ticket_log->documents->map(function ($document) {
                    return [
                        'resource_type' => $document->resource_type,
                        'resource_name' => $document->resource_name,
                        'resource_size' => $document->resource_size,
                        'previewable_on' => $document->previewable_on,
                    ];
                }),
            ];

            return $this->apiResponseService->created($reponse_data, 'Ticket log created successfully');
        }
        catch (Throwable $e)
        {
            Log::error('Error creating ticket log',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->apiResponseService->internalServerError('An error occurred while processing the request, please try again later');
        }
    }

    public function getHandlers(string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID'); 
        }

        try
        {
            $ticket = Ticket::find($_ticketId);

            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
            }

            $response_data =  $ticket->ticket_issues->flatMap(function ($issue) {
                
                return $issue->maintainers->map(function ($maintainer) {
                    return [
                        'id' => $maintainer->id,
                        'name' => $maintainer->name,
                        'role' => $maintainer->role,
                        'title' => $maintainer->title,
                        'specialties' => $maintainer->specialities->map(function ($specialty){
                            return [
                                'id' => $specialty->id,
                                'name' => $specialty->name,
                                'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                            ];
                        }),
                        'capabilities' => $maintainer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                    ];
                });
            });

            return $this->apiResponseService->ok($response_data, 'Ticket handlers retrieved successfully');
        }
        catch (Throwable $e)
        {
            Log::error('Error retrieving ticket handlers',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while retrieving ticket handlers');
        }
    }

    public function postHandlers(Request $_request, string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID'); 
        } 

        $data = $_request->input('data');

        if(!$data)
        {
            return $this->apiResponseService->badRequest('Missing required data payload');
        }

        $validator = Validator::make($data, [
            'appointed_member_ids' => 'required|array', 
            'appointed_member_ids.*' => 'uuid|exists:members,id',
            'work_description' => 'required|string', 
            'issue_type' => 'required|uuid|exists:ticket_issue_types,id'
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->unprocessableEntity('Validation failed, please check the provided data', $validator->errors());
        }

        try
        {
            $ticket = Ticket::find($_ticketId);
            $member_id = $_request->input('jwt_payload')['member_id'];

            $member = Member::find($member_id);

            $hasAssign = collect($member->capabilities)->contains('id', MemberCapabilityEnum::INVITE->id());

            if(!$hasAssign)
            {
                return $this->apiResponseService->forbidden('You do not have permission to perform this action.');
            }

            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
            }

            $work_description = $data['work_description'];
            $appointed_member_ids = $data['appointed_member_ids'];
            $issue_type_id = $data['issue_type'];

            $ticket_issue = $ticket->ticket_issues->firstWhere('issue_id', $issue_type_id);

            if (!$ticket_issue) {
                return $this->apiResponseService->unprocessableEntity('Ticket issue not found.');
            }

            $ticket_issue->update([
                'work_description' => $work_description,
            ]);

            $ticket_issue->maintainers()->sync($appointed_member_ids);

            TicketLog::create([
                'ticket_id' => $_ticketId,
                'member_id' => $member_id,
                'ticket_log_type_id' => TicketLogTypeEnum::ACTIVITY->id(),
                'news' => count($appointed_member_ids) . ' maintainer(s) assigned to the ticket issue.',
            ]);

            $updated_ticket = Ticket::find($_ticketId);

            $response_data =  $updated_ticket->ticket_issues->flatMap(function ($issue) {
                return $issue->maintainers->map(function ($maintainer) {
                    return [
                        'id' => $maintainer->id,
                        'name' => $maintainer->name,
                        'role' => $maintainer->role,
                        'title' => $maintainer->title,
                        'specialties' => $maintainer->specialities->map(function ($specialty){
                            return [
                                'id' => $specialty->id,
                                'name' => $specialty->name,
                                'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                            ];
                        }),
                        'capabilities' => $maintainer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                    ];
                });
            });

            return $this->apiResponseService->ok($response_data, 'Successfully added handler to the ticket');
        }
        catch (Throwable $e)
        {
            Log::error('Error assigns ticket handlers',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while assigns ticket handlers');
        }
    }       
}