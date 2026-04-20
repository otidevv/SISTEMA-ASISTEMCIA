<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Resumen de Postulantes</title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            line-height: 1.2;
            color: #333;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #ec008c;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .logo {
            width: 70px;
        }
        .header-text {
            text-align: center;
        }
        .header-text h1 {
            margin: 0;
            font-size: 14pt;
            color: #ec008c;
        }
        .header-text h2 {
            margin: 5px 0;
            font-size: 12pt;
            color: #2b5a6f;
        }
        .report-info {
            margin-bottom: 20px;
            font-size: 9pt;
        }
        .report-info table {
            width: 100%;
        }
        .table-container {
            margin-bottom: 30px;
        }
        .table-title {
            background-color: #ec008c;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #eee;
            padding: 5px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        
        /* Zebra striping */
        tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        .total-row {
            background-color: #FCE6F4 !important;
            font-weight: bold;
            color: #ec008c;
        }


        .summary-box {
            width: 40%;
            float: right;
        }
        .main-box {
            width: 58%;
            float: left;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .badge-grupo {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-a { background-color: #FCE6F4; color: #ec008c; }
        .badge-b { background-color: #F1F9E8; color: #8cc63f; }
        .badge-c { background-color: #E6F7FE; color: #00aeef; }
        .badge-d { background-color: #FFFEE6; color: #9c9400; }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 7pt;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="border:none; width: 80px;">
                    <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" class="logo">
                </td>
                <td style="border:none;" class="header-text">
                    <h1>UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</h1>
                    <h2>CENTRO PREUNIVERSITARIO - CEPRE UNAMAD</h2>
                    <h3 style="margin:0; text-transform:uppercase;">Reporte Resumen de Postulantes</h3>
                </td>
                <td style="border:none; width: 80px; text-align: right;">
                    <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" class="logo">
                </td>
            </tr>
        </table>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td><strong>Ciclo:</strong> {{ $ciclo ? $ciclo->nombre : 'Todos los ciclos' }}</td>
                <td style="text-align: right;"><strong>Fecha de Reporte:</strong> {{ date('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="clearfix">
        <div class="main-box table-container">
            <div class="table-title">Distribución por Carrera y Aula</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th style="width: 60px;">Grupo</th>
                        <th>Carrera / Grado</th>
                        <th style="width: 60px;">Aula</th>
                        <th style="width: 50px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tabla1 as $index => $row)
                        @php $isTotal = $row[2] === 'Total'; @endphp

                        <tr class="{{ $isTotal ? 'total-row' : '' }}">
                            <td class="text-center">{{ $row[0] }}</td>
                            <td class="text-center">
                                @if($row[1])
                                    @php 
                                        $letra = strtolower(trim(substr($row[1], -1))); 
                                    @endphp
                                    <span class="badge-grupo badge-{{ $letra }}">{{ $row[1] }}</span>
                                @endif
                            </td>

                            <td>{{ $row[2] }}</td>
                            <td class="text-center">{{ $row[3] }}</td>
                            <td class="text-center fw-bold">{{ $row[4] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary-box table-container">
            <div class="table-title">Resumen por Aula</div>
            <table>
                <thead>
                    <tr>
                        <th>Aula</th>
                        <th style="width: 80px;">N° Postulantes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tabla2 as $index => $row)
                        @php $isTotal = $row[0] === 'Total'; @endphp

                        <tr class="{{ $isTotal ? 'total-row' : '' }}">
                            <td>{{ $row[0] }}</td>
                            <td class="text-center fw-bold">{{ $row[1] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Sistema de Gestión Académica - CEPRE UNAMAD | Generado por {{ Auth::user()->nombre }} el {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
