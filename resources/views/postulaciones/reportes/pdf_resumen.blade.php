<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Resumen de Postulantes</title>
    <style>
        @page {
            margin: 0.7cm 0.8cm;
            size: A4;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            line-height: 1.1;
            color: #333;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #ec008c;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .header table {
            width: 100%;
        }
        .logo {
            width: 50px;
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
        thead {
            display: table-header-group;
        }
        th {
            background-color: #f2f2f2;
            border: 1px solid #999;
            padding: 3px 4px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
        }
        td {
            border: 1px solid #999;
            padding: 2px 4px;
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


        .layout-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }
        /* Solo las celdas directas de la tabla de diseño son invisibles */
        table.layout-table > tr > td,
        table.layout-table > tbody > tr > td {
            border: none !important;
            vertical-align: top;
            padding: 0;
            background-color: transparent !important;
        }
        
        /* Asegurar bordes en las tablas de datos */
        .table-container table td, 
        .table-container table th {
            border: 1px solid #999 !important;
        }
        .main-col {
            width: 62%;
            padding-right: 15px !important;
        }
        .summary-col {
            width: 38%;
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

        .tr-grupo-a { background-color: #FCE6F4 !important; }
        .tr-grupo-b { background-color: #F1F9E8 !important; }
        .tr-grupo-c { background-color: #E6F7FE !important; }
        .tr-grupo-d { background-color: #FFFEE6 !important; }

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

    <table class="layout-table">
        <tr>
            <td class="main-col">
                <div class="table-container">
                    <div class="table-title">Distribución por Carrera y Aula</div>
                    @php
                        $rowspansGrupo = [];
                        $rowspansCarrera = [];
                        
                        $lastGrupoIdx = -1;
                        $lastCarreraIdx = -1;
                        
                        foreach($tabla1 as $i => $row) {
                            if($row[1] != '') {
                                $lastGrupoIdx = $i;
                                $rowspansGrupo[$i] = 1;
                            } else if($lastGrupoIdx != -1) {
                                $rowspansGrupo[$lastGrupoIdx]++;
                            }
                            
                            if($row[2] != '' && $row[2] != 'Total') {
                                $lastCarreraIdx = $i;
                                $rowspansCarrera[$i] = 1;
                            } else if($lastCarreraIdx != -1 && $row[2] == '') {
                                $rowspansCarrera[$lastCarreraIdx]++;
                            }
                        }
                    @endphp

                    <table>
                        <thead>
                            <tr>
                                <th style="width: 25px;">#</th>
                                <th style="width: 55px;">Grupo</th>
                                <th>Carrera / Grado</th>
                                <th style="width: 60px;">Aula</th>
                                <th style="width: 50px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currentGrupoClass = ''; @endphp
                            @foreach($tabla1 as $index => $row)
                                @php 
                                    $isTotal = $row[2] === 'Total'; 
                                    if ($row[1] != '') {
                                        $letra = strtolower(trim(substr($row[1], -1)));
                                        $currentGrupoClass = 'tr-grupo-' . $letra;
                                    }
                                @endphp

                                <tr class="{{ $isTotal ? 'total-row' : $currentGrupoClass }}" style="page-break-inside: avoid;">
                                    {{-- Columna # --}}
                                    @if(isset($rowspansGrupo[$index]))
                                        <td class="text-center fw-bold" rowspan="{{ $rowspansGrupo[$index] }}">
                                            {{ $row[0] }}
                                        </td>
                                    @endif

                                    {{-- Columna Grupo --}}
                                    @if(isset($rowspansGrupo[$index]))
                                        <td class="text-center fw-bold" rowspan="{{ $rowspansGrupo[$index] }}">
                                            {{ $row[1] }}
                                        </td>
                                    @endif

                                    {{-- Columna Carrera --}}
                                    @if(isset($rowspansCarrera[$index]))
                                        <td rowspan="{{ $rowspansCarrera[$index] }}">
                                            <strong>{{ $row[2] }}</strong>
                                        </td>
                                    @elseif($isTotal)
                                        <td colspan="2" class="text-right"><strong>TOTAL GENERAL</strong></td>
                                    @endif

                                    {{-- Columna Aula y Total --}}
                                    @if(!$isTotal)
                                        <td class="text-center">{{ $row[3] }}</td>
                                        <td class="text-center fw-bold">{{ $row[4] }}</td>
                                    @else
                                        <td class="text-center fw-bold" style="background-color: #FCE6F4;">{{ $row[4] }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </td>
            <td class="summary-col">
                <div class="table-container">
                    <div class="table-title">Resumen por Aula</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Aula</th>
                                <th style="width: 65px;">Postulantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tabla2 as $index => $row)
                                @php $isTotal = $row[0] === 'Total'; @endphp
                                <tr class="{{ $isTotal ? 'total-row' : '' }}" style="page-break-inside: avoid;">
                                    <td>{{ $row[0] }}</td>
                                    <td class="text-center fw-bold">{{ $row[1] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Sistema de Gestión Académica - CEPRE UNAMAD | Generado por {{ Auth::user()->nombre }} el {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
