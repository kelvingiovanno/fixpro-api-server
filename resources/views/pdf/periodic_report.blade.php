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

        /* tickets */

        .tickets {
            width: 100%;
        }

        .tickets .tickets-table {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }

        .tickets th {
            border: 1px solid black;
            text-align: left;
            padding: 10px;
            background: rgb(226, 226, 226);s
        }

        .tickets td {
            border: 1px solid black;
            padding: 10px;
        }

        .tickets .issue {
            padding: 3px 4px;
            background: rgb(255, 183, 183);
            margin: 2px;
            border: 1px solid black;
            display: inline-block;
            border-radius: 2px;
        }

    </style>
</head>
<body>
    <table class="report-header">
        <tr>
            <td class="report-title">
                <h1 class="report-title">Periodic Report</h1>
                <h1 class="report-date">May 2025</h1>
            </td>
            <td class="report-location">
                <h1 class="report-location">Bali Beach Indah</h1>
            </td>
        </tr>
    </table>


    <table class="sections ticket-summary">
        <tr class="ticket-summary-row">
            <td class="ticket-summary-cell">
                <p class="summary-label">This month’s</p>
                <p class="summary-title">Nº of Opened Tickets</p>
                <p class="summary-value">123</p>
            </td>
            <td class="ticket-summary-cell">
                <p class="summary-label">This month’s</p>
                <p class="summary-title">Nº of Closed Tickets</p>
                <p class="summary-value">101</p>
            </td>
            <td class="ticket-summary-cell">
                <p class="summary-label">Total</p>
                <p class="summary-title">Nº of Opened Tickets</p>
                <p class="summary-value">24</p>
            </td>
            <td class="ticket-summary-cell">
                <p class="summary-label">Total</p>
                <p class="summary-title">Nº of Closed Tickets</p>
                <p class="summary-value">15</p>
            </td>
        </tr>
    </table>

    <table class="sections performance-summary">
        <tr>
            <td class="metric-cell">
                <p class="metric-label">This month’s</p>
                <p class="metric-title">Average response duration</p>
                <p class="metric-value" style="width: 120px;">12 mins 30 secs</p>
            </td>
            <td class="metric-cell">
                <p class="metric-label">This month’s</p>
                <p class="metric-title">SLA Compliance Rate</p>
                <p class="metric-value" style="font-size: 55px;">62%</p>
            </td>
            <td rowspan="3" class="department-performance-cell">
                <table class="department-performance-table">
                    <tr>
                        <td class="chart-cell">
                            <img src="{{ $engineering_chart }}" alt="Engineering Chart" width="150">
                        </td>
                        <td class="department-info">
                            <p class="department-name">Engineering</p>
                            <p class="ticket-stats">9 solved tickets / 22 total.</p>
                            <p class="staff-count">19 staffs</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="chart-cell">
                            <img src="{{ $housekeeping_chart }}" alt="Housekeeping Chart" width="150">
                        </td>
                        <td class="department-info">
                            <p class="department-name">Housekeeping</p>
                            <p class="ticket-stats">4 solved tickets / 14 total.</p>
                            <p class="staff-count">5 staffs</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="chart-cell">
                            <img src="{{ $hse_chart }}" alt="HSE Chart" width="150">
                        </td>
                        <td class="department-info">
                            <p class="department-name">HSE</p>
                            <p class="ticket-stats">8 solved tickets / 12 total.</p>
                            <p class="staff-count">8 staffs</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="chart-cell">
                            <img src="{{ $security_chart }}" alt="Security Chart" width="150">
                        </td>
                        <td class="department-info">
                            <p class="department-name">Security</p>
                            <p class="ticket-stats">2 solved tickets / 5 total.</p>
                            <p class="staff-count">11 staffs</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="monthly-chart-cell">
                <p class="metric-title">This month’s</p>
                <img src="{{ $this_month_piechart }}" alt="This Month Pie Chart" width="350">
            </td>
        </tr>
        <tr>
            <td colspan="2" class="overall-chart-cell">
                <p class="metric-title">Overall</p>
                <img src="{{ $overall_piechart }}" alt="Overall Pie Chart" width="350">
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
                    <tr>
                        <td>e3a12f</td>
                        <td>John Doe</td>
                        <td>Janitor</td>
                        <td>HSE, Housekeeping</td>
                        <td>17</td>
                    </tr>
                    <tr>
                        <td>b129j</td>
                        <td>Andika</td>
                        <td>Janitor</td>
                        <td>Housekeeping</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td>e2k2f</td>
                        <td>Rika</td>
                        <td>Janitor</td>
                        <td>Engineering</td>
                        <td>1</td>
                    </tr>
                </tbody>
            </table>
            <div style="margin-top: 15px;">
                <p style="color: gray">
                    <b>ATC</b>
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
                    <tr>
                        <td>81ASD</td>
                        <td>Lina fien</td>
                        <td>TR Receptionist</td>
                        <td>51</td>
                        <td>12</td>
                    </tr>
                    <tr>
                        <td>f1d29j</td>
                        <td>Byalat</td>
                        <td>Building Manager</td>
                        <td>0</td>
                        <td>9</td>
                    </tr>
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
            <h2 class="department-title">Crew Statistics</h2>
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
                    <tr>
                        <td>1k12f</td>
                        <td>Moona</td>
                        <td>LZ12 19/12</td>
                        <td>2</td>
                        <td>HSE, Engineering</td>
                    </tr>
                    <tr>
                        <td>b529j</td>
                        <td>Kaela</td>
                        <td>L123 11/2</td>
                        <td>9</td>
                        <td>Security</td>
                    </tr>
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

    <div class="page-break"></div>


    <div class="sections tickets">

        <h1 class="section-title">All Tickets</h1>
        <h3 class="section-subtitle">Recorded for May 2025</h3> 

        <table class="tickets-table">
            <thead>
                <tr>
                    <th >Id</th>
                    <th style="width: 70px">Raised</th>
                    <th style="width: 70px">Closed</th>
                    <th>Issue types</th>
                    <th>Before</th>
                    <th>After</th>
                    <th>Handlers</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>c1a5d1e9-b82f-4f89-a6d1-34d6d9bfa511</td>
                    <td>2025-05-01</td>
                    <td>2025-05-03</td>
                    <td>
                        <div class="issue">
                            Plumbing    
                        </div>
                        <div class="issue">
                             Facility
                        </div>
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>John, Ali</td>
                </tr>
                <tr>
                    <td>1e3b2a94-9d8b-4fe6-a0a1-7adf652b029d</td>
                    <td>2025-05-05</td>
                    <td>2025-05-06</td>
                    <td>
                        <div class="issue">
                            Housekeeping    
                        </div>
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>Siti</td>
                </tr>
                <tr>
                    <td>5f7c0b2e-21a1-4cb4-923e-20454dc1d442</td>
                    <td>2025-05-08</td>
                    <td>2025-05-09</td>
                    <td>
                        <div class="issue">
                            Social    
                        </div>
                        <div class="issue">
                            Security    
                        </div>
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>Rico, Wulan</td>
                </tr>
                <tr>
                    <td>3bc3fda2-68a4-4e3e-9c33-9bb4623aeb21</td>
                    <td>2025-05-12</td>
                    <td>2025-05-13</td>
                    <td>
                        <div class="issue">
                            Engineering    
                        </div>
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>Dimas</td>
                </tr>
                <tr>
                    <td>8d29ef8f-6b95-4ff4-81cc-e43e1a09b0e9</td>
                    <td>2025-05-15</td>
                    <td>2025-05-17</td>
                    <td>
                        <div class="issue">
                            Security    
                        </div>
                        <div class="issue">
                            Facility
                        </div>    
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>
                        <img src="#" alt="" width="100px" height="100px">
                    </td>
                    <td>Putra, Toni</td>
                </tr>
            </tbody>
        </table>
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