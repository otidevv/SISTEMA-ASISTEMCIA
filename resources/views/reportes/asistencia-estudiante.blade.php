<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia Individual - {{ $estudiante->numero_documento }}</title>
    <style>
        /* ESTILOS PREMIUM PARA REPORTE PDF */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: #2c3e50;
            --primary-light: #34495e;
            --secondary: #bdc3c7;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --text-main: #1a1a1a;
            --text-muted: #555555;
            --bg-light: #f8f9fa;
            --border-color: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: var(--text-main);
            background-color: #fff;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ENCABEZADO INSTITUCIONAL */
        .inst-header {
            display: table;
            width: 100%;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 120px;
        }

        .header-logo img {
            max-width: 100px;
        }

        .header-info {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .header-info h1 {
            color: var(--primary);
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-info p {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 500;
        }

        .ciclo-badge {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            margin-top: 5px;
            font-size: 11px;
        }

        /* SECCIÓN INFO ESTUDIANTE (DENTRO DEL PARTIAL PERO ESTILOS AQUÍ) */
        .info-section {
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        .info-container {
            display: table;
            width: 100%;
        }

        .student-photo-container {
            display: table-cell;
            vertical-align: top;
            width: 100px;
            padding-right: 20px;
        }

        .student-photo {
            width: 100px;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid var(--primary);
            background-color: #fff;
        }

        .student-details {
            display: table-cell;
            vertical-align: top;
        }

        .student-details h3 {
            font-size: 14px;
            color: var(--primary);
            margin-bottom: 12px;
            border-bottom: 1px solid var(--secondary);
            padding-bottom: 4px;
        }

        .info-grid {
            width: 100%;
        }

        .info-item {
            display: inline-block;
            width: 48%;
            margin-bottom: 8px;
            vertical-align: top;
        }

        .info-item.full-width {
            width: 100%;
        }

        .info-label {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 10px;
            display: block;
        }

        .info-value {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
        }

        .info-value.highlight {
            font-size: 15px;
            color: var(--primary);
            font-weight: 800;
        }

        /* RESUMEN DE EXAMEN */
        .section-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
        }

        .exam-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .exam-header {
            padding: 10px 15px;
            color: white;
            display: table;
            width: 100%;
        }

        .exam-header.success { background-color: var(--success); }
        .exam-header.warning { background-color: var(--warning); }
        .exam-header.danger { background-color: var(--danger); }

        .exam-title-group {
            display: table-cell;
            vertical-align: middle;
        }

        .exam-title-group h4 {
            font-size: 13px;
            font-weight: 700;
            margin: 0;
            display: inline-block;
        }

        .exam-icon {
            font-size: 16px;
            font-weight: 900;
            margin-right: 8px;
        }

        .exam-badges {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .badge {
            background-color: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
        }

        .dias-info {
            font-size: 10px;
            margin-left: 10px;
        }

        .exam-body {
            padding: 15px;
        }

        .kpi-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .kpi-item {
            display: table-cell;
            text-align: center;
            width: 25%;
            border-right: 1px solid var(--border-color);
        }

        .kpi-item:last-child {
            border-right: none;
        }

        .kpi-label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: 800;
        }

        .kpi-value.success { color: var(--success); }
        .kpi-value.warning { color: var(--warning); }
        .kpi-value.danger { color: var(--danger); }

        .progress-bar-wrapper {
            margin-top: 10px;
        }

        .progress-bar {
            height: 18px;
            background-color: #e9ecef;
            border-radius: 9px;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }

        .progress-fill {
            height: 100%;
            text-align: center;
            color: white;
            font-size: 9px;
            font-weight: 800;
            line-height: 16px;
        }

        .progress-fill.success { background-color: var(--success); }
        .progress-fill.warning { background-color: var(--warning); }
        .progress-fill.danger { background-color: var(--danger); }

        .exam-footer {
            padding: 8px 15px;
            font-size: 11px;
            text-align: center;
        }

        .exam-footer.success { background-color: #e8f5e9; color: #1b5e20; }
        .exam-footer.danger { background-color: #ffebee; color: #b71c1c; }

        /* TABLA DE DETALLE */
        .month-header {
            background-color: var(--primary-light);
            color: white;
            padding: 8px 15px;
            display: table;
            width: 100%;
            font-weight: 700;
            border-radius: 6px 6px 0 0;
            margin-top: 25px;
        }

        .month-stats {
            display: table-cell;
            text-align: right;
            font-size: 10px;
        }

        .stat-badge {
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 5px;
            background-color: rgba(255,255,255,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border-color);
        }

        table th {
            background-color: var(--bg-light);
            color: var(--primary);
            padding: 8px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 800;
            border: 1px solid var(--border-color);
        }

        table td {
            padding: 7px 10px;
            border: 1px solid var(--border-color);
            font-size: 11px;
            text-align: center;
        }

        tr.falta-row {
            background-color: #fff1f0;
        }

        .text-success { color: var(--success); font-weight: 700; }
        .text-danger { color: var(--danger); font-weight: 700; }
        .text-muted { color: var(--text-muted); font-style: italic; }

        /* FOOTER */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid var(--primary);
            text-align: center;
            font-size: 10px;
            color: var(--text-muted);
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            .container { padding: 0; }
            .info-section { border: 1px solid #ddd; }
            .exam-card { border: 1px solid #ddd; page-break-inside: avoid; }
            .inst-header { border-bottom: 2px solid #000; }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- ENCABEZADO INSTITUCIONAL -->
        <div class="inst-header">
            <div class="header-logo">
                @php
                    $logoPath = public_path('assets/images/logo-institucional.png');
                    if (file_exists($logoPath)) {
                        $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                    } else {
                        // Fallback placeholder logo
                        $logoData = 'data:image/svg+xml;base64,' . base64_encode('<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="#2c3e50"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="white" font-family="Arial" font-size="20">LOGO</text></svg>');
                    }
                @endphp
                <img src="{{ $logoData }}" alt="Logo">
            </div>
            <div class="header-info">
                <h1>REPORTE DE ASISTENCIA</h1>
                <p>Centro de Preparación Académica - Gestión de Control Académico</p>
                <div class="ciclo-badge">CICLO: {{ $ciclo->nombre }}</div>
                <p style="margin-top: 5px;">Generado el: {{ $fecha_generacion }}</p>
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

                <div class="inst-header" style="border-bottom: 2px dashed var(--secondary); margin-bottom: 10px;">
                    <div class="header-info" style="text-align: left;">
                        <h2 style="color: var(--primary); font-size: 18px; font-weight: 800;">DETALLE MENSUAL DE ASISTENCIAS</h2>
                    </div>
                </div>

                @foreach ($detalle_asistencias as $mesKey => $mes)
                    <div class="month-header">
                        <span>{{ strtoupper($mes['mes']) }} {{ $mes['anio'] }}</span>
                        <div class="month-stats">
                            <span class="stat-badge">ASISTIDOS: <strong>{{ $mes['dias_asistidos'] }}</strong></span>
                            <span class="stat-badge">FALTAS: <strong>{{ $mes['dias_falta'] }}</strong></span>
                        </div>
                    </div>
                    <table>
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
                @endforeach
            @endif
        @else
            <div style="background-color: #fff8e1; color: #856404; border: 1px solid #ffc107; padding: 20px; border-radius: 8px; text-align: center; margin-top: 30px;">
                <h3 style="margin-bottom: 5px;">Sin registros de asistencia</h3>
                <p>El estudiante aún no tiene registros de asistencia en el presente ciclo académico.</p>
            </div>
        @endif

        <div class="footer">
            <p><strong>Políticas Generales:</strong></p>
            <p>Amonestación: {{ $ciclo->porcentaje_amonestacion }}% inasistencias | Inhabilitación: {{ $ciclo->porcentaje_inhabilitacion }}% inasistencias</p>
            <p>A=Asistidos, F=Faltas, T=Total Días Hábiles. Los datos mostrados corresponden a la información registrada en el sistema biométrico.</p>
            <p style="margin-top: 10px; font-weight: 700;">DOCUMENTO GENERADO POR EL SISTEMA DE GESTIÓN ACADÉMICA</p>
        </div>
    </div>
</body>

</html>
