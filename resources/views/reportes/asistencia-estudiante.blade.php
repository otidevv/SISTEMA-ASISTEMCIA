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
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header h2 {
            color: #34495e;
            font-size: 18px;
            font-weight: normal;
        }

        .info-section {
            margin-bottom: 25px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .info-section h3 {
            color: #2c3e50;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            width: 40%;
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
        }

        .summary-section {
            margin-bottom: 25px;
        }

        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-card {
            display: table-cell;
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .summary-card h4 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .summary-card.success h4 {
            color: #28a745;
        }

        .summary-card.warning h4 {
            color: #ffc107;
        }

        .summary-card.danger h4 {
            color: #dc3545;
        }

        .exam-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .exam-card {
            border: 2px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
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
            margin: -15px -15px 15px -15px;
            padding: 10px 15px;
            border-radius: 3px 3px 0 0;
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

        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            text-align: center;
            line-height: 20px;
            color: white;
            font-weight: bold;
        }

        .progress-fill.success {
            background-color: #28a745;
        }

        .progress-fill.warning {
            background-color: #ffc107;
        }

        .progress-fill.danger {
            background-color: #dc3545;
        }

        .alert {
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert.warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .alert.danger {
            background-color: #f8d7da;
            color: #721c24;
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
            font-weight: bold;
        }

        table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }

        .page-break {
            page-break-after: always;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>REPORTE DE ASISTENCIA</h1>
        <h2>{{ $ciclo->nombre }}</h2>
        <p>Generado el: {{ $fecha_generacion }}</p>
    </div>

    <div class="info-section">
        <h3>Información del Estudiante</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Código de Inscripción:</div>
                <div class="info-value">{{ $inscripcion->codigo_inscripcion }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nombre Completo:</div>
                <div class="info-value">{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }}
                    {{ $estudiante->apellido_materno }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Documento:</div>
                <div class="info-value">{{ $estudiante->numero_documento }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Carrera:</div>
                <div class="info-value">{{ $carrera->nombre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Turno:</div>
                <div class="info-value">{{ $turno->nombre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Aula:</div>
                <div class="info-value">{{ $aula->codigo }} - {{ $aula->nombre }}</div>
            </div>
        </div>
    </div>

    @if (isset($info_asistencia) && !empty($info_asistencia))
        @if (isset($info_asistencia['total_ciclo']))
            <div class="summary-section">
                <h3>Resumen General del Ciclo</h3>
                <div class="summary-cards">
                    <div
                        class="summary-card {{ $info_asistencia['total_ciclo']['estado'] == 'regular' ? 'success' : ($info_asistencia['total_ciclo']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                        <h4>{{ $info_asistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $info_asistencia['total_ciclo']['porcentaje_asistencia'] }}%
                        </h4>
                        <p>Asistencia Total</p>
                    </div>
                    <div class="summary-card">
                        <h4>{{ $info_asistencia['total_ciclo']['dias_asistidos'] }}</h4>
                        <p>Días Asistidos</p>
                    </div>
                    <div class="summary-card">
                        <h4>{{ $info_asistencia['total_ciclo']['dias_falta'] }}</h4>
                        <p>Días de Falta</p>
                    </div>
                    <div class="summary-card">
                        <h4>{{ $info_asistencia['total_ciclo']['dias_habiles_transcurridos'] ?? $info_asistencia['total_ciclo']['dias_habiles'] }}
                        </h4>
                        <p>Días Hábiles</p>
                    </div>
                </div>

                <div class="progress-bar">
                    <div class="progress-fill {{ $info_asistencia['total_ciclo']['estado'] == 'regular' ? 'success' : ($info_asistencia['total_ciclo']['estado'] == 'amonestado' ? 'warning' : 'danger') }}"
                        style="width: {{ $info_asistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $info_asistencia['total_ciclo']['porcentaje_asistencia'] }}%">
                        {{ $info_asistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $info_asistencia['total_ciclo']['porcentaje_asistencia'] }}%
                    </div>
                </div>
            </div>
        @endif

        <div class="exam-section">
            <h3>Información por Examen</h3>

            @if (isset($info_asistencia['primer_examen']))
                <div
                    class="exam-card {{ $info_asistencia['primer_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['primer_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                    <div
                        class="exam-header {{ $info_asistencia['primer_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['primer_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                        <h4>Primer Examen - {{ \Carbon\Carbon::parse($ciclo->fecha_primer_examen)->format('d/m/Y') }}
                        </h4>
                        @if ($info_asistencia['primer_examen']['es_proyeccion'])
                            <span class="badge">Proyección</span>
                        @endif
                    </div>

                    <table>
                        <tr>
                            <td><strong>Asistencia:</strong></td>
                            <td>{{ $info_asistencia['primer_examen']['porcentaje_asistencia_actual'] ?? $info_asistencia['primer_examen']['porcentaje_asistencia'] }}%
                            </td>
                            <td><strong>Días Asistidos:</strong></td>
                            <td>{{ $info_asistencia['primer_examen']['dias_asistidos'] }} de
                                {{ $info_asistencia['primer_examen']['dias_habiles'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Faltas:</strong></td>
                            <td>{{ $info_asistencia['primer_examen']['dias_falta'] }}</td>
                            <td><strong>Estado:</strong></td>
                            <td>{{ ucfirst($info_asistencia['primer_examen']['estado']) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Límite Amonestación:</strong></td>
                            <td>{{ $info_asistencia['primer_examen']['limite_amonestacion'] }} faltas</td>
                            <td><strong>Límite Inhabilitación:</strong></td>
                            <td>{{ $info_asistencia['primer_examen']['limite_inhabilitacion'] }} faltas</td>
                        </tr>
                    </table>

                    <div
                        class="alert {{ $info_asistencia['primer_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['primer_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                        <strong>{{ $info_asistencia['primer_examen']['puede_rendir'] ? 'PUEDE RENDIR' : 'NO PUEDE RENDIR' }}</strong>
                    </div>
                </div>
            @endif

            @if (isset($info_asistencia['segundo_examen']) && $info_asistencia['segundo_examen']['estado'] != 'pendiente')
                <div
                    class="exam-card {{ $info_asistencia['segundo_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['segundo_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                    <div
                        class="exam-header {{ $info_asistencia['segundo_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['segundo_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                        <h4>Segundo Examen - {{ \Carbon\Carbon::parse($ciclo->fecha_segundo_examen)->format('d/m/Y') }}
                        </h4>
                        @if ($info_asistencia['segundo_examen']['es_proyeccion'])
                            <span class="badge">Proyección</span>
                        @endif
                    </div>

                    <table>
                        <tr>
                            <td><strong>Asistencia:</strong></td>
                            <td>{{ $info_asistencia['segundo_examen']['porcentaje_asistencia_actual'] ?? $info_asistencia['segundo_examen']['porcentaje_asistencia'] }}%
                            </td>
                            <td><strong>Días Asistidos:</strong></td>
                            <td>{{ $info_asistencia['segundo_examen']['dias_asistidos'] }} de
                                {{ $info_asistencia['segundo_examen']['dias_habiles'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Faltas:</strong></td>
                            <td>{{ $info_asistencia['segundo_examen']['dias_falta'] }}</td>
                            <td><strong>Estado:</strong></td>
                            <td>{{ ucfirst($info_asistencia['segundo_examen']['estado']) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Límite Amonestación:</strong></td>
                            <td>{{ $info_asistencia['segundo_examen']['limite_amonestacion'] }} faltas</td>
                            <td><strong>Límite Inhabilitación:</strong></td>
                            <td>{{ $info_asistencia['segundo_examen']['limite_inhabilitacion'] }} faltas</td>
                        </tr>
                    </table>

                    <div
                        class="alert {{ $info_asistencia['segundo_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['segundo_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                        <strong>{{ $info_asistencia['segundo_examen']['puede_rendir'] ? 'PUEDE RENDIR' : 'NO PUEDE RENDIR' }}</strong>
                    </div>
                </div>
            @endif

            @if (isset($info_asistencia['tercer_examen']) && $info_asistencia['tercer_examen']['estado'] != 'pendiente')
                <div
                    class="exam-card {{ $info_asistencia['tercer_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['tercer_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                    <div
                        class="exam-header {{ $info_asistencia['tercer_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['tercer_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                        <h4>Tercer Examen - {{ \Carbon\Carbon::parse($ciclo->fecha_tercer_examen)->format('d/m/Y') }}
                        </h4>
                        @if ($info_asistencia['tercer_examen']['es_proyeccion'])
                            <span class="badge">Proyección</span>
                        @endif
                    </div>

                    <table>
                        <tr>
                            <td><strong>Asistencia:</strong></td>
                            <td>{{ $info_asistencia['tercer_examen']['porcentaje_asistencia_actual'] ?? $info_asistencia['tercer_examen']['porcentaje_asistencia'] }}%
                            </td>
                            <td><strong>Días Asistidos:</strong></td>
                            <td>{{ $info_asistencia['tercer_examen']['dias_asistidos'] }} de
                                {{ $info_asistencia['tercer_examen']['dias_habiles'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Faltas:</strong></td>
                            <td>{{ $info_asistencia['tercer_examen']['dias_falta'] }}</td>
                            <td><strong>Estado:</strong></td>
                            <td>{{ ucfirst($info_asistencia['tercer_examen']['estado']) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Límite Amonestación:</strong></td>
                            <td>{{ $info_asistencia['tercer_examen']['limite_amonestacion'] }} faltas</td>
                            <td><strong>Límite Inhabilitación:</strong></td>
                            <td>{{ $info_asistencia['tercer_examen']['limite_inhabilitacion'] }} faltas</td>
                        </tr>
                    </table>

                    <div
                        class="alert {{ $info_asistencia['tercer_examen']['estado'] == 'regular' ? 'success' : ($info_asistencia['tercer_examen']['estado'] == 'amonestado' ? 'warning' : 'danger') }}">
                        <strong>{{ $info_asistencia['tercer_examen']['puede_rendir'] ? 'PUEDE RENDIR' : 'NO PUEDE RENDIR' }}</strong>
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
                    <h3>{{ $mes['mes'] }} {{ $mes['anio'] }} - Total días asistidos: {{ $mes['dias_asistidos'] }}
                    </h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Día</th>
                                <th>Hora Entrada</th>
                                <th>Hora Salida</th>
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
        <p>Las clases se imparten de Lunes a Viernes | Límite de amonestación: {{ $ciclo->porcentaje_amonestacion }}% |
            Límite de inhabilitación: {{ $ciclo->porcentaje_inhabilitacion }}%</p>
        <p>Este es un documento generado automáticamente por el sistema de gestión académica.</p>
    </div>
</body>

</html>
