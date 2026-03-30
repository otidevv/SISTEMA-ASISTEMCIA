<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario - {{ $aula->nombre }}</title>
    <style>
        /* CONFIGURACIÓN CORPORATIVA - PÁGINA ÚNICA A4 LANDSCAPE */
        @page { margin: 1cm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8px; 
            color: #334155; /* Slate 700 */
            line-height: 1.3;
            -webkit-font-smoothing: antialiased;
            background-color: white;
        }

        /* HEADER CLEAN MODERNO */
        .header-modern {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .logo-box { width: 45px; padding: 0; }
        .logo-img { width: 50px; height: auto; }
        
        .title-box { text-align: center; vertical-align: middle; }
        .title-box h1 {
            margin: 0 0 2px 0;
            font-size: 15px;
            color: #0f172a; /* Puro oscuro, muy serio */
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .title-box p {
            margin: 2px 0;
            font-size: 10.5px;
            font-weight: 700;
            color: #475569;
        }
        .sub-text { font-size: 7.5px; color: #64748b; font-weight: normal; }

        /* INFO BAR ELEGANTE */
        .info-bar {
            background-color: #f8fafc; /* Gris extra claro */
            color: #1e293b;
            border-left: 4px solid #3b82f6; /* Acento azul institucional */
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 14px;
            border-radius: 2px;
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 9.5px;
            letter-spacing: 1px;
        }

        /* TABLA DE HORARIO CLEAN */
        table.schedule {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            border: 1px solid #cbd5e1;
        }

        table.schedule th, table.schedule td {
            border: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
        }

        table.schedule thead th {
            background-color: #f1f5f9; /* Slate 100 */
            color: #475569;
            font-weight: 800;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 4px;
            border-bottom: 2px solid #cbd5e1;
        }

        th.hora-col {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 700;
            width: 85px;
        }

        table.schedule td {
            height: 38px;
            overflow: hidden;
            padding: 2px;
        }

        /* RECESO CORPORATIVO (Sutil, espaciado) */
        .receso-horizontal {
            background-color: #f8fafc !important; 
            color: #64748b !important;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 6px; /* Separación amplia para efecto elegante */
            padding: 8px !important;
            border-top: 1.5px dashed #cbd5e1 !important;
            border-bottom: 1.5px dashed #cbd5e1 !important;
        }

        /* CELDAS DE CURSO REFINADAS */
        .curso-cell {
            padding: 5px;
            font-size: 7px;
        }

        .curso-nombre {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .docente-nombre {
            color: #475569;
            font-weight: 500;
            font-style: italic;
        }

        /* FOOTER */
        .footer-modern {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 7px;
            text-align: center;
            color: #94a3b8;
        }
        
        .footer-modern strong { color: #64748b; }

        /* Aclarado automático de color elegante */
        <?php
        function lightenColor($hex, $percent) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) != 6) return '#ffffff'; // Fallback a blanco limpio
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $r = min(255, $r + (255 - $r) * ($percent + 5) / 100);
            $g = min(255, $g + (255 - $g) * ($percent + 5) / 100);
            $b = min(255, $b + (255 - $b) * ($percent + 5) / 100);
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

    <!-- TABLA DE HORARIO PREMIUM -->
    <table>
        <thead>
            <tr>
                <th class="hora-col">BLOQUE HORARIO</th>
                @foreach ($dias as $dia)
                    <th>{{ strtoupper($dia) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($grilla as $fila)
                @php
                    // Detección CIBERNÉTICA de Recesos (Enlace real con Base de Datos)
                    $horaFilaParts = explode(' - ', $fila['hora']);
                    $inicioFilaStr = trim($horaFilaParts[0]); 
                    
                    $recesoManana = $ciclo->receso_manana_inicio ? substr($ciclo->receso_manana_inicio, 0, 5) : 'NONE';
                    $recesoTarde  = $ciclo->receso_tarde_inicio ? substr($ciclo->receso_tarde_inicio, 0, 5) : 'NONE';
                    
                    $esReceso = ($inicioFilaStr === $recesoManana || $inicioFilaStr === $recesoTarde);
                @endphp
                
                @if($esReceso)
                    <!-- RECESO HORIZONTAL CORPORATIVO -->
                    <tr>
                        <th class="hora-col">{{ $fila['hora'] }}</th>
                        <td colspan="6" class="receso-horizontal">RECESO DE DESCANSO INSTITUCIONAL</td>
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
