<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Docente - {{ $docente->nombre_completo }}</title>
    <style>
        /* CONFIGURACIÓN MAESTRA: UNA SOLA PÁGINA A4 */
        @page { 
            margin: 0.25cm; 
            size: A4 portrait;
        }
        
        body { 
            font-family: Arial, sans-serif; 
            font-size: 6.2px; 
            color: #1a1a1a; 
            margin: 0; 
            padding: 0; 
            line-height: 0.95;
        }

        .main-container { width: 100%; border: 0.5pt solid transparent; }

        /* HEADER ULTRA-COMPACTO */
        .header-modern {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            border-bottom: 1.5pt solid #4f32c2;
        }
        
        .logo-box { width: 35px; padding: 2px; }
        .logo-img { width: 30px; height: auto; }
        
        .title-box { text-align: center; vertical-align: middle; }
        .title-box h1 { 
            margin: 0; 
            font-size: 9px; 
            color: #4f32c2; 
            text-transform: uppercase; 
            font-weight: bold;
        }
        .title-box p { 
            margin: 1px 0; 
            font-size: 7px; 
            font-weight: bold; 
            color: #333;
        }
        .univ-text { font-size: 5.5px; color: #666; font-style: italic; }

        /* INFO CARD COMPACTA */
        .info-card {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            border: 0.5pt solid #ccc;
        }
        
        .info-card td { padding: 2px 4px; border: 0.3pt solid #eee; font-size: 6.5px; }
        .label-style { 
            background-color: #f8f9ff; 
            width: 60px; 
            font-weight: bold; 
            color: #4f32c2; 
            text-transform: uppercase;
            font-size: 5px;
        }
        .value-style { font-weight: bold; color: #000; }

        /* TABLA DE HORARIO: ALTURA BLOQUEADA */
        .schedule-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; 
            border: 0.8pt solid #333;
        }
        
        .col-h { width: 14%; }
        .col-d { width: 14.33%; }

        .schedule-table th { 
            background: #4f32c2;
            color: #ffffff; 
            font-size: 6.5px; 
            font-weight: bold;
            height: 12px; 
            border: 0.5pt solid #000;
            text-align: center;
            text-transform: uppercase;
        }

        .schedule-table td { 
            border: 0.3pt solid #bbb; 
            text-align: center; 
            vertical-align: middle; 
            padding: 0; 
            height: 15px; /* Altura mínima infalible */
            overflow: hidden;
        }

        .time-cell {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 5.8px;
            color: #4f32c2;
            border-right: 1.2pt solid #4f32c2;
        }

        /* RECESO */
        .row-receso {
            background-color: #7ade77;
            color: #004d00;
            font-weight: bold;
            font-size: 7px;
            height: 13px !important;
        }
        
        .row-receso td {
            height: 13px !important;
            border: 0.8pt solid #333;
            background-color: #7ade77;
        }

        /* CURSOS */
        .course-box {
            margin: 0;
            padding: 1px;
            border-left: 2pt solid #4f32c2;
        }
        
        .color-1 { background-color: #f0f7ff; border-left-color: #007bff; }
        .color-2 { background-color: #fff4f4; border-left-color: #dc3545; }
        .color-3 { background-color: #f0fff4; border-left-color: #28a745; }
        .color-1 { background-color: #fffcf0; border-left-color: #ffc107; }
        .color-5 { background-color: #fcf0ff; border-left-color: #af40ff; }
        .color-6 { background-color: #f0ffff; border-left-color: #17a2b8; }

        .name-c { font-weight: bold; font-size: 6px; display: block; color: #000; line-height: 0.9; }
        .name-a { font-size: 4.5px; color: #4f32c2; font-weight: bold; display: block; }

        /* FOOTER PROFESIONAL COMPACTO */
        .footer-modern { 
            width: 100%; 
            margin-top: 4px;
            padding-top: 2px;
            border-top: 1pt solid #4f32c2;
        }
        
        .badge-total { 
            background-color: #4f32c2;
            color: white; 
            padding: 2px 6px; 
            font-weight: bold; 
            float: right; 
            font-size: 8px;
        }

        .metrics-box { font-size: 5.5px; color: #444; }
        .note-alert { color: #d32f2f; font-weight: bold; font-size: 5.5px; margin-top: 2px; }
        .trademark { text-align: center; font-size: 5px; color: #aaa; margin-top: 2px; font-style: italic; }
    </style>
</head>
<body>
    <div class="main-container">
        @php
            $diasMapping = [
                '1' => 'LUNES', '2' => 'MARTES', '3' => 'MIÉRCOLES', 
                '4' => 'JUEVES', '5' => 'VIERNES', '6' => 'SÁBADO'
            ];

            // RANGO DE HORAS INTELIGENTE
            $min_h = 7; $max_h = 21;
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
            }

            // GENERACIÓN DE SLOTS
            $slots = [];
            $curr = $min_h * 60;
            $limit = $max_h * 60;

            while($curr < $limit) {
                $h = floor($curr / 60);
                $m = $curr % 60;
                $dur = 60; 
                $isRec = false;

                if ($curr == 10*60 || $curr == 18*60) {
                    $dur = 30;
                    $isRec = true;
                }
                
                $nxt = $curr + $dur;
                $slots[] = [
                    'start' => sprintf('%02d:%02d', $h, $m),
                    'end' => sprintf('%02d:%02d', floor($nxt/60), $nxt % 60),
                    'ts' => $curr, 'te' => $nxt, 'es_r' => $isRec
                ];
                $curr = $nxt;
            }

            function h2m($h) { $p = explode(':', $h); return $p[0]*60 + $p[1]; }
            function color($n) { return 'color-' . ((crc32($n) % 6) + 1); }
        @endphp

        <table class="header-modern">
            <tr>
                <td class="logo-box"><img src="{{ public_path('assets/images/logo unamad constancia.png') }}" class="logo-img"></td>
                <td class="title-box">
                    <h1>REPORTE OFICIAL DE CARGA HORARIA</h1>
                    <p>CEPRE-UNAMAD | {{ $ciclo->nombre }}</p>
                    <div class="univ-text">UNAMAD - Madre de Dios</div>
                </td>
                <td class="logo-box" align="right"><img src="{{ public_path('assets/images/logo cepre costancia.png') }}" class="logo-img"></td>
            </tr>
        </table>

        <table class="info-card">
            <tr>
                <td class="label-style">Docente</td>
                <td class="value-style" style="color: #4f32c2;">{{ $docente->nombre_completo }}</td>
                <td class="label-style">DNI</td>
                <td class="value-style">{{ $docente->numero_documento }}</td>
            </tr>
            <tr>
                <td class="label-style">Celular</td>
                <td class="value-style">{{ $docente->celular ?? $docente->telefono ?? '---' }}</td>
                <td class="label-style">Ciclo</td>
                <td class="value-style">{{ $ciclo->nombre }}</td>
            </tr>
        </table>

        <table class="schedule-table">
            <thead>
                <tr>
                    <th class="col-h">HORARIO</th>
                    @foreach($diasMapping as $nombre)
                        <th class="col-d">{{ $nombre }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($slots as $slot)
                    @if($slot['es_r'])
                        <tr class="row-receso">
                            <td class="time-cell" align="center">{{ $slot['start'] }} - {{ $slot['end'] }}</td>
                            <td colspan="6" align="center">RECESO 30 MINUTOS</td>
                        </tr>
                    @else
                        <tr>
                            <td class="time-cell">{{ $slot['start'] }} - {{ $slot['end'] }}</td>
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
                                    $isStart = $c && (h2m($c->hora_inicio) >= $slot['ts'] && h2m($c->hora_inicio) < $slot['te']);
                                @endphp
                                <td>
                                    @if($c)
                                        <div class="course-box {{ color($c->curso ? $c->curso->nombre : '') }}">
                                            <span class="name-c">{{ $c->curso ? $c->curso->nombre : '---' }}</span>
                                            @if($isStart)<span class="name-a">AULA: {{ $c->aula ? $c->aula->nombre : '---' }}</span>@endif
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div class="footer-modern">
            <div class="badge-total">CARGA: {{ $data['total_horas_formateado'] }}</div>
            <div class="metrics-box">
                <strong>H. Base:</strong> {{ $data['horas_base_formateado'] }} | <strong>Tot. Ciclo:</strong> {{ $data['horas_totales_ciclo_formateado'] }}
                <div class="note-alert">* IMPORTANTE: Sábados son ROTATIVOS según programación mensual.</div>
            </div>
            <div class="trademark">Sistema de Asistencia CEPRE-UNAMAD | © {{ date('Y') }}</div>
        </div>
    </div>
</body>
</html>