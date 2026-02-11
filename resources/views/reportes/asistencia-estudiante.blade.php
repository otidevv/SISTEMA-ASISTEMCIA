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

        .container {
            width: 100%;
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
            border-bottom: 2px solid #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .header-logo {
            width: 100px;
            vertical-align: middle;
        }

        .header-info {
            text-align: right;
            vertical-align: middle;
        }

        .header-info h1 {
            color: #2c3e50;
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .header-info p {
            color: #555;
            font-size: 11px;
            margin: 2px 0;
        }

        .ciclo-badge {
            background-color: #2c3e50;
            color: white;
            padding: 3px 10px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 10px;
            display: inline-block;
            margin-top: 5px;
        }

        /* TÍTULOS DE SECCIÓN */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 12px;
            padding-bottom: 3px;
            border-bottom: 1px solid #dee2e6;
            text-transform: uppercase;
        }

        /* TABLA DE DETALLE */
        .month-header-table {
            width: 100%;
            background-color: #34495e;
            color: white;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .month-header-table td {
            padding: 6px 10px;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .data-table th {
            background-color: #f8f9fa;
            color: #333;
            padding: 6px;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        .data-table td {
            padding: 5px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            text-align: center;
        }

        .falta-row {
            background-color: #fff1f0;
        }

        .text-success { color: #27ae60; font-weight: bold; }
        .text-danger { color: #e74c3c; font-weight: bold; }
        .text-muted { color: #777; font-style: italic; }

        /* FOOTER */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #2c3e50;
            text-align: center;
            font-size: 9px;
            color: #555;
        }

        .page-break {
            page-break-after: always;
        }

        /* ESTILOS ESPECÍFICOS PARA PDF */
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- ENCABEZADO INSTITUCIONAL (Sincronizado con Constancia de Vacante) -->
        <table class="table-layout inst-header" style="border-bottom: 4px double #1a365d; background: #f8fafc; border-radius: 8px 8px 0 0;">
            <tr>
                <td style="width: 85px; padding: 10px 15px; vertical-align: middle;">
                    @php
                        $logoUnamad = public_path('assets/images/logo unamad constancia.png');
                        $logoUnamadData = file_exists($logoUnamad) 
                            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoUnamad)) 
                            : null;
                    @endphp
                    @if($logoUnamadData)
                        <img src="{{ $logoUnamadData }}" alt="Logo UNAMAD" style="width: 75px; height: auto;">
                    @else
                        <div style="width:65px;height:65px;border:1px solid #1a365d;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:7pt;color:#1a365d;font-weight:bold;text-align:center;">UNAMAD</div>
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
                <td style="width: 85px; padding: 10px 15px; text-align: right; vertical-align: middle;">
                    @php
                        $logoCepre = public_path('assets/images/logo cepre costancia.png');
                        $logoCepreData = file_exists($logoCepre) 
                            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoCepre)) 
                            : null;
                    @endphp
                    @if($logoCepreData)
                        <img src="{{ $logoCepreData }}" alt="Logo CEPRE" style="width: 75px; height: auto;">
                    @else
                        <div style="width:65px;height:65px;border:1px solid #1a365d;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:7pt;color:#1a365d;font-weight:bold;text-align:center;">CEPRE</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Espaciador -->
        <div style="height: 10px;"></div>

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

                <div class="section-title">Detalle Mensual de Asistencias</div>

                @foreach ($detalle_asistencias as $mesKey => $mes)
                    <div class="no-break">
                        <table class="month-header-table">
                            <tr>
                                <td>{{ strtoupper($mes['mes']) }} {{ $mes['anio'] }}</td>
                                <td style="text-align: right; font-size: 10px;">
                                    ASISTIDOS: <strong>{{ $mes['dias_asistidos'] }}</strong> | 
                                    FALTAS: <strong>{{ $mes['dias_falta'] }}</strong>
                                </td>
                            </tr>
                        </table>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Fecha</th>
                                    <th style="width: 20%;">Día</th>
                                    <th style="width: 20%;">Entrada</th>
                                    <th style="width: 20%;">Salida</th>
                                    <th style="width: 20%;">Estado</th>
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
                                                <span class="text-success">Asistió</span>
                                            @else
                                                <span class="text-danger">Falta</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        @else
            <div style="background-color: #fff8e1; color: #856404; border: 1px solid #ffc107; padding: 20px; border-radius: 8px; text-align: center; margin-top: 30px;">
                <h3 style="margin: 0;">Sin registros de asistencia</h3>
                <p style="margin: 5px 0 0 0;">El estudiante aún no tiene registros de asistencia en el presente ciclo.</p>
            </div>
        @endif

        <div class="footer">
            <strong>Políticas de Control:</strong> Amonestación: {{ $ciclo->porcentaje_amonestacion }}% | Inhabilitación: {{ $ciclo->porcentaje_inhabilitacion }}%<br>
            A/F/T = Asistidos / Faltas / Total Días Hábiles. Datos obtenidos directamente del sistema de control biométrico.<br>
            <span style="font-weight: bold; display: block; margin-top: 8px;">DOCUMENTO GENERADO POR EL SISTEMA DE GESTIÓN ACADÉMICA</span>
        </div>
    </div>
</body>

</html>
