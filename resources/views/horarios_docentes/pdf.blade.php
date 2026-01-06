<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario - {{ $aula->nombre }}</title>
    <style>
        @page {
            margin: 10mm 15mm;
            size: landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            line-height: 1.3;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 4px solid #1a1a1a;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header h2 {
            font-size: 12px;
            font-weight: normal;
            margin-bottom: 2px;
        }

        .header h3 {
            font-size: 11px;
            font-weight: bold;
            margin-top: 3px;
        }

        .info-bar {
            background: #1a1a1a;
            color: white;
            padding: 6px 12px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 1.5px solid #1a1a1a;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        thead th {
            background: #1a1a1a;
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 4px;
        }

        th.hora-col {
            background: #e8e8e8;
            color: #1a1a1a;
            width: 70px;
            font-weight: bold;
            font-size: 8px;
        }

        td {
            background: white;
            min-height: 50px;
        }

        .receso-cell {
            background: #10b981 !important;
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .curso-cell {
            padding: 5px 3px;
            font-size: 8px;
            line-height: 1.2;
        }

        .curso-nombre {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 9px;
            color: #000;
        }

        .docente-nombre {
            font-size: 7px;
            color: #333;
            font-style: italic;
        }

        .footer {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 2px solid #1a1a1a;
            font-size: 7px;
            text-align: center;
            color: #666;
        }

        .footer strong {
            color: #1a1a1a;
        }

        /* Función para aclarar colores */
        <?php
        function lightenColor($hex, $percent) {
            $hex = str_replace('#', '', $hex);
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
    <div class="header">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>"Centro Pre Universitario"</h2>
        <h3>{{ $ciclo->nombre }}</h3>
    </div>

    <div class="info-bar">
        GRUPO: {{ $aula->nombre }} &nbsp;|&nbsp; TURNO: {{ $turno }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="hora-col">HORA</th>
                @foreach ($dias as $dia)
                    <th>{{ strtoupper($dia) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($grilla as $fila)
                <tr>
                    <th class="hora-col">{{ $fila['hora'] }}</th>
                    @foreach ($dias as $dia)
                        @php
                            $horario = $fila[$dia] ?? null;
                            $esReceso = $horario && (stripos($horario->curso->nombre, 'receso') !== false || $horario->curso->nombre === 'RECESO');
                            
                            // Obtener color del curso y aclararlo
                            $bgColor = '#ffffff';
                            if ($horario && !$esReceso && $horario->curso->color) {
                                $bgColor = lightenColor($horario->curso->color, 75);
                            }
                        @endphp
                        
                        @if ($horario)
                            @if ($esReceso)
                                <td class="receso-cell">RECESO</td>
                            @else
                                <td class="curso-cell" style="background-color: {{ $bgColor }}; border-left: 4px solid {{ $horario->curso->color }};">
                                    <div class="curso-nombre">{{ strtoupper($horario->curso->nombre) }}</div>
                                    <div class="docente-nombre">{{ $horario->docente->nombre_completo ?? 'Sin docente' }}</div>
                                </td>
                            @endif
                        @else
                            <td></td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; <strong>Centro Pre Universitario - UNAMAD</strong>
    </div>
</body>
</html>
