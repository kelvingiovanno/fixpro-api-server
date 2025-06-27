<?php

namespace App\Services\Reports;

use App\Enums\TicketLogTypeEnum;

use App\Models\Ticket;

use App\Services\AreaService;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TicketReport
{
    public function __construct(
        protected AreaService $areaService,
    ) {}

    public function generate(int $month)
    {
        $month_name = Carbon::create()->month($month)->format('F');

        $year = now()->year;

        $tickets = Ticket::whereMonth('raised_on', $month)
                        ->whereYear('raised_on', $year)
                        ->get();

        $report_data = [
            'header' => [
                'date' => $month_name . ' ' . now()->format('Y'),
                'area' => $this->areaService->get_name(),
            ],
            'tickets' => $tickets->map(function ($ticket) {
                return [
                    'id' => substr($ticket->id, -5),
                    'raised' => $ticket->raised_on,
                    'closed' => $ticket->closed_on ?? 'not closed yet',
                    'issues' => $ticket->ticket_issues->map(fn($ti) => $ti->issue->name),
                    'before' => optional(
                        $ticket->documents->firstWhere('previewable_on', '!=', null)
                    )->previewable_on,
                    'after' => optional(
                        $ticket->logs->firstWhere('type_id', TicketLogTypeEnum::WORK_EVALUATION_REQUEST->id())->documents->firstWhere('previewable_on', '!=', null)
                    )->previewable_on,

                    'handlers' => $ticket->ticket_issues
                        ->flatMap(fn($ti) => $ti->maintainers->pluck('name'))
                        ->unique()
                        ->values(),
                ];
            }),
        ];

        $ticket_report = Pdf::loadView('pdf.ticket_report', $report_data)
                    ->setPaper('a4', 'portrait')
                    ->output();
        
        return $ticket_report;
    }
}