<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia Individual - {{ $estudiante->numero_documento }}</title>
    <style>
        /* CONFIGURACIÓN PARA DOMPDF Y A4 */
        @page {
            size: A4;
            margin: 1.2cm 1.4cm 1.8cm 1.4cm;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5px;
            line-height: 1.4;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* MARCA DE AGUA INSTITUCIONAL */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.035;
            z-index: -1000;
            width: 460px;
            text-align: center;
        }

        .container {
            width: 100%;
            position: relative;
        }

        /* CABECERA INSTITUCIONAL PREMIUM */
        .hdr-outer {
            border: 1px solid #d1dde4;
            border-radius: 10px;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(43,90,111,0.15);
        }

        .hdr-top {
            background: #2b5a6f; /* Color oficial */
            background: linear-gradient(135deg, #2b5a6f 0%, #1a3d52 60%, #0d2838 100%);
            padding: 12px 18px;
            color: white;
        }

        .hdr-top table {
            width: 100%;
            border-collapse: collapse;
        }

        .hdr-logo {
            width: 60px;
            vertical-align: middle;
        }

        .hdr-logo img {
            width: 50px;
            height: auto;
        }

        .hdr-title-cell {
            text-align: center;
            vertical-align: middle;
        }

        .hdr-title-cell h1 {
            margin: 0 0 3px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .hdr-title-cell .sub {
            font-size: 9px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .hdr-stripe {
            height: 4px;
            background: linear-gradient(to right, #cc0000 0% 33%, #00aeef 33% 66%, #8cc63f 66% 100%);
        }

        .hdr-sub-row {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            padding: 5px;
            font-size: 8.5px;
            font-weight: bold;
            color: #2b5a6f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* TÍTULOS DE SECCIÓN */
        .sec-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2b5a6f;
            border-bottom: 2px solid #00aeef;
            padding-bottom: 4px;
            margin: 18px 0 10px 0;
        }

        /* TABLA DE DETALLE MENSUAL REDISEÑADA */
        .month-card {
            border: 1px solid #d1dde4;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            page-break-inside: avoid;
        }

        .month-hdr-table {
            width: 100%;
            background: #2b5a6f;
            background: linear-gradient(90deg, #2b5a6f 0%, #1a3d52 100%);
            color: white;
            border-collapse: collapse;
        }

        .month-hdr-table td {
            padding: 6px 12px;
            font-weight: 800;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background-color: #f8fafc;
            color: #2b5a6f;
            padding: 7px 10px;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 800;
            border-bottom: 2px solid #cbd5e1;
            border-top: none;
            border-left: none;
            border-right: none;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8.5px;
            color: #334155;
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Sombreado de fila alterna */
        .data-table tr:nth-child(even) td {
            background-color: #fcfdfe;
        }

        .data-table tr.falta-row td {
            background-color: #fffafb;
        }

        /* BADGES DE ESTADO - DISEÑO PREMIUM */
        .badge-status {
            display: inline-block;
            padding: 3px 8px;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
        }

        .badge-status.puntual {
            background-color: #eef7e2;
            color: #5a8a1f;
            border: 1px solid #c3dfa1;
        }

        .badge-status.tarde {
            background-color: #fff8e1;
            color: #c07800;
            border: 1px solid #ffe082;
        }

        .badge-status.falta {
            background-color: #fce4f0;
            color: #cc0000;
            border: 1px solid #f5a3d3;
        }

        /* Textos de horas */
        .time-txt {
            font-family: monospace;
            font-size: 9px;
            font-weight: bold;
        }
        .time-txt.danger {
            color: #cc0000;
        }
        .time-txt.warning {
            color: #c07800;
        }

        /* SECCIÓN DE FIRMAS */
        .sig-container {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .sig-box {
            width: 180px;
            text-align: center;
            border-top: 2px solid #2b5a6f;
            padding-top: 5px;
            margin: 0 auto;
        }

        /* FOOTER */
        .pdf-footer {
            margin-top: 20px;
            padding-top: 6px;
            border-top: 2px solid #00aeef;
            text-align: center;
            font-size: 7.5px;
            color: #475569;
            line-height: 1.4;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <!-- MARCA DE AGUA -->
    <div class="watermark">
        @php
            $logoWPath = public_path('assets/images/logo unamad constancia.png');
            $logoWData = file_exists($logoWPath) 
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoWPath)) 
                : null;
        @endphp
        @if($logoWData)
            <img src="{{ $logoWData }}" style="width: 100%;">
        @endif
    </div>

    <div class="container">
        <!-- CABECERA INSTITUCIONAL PREMIUM -->
        <div class="hdr-outer">
            <div class="hdr-top">
                <table>
                    <tr>
                        <td class="hdr-logo">
                            @php
                                $logoUnamad = public_path('assets/images/logo unamad constancia.png');
                                $logoUnamadData = file_exists($logoUnamad) 
                                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoUnamad)) 
                                    : null;
                            @endphp
                            @if($logoUnamadData)
                                <img src="{{ $logoUnamadData }}" alt="Logo UNAMAD">
                            @endif
                        </td>
                        <td class="hdr-title-cell">
                            <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
                            <div class="sub">Centro Pre-Universitario (CEPRE) — Reporte de Asistencia</div>
                        </td>
                        <td class="hdr-logo" style="text-align: right;">
                            @php
                                $logoCepre = public_path('assets/images/logo cepre costancia.png');
                                $logoCepreData = file_exists($logoCepre) 
                                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoCepre)) 
                                    : null;
                            @endphp
                            @if($logoCepreData)
                                <img src="{{ $logoCepreData }}" alt="Logo CEPRE">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="hdr-stripe"></div>
            <div class="hdr-sub-row">
                Ciclo Académico: {{ $ciclo->nombre }} &nbsp;|&nbsp; Documento Oficial Individual
            </div>
        </div>

        <!-- SECCIÓN DE INFORMACIÓN DEL ESTUDIANTE -->
        @include('reportes.partials.info-estudiante')

        @if (isset($info_asistencia) && !empty($info_asistencia))

            <div class="sec-title">Resumen de Seguimiento Académico</div>

            <!-- PRIMER EXAMEN -->
            @if (isset($info_asistencia['primer_examen']))
                @include('reportes.partials.card-examen', [
                    'info' => $info_asistencia['primer_examen'],
                    'titulo' => 'PRIMER EXAMEN',
                    'fecha' => $ciclo->fecha_primer_examen
                ])
            @endif

            <!-- SEGUNDO EXAMEN -->
            @if (isset($info_asistencia['segundo_examen']) && $info_asistencia['segundo_examen']['condicion'] != 'Pendiente')
                @include('reportes.partials.card-examen', [
                    'info' => $info_asistencia['segundo_examen'],
                    'titulo' => 'SEGUNDO EXAMEN',
                    'fecha' => $ciclo->fecha_segundo_examen
                ])
            @endif

            <!-- TERCER EXAMEN -->
            @if (isset($info_asistencia['tercer_examen']) && $info_asistencia['tercer_examen']['condicion'] != 'Pendiente')
                @include('reportes.partials.card-examen', [
                    'info' => $info_asistencia['tercer_examen'],
                    'titulo' => 'TERCER EXAMEN',
                    'fecha' => $ciclo->fecha_tercer_examen
                ])
            @endif

            <!-- DETALLE DE ASISTENCIAS POR MES -->
            @if (isset($detalle_asistencias) && count($detalle_asistencias) > 0)
                <div class="page-break"></div>

                <div class="sec-title">Detalle Mensual de Asistencias</div>

                @foreach ($detalle_asistencias as $mesKey => $mes)
                    <div class="month-card">
                        <table class="month-hdr-table">
                            <tr>
                                <td style="text-align: left;">{{ strtoupper($mes['mes']) }} {{ $mes['anio'] }}</td>
                                <td style="text-align: right; font-size: 8px; font-family: monospace; opacity: 0.9;">
                                    ASISTENCIAS: {{ $mes['dias_asistidos'] }} &nbsp;|&nbsp; FALTAS: {{ $mes['dias_falta'] }}
                                </td>
                            </tr>
                        </table>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 15%; text-align: center;">Fecha</th>
                                    <th style="width: 15%; text-align: center;">Día</th>
                                    <th style="width: 20%; text-align: center;">Hora Entrada</th>
                                    <th style="width: 20%; text-align: center;">Hora Salida</th>
                                    <th style="width: 30%; text-align: center;">Estado del Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mes['registros'] as $registro)
                                    <tr class="{{ !$registro['asistio'] ? 'falta-row' : '' }}">
                                        <td style="text-align: center; font-family: monospace; font-weight: bold; color: #475569;">
                                            {{ $registro['fecha'] }}
                                        </td>
                                        <td style="text-align: center; font-weight: 600;">
                                            {{ $registro['dia_semana'] }}
                                        </td>
                                        
                                        {{-- Entrada --}}
                                        <td style="text-align: center;">
                                            @if($registro['hora_entrada'] == 'Sin registro' || $registro['hora_entrada'] == 'FALTA')
                                                <span class="time-txt danger">&mdash;</span>
                                            @else
                                                <span class="time-txt {{ $registro['es_tarde'] ? 'warning' : '' }}">
                                                    {{ $registro['hora_entrada'] }}
                                                </span>
                                                @if($registro['es_tarde'])
                                                    <span style="font-size: 6.5px; display: block; color: #c07800; font-weight: bold; margin-top: 1px;">(TARDE)</span>
                                                @endif
                                            @endif
                                        </td>

                                        {{-- Salida --}}
                                        <td style="text-align: center;">
                                            @if($registro['hora_salida'] == 'Sin registro' || $registro['hora_salida'] == 'FALTA')
                                                <span class="time-txt danger">&mdash;</span>
                                            @else
                                                <span class="time-txt">
                                                    {{ $registro['hora_salida'] }}
                                                </span>
                                            @endif
                                        </td>
                                        
                                        {{-- Estado Badge --}}
                                        <td style="text-align: center;">
                                            @if ($registro['asistio'])
                                                @if($registro['es_tarde'])
                                                    <span class="badge-status tarde">Tarde</span>
                                                @else
                                                    <span class="badge-status punctual">Puntual</span>
                                                @endif
                                            @else
                                                <span class="badge-status falta">Falta</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif

            <!-- SECCIÓN DE FIRMAS AL FINAL -->
            <div class="sig-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; padding-top: 20px; vertical-align: bottom;">
                            @if(isset($qr_code) && $qr_code)
                                <div style="margin-left: 10px; text-align: left;">
                                    <img src="data:image/png;base64,{{ $qr_code }}" width="50" style="display: block; margin-bottom: 3px; border: 1px solid #cbd5e1; padding: 2px; background: #fff;">
                                    <span style="font-size: 6px; color: #64748b; font-family: monospace; display: block;">VERIFICACIÓN: {{ $codigo_verificacion }}</span>
                                </div>
                            @endif
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: bottom;">
                            <div class="sig-box">
                                <span style="font-weight: bold; color: #2b5a6f; font-size: 8px; display: block; text-transform: uppercase; letter-spacing: 0.5px;">CONTROL ACADÉMICO</span>
                                <span style="font-weight: bold; color: #64748b; font-size: 7.5px; display: block; margin-top: 1px;">CEPRE-UNAMAD</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

        @else
            <div style="background-color: #fce4f0; color: #cc0000; border: 1px solid #cc0000; padding: 18px; border-radius: 6px; text-align: center; margin-top: 30px;">
                <h3 style="margin: 0; font-size: 10px; text-transform: uppercase;">Sin registros de asistencia</h3>
                <p style="margin: 4px 0 0 0; font-size: 8.5px;">El estudiante aún no tiene registros de asistencia en el presente ciclo.</p>
            </div>
        @endif

        <div class="pdf-footer">
            <strong>DOCUMENTO OFICIAL GENERADO POR EL SISTEMA DE CONTROL DE ASISTENCIA BIOMÉTRICA - CEPRE UNAMAD</strong><br>
            Generado por: {{ auth()->user()->nombre_completo ?? 'Administrador' }} &nbsp;|&nbsp; Fecha y Hora: {{ $fecha_generacion }}<br>
            Este reporte tiene validez exclusivamente académica y administrativa interna de la institución.
        </div>
    </div>
</body>

</html>
