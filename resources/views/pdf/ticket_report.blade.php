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
                <h1 class="report-title">Tickets Report</h1>
                <h1 class="report-date">{{ $header['date'] }}</h1>
            </td>
            <td class="report-location">
                <h1 class="report-location">{{ $header['area'] }}</h1>
            </td>
        </tr>
    </table>

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

                @foreach ($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket['id'] }}</td>
                        <td>{{ $ticket['raised'] }}</td>
                        <td>{{ $ticket['closed'] }}</td>
                        <td>{{ implode(', ', $ticket['issues']->toArray()) }}</td>
                        <td>
                            @if ($ticket['before'])
                                <img src="{{ $ticket['before'] }}" alt="Before" width="100px" height="100px">
                            @else
                                <span>No image</span>
                            @endif
                        </td>
                        <td>
                            @if ($ticket['after'])
                                <img src="{{ $ticket['after'] }}" alt="After" width="100px" height="100px">
                            @else
                                <span>No image</span>
                            @endif
                        </td>
                        <td>{{ implode(', ', $ticket['handlers']->toArray()) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>