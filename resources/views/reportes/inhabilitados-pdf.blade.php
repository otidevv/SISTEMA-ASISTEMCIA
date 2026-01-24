<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inhabilitados - {{ $ciclo->nombre }}</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #4f32c2;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header table {
            width: 100%;
        }
        .logo {
            width: 60px;
        }
        .title-container {
            text-align: center;
        }
        .title-container h1 {
            margin: 0;
            font-size: 18px;
            color: #4f32c2;
            text-transform: uppercase;
        }
        .title-container p {
            margin: 5px 0 0 0;
            font-size: 12px;
            font-weight: bold;
        }
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .info-grid {
            width: 100%;
        }
        .info-grid td {
            padding: 3px 0;
        }
        .label {
            font-weight: bold;
            color: #4f32c2;
            width: 150px;
        }
        .stats-container {
            margin-bottom: 20px;
            width: 100%;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats-box {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            background: #fff;
        }
        .stats-value {
            font-size: 20px;
            font-weight: bold;
            display: block;
            color: #4f32c2;
        }
        .stats-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
        }
        .table-container {
            width: 100%;
        }
        table.results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.results-table th {
            background-color: #4f32c2;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #3d26a1;
        }
        table.results-table td {
            padding: 7px 5px;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }
        table.results-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .status-badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
            background-color: #dc3545;
            text-transform: uppercase;
            font-size: 8px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
        .page-number:after {
            content: counter(page);
        }
        .text-danger { color: #dc3545; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" style="width: 50px;">
                </td>
                <td class="title-container">
                    <h1>Reporte de Estudiantes Inhabilitados</h1>
                    <p>CEPRE-UNAMAD | {{ $ciclo->nombre }}</p>
                </td>
                <td class="logo" style="text-align: right;">
                    <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" style="width: 50px;">
                </td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <table class="info-grid">
            <tr>
                <td class="label">Ciclo Académico:</td>
                <td>{{ $ciclo->nombre }} ({{ $ciclo->codigo }})</td>
                <td class="label">Fecha de Generación:</td>
                <td>{{ $fecha_generacion }}</td>
            </tr>
            <tr>
                <td class="label">Periodo de Cálculo:</td>
                <td class="font-bold">{{ $periodo_label }}</td>
                <td class="label">Total Estudiantes:</td>
                <td>{{ $total_general }}</td>
            </tr>
            <tr>
                <td class="label">Porcentaje Inhabilitados:</td>
                <td class="font-bold text-danger">{{ $resumen['porcentaje_inhabilitados'] }}%</td>
                <td class="label"></td>
                <td></td>
            </tr>
        </table>
    </div>

    <table class="stats-table" style="margin-bottom: 20px;">
        <tr>
            <td style="padding-right: 10px;">
                <div class="stats-box">
                    <span class="stats-value">{{ $total_regulares }}</span>
                    <span class="stats-label">Regulares</span>
                </div>
            </td>
            <td style="padding-right: 10px;">
                <div class="stats-box" style="border-color: #ffc107;">
                    <span class="stats-value" style="color: #ffc107;">{{ $total_amonestados }}</span>
                    <span class="stats-label">Amonestados</span>
                </div>
            </td>
            <td>
                <div class="stats-box" style="border-color: #dc3545;">
                    <span class="stats-value" style="color: #dc3545;">{{ $total_inhabilitados }}</span>
                    <span class="stats-label">Inhabilitados</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-container">
        <h2 style="font-size: 14px; color: #4f32c2; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Relación Detallada de Inhabilitados</h2>
        <table class="results-table">
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">N°</th>
                    <th>Estudiante</th>
                    <th style="width: 60px;" class="text-center">DNI</th>
                    <th>Carrera / Aula</th>
                    <th style="width: 40px;" class="text-center">Faltas</th>
                    <th style="width: 40px;" class="text-center">Límite</th>
                    <th style="width: 50px;" class="text-center">% Inasist.</th>
                    <th style="width: 60px;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inhabilitados as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $item['nombres'] }}</td>
                        <td class="text-center">{{ $item['dni'] }}</td>
                        <td>
                            {{ $item['carrera'] }}<br>
                            <small style="color: #666;">Aula: {{ $item['aula'] }} | Turno: {{ $item['turno'] }}</small>
                        </td>
                        <td class="text-center text-danger font-bold">{{ $item['faltas'] }}</td>
                        <td class="text-center">{{ $item['limite'] }}</td>
                        <td class="text-center font-bold">{{ 100 - $item['porcentaje'] }}%</td>
                        <td class="text-center">
                            <span class="status-badge">Inhabilitado</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px;">No se encontraron estudiantes inhabilitados en este ciclo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 40px; font-size: 10px; color: #555;">
        <p><strong>Nota Informativa:</strong> Este reporte ha sido generado automáticamente por el Sistema de Asistencia CEPRE-UNAMAD. El estado de "Inhabilitado" se otorga a los estudiantes que han superado el {{ $ciclo->porcentaje_inhabilitacion }}% de inasistencias permitidas según el reglamento vigente.</p>
    </div>

    <div class="footer">
        Sistema de Asistencia CEPRE-UNAMAD | Generado por: {{ Auth::user()->nombre_completo }} | Página <span class="page-number"></span> de <span class="page-number"></span>
    </div>
</body>
</html>
