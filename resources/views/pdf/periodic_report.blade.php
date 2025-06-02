<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", serif;
            margin: 20px;
        }
        h1, h3 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 8px;
            vertical-align: top;
            font-size: 12px;
            text-align: left;
        }

        .container-1 {
            margin-top: 50px;
        }

        .container-1, .container-2 {
            margin-bottom: 40px;
        }

        .tables th {
            background: #0078e9;
            color: white;
            border: 1px solid rgb(155, 155, 155);
        }

        .tables td {
            border: 1px solid rgb(155, 155, 155);
        }

        pre {
            margin-left: 20px;
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }

        .container-3 th{
            background: #0078e9;
            color: white;
        }

        .container-3    td{
            border: 1px solid rgb(155, 155, 155);
        }

        .p-area-name {
            font-family: "Times New Roman", serif;
            font-size: 12px;
            color: #5f5f5f;
        }
    </style>
</head>
<body>
    <h1>Periodic Report</h1>
    <p class="p-area-name">Binus Kamangisan Area, 12 Oktokber 2020</p>


    <div class="container-1">
        <table>
            <tbody>
                <tr><td style="font-weight: bold; width: 170px;">Area Name:</td><td>WO-2024-001231230981</td></tr>
                <tr><td style="font-weight: bold;">Requested by:</td><td>SRF-45678</td></tr>
                <tr><td style="font-weight: bold;">Requested on:</td><td>Maintenance</td></tr>
            </tbody>
        </table>
    </div>  


    <h3>Overall Ticket Summary</h3>
    <div class="container-2">
        <table class="tables">
            <thead>
                <tr>
                    <th>Ticket Status</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Opened</td>
                    <td>37</td>
                </tr>
                <tr>
                    <td>Closed</td>
                    <td>22</td>
                </tr>
                <tr>
                    <td>Rejected</td>
                    <td>22</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>Top Reported Issues</h3>
    <div class="container-2">
        <table class="tables">
            <thead>
                <tr>
                    <th>Issue</th>
                    <th>Count</th>
                    <th>Avg. Solve Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Login Failure</td>
                    <td>10</td>
                    <td>2h 15m</td>
                </tr>
                <tr>
                    <td>System Crash</td>
                    <td>5</td>
                    <td>4h 30m</td>
                </tr>
                <tr>
                    <td>Network Issue</td>
                    <td>7</td>
                    <td>1h 45m</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>Tickets by Member</h3>
    <div class="container-3">
        <table class="tables">
            <thead>
                <tr>
                    <th>Name Member</th>
                    <th>No. Tickets Raised</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sarah D.</td>
                    <td>8</td>
                </tr>
                <tr>
                    <td>James T.</td>
                    <td>12</td>
                </tr>
                <tr>
                    <td>Ani R.</td>
                    <td>5</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>Crew Contributions</h3>
    <div class="container-4">
        <table class="tables">
            <thead>
                <tr>
                    <th>Crew Name</th>
                    <th>Contributions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tech Support</td>
                    <td>32</td>
                </tr>
                <tr>
                    <td>Development Team</td>
                    <td>18</td>
                </tr>
                <tr>
                    <td>QA Team</td>
                    <td>27</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>Management Evaluation</h3>
    <div class="container-5">
        <table class="tables">
            <thead>
                <tr>
                    <th>Management Name</th>
                    <th>Tickets Evaluated</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Rina M.</td>
                    <td>15</td>
                </tr>
                <tr>
                    <td>Jonathan K.</td>
                    <td>19</td>
                </tr>
                <tr>
                    <td>Linda S.</td>
                    <td>10</td>
                </tr>
            </tbody>
        </table>
    </div>


    <h3>Ticket Records</h3>
    <div class="container-6">
        <table class="tables">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Ticket Identifier</th>
                    <th>Location</th>
                    <th>Stated Issue</th>
                    <th>Type</th>
                    <th>Response Level</th>
                    <th>Status</th>
                    <th>Handlers</th>
                    <th>Evaluator</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>b8458b22-02c1-47b5-b225-1e25b52a2e4a</td>
                    <td>Jakarta HQ</td>
                    <td>Login not working</td>
                    <td>Bug</td>
                    <td>High</td>
                    <td>Closed</td>
                    <td>Andi, Budi</td>
                    <td>Sarah</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>b8458b22-02c1-47b5-b225-1e25b52a2e4a</td>
                    <td>Bandung Branch</td>
                    <td>System crash on load</td>
                    <td>Error</td>
                    <td>Critical</td>
                    <td>Open</td>
                    <td>Sinta</td>
                    <td>Jonathan</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>b8458b22-02c1-47b5-b225-1e25b52a2e4a</td>
                    <td>Surabaya Office</td>
                    <td>Printer offline</td>
                    <td>Hardware</td>
                    <td>Medium</td>
                    <td>In Progress</td>
                    <td>Agus</td>
                    <td>Linda</td>
                </tr>
            </tbody>
        </table>
    </div>
</html>
