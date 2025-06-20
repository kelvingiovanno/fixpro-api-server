<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
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
</body>
</html>