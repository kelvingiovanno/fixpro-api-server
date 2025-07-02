<?php

namespace App\Services\Reports;

use App\Models\Member;
use App\Models\Ticket;

use App\Services\AreaService;

use Barryvdh\DomPDF\Facade\Pdf;

class PrintViewReport
{

    public function __construct(
        protected AreaService $areaService,
    ) {}

    public function generate(Ticket $ticket, Member $requester)    
    {
        $report_data = 
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
                'requester_name' => $ticket->issuer->name   ,
                'identifier_no' => substr($ticket->issuer->id, -5),
                'work_type' => $ticket->ticket_issues->pluck('issue.name')->toArray(),
                'handling_priority' => $ticket->response->name,
                'location' => $ticket->location->stated_location,
                'evaluated_by' => $ticket->evaluated->name ?? 'Not evaluated yet.',
            ],
            'complaints' => $ticket->stated_issue,
            'supportive_documents' => $ticket->documents->map(function ($document){
                return public_path(parse_url($document->previewable_on, PHP_URL_PATH));
            }),
            'logs' => $ticket->logs->values()->map(function ($log, $index) {
                return [
                    'name' => $log->issuer->name,
                    'id_number' => substr($log->issuer->id, -5),
                    'log_type' => $log->type->name,
                    'raised_on' => $log->recorded_on,
                    'news' => $log->news,
                    'supportive_documents' => $log->documents
                        ->filter(function ($document) {
                            return preg_match('/\.(png|jpe?g|gif|bmp|webp)$/i', $document->resource_name);
                        })
                        ->map(function ($document) {
                            return public_path(parse_url($document->previewable_on, PHP_URL_PATH));
                        }),
                ];
            }),
            'print_view_requested_by' => $requester->name,
        ];

        $print_view = Pdf::loadView('pdf.ticket_print_view',$report_data)->setPaper('a4', 'portrait')->output();
            
        return $print_view;
    }
}