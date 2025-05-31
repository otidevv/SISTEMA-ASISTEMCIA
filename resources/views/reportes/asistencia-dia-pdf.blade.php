<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia - {{ $fecha_reporte_formato }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #2c3e50;
        }

        .container {
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #34495e;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 22px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .header h2 {
            color: #495057;
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 5px;
        }

        .header .date {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
        }

        .info-section {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-item {
            display: table-cell;
            padding: 5px 10px;
            width: 50%;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            display: inline-block;
            min-width: 100px;
        }

        .summary-box {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 4px;
            text-align: center;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }

        .summary-label {
            font-size: 10px;
            color: #6c757d;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th {
            background-color: #f8f9fa;
            padding: 8px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 10px;
        }

        table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
        }

        .asistio {
            color: #28a745;
            font-weight: 600;
        }

        .falto {
            color: #dc3545;
            font-weight: 600;
        }

        .sin-registro {
            color: #dc3545;
            font-style: italic;
        }

        tr.falta-row {
            background-color: #ffe4e4;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: 600;
            border-radius: 3px;
            color: white;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }

        .page-break {
            page-break-after: always;
        }

        @if ($es_examen)
            .exam-header {
                background-color: #fff3cd;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ffeeba;
                border-radius: 4px;
                text-align: center;
            }

            .exam-header h3 {
                color: #856404;
                margin: 0;
            }
        @endif
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>REPORTE DE ASISTENCIA</h1>
            <h2>{{ $ciclo->nombre }} - {{ $dia_semana }} {{ $fecha_reporte_formato }}</h2>
            @if ($es_examen)
                <h3 style="color: #856404; margin-top: 10px;">
                    DÍA DE EXAMEN - {{ $numero_examen == 1 ? 'PRIMER' : ($numero_examen == 2 ? 'SEGUNDO' : 'TERCER') }}
                    EXAMEN
                </h3>
            @endif
            <p class="date">Generado el: {{ $fecha_generacion }}</p>
        </div>

        @if ($es_examen && $tipo_reporte === 'resumen_examen')
            <div class="exam-header">
                <h3>REPORTE ESPECIAL DE EXAMEN</h3>
                <p>Este reporte incluye información sobre la elegibilidad de los estudiantes para rendir el examen</p>
            </div>
        @endif

        <div class="info-section">
            <h3 style="margin-bottom: 10px;">Información del Reporte</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Fecha:</span>
                        {{ $dia_semana }} {{ $fecha_reporte_formato }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipo:</span>
                        @if ($tipo_reporte === 'asistencia_dia')
                            Asistencia del Día
                        @elseif($tipo_reporte === 'faltas_dia')
                            Solo Faltas del Día
                        @else
                            Resumen para Examen
                        @endif
                    </div>
                </div>
                @if ($filtros['carrera'] || $filtros['turno'] || $filtros['aula'])
                    <div class="info-row">
                        @if ($filtros['carrera'])
                            <div class="info-item">
                                <span class="info-label">Carrera:</span>
                                {{ $filtros['carrera']->nombre }}
                            </div>
                        @endif
                        @if ($filtros['turno'])
                            <div class="info-item">
                                <span class="info-label">Turno:</span>
                                {{ $filtros['turno']->nombre }}
                            </div>
                        @endif
                    </div>
                    @if ($filtros['aula'])
                        <div class="info-row">
                            <div class="info-item">
                                <span class="info-label">Aula:</span>
                                {{ $filtros['aula']->codigo }} - {{ $filtros['aula']->nombre }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="summary-box">
            <h3>Resumen de Asistencia</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">{{ $total_estudiantes }}</div>
                    <div class="summary-label">TOTAL ESTUDIANTES</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value" style="color: #28a745;">{{ $total_asistencias }}</div>
                    <div class="summary-label">ASISTENCIAS</div>
                    <div style="font-size: 14px; margin-top: 5px;">{{ $porcentaje_asistencia }}%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value" style="color: #dc3545;">{{ $total_faltas }}</div>
                    <div class="summary-label">FALTAS</div>
                    <div style="font-size: 14px; margin-top: 5px;">{{ $porcentaje_faltas }}%</div>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 10px;">
            @if ($tipo_reporte === 'faltas_dia')
                Estudiantes que Faltaron
            @else
                Detalle de Estudiantes
            @endif
        </h3>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 25%;">Estudiante</th>
                    <th style="width: 10%;">Documento</th>
                    <th style="width: 15%;">Carrera</th>
                    <th style="width: 8%;">Turno</th>
                    <th style="width: 8%;">Aula</th>
                    @if ($tipo_reporte !== 'faltas_dia')
                        <th style="width: 10%;">Entrada</th>
                        <th style="width: 10%;">Salida</th>
                        <th style="width: 9%;">Estado</th>
                    @endif
                    @if ($es_examen && $tipo_reporte === 'resumen_examen')
                        <th style="width: 10%;">% Asist.</th>
                        <th style="width: 10%;">Puede Rendir</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($estudiantes as $index => $item)
                    <tr class="{{ !$item['asistio'] ? 'falta-row' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['inscripcion']->codigo_inscripcion }}</td>
                        <td>
                            {{ $item['estudiante']->apellido_paterno }}
                            {{ $item['estudiante']->apellido_materno }},
                            {{ $item['estudiante']->nombre }}
                        </td>
                        <td>{{ $item['estudiante']->numero_documento }}</td>
                        <td>{{ $item['inscripcion']->carrera->codigo }}</td>
                        <td>{{ $item['inscripcion']->turno->nombre }}</td>
                        <td>{{ $item['inscripcion']->aula->codigo }}</td>
                        @if ($tipo_reporte !== 'faltas_dia')
                            <td class="{{ $item['hora_entrada'] === 'Sin registro' ? 'sin-registro' : '' }}">
                                {{ $item['hora_entrada'] ?? '-' }}
                            </td>
                            <td>{{ $item['hora_salida'] ?? '-' }}</td>
                            <td>
                                @if ($item['asistio'])
                                    <span class="asistio">Asistió</span>
                                @else
                                    <span class="falto">Faltó</span>
                                @endif
                            </td>
                        @endif
                        @if ($es_examen && $tipo_reporte === 'resumen_examen')
                            <td>{{ $item['porcentaje_asistencia'] }}%</td>
                            <td>
                                @if ($item['puede_rendir_examen'])
                                    <span class="badge badge-success">SÍ</span>
                                @else
                                    <span class="badge badge-danger">NO</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p><strong>Información Importante:</strong></p>
            <p>Este reporte refleja la asistencia registrada para el día {{ $fecha_reporte_formato }}</p>
            @if ($es_examen)
                <p>Los estudiantes que no pueden rendir el examen han superado el límite de
                    {{ $ciclo->porcentaje_inhabilitacion }}% de inasistencias</p>
            @endif
            <p>Documento generado automáticamente por el sistema de gestión académica</p>
        </div>
    </div>
</body>

</html>
