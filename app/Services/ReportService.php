<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Ticket;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportService
{
    public function __construct(
        protected AreaService $areaService,
    ) {}

    public function workOrder(array $_data = [])
    {
    }

    public function service_form(Ticket $ticket)
    {
        $service_form_data = 
        [
            'header' => [
                'work_order_id' => 'SF-' . substr($ticket->id, 0, 5),
                'area_name' => $this->areaService->get_name(),
                'date' => now()->translatedFormat('l, d F Y'),
            ],
            'requestor_identity' => array_merge(
                Arr::except($ticket->issuer, ['id', 'role_id', 'member_since', 'member_until', 'title']),
                ['name' => $ticket->issuer->name . ' (' . substr($ticket->issuer->id, -5) . ')']
            ),
            'formally_requests' => [
                'work_type' => $ticket->ticket_issues->pluck('issue.name')->toArray(),
                'response_level' => $ticket['response_level'],
                'location' => $ticket->location->stated_location,
                'that_can_be_described_by' => $ticket->executive_summary,
            ],
            'supportive_documents' => collect($ticket->documents)->map(function ($document) {
                return [
                    'resource_name' => $document->resource_name,
                    'image_src' => $document->previewable_on,
                ];
            })->toArray()
        ];

        
        $service_form = Pdf::loadView('pdf.service_form', $service_form_data)->setPaper('a4', 'portrait')->output();
        
        return $service_form;
    }

    public function periodicReport()
    {
    }

    public function print_view(Ticket $ticket, Member $requester)    
    {
        $print_view_data = 
        [
            'header' => [
                'document_id' => 'PV-' . substr($ticket->id, 0, 5),
                'area_name' => $this->areaService->get_name(),
                'date' => now()->translatedFormat('l, d F Y'),
            ],
            'details' => [
                'ticket_no' => substr($ticket->id, -5),
                'created_at' => $ticket->raised_on,
                'completed_at' => $ticket->closed_on ?? 'Not completed.',
                'assessed_by' => $ticket->assessed->name ?? 'Not assessed.', 
            'ticket_status' => $ticket->status->name,
                'requester_name' => $ticket->issuer->name,
                'identifier_no' => substr($ticket->issuer->id, -5),
                'work_type' => $ticket->ticket_issues->pluck('issue.name')->toArray(),
                'handling_priority' => $ticket->response->name,
                'location' => $ticket->location->stated_location,
                'evaluated_by' => $ticket->evaluated->name ?? 'Not evaluated yet.',
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
            'print_view_requested_by' => $requester->name,
        ];

        $print_view = Pdf::loadView('pdf.ticket_print_view',$print_view_data)->setPaper('a4', 'portrait')->output();
            
        return $print_view;
    }
}   