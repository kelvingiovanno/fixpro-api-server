<?php

namespace App\Http\Controllers;

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
                'maintainers.specialities',
                'maintainers.capabilities', 
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

            foreach ($request->input('data') as $assign) {
                
                $this->ticketService->assign_handlers(
                    $ticket,
                    $assign['issue_type'],
                    $assign['appointed_member_ids'],
                    $assign['work_description'],
                    $assign['supportive_documents'],
                    $request->client['id'],
                );
            };

            $ticket->load([
                'ticket_issues.issue',
                'ticket_issues.maintainers.specialities',
                'ticket_issues.maintainers.capabilities',
                'ticket_issues.maintainers.role',
            ]);

            $response_data = $ticket->ticket_issues->flatMap(function ($issue) {
                return $issue->maintainers->map(function ($maintainer) {
                    return [
                        'id' => $maintainer->id,
                        'name' => $maintainer->name,
                        'role' => $maintainer->role,
                        'title' => $maintainer->title,
                        'specialties' => $maintainer->specialities->map(function ($specialty) {
                            return [
                                'id' => $specialty->id,
                                'name' => $specialty->name,
                                'service_level_agreement_duration_hour' => $specialty->sla_hours ,
                            ];
                        }),
                        'capabilities' => $maintainer->capabilities->map(fn($capability) => $capability->name),
                    ];
                });
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
