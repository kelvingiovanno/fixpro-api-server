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

    .logs .log-rows {
        border: 1px solid black;
    }

    .logs .column {
        width: 50%;
    }

    .log-images {
        margin-top: 40px;
        padding: 20px;
    }
    .log-details {
        margin-top: 20px;
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
    <p class="work-order-id">WO0123102-1293192/12</p>
    <p class="work-order-area">PT XYZ  •  Thu, 05-06-2025</p>
  </div>

  <div class="section ticket-details">
    <h3>Details</h3>
    <table>
      <tr>
        <td>
          <table class="section-1">
            <tr><td>Ticket No.</td><td>:</td><td>e4c7f3c2-bc2b-4f90-82f5-8a2a1c4b9e19</td></tr>
            <tr><td>Created At</td><td>:</td><td>2025-06-01 10:45</td></tr>
            <tr><td>Completed At</td><td>:</td><td>2025-06-03 15:20</td></tr>
            <tr><td>Reviewed By</td><td>:</td><td>Asep Nugraha</td></tr>
            <tr><td>Ticket Status</td><td>:</td><td>Closed</td></tr>
          </table>
        </td>
        <td>
          <table class="section-2">
            <tr><td>Requester Name</td><td>:</td><td>John Doe</td></tr>
            <tr><td>Identifier No.</td><td>:</td><td>9b2e7e61-d4a4-4e2c-b94c-7f942fa5a28c</td></tr>
            <tr><td>Work Type</td><td>:</td><td>Preventive Maintenance</td></tr>
            <tr><td>Handling Priority</td><td>:</td><td>Urgent</td></tr>
            <tr><td>Location</td><td>:</td><td>Building B, 3rd Floor, Server Room</td></tr>
          </table>
        </td>
      </tr>
    </table>
  </div>

  <div class="section issue-complain">
    <h3>Issues and complaints</h3>
    <div class="content">
      <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Tempore repellat aspernatur hic vero...</p>
    </div>
  </div>

  <div class="section supportive-documents">
    <h3>Supportive Document</h3>
    <div class="documents">
      <img src="storage/dummy-image-building-issue-1.jpg" alt="">
      <img src="storage/dummy-image-building-issue-2.jpg" alt="">
      <img src="storage/dummy-image-building-issue-3.jpg" alt="">
      <img src="storage/dummy-image-building-issue-4.jpg" alt="">
    </div>
  </div>

  <div class="section chronology">
    <div class="chronology-heading">
        <h3>Chronology</h3>
    </div>
    <table class="logs">
        <tr class="log-rows">
            <td class="column">
                <div class="log-images">
                    <img src="storage/dummy-image-building-issue-2.jpg" alt="">
                </div>
            </td>
            <td class="column">
                <table class="log-details">
                    <tr><td>Name</td><td>:</td><td>Jane Smith</td></tr>
                    <tr><td>ID Number</td><td>:</td><td>JS-00123</td></tr>
                    <tr><td>Log Type</td><td>:</td><td>Investigation</td></tr>
                    <tr><td>Raised On</td><td>:</td><td>2025-06-01 14:30</td></tr>
                    <tr><td>News</td><td>:</td><td>Water leak detected in Server Room 3B. Maintenance team dispatched to resolve issue.</td></tr>
                </table>
            </td>
        </tr>
        <tr class="log-rows">
            <td class="column">
                <div class="log-images">
                </div>
            </td>
            <td class="column">
                <table class="log-details">
                    <tr><td>Name</td><td>:</td><td>Jane Smith</td></tr>
                    <tr><td>ID Number</td><td>:</td><td>JS-00123</td></tr>
                    <tr><td>Log Type</td><td>:</td><td>Investigation</td></tr>
                    <tr><td>Raised On</td><td>:</td><td>2025-06-01 14:30</td></tr>
                    <tr><td>News</td><td>:</td><td>Water leak detected in Server Room 3B. Maintenance team dispatched to resolve issue.</td></tr>
                </table>
            </td>
        </tr>
        <tr class="log-rows">
            <td class="column">
                <div class="log-images">
                    <img src="storage/dummy-image-building-issue-1.jpg" alt="">
                    <img src="storage/dummy-image-building-issue-2.jpg" alt="">
                    <img src="storage/dummy-image-building-issue-2.jpg" alt="">
                </div>
            </td>
            <td class="column">
                <table class="log-details">
                    <tr><td>Name</td><td>:</td><td>Jane Smith</td></tr>
                    <tr><td>ID Number</td><td>:</td><td>JS-00123</td></tr>
                    <tr><td>Log Type</td><td>:</td><td>Investigation</td></tr>
                    <tr><td>Raised On</td><td>:</td><td>2025-06-01 14:30</td></tr>
                    <tr><td>News</td><td>:</td><td>Water leak detected in Server Room 3B. Maintenance team dispatched to resolve issue.</td></tr>
                </table>
            </td>
        </tr>
    </table>
  </div>
<script type="text/php">
    if (isset($pdf)) {
        $w = $pdf->get_width();
        $h = $pdf->get_height();

        $pageText = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $customText = "Made available to KELVIN GIOVANNO"; 

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
