<?php

namespace App\Services;

use App\Models\Enums\TicketIssueType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(
        protected AreaService $areaService,
    ) {}

    public function workOrder(array $_data = [])
    {
    }

    public function serviceForm(
        array $ticket,
        array $issuer,
        array $issues,
        array $location,
        array $documents,

    ) 
    {
        $service_form_data = [
            'header' => [
                'work_order_id' => 'WO-' . substr(Str::uuid(), -5),
                'area_name' => $this->areaService->get_name(),
                'date' => now()->translatedFormat('l, d F Y'),
            ],
            'requestor_identity' => array_merge(
                Arr::except($issuer, ['id', 'role_id', 'member_since', 'member_until', 'title']),
                ['name' => $issuer['name'] . ' (' . substr($issuer['id'], -5) . ')']
            ),
            'formally_requests' => [
                'work_type' => TicketIssueType::whereIn('id', $issues)->pluck('name')->toArray(),
                'response_level' => $ticket['response_level'],
                'location' => $location['stated_location'],
                'that_can_be_described_by' => $ticket['executive_summary'],
            ],
            'supportive_documents' => collect($documents)->map(function ($document) {
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

    public function printViewTicket()
    {
    }
}   