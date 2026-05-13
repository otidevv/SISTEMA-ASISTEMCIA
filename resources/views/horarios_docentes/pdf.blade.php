<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Oficial - {{ $aula->nombre }}</title>
    <style>
        /* CONFIGURACIÓN FINAL CERTIFICADA - ESTRICTAMENTE UNA SOLA HOJA */
        @page { margin: 0; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            background-color: white;
            line-height: 1.1;
            padding: 0.7cm 1.5cm;
        }

        /* MARCA DE AGUA SUTIL */
        .watermark {
            position: fixed;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            z-index: -1000;
            width: 420px;
        }

        .container { width: 100%; position: relative; }

        /* HEADER */
        .header-table { width: 100%; margin-bottom: 6px; }
        .logo-img { width: 55px; height: auto; }
        .title-box { text-align: center; }
        .title-box h1 { font-size: 21px; color: #003366; font-weight: 900; text-transform: uppercase; margin-bottom: 1px; }
        .title-box p { font-size: 13px; color: #cc0066; font-weight: bold; }

        .vigencia-tag {
            background-color: #cc0066;
            color: white;
            padding: 3px 15px;
            border-radius: 15px;
            font-size: 8.5px;
            font-weight: 900;
            display: inline-block;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .info-bar {
            background-color: #003366;
            color: white;
            padding: 7px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        /* TABLA MAXIMIZADA */
        table.schedule-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 2px solid #000;
            page-break-inside: avoid;
        }

        table.schedule-table th, table.schedule-table td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
        }

        table.schedule-table th {
            background-color: #003366;
            color: white;
            padding: 10px 2px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .time-col {
            background-color: #f8f9fa;
            width: 80px;
            font-weight: 900;
            font-size: 11px;
            color: #003366;
        }

        table.schedule-table td {
            height: 65px;
            padding: 3px;
        }

        .course-block { width: 100%; }
        .course-name { font-weight: 900; font-size: 10.5px; display: block; margin-bottom: 2px; text-transform: uppercase; }
        .teacher-name { font-size: 8.5px; font-weight: bold; opacity: 0.9; }

        /* RECESO */
        .break-cell {
            background-color: #eee !important;
            color: #000;
            font-weight: 900;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 12px;
            height: 35px !important;
        }

        /* RESUMEN COMPACTO */
        .summary-section {
            margin-top: 10px;
            border-top: 2px solid #003366;
            padding-top: 8px;
        }
        .summary-label { font-size: 10px; font-weight: 900; color: #003366; margin-bottom: 6px; display: block; text-transform: uppercase; }
        .summary-item { display: inline-block; width: 19.5%; margin-bottom: 4px; }
        .color-box { width: 10px; height: 10px; display: inline-block; vertical-align: middle; margin-right: 5px; border: 0.5px solid #333; }
        .summary-text { display: inline-block; vertical-align: middle; font-size: 9px; font-weight: bold; }

        /* FOOTER OPTIMIZADO */
        .footer-table {
            width: 100%;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .qr-img { width: 50px; height: 50px; vertical-align: middle; }
        
        .footer-text {
            text-align: right;
            font-size: 9px;
            font-weight: bold;
            color: #444;
            vertical-align: bottom;
        }

        <?php
        function getContrastYIQ($hexcolor){
            if (!$hexcolor || $hexcolor == '#ffffff' || $hexcolor == 'transparent') return '#000000';
            return '#ffffff'; 
        }
        ?>
    </style>
</head>
<body>
    <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" class="watermark">

    @php
        $resumen = [];
        foreach($grilla as $fila) {
            foreach($dias as $dia) {
                $h = $fila[$dia] ?? null;
                if($h && stripos($h->curso->nombre, 'receso') === false) {
                    $nombre = strtoupper($h->curso->nombre);
                    if(!isset($resumen[$nombre])) {
                        $resumen[$nombre] = [
                            'horas' => 0,
                            'color' => $h->curso->color ?? '#ffffff'
                        ];
                    }
                    $resumen[$nombre]['horas']++;
                }
            }
        }
        ksort($resumen);
    @endphp

    <div class="container">
        <table class="header-table">
            <tr>
                <td width="10%"><img src="{{ public_path('assets/images/logo unamad constancia.png') }}" class="logo-img"></td>
                <td class="title-box">
                    <h1>HORARIO ACADÉMICO OFICIAL</h1>
                    <p>CEPRE-UNAMAD | SEDE CENTRAL</p>
                    <div class="vigencia-tag">VÁLIDO PARA EL CICLO {{ strtoupper($ciclo->nombre) }}</div>
                </td>
                <td width="10%" align="right"><img src="{{ public_path('assets/images/logo cepre costancia.png') }}" class="logo-img"></td>
            </tr>
        </table>

        <div class="info-bar">
            AULA: {{ $aula->nombre }} &nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp; TURNO: {{ $turno }}
        </div>

        <table class="schedule-table">
            <thead>
                <tr>
                    <th class="time-col">HORARIO</th>
                    @foreach ($dias as $dia)
                        <th>{{ $dia }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($grilla as $index => $fila)
                    @php
                        $horaFilaParts = explode(' - ', $fila['hora']);
                        $inicioFilaStr = trim($horaFilaParts[0]); 
                        $recesoManana = $ciclo->receso_manana_inicio ? substr($ciclo->receso_manana_inicio, 0, 5) : 'NONE';
                        $recesoTarde  = $ciclo->receso_tarde_inicio ? substr($ciclo->receso_tarde_inicio, 0, 5) : 'NONE';
                        $esReceso = ($inicioFilaStr === $recesoManana || $inicioFilaStr === $recesoTarde);
                    @endphp
                    
                    @if($esReceso)
                        <tr>
                            <td class="time-col">{{ $fila['hora'] }}</td>
                            <td colspan="{{ count($dias) }}" class="break-cell">RECESO INSTITUCIONAL</td>
                        </tr>
                    @else
                        <tr>
                            <td class="time-col" style="height: auto;">{{ $fila['hora'] }}</td>
                            @foreach ($dias as $dia)
                                @php
                                    $span = $rowspans[$index][$dia] ?? 1;
                                    if ($span === 0) continue; // Celda saltada por rowspan

                                    $horario = $fila[$dia] ?? null;
                                    $esCursoReceso = $horario && (stripos($horario->curso->nombre, 'receso') !== false || $horario->curso->nombre === 'RECESO');
                                    $bgColor = '#ffffff';
                                    $textColor = '#000000';
                                    if ($horario && !$esCursoReceso && $horario->curso->color) {
                                        $bgColor = $horario->curso->color;
                                        $textColor = getContrastYIQ($bgColor);
                                    }
                                @endphp
                                
                                <td rowspan="{{ $span }}" style="background-color: {{ $bgColor }}; color: {{ $textColor }}; {{ $span > 1 ? 'height: auto;' : 'height: 40px;' }}">
                                    @if ($horario && !$esCursoReceso)
                                        <div class="course-block">
                                            <span class="course-name">{{ strtoupper($horario->curso->nombre) }}</span>
                                            <span class="teacher-name">{{ $horario->docente->nombre_completo ?? 'Sin docente' }}</span>
                                            @if($span > 1)
                                                <div style="font-size: 7px; margin-top: 2px; opacity: 0.8;">
                                                    {{ substr($horario->hora_inicio, 0, 5) }} - {{ substr($horario->hora_fin, 0, 5) }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="summary-section">
            <span class="summary-label">Resumen de Carga Horaria Semanal:</span>
            <div class="summary-container">
                @foreach($resumen as $curso => $data)
                    <div class="summary-item">
                        <div class="color-box" style="background-color: {{ $data['color'] }};"></div>
                        <span class="summary-text">
                            <strong>{{ $curso }}:</strong> {{ $data['horas'] }} hrs
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <table class="footer-table">
            <tr>
                <td width="10%" align="left">
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" class="qr-img">
                </td>
                <td class="footer-text">
                    Generado por el sistema Portal cepre unamad oficial<br>
                    Fecha de Emisión: {{ now()->format('d/m/Y H:i') }} | ID: {{ strtoupper(uniqid()) }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>