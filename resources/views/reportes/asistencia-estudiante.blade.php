<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Asistencia</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #2c3e50;
            margin: 20px;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 35px 45px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 2px solid #34495e;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 26px;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        .header h2 {
            color: #495057;
            font-size: 18px;
            font-weight: 400;
            margin-bottom: 8px;
        }

        .header .date {
            color: #6c757d;
            font-size: 13px;
            margin-top: 8px;
        }

        .info-section {
            margin-bottom: 25px;
            background-color: #f8f9fa;
            padding: 18px 22px;
            border-radius: 6px;
            border-left: 3px solid #3498db;
        }

        .info-section h3 {
            color: #2c3e50;
            font-size: 16px;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-item {
            display: flex;
            align-items: baseline;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            margin-right: 8px;
            min-width: 140px;
            font-size: 11px;
        }

        .info-value {
            color: #2c3e50;
            flex: 1;
            font-size: 12px;
        }

        .summary-section {
            margin-bottom: 30px;
        }

        .summary-section h3 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 18px;
            font-weight: 600;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            text-align: center;
            padding: 18px 8px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1.5px solid #e9ecef;
            transition: all 0.2s;
        }

        .summary-card h4 {
            font-size: 28px;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .summary-card p {
            font-size: 11px;
            color: #6c757d;
            font-weight: 500;
        }

        .summary-card.success {
            border-color: #28a745;
            background-color: #d4edda;
        }

        .summary-card.success h4 {
            color: #155724;
        }

        .summary-card.warning {
            border-color: #ffc107;
            background-color: #fff3cd;
        }

        .summary-card.warning h4 {
            color: #856404;
        }

        .summary-card.danger {
            border-color: #dc3545;
            background-color: #f8d7da;
        }

        .summary-card.danger h4 {
            color: #721c24;
        }

        .exam-section {
            margin-bottom: 30px;
        }

        .exam-section h3 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 18px;
            font-weight: 600;
        }

        .exam-card {
            border: 1.5px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 18px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .exam-card.success {
            border-color: #28a745;
        }

        .exam-card.warning {
            border-color: #ffc107;
        }

        .exam-card.danger {
            border-color: #dc3545;
        }

        .exam-header {
            background-color: #f8f9fa;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .exam-header h4 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
        }

        .exam-header.success {
            background-color: #d4edda;
            color: #155724;
        }

        .exam-header.warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .exam-header.danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .exam-body {
            padding: 18px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .stat-item {
            text-align: center;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .stat-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
        }

        .stat-detail {
            font-size: 11px;
            color: #6c757d;
            margin-top: 2px;
        }

        .progress-bar {
            width: 100%;
            height: 22px;
            background-color: #e9ecef;
            border-radius: 11px;
            overflow: hidden;
            margin: 16px 0;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 11px;
            transition: width 0.3s;
        }

        .progress-fill.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .progress-fill.warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        }

        .progress-fill.danger {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
        }

        .alert {
            padding: 12px 18px;
            border-radius: 4px;
            margin-top: 12px;
            font-weight: 600;
            text-align: center;
            font-size: 13px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert.danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table th {
            background-color: #f8f9fa;
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        table td {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            font-size: 11px;
            color: #2c3e50;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            line-height: 1.6;
        }

        .page-break {
            page-break-after: always;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: 600;
            border-radius: 3px;
            background-color: #6c757d;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .dias-info {
            display: inline-block;
            font-size: 12px;
            color: #495057;
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.05);
            padding: 3px 8px;
            border-radius: 3px;
            margin-left: 4px;
        }

        @media print {
            body {
                margin: 0;
                background-color: white;
            }

            .container {
                box-shadow: none;
                padding: 15px;
                max-width: 100%;
            }

            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>REPORTE DE ASISTENCIA</h1>
            <h2>{{ $ciclo->nombre }}</h2>
            <p class="date">Generado el: {{ $fecha_generacion }}</p>
        </div>

        <div class="info-section">
            <h3>Información del Estudiante</h3>
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
                    <span class="info-value">{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }}
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

            <div class="exam-section">
                <h3>Información por Examen</h3>

                @if (isset($info_asistencia['primer_examen']))
                    <div
                        class="exam-card {{ $info_asistencia['primer_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['primer_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}">
                        <div
                            class="exam-header {{ $info_asistencia['primer_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['primer_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}">
                            <h4>Primer Examen -
                                {{ \Carbon\Carbon::parse($ciclo->fecha_primer_examen)->format('d/m/Y') }}</h4>
                            <div>
                                @if ($info_asistencia['primer_examen']['es_proyeccion'] ?? false)
                                    <span class="badge">Proyección</span>
                                @endif
                                <span class="dias-info">
                                    A/F/T:
                                    {{ $info_asistencia['primer_examen']['dias_asistidos'] }}/{{ $info_asistencia['primer_examen']['dias_falta'] }}/{{ $info_asistencia['primer_examen']['dias_habiles'] }}
                                </span>
                            </div>
                        </div>

                        <div class="exam-body">
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-label">Asistencia</div>
                                    <div class="stat-value">
                                        {{ $info_asistencia['primer_examen']['porcentaje_asistencia'] }}%</div>
                                    <div class="stat-detail">{{ $info_asistencia['primer_examen']['dias_asistidos'] }}
                                        días</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Faltas</div>
                                    <div class="stat-value">
                                        {{ $info_asistencia['primer_examen']['porcentaje_falta'] }}%</div>
                                    <div class="stat-detail">{{ $info_asistencia['primer_examen']['dias_falta'] }} días
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Estado</div>
                                    <div class="stat-value" style="font-size: 16px;">
                                        {{ $info_asistencia['primer_examen']['condicion'] }}</div>
                                </div>
                            </div>

                            <div class="progress-bar">
                                <div class="progress-fill {{ $info_asistencia['primer_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['primer_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}"
                                    style="width: {{ $info_asistencia['primer_examen']['porcentaje_asistencia'] }}%">
                                    {{ $info_asistencia['primer_examen']['porcentaje_asistencia'] }}%
                                </div>
                            </div>

                            <div
                                class="alert {{ $info_asistencia['primer_examen']['puede_rendir'] == 'SÍ' ? 'success' : 'danger' }}">
                                <strong>{{ $info_asistencia['primer_examen']['puede_rendir'] == 'SÍ' ? 'PUEDE RENDIR EL EXAMEN' : 'NO PUEDE RENDIR EL EXAMEN' }}</strong>
                            </div>
                        </div>
                    </div>
                @endif

                @if (isset($info_asistencia['segundo_examen']) && $info_asistencia['segundo_examen']['condicion'] != 'Pendiente')
                    <div
                        class="exam-card {{ $info_asistencia['segundo_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['segundo_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}">
                        <div
                            class="exam-header {{ $info_asistencia['segundo_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['segundo_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}">
                            <h4>Segundo Examen -
                                {{ \Carbon\Carbon::parse($ciclo->fecha_segundo_examen)->format('d/m/Y') }}</h4>
                            <div>
                                @if ($info_asistencia['segundo_examen']['es_proyeccion'] ?? false)
                                    <span class="badge">Proyección</span>
                                @endif
                                <span class="dias-info">
                                    A/F/T:
                                    {{ $info_asistencia['segundo_examen']['dias_asistidos'] }}/{{ $info_asistencia['segundo_examen']['dias_falta'] }}/{{ $info_asistencia['segundo_examen']['dias_habiles'] }}
                                </span>
                            </div>
                        </div>

                        <div class="exam-body">
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-label">Asistencia</div>
                                    <div class="stat-value">
                                        {{ $info_asistencia['segundo_examen']['porcentaje_asistencia'] }}%</div>
                                    <div class="stat-detail">{{ $info_asistencia['segundo_examen']['dias_asistidos'] }}
                                        días</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Faltas</div>
                                    <div class="stat-value">
                                        {{ $info_asistencia['segundo_examen']['porcentaje_falta'] }}%</div>
                                    <div class="stat-detail">{{ $info_asistencia['segundo_examen']['dias_falta'] }}
                                        días</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Estado</div>
                                    <div class="stat-value" style="font-size: 16px;">
                                        {{ $info_asistencia['segundo_examen']['condicion'] }}</div>
                                </div>
                            </div>

                            <div class="progress-bar">
                                <div class="progress-fill {{ $info_asistencia['segundo_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['segundo_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}"
                                    style="width: {{ $info_asistencia['segundo_examen']['porcentaje_asistencia'] }}%">
                                    {{ $info_asistencia['segundo_examen']['porcentaje_asistencia'] }}%
                                </div>
                            </div>

                            <div
                                class="alert {{ $info_asistencia['segundo_examen']['puede_rendir'] == 'SÍ' ? 'success' : 'danger' }}">
                                <strong>{{ $info_asistencia['segundo_examen']['puede_rendir'] == 'SÍ' ? 'PUEDE RENDIR EL EXAMEN' : 'NO PUEDE RENDIR EL EXAMEN' }}</strong>
                            </div>
                        </div>
                    </div>
                @endif

                @if (isset($info_asistencia['tercer_examen']) && $info_asistencia['tercer_examen']['condicion'] != 'Pendiente')
                    <div
                        class="exam-card {{ $info_asistencia['tercer_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['tercer_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}">
                        <div
                            class="exam-header {{ $info_asistencia['tercer_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['tercer_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}">
                            <h4>Tercer Examen -
                                {{ \Carbon\Carbon::parse($ciclo->fecha_tercer_examen)->format('d/m/Y') }}</h4>
                            <div>
                                @if ($info_asistencia['tercer_examen']['es_proyeccion'] ?? false)
                                    <span class="badge">Proyección</span>
                                @endif
                                <span class="dias-info">
                                    A/F/T:
                                    {{ $info_asistencia['tercer_examen']['dias_asistidos'] }}/{{ $info_asistencia['tercer_examen']['dias_falta'] }}/{{ $info_asistencia['tercer_examen']['dias_habiles'] }}
                                </span>
                            </div>
                        </div>

                        <div class="exam-body">
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-label">Asistencia</div>
                                    <div class="stat-value">
                                        {{ $info_asistencia['tercer_examen']['porcentaje_asistencia'] }}%</div>
                                    <div class="stat-detail">{{ $info_asistencia['tercer_examen']['dias_asistidos'] }}
                                        días</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Faltas</div>
                                    <div class="stat-value">
                                        {{ $info_asistencia['tercer_examen']['porcentaje_falta'] }}%</div>
                                    <div class="stat-detail">{{ $info_asistencia['tercer_examen']['dias_falta'] }} días
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Estado</div>
                                    <div class="stat-value" style="font-size: 16px;">
                                        {{ $info_asistencia['tercer_examen']['condicion'] }}</div>
                                </div>
                            </div>

                            <div class="progress-bar">
                                <div class="progress-fill {{ $info_asistencia['tercer_examen']['condicion'] == 'Regular' ? 'success' : ($info_asistencia['tercer_examen']['condicion'] == 'Amonestado' ? 'warning' : 'danger') }}"
                                    style="width: {{ $info_asistencia['tercer_examen']['porcentaje_asistencia'] }}%">
                                    {{ $info_asistencia['tercer_examen']['porcentaje_asistencia'] }}%
                                </div>
                            </div>

                            <div
                                class="alert {{ $info_asistencia['tercer_examen']['puede_rendir'] == 'SÍ' ? 'success' : 'danger' }}">
                                <strong>{{ $info_asistencia['tercer_examen']['puede_rendir'] == 'SÍ' ? 'PUEDE RENDIR EL EXAMEN' : 'NO PUEDE RENDIR EL EXAMEN' }}</strong>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if (isset($detalle_asistencias) && count($detalle_asistencias) > 0)
                <div class="page-break"></div>

                <div class="header">
                    <h2>Detalle de Asistencias por Mes</h2>
                </div>

                @foreach ($detalle_asistencias as $mesKey => $mes)
                    <div class="info-section" style="margin-bottom: 20px;">
                        <h3>{{ $mes['mes'] }} {{ $mes['anio'] }} - Total días asistidos:
                            {{ $mes['dias_asistidos'] }}</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Fecha</th>
                                    <th style="width: 25%;">Día</th>
                                    <th style="width: 25%;">Hora Entrada</th>
                                    <th style="width: 25%;">Hora Salida</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mes['registros'] as $registro)
                                    <tr>
                                        <td>{{ $registro['fecha'] }}</td>
                                        <td>{{ ucfirst($registro['dia_semana']) }}</td>
                                        <td>{{ $registro['hora_entrada'] ?? '-' }}</td>
                                        <td>{{ $registro['hora_salida'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endif
        @else
            <div class="alert warning">
                <strong>Sin registros de asistencia</strong>
                <p>El estudiante aún no tiene registros de asistencia en este ciclo.</p>
            </div>
        @endif

        <div class="footer">
            <p><strong>Información Importante:</strong></p>
            <p>Las clases se imparten de Lunes a Viernes | Límite de amonestación:
                {{ $ciclo->porcentaje_amonestacion }}% | Límite de inhabilitación:
                {{ $ciclo->porcentaje_inhabilitacion }}%</p>
            <p>A/F/T = Asistidos/Faltas/Total días hábiles</p>
            <p>Este es un documento generado automáticamente por el sistema de gestión académica.</p>
        </div>
    </div>
</body>

</html>
