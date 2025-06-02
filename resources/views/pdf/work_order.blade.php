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

        .container-1, .container-2, .container-3 {
            margin-bottom: 40px;
        }

        .container-2 .section {
            float: left;
            width: 45%;
            margin-right: 4%;
            padding: 10px;
            margin-bottom: 10px;
        }

        .container-2 .section:last-child {
            margin-right: 0;
        }

        .container-2 table {
            width: 100%;
        }

        .container-2 table th {
            background: #0078e9;
            color: white;
            border: 1px solid rgb(155, 155, 155);
        }   

        .container-2 table td {
            border: 1px solid rgb(155, 155, 155);
        }


        .container-3 {
            border: 1px solid rgb(155, 155, 155);
        }

        pre {
            margin-left: 20px;
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }

        .p-area-name {
            font-family: "Times New Roman", serif;
            font-size: 12px;
            color: #5f5f5f;
        }
    </style>
</head>
<body>
    <h1>Work Order Report</h1>
    <p class="p-area-name">Binus Kamangisan Area, 12 Oktokber 2020</p>


    <div class="container-1">
        <table>
            <tbody>
                <tr><td style="font-weight: bold; width: 170px;">No. WO:</td><td>WO-2024-001231230981</td></tr>
                <tr><td style="font-weight: bold;">Ref. No. SRF:</td><td>SRF-45678</td></tr>
                <tr><td style="font-weight: bold;">Tipe pekerjaan:</td><td>Maintenance</td></tr>
                <tr><td style="font-weight: bold;">Tingkat penanganan:</td><td>Urgent</td></tr>
                <tr><td style="font-weight: bold;">Nama penerbit:</td><td>Andi Setiawan</td></tr>
                <tr><td style="font-weight: bold;">No. pengenal penerbit:</td><td>EMP-78910</td></tr>
                <tr><td style="font-weight: bold;">Diterbitkan pada:</td><td>2025-05-17</td></tr>
                <tr><td style="font-weight: bold;">Lokasi:</td><td>Jl. Kelapa Lilin 2 No. 10 Blok NG 5</td></tr>
            </tbody>
        </table>
    </div>

    <h3>Tiket didelegasikan kepada</h3>
    <div class="container-2">
        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Posisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leftTable as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['title'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Posisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rightTable as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['title'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <h3 style="margin-top: 150px;">Uraian pekerjaan</h3>
    <div class="container-3">
        <pre>
Pekerjaan meliputi pengecekan sistem perangkat lunak, pengujian fitur baru,
dan perbaikan bug yang ditemukan oleh tim QA. Selain itu dilakukan pembaruan
dokumentasi teknis dan koordinasi dengan tim UI/UX untuk perbaikan tampilan.
        </pre>
    </div>
</body>
</html>
