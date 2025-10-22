<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia Individual</title>
    <style>
        /* BASE Y FUENTES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1a1a1a; /* Negro para texto principal */
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .container {
            max-width: 950px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border: 1px solid #e0e0e0; /* Borde general para estructura */
        }

        /* ENCABEZADO */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 4px solid #2c3e50; /* Gris Azulado Oscuro */
        }

        .header h1 {
            color: #2c3e50;
            font-size: 30px;
            margin-bottom: 4px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header h2 {
            color: #333333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .header .date {
            color: #666666;
            font-size: 12px;
            margin-top: 8px;
        }

        /* INFORMACIÓN DEL ESTUDIANTE */
        .info-section {
            margin-bottom: 30px;
            background-color: #f4f6f9; /* Fondo muy claro */
            padding: 20px;
            border-radius: 0;
            border-top: 2px solid #34495e; /* Gris Azulado Secundario */
            border-bottom: 2px solid #e0e0e0;
        }

        .info-section h3 {
            color: #34495e;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: 700;
            padding-bottom: 5px;
            border-bottom: 1px solid #cccccc;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
        }

        .info-label {
            font-weight: 600;
            color: #4a4a4a;
            margin-right: 8px;
            min-width: 140px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .info-value {
            color: #1a1a1a;
            flex: 1;
            font-size: 12px;
            font-weight: 500;
        }
        
        /* --- TARJETAS DE EXAMEN (Ahora son BLOQUES de DATA) --- */
        .exam-section h3 {
            color: #1a1a1a;
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 700;
            border-bottom: 1px solid #dcdcdc; 
            padding-bottom: 5px;
        }

        .exam-card {
            border-radius: 0;
            margin-bottom: 25px; /* Mayor separación entre bloques */
            overflow: hidden;
            border: 1px solid #dcdcdc; 
        }

        /* Encabezados de Bloque */
        .exam-header {
            padding: 10px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            background-color: #34495e; /* Gris Azulado Secundario */
            color: white;
        }
        
        .exam-header h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
        }

        /* Estilos condicionales para encabezado */
        .exam-header.success { background-color: #e6ffe6; color: #155724; border-bottom: 2px solid #28a745; }
        .exam-header.warning { background-color: #fff8e1; color: #856404; border-bottom: 2px solid #ffc107; }
        .exam-header.danger { background-color: #ffe8e8; color: #721c24; border-bottom: 2px solid #dc3545; }

        .exam-body {
            padding: 0; /* El padding se maneja dentro de la tabla KPI */
            background-color: white;
        }

        /* Tabla para KPIs (Diseño de Auditoría/Reporte Ejecutivo) */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kpi-table th {
            background-color: #f4f6f9; /* Fondo muy claro */
            color: #4a4a4a;
            padding: 8px 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-table td {
            padding: 10px;
            border: 1px solid #e0e0e0;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: 800;
        }

        /* Barra de Progreso */
        .progress-bar-container {
            padding: 0 18px 18px 18px;
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
        }
        .progress-bar {
            width: 100%;
            height: 15px; /* Aún más delgado */
            background-color: #e9ecef;
            border-radius: 10px; 
            overflow: hidden;
            margin: 10px 0;
            border: 1px solid #cccccc;
        }

        .progress-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 9px;
        }

        .progress-fill.success { background-color: #28a745; }
        .progress-fill.warning { background-color: #ffc107; }
        .progress-fill.danger { background-color: #dc3545; }

        /* Alerta Final */
        .alert {
            padding: 12px 18px;
            margin: 0; 
            border-radius: 0;
            border: none;
            font-size: 14px;
            font-weight: 700;
        }

        .alert.success { background-color: #e8f5e9; color: #155724; }
        .alert.danger { background-color: #ffe8e8; color: #721c24; }
        
        /* Contador de Mes (Detalle) */
        .detalle-mes-header {
            margin-bottom: 5px;
            padding: 8px 15px;
            background-color: #f7f7f7;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .detalle-mes-header h3 {
            margin: 0;
            font-size: 14px;
            color: #004d4d;
            font-weight: 700;
            border-bottom: none;
            padding-bottom: 0;
        }
        .contador-asistencias {
            display: flex;
            gap: 10px;
            font-size: 11px;
        }

        .contador-item {
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            border: 1px solid;
            background-color: #ffffff;
        }

        .contador-asistidos { color: #28a745; border-color: #28a745; }
        .contador-faltas { color: #dc3545; border-color: #dc3545; }
        
        /* TABLA DE DETALLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        table th {
            background-color: #34495e; /* Gris Azulado Secundario */
            color: white;
            padding: 7px 6px;
            text-align: center;
            border: 1px solid #2c3e50;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
        }

        table td {
            padding: 6px 8px; 
            border: 1px solid #e0e0e0;
            font-size: 11px;
            text-align: center;
            vertical-align: middle;
        }
        
        /* Zebra Striping */
        table tbody tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        
        tr.falta-row {
            background-color: #ffe8e8 !important; /* Asegurar que la falta se vea */
        }

        tr.falta-row td {
            color: #721c24;
        }

        .falta-text {
            color: #dc3545;
            font-weight: 600;
        }

        .sin-registro {
            color: #9c7800;
            font-style: italic;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 10px;
            color: #666666;
            line-height: 1.6;
        }

        .page-break {
            page-break-after: always;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 4px;
            background-color: #666666;
            color: white;
            text-transform: uppercase;
        }

        .dias-info {
            display: inline-block;
            font-size: 10px;
            color: white; /* Blanco para alto contraste en encabezado oscuro */
            font-weight: 600;
            background-color: transparent;
            border: 1px solid white;
            padding: 3px 6px;
            border-radius: 3px;
        }

        /* Estilos de impresión */
        @media print {
            .container {
                padding: 15px;
                border: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>REPORTE DE ASISTENCIA INDIVIDUAL</h1>
            <h2>CICLO: {{ $ciclo->nombre }}</h2>
            <p class="date">Generado el: {{ $fecha_generacion }}</p>
        </div>
        

        <!-- SECCIÓN DE INFORMACIÓN DEL ESTUDIANTE -->
        <div class="info-section">
            <h3>Datos del Estudiante</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Código de Inscripción:</span>
                    <span class="info-value">{{ $inscripcion->codigo_inscripcion }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Documento:</span>
                    <span class="info-value">{{ $estudiante->numero_documento }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nombre Completo:</span>
                    <span class="info-value" style="font-weight: 700;">{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }}
                        {{ $estudiante->apellido_materno }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Carrera:</span>
                    <span class="info-value">{{ $carrera->nombre }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Turno:</span>
                    <span class="info-value">{{ $turno->nombre }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Aula:</span>
                    <span class="info-value">{{ $aula->codigo }} - {{ $aula->nombre }}</span>
                </div>
            </div>
        </div>

        @if (isset($info_asistencia) && !empty($info_asistencia))

            <!-- SECCIÓN DE INFORMACIÓN POR EXAMEN (ACUMULADO) -->
            <div class="exam-section">
                <h3>Resumen de Asistencia Acumulada por Examen</h3>
                
                <!-- PRIMER EXAMEN -->
                @if (isset($info_asistencia['primer_examen']))
                    @php
                        $condicion = $info_asistencia['primer_examen']['condicion'];
                        $class = $condicion == 'Regular' ? 'success' : ($condicion == 'Amonestado' ? 'warning' : 'danger');
                        $puedeRendir = $info_asistencia['primer_examen']['puede_rendir'] == 'SÍ';
                    @endphp
                    <div class="exam-card">
                        <div class="exam-header {{ $class }}">
                            <h4>PRIMER EXAMEN - FECHA:
                                {{ \Carbon\Carbon::parse($ciclo->fecha_primer_examen)->format('d/m/Y') }}</h4>
                            <div>
                                @if ($info_asistencia['primer_examen']['es_proyeccion'] ?? false)
                                    <span class="badge">PROYECCIÓN</span>
                                @endif
                                <span class="dias-info">
                                    A/F/T:
                                    {{ $info_asistencia['primer_examen']['dias_asistidos'] }}/{{ $info_asistencia['primer_examen']['dias_falta'] }}/{{ $info_asistencia['primer_examen']['dias_habiles'] }}
                                </span>
                            </div>
                        </div>

                        <div class="exam-body">
                            <table class="kpi-table">
                                <thead>
                                    <tr>
                                        <th>Asistencia</th>
                                        <th>Faltas</th>
                                        <th>Días (Asist. / Falta)</th>
                                        <th>Estado</th>
                                        <th>Puede Rendir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="kpi-value" style="color: #28a745;">
                                                {{ $info_asistencia['primer_examen']['porcentaje_asistencia'] }}%</div>
                                        </td>
                                        <td>
                                            <div class="kpi-value" style="color: #dc3545;">
                                                {{ $info_asistencia['primer_examen']['porcentaje_falta'] }}%</div>
                                        </td>
                                        <td>
                                            {{ $info_asistencia['primer_examen']['dias_asistidos'] }} / 
                                            {{ $info_asistencia['primer_examen']['dias_falta'] }}
                                        </td>
                                        <td>
                                            <div class="kpi-value kpi-value-small" style="font-weight: 700; color: {{ $class == 'success' ? '#28a745' : ($class == 'warning' ? '#ffc107' : '#dc3545') }};">
                                                {{ $info_asistencia['primer_examen']['condicion'] }}</div>
                                        </td>
                                        <td style="font-weight: 700; color: {{ $puedeRendir ? '#28a745' : '#dc3545' }};">
                                            {{ $info_asistencia['primer_examen']['puede_rendir'] }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-fill {{ $class }}"
                                    style="width: {{ $info_asistencia['primer_examen']['porcentaje_asistencia'] }}%">
                                    {{ $info_asistencia['primer_examen']['porcentaje_asistencia'] }}% ASISTENCIA
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert {{ $puedeRendir ? 'success' : 'danger' }}">
                            <strong>{{ $puedeRendir ? 'PUEDE RENDIR EL EXAMEN' : 'NO PUEDE RENDIR EL EXAMEN' }}</strong>
                        </div>
                    </div>
                @endif
                
                <!-- SEGUNDO EXAMEN -->
                @if (isset($info_asistencia['segundo_examen']) && $info_asistencia['segundo_examen']['condicion'] != 'Pendiente')
                    @php
                        $condicion = $info_asistencia['segundo_examen']['condicion'];
                        $class = $condicion == 'Regular' ? 'success' : ($condicion == 'Amonestado' ? 'warning' : 'danger');
                        $puedeRendir = $info_asistencia['segundo_examen']['puede_rendir'] == 'SÍ';
                    @endphp
                    <div class="exam-card">
                        <div class="exam-header {{ $class }}">
                            <h4>SEGUNDO EXAMEN - FECHA:
                                {{ \Carbon\Carbon::parse($ciclo->fecha_segundo_examen)->format('d/m/Y') }}</h4>
                            <div>
                                @if ($info_asistencia['segundo_examen']['es_proyeccion'] ?? false)
                                    <span class="badge">PROYECCIÓN</span>
                                @endif
                                <span class="dias-info">
                                    A/F/T:
                                    {{ $info_asistencia['segundo_examen']['dias_asistidos'] }}/{{ $info_asistencia['segundo_examen']['dias_falta'] }}/{{ $info_asistencia['segundo_examen']['dias_habiles'] }}
                                </span>
                            </div>
                        </div>

                        <div class="exam-body">
                            <table class="kpi-table">
                                <thead>
                                    <tr>
                                        <th>Asistencia</th>
                                        <th>Faltas</th>
                                        <th>Días (Asist. / Falta)</th>
                                        <th>Estado</th>
                                        <th>Puede Rendir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="kpi-value" style="color: #28a745;">
                                                {{ $info_asistencia['segundo_examen']['porcentaje_asistencia'] }}%</div>
                                        </td>
                                        <td>
                                            <div class="kpi-value" style="color: #dc3545;">
                                                {{ $info_asistencia['segundo_examen']['porcentaje_falta'] }}%</div>
                                        </td>
                                        <td>
                                            {{ $info_asistencia['segundo_examen']['dias_asistidos'] }} / 
                                            {{ $info_asistencia['segundo_examen']['dias_falta'] }}
                                        </td>
                                        <td>
                                            <div class="kpi-value kpi-value-small" style="font-weight: 700; color: {{ $class == 'success' ? '#28a745' : ($class == 'warning' ? '#ffc107' : '#dc3545') }};">
                                                {{ $info_asistencia['segundo_examen']['condicion'] }}</div>
                                        </td>
                                        <td style="font-weight: 700; color: {{ $puedeRendir ? '#28a745' : '#dc3545' }};">
                                            {{ $info_asistencia['segundo_examen']['puede_rendir'] }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-fill {{ $class }}"
                                    style="width: {{ $info_asistencia['segundo_examen']['porcentaje_asistencia'] }}%">
                                    {{ $info_asistencia['segundo_examen']['porcentaje_asistencia'] }}% ASISTENCIA
                                </div>
                            </div>
                        </div>

                        <div class="alert {{ $puedeRendir ? 'success' : 'danger' }}">
                            <strong>{{ $puedeRendir ? 'PUEDE RENDIR EL EXAMEN' : 'NO PUEDE RENDIR EL EXAMEN' }}</strong>
                        </div>
                    </div>
                @endif

                <!-- TERCER EXAMEN -->
                @if (isset($info_asistencia['tercer_examen']) && $info_asistencia['tercer_examen']['condicion'] != 'Pendiente')
                    @php
                        $condicion = $info_asistencia['tercer_examen']['condicion'];
                        $class = $condicion == 'Regular' ? 'success' : ($condicion == 'Amonestado' ? 'warning' : 'danger');
                        $puedeRendir = $info_asistencia['tercer_examen']['puede_rendir'] == 'SÍ';
                    @endphp
                    <div class="exam-card">
                        <div class="exam-header {{ $class }}">
                            <h4>TERCER EXAMEN - FECHA:
                                {{ \Carbon\Carbon::parse($ciclo->fecha_tercer_examen)->format('d/m/Y') }}</h4>
                            <div>
                                @if ($info_asistencia['tercer_examen']['es_proyeccion'] ?? false)
                                    <span class="badge">PROYECCIÓN</span>
                                @endif
                                <span class="dias-info">
                                    A/F/T:
                                    {{ $info_asistencia['tercer_examen']['dias_asistidos'] }}/{{ $info_asistencia['tercer_examen']['dias_falta'] }}/{{ $info_asistencia['tercer_examen']['dias_habiles'] }}
                                </span>
                            </div>
                        </div>

                        <div class="exam-body">
                            <table class="kpi-table">
                                <thead>
                                    <tr>
                                        <th>Asistencia</th>
                                        <th>Faltas</th>
                                        <th>Días (Asist. / Falta)</th>
                                        <th>Estado</th>
                                        <th>Puede Rendir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="kpi-value" style="color: #28a745;">
                                                {{ $info_asistencia['tercer_examen']['porcentaje_asistencia'] }}%</div>
                                        </td>
                                        <td>
                                            <div class="kpi-value" style="color: #dc3545;">
                                                {{ $info_asistencia['tercer_examen']['porcentaje_falta'] }}%</div>
                                        </td>
                                        <td>
                                            {{ $info_asistencia['tercer_examen']['dias_asistidos'] }} / 
                                            {{ $info_asistencia['tercer_examen']['dias_falta'] }}
                                        </td>
                                        <td>
                                            <div class="kpi-value kpi-value-small" style="font-weight: 700; color: {{ $class == 'success' ? '#28a745' : ($class == 'warning' ? '#ffc107' : '#dc3545') }};">
                                                {{ $info_asistencia['tercer_examen']['condicion'] }}</div>
                                        </td>
                                        <td style="font-weight: 700; color: {{ $puedeRendir ? '#28a745' : '#dc3545' }};">
                                            {{ $info_asistencia['tercer_examen']['puede_rendir'] }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-fill {{ $class }}"
                                    style="width: {{ $info_asistencia['tercer_examen']['porcentaje_asistencia'] }}%">
                                    {{ $info_asistencia['tercer_examen']['porcentaje_asistencia'] }}% ASISTENCIA
                                </div>
                            </div>
                        </div>

                        <div class="alert {{ $puedeRendir ? 'success' : 'danger' }}">
                            <strong>{{ $puedeRendir ? 'PUEDE RENDIR EL EXAMEN' : 'NO PUEDE RENDIR EL EXAMEN' }}</strong>
                        </div>
                    </div>
                @endif
            </div>

            <!-- DETALLE DE ASISTENCIAS POR MES -->
            @if (isset($detalle_asistencias) && count($detalle_asistencias) > 0)
                <div class="page-break"></div>

                <div class="header" style="border-bottom: none; margin-bottom: 25px; padding-bottom: 0;">
                    <h2 style="font-weight: 700; color: #34495e; border-bottom: 1px dashed #dee2e6; padding-bottom: 10px;">DETALLE DE ASISTENCIAS POR MES</h2>
                </div>

                @foreach ($detalle_asistencias as $mesKey => $mes)
                    <div class="info-section" style="margin-bottom: 25px; background-color: #ffffff; border: 1px solid #e0e0e0; padding: 0;">
                        <div class="detalle-mes-header">
                            <h3>{{ $mes['mes'] }} {{ $mes['anio'] }}</h3>
                            <div class="contador-asistencias">
                                <span class="contador-item contador-asistidos">
                                    Asistidos: {{ $mes['dias_asistidos'] }}
                                </span>
                                <span class="contador-item contador-faltas">
                                    Faltas: {{ $mes['dias_falta'] }}
                                </span>
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 20%;">Fecha</th>
                                    <th style="width: 20%;">Día</th>
                                    <th style="width: 20%;">Hora Entrada</th>
                                    <th style="width: 20%;">Hora Salida</th>
                                    <th style="width: 20%;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mes['registros'] as $registro)
                                    <tr class="{{ !$registro['asistio'] ? 'falta-row' : '' }}">
                                        <td>{{ $registro['fecha'] }}</td>
                                        <td>{{ $registro['dia_semana'] }}</td>
                                        <td
                                            class="{{ $registro['hora_entrada'] == 'Sin registro' ? 'sin-registro' : ($registro['hora_entrada'] == 'FALTA' ? 'falta-text' : '') }}">
                                            {{ $registro['hora_entrada'] }}
                                        </td>
                                        <td class="{{ $registro['hora_salida'] == 'FALTA' ? 'falta-text' : '' }}">
                                            {{ $registro['hora_salida'] }}
                                        </td>
                                        <td style="text-align: center;">
                                            @if ($registro['asistio'])
                                                <span style="color: #28a745; font-weight: 600;">Asistió</span>
                                            @else
                                                <span style="color: #dc3545; font-weight: 600;">Falta</span>
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
            <div class="alert warning" style="background-color: #fff8e1; color: #856404; border: 1px solid #ffc107; font-size: 14px;">
                <strong>Sin registros de asistencia</strong>
                <p style="font-weight: 400; margin-top: 5px;">El estudiante aún no tiene registros de asistencia en este ciclo.</p>
            </div>
        @endif

        <div class="footer">
            <p><strong>Información de Políticas de Asistencia:</strong></p>
            <p>Límite de amonestación: **{{ $ciclo->porcentaje_amonestacion }}%** de inasistencias. | Límite de inhabilitación: **{{ $ciclo->porcentaje_inhabilitacion }}%** de inasistencias.</p>
            <p>A/F/T = Días Asistidos / Días de Falta / Total de Días Hábiles en el periodo.</p>
            <p style="margin-top: 10px;">Documento generado automáticamente por el sistema de gestión académica.</p>
        </div>
    </div>
</body>

</html>
