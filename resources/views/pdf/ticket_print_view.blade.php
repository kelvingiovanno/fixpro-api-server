<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
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

    .section {
      margin-top: 12px;
      page-break-inside: avoid;
    }

    .report-header {
      margin-bottom: 10px;
    }
    .ticket-details td {
      width: 100px;
    }

    .ticket-details .section-1 td:first-child, .ticket-details .section-2 td:first-child {
      width: 30px;  
      font-weight: bold;
      color: #555;
    }

    .ticket-details td:nth-child(2) {
      width: 10px;
      padding: 0 5px;
    }

    .issue-complain .content {
      background-color: #f2f2f2;
      min-height: 50px;
      padding: 10px;
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

    .chronology-heading {
        background-color: #ebebeb;
        border: 1px solid black;
        border-bottom: 0;
        padding: 15px;
    }

    .logs {
        width: 100%;
    }

    .chronology td{
      page-break-inside: avoid;
      break-inside: avoid;
    }

    .logs .log-rows {
      border: 1px solid black;
    }

    .logs .column {
        width: 50%;
    }

    .log-images {
        margin-top: 40px;
    }

    .log-details {
        padding: 20px;
    }

    .log-images img {
      width: 100px;
      height: 100px;
      object-fit: cover;
    }

    .log-details td:first-child {
        width: 100px;
    }

    .log-details td:nth-child(2) {
        padding: 0 8px;
    }
    
  </style>
</head>
<body>

  <div class="report-header">
    <h1>Ticket Print View</h1>
    <p class="work-order-id">{{$header['document_id']}}</p>
    <p class="work-order-area"> {{$header['area_name']}} •  {{$header['date']}}</p>
  </div>

  <div class="section ticket-details">
    <h3>Details</h3>
    <table>
      <tr>
        <td>
          <table class="section-1">
            <tr><td>Ticket No.</td><td>:</td><td> {{$details['ticket_no']}} </td></tr>
            <tr><td>Created At</td><td>:</td><td> {{$details['created_at']}} </td></tr>
            <tr><td>Completed At</td><td>:</td><td> {{$details['completed_at']}} </td></tr>
            <tr><td>Assessed  By</td><td>:</td><td> {{$details['assessed_by']}} </td></tr>
            <tr><td>Evaluated By</td><td>:</td><td> {{$details['evaluated_by']}} </td></tr>
            <tr><td>Current Status</td><td>:</td><td> {{$details['ticket_status']}} </td></tr>
          </table>
        </td>
        <td>
          <table class="section-2">
            <tr><td>Requester Name</td><td>:</td><td> {{$details['requester_name']}} </td></tr>
            <tr><td>Identifier No.</td><td>:</td><td> {{$details['identifier_no']}} </td></tr>
            <tr><td>Work Type</td><td>:</td><td> {{ implode(', ', $details['work_type']) }} </td></tr>
            <tr><td>Handling Priority</td><td>:</td><td> {{$details['handling_priority']}} </td></tr>
            <tr><td>Location</td><td>:</td><td> {{$details['location']}} </td></tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  <div class="section issue-complain">
    <h3>Issues and complaints</h3>
    <div class="content">
      <p>{{$complaints}}</p>
    </div>
  </div>

  <div class="section supportive-documents">
    <h3>Supportive Document</h3>
    <div class="documents">
      @foreach ($supportive_documents as $document)
        <img src="{{ $document }}">
      @endforeach
    </div>
  </div>

<div class="section chronology">
    <div class="chronology-heading">
        <h3>Chronology</h3>
    </div>
    <div class="logs" style="page-break-inside: avoid">
      @foreach ($logs as $log)
        <div class="log-block" style="page-break-inside: avoid; border: 1px solid #000; border-top: none; padding: 5px 10px;">
          <div class="log-images">
            @foreach ($log['supportive_documents'] as $document)
              <img src="{{ $document }}" width="100px">
            @endforeach
          </div>
          <table class="log-details" style="width: 100%;">
            <tr><td>Name</td><td>:</td><td>{{ $log['name'] }}</td></tr>
            <tr><td>ID Number</td><td>:</td><td>{{ $log['id_number'] }}</td></tr>
            <tr><td>Log Type</td><td>:</td><td>{{ $log['log_type'] }}</td></tr>
            <tr><td>Raised On</td><td>:</td><td>{{ $log['raised_on'] }}</td></tr>
            <tr><td>News</td><td>:</td><td style="word-break: break-word;">{{ $log['news'] }}</td></tr>
          </table>
        </div>
      @endforeach
    </div>
</div>

<script type="text/php">
    if (isset($pdf)) {
        $w = $pdf->get_width();
        $h = $pdf->get_height();

        $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $customText = "Made available to {{ $print_view_requested_by }}";

        $fullText = $pageText . " | " . $customText;
        $font = null;
        $size = 8;
        $x = ($w - 200) / 2;
        $y = $h - 30;

        $pdf->page_text($x + 15 , $y, $fullText, $font, $size, array(0, 0, 0));
    }
</script>

</body>
</html>
