<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Docente - {{ $docente->nombre_completo }}</title>
    <style>
        /* CONFIGURACIÓN PREMIUM CERTIFICADA - UNA SOLA HOJA */
        @page { margin: 0; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9.5px;
            color: #1a1a1a;
            background-color: white;
            line-height: 1.1;
            padding: 0.8cm 1.5cm;
        }

        /* MARCA DE AGUA SUTIL */
        .watermark {
            position: fixed;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            z-index: -1000;
            width: 400px;
        }

        .container { width: 100%; position: relative; }

        /* HEADER */
        .header-table { width: 100%; margin-bottom: 8px; }
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

        /* INFO CARD PREMIUM */
        .info-card {
            width: 100%;
            background-color: #003366;
            color: white;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .info-card td { padding: 2px 10px; font-size: 11px; }
        .info-label { font-weight: 900; color: #66ccff; text-transform: uppercase; font-size: 9px; }
        .info-value { font-weight: bold; font-size: 12px; }

        /* TABLA DE HORARIO */
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
            font-size: 10px;
            color: #003366;
        }

        table.schedule-table td {
            height: 52px;
            padding: 3px;
        }

        .course-block { 
            width: 100%; 
            padding: 4px;
        }
        .course-name { font-weight: 900; font-size: 9px; display: block; margin-bottom: 2px; text-transform: uppercase; }
        .aula-name { font-size: 7.5px; font-weight: bold; color: #003366; background: rgba(255,255,255,0.7); display: inline-block; padding: 1px 4px; border-radius: 3px; }

        /* RECESO */
        .break-cell {
            background-color: #eee !important;
            color: #000;
            font-weight: 900;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 12px;
            height: 25px !important;
        }

        /* RESUMEN COMPACTO */
        .summary-section {
            margin-top: 10px;
            border-top: 1.5px solid #003366;
            padding-top: 6px;
        }
        .summary-label { font-size: 9px; font-weight: 900; color: #003366; margin-bottom: 4px; display: block; text-transform: uppercase; }
        .summary-item { display: inline-block; width: 19.5%; margin-bottom: 3px; }
        .color-box { width: 10px; height: 10px; display: inline-block; vertical-align: middle; margin-right: 4px; border: 0.5px solid #333; }
        .summary-text { display: inline-block; vertical-align: middle; font-size: 8.5px; font-weight: bold; }

        /* METRICAS Y TOTALES */
        .metrics-container {
            margin-top: 10px;
            width: 100%;
        }
        .total-badge {
            float: right;
            background-color: #cc0066;
            color: white;
            padding: 6px 15px;
            font-size: 12px;
            font-weight: 900;
            border-radius: 4px;
        }
        .metrics-text {
            font-size: 10px;
            color: #444;
            font-weight: bold;
        }

        /* FOOTER */
        .footer-table {
            width: 100%;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .qr-box { width: 45px; text-align: center; }
        .qr-img { width: 40px; height: 40px; }
        .qr-label { font-size: 6px; font-weight: bold; color: #003366; text-transform: uppercase; display: block; margin-top: 2px; }
        
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
        $diasMapping = [
            '1' => 'LUNES', '2' => 'MARTES', '3' => 'MIÉRCOLES', 
            '4' => 'JUEVES', '5' => 'VIERNES', '6' => 'SÁBADO'
        ];

        // RANGO DE HORAS DINÁMICO
        $min_h = 7; $max_h = 13;
        if (count($data['horarios']) > 0) {
            $m_min = 24*60; $m_max = 0;
            foreach($data['horarios'] as $item) {
                $ts = explode(':', $item->hora_inicio); $te = explode(':', $item->hora_fin);
                $t1 = $ts[0]*60 + $ts[1]; $t2 = $te[0]*60 + $te[1];
                if ($t1 < $m_min) $m_min = $t1;
                if ($t2 > $m_max) $m_max = $t2;
            }
            $min_h = max(7, floor($m_min/60));
            $max_h = min(22, ceil($m_max/60));
            if ($max_h < 13) $max_h = 13;
        }

        $slots = [];
        $curr = $min_h * 60;
        $limit = $max_h * 60;
        
        $recesoMananaInicioStr = $ciclo ? substr($ciclo->receso_manana_inicio, 0, 5) : null;
        $recesoMananaFinStr    = $ciclo ? substr($ciclo->receso_manana_fin, 0, 5) : null;
        $recesoTardeInicioStr  = $ciclo ? substr($ciclo->receso_tarde_inicio, 0, 5) : null;
        $recesoTardeFinStr     = $ciclo ? substr($ciclo->receso_tarde_fin, 0, 5) : null;
        
        $recesoMananaMins    = $recesoMananaInicioStr ? (intval(substr($recesoMananaInicioStr, 0, 2)) * 60 + intval(substr($recesoMananaInicioStr, 3, 2))) : -1;
        $recesoMananaFinMins = $recesoMananaFinStr    ? (intval(substr($recesoMananaFinStr, 0, 2)) * 60 + intval(substr($recesoMananaFinStr, 3, 2))) : -1;
        $recesoTardeMins     = $recesoTardeInicioStr  ? (intval(substr($recesoTardeInicioStr, 0, 2)) * 60 + intval(substr($recesoTardeInicioStr, 3, 2))) : -1;
        $recesoTardeFinMins  = $recesoTardeFinStr     ? (intval(substr($recesoTardeFinStr, 0, 2)) * 60 + intval(substr($recesoTardeFinStr, 3, 2))) : -1;

        while($curr < $limit) {
            $h = floor($curr / 60);
            $m = $curr % 60;
            $dur = 60; 
            $isRec = false;

            if ($recesoMananaMins !== -1 && $curr == $recesoMananaMins) {
                $dur = $recesoMananaFinMins - $recesoMananaMins;
                $isRec = true;
            } elseif ($recesoTardeMins !== -1 && $curr == $recesoTardeMins) {
                $dur = $recesoTardeFinMins - $recesoTardeMins;
                $isRec = true;
            } else {
                if ($recesoMananaMins !== -1 && $recesoMananaMins > $curr && $recesoMananaMins < $curr + 60) {
                    $dur = $recesoMananaMins - $curr;
                } elseif ($recesoTardeMins !== -1 && $recesoTardeMins > $curr && $recesoTardeMins < $curr + 60) {
                    $dur = $recesoTardeMins - $curr;
                }
            }
            
            if ($dur <= 0) $dur = 60;
            $nxt = $curr + $dur;
            $slots[] = [
                'start' => sprintf('%02d:%02d', $h, $m),
                'end' => sprintf('%02d:%02d', floor($nxt/60), $nxt % 60),
                'ts' => $curr, 'te' => $nxt, 'es_r' => $isRec
            ];
            $curr = $nxt;
        }

        function h2m($h) { $p = explode(':', $h); return $p[0]*60 + $p[1]; }
    @endphp

    <div class="container">
        <table class="header-table">
            <tr>
                <td width="10%"><img src="{{ public_path('assets/images/logo unamad constancia.png') }}" class="logo-img"></td>
                <td class="title-box">
                    <h1>REPORTE OFICIAL DE CARGA HORARIA</h1>
                    <p>CEPRE-UNAMAD | SEDE CENTRAL</p>
                    <div class="vigencia-tag">CICLO ACADÉMICO {{ $ciclo->nombre }}</div>
                </td>
                <td width="10%" align="right"><img src="{{ public_path('assets/images/logo cepre costancia.png') }}" class="logo-img"></td>
            </tr>
        </table>

        <table class="info-card">
            <tr>
                <td><span class="info-label">Docente:</span><br><span class="info-value">{{ $docente->nombre_completo }}</span></td>
                <td><span class="info-label">DNI:</span><br><span class="info-value">{{ $docente->numero_documento }}</span></td>
                <td><span class="info-label">Celular:</span><br><span class="info-value">{{ $docente->celular ?? '---' }}</span></td>
                <td align="right"><span class="info-label">Ciclo:</span><br><span class="info-value">{{ $ciclo->nombre }}</span></td>
            </tr>
        </table>

        <table class="schedule-table">
            <thead>
                <tr>
                    <th class="time-col">HORARIO</th>
                    @foreach($diasMapping as $nombre)
                        <th>{{ $nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $cursosVistos = [];
                @endphp
                @foreach($slots as $slot)
                    @if($slot['es_r'])
                        <tr>
                            <td class="time-col">{{ $slot['start'] }} - {{ $slot['end'] }}</td>
                            <td colspan="6" class="break-cell">RECESO INSTITUCIONAL</td>
                        </tr>
                    @else
                        <tr>
                            <td class="time-col">{{ $slot['start'] }} - {{ $slot['end'] }}</td>
                            @foreach($diasMapping as $num => $labelDia)
                                @php
                                    $c = $data['horarios']->filter(function($item) use ($num, $slot) {
                                        $ts = h2m($item->hora_inicio); $te = h2m($item->hora_fin);
                                        $d = strtolower($item->dia_semana);
                                        $match = ($item->dia_semana == $num) ||
                                                 ($num == 1 && $d == 'lunes') ||
                                                 ($num == 2 && $d == 'martes') ||
                                                 ($num == 3 && ($d == 'miércoles' || $d == 'miercoles')) ||
                                                 ($num == 4 && $d == 'jueves') ||
                                                 ($num == 5 && $d == 'viernes') ||
                                                 ($num == 6 && ($d == 'sábado' || $d == 'sabado'));
                                        return $match && ($slot['ts'] < $te && $slot['te'] > $ts);
                                    })->first();
                                    
                                    $bgColor = '#ffffff';
                                    $textColor = '#000000';
                                    if ($c && $c->curso) {
                                        $bgColor = $c->curso->color ?? '#4f32c2';
                                        $textColor = getContrastYIQ($bgColor);
                                        
                                        $nombreCurso = strtoupper($c->curso->nombre);
                                        if(!isset($cursosVistos[$nombreCurso])) {
                                            $cursosVistos[$nombreCurso] = $bgColor;
                                        }
                                    }
                                @endphp
                                <td style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                    @if($c)
                                        <div class="course-block">
                                            <span class="course-name">{{ $c->curso ? strtoupper($c->curso->nombre) : '---' }}</span>
                                            <span class="aula-name">AULA: {{ $c->aula ? $c->aula->nombre : '---' }}</span>
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
            <span class="summary-label">Resumen de Carga por Curso:</span>
            <div class="summary-container">
                @foreach($data['horas_por_curso'] as $h)
                    <div class="summary-item">
                        <div class="color-box" style="background-color: {{ $cursosVistos[strtoupper($h['curso'])] ?? '#4f32c2' }};"></div>
                        <span class="summary-text">
                            <strong>{{ strtoupper($h['curso']) }}:</strong> {{ $h['horas'] }} hrs
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="metrics-container">
            <div class="total-badge">CARGA TOTAL: {{ $data['total_horas_formateado'] }}</div>
            <div class="metrics-text">
                H. Base: {{ $data['horas_base_formateado'] }} | Tot. Ciclo: {{ $data['horas_totales_ciclo_formateado'] }}
            </div>
        </div>

        <table class="footer-table">
            <tr>
                <td width="10%" align="left" class="qr-box">
                    <img src="data:image/svg+xml;base64,{{ $qrCode }}" class="qr-img">
                    <span class="qr-label">VALIDAR REPORTE</span>
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