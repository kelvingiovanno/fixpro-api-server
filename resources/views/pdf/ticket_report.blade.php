<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: "Times New Roman", serif;
            margin: 20px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        h3 {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            padding: 4px;
            vertical-align: top;
            font-size: 12px;
            text-align: left;
        }

        .container-1 {
            margin-top: 20px;
        }

        .container-1, .container-2 {
            margin-bottom: 50px;
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
    <h1>Ticket Report</h1>
    <p class="p-area-name"> Area, 12 Oktokber 2020</p>


    <div class="container-1">
        <table>
            <tbody>
                <tr><td style="font-weight: bold; width: 170px;">No. Ticket:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;">Diangkat pada:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;">Diselesaikan pada:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;"> Diperiksa oleh:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;">Status tiket:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;">Nama peminta:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;">No. pengenal peminta:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;">Tipe pekerjaan:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;">Tingkat penanganan:</td><td>xxx</td></tr>
                <tr><td style="font-weight: bold;">Lokasi:</td><td>xxx</td></tr>
            </tbody>
        </table>
    </div>


    <h3>Temuan/keluhan</h3>
    <div class="container-2">
        <pre>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Quos 
            commodi illum laborum enim asperiores esse, sed corporis magni quae libero.
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
                    <td>Gambar 1 keramik pecah</td>
                    <td>
                        <img 
                            src="https://pbs.twimg.com/media/C9yiWbLUAAIyiRg?format=jpg&name=medium"
                            width="120"
                            style="margin-top: 8px;"
                        />
                    </td>
                </tr>
                <tr>
                    <td>Gambar 2 pipa bocor</td>
                    <td>
                        <img 
                            src="https://i0.wp.com/selayarnews.com/wp-content/uploads/2022/02/IMG-20220219-WA0034.jpg?fit=721%2C1280&ssl=1"
                            width="120"
                            style="margin-top: 8px;"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>Kronologi</h3>
    <div class="container-4">
        <table class="tables">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Log Type</th>
                    <th>News</th>
                    <th>UUID Pengangkat</th>
                    <th>Nama Pengangkat</th>
                    <th>Documen Pendukung</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2025-05-01</td>
                    <td>Work Progress</td>
                    <td>Proses memperbaiki.</td>
                    <td>c9b1e123-4d3a-4e3c-b1f0-88e91aee6d52</td>
                    <td>Ahmad Santoso</td>
                    <td>
                        <img 
                            src="https://dokterpipa.com/wp-content/uploads/2020/01/pipa-bocor-sambungan-Small.jpg"
                            width="100"
                            style="margin-top: 8px;"
                        />
                    </td>
                </tr>
                <tr>
                    <td>2025-05-01</td>
                    <td>Work Progress</td>
                    <td>Proses memperbaiki.</td>
                    <td>c9b1e123-4d3a-4e3c-b1f0-88e91aee6d52</td>
                    <td>Moona Hoshinova</td>
                    <td>
                        <img 
                            src="https://aquaproof.co.id/storage/images/newsrooms/TCzGIhjW4IgF5dRiNL2cf8GZwQ91KKuxsG4GDF7B.png"
                            width="100"
                            style="margin-top: 8px;"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
