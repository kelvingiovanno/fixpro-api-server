<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Exceptions\InvalidTicketStatusException;

use App\Enums\TicketResponseTypeEnum;

use App\Models\Ticket;
use App\Models\Member;

use App\Services\Reports\PrintViewReport;

use App\Services\ApiResponseService;
use App\Services\StorageService;
use App\Services\TicketService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
      
use Throwable;

class TicketController extends Controller   
{
    public function __construct (
        protected ApiResponseService $apiResponseService, 
        protected StorageService $storageService,
        protected TicketService $ticketService,
        protected PrintViewReport $printViewReport,
    ) { }

    public function index()
    {
        try 
        {
            $tickets = $this->ticketService->all(['ticket_issues.issue', 'status', 'response']);
            
            $response_data = $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'issue_types' => $ticket->ticket_issues->map(function ($ticket_issue) {
                        return [
                            'id' => $ticket_issue->issue->id,
                            'name' => $ticket_issue->issue->name,
                            'service_level_agreement_duration_hour' => (string) $ticket_issue->issue->sla_hours,
                        ];
                    }),
                    'response_level' => $ticket->response->name, 
                    'raised_on' => $ticket->raised_on->format('Y-m-d\TH:i:sP'),
                    'status' => $ticket->status->name,  
                    'closed_on' => $ticket->closed_on?->format('Y-m-d\TH:i:sP'),  
                ];
            });
    
            return $this->apiResponseService->ok($response_data, 'Tickets retrieved successfully.');
        } 
        catch (Throwable $e) 
        {
            Log::error('An error occurred while retrieving tickets', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.issue_type_ids' => 'required|array',
            'data.issue_type_ids.*' => 'required|uuid|exists:ticket_issue_types,id',
            'data.response_level' => 'required|string|exists:ticket_response_types,name',
            'data.stated_issue' => 'required|string',
            'data.location.stated_location' => 'required|string',
            'data.location.gps_location.latitude' => 'required|numeric',
            'data.location.gps_location.longitude' => 'required|numeric',
            'data.supportive_documents' => 'required|array',
            'data.supportive_documents.*.resource_type' => 'required|string',
            'data.supportive_documents.*.resource_name' => 'required|string',
            'data.supportive_documents.*.resource_size' => 'required|numeric',
            'data.supportive_documents.*.resource_content' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }
    
        try 
        {
            $ticket = $this->ticketService->create (
                $request->client['id'],
                [
                    'response_id' => TicketResponseTypeEnum::from($request->data['response_level'])->id(),
                    'stated_issue' => $request->data['stated_issue'],
                ],
                $request->data['issue_type_ids'],
                [
                    'stated_location' => $request->data['location']['stated_location'],
                    'latitude' => $request->data['location']['gps_location']['latitude'],
                    'longitude' => $request->data['location']['gps_location']['longitude'],
                ],
                $request->data['supportive_documents'],
            );
            
            $reponse_data = [
                'id' => $ticket->id,
                'issue_types' => $ticket->ticket_issues->map(function ($ticket_issue) {
                    return [
                        'id' => $ticket_issue->issue->id,
                        'name' => $ticket_issue->issue->name,
                        'service_level_agreement_duration_hour' => (string) $ticket_issue->issue->sla_hours,
                    ];
                }),
                'response_level' => $ticket->response->name,
                'status' => $ticket->status->name,
                'executive_summary' => '',
                'raised_on' => $ticket->raised_on->format('Y-m-d\TH:i:sP'),
                'status' => $ticket->status->name,
                'closed_on' => $ticket->closed_on?->format('Y-m-d\TH:i:sP'),
            ];
    
            return $this->apiResponseService->created($reponse_data, 'Ticket created successfully.');
    
        } 
        catch (Throwable $e) 
        {
            Log::error('An error occurred while creating ticket', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function show(Request $request, string $ticket_id)
    {
        $validator = Validator::make(['ticket_id' => $ticket_id], [
            'ticket_id' => 'required|uuid'
        ],
        [
            'ticket_id.required' => 'The member ID is required.',
            'ticket_id.uuid'     => 'Invalid member ID format.',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }
        
        try 
        {

            $response_data = $this->ticketService->details(
                $ticket_id,
                $request->client['id'],
                $request->client['role_id'],
            );

            
            
            return $this->apiResponseService->ok($response_data, 'Ticket details retrieved successfully.');
        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred while retrieving ticket details', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
    
    public function update(Request $request ,string $ticket_id)
    {

        $validator = Validator::make(array_merge(
            ['ticket_id' => $ticket_id],
            $request->all()
        ), [
            'ticket_id' => 'required|uuid',
            'data' => 'required|array',
            'data.issue_type' => 'required|array|min:1',
            'data.issue_type.*' => 'required|uuid|exists:ticket_issue_types,id',
            'data.status' => 'required|string|exists:ticket_status_types,name',
            'data.stated_issue' => 'required|string',
            'data.location' => 'required|array',
            'data.location.stated_location' => 'required|string',
            'data.location.gps_location' => 'required|array',
            'data.location.gps_location.latitude' => 'required|numeric|between:-90,90',
            'data.location.gps_location.longitude' => 'required|numeric|between:-180,180',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try
        {
            $this->ticketService->update(
                $ticket_id,
                $request->data['issue_type'],
                $request->data['status'],
                $request->data['stated_issue'],
                [
                    'stated_location' => $request->data['location']['stated_location'],
                    'latitude' => $request->data['location']['gps_location']['latitude'],
                    'longitude' => $request->data['location']['gps_location']['longitude'],
                ],
                $request->client['id'],
            );

            $response_data = $this->ticketService->details($ticket_id);
            
            return $this->apiResponseService->ok($response_data, 'Ticket updated successfully.');

        }
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while updating ticket', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }

    }

    public function evaluate_request(Request $request, string $ticket_id)
    {
        $validator = Validator::make(array_merge(
            ['ticket_id' => $ticket_id],
            $request->all()
        ), [
            'ticket_id' => 'required|uuid',
            'data' => 'required|array',
            'data.remark' => 'required|string',
            'data.supportive_documents' => 'nullable|array',
            'data.supportive_documents.*.resource_type' => 'required|string',
            'data.supportive_documents.*.resource_name' => 'required|string',
            'data.supportive_documents.*.resource_size' => 'required|numeric',
            'data.supportive_documents.*.resource_content' => 'required|string',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try 
        {
            $this->ticketService->evaluate_request(
                $request->client['id'],
                $ticket_id,
                $request->data['remark'],
                $request->input('data.supportive_documents'),
            );

            return $this->apiResponseService->created(null, 'Evaluation request submitted successfully.');
        } 
        catch(ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch(InvalidTicketStatusException $e)
        {
            return $this->apiResponseService->badRequest($e->getMessage());
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred while requesting ticket evaluation ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function evaluate(Request $request, string $ticket_id)
    {
        $validator = Validator::make(array_merge(
            ['ticket_id' => $ticket_id],
            $request->all()
        ), [
            'ticket_id' => 'required|uuid',
            'data' => 'required|array',
            'data.resolveToApprove' => 'required|boolean',
            'data.reason' => 'required|string',  
            'data.supportive_documents' => 'nullable|array',
            'data.supportive_documents.*.resource_type' => 'required|string',
            'data.supportive_documents.*.resource_name' => 'required|string',
            'data.supportive_documents.*.resource_size' => 'required|numeric',
            'data.supportive_documents.*.resource_content' => 'required|string',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }  

        try
        {
            $this->ticketService->evaluate(
                $request->client['id'],
                $ticket_id,
                $request->data['resolveToApprove'],
                $request->data['reason'],
                $request->input('data.supportive_documents'),
            );

            return $this->apiResponseService->created(null, 'Ticket evaluation recorded successfully.');
        }
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch (InvalidTicketStatusException $e)
        {
            return $this->apiResponseService->badRequest($e->getMessage());
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while evaluating ticket', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function close(Request $request, string $ticket_id)
    {
        $validator = Validator::make(array_merge(
            ['ticket_id' => $ticket_id],
            $request->all()
        ), [
            'ticket_id' => 'required|uuid',
            'data' => 'required|array',
            'data.reason' => 'nullable|string',    
            'data.supportive_documents' => 'nullable|array',
            'data.supportive_documents.*.resource_type' => 'required|string',
            'data.supportive_documents.*.resource_name' => 'required|string',
            'data.supportive_documents.*.resource_size' => 'required|numeric',
            'data.supportive_documents.*.resource_content' => 'required|string',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }  
        
        try
        {
            $this->ticketService->close(
                $ticket_id,
                $request->client['id'],
                $request->client['role_id'],
                $request->input('data.reason'),
                $request->input('data.supportive_documents'),
            );

            return $this->apiResponseService->created(null, 'Ticket has been closed successfully.');
        }
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not Found.');
        }
        catch (InvalidTicketStatusException $e)
        {
            return $this->apiResponseService->badRequest($e->getMessage());
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while closing ticket', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function force_close(Request $request, string $ticket_id)
    {
        $validator = Validator::make(array_merge(
            ['ticket_id' => $ticket_id],
            $request->all()
        ), [
            'ticket_id' => 'required|uuid',
            'data' => 'required|array',
            'data.reason' => 'required|string',    
            'data.supportive_documents' => 'nullable|array',
            'data.supportive_documents.*.resource_type' => 'required|string',
            'data.supportive_documents.*.resource_name' => 'required|string',
            'data.supportive_documents.*.resource_size' => 'required|numeric',
            'data.supportive_documents.*.resource_content' => 'required|string',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        } 

        try
        {
            $this->ticketService->force_close(
                $ticket_id,
                $request->client['id'],
                $request->data['reason'],
                $request->input('data.supportive_documents'),
            );
            return $this->apiResponseService->created(null, 'The ticket has been successfully forced closed.');
        }
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch (InvalidTicketStatusException $e)
        {
            return $this->apiResponseService->badRequest($e->getMessage());
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while force-closing the ticket', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function print_view(Request $request, string $ticket_id)
    {
        $validator = Validator::make(['ticket_id' => $ticket_id], 
        [
            'ticket_id' => 'required|uuid',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        } 

        try
        {
            $ticket = Ticket::with([
                'assessed',
                'status',
                'issuer',
                'ticket_issues.issue',
                'response',
                'location',
                'evaluated',
                'documents',
                'logs.issuer',
                'logs.type',
                'logs.documents',
            ])->findOrFail($ticket_id);

            $requester = Member::find($request->client['id']);

            $print_view = $this->printViewReport->generate(
                $ticket,
                $requester,
            );
            
            return $this->apiResponseService->raw($print_view, 'application/pdf', 'ticket-print.pdf');
        }
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while retrieving and generating the ticket print view', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}