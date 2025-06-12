<!DOCTYPE html>
<html>
<head>
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

        .section {
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 1px 0;
            vertical-align: top;
        }

        .sections {
            margin-top: 12px;
            page-break-inside: avoid;
        }

        .report-header {
            margin-bottom: 10px;
        }

        .details td:first-child, .identity td:first-child {
            width: 140px;
            font-weight: bold;
            color: #555;
        }

        .details td:nth-child(2), .identity td:nth-child(2){
            padding: 0 8px;
            width: 10px;
        }

        .described-content {
            background-color: #f2f2f2;
            min-height: 50px;
            padding: 10px;
            margin-top: 8px; 
        }

        .supportive-documents img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin: 5px;
        }

        .documents {
            margin-top: 40px;
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
        <h1>Service Form</h1>
        <p class="work-order-id">{{ $header['work_order_id'] }}</p>
        <p class="work-order-area">{{$header['area_name']}}  •  {{$header['date']}}</p>
    </div>


    <div class="section identity">
        <h3>Requestor Identity</h3>
        <table>
            <tbody>
                @foreach ($requestor_identity as $key => $value)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $key)) }}:</td> 
                        <td>:</td>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section details">
        <h3>Formally Requests</h3>
        <table>
            <tbody>
                <tr>
                    <td>Work Type</td>
                    <td>:</td>
                    <td>{{ implode(', ', $formally_requests['work_type']) }}</td>
                </tr>
                <tr>
                    <td>Response Level</td>
                    <td>:</td>
                    <td> {{$formally_requests['response_level']}} </td>
                </tr>
                <tr>
                    <td>Location</td>
                    <td>:</td>
                    <td>{{$formally_requests['location']}}</td>
                </tr>
                <tr>
                    <td>That Can Be Described By</td>
                    <td>:</td>
                </tr>   
            </tbody>
        </table>
        <div class="described-content">
            <p>{{$formally_requests['that_can_be_described_by']}}</p>
        </div>
    </div>

    <div class="section supportive-documents">
        <h3>Supportive Document</h3>
        <div class="documents">
            @if (!empty($supportive_documents))
                @foreach ($supportive_documents as $doc)
                    <img src="{{ $doc['image_src'] }}" width="100">
                @endforeach
            @endif
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
