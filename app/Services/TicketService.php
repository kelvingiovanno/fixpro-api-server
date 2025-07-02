<?php

namespace App\Services;

use App\Enums\IssueTypeEnum;
use App\Enums\MemberRoleEnum;
use App\Enums\TicketLogTypeEnum;
use App\Enums\TicketStatusEnum;

use App\Models\Ticket;
use App\Models\Member;

use App\Exceptions\InvalidTicketStatusException;
use App\Exceptions\IssueNotFoundException;
use App\Models\Inbox;
use App\Services\Reports\ServiceFormReport;
use App\Services\Reports\WorkOrderReport;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct (
        protected ServiceFormReport $serviceFormReport,
        protected WorkOrderReport $workOrderReport,
        protected StorageService $storageService,
        protected AreaService $areaService,
        protected CalenderService $calenderService,
    ) { }

    public function details(
        string $ticket_id,
        ?string $requester_id = null,
        ?string $requester_role_id = null,
    ) {
        $ticket = Ticket::with('logs', 'issuer')->findOrFail($ticket_id);

        if(
            $ticket->status->id == TicketStatusEnum::OPEN->id() &&
            $requester_role_id == MemberRoleEnum::MANAGEMENT->id()    
        ){
            DB::transaction(function () 
                use($ticket, $requester_id)
            {
                $requester = Member::find($requester_id);
                
                $ticket->update([
                    'status_id' => TicketStatusEnum::IN_ASSESSMENT->id(),
                    'assessed_by' => $requester_id,
                ]);

                $ticket->logs()->create([
                    'member_id' => $requester_id,
                    'type_id' => TicketLogTypeEnum::ASSESSMENT->id(),
                    'news' => "Ticket assessed by {$requester->name}",
                    'recorded_on' => now(),
                ]);

            });
        }
        
        $lastLog = $ticket->logs->sortByDesc('recorded_on')->first();

        if ($lastLog && $lastLog->type_id == TicketLogTypeEnum::OWNER_EVALUATION_REQUEST->id()) 
        {
            $hoursPassed = now()->diffInHours($lastLog->recorded_on);
            $sla_auto_close = $this->areaService->get_sla_close();

            if ($hoursPassed > $sla_auto_close) 
            {
                DB::transaction(function () use ($ticket) {
                    $ticket->update([
                        'status_id' => TicketStatusEnum::CLOSED->id(),
                        'closed_on' => now(),
                    ]);

                    $ticket->logs()->create([
                        'member_id' => $ticket->issuer->id,
                        'type_id' => TicketLogTypeEnum::AUTO_CLOSE->id(),
                        'news' => 'This ticket was automatically closed by the system',
                        'recorded_on' => now(),
                    ]);
                });
            }
        }

        $ticket->load([
            'issuer.role',
            'issuer.specialities',
            'issuer.capabilities',
            'ticket_issues.maintainers',
            'ticket_issues.maintainers.role',
            'ticket_issues.maintainers.specialities',
            'ticket_issues.maintainers.capabilities',
            'location',
            'documents',
            'status',
            'response',
            'logs.type',
            'logs.issuer',
            'logs.issuer.role',
            'logs.issuer.specialities',
            'logs.issuer.specialities',
            'logs.documents',
        ]);

        $details_data = [
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
            'executive_summary' => '',
            'stated_issue' => $ticket->stated_issue,
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
                        'service_level_agreement_duration_hour' => (string) $specialty->sla_hours,
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
                                'service_level_agreement_duration_hour' => (string) $specialty->sla_hours,
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
            }),
            'handlers' => $ticket->ticket_issues->flatMap(function ($issue) {
                return $issue->maintainers->map(function ($maintainer) {
                    return [
                        'id' => $maintainer->id,
                        'name' => $maintainer->name,
                        'role' => $maintainer->role->name,
                        'title' => $maintainer->title,
                        'specialties' => $maintainer->specialities->map(function ($specialty){
                            return [
                                'id' => $specialty->id,
                                'name' => $specialty->name,
                                'service_level_agreement_duration_hour' => (string) $specialty->sla_hours,
                            ];
                        }),
                        'capabilities' => $maintainer->capabilities->map(function ($capability) {
                            return $capability->name;
                        }),
                    ];
                });
            }),
            'closed_on' => $ticket->closed_on?->format('Y-m-d\TH:i:sP'),
        ];

        return $details_data;
    }

    public function all(
        string $requester_id,
        string $requester_role_id,
    ) {
        
        if($requester_role_id == MemberRoleEnum::CREW->id())
        {
  

            $member = Member::with([
                'tickets.ticket_issues.issue',
                'tickets.status',
                'tickets.response',
                'maintained_tickets.ticket.ticket_issues.issue',
                'maintained_tickets.ticket.status',
                'maintained_tickets.ticket.response',
            ])->findOrFail($requester_id);

            $createdTickets = $member->tickets;

            $maintainedTickets = $member->maintained_tickets
                ->pluck('ticket') 
                ->unique('id'); 


            $tickets = $createdTickets->merge($maintainedTickets)->unique('id')->values();
                    
        }
        else if($requester_role_id == MemberRoleEnum::MEMBER->id())
        {
            $tickets = Member::with([
                'tickets.ticket_issues.issue', 
                'tickets.status', 
                'tickets.response'
            ])->find($requester_id)->tickets; 
        }
        else
        {
            $tickets = Ticket::with(['ticket_issues.issue', 'status', 'response'])->get();
        }


        DB::transaction(function () use ($tickets, &$ticketIds) {

            foreach ($tickets as $ticket) {
                $lastLog = $ticket->logs->sortByDesc('recorded_on')->first();

                if (!$lastLog || $lastLog->type_id !== TicketLogTypeEnum::OWNER_EVALUATION_REQUEST->id()) {
                    continue;
                }

                $hoursPassed = now()->diffInHours($lastLog->recorded_on);
                $sla_auto_close = $this->areaService->get_sla_close();

                if ($hoursPassed > $sla_auto_close) {
                    $ticket->update([
                        'status_id' => TicketStatusEnum::CLOSED->id(),
                        'closed_on' => now(),
                    ]);

                    $ticket->load('status'); 

                    $ticket->logs()->create([
                        'member_id' => $ticket->issuer->id,
                        'type_id' => TicketLogTypeEnum::AUTO_CLOSE->id(),
                        'news' => 'This ticket was automatically closed by the system.',
                        'recorded_on' => now(),
                    ]);

                    $ticketIds[] = $ticket->id; 
                }
            }
        });

        return $tickets;
    }

    public function create(
        string $owner_id,
        array $ticket,
        array $issues,
        array $location,
        ?array $documents, 
        
    ) {
        $created_ticket = DB::transaction(function () 
            use ($owner_id, $ticket, $issues, $location, $documents) 
        {
            $owner = Member::find($owner_id);

            $ticket = Ticket::create([
                'member_id' => $owner_id,
                'status_id' => TicketStatusEnum::OPEN->id(),
                'response_id' => $ticket['response_id'],
                'stated_issue' => $ticket['stated_issue'],
                'raised_on' => now(),
            ]);

            $ticket_log = $ticket->logs()->create([
                'member_id' => $owner_id,
                'type_id' => TicketLogTypeEnum::ACTIVITY->id(),
                'news' => 'Ticket has been created.',
                'recorded_on' => now(),
            ]);

            $menegements = Member::where('role_id', MemberRoleEnum::MANAGEMENT->id())->get();

            foreach($menegements as $menegement)
            {
                Inbox::create([
                    'member_id' => $menegement->id,
                    'ticket_id' => $ticket->id,
                    'title' => "Ticket was opened by {$owner->name}",
                    'body' => $ticket['stated_issue'],
                    'sent_on' => now(),
                ]);
            }

            $is_calender_setup = $this->areaService->is_calendar_setup();

            foreach($issues as $issue)
            {
                $calendar_issue_name = $ticket->ticket_issues()->create([
                    'issue_id' => $issue,
                ])->issue->id;

                if($is_calender_setup)
                {
                    $this->calenderService->create_event(
                        $ticket,
                        'Ticket Due - ' . substr($ticket->id, -5),
                        '',
                        $calendar_issue_name,
                    );
                }
            }

            $ticket->location()->create([
                'stated_location' => $location['stated_location'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        
            foreach($documents as $document)
            {
                $document_path = $this->storageService->storeTicketDocument(
                    $document['resource_content'],
                    $document['resource_name'], 
                    $ticket->id
                );

                $ticket->documents()->create([
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => $document['resource_size'],
                    'previewable_on' => $document_path,
                ]);
            }

            $ticket->load('issuer', 'ticket_issues', 'status', 'response');

            $service_form_pdf = $this->serviceFormReport->generate (
                $ticket
            );

            $service_form_path = $this->storageService->storeLogTicketDocument(
                base64_encode($service_form_pdf), 
                'service_form.pdf', 
                $ticket_log->id
            );

            $ticket_log->documents()->create([
                'resource_type' => 'application/pdf',
                'resource_name' => 'service_form-' . substr($ticket->id, -5),
                'resource_size' =>  (double)  round(strlen($service_form_pdf) / 1048576, 2),
                'previewable_on' => $service_form_path,
            ]);

            return $ticket;
        });

        return $created_ticket;

    }

    public function update(
        string $ticket_id,
        array $issue_ids,
        string $status,
        string $stated_issue,
        array $location,
        string $requester_id,
    ) {
        $updated_ticket = DB::transaction(function () 
            use ($ticket_id, $issue_ids, $status, $stated_issue, $location, $requester_id) 
        {
            
            $ticket = Ticket::findOrFail($ticket_id);

            $ticket->update([
                'status_id' => TicketStatusEnum::from($status)->id(),
                'stated_issue' => $stated_issue,
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

            $ticket->logs()->create([
                'member_id' => $requester_id,
                'type_id' => TicketLogTypeEnum::ACTIVITY->id(),
                'news' => 'Ticket infromation has been updated.',
                'recorded_on' => now(),
            ]);

            return $ticket;
        });

        return $updated_ticket;
    }

    public function evaluate_request(
        string $requester_id,
        string $ticket_id,
        ?string $remark,
        ?array $documents
    ) {

        $ticket_log = DB::transaction(function () 
            use ($requester_id, $ticket_id, $remark, $documents)
        {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($ticket->status_id != TicketStatusEnum::ON_PROGRESS->id()) {
                throw new InvalidTicketStatusException('Ticket is not in progress.');
            }

            $ticket->update(['status_id' => TicketStatusEnum::WORK_EVALUATION->id()]);

            $ticket_log = $ticket->logs()->create([
                'member_id' => $requester_id,
                'type_id' => TicketLogTypeEnum::WORK_EVALUATION_REQUEST->id(),
                'news' => $remark,
                'recorded_on' => now(),
            ]);

            $menegements = Member::where('role_id', MemberRoleEnum::MANAGEMENT->id())->get();

            foreach($menegements as $menegement)
            {
                Inbox::create([
                    'member_id' => $menegement->id,
                    'ticket_id' => $ticket->id,
                    'title' => "Work Evaluation Requested",
                    'body' => "A work evaluation has been requested for ticket #" . substr($ticket->id, 0, 5) . ".",
                    'sent_on' => now(),
                ]);
            }

            foreach ($documents ?? [] as $document) 
            {
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['resource_content'],
                    $document['resource_name'],
                    $ticket->id
                );

                $ticket_log->documents()->create([
                    'log_id' => $ticket_log->id,
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => $document['resource_size'],
                    'previewable_on' => $filePath,
                ]);
            }

            return $ticket_log;
        });

        return $ticket_log;
    }
    
    public function evaluate(
        string $evaluated_by,
        string $evaluate_role_id,
        string $ticket_id,
        bool $approved,
        bool $owner_approval,
        ?string $reason,
        ?array $documents,

    ) {

        $ticket_log = DB::transaction(function () 
            use ($evaluated_by, $evaluate_role_id,$ticket_id, $approved, $owner_approval, $reason, $documents) {
            
            $ticket = Ticket::findOrFail($ticket_id);
            
            $handler_ids = $ticket->ticket_issues->pluck('maintainers.id');

            $ticket_log = null;

            if(!$approved)
            {
                $ticket->update([
                    'status_id' => TicketStatusEnum::ON_PROGRESS->id(),
                ]);

                $reason = $reason ?? 'Ticket evaluation resulted in rejection.';

                $ticket_log = $ticket->logs()->create([
                    'member_id' => $evaluated_by,
                    'type_id' => TicketLogTypeEnum::REJECTION->id(),
                    'news' => $reason,
                    'recorded_on' => now(),
                ]);

                foreach ($handler_ids as $handler_id)
                {
                    Inbox::create([
                        'member_id' => $handler_id,
                        'ticket_id' => $ticket->id,
                        'title' => 'Ticket Rejected',
                        'body' => 'Ticket #' . substr($ticket->id, 0, 5) . ' has been rejected and sent back to progress.',
                        'sent_on' => now(),
                    ]);
                }
            }
            else 
            {
                if($evaluate_role_id == MemberRoleEnum::CREW->id() && $ticket->status_id == TicketStatusEnum::WORK_EVALUATION->id())
                {
                    $ticket->update([
                        'status_id' => TicketStatusEnum::QUIALITY_CONTROL->id(),
                        'evaluated_by' => $evaluated_by,   
                    ]);

                    $reason = $reason ?? 'The work has been approved after evaluation.';

                    $ticket_log = $ticket->logs()->create([
                        'member_id' => $evaluated_by,
                        'type_id' => TicketLogTypeEnum::WORK_EVALUATION->id(),
                        'news' => $reason,
                        'recorded_on' => now(),
                    ]);

                    Inbox::create([
                        'member_id' => $ticket->assessed_by,
                        'ticket_id' => $ticket->id,
                        'title' => 'Quiality Control Evaluation Required',
                        'body' => 'Please evaluate the result of ticket #' . substr($ticket->id, 0, 5) . '.',
                        'sent_on' => now(),
                    ]);

                    foreach ($handler_ids as $handler_id)
                    {
                        Inbox::create([
                            'member_id' => $handler_id,
                            'ticket_id' => $ticket->id,
                            'title' => 'Work Evaluation Approved',
                            'body' => 'Ticket #' . substr($ticket->id, 0, 5) . ' has been approved and moved to quality control.',
                            'sent_on' => now(),
                        ]);
                    }

                }
                else if(
                    $evaluate_role_id == MemberRoleEnum::MANAGEMENT->id() &&
                    (
                        $ticket->status->id == TicketStatusEnum::QUIALITY_CONTROL->id() ||
                        $ticket->status->id == TicketStatusEnum::WORK_EVALUATION->id()
                    )
                ){
                    if($owner_approval)
                    {
                        $ticket->update([
                            'status_id' => TicketStatusEnum::OWNER_EVALUATION->id(),
                        ]);

                        $reason = $reason ?? 'The ticket has passed quality control.';

                        $ticket_log = $ticket->logs()->create([
                            'member_id' => $evaluated_by,
                            'type_id' => TicketLogTypeEnum::OWNER_EVALUATION_REQUEST->id(),
                            'news' => $reason,
                            'recorded_on' => now(),
                        ]);

                        Inbox::create([
                            'member_id' => $ticket->issuer->id,
                            'ticket_id' => $ticket->id,
                            'title' => 'Owner Evaluation Required',
                            'body' => 'Please evaluate the result of ticket #' . substr($ticket->id, 0, 5) . '.',
                            'sent_on' => now(),
                        ]);

                        foreach ($handler_ids as $handler_id)
                        {
                            Inbox::create([
                                'member_id' => $handler_id,
                                'ticket_id' => $ticket->id,
                                'title' => 'Quality Control Passed',
                                'body' => 'Ticket #' . substr($ticket->id, 0, 5) . ' has passed quality control and is now in owner evaluation.',
                                'sent_on' => now(),
                            ]);
                        }

                    }
                    else 
                    {
                        $ticket->update([
                            'status_id' => TicketStatusEnum::CLOSED->id(),
                            'closed_on' => now(),
                        ]);

                        $reason = $reason ?? 'The ticket has been resolved and closed.';

                        $ticket_log = $ticket->logs()->create([
                            'member_id' => $evaluated_by,
                            'type_id' => TicketLogTypeEnum::APPROVAL->id(),
                            'news' => $reason,
                            'recorded_on' => now(),
                        ]);

                        Inbox::create([
                            'member_id' => $ticket->issuer->id,
                            'ticket_id' => $ticket->id,
                            'title' => 'Ticket Closed',
                            'body' => 'Your ticket #' . substr($ticket->id, 0, 5) . ' has been approved and closed.',
                            'sent_on' => now(),
                        ]);

                        foreach ($handler_ids as $handler_id)
                        {
                            Inbox::create([
                                'member_id' => $handler_id,
                                'ticket_id' => $ticket->id,
                                'title' => 'Ticket Closed',
                                'body' => 'Ticket #' . substr($ticket->id, 0, 5) . ' has been approved and closed.',
                                'sent_on' => now(),
                            ]);
                        }
                    }

                }
                else if($evaluated_by == $ticket->issuer->id  && $ticket->status->id == TicketStatusEnum::OWNER_EVALUATION->id())
                {
                    $ticket->update([
                        'status_id' => TicketStatusEnum::CLOSED->id(),
                        'closed_on' => now(),
                    ]);

                    $reason = $reason ?? 'The owner has approved the completion of the ticket.';

                    $ticket_log = $ticket->logs()->create([
                        'member_id' => $evaluated_by,
                        'type_id' => TicketLogTypeEnum::APPROVAL->id(),
                        'news' => $reason,
                        'recorded_on' => now(),
                    ]);

                    foreach ($handler_ids as $handler_id)
                    {
                        Inbox::create([
                            'member_id' => $handler_id,
                            'ticket_id' => $ticket->id,
                            'title' => 'Ticket Closed',
                            'body' => 'Ticket #' . substr($ticket->id, 0, 5) . ' has been approved and closed.',
                            'sent_on' => now(),
                        ]);
                    }
                }
                else 
                {
                    throw new InvalidTicketStatusException('The ticket\'s current status does not permit this operation.');
                }
            }
                    
            foreach ($documents ?? [] as $document) 
            {
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['resource_content'],
                    $document['resource_name'],
                    $ticket->id
                );

                $ticket_log->documents()->create([
                    'log_id' => $ticket_log->id,
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => $document['resource_size'],
                    'previewable_on' => $filePath,
                ]);
            }

            return $ticket_log;
        });

        return $ticket_log;
    }

    public function close(
        string $ticket_id,
        string $requester_id,
        string $requestor_role_id,
        ?string $reason,
        ?array $documents,
    ) {

        $reason = $reason ?? '';

        $ticket_log = DB::transaction(function () 
            use ($ticket_id, $requester_id, $requestor_role_id, $reason, $documents) 
        {
            
            $ticket = Ticket::with('issuer')->findOrFail($ticket_id);

            if($ticket->status_id != TicketStatusEnum::OPEN->id() && $ticket->status_id != TicketStatusEnum::IN_ASSESSMENT->id())
            {
                throw new InvalidTicketStatusException('The ticket\'s current status does not permit this operation.');
            }

            if($ticket->issuer->id == $requester_id)
            {
                $ticket->update([
                    'status_id' => TicketStatusEnum::CANCELLED->id(),
                    'closed_on' => now(),
                ]);
            }
            else if($requestor_role_id == MemberRoleEnum::MANAGEMENT->id())
            {
                $ticket->update([
                    'status_id' => TicketStatusEnum::REJECTED->id(),
                    'closed_on' => now(),
                ]);
            } 

            $ticket_log = $ticket->logs()->create([
                'member_id' => $requester_id,
                'type_id' => TicketLogTypeEnum::REJECTION->id(),
                'news' => $reason,
                'recorded_on' => now(),
            ]);

            foreach ($documents ?? [] as $document) 
            {
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['resource_content'],
                    $document['resource_name'],
                    $ticket->id
                );

                $ticket_log->documents()->create([
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => $document['resource_size'],
                    'previewable_on' => $filePath,
                ]);
            }

            return $ticket_log;
        });  

        return $ticket_log;
    }

    public function force_close(
        string $ticket_id,
        string $requester_id,
        ?string $reason,
        ?array $documents,
    ) {

        $reason = $reason ?? '';

        DB::transaction(function () 
        use ($ticket_id, $requester_id, $reason, $documents) {

            $ticket = Ticket::findOrFail($ticket_id);

            if(
                $ticket->status_id != TicketStatusEnum::ON_PROGRESS->id() &&
                $ticket->status_id != TicketStatusEnum::WORK_EVALUATION->id()
            ) {
                throw new InvalidTicketStatusException('The ticket\'s current status does not permit this operation.');
            }

            $ticket->update([
                'status_id' => TicketStatusEnum::CLOSED->id(),
                'closed_on' => now(),
            ]);

            $ticket_log = $ticket->logs()->create([
                'member_id' => $requester_id,
                'type_id' => TicketLogTypeEnum::FORCE_CLOSURE->id(),
                'news' => $reason,
                'recorded_on' => now(),
            ]);

            foreach ($documents ?? [] as $document) 
            {
                
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['resource_content'],
                    $document['resource_name'],
                    $ticket->id
                );

                $ticket_log->documents()->create([
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => $document['resource_size'],
                    'previewable_on' => $filePath,
                ]);
            }
        });
    }

    public function assign_handlers(
        Ticket $ticket,
        string $issue_id,
        array $handler_ids,
        string $work_description,
        ?array $documents,
        string $requester_id,
    ) {
        $ticket_log = DB::transaction(function () 

        use ($ticket, $issue_id, $handler_ids, $work_description, $documents, $requester_id) 
        {
            $ticket_issue = $ticket->ticket_issues->firstWhere('issue_id', $issue_id);

            if(!$ticket_issue)
            {
                throw new IssueNotFoundException();
            }

            $ticket_issue->maintainers()->sync($handler_ids);

            $ticket->update([
                'status_id' => TicketStatusEnum::ON_PROGRESS->id(),
            ]);

            foreach($handler_ids as $handler_id)
            {       
                Inbox::create([
                    'member_id' => $handler_id,
                    'ticket_id' => $ticket->id,
                    'title' => 'New Ticket Assignment',
                    'body' => sprintf(
                        'You have been assigned to handle an issue: "%s" on Ticket #%s. Please review the work description and begin action.',
                        $ticket_issue->issue->name,
                        substr($ticket->id, -5)
                    ),
                    'sent_on' => now(),
                ]);
            }

            $ticket->load(
                'location',
                'response',
                'issuer',
                'status',
                'ticket_issues.maintainers',
            );

            $wo_id = 'WO-'. substr(Str::uuid(), -5);

            $work_order = $this->workOrderReport->generate(
                $ticket,
                $issue_id,
                $work_description,
                $wo_id
            );

            $work_order_path = $this->storageService->storeWoDocument(
                base64_encode($work_order), 
                'work_order.pdf',
                $issue_id,
            );

            $ticket_log = $ticket->logs()->create([
                'member_id' => $requester_id,
                'type_id' => TicketLogTypeEnum::INVITATION->id(),
                'news' => "Maintainers have been assigned to the {$ticket_issue->issue->name} issue.",
                'recorded_on' => now(),
            ]);
            
            $ticket_issue->work_order()->create([
                'id' => $wo_id,
                'resource_type' => 'application/pdf',
                'resource_name' => 'work_order.pdf',
                'resource_size' => (string) round(strlen($work_order) / 1048576, 2) . ' MB',
                'previewable_on' => $work_order_path,
            ]);

            $ticket_issue->update([
                'work_description' => $work_description,
            ]);

            $ticket_log->documents()->create([
                'resource_type' => 'application/pdf',
                'resource_name' => 'work_order.pdf',
                'resource_size' => (double) round(strlen($work_order) / 1048576, 2),
                'previewable_on' => $work_order_path,
            ]);

            foreach ($documents ?? [] as $document) 
            {
                $filePath = $this->storageService->storeLogTicketDocument(
                    $document['resource_content'],
                    $document['resource_name'],
                    $ticket->id
                );

                $ticket_log->documents()->create([
                    'resource_type' => $document['resource_type'],
                    'resource_name' => $document['resource_name'],
                    'resource_size' => (double) $document['resource_size'],
                    'previewable_on' => $filePath,
                ]);
            }

            return $ticket_log;
        });

        return $ticket_log;
    }

    public function add_log(
        string $ticket_id,
        string $log_type,
        ?string $news,
        ?array $documents,
        string $requester_id,
    ) {

        $news = $news ?? '';

        $new_log = DB::transaction(function () 
            use($ticket_id, $log_type, $news, $documents, $requester_id) {
                
                $ticket = Ticket::findOrFail($ticket_id);

                $ticket_log = $ticket->logs()->create([
                    'member_id' => $requester_id,
                    'type_id' => TicketLogTypeEnum::from($log_type)->id(),
                    'news' => $news,
                    'recorded_on' => now(),
                ]);
                
                foreach ($documents ?? [] as $document) 
                {
                    $filePath = $this->storageService->storeLogTicketDocument(
                        $document['resource_content'],
                        $document['resource_name'],
                        $ticket->id
                    );

                    $ticket_log->documents()->create([
                        'resource_type' => $document['resource_type'],
                        'resource_name' => $document['resource_name'],
                        'resource_size' => (double) $document['resource_size'],
                        'previewable_on' => $filePath,
                    ]);
                }

                $ticket_log->load(
                    'type',
                    'issuer.role',
                    'issuer.specialities',
                    'issuer.capabilities',
                    'documents',
                );

                return $ticket_log;
            });

        return $new_log;
    }
}