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

        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $tickets = Ticket::whereDate('raised_on', '<=', $endOfMonth)->get();

        $report_data = [
            'header' => [
                'date' => $month_name . ' ' . now()->format('Y'),
                'area' => $this->areaService->get_name(),
            ],
            'tickets' => $tickets->map(function ($ticket) {
                $before_url = optional(
                    $ticket->documents->firstWhere('previewable_on', '!=', null)
                )?->previewable_on;

                $after_url = optional(
                    $ticket->logs
                        ->firstWhere('type_id', TicketLogTypeEnum::WORK_EVALUATION_REQUEST->id())
                        ?->documents
                        ->firstWhere('previewable_on', '!=', null)
                )?->previewable_on;

                return [
                    'id' => substr($ticket->id, -5),
                    'raised' => $ticket->raised_on,
                    'closed' => $ticket->closed_on ?? 'not closed yet',
                    'issues' => $ticket->ticket_issues->map(fn($ti) => $ti->issue->name),
                    'before' => $before_url ? public_path(parse_url($before_url, PHP_URL_PATH)) : null,
                    'after' => $after_url ? public_path(parse_url($after_url, PHP_URL_PATH)) : null,
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