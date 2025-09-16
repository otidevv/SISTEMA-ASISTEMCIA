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
            <th colspan="7" style="text-align: center; font-size: 16px; font-weight: bold; background-color: #2c3e50; color: white;">
                REPORTE FINANCIERO CONSOLIDADO
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center; background-color: #ecf0f1;">
                Generado el: {{ $fecha_generacion }}
            </th>
        </tr>
    </thead>
</table>

<br>

<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; background-color: #3498db; color: white; font-weight: bold;">
                RESUMEN POR CARRERA Y CICLO
            </th>
        </tr>
        <tr>
            <th style="background-color: #ecf0f1;">Ciclo</th>
            <th style="background-color: #ecf0f1;">Carrera</th>
            <th style="background-color: #ecf0f1;">Total Postulantes</th>
            <th style="background-color: #ecf0f1;">Total Matrícula</th>
            <th style="background-color: #ecf0f1;">Total Enseñanza</th>
            <th style="background-color: #ecf0f1;">Total Recaudado</th>
            <th style="background-color: #ecf0f1;">Vouchers Emitidos</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($datos['resumen_por_carrera'] as $resumen)
            <tr>
                <td>{{ $resumen['ciclo'] }}</td>
                <td>{{ $resumen['carrera'] }}</td>
                <td style="text-align: center;">{{ $resumen['total_postulantes'] }}</td>
                <td style="text-align: right;">S/ {{ number_format($resumen['total_matricula'], 2) }}</td>
                <td style="text-align: right;">S/ {{ number_format($resumen['total_ensenanza'], 2) }}</td>
                <td style="text-align: right; font-weight: bold;">S/ {{ number_format($resumen['total_recaudado'], 2) }}</td>
                <td style="text-align: center;">{{ $resumen['vouchers_emitidos'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>

<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th colspan="4" style="text-align: center; background-color: #e74c3c; color: white; font-weight: bold;">
                PAGOS PENDIENTES
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3" style="background-color: #ecf0f1;">Postulantes Aprobados con Pago Pendiente:</td>
            <td style="text-align: center; font-weight: bold; background-color: #ffebee;">{{ $datos['pagos_pendientes'] }}</td>
        </tr>
    </tbody>
</table>

<br>

<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th colspan="4" style="text-align: center; background-color: #f39c12; color: white; font-weight: bold;">
                RESUMEN MENSUAL
            </th>
        </tr>
        <tr>
            <th style="background-color: #ecf0f1;">Período</th>
            <th style="background-color: #ecf0f1;">Total Postulantes</th>
            <th style="background-color: #ecf0f1;">Total Recaudado</th>
            <th style="background-color: #ecf0f1;">Promedio por Postulante</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($datos['resumen_mensual'] as $mes)
            <tr>
                <td>{{ $mes['periodo'] }}</td>
                <td style="text-align: center;">{{ $mes['total_postulantes'] }}</td>
                <td style="text-align: right;">S/ {{ number_format($mes['total_recaudado_mes'], 2) }}</td>
                <td style="text-align: right;">S/ {{ $mes['total_postulantes'] > 0 ? number_format($mes['total_recaudado_mes'] / $mes['total_postulantes'], 2) : 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>

<table style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th colspan="4" style="text-align: center; background-color: #27ae60; color: white; font-weight: bold;">
                TOTAL GENERAL
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="background-color: #ecf0f1;">Total Postulantes con Pago Verificado:</td>
            <td style="text-align: center; font-weight: bold;">{{ $datos['total_general']['postulantes'] }}</td>
            <td style="background-color: #ecf0f1;">Total Recaudado:</td>
            <td style="text-align: right; font-weight: bold;">S/ {{ number_format($datos['total_general']['recaudado'], 2) }}</td>
        </tr>
        <tr>
            <td style="background-color: #ecf0f1;">Vouchers Emitidos:</td>
            <td style="text-align: center; font-weight: bold;">{{ $datos['total_general']['vouchers'] }}</td>
            <td style="background-color: #ecf0f1;">Promedio por Postulante:</td>
            <td style="text-align: right; font-weight: bold;">S/ {{ $datos['total_general']['postulantes'] > 0 ? number_format($datos['total_general']['recaudado'] / $datos['total_general']['postulantes'], 2) : 0 }}</td>
        </tr>
    </tbody>
</table>
