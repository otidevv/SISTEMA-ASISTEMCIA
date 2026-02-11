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
            margin: 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #1a1a1a;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        /* MARCA DE AGUA INSTITUCIONAL (Sutil para no estorbar) */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.04;
            z-index: -1000;
            width: 500px;
            text-align: center;
        }

        .container {
            width: 100%;
            position: relative;
        }

        /* TABLAS DE ESTRUCTURA */
        .table-layout {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        /* ENCABEZADO INSTITUCIONAL */
        .inst-header {
            width: 100%;
            border-bottom: 4px double #1a365d;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        /* TÍTULOS DE SECCIÓN */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a365d;
            margin: 15px 0 10px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #dee2e6;
            text-transform: uppercase;
        }

        /* TABLA DE DETALLE */
        .month-header-table {
            width: 100%;
            background-color: #1a365d;
            color: white;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .month-header-table td {
            padding: 5px 10px;
            font-weight: bold;
            font-size: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #1a365d;
            padding: 5px;
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .data-table td {
            padding: 4px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            text-align: center;
        }

        .falta-row {
            background-color: #fff5f5;
        }

        .text-success { color: #059669; font-weight: bold; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-muted { color: #64748b; font-style: italic; }

        /* SECCIÓN DE FIRMAS */
        .signature-container {
            margin-top: 40px;
            width: 100%;
        }

        .signature-box {
            width: 200px;
            text-align: center;
            border-top: 1px solid #1a365d;
            padding-top: 5px;
            margin: 0 auto;
        }

        /* FOOTER */
        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 2px solid #1a365d;
            text-align: center;
            font-size: 8px;
            color: #475569;
        }

        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <!-- ELEMENTOS DECORATIVOS FIJOS -->
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
        <!-- ENCABEZADO INSTITUCIONAL (Sincronizado con Constancia de Vacante) -->
        <table class="table-layout inst-header" style="border-bottom: 4px double #1a365d; background: #f8fafc; border-radius: 8px 8px 0 0;">
            <tr>
                <td style="width: 85px; padding: 10px; vertical-align: middle;">
                    @php
                        $logoUnamad = public_path('assets/images/logo unamad constancia.png');
                        $logoUnamadData = file_exists($logoUnamad) 
                            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoUnamad)) 
                            : null;
                    @endphp
                    @if($logoUnamadData)
                        <img src="{{ $logoUnamadData }}" alt="Logo UNAMAD" style="width: 70px; height: auto;">
                    @endif
                </td>
                <td style="text-align: center; vertical-align: middle; padding: 10px 0;">
                    <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; color: #1a365d; margin-bottom: 2px;">
                        Universidad Nacional Amazónica de Madre de Dios
                    </div>
                    <div style="font-size: 11pt; font-weight: bold; color: #4a5568; font-style: italic; margin-bottom: 4px;">
                        "Centro Pre-Universitario"
                    </div>
                    <div style="background-color: #1a365d; color: white; padding: 2px 15px; border-radius: 4px; font-weight: bold; font-size: 11px; display: inline-block; text-transform: uppercase;">
                        Reporte de Asistencia - Ciclo: {{ $ciclo->nombre }}
                    </div>
                </td>
                <td style="width: 85px; padding: 10px; text-align: right; vertical-align: middle;">
                    @php
                        $logoCepre = public_path('assets/images/logo cepre costancia.png');
                        $logoCepreData = file_exists($logoCepre) 
                            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoCepre)) 
                            : null;
                    @endphp
                    @if($logoCepreData)
                        <img src="{{ $logoCepreData }}" alt="Logo CEPRE" style="width: 70px; height: auto;">
                    @endif
                </td>
            </tr>
        </table>

        <!-- SECCIÓN DE INFORMACIÓN DEL ESTUDIANTE -->
        @include('reportes.partials.info-estudiante')

        @if (isset($info_asistencia) && !empty($info_asistencia))

            <div class="section-title">Resumen de Seguimiento Académico</div>

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
                <!-- Mostrar borde nuevamente en pág 2 si DomPDF lo permite vía fixed -->

                <div class="section-title">Detalle Mensual de Asistencias</div>

                @foreach ($detalle_asistencias as $mesKey => $mes)
                    <div class="no-break" style="margin-bottom: 10px;">
                        <table class="month-header-table">
                            <tr>
                                <td>{{ strtoupper($mes['mes']) }} {{ $mes['anio'] }}</td>
                                <td style="text-align: right; font-size: 9px;">
                                    ASISTIDOS: {{ $mes['dias_asistidos'] }} | FALTAS: {{ $mes['dias_falta'] }}
                                </td>
                            </tr>
                        </table>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 18%;">Fecha</th>
                                    <th style="width: 18%;">Día</th>
                                    <th style="width: 18%;">Entrada</th>
                                    <th style="width: 18%;">Salida</th>
                                    <th style="width: 28%;">Estado del Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mes['registros'] as $registro)
                                    <tr class="{{ !$registro['asistio'] ? 'falta-row' : '' }}">
                                        <td>{{ $registro['fecha'] }}</td>
                                        <td>{{ $registro['dia_semana'] }}</td>
                                        <td class="{{ $registro['hora_entrada'] == 'Sin registro' ? 'text-muted' : ($registro['hora_entrada'] == 'FALTA' ? 'text-danger' : '') }}">
                                            {{ $registro['hora_entrada'] }}
                                        </td>
                                        <td class="{{ $registro['hora_salida'] == 'FALTA' ? 'text-danger' : '' }}">
                                            {{ $registro['hora_salida'] }}
                                        </td>
                                        <td>
                                            @if ($registro['asistio'])
                                                <span class="text-success">ASISTENCIA REGISTRADA</span>
                                            @else
                                                <span class="text-danger">INASISTENCIA / FALTA</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif

            <!-- SECCIÓN DE FIRMAS AL FINAL (no-break para que no se separe la línea del cargo) -->
            <div class="no-break signature-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; padding-top: 50px;">
                            <div class="qr-box" style="position: static; margin-left: 20px; text-align: left; border: none;">
                                @php
                                    $qrData = 'data:image/svg+xml;base64,' . base64_encode('<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><rect width="60" height="60" fill="#eee"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#999" font-family="Arial" font-size="8">E-Sello</text></svg>');
                                @endphp
                                <img src="{{ $qrData }}" width="50" style="display: block; margin-bottom: 5px;">
                                <span style="font-size: 7px; color: #94a3b8; font-family: monospace;">VAL-{{ $estudiante->numero_documento }}-{{ date('Y') }}</span>
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: bottom;">
                            <div class="signature-box">
                                <span style="font-weight: bold; color: #1a365d; font-size: 10px; display: block;">CONTROL ACADÉMICO</span>
                                <span style="font-weight: bold; color: #475569; font-size: 9px; display: block;">CEPRE-UNAMAD</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

        @else
            <div style="background-color: #fff8e1; color: #856404; border: 1px solid #ffc107; padding: 20px; border-radius: 8px; text-align: center; margin-top: 30px;">
                <h3 style="margin: 0;">Sin registros de asistencia</h3>
                <p style="margin: 5px 0 0 0;">El estudiante aún no tiene registros de asistencia en el presente ciclo.</p>
            </div>
        @endif

        <div class="footer">
            <strong>DOCUMENTO OFICIAL GENERADO POR EL SISTEMA DE CONTROL DE ASISTENCIA BIOMÉTRICA - CEPRE UNAMAD</strong><br>
            Generado por: {{ auth()->user()->nombre_completo ?? 'Administrador' }} | Fecha y Hora: {{ $fecha_generacion }}<br>
            A/F/T = Asistidos / Faltas / Total Días Hábiles. Documento para uso exclusivamente académico administrativo.
        </div>
    </div>
</body>

</html>

</html>
