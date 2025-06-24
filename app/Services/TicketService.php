<?php

namespace App\Services;

use App\Enums\MemberRoleEnum;
use App\Enums\TicketLogTypeEnum;
use App\Enums\TicketStatusEnum;
use App\Exceptions\InvalidTicketStatusException;
use App\Models\Member;
use App\Models\Ticket;
use App\Models\TicketLogDocument;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct (
        protected ReportService $reportService,
        protected StorageService $storageService,
    ) { }

    public function create(
        string $owner_id,
        array $ticket,
        array $issues,
        array $location,
        array $documents, 
        
    ) {

        $created_ticket = DB::transaction(function () 
            use ($owner_id, $ticket, $issues, $location, $documents) 
        {
            
        
            $ticket = Ticket::create([
                'member_id' => $owner_id,
                'status_id' => TicketStatusEnum::OPEN->id(),
                'response_id' => $ticket['response_level'],
                'executive_summary' => $ticket['executive_summary'],
            ]);

            $ticket_log = $ticket->logs()->create([
                'member_id' => $owner_id,
                'type_id' => TicketLogTypeEnum::ACTIVITY->id(),
                'news' => 'Ticket has been created.'
            ]);

            foreach($issues as $issue)
            {
                $ticket->ticket_issues()->create([
                    'issue_id' => $issue,
                ]);
            }

            $ticket->location()->create([
                'stated_location' => $location['stated_location'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        
            foreach($documents as &$document)
            {
                $document_path = $this->storageService->storeTicketDocument(
                    $document['previewable_on'],
                    'service_form.pdf', 
                    $ticket->id
                );

                $document['previewable_on'] = $document_path;

                $ticket->documents()->create([
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => $document['resource_size'],
                    'previewable_on' => $document['previewable_on'],
                ]);
            }

            $ticket->load('issuer', 'ticket_issues', 'status', 'response');

            $service_form_pdf = $this->reportService->serviceForm (
                $ticket,
                $ticket->issuer,
                $issues,
                $location,
                $documents
            );

            $service_form_path = $this->storageService->storeLogTicketDocument(
                base64_encode($service_form_pdf), 
                'service_form.pdf', 
                $ticket_log->id
            );

            $ticket_log->documents()->create([
                'resource_type' => 'pdf',
                'resource_name' => 'service_form-' . substr($ticket->id, -5),
                'resource_size' => '',
                'previewable_on' => $service_form_path,
            ]);

            return $ticket;

        });

        return $created_ticket;

    }

    public function details(Ticket $ticket)
    {
        $ticket->load([
            'issuer.role',
            'issuer.specialties',
            'issuer.capabilities',
            'ticket_issues.maintainers',
            'ticket_issues.maintainers.role',
            'ticket_issues.maintainers.specialties',
            'ticket_issues.maintainers.capabilities',
            'location',
            'documents',
            'status',
            'response',
            'logs.type',
            'logs.issuer',
            'logs.issuer.role',
            'logs.issuer.specialties',
            'logs.issuer.capabilities',
            'logs.documents',
        ]);

        $details_data = [
            'id' => $ticket->id,
            'issue_type' => $ticket->ticket_issues->map(function ($ticket_issue) {
                return [
                    'id' => $ticket_issue->issue->id,
                    'name' => $ticket_issue->issue->name,
                    'service_level_agreement_duration_hour' => $ticket_issue->issue->sla_hours,
                ];
            }),
            'response_level' => $ticket->response->name,
            'raised_on' => $ticket->raised_on,
            'status' => $ticket->status->name,
            'executive_summary' => $ticket->executive_summary,
            'location' => [
                'stated_location' => $ticket->location->stated_location,
                'gps_location' => [
                    'latitude' => $ticket->location->latitude,
                    'longitude' => $ticket->location->longitude
                ],
            ],
            'supportive_documents' => $ticket->documents,
            'issuer' => [
                'id' => $ticket->issuer->id,
                'name' => $ticket->issuer->name,
                'role' => $ticket->issuer->role->name,
                'title' => $ticket->issuer->title,
                'specialties' => $ticket->issuer->specialities->map(function ($specialty){
                    return [
                        'id' => $specialty->id,
                        'name' => $specialty->name,
                        'service_level_agreement_duration_hour' => $specialty->sla_hours,
                    ];
                }),
                'capabilities' => $ticket->issuer->capabilities->map(function ($capability) {
                    return $capability->name;
                }),
            ],
            'logs' => $ticket->logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'owning_ticket_id' => $log->ticket_id,
                    'type' => $log->type->name,
                    'issuer' => [
                        'id' => $log->issuer->id,
                        'name' => $log->issuer->name,
                        'role' => $log->issuer->role->name,
                        'title' => $log->issuer->title,
                        'specialties' => $log->issuer->specialities->map(function ($specialty){
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
                    'recorded_on' => $log->recorded_on,
                    'news' => $log->news,
                    'attachments' => $log->documents,
                ];
            }),
            'handlers' => $ticket->ticket_issues->flatMap(function ($issue) {
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
                    ];
                });
            }),
            'closed_on' => $ticket->closed_on,
        ];

        return $details_data;
    }

    public function update(
        string $ticket_id,
        array $issue_ids,
        string $status,
        string $stated_issue,
        string $executive_summary,
        array $location 
    ) {
        $updated_ticket = DB::transaction(function () 
            use ($ticket_id, $issue_ids, $status, $stated_issue, $executive_summary, $location) 
        {
            
            $ticket = Ticket::findOrFail($ticket_id);

            $ticket->update([
                'status_id' => TicketStatusEnum::from($status['status'])->id(),
                'stated_issue' => $stated_issue,
                'executive_summary' => $executive_summary,
            ]);
            
            $ticket->location()->update([
                'stated_location' => $location['stated_location'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);


            foreach ($issue_ids as $issue_id) 
            {
                $exists = $ticket->ticket_issues()
                    ->where('issue_id', $issue_id)
                    ->exists();

                if (! $exists) {
                    $ticket->ticket_issues()->create([
                        'issue_id' => $issue_id,
                    ]);
                }
            }
            return $ticket;
        });

        return $updated_ticket;
    }

    public function evaluate_request(
        string $requester_id,
        string $ticket_id,
        string $remark,
        array $documents = []
    ) {

        DB::transaction(function () 
            use ($requester_id, $ticket_id, $remark, $documents)
        {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($ticket->status->id != TicketStatusEnum::ON_PROGRESS->id()) {
                throw new InvalidTicketStatusException('Ticket is not in progress.');
            }

            $ticket->update(['status_id' => TicketStatusEnum::WORK_EVALUATION->id()]);

            $ticket_log = $ticket->logs()->create([
                'member_id' => $requester_id,
                'type_id' => TicketLogTypeEnum::WORK_EVALUATION_REQUEST->id(),
                'news' => $remark,
            ]);

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
        });
    }
    
    public function evaluate(
        string $evaluated_by,
        string $ticket_id,
        bool $approved,
        string $reason,
        array $documents = [],

    ) {
        DB::transaction(function () 
            use ($evaluated_by ,$ticket_id, $approved, $reason, $documents) {
            
            $ticket = Ticket::findOrFail($ticket_id);

            $ticket_log = null;

            if($approved)
            {
                $ticket->update([
                    'status_id' => TicketStatusEnum::ON_PROGRESS->id(),
                ]);

                $ticket_log = $ticket->logs()->create([
                    'member_id' => $evaluated_by,
                    'type_id' => TicketLogTypeEnum::REJECTION->id(),
                    'news' => $reason,
                ]);
            }
            else 
            {
                if($ticket->status->id == TicketStatusEnum::WORK_EVALUATION->id())
                {
                    $ticket->update([
                        'status_id' => TicketStatusEnum::QUIALITY_CONTROL->id(),
                        'evaluated_by' => $evaluated_by,   
                    ]);

                    $ticket_log = $ticket->logs()->create([
                        'member_id' => $evaluated_by,
                        'type_id' => TicketLogTypeEnum::WORK_EVALUATION->id(),
                        'news' => $reason,
                    ]);

                }
                else if($ticket->status->id == TicketStatusEnum::QUIALITY_CONTROL->id())
                {
                    $ticket->update([
                        'status_id' => TicketStatusEnum::OWNER_EVALUATION->id(),
                    ]);

                    $ticket_log = $ticket->logs()->create([
                        'member_id' => $evaluated_by,
                        'type_id' => TicketLogTypeEnum::OWNER_EVALUATION_REQUEST->id(),
                        'news' => $reason,
                    ]);

                }
                else if($ticket->status->id == TicketStatusEnum::OWNER_EVALUATION->id())
                {
                    $ticket->update([
                        'status_id' => TicketStatusEnum::CLOSED->id(),
                    ]);

                    $ticket_log = $ticket->logs()->create([
                        'member_id' => $evaluated_by,
                        'type_id' => TicketLogTypeEnum::APPROVAL->id(),
                        'news' => $reason,
                    ]);
                }
                else 
                {
                    throw new InvalidTicketStatusException('Ticket status does not permit this operation.');
                }
            }
                    
            foreach ($documents as $document) 
            {
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
        });
    }

    public function reject()
    {
        
    }


}