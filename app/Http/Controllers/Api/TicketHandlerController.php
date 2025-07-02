<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Exceptions\IssueNotFoundException;

use App\Models\Ticket;

use App\Services\ApiResponseService;
use App\Services\TicketService;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Throwable;

class TicketHandlerController extends Controller
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
        protected TicketService $ticketService,
    ) {} 

    public function index(string $ticket_id)
    {
        
        $validator = Validator::make(['ticket_id' => $ticket_id], [
            'ticket_id' => 'required|uuid'
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try
        {
            $ticket = Ticket::with(
                'ticket_issues.maintainers.specialities',
                'ticket_issues.maintainers.capabilities', 
            )->findOrFail($ticket_id);

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
                                'service_level_agreement_duration_hour' => $specialty->sla_hours,
                            ];
                        }),
                        'capabilities' => $maintainer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                        'member_since' => $maintainer->member_since->format('Y-m-d\TH:i:sP'),
                        'member_until' => $maintainer->member_until->format('Y-m-d\TH:i:sP'),
                    ];
                });
            });

            return $this->apiResponseService->ok($response_data, 'Ticket handlers retrieved successfully.');
        }
        catch(ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while retrieving ticket handlers',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }

    public function store(Request $request, string $ticket_id)
    {
        $validator = Validator::make(array_merge(
            ['ticket_id' => $ticket_id],
            $request->all(),
        ), [
            'ticket_id' => 'required|uuid',
            'data' => 'required|array',
            'data.*.appointed_member_ids' => 'required|array',
            'data.*.appointed_member_ids.*' => 'uuid|exists:members,id',
            'data.*.work_description' => 'required|string',
            'data.*.issue_type' => 'required|uuid|exists:ticket_issue_types,id',
            'data.*.supportive_documents' => 'nullable|array',
            'data.*.supportive_documents.*.resource_type' => 'required|string',
            'data.*.supportive_documents.*.resource_name' => 'required|string',
            'data.*.supportive_documents.*.resource_size' => 'required|numeric',
            'data.*.supportive_documents.*.resource_content' => 'required|string',
        ]);

        if($validator->fails())
        {
            return $this->apiResponseService->badRequest('Validation failed.', $validator->errors());
        }

        try 
        {
            $ticket = Ticket::with('ticket_issues.issue')->findOrFail($ticket_id);

            
            $ticket_logs = [];

            foreach ($request->input('data') as $assign) 
            {
                $ticket_log = $this->ticketService->assign_handlers(
                    $ticket,
                    $assign['issue_type'],
                    $assign['appointed_member_ids'],
                    $assign['work_description'],
                    $assign['supportive_documents'],
                    $request->client['id'],
                );

                $ticket_logs[] = $ticket_log;
            }

            $response_data = collect($ticket_logs)->map(function ($ticket_log) {
                return [
                    'id' => $ticket_log->id,
                    'owning_ticket_id' => $ticket_log->ticket_id,
                    'type' => $ticket_log->type->name,
                    'issuer' => [
                        'id' => $ticket_log->issuer->id,
                        'name' => $ticket_log->issuer->name,
                        'role' => $ticket_log->issuer->role->name,
                        'title' => $ticket_log->issuer->title,
                        'specialties' => $ticket_log->issuer->specialities->map(function ($specialty){
                            return [
                                'id' => $specialty->id,
                                'name' => $specialty->name,
                                'service_level_agreement_duration_hour' => (string) $specialty->sla_hours,
                            ]; 
                        }),
                        'capabilities' => $ticket_log->issuer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                        'member_since' => now()->format('Y-m-d\TH:i:sP'),
                    ],
                    'recorded_on' => $ticket_log->recorded_on->format('Y-m-d\TH:i:sP'),
                    'news' => $ticket_log->news,
                    'attachments' => $ticket_log->documents->map(function ($document) {
                        return [
                            'id' => $document->id,
                            'resource_type' => $document->resource_type,
                            'resource_name' => $document->resource_name,
                            'resource_size' => $document->resource_size,
                            'previewable_on' => $document->previewable_on,
                        ];
                    }),
                    'actionable' => [
                        'genus' => 'SEGUE',
                        'species' => 'TICKET',
                        'segue_destination' => 'whatever',
                    ],
                ];
            });


            return $this->apiResponseService->ok($response_data, 'Successfully assigned the handlers to the ticket.');

        } 
        catch (ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch (IssueNotFoundException $e)
        {
            return $this->apiResponseService->badRequest($e->getMessage());
        }
        catch (Throwable $e) 
        {
            Log::error('An error occurred while assigning ticket handlers', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
