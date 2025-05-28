<style>
    table th {
        vertical-align: middle;
        padding: 5px;
        border: 1px solid #ddd;
    }

    table td {
        vertical-align: bottom;
        padding: 5px;
        border: 1px solid #ddd;
    }
</style>

<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th colspan="25"
                style="text-align: center; font-size: 16px; font-weight: bold; background-color: #2c3e50; color: white;">
                REPORTE DE ASISTENCIAS - {{ $ciclo->nombre }}
            </th>
        </tr>
        <tr>
            <th colspan="25" style="text-align: center; background-color: #ecf0f1;">
                Generado el: {{ $fecha_generacion }}
            </th>
        </tr>
        <tr>
            <th rowspan="3" style="background-color: #3498db; color: white; vertical-align: middle;">Código</th>
            <th rowspan="3" style="background-color: #3498db; color: white; vertical-align: middle;">Estudiante</th>
            <th rowspan="3" style="background-color: #3498db; color: white; vertical-align: middle;">Documento</th>
            <th rowspan="3" style="background-color: #3498db; color: white; vertical-align: middle;">Carrera</th>
            <th rowspan="3" style="background-color: #3498db; color: white; vertical-align: middle;">Aula</th>
            <th rowspan="3" style="background-color: #3498db; color: white; vertical-align: middle;">Turno</th>
            <th rowspan="3" style="background-color: #3498db; color: white; vertical-align: middle;">Celular</th>
            <th rowspan="3" style="background-color: #3498db; color: white; vertical-align: middle;">Primer Registro
            </th>
            <th colspan="5" style="text-align: center; background-color: #e74c3c; color: white;">PRIMER EXAMEN</th>
            <th colspan="5" style="text-align: center; background-color: #f39c12; color: white;">SEGUNDO EXAMEN</th>
            <th colspan="5" style="text-align: center; background-color: #9b59b6; color: white;">TERCER EXAMEN</th>
            <th colspan="2" style="text-align: center; background-color: #27ae60; color: white;">TOTAL CICLO</th>
        </tr>
        <tr>
            {{-- Primer Examen --}}
            <th colspan="5" style="text-align: center; background-color: #ffcdd2; font-size: 11px;">
                @if ($ciclo->fecha_primer_examen)
                    {{ \Carbon\Carbon::parse($ciclo->fecha_primer_examen)->format('d/m/Y') }}
                @endif
            </th>

            {{-- Segundo Examen --}}
            <th colspan="5" style="text-align: center; background-color: #ffe0b2; font-size: 11px;">
                @if ($ciclo->fecha_segundo_examen)
                    {{ \Carbon\Carbon::parse($ciclo->fecha_segundo_examen)->format('d/m/Y') }}
                @endif
            </th>

            {{-- Tercer Examen --}}
            <th colspan="5" style="text-align: center; background-color: #e1bee7; font-size: 11px;">
                @if ($ciclo->fecha_tercer_examen)
                    {{ \Carbon\Carbon::parse($ciclo->fecha_tercer_examen)->format('d/m/Y') }}
                @endif
            </th>

            {{-- Total Ciclo --}}
            <th colspan="2" style="text-align: center; background-color: #c8e6c9; font-size: 11px;">
                Hasta {{ \Carbon\Carbon::now()->format('d/m/Y') }}
            </th>
        </tr>
        <tr>
            {{-- Primer Examen --}}
            <th style="background-color: #ffebee; vertical-align: middle;">% Asist.</th>
            <th style="background-color: #ffebee; vertical-align: middle;">% Falta</th>
            <th style="background-color: #ffebee; vertical-align: middle;">Estado</th>
            <th style="background-color: #ffebee; vertical-align: middle;">Puede Rendir</th>
            <th style="background-color: #ffebee; vertical-align: middle;">Días (A/F/T)</th>

            {{-- Segundo Examen --}}
            <th style="background-color: #fff3e0; vertical-align: middle;">% Asist.</th>
            <th style="background-color: #fff3e0; vertical-align: middle;">% Falta</th>
            <th style="background-color: #fff3e0; vertical-align: middle;">Estado</th>
            <th style="background-color: #fff3e0; vertical-align: middle;">Puede Rendir</th>
            <th style="background-color: #fff3e0; vertical-align: middle;">Días (A/F/T)</th>

            {{-- Tercer Examen --}}
            <th style="background-color: #f3e5f5; vertical-align: middle;">% Asist.</th>
            <th style="background-color: #f3e5f5; vertical-align: middle;">% Falta</th>
            <th style="background-color: #f3e5f5; vertical-align: middle;">Estado</th>
            <th style="background-color: #f3e5f5; vertical-align: middle;">Puede Rendir</th>
            <th style="background-color: #f3e5f5; vertical-align: middle;">Días (A/F/T)</th>

            {{-- Total Ciclo --}}
            <th style="background-color: #e8f5e9; vertical-align: middle;">Días (A/F/T)</th>
            <th style="background-color: #e8f5e9; vertical-align: middle;">Transcurridos</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($inscripciones as $inscripcion)
            <tr>
                {{-- Datos básicos --}}
                <td>{{ $inscripcion['codigo_inscripcion'] }}</td>
                <td>{{ $inscripcion['nombre_completo'] }}</td>
                <td>{{ $inscripcion['documento'] }}</td>
                <td>{{ $inscripcion['carrera'] }}</td>
                <td>{{ $inscripcion['aula'] }}</td>
                <td>{{ $inscripcion['turno'] }}</td>
                <td>{{ $inscripcion['celular'] }}</td>
                <td>{{ $inscripcion['primer_registro'] }}</td>

                {{-- Primer Examen --}}
                <td
                    style="text-align: center; {{ $inscripcion['primer_examen']['condicion'] == 'Inhabilitado' || strpos($inscripcion['primer_examen']['condicion'], 'Inhabilitado') !== false ? 'background-color: #ffcdd2;' : ($inscripcion['primer_examen']['condicion'] == 'Amonestado' || strpos($inscripcion['primer_examen']['condicion'], 'Amonestado') !== false ? 'background-color: #fff9c4;' : '') }}">
                    {{ $inscripcion['primer_examen']['porcentaje_asistencia'] }}%
                    @if ($inscripcion['primer_examen']['es_proyeccion'] ?? false)
                        <br>
                    @endif
                </td>

                <td
                    style="text-align: center; {{ $inscripcion['primer_examen']['condicion'] == 'Inhabilitado' || strpos($inscripcion['primer_examen']['condicion'], 'Inhabilitado') !== false ? 'background-color: #ffcdd2;' : ($inscripcion['primer_examen']['condicion'] == 'Amonestado' || strpos($inscripcion['primer_examen']['condicion'], 'Amonestado') !== false ? 'background-color: #fff9c4;' : '') }}">
                    {{ $inscripcion['primer_examen']['porcentaje_falta'] }}%
                </td>
                <td
                    style="text-align: center; font-weight: bold;
                    {{ strpos($inscripcion['primer_examen']['condicion'], 'Inhabilitado') !== false
                        ? 'color: #c62828; background-color: #ffcdd2;'
                        : (strpos($inscripcion['primer_examen']['condicion'], 'Amonestado') !== false
                            ? 'color: #f57c00; background-color: #fff9c4;'
                            : ($inscripcion['primer_examen']['condicion'] == 'Regular'
                                ? 'color: #2e7d32;'
                                : '')) }}">
                    {{ $inscripcion['primer_examen']['condicion'] }}
                </td>
                <td
                    style="text-align: center; font-weight: bold;
                    {{ $inscripcion['primer_examen']['puede_rendir'] == 'NO'
                        ? 'color: #c62828; background-color: #ffcdd2;'
                        : ($inscripcion['primer_examen']['puede_rendir'] == 'SÍ'
                            ? 'color: #2e7d32;'
                            : '') }}">
                    {{ $inscripcion['primer_examen']['puede_rendir'] }}
                </td>
                <td style="text-align: center;">
                    {{ $inscripcion['primer_examen']['dias_asistidos'] }}/{{ $inscripcion['primer_examen']['dias_falta'] }}/{{ $inscripcion['primer_examen']['dias_habiles'] }}
                </td>

                {{-- Segundo Examen --}}
                <td
                    style="text-align: center; {{ $inscripcion['segundo_examen']['condicion'] == 'Inhabilitado' || strpos($inscripcion['segundo_examen']['condicion'], 'Inhabilitado') !== false ? 'background-color: #ffcdd2;' : ($inscripcion['segundo_examen']['condicion'] == 'Amonestado' || strpos($inscripcion['segundo_examen']['condicion'], 'Amonestado') !== false ? 'background-color: #fff9c4;' : '') }}">
                    {{ $inscripcion['segundo_examen']['porcentaje_asistencia'] }}%
                    @if ($inscripcion['segundo_examen']['es_proyeccion'] ?? false)
                        <br>
                    @endif
                </td>
                <td
                    style="text-align: center; {{ $inscripcion['segundo_examen']['condicion'] == 'Inhabilitado' || strpos($inscripcion['segundo_examen']['condicion'], 'Inhabilitado') !== false ? 'background-color: #ffcdd2;' : ($inscripcion['segundo_examen']['condicion'] == 'Amonestado' || strpos($inscripcion['segundo_examen']['condicion'], 'Amonestado') !== false ? 'background-color: #fff9c4;' : '') }}">
                    {{ $inscripcion['segundo_examen']['porcentaje_falta'] }}%
                </td>
                <td
                    style="text-align: center; font-weight: bold;
                    {{ strpos($inscripcion['segundo_examen']['condicion'], 'Inhabilitado') !== false
                        ? 'color: #c62828; background-color: #ffcdd2;'
                        : (strpos($inscripcion['segundo_examen']['condicion'], 'Amonestado') !== false
                            ? 'color: #f57c00; background-color: #fff9c4;'
                            : ($inscripcion['segundo_examen']['condicion'] == 'Regular'
                                ? 'color: #2e7d32;'
                                : '')) }}">
                    {{ $inscripcion['segundo_examen']['condicion'] }}
                </td>
                <td
                    style="text-align: center; font-weight: bold;
                    {{ $inscripcion['segundo_examen']['puede_rendir'] == 'NO'
                        ? 'color: #c62828; background-color: #ffcdd2;'
                        : ($inscripcion['segundo_examen']['puede_rendir'] == 'SÍ'
                            ? 'color: #2e7d32;'
                            : '') }}">
                    {{ $inscripcion['segundo_examen']['puede_rendir'] }}
                </td>
                <td style="text-align: center;">
                    {{ $inscripcion['segundo_examen']['dias_asistidos'] }}/{{ $inscripcion['segundo_examen']['dias_falta'] }}/{{ $inscripcion['segundo_examen']['dias_habiles'] }}
                </td>

                {{-- Tercer Examen --}}
                <td
                    style="text-align: center; {{ $inscripcion['tercer_examen']['condicion'] == 'Inhabilitado' || strpos($inscripcion['tercer_examen']['condicion'], 'Inhabilitado') !== false ? 'background-color: #ffcdd2;' : ($inscripcion['tercer_examen']['condicion'] == 'Amonestado' || strpos($inscripcion['tercer_examen']['condicion'], 'Amonestado') !== false ? 'background-color: #fff9c4;' : '') }}">
                    {{ $inscripcion['tercer_examen']['porcentaje_asistencia'] }}%
                    @if ($inscripcion['tercer_examen']['es_proyeccion'] ?? false)
                        <br>
                    @endif
                </td>
                <td
                    style="text-align: center; {{ $inscripcion['tercer_examen']['condicion'] == 'Inhabilitado' || strpos($inscripcion['tercer_examen']['condicion'], 'Inhabilitado') !== false ? 'background-color: #ffcdd2;' : ($inscripcion['tercer_examen']['condicion'] == 'Amonestado' || strpos($inscripcion['tercer_examen']['condicion'], 'Amonestado') !== false ? 'background-color: #fff9c4;' : '') }}">
                    {{ $inscripcion['tercer_examen']['porcentaje_falta'] }}%
                </td>
                <td
                    style="text-align: center; font-weight: bold;
                    {{ strpos($inscripcion['tercer_examen']['condicion'], 'Inhabilitado') !== false
                        ? 'color: #c62828; background-color: #ffcdd2;'
                        : (strpos($inscripcion['tercer_examen']['condicion'], 'Amonestado') !== false
                            ? 'color: #f57c00; background-color: #fff9c4;'
                            : ($inscripcion['tercer_examen']['condicion'] == 'Regular'
                                ? 'color: #2e7d32;'
                                : '')) }}">
                    {{ $inscripcion['tercer_examen']['condicion'] }}
                </td>
                <td
                    style="text-align: center; font-weight: bold;
                    {{ $inscripcion['tercer_examen']['puede_rendir'] == 'NO'
                        ? 'color: #c62828; background-color: #ffcdd2;'
                        : ($inscripcion['tercer_examen']['puede_rendir'] == 'SÍ'
                            ? 'color: #2e7d32;'
                            : '') }}">
                    {{ $inscripcion['tercer_examen']['puede_rendir'] }}
                </td>
                <td style="text-align: center;">
                    {{ $inscripcion['tercer_examen']['dias_asistidos'] }}/{{ $inscripcion['tercer_examen']['dias_falta'] }}/{{ $inscripcion['tercer_examen']['dias_habiles'] }}
                </td>

                {{-- Total Ciclo --}}

                <td style="text-align: center;">
                    {{ $inscripcion['total_ciclo']['dias_asistidos'] }}/{{ $inscripcion['total_ciclo']['dias_falta'] }}/{{ $inscripcion['total_ciclo']['dias_habiles'] }}
                </td>
                <td style="text-align: center;">
                    {{ $inscripcion['total_ciclo']['dias_habiles_transcurridos'] ?? $inscripcion['total_ciclo']['dias_habiles'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="25" style="text-align: left; background-color: #ecf0f1; padding: 10px;">
                <strong>Leyenda:</strong>
                <br>• A/F/T = Asistidos/Faltas/Total días hábiles
                <br>• <span style="color: #2e7d32;">Regular</span> = Asistencia adecuada |
                <span style="color: #f57c00;">Amonestado</span> = Más del {{ $ciclo->porcentaje_amonestacion }}% de
                faltas |
                <span style="color: #c62828;">Inhabilitado</span> = Más del {{ $ciclo->porcentaje_inhabilitacion }}% de
                faltas
                <br>• <strong>(Proyección)</strong> = Estado calculado asumiendo que no habrá más asistencias hasta el
                examen
                <br>• <strong>Porcentaje Actual</strong> = Calculado sobre los días transcurridos hasta hoy
            </td>
        </tr>
    </tfoot>
</table>
