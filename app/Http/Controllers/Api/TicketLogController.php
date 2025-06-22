<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TicketLogController extends Controller
{
    public function index(string $_ticketId)
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
                                'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                            ];
                        }),
                        'capabilities' => $log->issuer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                    ],
                    'recorded_on' => $log->recorded_on,
                    'news' => $log->news,
                    'attachments' => $log->documents,
                ];
            });

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

    public function store(Request $_request, string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID'); 
        }

        $data = $_request->input('data');

        if (!$data) 
        {
            return $this->apiResponseService->badRequest('Missing required data payload');
        }
        
        $validator = Validator::make($data, [
            'type' => 'required|string|exists:ticket_log_types,name',
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

        try
        {
            $ticket = Ticket::find($_ticketId);
            
            if(!$ticket)
            {
                return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
            }

            $member_id = $_request->input('jwt_payload')['member_id'];
            $member_role_id = $_request->input('jwt_payload')['member_role_id'];

            $log_type_id = TicketLogTypeEnum::idFromName($data['type']);
            
            $ticket_log = TicketLog::create([
                'ticket_id' => $_ticketId,
                'member_id' => $member_id,
                'type_id' => $log_type_id,
                'news' => $data['news'],
            ]);
            
            $statusMap = [
                TicketLogTypeEnum::ASSESSMENT->id() => TicketStatusEnum::IN_ASSESSMENT->id(),
                TicketLogTypeEnum::INVITATION->id() => TicketStatusEnum::ON_PROGRESS->id(),
                TicketLogTypeEnum::WORK_PROGRESS->id() => TicketStatusEnum::ON_PROGRESS->id(),  
                TicketLogTypeEnum::WORK_EVALUATION_REQUEST->id() => TicketStatusEnum::WORK_EVALUATION->id(),
                TicketLogTypeEnum::WORK_EVALUATION->id() => TicketStatusEnum::QUIALITY_CONTROL->id(),
                TicketLogTypeEnum::OWNER_EVALUATION_REQUEST->id() => TicketStatusEnum::OWNER_EVALUATION->id(),
            ];

            if (isset($statusMap[$log_type_id])) {
                
                if($log_type_id == TicketLogTypeEnum::ASSESSMENT->id())
                {

                    if($member_role_id != MemberRoleEnum::MANAGEMENT->id())
                    {
                        return $this->apiResponseService->forbidden('You are not authorized to perform this action.');
                    }

                    $ticket->update([
                        'status_id' => $statusMap[$log_type_id],
                        'assessed_by' => $member_id,
                    ]);
                }
                
                $ticket->update(['status_id' => $statusMap[$log_type_id]]);
            }
                
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
                'recorded_on' => $ticket_log->recorded_on,
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
}
