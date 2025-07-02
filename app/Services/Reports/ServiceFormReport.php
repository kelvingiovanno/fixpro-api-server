<?php

namespace App\Services\Reports;

use App\Models\Ticket;

use App\Services\AreaService;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;

class ServiceFormReport
{
    public function __construct(
        protected AreaService $areaService,
    ) {}

    public function generate(Ticket $ticket)
    {
        $report_data = 
        [
            'header' => [
                'work_order_id' => 'SF-' . substr($ticket->id, 0, 5),
                'area_name' => $this->areaService->get_name(),
                'date' => now()->translatedFormat('l, d F Y'),
            ],
            'requestor_identity' => array_merge(
                Arr::except($ticket->issuer->toArray(), ['id', 'role_id', 'member_since', 'member_until', 'title', 'access_token']),
                ['name' => $ticket->issuer->name . ' (' . substr($ticket->issuer->id, -5) . ')']
            ),
            'formally_requests' => [
                'work_type' => $ticket->ticket_issues->pluck('issue.name')->toArray(),
                'response_level' => $ticket->response->name,
                'location' => $ticket->location->stated_location,
                'that_can_be_described_by' => $ticket->stated_issue,
            ],
            'supportive_documents' => collect($ticket->documents)->map(function ($document) {
                return [
                    'resource_name' => $document->resource_name,
                    'image_src' => public_path(parse_url($document->previewable_on, PHP_URL_PATH)),
                ];
            })->toArray()
        ];
        
        $service_form = Pdf::loadView('pdf.service_form', $report_data)->setPaper('a4', 'portrait')->output();
        
        return $service_form;
    }
}