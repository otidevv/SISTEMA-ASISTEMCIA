<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Horario Oficial - {{ $aula->nombre }}</title>
    <style>
        /* CONFIGURACIÓN CON MARGEN DE 1.5cm */
        @page {
            margin: 0;
            size: A4 landscape;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            background-color: white;
            line-height: 1.2;
            padding: 1.5cm;
            /* Margen uniforme de 1.5cm en todos los lados */
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .logo-img {
            width: 65px;
            height: auto;
        }

        .title-box {
            text-align: center;
            vertical-align: middle;
        }

        .title-box h1 {
            font-size: 21px;
            color: #003366;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .title-box p {
            font-size: 14px;
            color: #cc0066;
            font-weight: bold;
        }

        .info-header {
            background-color: #003366;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        /* TABLA DE HORARIO */
        table.schedule-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1.5px solid #333;
        }

        table.schedule-table th,
        table.schedule-table td {
            border: 1.2px solid #333;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.schedule-table th {
            background-color: #f2f2f2;
            color: #003366;
            padding: 10px 2px;
            font-size: 11px;
            font-weight: bold;
        }

        .time-col {
            background-color: #f9f9f9;
            width: 95px;
            font-weight: bold;
            font-size: 9.5px;
        }

        table.schedule-table td {
            height: 55px;
            /* Altura ideal para llenar la hoja con margen de 1.5cm */
            padding: 5px;
        }

        .course-item {
            width: 100%;
        }

        .course-name {
            font-weight: 800;
            font-size: 10px;
            display: block;
            margin-bottom: 2px;
        }

        .teacher-name {
            font-size: 8.5px;
            font-weight: 600;
        }

        .break-cell {
            background-color: #eee !important;
            color: #000;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 10px;
            height: 35px !important;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 9.5px;
            font-weight: bold;
            color: #666;
        }

        <?php
function getContrastYIQ($hexcolor)
{
    $hexcolor = str_replace('#', '', $hexcolor);
    if (strlen($hexcolor) != 6)
        return 'black';
    $r = hexdec(substr($hexcolor, 0, 2));
    $g = hexdec(substr($hexcolor, 2, 2));
    $b = hexdec(substr($hexcolor, 4, 2));
    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    return ($yiq >= 128) ? 'black' : 'white';
}
        ?>
    </style>
</head>

<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td width="10%"><img src="{{ public_path('assets/images/logo unamad constancia.png') }}"
                        class="logo-img"></td>
                <td class="title-box">
                    <h1>HORARIO ACADÉMICO OFICIAL</h1>
                    <p>CEPRE-UNAMAD | CICLO {{ $ciclo->nombre }}</p>
                </td>
                <td width="10%" align="right"><img src="{{ public_path('assets/images/logo cepre costancia.png') }}"
                        class="logo-img"></td>
            </tr>
        </table>

        <div class="info-header">
            AULA: {{ $aula->nombre }} &nbsp;&nbsp;&nbsp;&nbsp; TURNO: {{ $turno }}
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
                        $recesoTarde = $ciclo->receso_tarde_inicio ? substr($ciclo->receso_tarde_inicio, 0, 5) : 'NONE';
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
                                                <div style="font-size: 8.5px; font-weight: bold;">G: {{ $horario->grupo }}</div>
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
            Sistema Portal CEPRE-UNAMAD | {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>

</html>