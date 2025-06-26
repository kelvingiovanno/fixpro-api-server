<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Order</title>
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
            page-break-inside: avoid;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        h3 {
            font-size: 13px;
            margin: 8px 0;
        }

        .report-header {
            margin-bottom: 10px;
        }

        .work-order-id,
        .work-order-area {
            font-size: 11px;
            margin-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 1px 0;
            vertical-align: top;
        }

        .work-order-details td:nth-child(2), .requestor-info td:nth-child(2), {
            padding: 0 8px;
            width: 10px;
        }

        .work-order-details td:first-child,
        .requestor-info td:first-child {
            width: 140px;
            font-weight: bold;
            color: #555;
        }

        .delegated-team-table table {
            width: 100%;
            margin-top: 5px;
            font-size: 11px;
        }

        .delegated-team-table th,
        .delegated-team-table td {
            border: 1px solid #999;
            padding: 4px 6px;
            text-align: left;
        }

        .delegated-team-table th {
            background-color: #e6e6e6;
            color: black;
        }

        .note-content {
            background-color: #f2f2f2;
            min-height: 50px;
            padding: 10px;
        }

        .auto-generated{
            width: 100%;
            margin-bottom: 20px;
            position: fixed;
            bottom: 0;
            left: 0; 
            font-size: 10px;
            text-align: center;
        }

        .auto-generated .signature {
            margin-bottom: 50px;
        }
        
        .signature table {
            width: 100%;
        }

        .signature table td {
            text-align: center;
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="report-header">
    <h1>Work Order</h1>
    <p class="work-order-id">{{ $header['work_order_id'] }}</p>
    <p class="work-order-area">{{ $header['area_name'] }}  •  {{ $header['date'] }}</p>
</div>

<div class="sections work-order-details">
    <h3>To Perform</h3>
    <table>
        <tr>
            <td>Work Type</td>
            <td>:</td>
            <td>
                <p>
                    {{ $to_perform['work_type'] }}
                </p>
            </td>
        </tr>
        <tr>
            <td>Response Level</td>
            <td>:</td>
            <td>{{ $to_perform['response_level'] }}</td>
        </tr>
        <tr>
            <td>Location</td>
            <td>:</td>
            <td>{{ $to_perform['location'] }}</td>
        </tr>
        <tr>
            <td>As a Follow-up for</td>
            <td>:</td>
            <td>{{ $to_perform['as_a_follow_up_for'] }}</td>
        </tr>
        <tr>
            <td>Work Directive</td>
            <td>:</td>
            <td>{{ $to_perform['work_directive'] }}</td>
        </tr>
    </table>
</div>

<div class="sections requestor-info">
    <h3>Upon the Request of</h3>
    <table>
        @foreach ($upon_the_request_of as $key => $value)
            <tr>
                <td class="label">{{ ucwords(str_replace('_', ' ', $key)) }}:</td> 
                <td>:</td>
                <td>{{ $value }}</td>
            </tr>
        @endforeach
    </table>
</div>

<div class="sections delegated-team-table">
    <h3>To Be Carried Out By</h3>
    <table>
        <thead>
            <tr><th>Name</th><th>Title</th></tr>
        </thead>
        <tbody>
            @foreach ($to_be_carried_out_by as $crew)
                <tr>
                    <td>{{ $crew['name'] }}</td>
                    <td>{{ $crew['title'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="sections note">
    <h3>Note*</h3>
    <div class="note-content">
        <p>...</p>
    </div>
</div>

<div class="auto-generated">
    <div class="signature">
        <table>
            <tr>
                <td>
                    <strong>Management</strong>
                    <br><br><br><br><br>
                    __________________________<br>
                </td>
                <td>
                    <strong>Member</strong>
                    <br><br><br><br><br>
                    __________________________<br>
                </td>
            </tr>
        </table>
    </div>
    <div class="disclaimer">
        Dokumen ini dibuat secara otomatis oleh sistem komputer dan sah tanpa tanda tangan pejabat yang berwenang atau stempel.
    </div>
</div>
</body>
</html>
