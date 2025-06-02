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

        .container-2 {
            border: 1px solid rgb(155, 155, 155);
        }

        pre {
            margin-left: 20px;
            font-family: "Times New Roman", serif;
            font-size: 12px;
        }

        .tables th{
            background: #0078e9;
            color: white;
            border: 1px solid rgb(155, 155, 155);
        }

        .tables td{
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
    <h1>Service Request Form</h1>
    <p class="p-area-name">Binus Kamangisan Area, 12 Oktokber 2020</p>


    <div class="container-1">
        <table>
            <tbody>
                <tr><td style="font-weight: bold; width: 170px;">No. SRF:</td><td>WO-2024-001231230981</td></tr>
                <tr><td style="font-weight: bold;">Nama peminta:</td><td>SRF-45678</td></tr>
                <tr><td style="font-weight: bold;">Tipe pekerjaan:</td><td>Maintenance</td></tr>
                <tr><td style="font-weight: bold;">No. pengenal peminta:</td><td>Urgent</td></tr>
                <tr><td style="font-weight: bold;">Dibuat pada:</td><td>Andi Setiawan</td></tr>
                <tr><td style="font-weight: bold;">Tingkat penanganan:</td><td>EMP-78910</td></tr>
                <tr><td style="font-weight: bold;">Tipe permintaan:</td><td>2025-05-17</td></tr>
                <tr><td style="font-weight: bold;">Lokasi:</td><td>Jl. Kelapa Lilin 2 No. 10 Blok NG 5</td></tr>
            </tbody>
        </table>
    </div>


    <h3>Temuan/keluhan</h3>
    <div class="container-2">
        <pre>
Pekerjaan meliputi pengecekan sistem perangkat lunak, pengujian fitur baru,
dan perbaikan bug yang ditemukan oleh tim QA. Selain itu dilakukan pembaruan
dokumentasi teknis dan koordinasi dengan tim UI/UX untuk perbaikan tampilan.
        </pre>
    </div>

    <h3>Dokumen pendukung</h3>
    <div class="container-3">
        <table class="tables">
            <thead>
                <tr>
                    <th>Nama File</th>
                    <th>Link</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Manual Mesin ZX200</td>
                    <td>
                        <img 
                            src="https://fastly.picsum.photos/id/1060/536/354.jpg?blur=2&hmac=0zJLs1ar00sBbW5Ahd_4zA6pgZqCVavwuHToO6VtcYY"
                            width="400"
                        >
                    </td>
                </tr>
                <tr>
                    <td>Manual Mesin ZX200</td>
                    <td>
                        <img 
                            src="https://compote.slate.com/images/22ce4663-4205-4345-8489-bc914da1f272.jpeg?crop=1560%2C1040%2Cx0%2Cy0"
                            width="400"
                        >
                    </td>
                </tr>
                <tr>
                    <td>Manual Mesin ZX200</td>
                    <td>
                        <img 
                            src="https://fastly.picsum.photos/id/1060/536/354.jpg?blur=2&hmac=0zJLs1ar00sBbW5Ahd_4zA6pgZqCVavwuHToO6VtcYY"
                            width="400"
                        >
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
