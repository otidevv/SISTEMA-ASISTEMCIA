<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Asistencia Docente - {{ $cicloNombre }}</title>
    <style>
        @page { margin: 1.0cm; }
        body { 
            font-family: 'Helvetica', Arial, sans-serif; 
            font-size: 10px; 
            color: #333; 
            line-height: 1.4; 
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #2c3e50; 
            padding-bottom: 10px; 
            position: relative;
            min-height: 70px;
        }
        .header-logo-left { position: absolute; left: 0; top: 0; width: 60px; }
        .header-logo-right { position: absolute; right: 0; top: 0; width: 60px; }
        .header-logo-left img, .header-logo-right img { width: 60px; height: auto; }
        .header h1 { margin: 0; color: #2c3e50; text-transform: uppercase; font-size: 18px; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #7f8c8d; }
        
        .informe-box {
            margin: 20px 0;
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .informe-item { margin-bottom: 3px; }
        .informe-label { font-weight: bold; width: 60px; display: inline-block; text-transform: uppercase; color: #2c3e50; }
        
        .section-title { 
            background: #2c3e50;
            color: white;
            padding: 8px 12px; 
            margin: 20px 0 10px; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 11px; 
            border-radius: 3px;
        }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { 
            background-color: #f1f3fa; 
            color: #2c3e50; 
            padding: 8px 5px; 
            text-align: center; 
            font-size: 9px; 
            font-weight: bold; 
            border: 1px solid #dee2e6;
            text-transform: uppercase;
        }
        .data-table td { 
            padding: 8px 5px; 
            border: 1px solid #dee2e6; 
            font-size: 9px; 
            vertical-align: middle; 
        }
        .data-table tr:nth-child(even) { background-color: #fafbfc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .total-row { background-color: #2c3e50 !important; color: white !important; font-weight: bold; }
        .total-row td { border-color: #2c3e50 !important; }

        .signature-section { margin-top: 40px; width: 100%; }
        .signature-box { border-top: 1px solid #000; margin: 30px 15px 0; padding-top: 5px; text-align: center; font-size: 9px; }
        
        .qr-container { text-align: center; margin-top: 15px; }
        .qr-image { width: 60px; height: 60px; }

        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8px; color: #95a5a6; text-align: center; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo-left">
            <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="Logo UNAMAD">
        </div>
        <div class="header-logo-right">
            <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="Logo CEPRE">
        </div>
        <h1>CENTRO PREUNIVERSITARIO</h1>
        <p>Universidad Nacional Amazónica de Madre de Dios</p>
        <p style="font-weight: bold; color: #34495e; font-size: 14px; margin-top: 10px;">RESUMEN DE PAGOS POR DOCENTE</p>
    </div>

    <div class="informe-box">
        <div class="informe-item">
            <span class="informe-label">Ciclo:</span> 
            <span>{{ strtoupper($cicloNombre) }}</span>
        </div>
        <div class="informe-item">
            <span class="informe-label">Periodo:</span> 
            <span>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="section-title">Detalle de Asistencia y Liquidación</div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">Nº</th>
                <th width="37%" style="text-align: left;">DOCENTE / CURSOS</th>
                <th width="8%">SES.</th>
                <th width="12%">TIEMPO (H:M:S)</th>
                <th width="20%">MONTO REAL</th>
                <th width="20%">REDONDEADO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSesionesG = 0;
                $totalMontoG = 0;
                $totalRedondeadoG = 0;
            @endphp
            @foreach($processedDetailedAsistencias as $docenteId => $docenteData)
                @php
                    $sessionCount = 0;
                    $courses = [];
                    foreach ($docenteData['months'] as $month) {
                        foreach ($month['weeks'] as $week) {
                            $sessionCount += count($week['details']);
                            foreach ($week['details'] as $detail) {
                                if (!in_array($detail['curso'], $courses)) {
                                    $courses[] = $detail['curso'];
                                }
                            }
                        }
                    }
                    $courseList = implode(', ', $courses);
                    $totalSesionesG += $sessionCount;
                    $totalMontoG += $docenteData['total_pagos'];
                    $totalRedondeadoG += $docenteData['total_pagos_redondeado'];
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <span class="font-bold">{{ $docenteData['docente_info']->nombre }} {{ $docenteData['docente_info']->apellido_paterno }} {{ $docenteData['docente_info']->apellido_materno }}</span><br>
                        <small style="color: #666;">{{ $courseList ?: 'Sin cursos registrados' }}</small>
                    </td>
                    <td class="text-center">{{ $sessionCount }}</td>
                    <td class="text-center font-bold">{{ $docenteData['total_duracion_texto'] }}</td>
                    <td class="text-right">S/ {{ number_format($docenteData['total_pagos'], 2) }}</td>
                    <td class="text-right font-bold" style="background-color: #f8f9fa;">S/ {{ number_format($docenteData['total_pagos_redondeado'], 0) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL GENERAL CONSOLIDADO</td>
                <td class="text-center">{{ $totalSesionesG }}</td>
                <td class="text-center">---</td>
                <td class="text-right">S/ {{ number_format($totalMontoG, 2) }}</td>
                <td class="text-right">S/ {{ number_format($totalRedondeadoG, 0) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 40%; vertical-align: middle; border: none;">
                    <div class="qr-container" style="text-align: left;">
                        @if(isset($qrCode))
                            <img src="data:image/svg+xml;base64,{{ $qrCode }}" class="qr-image" alt="QR Code">
                        @endif
                        <div style="font-size: 7px; color: #95a5a6; margin-top: 5px;">Validación Digital Portal CEPRE</div>
                    </div>
                </td>
                <td style="width: 20%; border: none;"></td>
                <td style="width: 40%; vertical-align: bottom; border: none;">
                    <div class="signature-box" style="border-top: 1px solid #000; margin-top: 40px;">
                        <strong>ING. ROY KEVIN BONIFACIO FERNANDEZ</strong><br>
                        SERVICIO ESPECIALIZADO EN GESTIÓN DE SERVICIOS INFORMATICOS
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generado el {{ $fecha_generacion }} | Portal de Gestión de Asistencia - UNAMAD
    </div>
</body>
</html>
