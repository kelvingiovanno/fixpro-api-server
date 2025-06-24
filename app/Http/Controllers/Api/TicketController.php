<?php

namespace App\Http\Controllers\Api;

use App\Enums\IssueTypeEnum;
use App\Http\Controllers\Controller;

use App\Enums\MemberCapabilityEnum;
use App\Enums\MemberRoleEnum;
use App\Enums\TicketResponseTypeEnum;
use App\Enums\TicketLogTypeEnum;
use App\Enums\TicketStatusEnum;
use App\Models\Enums\MemberRole;
use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketStatusType;
use App\Models\Ticket;
use App\Models\Location;
use App\Models\Member;
use App\Models\SystemSetting;
use App\Models\TicketDocument;
use App\Models\WODocument;
use App\Models\TicketIssue;
use App\Models\TicketLog;
use App\Models\TicketLogDocument;

use App\Services\ApiResponseService;
use App\Services\StorageService;
use App\Services\TicketService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use Throwable;

class TicketController extends Controller   
{
    public function __construct (
        protected ApiResponseService $apiResponseService, 
        protected StorageService $storageService,
        protected TicketService $ticketService,
    ) { }

    public function index()
    {
        try 
        {
            $tickets = Ticket::with(['ticket_issues.issue', 'status', 'response'])->get();
            
            $response_data = $tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'issue_types' => $ticket->ticket_issues->map(function ($ticket_issue) {
                        return [
                            'id' => $ticket_issue->issue->id,
                            'name' => $ticket_issue->issue->name,
                            'service_level_agreement_duration_hour' => $ticket_issue->issue->sla_hours,
                        ];
                    }),
                    'response_level' => $ticket->response->name, 
                    'raised_on' => $ticket->raised_on,
                    'status' => $ticket->status->name,  
                    'closed_on' => $ticket->closed_on,  
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
            'data.executive_summary' => 'required|string',
            'data.location.stated_location' => 'required|string',
            'data.location.gps_location.latitude' => 'required|numeric',
            'data.location.gps_location.longitude' => 'required|numeric',
            'data.supportive_documents' => 'nullable|array',
            'data.supportive_documents.*.resource_type' => 'required_with:supportive_documents|string',
            'data.supportive_documents.*.resource_name' => 'required_with:supportive_documents|string',
            'data.supportive_documents.*.resource_size' => 'required_with:supportive_documents|numeric',
            'data.supportive_documents.*.resource_content' => 'required_with:supportive_documents|string',
        ]);
    
        if ($validator->fails()) {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }
    
        try 
        {
            $ticket = $this->ticketService->create (
                $request->client['id'],
                [
                    'response_id' => TicketResponseTypeEnum::from($request->data['response_level']),
                    'stated_issue' => $request->data['stated_issue'],
                    'executive_summary' => $request->data['executive_summary'],
                ],
                $request->data['issue_type_ids'],
                [
                    'stated_location' => $request->data['stated_location'],
                    'latitude' => $request->data['gps_location']['latitude'],
                    'longitude' => $request->data['gps_location']['longitude'],
                ],
                $request->data['supportive_documents'],
            );
            
            $reponse_data = [
                'id' => $ticket->id,
                'issue_types' => $ticket->ticket_issues->map(function ($ticket_issue) {
                    return [
                        'id' => $ticket_issue->issue->id,
                        'name' => $ticket_issue->issue->name,
                        'service_level_agreement_duration_hour' => $ticket_issue->issue->sla_hours,
                    ];
                }),
                'response_level' => $ticket->response->name,
                'raised_on' => $ticket->raised_on,
                'status' => $ticket->status->name,
                'executive_summary' =>  $ticket->executive_summary,
                'closed_on' => $ticket->closed_on,
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

    public function show(string $ticket_id)
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
            $ticket = Ticket::findOrFail($ticket_id);

            $response_data = $this->ticketService->details($ticket);
            
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
            'data.executive_summary' => 'required|string',
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
            $updated_ticket = $this->ticketService->update(
                $ticket_id,
                $request->data['issue_type'],
                $request->data['status'],
                $request->data['statstated_issueus'],
                $request->data['executive_summary'],
                [
                    'stated_location' => $request->data['stated_location'],
                    'stated_location' => $request->data['gps_location']['latitude'],
                    'stated_location' => $request->data['gps_location']['longitude'],
                ],
            );

            $response_data = $this->ticketService->details($updated_ticket);
            
            return $this->apiResponseService->ok($response_data, 'Ticket updated successfully.');

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
            'data.supportive_documents.*.resource_type' => 'required_with:supportive_documents|string',
            'data.supportive_documents.*.resource_name' => 'required_with:supportive_documents|string',
            'data.supportive_documents.*.resource_size' => 'required_with:supportive_documents|numeric',
            'data.supportive_documents.*.resource_content' => 'required_with:supportive_documents|string',
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
                $request->data['supportive_documents'],
            );

            return $this->apiResponseService->created('Evaluation request submitted successfully');
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
            Log::error('Error submitting the evaluation request.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while submitting the evaluation request.');
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
            'data.issue_type' => 'required|uuid|exists:ticket_issue_types,id',
            'data.reason' => 'required|string',    
            'data.supportive_documents.*.resource_type' => 'required_with:supportive_documents|string',
            'data.supportive_documents.*.resource_name' => 'required_with:supportive_documents|string',
            'data.supportive_documents.*.resource_size' => 'required_with:supportive_documents|numeric',
            'data.supportive_documents.*.resource_content' => 'required_with:supportive_documents|string',
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
                $request->data['supportive_documents'],
            );

            return $this->apiResponseService->created('Ticket evaluation recorded successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('Error evaluating ticket.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An unexpected error occurred while processing the ticket evaluation.');
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
            'data.reason' => 'required|string',    
            'data.supportive_documents.*.resource_type' => 'required_with:supportive_documents|string',
            'data.supportive_documents.*.resource_name' => 'required_with:supportive_documents|string',
            'data.supportive_documents.*.resource_size' => 'required_with:supportive_documents|numeric',
            'data.supportive_documents.*.resource_content' => 'required_with:supportive_documents|string',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }  
        
        try
        {
            $ticket = Ticket::find($_ticketId);

            if(!$ticket)
            {
                return $this->apiResponseService->notFound('The ticket not found.');
            }

            if($ticket->status->id != TicketStatusEnum::IN_ASSESSMENT->id() &&  $ticket->status->id != TicketStatusEnum::OPEN->id())
            {
                return $this->apiResponseService->forbidden('Action not allowed for the current ticket status.');
            }

            if($ticket->issuer->id == $member_id)
            {
                $ticket->update([
                    'status_id' => TicketStatusEnum::CANCELLED->id(),
                ]);
            }
            else if ($member_role_id == MemberRoleEnum::MANAGEMENT->id())
            {
                $ticket->update([
                    'status_id' => TicketStatusEnum::REJECTED->id(),
                ]);
            }
            else {
                return $this->apiResponseService->forbidden('You are not authorized to perform this action.');
            }

            $ticket_log = $ticket->logs()->create([
                'member_id' => $member_id,
                'type_id' => TicketLogTypeEnum::REJECTION->id(),
                'news' => $data['reason'],
            ]);

            $documents = $data['supportive_documents'] ?? [];

            foreach ($documents as $document) {
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['resource_content'],
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

            return $this->apiResponseService->created('Ticket has been closed successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('Error closing ticket.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An unexpected error occurred while closing the ticket.');
        }
    }

    public function force_close(Request $_request, string $_ticketId)
    {
        if(!Str::isUuid($_ticketId))
        {
            return $this->apiResponseService->notFound('The ticket not found');
        }

        $data = $_request->input('data');

        if(!$data)
        {
            return $this->apiResponseService->badRequest('Missing required data payload');
        }

        $validator = Validator::make($data, [
            'reason' => 'required|string',    
            'supportive_documents.*.resource_type' => 'required_with:supportive_documents|string',
            'supportive_documents.*.resource_name' => 'required_with:supportive_documents|string',
            'supportive_documents.*.resource_size' => 'required_with:supportive_documents|numeric',
            'supportive_documents.*.resource_content' => 'required_with:supportive_documents|string',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->unprocessableEntity('Validation failed', $validator->errors());
        }

        $member_id = $_request->input('jwt_payload')['member_id'];
        $member_role_id =$_request->input('jwt_payload')['role_id'];

        try
        {
            $ticket = Ticket::find($_ticketId);

            if(!$ticket)
            {
                return $this->apiResponseService->notFound('The ticket not found.');
            }

            if($member_role_id != MemberRoleEnum::MANAGEMENT->id())
            {
                return $this->apiResponseService->forbidden('You are not authorized to perform this action.');
            }
            
            $ticket->update([
                'status_id' => TicketStatusEnum::CLOSED->id(),
            ]);

            $ticket_log = $ticket->logs()->create([
                'member_id' => $member_id,
                'type_id' => TicketLogTypeEnum::FORCE_CLOSURE->id(),
                'news' => $data['reason'],
            ]);

            $documents = $data['supportive_documents'] ?? [];

            foreach ($documents as $document) {
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['resource_content'],
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

            return $this->apiResponseService->created('Ticket has been closed successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('Error closing ticket.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An unexpected error occurred while closing the ticket.');
        }
    }

    public function print_view(Request $_request, string $_ticketId)
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

            $member_id = $_request->input('jwt_payload')['member_id'];

            $print_view_data = [
                'header' => [
                    'document_id' => 'TK-' . substr(Str::uuid(), -5),
                    'area_name' => SystemSetting::get('area_name') ?? 'Area name not set yet',
                    'date' => now()->translatedFormat('l, d F Y'),
                ],
                'details' => [
                    'ticket_no' => substr($ticket->id, -5),
                    'created_at' => $ticket->raised_on,
                    'completed_at' => $ticket->closed_on ?? 'Not completed',
                    'assessed_by' => $ticket->assessed->name ?? 'Not assessed', 
                    'ticket_status' => $ticket->status->name,
                    'requester_name' => $ticket->issuer->name,
                    'identifier_no' => substr($ticket->issuer->id, -5),
                    'work_type' => $ticket->ticket_issues->map(function ($ticket_issue) {
                        return $ticket_issue->issue->name;
                    }),
                    'handling_priority' => $ticket->response->name,
                    'location' => $ticket->location->stated_location,
                    'evaluated_by' => $ticket->evaluated->name ?? 'Not evaluated',
                ],
                'complaints' => $ticket->stated_issue,
                'supportive_documents' => $ticket->documents->map(function ($document){
                    return $document->previewable_on;
                }),
                'logs' => $ticket->logs->values()->map(function ($log, $index) {
                    return [
                        'name' => $log->issuer->name,
                        'id_number' => substr($log->issuer->id, -5),
                        'log_type' => $log->type->name,
                        'raised_on' => $log->recorded_on,
                        'news' => $log->news,
                        'supportive_documents' => $log->documents->map(function ($document) {
                            return $document->previewable_on;
                        }),
                    ];
                }),
                'print_view_requested_by' => Member::find($member_id)->name,
            ];

            $ticket_print_view = Pdf::loadView('pdf.ticket_print_view',$print_view_data)->setPaper('a4', 'portrait')->output();
            
            $this->storageService->storeWoDocument(base64_encode($ticket_print_view), 'ticket_print_view.pdf', 'testing');

            return $this->apiResponseService->ok('ok');
        }
        catch (Throwable $e)
        {
            Log::error('', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('');
        }
    }
}