<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            padding: 70px;
            font-family: "Morphe", sans-serif;  
            font-size: 11px;
            line-height: 1.5;
            background-image: url('storage/document-background.jpg');
            background-size: cover;      
            background-repeat: no-repeat;
            background-position: center; 
            background-attachment: fixed;
        }

        .sections {
            margin-top: 12px;
            page-break-inside: unset;
            width: 100%;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        h3 {
            font-size: 13px;
            margin: 8px 0;
        }

        .page-break {
            page-break-before: always;
            break-before: page; 
        }

        /* report header */

        .report-header {
            width: 100%;
            margin-bottom: 30px;
        }
        
        .report-title, .report-location {
            vertical-align: top;
        }

        .report-date {
            color: rgba(0, 0, 0, 0.5);
            font-weight: normal;
        }

        .report-location {
            text-align: right;
        }

        /* ticket summary */


        .ticket-summary-cell {
            width: 25%;
        }

        .summary-label {
            font-size: 12px;
            color: rgba(0, 0, 0, 0.5);
        }

        .summary-title {
            font-size: 14px;
        }

        .summary-value {
            font-weight: bold;
            font-size: 24px;
        }

        /* performance summary */
        
        .performance-summary {
            border-collapse: collapse; 
        }
        
        .performance-summary .metric-cell, .department-performance-cell, .monthly-chart-cell, .overall-chart-cell {
            border: 1px solid black;
        }

        .performance-summary .metric-cell {
            width: 25%;
            height: 20px;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .performance-summary .monthly-chart-cell, .overall-chart-cell {
            text-align: center;
        }

        .performance-summary .department-performance-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 20px;
        }
        
        .performance-summary .metric-cell .metric-label {
            font-size: 12px;
            color: rgba(0, 0, 0, 0.5);
        }

        .performance-summary .metric-cell .metric-title {
            font-size: 14px;
            width: 120px;
        }

        .performance-summary .metric-cell .metric-value {
            font-weight: bold;
            font-size: 24px;
            margin-top: 4px; 
            line-height: 1;
        }

        .performance-summary .monthly-chart-cell, .overall-chart-cell {
            padding: 10px 0;
        }

        .performance-summary .monthly-chart-cell .metric-title,
        .overall-chart-cell .metric-title {
            margin-bottom: 20px;
            color: rgba(0, 0, 0, 0.5);
        }

        .chart-cell, .department-info{
            height: 120px;
            vertical-align: top;
            text-align: left;
        }


        .department-info .department-name {
            font-size: 16px;
            font-weight: bold;
        }
        
        /* staffs database */

        .staffs-database {
            width: 100%;
        }

        .staffs-database .staff-table {
            width: 100%;
            border-collapse: collapse;
        }

        .staffs-database .staff-table td {
            border: 1px solid black;
            padding: 10px;
            width: 50%;
        }

        .staffs-database .staff-table th {
            border: 1px solid black;
            text-align: left;
            padding: 10px;
            background: rgb(226, 226, 226);
        }

        .staffs-database .department-title {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <table class="report-header">
        <tr>
            <td class="report-title">
                <h1 class="report-title">Periodic Report</h1>
                <h1 class="report-date">{{ $header['date'] }}</h1>
            </td>
            <td class="report-location">
                <h1 class="report-location">{{ $header['area'] }}</h1>
            </td>
        </tr>
    </table>


    <table class="sections ticket-summary">
        <tr class="ticket-summary-row">
            <td class="ticket-summary-cell">
                <p class="summary-label">This month’s</p>
                <p class="summary-title">Nº of Opened Tickets</p>
                <p class="summary-value">{{ $opened_this_month }}</p>
            </td>
            <td class="ticket-summary-cell">
                <p class="summary-label">This month’s</p>
                <p class="summary-title">Nº of Closed Tickets</p>
                <p class="summary-value"> {{ $closed_this_month }} </p>
            </td>
            <td class="ticket-summary-cell">
                <p class="summary-label">Total</p>
                <p class="summary-title">Nº of Opened Tickets</p>
                <p class="summary-value"> {{ $opened_total }} </p>
            </td>
            <td class="ticket-summary-cell">
                <p class="summary-label">Total</p>
                <p class="summary-title">Nº of Closed Tickets</p>
                <p class="summary-value"> {{ $closed_total }} </p>
            </td>
        </tr>
    </table>

    <table class="sections performance-summary">
        <tr>
            <td class="metric-cell">
                <p class="metric-label">This month’s</p>
                <p class="metric-title">Average response duration</p>
                <p class="metric-value" style="width: 120px;">{{ $avg_response_time }}</p>
            </td>
            <td class="metric-cell">
                <p class="metric-label">This month’s</p>
                <p class="metric-title">SLA Compliance Rate</p>
                <p class="metric-value" style="font-size: 55px;">{{ $compliance_rate }}</p>
            </td>
            <td rowspan="3" class="department-performance-cell">
                <table class="department-performance-table">
                    @foreach ($issues as $issue)
                        <tr>
                            <td class="chart-cell">
                                <img src="{{ $issue['doughnut_chart'] }}" alt="Engineering Chart" width="150">
                            </td>
                            <td class="department-info">
                                <p class="department-name">{{$issue['name']}}</p>
                                <p class="ticket-stats">{{$issue['resolved_count']}} solved tickets / {{$issue['ticket_count']}} total.</p>
                                <p class="staff-count">{{$issue['maintainer_count']}} staffs</p>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="monthly-chart-cell">
                <p class="metric-title">This month’s</p>
                <img src="{{ $chart_pie_issues['montly'] }}" alt="This Month Pie Chart" width="350">
            </td>
        </tr>
        <tr>
            <td colspan="2" class="overall-chart-cell">
                <p class="metric-title">Overall</p>
                <img src="{{ $chart_pie_issues['overall'] }}" alt="Overall Pie Chart" width="350">
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <div class="sections staffs-database">
        <div class="department-block">
            <h2 class="department-title">Crew Statistics</h2>
            <table class="staff-table">
                <thead>
                    <tr>
                        <th class="table-header">Id</th>
                        <th class="table-header">Name</th>
                        <th class="table-header">Title</th>
                        <th class="table-header">Specialties</th>
                        <th class="table-header">HTC</th>
                    </tr>
                </thead>
                <tbody>
                    
                    @foreach ($crew_statistic as $crew)
                        <tr>
                            <td>{{$crew['id']}}</td>
                            <td> {{$crew['name']}} </td>
                            <td>{{$crew['title']}}</td>
                            <td> {{ collect($crew['specialties'])->map(fn($s) => Str::title($s))->implode(', ') }} </td>
                            <td>{{$crew['HTC']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 15px;">
                <p style="color: gray">
                    <b>HTC</b>
                    The number of tickets to which the staff contributed to.
                </p>
            </div>
        </div>

        <div class="department-block">
            <h2 class="department-title">Management Statistics</h2>
            <table class="staff-table">
                <thead>
                    <tr>
                        <th class="table-header">Id</th>
                        <th class="table-header">Name</th>
                        <th class="table-header">Title</th>
                        <th class="table-header">ATC</th>
                        <th class="table-header">ETC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($management_statistics as $management)
                        <tr>
                            <td>{{$management['id']}}</td>
                            <td>{{$management['name']}}</td>
                            <td>{{$management['title']}}</td>
                            <td>{{$management['assessed_count']}}</td>
                            <td>{{$management['evaluated_count']}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="margin-top: 15px;">
                <p style="color: gray">
                    <b>ATC</b>
                    The number of tickets which were assessed by a give staff.
                </p>
                <p style="color: gray">
                    <b>ETC</b>
                    The number of work evaluation done by the given staff.
                </p>
            </div>
        </div>

        <div class="department-block">
            <h2 class="department-title">Member Statistics</h2>
            <table class="staff-table">
                <thead>
                    <tr>
                        <th class="table-header">Id</th>
                        <th class="table-header">Name</th>
                        <th class="table-header">Title</th>
                        <th class="table-header">OTC</th>
                        <th class="table-header">Top 5 Issues Types</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($member_statistics as $member)
                        <tr>
                            <td>{{$member['id']}}</td>
                            <td>{{$member['name']}}</td>
                            <td>{{$member['title']}}</td>
                            <td>{{$member['ticket_opened']}}</td>
                            <td>{{ collect($member['issues'])->map(fn($issue) => Str::title($issue))->implode(', ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 15px;">
                <p style="color: gray">
                    <b>OTC</b>
                    The number of tickets which were opened by given member.
                </p>
            </div>
        </div>
    </div>
    
<script type="text/php">
    if (isset($pdf)) {
        $w = $pdf->get_width();
        $h = $pdf->get_height();

        $pageText = "Page {PAGE_NUM} of {PAGE_COUNT} | May 2025";
        

        $fullText = $pageText;
        $font = null;
        $size = 8;
        $x = ($w - 200) / 2;
        $y = $h - 30;

        $pdf->page_text($x + 70 , $y, $fullText, $font, $size, array(0, 0, 0));
    }
</script>
</body>
</html>