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
            margin: 1.3cm 1.5cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* MARCA DE AGUA INSTITUCIONAL (Sutil) */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.035;
            z-index: -1000;
            width: 450px;
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
        .inst-header-outer {
            border: 2px solid #000;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .inst-header-top {
            background-color: #2b5a6f;
            padding: 10px;
            color: white;
        }

        .inst-header-stripe {
            height: 4px;
            background: #000;
            background: linear-gradient(to right, #cc0000 0% 33%, #00aeef 33% 66%, #8cc63f 66% 100%);
        }

        .inst-header-bottom {
            background-color: #f1f5f9;
            text-align: center;
            padding: 4px;
            font-size: 8.5px;
            font-weight: bold;
            border-top: 1px solid #aaa;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* TÍTULOS DE SECCIÓN */
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #000;
            margin: 15px 0 8px 0;
            padding: 3px 0;
            border-top: 2.5px solid #000;
            border-bottom: 1px solid #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* TABLA DE DETALLE MENSUAL */
        .month-header-table {
            width: 100%;
            background-color: #2b5a6f;
            color: white;
            border-collapse: collapse;
            margin-top: 12px;
            border: 1px solid #000;
        }

        .month-header-table td {
            padding: 5px 8px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            border: 1.5px solid #000;
            border-top: none;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #000;
            padding: 4px 6px;
            font-size: 7.5px;
            text-transform: uppercase;
            font-weight: bold;
            border: 1px solid #888;
            text-align: center;
        }

        .data-table td {
            padding: 4px 6px;
            border: 1px solid #aaa;
            font-size: 8px;
            text-align: center;
        }

        .falta-row td {
            background-color: #f5f5f5 !important;
        }

        .text-success { color: #5a8a1f; font-weight: bold; }
        .text-danger { color: #cc0000; font-weight: bold; }
        .text-muted { color: #555; font-style: italic; }

        /* SECCIÓN DE FIRMAS */
        .signature-container {
            margin-top: 35px;
        }

        .signature-box {
            width: 200px;
            text-align: center;
            border-top: 1.5px solid #000;
            padding-top: 4px;
            margin: 0 auto;
        }

        /* FOOTER */
        .footer {
            margin-top: 20px;
            padding-top: 5px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 7.5px;
            color: #000;
            line-height: 1.4;
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
        <!-- ENCABEZADO INSTITUCIONAL -->
        <div class="inst-header-outer">
            <div class="inst-header-top">
                <table class="table-layout">
                    <tr>
                        <td style="width: 75px; vertical-align: middle; text-align: left;">
                            @php
                                $logoUnamad = public_path('assets/images/logo unamad constancia.png');
                                $logoUnamadData = file_exists($logoUnamad) 
                                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoUnamad)) 
                                    : null;
                            @endphp
                            @if($logoUnamadData)
                                <img src="{{ $logoUnamadData }}" alt="Logo UNAMAD" style="width: 58px; height: auto;">
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; color: #ffffff; letter-spacing: 0.5px; margin-bottom: 2px;">
                                Universidad Nacional Amazónica de Madre de Dios
                            </div>
                            <div style="font-size: 9.5pt; font-weight: bold; color: #e0e0e0; font-style: italic;">
                                "Centro Pre-Universitario (CEPRE)"
                            </div>
                        </td>
                        <td style="width: 75px; vertical-align: middle; text-align: right;">
                            @php
                                $logoCepre = public_path('assets/images/logo cepre costancia.png');
                                $logoCepreData = file_exists($logoCepre) 
                                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoCepre)) 
                                    : null;
                            @endphp
                            @if($logoCepreData)
                                <img src="{{ $logoCepreData }}" alt="Logo CEPRE" style="width: 58px; height: auto;">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="inst-header-stripe"></div>
            <div class="inst-header-bottom">
                Reporte de Asistencia de Estudiante &mdash; Ciclo: {{ $ciclo->nombre }}
            </div>
        </div>

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
                    <div class="no-break" style="margin-bottom: 12px;">
                        <table class="month-header-table">
                            <tr>
                                <td style="padding: 4px 8px;">{{ strtoupper($mes['mes']) }} {{ $mes['anio'] }}</td>
                                <td style="text-align: right; padding: 4px 8px; font-size: 8.5px; font-family: monospace;">
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
                                        <td style="font-family: monospace;">{{ $registro['fecha'] }}</td>
                                        <td>{{ $registro['dia_semana'] }}</td>
                                        
                                        {{-- Lógica de visualización de Hora de Entrada --}}
                                        <td class="{{ 
                                            $registro['hora_entrada'] == 'Sin registro' ? 'text-muted' : 
                                            ($registro['hora_entrada'] == 'FALTA' ? 'text-danger' : 
                                            ($registro['es_tarde'] ? 'text-danger fw-bold' : '')) 
                                        }}">
                                            {{ $registro['hora_entrada'] }}
                                            @if($registro['es_tarde'])
                                                <span style="font-size: 7px; display: block; color: #cc0000; font-weight: bold;">(TARDE)</span>
                                            @endif
                                        </td>

                                        <td class="{{ $registro['hora_salida'] == 'FALTA' ? 'text-danger' : '' }}">
                                            {{ $registro['hora_salida'] }}
                                        </td>
                                        
                                        <td>
                                            @if ($registro['asistio'])
                                                @if($registro['es_tarde'])
                                                    <span style="color: #cc0000; font-weight: bold;">ASISTENCIA (TARDE)</span>
                                                @else
                                                    <span class="text-success">ASISTENCIA PUNTUAL</span>
                                                @endif
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

            <!-- SECCIÓN DE FIRMAS AL FINAL -->
            <div class="no-break signature-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; padding-top: 40px;">
                            @if(isset($qr_code) && $qr_code)
                                <div style="margin-left: 10px; text-align: left;">
                                    <img src="data:image/png;base64,{{ $qr_code }}" width="55" style="display: block; margin-bottom: 4px; border: 1.5px solid #000; padding: 2px; background: #fff;">
                                    <span style="font-size: 6.5px; color: #000; font-family: monospace; display: block;">VERIF: {{ $codigo_verificacion }}</span>
                                </div>
                            @endif
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: bottom;">
                            <div class="signature-box">
                                <span style="font-weight: bold; color: #2b5a6f; font-size: 8.5px; display: block; text-transform: uppercase; letter-spacing: 0.3px;">CONTROL ACADÉMICO</span>
                                <span style="font-weight: bold; color: #555; font-size: 8px; display: block;">CEPRE-UNAMAD</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

        @else
            <div style="background-color: #fce4f0; color: #cc0000; border: 1.5px solid #cc0000; padding: 18px; border-radius: 4px; text-align: center; margin-top: 30px;">
                <h3 style="margin: 0; font-size: 11px; text-transform: uppercase;">Sin registros de asistencia</h3>
                <p style="margin: 4px 0 0 0; font-size: 9px;">El estudiante aún no tiene registros de asistencia en el presente ciclo.</p>
            </div>
        @endif

        <div class="footer">
            <strong>DOCUMENTO OFICIAL GENERADO POR EL SISTEMA DE CONTROL DE ASISTENCIA BIOMÉTRICA - CEPRE UNAMAD</strong><br>
            Generado por: {{ auth()->user()->nombre_completo ?? 'Administrador' }} | Fecha y Hora: {{ $fecha_generacion }}<br>
            AST/FAL/TOT = Asistidos / Faltas / Total Días Hábiles. Este reporte tiene validez exclusivamente académica y administrativa.
        </div>
    </div>
</body>

</html>
