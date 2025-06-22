<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TicketHandlerController extends Controller
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
                                'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                            ];
                        }),
                        'capabilities' => $maintainer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                    ];
                });
            });

            return $this->apiResponseService->ok($response_data, 'Ticket handlers retrieved successfully');
        }
        catch (Throwable $e)
        {
            Log::error('Error retrieving ticket handlers',  [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while retrieving ticket handlers');
        }
    }

    public function store(Request $_request, string $_ticketId)
    {
        if (!Str::isUuid($_ticketId)) {
            return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
        }

        $data = $_request->input('data');

        if (!$data || !is_array($data)) {
            return $this->apiResponseService->badRequest('Missing or invalid data payload');
        }

        $validator = Validator::make($_request->all(), [
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

        if ($validator->fails()) {
            return $this->apiResponseService->unprocessableEntity(
                'Validation failed, please check the provided data',
                $validator->errors()
            );
        }

        try {
            $ticket = Ticket::find($_ticketId);
            $member_id = $_request->input('jwt_payload')['member_id'];
            $member_role_id = $_request->input('jwt_payload')['member_role_id'];
            $member = Member::find($member_id);

        

            if($member_role_id != MemberRoleEnum::MANAGEMENT->id())
            {
                $hasAssign = collect($member->capabilities)->contains('id', MemberCapabilityEnum::INVITE->id());

                if (!$hasAssign) {
                    return $this->apiResponseService->forbidden('You do not have permission to perform this action.');
                }
            }

            if (!$ticket) {
                return $this->apiResponseService->notFound('Ticket not found or invalid ticket ID');
            }

            foreach ($data as $item) {
                $work_description = $item['work_description'];
                $appointed_member_ids = $item['appointed_member_ids'];
                $issue_type_id = $item['issue_type'];

                $ticket_issue = $ticket->ticket_issues->firstWhere('issue_id', $issue_type_id);

                if (!$ticket_issue) {
                    return $this->apiResponseService->unprocessableEntity("Ticket issue with ID $issue_type_id not found.");
                }

                $ticket_issue->update([
                    'work_description' => $work_description,
                ]);

                $ticket_issue->maintainers()->sync($appointed_member_ids);

                $ticket_log = TicketLog::create([
                    'ticket_id' => $_ticketId,
                    'member_id' => $member_id,
                    'type_id' => TicketLogTypeEnum::INVITATION->id(),
                    'news' => count($appointed_member_ids) . " maintainer(s) assigned to ticket issue $issue_type_id.",
                ]);

                $wo_id = Str::uuid();

                $work_order_data = [
                    'header' => [
                        'work_order_id' => 'WO-'. substr($wo_id, -5),
                        'area_name' => SystemSetting::get('area_name') ?? 'Area name not set yet',
                        'date' => now()->translatedFormat('l, d F Y'),
                    ],
                    'to_perform' => [
                        'work_type' => TicketIssueType::find($issue_type_id)->name,
                        'response_level' => $ticket->response->name,
                        'location' => $ticket->location->stated_location,
                        'as_a_follow_up_for' => $ticket->id,
                        'work_directive' => $work_description,
                    ],
                    'upon_the_request_of' => array_merge(
                        Arr::except($ticket->issuer->toArray(), ['id', 'role_id', 'member_since', 'member_until', 'title']),
                        ['on' => $ticket->raised_on, 'name' => $ticket->issuer->name . ' (' . substr($ticket->issuer->id, -5) . ')']
                    ),
                    'to be carried out by' => Member::whereIn('id', $appointed_member_ids)
                        ->get()
                        ->map(function ($member) {
                            return $member->only(['name', 'title']); 
                    }),
                ];
                    
                $pdf = Pdf::loadView('pdf.work_order', ['work_order_data' => $work_order_data])->setPaper('a4', 'portrait')->output();

                $wo_path = $this->storageService->storeLogTicketDocument(base64_encode($pdf), 'work_order.pdf', $ticket_log->id);

                TicketLogDocument::create([
                    'log_id' => $ticket_log->id,
                    'resource_type' => 'pdf',
                    'resource_name' => $wo_id,
                    'resource_size' => '123',
                    'previewable_on' => $wo_path,
                ]);

                $documents = $item['supportive_documents'] ?? [];

                foreach ($documents as $document) {
                    $filePath = $this->storageService->storeLogTicketDocument(
                        $document['resource_content'],
                        $document['resource_name'],
                        $ticket_log->id
                    );

                    TicketLogDocument::create([
                        'log_id' => $ticket_log->id,
                        'resource_type' => $document['resource_type'],
                        'resource_name' => $document['resource_name'],
                        'resource_size' => $document['resource_size'],
                        'previewable_on' => $filePath,
                    ]);
                }                
            }

            $updated_ticket = Ticket::find($_ticketId);

            $response_data = $updated_ticket->ticket_issues->flatMap(function ($issue) {
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
                                'service_level_agreement_duration_hour' => $specialty->sla_duration_hour ?? 'Not assigned yet',
                            ];
                        }),
                        'capabilities' => $maintainer->capabilities->map(fn($capability) => $capability->name),
                    ];
                });
            });

            return $this->apiResponseService->ok($response_data, 'Successfully added handlers to the ticket');

        } 
        catch (Throwable $e) {
            Log::error('Error assigning ticket handlers', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('An error occurred while assigning ticket handlers');
        }
    }
}
