<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia - {{ $docente->nombre_completo ?? $docente->nombre }}</title>
    <style>
        @page { margin: 1.0cm; }
        body { 
            font-family: 'Helvetica', Arial, sans-serif; 
            font-size: 10px; 
            color: #333; 
            line-height: 1.2; 
        }
        
        /* Watermark Optimized */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1000;
            width: 100%;
            height: 100%;
            text-align: center;
        }
        .watermark img {
            width: 500px; /* Fixed width to prevent scaling artifacts */
            height: auto;
            opacity: 0.08; /* Lower opacity */
            margin-top: 200px;
        }

        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #2c3e50; 
            padding-bottom: 10px; 
            position: relative;
            min-height: 60px;
        }
        .header-logo-left { position: absolute; left: 0; top: 0; width: 50px; }
        .header-logo-right { position: absolute; right: 0; top: 0; width: 50px; }
        .header-logo-left img, .header-logo-right img { width: 50px; height: auto; }
        .header h1 { margin: 0; color: #2c3e50; text-transform: uppercase; font-size: 16px; letter-spacing: 1px; padding: 0 60px; }
        .header p { margin: 3px 0 0; font-size: 11px; color: #7f8c8d; padding: 0 60px; }
        
        .section-title { 
            background: linear-gradient(to right, #f1f4f9, white);
            padding: 5px 12px; 
            border-left: 4px solid #3498db; 
            margin: 15px 0 10px; 
            font-weight: bold; 
            color: #2c3e50; 
            text-transform: uppercase; 
            font-size: 10px; 
        }
        
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-grid td { padding: 4px 0; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #7f8c8d; width: 120px; text-transform: uppercase; font-size: 9px; }
        .value { color: #2c3e50; font-size: 10px; font-weight: 500; }
        
        /* Premium Summary Cards */
        .summary-container {
            display: table;
            width: 100%;
            border-spacing: 10px 0;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 33%;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .summary-card-title {
            display: block;
            color: #7f8c8d;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .summary-card-value {
            display: block;
            color: #2c3e50;
            font-size: 16px;
            font-weight: bold;
        }
        .summary-card-icon {
            color: #3498db;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .data-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 5px; border: 1px solid #e0e0e0; border-radius: 5px; overflow: hidden; }
        .data-table th { background-color: #2c3e50; color: white; padding: 8px; text-align: left; font-size: 9px; font-weight: 600; text-transform: uppercase; }
        .data-table td { padding: 8px; border-bottom: 1px solid #f0f0f0; font-size: 9px; vertical-align: middle; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:nth-child(even) { background-color: #fcfcfc; }
        
        .month-header { background-color: #e8f4fd !important; font-weight: bold; color: #2980b9; }
        .week-header { background-color: #f7f9fb !important; font-style: italic; color: #636e72; font-size: 9px; }
        
        .status-badge { padding: 3px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .status-completada { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-pendiente { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-falta { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status-incompleta { background-color: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }
        .status-sin-tema { background-color: #fbecec; color: #c0392b; border: 1px solid #ea9999; } /* Red/Orange for attention */
        
        .status-icon { margin-right: 3px; font-family: DejaVu Sans, sans-serif; }

        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8px; color: #95a5a6; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        
        .signature-section { margin-top: 40px; page-break-inside: avoid; }
        .signature-box { border-top: 1px solid #000; margin: 0 10px; padding-top: 5px; }
        
        .qr-container {
            text-align: center;
        }
        .qr-image {
            width: 70px;
            height: 70px;
        }
    </style>
</head>
<body>
    <!-- Watermark Background -->
    <div class="watermark">
        <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="Watermark">
    </div>

    <div class="header">
        <div class="header-logo-left">
            <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="Logo UNAMAD">
        </div>
        <div class="header-logo-right">
            <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="Logo CEPRE">
        </div>
        <h1>REPORTE DE ASISTENCIA Y TEMAS DESARROLLADOS</h1>
        <p>Ciclo Académico: {{ $ciclo->nombre }}</p>
        <p style="font-size: 8px; font-style: italic; color: #95a5a6; margin-top: 2px;">"Centro Pre-Universitario - UNAMAD"</p>
    </div>

    <div class="section-title">Información del Docente</div>
    <table class="info-grid">
        <tr>
            <td class="label">Docente:</td>
            <td class="value">{{ $docente->nombre }} {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}</td>
            <td class="label">DNI:</td>
            <td class="value">{{ $docente->numero_documento }}</td>
        </tr>
        <tr>
            <td class="label">Periodo:</td>
            <td colspan="3" class="value">
                @if($fechaInicio && $fechaFin)
                    Del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                @else
                    Ciclo Completo
                @endif
            </td>
        </tr>
    </table>

    <!-- Graphic Summary Cards -->
    <div class="summary-container">
        <div class="summary-card">
            <div class="summary-card-title">Total Horas Dictadas</div>
            <div class="summary-card-value">{{ number_format($data['total_horas'], 2) }} hrs</div>
        </div>
        <div class="summary-card">
            @php
                $totalSesiones = 0;
                foreach($data['months'] as $month) {
                    foreach($month['weeks'] as $week) {
                        $totalSesiones += count($week['details']);
                    }
                }
            @endphp
            <div class="summary-card-title">Total Sesiones</div>
            <div class="summary-card-value">{{ $totalSesiones }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Pago Estimado</div>
            <div class="summary-card-value" style="color: #27ae60;">S/ {{ number_format($data['total_pagos_redondeado'], 2) }}</div>
            <div style="font-size: 8px; color: #7f8c8d; margin-top: 2px;">Real: S/ {{ number_format($data['total_pagos'], 2) }}</div>
        </div>
    </div>

    <div class="section-title">Detalle de Sesiones y Temas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="10%">Fecha</th>
                <th width="20%">Curso / Aula</th>
                <th width="12%">Horario</th>
                <th width="12%">Registro</th>
                <th width="8%">Hrs</th>
                <th width="13%">Estado</th>
                <th width="25%">Tema Desarrollado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['months'] as $monthName => $monthData)
                <tr class="month-header">
                    <td colspan="7">{{ strtoupper($monthName) }}</td>
                </tr>
                @foreach($monthData['weeks'] as $weekNum => $weekData)
                    <tr class="week-header">
                        <td colspan="7">
                            Semana {{ $weekNum }} &nbsp;|&nbsp; 
                            Horas: {{ number_format($weekData['total_horas'], 2) }} &nbsp;|&nbsp; 
                            Pago Real: S/ {{ number_format($weekData['total_pagos'], 2) }} &nbsp;
                            <strong style="color: #27ae60;">(Redondeado: S/ {{ number_format($weekData['total_pagos_redondeado'], 2) }})</strong>
                        </td>
                    </tr>
                    @foreach($weekData['details'] as $session)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($session['fecha'])->format('d/m/Y') }}</td>
                            <td>
                                <strong>{{ $session['curso'] }}</strong><br>
                                <span style="color: #7f8c8d; font-size: 8px;">{{ $session['aula'] }} - {{ $session['turno'] }}</span>
                            </td>
                            <td>{{ $session['hora_entrada_prog'] ?? '' }} - {{ $session['hora_salida_prog'] ?? '' }}</td>
                            <td>
                                <div style="font-size: 8px;">E: {{ $session['hora_entrada'] }}</div>
                                <div style="font-size: 8px;">S: {{ $session['hora_salida'] }}</div>
                            </td>
                            <td style="text-align: right; font-weight: bold;">
                                {{ number_format($session['horas_dictadas'], 2) }}
                                <br><span style="font-size: 8px; color: #7f8c8d;">({{ $session['duracion_texto'] }})</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = 'status-' . strtolower(str_replace(' ', '-', $session['estado_sesion']));
                                    $icon = '';
                                    $estadoLower = strtolower($session['estado_sesion']);
                                    
                                    if($estadoLower == 'completada') $icon = '✔';
                                    elseif($estadoLower == 'pendiente') $icon = '⏳';
                                    elseif($estadoLower == 'falta') $icon = '✖';
                                    elseif($estadoLower == 'sin tema') $icon = '⚠';
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    <span class="status-icon">{{ $icon }}</span> {{ $session['estado_sesion'] }}
                                </span>
                            </td>
                            <td style="font-size: 8px;">
                                @if($session['tema_desarrollado'] && $session['tema_desarrollado'] != 'Pendiente')
                                    {{ $session['tema_desarrollado'] }}
                                @else
                                    <span style="color: #e74c3c; font-style: italic;">[Sin tema]</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="signature-section">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 40%; text-align: center; vertical-align: bottom; border: none;">
                    <br><br><br>
                    <div class="signature-box">
                        <strong>{{ strtoupper($docente->nombre . ' ' . $docente->apellido_paterno . ' ' . $docente->apellido_materno) }}</strong><br>
                        DOCENTE
                    </div>
                </td>
                <td style="width: 20%; text-align: center; vertical-align: middle; border: none;">
                     @if(isset($qrCode))
                        <div class="qr-container">
                            <img src="data:image/svg+xml;base64,{{ $qrCode }}" class="qr-image" alt="QR Code">
                        </div>
                    @endif
                </td>
                <td style="width: 40%; text-align: center; vertical-align: bottom; border: none;">
                    <br><br><br>
                    <div class="signature-box">
                        <strong>ING. ROY KEVIN BONIFACIO FERNANDEZ</strong><br>
                        INGENIERO DE SISTEMAS E INFORMATICA
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Este documento es un reporte generado automáticamente por el <strong>PORTAL CEPRE UNAMAD</strong> - {{ $fecha_generacion }}<br>
        Centro Preuniversitario - Universidad Nacional Amazónica de Madre de Dios | Página <span class="page-number"></span>
    </div>
</body>
</html>
