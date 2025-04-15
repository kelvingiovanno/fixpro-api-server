<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Ticket;
use App\Models\Location;
use App\Models\SupportiveTicketDocument;

use App\Enums\IssueTypeEnum;
use App\Enums\ResponLevelEnum;


use App\Services\ApiResponseService;
use App\Services\StorageService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Exception;
use Throwable;
use \Illuminate\Database\Eloquent\ModelNotFoundException;

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
        try {
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
        catch (Throwable $e) 
        {
            $this->apiResponseService->internalServerError('Something went wrong', $e->getMessage());
        }
    }
    
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
    
        if ($validator->fails()) 
        {
            return $this->apiResponseService->unprocessableEntity('Validation failed', $validator->errors());
        }

        try 
        {
            $locationData = $request->input('location');
    
            $user_id = $request->input('jwt_payload')['user_id'];
            $ticket_issue_type = IssueTypeEnum::id($request->input('issue_type'));
            $ticket_response_level = ResponLevelEnum::id($request->input('response_level'));
    
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

            return $this->apiResponseService->created('Ticket created successfully');
    
        } 
        catch (Exception $e) 
        {
            $this->apiResponseService->internalServerError('Something went wrong', $e->getMessage());
        }
    }

    public function get(int $ticket_id)
    {
        try 
        {
            $ticket = Ticket::with(['issueType', 'statusType', 'responseLevelType'])->findOrFail($ticket_id);

            $data = [
                'id' => $ticket->id,
                'issue_type' => optional($ticket->issueType)->label,
                'response_level' => optional($ticket->responseLevelType)->label,
                'raised_on' => $ticket->raised_on,
                'status' => optional($ticket->statusType)->label,
                'closed_on' => $ticket->closed_on,
            ];

            return $this->apiResponseService->ok($data, 'Ticket retrieved successfully');

        } 
        catch (ModelNotFoundException $e) 
        {
            return $this->apiResponseService->notFound('Ticket not found');
        } 
        catch (Throwable $e) 
        {
            $this->apiResponseService->internalServerError('Something went wrong', $e->getMessage());
        }
    }

    public function delete(int $ticket_id)
    {
        try 
        {
            $ticket = Ticket::findOrFail($ticket_id);
            $ticket->delete();

            return $this->apiResponseService->ok(null, 'Ticket deleted successfully');

        } 
        catch (ModelNotFoundException $e) 
        {
            return $this->apiResponseService->notFound('Ticket not found');
        } 
        catch (Throwable $e) 
        {
            $this->apiResponseService->internalServerError('Something went wrong', $e->getMessage());
        }
    }
}