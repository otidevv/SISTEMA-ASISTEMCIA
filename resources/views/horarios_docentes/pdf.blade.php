<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Oficial - {{ $aula->nombre }}</title>
    <style>
        /* CONFIGURACIÓN CRÍTICA PARA UNA SOLA HOJA - FIX MULTIPAGE */
        @page { margin: 0; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            background-color: white;
            line-height: 1.1;
            padding: 1.5cm;
        }

        .container { width: 100%; margin: 0 auto; }

        /* HEADER */
        .header-table { width: 100%; margin-bottom: 12px; }
        .logo-img { width: 60px; height: auto; }
        .title-box { text-align: center; }
        .title-box h1 { font-size: 20px; color: #003366; font-weight: 900; text-transform: uppercase; }
        .title-box p { font-size: 13px; color: #cc0066; font-weight: bold; }

        .info-header {
            background-color: #003366;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 12px;
        }

        /* TABLA ESTABLE */
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
            word-wrap: break-word;
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
            width: 75px; /* Reducido para ganar espacio horizontal */
            font-weight: 900;
            font-size: 11px; /* Letra más grande para las horas */
            color: #003366;
        }

        table.schedule-table td {
            height: 52px; /* Altura segura */
            padding: 4px;
        }

        /* BLOQUES DE CURSO - SIN HEIGHT 100% PARA EVITAR BUGS */
        .course-item { 
            width: 100%; 
            display: block;
        }

        .course-name { 
            font-weight: 900; 
            font-size: 10px; 
            display: block; 
            margin-bottom: 2px; 
            text-transform: uppercase;
        }

        .teacher-name { 
            font-size: 8px; 
            font-weight: bold;
        }

        .group-tag {
            font-size: 7.5px;
            font-weight: 900;
            margin-top: 2px;
            display: inline-block;
        }

        /* RECESO */
        .break-cell {
            background-color: #f1f5f9 !important;
            color: #003366;
            font-weight: 900;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 10px;
            height: 30px !important;
        }

        .footer {
            margin-top: 10px;
            text-align: right;
            font-size: 9px;
            font-weight: bold;
            color: #444;
        }

        <?php
        function getContrastYIQ($hexcolor){
            // FORZAR BLANCO EN TODO LO QUE TENGA COLOR PARA ESTILO PREMIUM
            if (!$hexcolor || $hexcolor == '#ffffff' || $hexcolor == 'transparent') return '#000000';
            return '#ffffff'; 
        }
        ?>
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td width="10%"><img src="{{ public_path('assets/images/logo unamad constancia.png') }}" class="logo-img"></td>
                <td class="title-box">
                    <h1>HORARIO ACADÉMICO OFICIAL</h1>
                    <p>CEPRE-UNAMAD | CICLO {{ $ciclo->nombre }}</p>
                </td>
                <td width="10%" align="right"><img src="{{ public_path('assets/images/logo cepre costancia.png') }}" class="logo-img"></td>
            </tr>
        </table>

        <div class="info-header">
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
                @foreach ($grilla as $fila)
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
                            <td class="time-col">{{ $fila['hora'] }}</td>
                            @foreach ($dias as $dia)
                                @php
                                    $horario = $fila[$dia] ?? null;
                                    $esCursoReceso = $horario && (stripos($horario->curso->nombre, 'receso') !== false || $horario->curso->nombre === 'RECESO');
                                    $bgColor = '#ffffff';
                                    $textColor = '#000000';
                                    if ($horario && !$esCursoReceso && $horario->curso->color) {
                                        $bgColor = $horario->curso->color;
                                        $textColor = getContrastYIQ($bgColor);
                                    }
                                @endphp
                                
                                @if ($horario && !$esCursoReceso)
                                    <td style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                                        <div class="course-item">
                                            <span class="course-name">{{ strtoupper($horario->curso->nombre) }}</span>
                                            <span class="teacher-name">{{ $horario->docente->nombre_completo ?? 'Sin docente' }}</span>
                                            @if($horario->grupo)
                                                <div class="group-tag">G: {{ $horario->grupo }}</div>
                                            @endif
                                        </div>
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

        <div class="footer">
            Generado por Sistema CEPRE-UNAMAD | {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>