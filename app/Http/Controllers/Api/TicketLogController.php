<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

use App\Services\ApiResponseService;
use App\Services\TicketService;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Throwable;
use ValueError;

class TicketLogController extends Controller
{

    public function __construct(
        protected ApiResponseService $apiResponseService,
        protected TicketService $ticketService,
    ){}

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
            $ticket = Ticket::with([
                'logs.type',
                'logs.issuer.role',
                'logs.issuer.specialities',
                'logs.issuer.capabilities',
                'logs.documents',
            ])->findOrFail($ticket_id);
            
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
                                'service_level_agreement_duration_hour' => $specialty->sla_hours,
                            ];
                        }),
                        'capabilities' => $log->issuer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                    ],
                    'recorded_on' => $log->recorded_on->format('Y-m-d\TH:i:sP'),
                    'news' => $log->news,
                    'attachments' => $log->documents,
                ];
            });

            return $this->apiResponseService->ok($response_data, 'Ticket logs retrieved successfully.');
        }
        catch(ModelNotFoundException) 
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while retrieving ticket logs',  [
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
        $validator = Validator::make(
            array_merge($request->all(),['ticket_id' => $ticket_id]
        ),
        [
            'ticket_id' => 'required|uuid',
            'data.type' => 'required|string',
            'data.news' => 'required|string',
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
            $ticket_log = $this->ticketService->add_log(
                $ticket_id,
                $request->data['type'],
                $request->data['news'],
                $request->data['documents'],
                $request->client['id'],
            );

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
                'recorded_on' => $ticket_log->recorded_on->format('Y-m-d\TH:i:sP'),
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

            return $this->apiResponseService->created($reponse_data, 'Ticket log created successfully.');
        }
        catch(ModelNotFoundException)
        {
            return $this->apiResponseService->notFound('Ticket not found.');
        }
        catch(ValueError)
        {
            return $this->apiResponseService->badRequest('Invalid log type.');
        }
        catch (Throwable $e)
        {
            Log::error('An error occurred while creating ticket log',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
