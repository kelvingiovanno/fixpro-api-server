<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Enums\IssueTypeEnum;
use App\Enums\ResponLevelEnum;

use App\Models\Ticket;
use App\Models\Location;
use App\Models\TicketDocument;

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

    public function getAll()
    {
        try 
        {
            $tickets = Ticket::with(['issueType', 'statusType', 'responseLevelType'])->get();
            
            $data = $tickets->map(function ($ticket) {
                return [
                    'ticket_id' => $ticket->id,
                    'issue_type' => optional($ticket->issueType)->label ?? 'N/A',
                    'response_level' => optional($ticket->responseLevelType)->label ?? 'N/A', 
                    'raised_on' => $ticket->raised_on,
                    'status' => optional($ticket->statusType)->label ?? 'N/A',  
                    'executive_summary' => $ticket->executive_summary,   
                    'closed_on' => $ticket->closed_on ?? 'Not closed yet',  
                ];
            });
    
            return $this->apiResponseService->ok($data, 'Tickets retrieved successfully');
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

    public function create(Request $_request)
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
                'raised_on' => now(),
            ]);
    
            $documents = $_request->input('supportive_documents', []);
           
            foreach ($documents as $doc) {
                $filePath = $this->storageService->storeLogTicketDocument(
                    $doc['resource_content'],
                    $doc['resource_name'],
                    $ticket->id
                );
    
                TicketDocument::create([
                    'ticket_id' => $ticket->id,
                    'resource_type' => $doc['resource_type'],
                    'resource_name' => $doc['resource_name'],
                    'resource_size' => $doc['resource_size'],
                    'resource_path' => $filePath,
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

    public function get(string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found'); 
        }

        try 
        {
            $ticket = Ticket::with(['user', 'location', 'documents', 'issueType', 'statusType', 'responseLevelType'])->find($_ticketId);

            if (!$ticket) {
                return $this->apiResponseService->notFound('Ticket not found');
            }

            $data = [
                'ticket_id' => $ticket->id,
                'issue_type' => optional($ticket->issueType)->label,
                'response_level' => optional($ticket->responseLevelType)->label,
                'raised_on' => $ticket->raised_on,
                'status' => optional($ticket->statusType)->label,
                'executive_summary' => $ticket->executive_summary,
                'stated_issue' => $ticket->stated_issue,
                'locations' => $ticket->location,
                'supportive_documents' => $ticket->documents,
                'issuer' => [
                    'name' => $ticket->user->name,
                    'more_information' => $ticket->user->userData
                ],
                // 'logs' => $ticket->logs,
                // 'handlers' => ,
                'closed_on' => $ticket->closed_on,
            ];

            return $this->apiResponseService->ok($data, 'Ticket retrieved successfully');
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

    public function delete(int $_ticketId)
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
}