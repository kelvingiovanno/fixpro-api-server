<?php 

namespace App\Services\Reports;

use App\Enums\IssueTypeEnum;
use App\Enums\MemberRoleEnum;
use App\Enums\TicketLogTypeEnum;
use App\Enums\TicketStatusEnum;

use App\Models\Member;
use App\Models\Ticket;
use App\Models\TicketIssue;

use App\Services\AreaService;
use App\Services\QuickChartService;
use App\Services\TicketService;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Collection;

class PeriodicReport 
{
    public function __construct(
        protected AreaService $areaService,
        protected QuickChartService $quickChartService,
    ) {}

    public function generate(int $month)
    {
        $month_name = Carbon::create()->month($month)->format('F');

        $year = now()->year;

        $lastDateOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $all_ticket = Ticket::with(['ticket_issues.issue', 'status', 'response'])
            ->whereDate('raised_on', '<=', $lastDateOfMonth)
            ->get();

        $current_month_open_tickets = $all_ticket->filter(function ($ticket) use ($month, $year) {
            return $ticket->raised_on->month == $month && $ticket->raised_on->year == $year;
        });

        $status_id_closed = TicketStatusEnum::CLOSED->id();
        $current_month_closed_tickets = $all_ticket->filter(function ($ticket) use ($month, $year, $status_id_closed) {
            return $ticket->raised_on->month == $month &&
                $ticket->raised_on->year == $year &&
                $ticket->status_id === $status_id_closed;
        });

        $sla_stats = $this->sla_stats($current_month_closed_tickets);

        $issueTypes = collect(IssueTypeEnum::cases());
        $labels = $issueTypes->pluck('name')->values()->toArray();

        $currentMonthDataset = $issueTypes->map(function ($issue) use ($month, $year) {
            return TicketIssue::where('issue_id', $issue->id())
                ->whereHas('ticket', function ($query) use ($month, $year) {
                    $query->whereMonth('raised_on', $month)
                        ->whereYear('raised_on', $year);
                })
                ->count();
        })->values()->toArray();

        $current_month_piechart_url = $this->quickChartService->piechart(
            $currentMonthDataset,
            $labels
        );

        $overallDataset = $issueTypes->map(function ($issue) {
            return TicketIssue::where('issue_id', $issue->id())->count();
        })->values()->toArray();

        $overall_piechart_url = $this->quickChartService->piechart(
            $overallDataset,
            $labels
        );



        $report_data = [
                'header' => [
                    'date' => $month_name . ' ' . now()->format('Y'),
                    'area' => $this->areaService->get_name(),
                ],
                'opened_this_month' => $current_month_open_tickets->count(),
                'closed_this_month' => $current_month_closed_tickets->count(),
                'opened_total' => $all_ticket->count(),
                'closed_total' => $all_ticket->where('status_id', TicketStatusEnum::CLOSED->id())->count(),
                'compliance_rate' => $sla_stats['compliance_rate'],
                'avg_response_time' => $sla_stats['avg_response_time'],
                'issues' => $this->issue_stats($month,$year),
                'chart_pie_issues' => [
                    'montly' => $current_month_piechart_url,
                    'overall' => $overall_piechart_url,
                ],
                'crew_statistic' => $this->crew_stats($month, $year),
                'management_statistics' => $this->management_stats($month, $year),
                'member_statistics' => $this->member_stats($month, $year),
            ];


        $document = Pdf::loadView('pdf.periodic_report', $report_data)->setPaper('a4', 'portrait')->output();

        return $document;
    }
    

    private function sla_stats(Collection $tickets): array
    {
        $logIds = [
            'assessment' => TicketLogTypeEnum::ASSESSMENT->id(),
            'work_eval_req' => TicketLogTypeEnum::WORK_EVALUATION_REQUEST->id(),
            'invitation' => TicketLogTypeEnum::INVITATION->id(),
            'time_extension' => TicketLogTypeEnum::TIME_EXTENSION->id(),
        ];

        $ttw = 0;
        $totalResponseSeconds = 0;
        $responseCount = 0;

        foreach ($tickets as $ticket) 
        {
            $logs = $ticket->logs->sortBy('recorded_on')->values();

            $assessmentLog = $logs->firstWhere('type_id', $logIds['assessment']);
            $evalLog = $logs->last(fn($log) => $log->type_id === $logIds['work_eval_req']);
            $progressLog = $logs->firstWhere('type_id', $logIds['invitation']);

            $responseTime = $ticket->raised_on->diffInSeconds($assessmentLog->recorded_on);
            
            $totalResponseSeconds += $responseTime;
            $responseCount++;

            $totalExtensionSeconds = 0;
            $start = null;

            foreach ($logs as $log) {
                if ($log->type_id === $logIds['time_extension']) {
                    $start ??= Carbon::parse($log->recorded_on);
                } elseif ($start) {
                    $totalExtensionSeconds += Carbon::parse($log->recorded_on)->diffInSeconds($start);
                    $start = null;
                }
            }

            $actualDuration = $responseTime
                + $progressLog->recorded_on->diffInSeconds($evalLog->recorded_on)
                - $totalExtensionSeconds;

            $expectedDuration = $ticket->response->sla_modifier * $this->areaService->get_sla_response()
                + $ticket->ticket_issues->map(fn($ti) => $ti->issue->sla_hours * 3600)->max();

            if ($actualDuration <= $expectedDuration) $ttw++;
        }

        $total = $tickets->count();
        $complianceRate = $total ? round(($ttw / $total) * 100, 1) : 0;
        $avgTime = $responseCount
            ? CarbonInterval::seconds(round($totalResponseSeconds / $responseCount))->cascade()->format('%h hours %i minutes')
            : '0 hours 0 minutes';

        return [
            'compliance_rate' => $complianceRate,
            'avg_response_time' => $avgTime,
        ];
    }

    private function issue_stats(int $month, int $year): array
    {
        $issue_stats = [];

        foreach (IssueTypeEnum::cases() as $issueEnum) {
            $issue_id = $issueEnum->id();

            $related_ticket_issues = TicketIssue::with('maintainers') 
                ->whereHas('ticket', function ($query) use ($month, $year) {
                    $query->whereMonth('raised_on', $month)
                        ->whereYear('raised_on', $year);
                })
                ->where('issue_id', $issue_id)
                ->get();

            $ticket_ids = $related_ticket_issues->pluck('ticket_id')->unique();

            $resolved_ticket_ids = Ticket::whereIn('id', $ticket_ids)
                ->where('status_id', TicketStatusEnum::CLOSED->id())
                ->pluck('id');

            $distinct_maintainers = $related_ticket_issues
                ->flatMap(fn($ti) => $ti->maintainers->pluck('id'))
                ->unique();

            $total = $ticket_ids->count();
            $resolved = $resolved_ticket_ids->count();

            $issue_stats[] = [
                'name' => $issueEnum->value,
                'ticket_count' => $total,   
                'resolved_count' => $resolved,
                'maintainer_count' => $distinct_maintainers->count(),
                'doughnut_chart' => $this->quickChartService->ratio_doughnut($resolved, $total),
            ];
        }

        return $issue_stats;
    }

    private function crew_stats(int $month, int $year): array
    {
        logger($year);

        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        return Member::with('specialities')
            ->where('role_id', MemberRoleEnum::CREW->id())
            ->withTrashed()
            ->get()
            ->filter(function ($member) use ($startOfMonth, $endOfMonth) {
                return $member->member_since <= $endOfMonth &&
                    (
                        is_null($member->member_until) ||
                        $member->member_until >= $startOfMonth
                    );
            })
            ->map(function ($member) use ($month, $year) {
                return [
                    'id' => substr($member->id, -5),
                    'name' => $member->name,
                    'title' => $member->title,
                    'specialties' => $member->specialities->pluck('name'),
                        'HTC' => TicketIssue::whereHas('maintainers', fn($q) =>
                                $q->where('member_id', $member->id)
                            )->whereHas('ticket', function ($q) use ($month, $year) {
                                $q->whereMonth('raised_on', $month)
                                ->whereYear('raised_on', $year);
                            })->distinct('ticket_id')
                            ->count(),
                ];
            })
            ->toArray();
    }


    private function management_stats(int $month, int $year): array
    {
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        return Member::where('role_id', MemberRoleEnum::MANAGEMENT->id())
            ->withTrashed()
            ->get()
            ->filter(function ($member) use ($startOfMonth, $endOfMonth) {
                return $member->member_since <= $endOfMonth &&
                    (
                        is_null($member->member_until) ||
                        $member->member_until >= $startOfMonth
                    );
            })
            ->map(function ($member) use ($month, $year) {
                return [
                    'id' => substr($member->id, -5),
                    'name' => $member->name,
                    'title' => $member->title,
                    'assessed_count' => $member->assessed_tickets()
                        ->whereMonth('raised_on', $month)
                        ->whereYear('raised_on', $year)
                        ->count(),
                    'evaluated_count' => $member->evaluated_tickets()
                        ->whereMonth('raised_on', $month)
                        ->whereYear('raised_on', $year)
                        ->count(),
                ];
            })
            ->toArray();
    }


    private function member_stats(int $month, int $year): array
    {
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        return Member::with(['tickets.ticket_issues.issue'])
            ->withTrashed()
            ->where('role_id', MemberRoleEnum::MEMBER->id())
            ->get()
            ->filter(function ($member) use ($startOfMonth, $endOfMonth) {
                return $member->member_since <= $endOfMonth &&
                    (
                        is_null($member->member_until) ||
                        $member->member_until >= $startOfMonth
                    );
            })
            ->map(function ($member) use ($month, $year) {
                $tickets_in_month = $member->tickets->filter(fn($ticket) =>
                    $ticket->raised_on->month === $month &&
                    $ticket->raised_on->year === $year
                );

                $issue_names = $tickets_in_month
                    ->flatMap(fn($ticket) =>
                        $ticket->ticket_issues->map(fn($ti) => $ti->issue->name)
                    )
                    ->unique()
                    ->values();

                return [
                    'id' => substr($member->id, -5),
                    'name' => $member->name,
                    'title' => $member->title,
                    'ticket_opened' => $tickets_in_month->count(),
                    'issues' => $issue_names,
                ];
            })
            ->toArray();
    }

}