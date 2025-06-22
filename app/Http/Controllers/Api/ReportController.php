<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function periodic(string $_month)
    {
        try {

            $validator = Validator::make(['month' => $_month], [
                'month' => 'required|string|in:january,february,march,april,may,june,july,august,september,october,november,december',
            ]);

            if ($validator->fails()) {
                return $this->apiResponseService->badRequest('Invalid month.');
            }

            $month_in_number = Carbon::parse('1 ' . ucfirst($_month))->month;
            $year = now()->year;

            $this_month_tickets = Ticket::with('logs','response','ticket_issues.issue')->whereMonth('raised_on', $month_in_number)->whereYear('raised_on', $year)->get();

            $this_month_closed_tickets = $this_month_tickets->where('status_id', TicketStatusEnum::CLOSED->id());

            $ttw_ticket = 0;

            foreach ($this_month_closed_tickets as $ticket) {
                
                $assessment_log = $ticket->logs->where('type_id', TicketLogTypeEnum::ASSESSMENT->id())->first();
                $evaluation_log = $ticket->logs->where('type_id', TicketLogTypeEnum::OWNER_EVALUATION_REQUEST->id())->first();
                $work_progress_log = $ticket->logs->where('type_id', TicketLogTypeEnum::WORK_PROGRESS->id())->first();

                $total_extension_seconds = 0;

                $logs = $ticket->logs->sortBy('recorded_on')->values(); 

                foreach ($logs as $i => $log) {
                    if ($log->type_id === TicketLogTypeEnum::TIME_EXTENSION->id()) {
                        $nextLog = $logs->get($i + 1);

                        if ($nextLog) {
                            $start = Carbon::parse($log->recorded_on);
                            $end = Carbon::parse($nextLog->recorded_on);

                            $total_extension_seconds += $end->diffInSeconds($start,true);
                        }
                    }
                }

                if (!$assessment_log || !$evaluation_log || !$work_progress_log) {
                    continue;
                }

                $ticket_response_duration = $ticket->raised_on->diffInSeconds($assessment_log->recorded_on);
                $ticket_resolved_duration = $work_progress_log->recorded_on->diffInSeconds($evaluation_log->recorded_on);
                $ticket_total_duration = $ticket_response_duration + $ticket_resolved_duration - $total_extension_seconds;

                $ptl_response_duration = $ticket->response->sla_modifier * (SystemSetting::get('sla_response') ?? 86400);

                $ptl_resolved_duration = $ticket->ticket_issues
                    ->map(function ($ticket_issue) {
                        return $ticket_issue->issue->sla_hours * 3600;
                    })->max(); 
                $ptl_total_duration = $ptl_response_duration + $ptl_resolved_duration;

                if ($ticket_total_duration <= $ptl_total_duration) {
                    $ttw_ticket++;
                }
            }

            $total_ticket = $this_month_closed_tickets->count();

            $compliance_rate = $total_ticket > 0 ? round(($ttw_ticket / $total_ticket) * 100, 1) : 0;


            $total_response_seconds = 0;
            $response_count = 0;

            foreach ($this_month_tickets as $ticket) {
                $assessment_log = $ticket->logs
                    ->where('type_id', TicketLogTypeEnum::ASSESSMENT->id())
                    ->first();

                if (!$assessment_log) {
                    continue;
                }

                $response_time = $ticket->raised_on->diffInSeconds($assessment_log->recorded_on);
                
                $total_response_seconds += $response_time;
                $response_count++;
            }

            if ($response_count > 0) {
                $avg_seconds = (int) round($total_response_seconds / $response_count);
                $avg_response_time = CarbonInterval::seconds($avg_seconds)->cascade()->format('%h hours %i minutes');
            } else {
                $avg_response_time = '0 hours 0 minutes';
            }


            $issue_stats = [];

            foreach (IssueTypeEnum::cases() as $issueEnum) {
                $issue_id = $issueEnum->id();

                $related_ticket_issues = TicketIssue::with('maintainers') 
                    ->whereHas('ticket', function ($query) use ($month_in_number, $year) {
                        $query->whereMonth('raised_on', $month_in_number)
                            ->whereYear('raised_on', $year);
                    })
                    ->where('issue_id', $issue_id)
                    ->get();

                $ticket_ids = $related_ticket_issues->pluck('ticket_id')->unique();
                $resolved_ticket_ids = $related_ticket_issues
                    ->whereNotNull('resolved_on')
                    ->pluck('ticket_id')
                    ->unique();
                $distinct_maintainers = $related_ticket_issues
                    ->flatMap(fn($ti) => $ti->maintainers->pluck('id'))
                    ->unique();

                $total = $ticket_ids->count();
                $resolved = $resolved_ticket_ids->count();
                $percent = $total > 0 ? round(($resolved / $total) * 100, 2) : 0;

                $color = sprintf("#%02x%02x%02x", random_int(0, 255), random_int(0, 255), random_int(0, 255));

                $chart_url = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
                    'type' => 'doughnut',
                    'data' => [
                        'datasets' => [[
                            'data' => [$resolved, max(0, $total - $resolved)],
                            'backgroundColor' => [$color, '#e8e8e8'],
                            'borderWidth' => 0
                        ]]
                    ],
                    'options' => [
                        'plugins' => [
                            'legend' => false,
                            'doughnutlabel' => [
                                'labels' => [[
                                    'text' => $percent . '%',
                                    'font' => ['size' => 60]
                                ]]
                            ],
                            'datalabels' => ['display' => false]
                        ],
                        'cutoutPercentage' => 70
                    ]
                ]));

                $issue_stats[] = [
                    'name' => $issueEnum->name,
                    'ticket_count' => $total,
                    'resolved_count' => $resolved,
                    'maintainer_count' => $distinct_maintainers->count(),
                    'doughnut_chart' => $chart_url
                ];
            }

            $this_month_piechart = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
                'type' => 'outlabeledPie',
                'data' => [
                    'labels' => collect(IssueTypeEnum::cases())->map(fn($issue) => $issue->name)->values(),
                    'datasets' => [[
                        'backgroundColor' => [
                            '#FF3784', 
                            '#36A2EB',
                            '#4BC0C0',
                            '#F77825',
                            '#9966FF',
                            '#00C49F',
                            '#FFBB28',
                            '#C71585', 
                            '#0088FE', 
                            '#A52A2A'  
                        ],
                        'data' => collect(IssueTypeEnum::cases())->map(function ($issue) use ($month_in_number, $year) {
                            return TicketIssue::where('issue_id', $issue->id())
                                ->whereHas('ticket', function ($query) use ($month_in_number, $year) {
                                    $query->whereMonth('raised_on', $month_in_number)
                                        ->whereYear('raised_on', $year);
                                })
                                ->count();
                        })->values(),
                    ]]
                ],
                'options' => [
                    'plugins' => [
                        'legend' => false,
                        'outlabels' => [
                            'text' => '%l %p',
                            'color' => 'white',
                            'stretch' => 35,
                            'font' => [
                                'resizable' => true,
                                'minSize' => 14,
                                'maxSize' => 15
                            ]
                        ]
                    ]
                ]
            ]));

            

            $overall_piechart_url = 'https://quickchart.io/chart?c=' . urlencode(json_encode([
                'type' => 'outlabeledPie',
                'data' => [
                    'labels' => collect(IssueTypeEnum::cases())->map(fn($issue) => $issue->name)->values(),
                    'datasets' => [[
                        'backgroundColor' => [
                            '#FF3784', 
                            '#36A2EB',
                            '#4BC0C0',
                            '#F77825',
                            '#9966FF',
                            '#00C49F',
                            '#FFBB28',
                            '#C71585', 
                            '#0088FE', 
                            '#A52A2A'  
                        ],
                        'data' => collect(IssueTypeEnum::cases())->map(function ($issue) {
                            return TicketIssue::where('issue_id', $issue->id())
                                ->whereHas('ticket', function ($q) {})
                                ->count();
                        })->values(),
                    ]]
                ],
                'options' => [
                    'plugins' => [
                        'legend' => false,
                        'outlabels' => [
                            'text' => '%l %p',
                            'color' => 'white',
                            'stretch' => 35,
                            'font' => [
                                'resizable' => true,
                                'minSize' => 14,
                                'maxSize' => 15
                            ]
                        ]
                    ]
                ]
            ]));

            $document_data = [
                'header' => [
                    'date' => ucfirst($_month) . ' ' . now()->format('Y'),
                    'area' => SystemSetting::get('area_name') ?? 'Area name not set yet',
                ],
                'opened_this_month' => $this_month_tickets->count(),
                'closed_this_month' => $total_ticket,
                'opened_total' => Ticket::count(),
                'closed_total' => Ticket::where('status_id', TicketStatusEnum::CLOSED->id())->count(),
                'compliance_rate' => $compliance_rate,
                'avg_response_time' => $avg_response_time,
                'issues' => $issue_stats,
                'chart_pie_issues' => [
                    'montly' => $this_month_piechart,
                    'overall' => $overall_piechart_url,
                ],
                'crew_statistic' => Member::with('specialities')
                    ->where('role_id', MemberRoleEnum::CREW->id())
                    ->get()
                    ->map(function ($member) use ($month_in_number, $year) {
                        return [
                            'id' => substr($member->id,-5),
                            'name' => $member->name,
                            'title' => $member->title,
                            'specialties' => $member->specialities->pluck('name'),
                            'HTC' => TicketIssue::whereHas('maintainers', function ($q) use ($member) {
                                    $q->where('member_id', $member->id);
                                })
                                ->whereHas('ticket', function ($q) use ($month_in_number, $year) {
                                    $q->whereMonth('raised_on', $month_in_number)
                                        ->whereYear('raised_on', $year);
                                })
                                ->distinct('ticket_id')
                                ->count()
                        ];
                    }),
                'management_statistics' => Member::where('role_id', MemberRoleEnum::MANAGEMENT->id())
                    ->get()
                    ->map(function ($member) use ($month_in_number, $year) {
                        return [
                            'id' => substr($member->id,-5),
                            'name' => $member->name,
                            'title' => $member->title,
                            'assessed_count' => $member->assessed_tickets()
                                ->whereMonth('raised_on', $month_in_number)
                                ->whereYear('raised_on', $year)
                                ->count(),
                            'evaluated_count' => $member->evaluated_tickets()
                                ->whereMonth('raised_on', $month_in_number)
                                ->whereYear('raised_on', $year)
                                ->count(),
                        ];
                    }),
                'member_statistics' => Member::with(['tickets.ticket_issues.issue'])
                    ->where('role_id', MemberRoleEnum::MEMBER->id())
                    ->get()
                    ->map(function ($member) use ($month_in_number, $year) {
                        $tickets_in_month = $member->tickets
                            ->filter(fn($ticket) =>
                                $ticket->raised_on->month === $month_in_number &&
                                $ticket->raised_on->year === $year
                            );

                        $issue_names = $tickets_in_month
                            ->flatMap(fn($ticket) => $ticket->ticket_issues->map(fn($ti) => $ti->issue->name))
                            ->unique()
                            ->values();

                        return [
                            'id' => substr($member->id, -5),
                            'name' => $member->name,
                            'title' => $member->title,
                            'ticket_opened' => $tickets_in_month->count(),
                            'issues' => $issue_names,
                        ];
                    }),
            ];


            $document = Pdf::loadView('pdf.periodic_report', $document_data)
                        ->setPaper('a4', 'portrait')
                        ->output();

            return response($document)->header('Content-Type', 'application/pdf');

        } catch (Throwable $e) {
            Log::error('Failed to generate periodic report', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Failed to generate periodic report.');
        }
    }

    public function ticket(string $_month)
    {
        try
        {
            $validator = Validator::make(['month' => $_month], [
                'month' => 'required|string|in:january,february,march,april,may,june,july,august,september,october,november,december',
            ]);

            if ($validator->fails()) {
                return $this->apiResponseService->badRequest('Invalid month.');
            }

            $monthNumber = Carbon::parse('1 ' . $_month)->month;
            $year = now()->year;

            $tickets = Ticket::whereMonth('raised_on', $monthNumber)
                            ->whereYear('raised_on', $year)
                            ->get();

            $document_data = [
                'header' => [
                    'date' => ucfirst($_month) . ' ' . now()->format('Y'),
                    'area' => SystemSetting::get('area_name') ?? 'Area name not set yet',
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

            $document = Pdf::loadView('pdf.ticket_report', $document_data)
                        ->setPaper('a4', 'portrait')
                        ->output();

            return response($document)->header('Content-Type', 'application/pdf');

        }
        catch (Throwable $e)
        {
            Log::error('Failed to generate tickets report', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->apiResponseService->internalServerError('Failed to generate tickets report.');
        }
    }
}
