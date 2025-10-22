<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia - {{ $fecha_reporte_formato }}</title>
    <style>
        /* Paleta: #007BFF (Azul Corporativo), #2c3e50 (Gris Oscuro), #f4f6f9 (Gris Claro Fondo) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif; /* Fuente más moderna */
            font-size: 10.5px;
            line-height: 1.5;
            color: #2c3e50;
        }

        .container {
            padding: 30px 25px; /* Más espacio */
        }

        /* --- ENCABEZADO --- */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 4px solid #007BFF; /* Línea de color corporativo */
        }

        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 800; /* Más peso */
            text-transform: uppercase;
        }

        .header h2 {
            color: #495057;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .header .date {
            color: #6c757d;
            font-size: 11px;
            margin-top: 5px;
        }

        /* --- SECCIÓN DE INFORMACIÓN (Mantenemos display: table para PDF) --- */
        .info-section {
            margin-bottom: 25px;
            padding: 15px 20px;
            background-color: #f4f6f9; /* Fondo más sutil */
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }

        .info-section h3 {
            color: #007BFF;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #dcdcdc;
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
            padding: 4px 10px;
            width: 50%;
            vertical-align: top;
        }

        .info-label {
            font-weight: 700;
            color: #5d6d7e;
            display: inline-block;
            min-width: 90px;
            font-size: 10px;
            text-transform: uppercase;
        }

        /* --- RESUMEN (KPIs) --- */
        .summary-box {
            margin-bottom: 25px;
            padding: 15px;
            background-color: #e9f5ff; /* Fondo azul claro para destacar */
            border-radius: 6px;
            text-align: center;
            border: 1px solid #007BFF;
        }
        
        .summary-box h3 {
            color: #2c3e50;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #cce5ff;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 5px;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 8px;
            border-right: 1px solid #cce5ff;
        }
        
        .summary-item:last-child {
            border-right: none;
        }

        .summary-value {
            font-size: 26px; /* Más grande */
            font-weight: 900;
            color: #2c3e50;
            line-height: 1;
        }

        .summary-label {
            font-size: 9px;
            color: #6c757d;
            margin-top: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .summary-percentage {
            font-size: 12px;
            font-weight: 700;
            margin-top: 5px;
        }

        /* --- TABLA DE DETALLE --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th {
            background-color: #34495e; /* Gris Azulado Oscuro */
            color: white;
            padding: 9px 8px;
            text-align: left;
            border: 1px solid #2c3e50;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
        }

        table td {
            padding: 6px 8px;
            border-left: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 10px;
            vertical-align: top;
        }

        /* Zebra Striping */
        table tbody tr:nth-child(even) {
            background-color: #f7f9fb;
        }

        .asistio {
            color: #28a745;
            font-weight: 700;
        }

        .falto {
            color: #dc3545;
            font-weight: 700;
        }

        .sin-registro {
            color: #9c7800; /* Más amarillento para 'sin registro' */
            font-style: italic;
        }

        tr.falta-row {
            background-color: #fdeaea !important; /* Rojo sutil para filas con falta */
        }
        
        tr.falta-row td {
            color: #721c24;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 12px; /* Más redondeado */
            color: white;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        /* --- PIE DE PÁGINA Y EXAMEN --- */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dcdcdc;
            text-align: center;
            font-size: 9.5px;
            color: #6c757d;
            line-height: 1.6;
        }

        .page-break {
            page-break-after: always;
        }

        @if ($es_examen)
            .exam-header {
                background-color: #fff8e1; /* Amarillo sutil */
                padding: 12px;
                margin-bottom: 20px;
                border: 1px solid #ffecb3;
                border-left: 5px solid #ffc107; /* Banda amarilla */
                border-radius: 4px;
                text-align: center;
            }

            .exam-header h3 {
                color: #856404;
                font-size: 13px;
                margin: 0;
                font-weight: 700;
            }
            .exam-header p {
                font-size: 10px;
                margin-top: 3px;
                color: #856404;
            }
        @endif
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>REPORTE DE ASISTENCIA DIARIA</h1>
            <h2>CICLO: {{ $ciclo->nombre }} &mdash; FECHA: {{ $dia_semana }} {{ $fecha_reporte_formato }}</h2>
            
            @if ($es_examen)
                <div class="exam-header" style="margin-top: 15px;">
                    <h3>🚨 DÍA DE EXAMEN - {{ $numero_examen == 1 ? 'PRIMER' : ($numero_examen == 2 ? 'SEGUNDO' : 'TERCER') }} EXAMEN 🚨</h3>
                </div>
            @endif
            
            <p class="date">Generado el: {{ $fecha_generacion }}</p>
        </div>

        @if ($es_examen && $tipo_reporte === 'resumen_examen')
            <div class="exam-header" style="border-left: 5px solid #007BFF; background-color: #e9f5ff; border-color: #cce5ff;">
                <h3 style="color: #004d4d;">REPORTE ESPECIAL DE ELEGIBILIDAD PARA EXAMEN</h3>
                <p style="color: #004d4d;">Este reporte incluye información sobre la elegibilidad de los estudiantes para rendir el examen, basada en la asistencia acumulada.</p>
            </div>
        @endif

        <div class="info-section">
            <h3>INFORMACIÓN DEL REPORTE Y FILTROS</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Fecha:</span>
                        {{ $dia_semana }} {{ $fecha_reporte_formato }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipo:</span>
                        @if ($tipo_reporte === 'asistencia_dia')
                            Asistencia del Día (Completo)
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
                            <div class="info-item" style="width: 100%;">
                                <span class="info-label">Aula:</span>
                                {{ $filtros['aula']->codigo }} - {{ $filtros['aula']->nombre }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="summary-box">
            <h3>RESUMEN DE ASISTENCIA DIARIA</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">{{ $total_estudiantes }}</div>
                    <div class="summary-label">TOTAL INSCRITOS</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value" style="color: #28a745;">{{ $total_asistencias }}</div>
                    <div class="summary-label">ASISTENCIAS</div>
                    <div class="summary-percentage" style="color: #28a745;">{{ $porcentaje_asistencia }}%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value" style="color: #dc3545;">{{ $total_faltas }}</div>
                    <div class="summary-label">FALTAS</div>
                    <div class="summary-percentage" style="color: #dc3545;">{{ $porcentaje_faltas }}%</div>
                </div>
            </div>
        </div>

        <h3 style="margin-bottom: 15px; font-size: 15px; font-weight: 700; color: #2c3e50; border-bottom: 2px solid #e0e0e0; padding-bottom: 5px;">
            @if ($tipo_reporte === 'faltas_dia')
                DETALLE: ESTUDIANTES QUE FALTARON ({{ count($estudiantes) }})
            @else
                DETALLE COMPLETO DE ESTUDIANTES ({{ count($estudiantes) }})
            @endif
        </h3>

        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 10%;">CÓDIGO</th>
                    <th style="width: 25%;">ESTUDIANTE</th>
                    <th style="width: 10%;">DOCUMENTO</th>
                    <th style="width: 13%;">CARRERA</th>
                    <th style="width: 7%;">TURNO</th>
                    <th style="width: 7%;">AULA</th>
                    @if ($tipo_reporte !== 'faltas_dia')
                        <th style="width: 8%;">ENTRADA</th>
                        <th style="width: 8%;">SALIDA</th>
                        <th style="width: 7%;">ESTADO</th>
                    @endif
                    
                    {{-- Condición modificada para mostrar el porcentaje acumulado en Reporte Detallado o Resumen de Examen --}}
                    @if ($es_examen || $tipo_reporte === 'resumen_examen' || $tipo_reporte === 'asistencia_dia')
                        <th style="width: 7%;">% ASIST. ACUM.</th>
                        <th style="width: 7%;">% FALTA ACUM.</th>
                    @endif

                    {{-- 'Puede Rendir' es solo para el Resumen de Examen --}}
                    @if ($es_examen && $tipo_reporte === 'resumen_examen')
                        <th style="width: 7%;">PUEDE RENDIR</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($estudiantes as $index => $item)
                    <tr class="{{ !$item['asistio'] ? 'falta-row' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td style="font-weight: 600;">{{ $item['inscripcion']->codigo_inscripcion }}</td>
                        <td>
                            {{ $item['estudiante']->apellido_paterno }}
                            {{ $item['estudiante']->apellido_materno }},
                            <span style="font-weight: 600;">{{ $item['estudiante']->nombre }}</span>
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
                        
                        {{-- Celdas del Porcentaje Acumulado (CORREGIDAS con ?? '-') --}}
                        @if ($es_examen || $tipo_reporte === 'resumen_examen' || $tipo_reporte === 'asistencia_dia')
                            <td>{{ $item['porcentaje_asistencia'] ?? '-' }}%</td>
                            <td style="color: #dc3545; font-weight: 600;">
                                {{ $item['porcentaje_falta'] ?? '-' }}%
                            </td>
                        @endif

                        {{-- Celda de 'Puede Rendir' --}}
                        @if ($es_examen && $tipo_reporte === 'resumen_examen')
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
                @if (count($estudiantes) === 0)
                    <tr>
                        <td colspan="{{ 7 + ($tipo_reporte !== 'faltas_dia' ? 3 : 0) + ($es_examen && $tipo_reporte === 'resumen_examen' ? 3 : 0) }}" style="text-align: center; font-style: italic; color: #95a5a6; padding: 20px;">
                            No se encontraron registros de estudiantes para los filtros seleccionados.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            <p><strong>Información de Políticas y Generación:</strong></p>
            <p>Este reporte refleja la asistencia registrada para el día **{{ $fecha_reporte_formato }}**.</p>
            @if ($es_examen)
                <p>Los estudiantes que no pueden rendir el examen han superado el límite de
                    **{{ $ciclo->porcentaje_inhabilitacion }}%** de inasistencias acumuladas.</p>
            @endif
            <p style="margin-top: 5px;">Documento generado automáticamente por el sistema de gestión académica.</p>
        </div>
    </div>
</body>

</html>
