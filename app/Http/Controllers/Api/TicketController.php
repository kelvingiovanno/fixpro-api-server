<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Ticket;

use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private ApiResponseService $apiResponseService;

    public function __construct(ApiResponseService $_apiResponseService)
    {
        $this->apiResponseService = $_apiResponseService; 
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