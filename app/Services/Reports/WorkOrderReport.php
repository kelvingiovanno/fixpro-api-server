<?php

namespace App\Services\Reports;

use App\Models\Ticket;

use App\Services\AreaService;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Arr;

class WorkOrderReport
{
    public function __construct(
        protected AreaService $areaService,
    ) {}

    public function generate(
        Ticket $ticket,
        string $issue_id,
        string $work_description,
        string $wo_id,
    )  {
        $report_data = [
            'header' => [
                'work_order_id' => $wo_id,
                'area_name' => $this->areaService->get_name(),
                'date' => now()->translatedFormat('l, d F Y'),
            ],
            'to_perform' => [
                'work_type' => $ticket->ticket_issues->firstWhere('issue_id', $issue_id)->issue->name,
                'response_level' => $ticket->response->name,
                'location' => $ticket->location->stated_location,
                'as_a_follow_up_for' => 'SF-' . substr($ticket->id, 0, 5),
                'work_directive' => $work_description,
            ],
            'upon_the_request_of' => array_merge(
                Arr::except($ticket->issuer->toArray(), ['id', 'role_id', 'member_since', 'member_until', 'title']),
                ['on' => $ticket->raised_on, 'name' => $ticket->issuer->name . ' (' . substr($ticket->issuer->id, -5) . ')']
            ),
            'to_be_carried_out_by' => $ticket->ticket_issues->firstWhere('issue_id', $issue_id)->maintainers->map(function ($member) {
                return $member->only(['name', 'title']);
            })->values()->all(),
        ];

        $work_order = Pdf::loadView('pdf.work_order', $report_data)->setPaper('a4', 'portrait')->output();
        
        return $work_order;
    }
}