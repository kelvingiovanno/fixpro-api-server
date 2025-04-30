<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Enums\IssueTypeEnum;
use App\Enums\ResponLevelEnum;
use App\Enums\TicketLogTypeEnum;

use App\Models\Ticket;
use App\Models\Location;
use App\Models\TicketDocument;
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
            $tickets = Ticket::with(['issueType', 'statusType', 'responseLevelType'])->get();
            
            $response_data = $tickets->map(function ($ticket) {
                return [
                    'ticket_id' => $ticket->id,
                    'issue_type' => $ticket->issueType->label,
                    'response_level' => $ticket->responseLevelType->label, 
                    'raised_on' => $ticket->raised_on,
                    'status' => $ticket->statusType->label,  
                    'executive_summary' => $ticket->executive_summary,   
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
    
            return $this->apiResponseService->internalServerError('An error occurred while retrieving area data');
        }
    }

    public function postTicket(Request $_request)
    {
        $validator = Validator::make($_request->all(), [
            'issue_type' => 'required|string',
            'response_level' => 'required|string',
            'stated_issue' => 'required|string',
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
            $locationData = $_request->input('location');
            $user_id = $_request->input('jwt_payload')['user_id'];
            
            $ticket_issue_type = IssueTypeEnum::id($_request->input('issue_type'));
            $ticket_response_level = ResponLevelEnum::id($_request->input('response_level'));
    
            $location = Location::create([
                'stated_location' => $locationData['stated_location'],
                'latitude' => $locationData['gps_location']['latitude'],
                'longitude' => $locationData['gps_location']['longitude'],
            ]);
    
            $ticket = Ticket::create([
                'user_id' => $user_id,
                'ticket_issue_type_id' => $ticket_issue_type,
                'response_level_type_id' => $ticket_response_level,
                'location_id' => $location->id,
                'stated_issue' => $_request->input('stated_issue'),
            ]);
    
            $documents = $_request->input('supportive_documents', []);
           
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
    
            return $this->apiResponseService->created('Ticket created successfully');
    
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
            $ticket = Ticket::with(['issuer', 'location', 'documents', 'issueType', 'statusType', 'responseLevelType', 'logs'])->find($_ticketId);

            if (!$ticket) {
                return $this->apiResponseService->notFound('Ticket not found');
            }

            $response_data = [
                'ticket_id' => $ticket->id,
                'issue_type' => $ticket->issueType->label,
                'response_level' => $ticket->responseLevelType->label,
                'raised_on' => $ticket->raised_on,
                'status' => $ticket->statusType->label,
                'executive_summary' => $ticket->executive_summary,
                'stated_issue' => $ticket->stated_issue,
                'locations' => [
                    'stated_location' => $ticket->location->stated_location,
                    'gps_location' => [
                        'latitude' => $ticket->location->latitude,
                        'longitude' => $ticket->location->longitude
                    ],
                ],
                'supportive_documents' => $ticket->documents,
                'issuer' => [
                    'name' => $ticket->issuer->name,
                    'more_information' => $ticket->issuer->userData,
                ],
                'logs' => $ticket->logs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'owning_ticket_id' => $log->ticket_id,
                        'log_type' => $log->logType->label,
                        'issuer' => [
                            'name' => $log->issuer->name,
                            'more_information' => $log->issuer->userData,
                        ],
                        'recorded_on' => $log->recorded_on,
                        'news' => $log->news,
                        'attachment' => $log->documents,
                    ];
                }),
                'handlers' => $ticket->maintainers->map(function ($maintainer) {
                    return [
                        'name' => $maintainer->name,
                        'more_information' => $maintainer->userData, 
                    ];
                }),
                'closed_on' => $ticket->closed_on,
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

    public function delTicket(string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found'); 
        }

        try 
        {
            $ticket = Ticket::find($_ticketId);

            if (!$ticket) {
                return $this->apiResponseService->notFound('Ticket not found');
            }

            $ticket->delete();

            return $this->apiResponseService->ok(null, 'Ticket deleted successfully');
        } 
        catch (Throwable $e) 
        {
            Log::error('Error deleting ticket',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while deleting the ticket. Please try again later.');
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
                    'log_type' => $log->logType->label,
                    'issuer' => [
                        'name' => $log->issuer->name,
                        'more_information' => $log->issuer->userData,
                    ],
                    'recorded_on' => $log->recorded_on,
                    'news' => $log->news,
                    'attachment' => $log->documents,
                ];
            })->toArray();


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
        
        $validator = Validator::make($_request->all(), [
            'log_type' => 'required|string',
            'news' => 'required|string',
            'supportive_documents' => 'nullable|array',
            'supportive_documents.*.resource_type' => 'required_with:supportive_documents|string',
            'supportive_documents.*.resource_name' => 'required_with:supportive_documents|string',
            'supportive_documents.*.resource_size' => 'required_with:supportive_documents|numeric',
            'supportive_documents.*.resource_content' => 'required_with:supportive_documents|string',
        ]);

        if ($validator->fails()) {
            return $this->apiResponseService->unprocessableEntity('Validation failed, please check the provided data', $validator->errors());
        }

        $log_type = TicketLogTypeEnum::id($_request->input('log_type'));
        
        if(!$log_type)
        {
            return $this->apiResponseService->unprocessableEntity('Validation failed, please check the provided data', ['log_type' => 'Invalid log type']);
        }

        try
        {
            $ticket = Ticket::find($_ticketId);
            
            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
            }

            $user_id = $_request->input('jwt_payload')['user_id'];

            $ticket_log = TicketLog::create([
                'ticket_id' => $_ticketId,
                'user_id' => $user_id,
                'ticket_log_type_id' => $log_type,
                'news' => $_request->input('news'),
            ]);

            $documents = $_request->input('supportive_documents', []);
           
            foreach ($documents as $document) {
                
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['resource_content'],
                    $document['resource_name'],
                    $ticket->id
                );
    
                TicketLogDocument::create([
                    'ticket_log_id' => $ticket_log->id,
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => $document['resource_size'],
                    'previewable_on' => $filePath,
                ]);
            }

            $reponse_data = [
                'id' => $ticket_log->id,
                'owning_ticket_id' => $ticket_log->ticket_id,
                'log_type' => $ticket_log->logType->label,
                'issuer' => [
                    'name' => $ticket_log->issuer->name,
                    'more_information' => $ticket_log->issuer->userData,
                ],
                'recorded_on' => $ticket_log->recorded_on,
                'news' => $ticket_log->news,
                'attachment' => $ticket_log->documents->map(function ($document) {
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

            $response_data = $ticket->maintainers->map(function ($maintainer) {
                return [
                    'name' => $maintainer->name,
                    'more_information' => $maintainer->userData,
                ];
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

        $validator = Validator::make($_request->all(), [
            'target_member_ids' => 'required|array', 
            'target_member_ids.*' => 'uuid|exists:users,id',
            'executive_summary' => 'required|string|max:126', 
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->unprocessableEntity('Validation failed, please check the provided data', $validator->errors());
        }

        try
        {
            $ticket = Ticket::find($_ticketId);

            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
            }

            $executive_summary = $_request->input('executive_summary');
            $maintainers = $_request->input('target_member_ids');

            $ticket->maintainers()->detach();
            
            $ticket->maintainers()->attach($maintainers);
            $ticket->update(['executive_summary' => $executive_summary]);

            $updated_ticket = Ticket::find($_ticketId);

            $response_data = [
                'handlers' => $updated_ticket->maintainers->map(function ($maintainer) {
                    return [
                        'name' => $maintainer->name,
                        'more_information' => $maintainer->userData,
                    ];
                }),
                'executive_summary' => $updated_ticket->executive_summary,
            ];

            return $this->apiResponseService->created($response_data, 'Successfully added handler to the ticket');

        }
        catch (Throwable $e)
        {
            Log::error('Error deleting ticket',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('');
        }
    }
}