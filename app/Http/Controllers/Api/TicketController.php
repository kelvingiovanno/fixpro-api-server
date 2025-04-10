<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Ticket;
use App\Models\Location;
use App\Models\SupportiveTicketDocument;

use App\Enums\IssueTypeEnum;
use App\Enums\TikectStatusEnum;


use App\Services\ApiResponseService;
use App\Services\StorageService;

use Illuminate\Http\Request;

class TicketController extends Controller
{
    private ApiResponseService $apiResponseService;
    private StorageService $storageService;

    public function __construct(ApiResponseService $_apiResponseService, StorageService $_storageService)
    {
        $this->apiResponseService = $_apiResponseService; 
        $this->storageService = $_storageService;
    }

    public function getAll()
    {
        $tickets = Ticket::with(['issueType', 'statusType', 'responseLevelType'])->get();

        $data = $tickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'issue_type' => $ticket->issueType->label ?? null,  
                'response_level' => $ticket->responseLevelType->label ?? null,
                'raised_on' => $ticket->raised_on,
                'status' => $ticket->statusType->label ?? null,
                'closed_on' => $ticket->closed_on,
            ];
        });
    
        return $this->apiResponseService->ok($data, 'Tickets retrieved successfully');
    }

    public function create(Request $request)
    {
        $locationData = $request->input('location');

        $user_id = $request->input('jwt_payload')['user_id'];

        $ticket_issue_type = IssueTypeEnum::id($request->input('issue_type'));
        $ticket_response_level = TikectStatusEnum::id($request->input('response_level'));

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
            'stated_issue' => $request->input('stated_issue'),
            'raised_on' => now(),
        ]);
    
        $documents = $request->input('supportive_documents', []);
        foreach ($documents as $doc) {
            
            $filePath = $this->storageService->storeLogTicketDocument(
                $doc['resource_content'],
                $doc['resource_name'],
                $ticket->id
            );

            SupportiveTicketDocument::create([
                'ticket_id' => $ticket->id,
                'resource_type' => $doc['resource_type'],
                'resource_name' => $doc['resource_name'],
                'resource_size' => $doc['resource_size'],
                'resource_path' => $filePath, 
            ]);
        }
    
        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket_id' => $ticket->id
        ], 201);
    }

    public function get($ticket_id)
    {
        $ticket = Ticket::with(['issueType', 'statusType', 'responseLevelType'])->find($ticket_id);

        $data = [
            'id' => $ticket->id,
            'issue_type' => $ticket->issueType->label ?? null,  
            'response_level' => $ticket->responseLevelType->label ?? null,
            'raised_on' => $ticket->raised_on,
            'status' => $ticket->statusType->label ?? null,
            'closed_on' => $ticket->closed_on,
        ];

        return $this->apiResponseService->ok($data, 'Ticket retrieved successfully');
    }

    public function delete($ticket_id)
    {
        $ticket = Ticket::find($ticket_id);

        if (!$ticket) {
            return $this->apiResponseService->notFound('Ticket not found');
        }

        $ticket->delete();

        return $this->apiResponseService->ok(null, 'Ticket deleted successfully');
    }

    public function patch($ticket_id)
    {

    }
}