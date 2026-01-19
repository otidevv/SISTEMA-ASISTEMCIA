<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario - {{ $aula->nombre }}</title>
    <style>
        /* CONFIGURACIÓN LANDSCAPE - PÁGINA ÚNICA */
        @page { margin: 0.7cm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 7px;
            color: #1a1a1a;
            line-height: 1.1;
        }

        /* HEADER MODERNO CON LOGOS */
        .header-modern {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            border-bottom: 2.5pt solid #4f32c2;
            background-color: #ffffff;
        }
        
        .logo-box { width: 45px; padding: 2px; }
        .logo-img { width: 40px; height: auto; }
        
        .title-box { text-align: center; vertical-align: middle; }
        .title-box h1 {
            margin: 0;
            font-size: 13px;
            color: #4f32c2;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 1.5px;
        }
        .title-box p {
            margin: 1px 0;
            font-size: 9px;
            font-weight: 700;
            color: #333;
        }
        .sub-text { font-size: 6.5px; color: #666; font-style: italic; font-weight: 600; }

        /* INFO BAR MODERNA */
        .info-bar {
            background-color: #4f32c2;
            color: white;
            padding: 5px 12px;
            margin-bottom: 4px;
            text-align: center;
            font-weight: 900;
            font-size: 9px;
            letter-spacing: 2px;
        }

        /* TABLA DE HORARIO */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            border: 1.2pt solid #4f32c2;
        }

        th, td {
            border: 0.5pt solid #dee2e6;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
        }

        thead th {
            background-color: #4f32c2;
            color: white;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 7px 3px;
            height: 20px;
            border: 0.5pt solid #3c26a0;
        }

        th.hora-col {
            background-color: #f8f9ff;
            color: #4f32c2;
            width: 85px;
            font-weight: bold;
            font-size: 6.5px;
            border-right: 1.5pt solid #4f32c2;
        }

        td {
            background: white;
            height: 32px;
            overflow: hidden;
        }

        /* RECESO HORIZONTAL COMPLETO */
        .receso-horizontal {
            background-color: #7ade77 !important;
            color: #004d00;
            font-weight: 900;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 1.2pt solid #4f32c2 !important;
            padding: 6px !important;
        }

        /* CELDAS DE CURSO MODERNAS */
        .curso-cell {
            padding: 4px 3px;
            font-size: 6.5px;
            line-height: 1.1;
        }

        .curso-nombre {
            font-weight: 800;
            margin-bottom: 2px;
            font-size: 7.5px;
            color: #000;
            line-height: 1;
        }

        .docente-nombre {
            font-size: 5.8px;
            color: #555;
            font-weight: 600;
            margin-top: 1px;
        }

        /* FOOTER PROFESIONAL */
        .footer-modern {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1.5pt solid #4f32c2;
            font-size: 6px;
            text-align: center;
            color: #666;
        }

        .footer-modern strong { color: #4f32c2; }

        /* Función PHP para aclarar colores */
        <?php
        function lightenColor($hex, $percent) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) != 6) return '#f0f7ff';
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $r = min(255, $r + (255 - $r) * $percent / 100);
            $g = min(255, $g + (255 - $g) * $percent / 100);
            $b = min(255, $b + (255 - $b) * $percent / 100);
            return sprintf("#%02x%02x%02x", $r, $g, $b);
        }
        ?>
    </style>
</head>
<body>
    <!-- HEADER PREMIUM -->
    <table class="header-modern">
        <tr>
            <td class="logo-box">
                <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" class="logo-img">
            </td>
            <td class="title-box">
                <h1>HORARIO ACADÉMICO OFICIAL</h1>
                <p>CEPRE-UNAMAD | CICLO {{ $ciclo->nombre }}</p>
                <div class="sub-text">Universidad Nacional Amazónica de Madre de Dios</div>
            </td>
            <td class="logo-box" align="right">
                <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" class="logo-img">
            </td>
        </tr>
    </table>

    <!-- INFO BAR -->
    <div class="info-bar">
        AULA: {{ $aula->nombre }} &nbsp;|&nbsp; TURNO: {{ $turno }}
    </div>

    <!-- TABLA DE HORARIO -->
    <table>
        <thead>
            <tr>
                <th class="hora-col">HORARIO</th>
                @foreach ($dias as $dia)
                    <th>{{ strtoupper($dia) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($grilla as $fila)
                @php
                    // Detectar si este slot es un receso (10:00 - 10:30 o 18:00 - 18:30)
                    $esReceso = ($fila['hora'] === '10:00 - 10:30' || $fila['hora'] === '18:00 - 18:30');
                @endphp
                
                @if($esReceso)
                    <!-- RECESO HORIZONTAL -->
                    <tr>
                        <th class="hora-col">{{ $fila['hora'] }}</th>
                        <td colspan="6" class="receso-horizontal">RECESO ACADÉMICO - 30 MINUTOS</td>
                    </tr>
                @else
                    <!-- FILA NORMAL DE CLASES -->
                    <tr>
                        <th class="hora-col">{{ $fila['hora'] }}</th>
                        @foreach ($dias as $dia)
                            @php
                                $horario = $fila[$dia] ?? null;
                                $esCursoReceso = $horario && (stripos($horario->curso->nombre, 'receso') !== false || $horario->curso->nombre === 'RECESO');
                                
                                // Color de fondo para cursos
                                $bgColor = '#ffffff';
                                $borderColor = '#4f32c2';
                                if ($horario && !$esCursoReceso && $horario->curso->color) {
                                    $bgColor = lightenColor($horario->curso->color, 85);
                                    $borderColor = $horario->curso->color;
                                }
                            @endphp
                            
                            @if ($horario && !$esCursoReceso)
                                <td class="curso-cell" style="background-color: {{ $bgColor }}; border-left: 3pt solid {{ $borderColor }};">
                                    <div class="curso-nombre">{{ strtoupper($horario->curso->nombre) }}</div>
                                    <div class="docente-nombre">{{ $horario->docente->nombre_completo ?? 'Sin docente' }}</div>
                                </td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer-modern">
        <strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; <strong>Sistema CEPRE-UNAMAD</strong> &nbsp;|&nbsp; © {{ date('Y') }} UNAMAD
    </div>
</body>
</html>
