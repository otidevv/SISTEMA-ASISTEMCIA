<table>
    <thead>
        <tr>
            <th colspan="12" style="text-align: center; font-size: 16px; font-weight: bold;">
                REPORTE DE ASISTENCIA
            </th>
        </tr>
        <tr>
            <th colspan="12" style="text-align: center; font-size: 14px;">
                {{ $ciclo->nombre }} - {{ $dia_semana }} {{ $fecha_reporte_formato }}
                @if ($es_examen)
                    - DÍA DE EXAMEN
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="12"></th>
        </tr>
        <tr>
            <th colspan="3">Total Estudiantes: {{ $total_estudiantes }}</th>
            <th colspan="3">Asistencias: {{ $total_asistencias }} ({{ $porcentaje_asistencia }}%)</th>
            <th colspan="3">Faltas: {{ $total_faltas }} ({{ $porcentaje_faltas }}%)</th>
            <th colspan="3">Generado: {{ $fecha_generacion }}</th>
        </tr>
        <tr>
            <th colspan="12"></th>
        </tr>
    </thead>
    <tbody>
        <tr style="background-color: #2C3E50; color: white;">
            <th>#</th>
            <th>Código</th>
            <th>Estudiante</th>
            <th>Documento</th>
            <th>Carrera</th>
            <th>Turno</th>
            <th>Aula</th>
            @if ($tipo_reporte !== 'faltas_dia')
                <th>Entrada</th>
                <th>Salida</th>
                <th>Estado</th>
            @endif
            @if ($es_examen && $tipo_reporte === 'resumen_examen')
                <th>% Asist.</th>
                <th>Puede Rendir</th>
            @endif
        </tr>
        @foreach ($estudiantes as $index => $item)
            <tr style="{{ !$item['asistio'] ? 'background-color: #ffe4e4;' : '' }}">
                <td>{{ $index + 1 }}</td>
                <td>{{ $item['inscripcion']->codigo_inscripcion }}</td>
                <td>
                    {{ $item['estudiante']->apellido_paterno }}
                    {{ $item['estudiante']->apellido_materno }},
                    {{ $item['estudiante']->nombre }}
                </td>
                <td>{{ $item['estudiante']->numero_documento }}</td>
                <td>{{ $item['inscripcion']->carrera->nombre }}</td>
                <td>{{ $item['inscripcion']->turno->nombre }}</td>
                <td>{{ $item['inscripcion']->aula->codigo }}</td>
                @if ($tipo_reporte !== 'faltas_dia')
                    <td
                        style="{{ $item['hora_entrada'] === 'Sin registro' ? 'color: #dc3545; font-style: italic;' : '' }}">
                        {{ $item['hora_entrada'] ?? '-' }}
                    </td>
                    <td>{{ $item['hora_salida'] ?? '-' }}</td>
                    <td style="{{ $item['asistio'] ? 'color: #28a745;' : 'color: #dc3545;' }}">
                        {{ $item['asistio'] ? 'Asistió' : 'Faltó' }}
                    </td>
                @endif
                @if ($es_examen && $tipo_reporte === 'resumen_examen')
                    <td>{{ $item['porcentaje_asistencia'] }}%</td>
                    <td style="{{ $item['puede_rendir_examen'] ? 'color: #28a745;' : 'color: #dc3545;' }}">
                        {{ $item['puede_rendir_examen'] ? 'SÍ' : 'NO' }}
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="12"></td>
        </tr>
        <tr>
            <td colspan="12" style="font-size: 10px; color: #6c757d;">
                @if ($filtros['carrera'] || $filtros['turno'] || $filtros['aula'])
                    Filtros aplicados:
                    @if ($filtros['carrera'])
                        Carrera: {{ $filtros['carrera']->nombre }}
                    @endif
                    @if ($filtros['turno'])
                        | Turno: {{ $filtros['turno']->nombre }}
                    @endif
                    @if ($filtros['aula'])
                        | Aula: {{ $filtros['aula']->codigo }}
                    @endif
                @endif
            </td>
        </tr>
        @if ($es_examen)
            <tr>
                <td colspan="12" style="font-size: 10px; color: #6c757d;">
                    Los estudiantes que no pueden rendir el examen han superado el límite de
                    {{ $ciclo->porcentaje_inhabilitacion }}% de inasistencias
                </td>
            </tr>
        @endif
    </tfoot>
</table>
