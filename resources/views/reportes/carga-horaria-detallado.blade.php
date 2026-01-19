<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Detallado de Carga Horaria - {{ $docente->nombre_completo }}</title>
    <style>
        @page { margin: 1.0cm; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.2; }
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 2px solid #2c3e50; 
            padding-bottom: 8px; 
            position: relative;
            min-height: 60px;
        }
        .header-logo-left {
            position: absolute;
            left: 0;
            top: 0;
            width: 50px;
        }
        .header-logo-right {
            position: absolute;
            right: 0;
            top: 0;
            width: 50px;
        }
        .header-logo-left img, .header-logo-right img {
            width: 50px;
            height: auto;
        }
        .header h1 { margin: 0; color: #2c3e50; text-transform: uppercase; font-size: 16px; letter-spacing: 1px; padding: 0 60px; }
        .header p { margin: 3px 0 0; font-size: 11px; color: #7f8c8d; padding: 0 60px; }
        
        .section-title { background-color: #f1f4f9; padding: 5px 12px; border-left: 4px solid #3498db; margin: 12px 0 8px; font-weight: bold; color: #2c3e50; text-transform: uppercase; font-size: 10px; }
        
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-grid td { padding: 3px 0; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #7f8c8d; width: 120px; text-transform: uppercase; font-size: 9px; }
        .value { color: #2c3e50; font-size: 10px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { background-color: #2c3e50; color: white; padding: 8px; text-align: left; font-size: 10px; }
        .data-table td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
        
        .payment-box { margin-top: 15px; padding: 12px; border: 2px dashed #27ae60; border-radius: 8px; background-color: #f9fffb; }
        .payment-row { display: table; width: 100%; margin-bottom: 5px; }
        .payment-label { display: table-cell; font-weight: bold; color: #2c3e50; font-size: 10px; }
        .payment-value { display: table-cell; text-align: right; font-weight: bold; color: #27ae60; font-size: 12px; }
        
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8px; color: #95a5a6; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
        
        .badge { padding: 2px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo-left">
            <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="Logo UNAMAD">
        </div>
        <div class="header-logo-right">
            <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="Logo CEPRE">
        </div>
        <h1>REPORTE DE CARGA HORARIA DOCENTE</h1>
        <p>Ciclo Académico {{ $ciclo->nombre }}</p>
        <p style="font-size: 8px; font-style: italic; color: #95a5a6; margin-top: 2px;">"Centro Pre-Universitario - UNAMAD"</p>
    </div>

    <div class="section-title">Información Personal</div>
    <table class="info-grid">
        <tr>
            <td class="label">Docente:</td>
            <td class="value">{{ $docente->nombre_completo }}</td>
            <td class="label">DNI:</td>
            <td class="value">{{ $docente->numero_documento }}</td>
        </tr>
        <tr>
            <td class="label">Email:</td>
            <td class="value">{{ $docente->email }}</td>
            <td class="label">Teléfono:</td>
            <td class="value">{{ $docente->celular ?? $docente->telefono ?? 'No registrado' }}</td>
        </tr>
    </table>

    <div class="section-title">Detalle de Asignaturas y Horarios</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Curso</th>
                <th>Aula</th>
                <th>Día</th>
                <th>Horario</th>
                <th>Turno</th>
                <th style="text-align: right;">Horas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['horarios'] as $h)
            <tr>
                <td><strong>{{ $h->curso ? $h->curso->nombre : '---' }}</strong><br><small>{{ $h->curso ? $h->curso->codigo : '' }}</small></td>
                <td>{{ $h->aula ? $h->aula->nombre : '---' }}</td>
                <td>{{ $h->dia_semana }}</td>
                <td>{{ substr($h->hora_inicio, 0, 5) }} - {{ substr($h->hora_fin, 0, 5) }}</td>
                <td><span class="badge badge-info">{{ $h->turno }}</span></td>
                <td style="text-align: right; font-weight: bold;">
                    @if($h->es_receso)
                        ---
                    @else
                        {{ $h->horas_formateado }}
                        @if($h->minutos_receso_sustraidos > 0)
                            <br><small style="color: #7f8c8d; font-weight: normal;">(-{{ $h->minutos_receso_sustraidos }}m receso)</small>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f4f9;">
                <td colspan="5" style="text-align: right; font-weight: bold; padding: 10px;">HORAS SEMANALES BASE (L-V):</td>
                <td style="text-align: right; font-weight: bold; color: #2c3e50; font-size: 13px; border-top: 2px solid #2c3e50;">{{ $data['horas_base_formateado'] }}</td>
            </tr>
            <tr style="background-color: #fff;">
                <td colspan="5" style="text-align: right; font-weight: bold; padding: 5px; color: #7f8c8d; font-size: 11px;">PROMEDIO SEMANAL (Incluyendo Sábados Rotativos):</td>
                <td style="text-align: right; font-weight: bold; color: #7f8c8d; font-size: 11px;">{{ $data['total_horas_formateado'] }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">Resumen Económico</div>
    <div class="payment-box">
        <div class="payment-row">
            <span class="payment-label">Tarifa establecida por hora:</span>
            <span class="payment-value">S/ {{ number_format($data['tarifa_por_hora'], 2) }}</span>
        </div>
        <div class="payment-row">
            <span class="payment-label">Total Horas Programadas en el Ciclo:</span>
            <span class="payment-value">{{ $data['horas_totales_ciclo_formateado'] }}</span>
        </div>
        <div class="payment-row" style="color: #666; font-size: 11px; margin-bottom: 5px;">
            <span class="payment-label"><i>(Suma de Lunes a Viernes + Sábados rotativos según calendario)</i></span>
        </div>
        <div class="payment-row">
            <span class="payment-label">Promedio Pago Semanal:</span>
            <span class="payment-value">S/ {{ number_format($data['pago_semanal'], 2) }}</span>
        </div>
        <div style="margin-top: 10px; border-top: 1px solid #ccc; padding-top: 10px;" class="payment-row">
            <span class="payment-label">PAGO TOTAL PROYECTADO CICLO ({{ $data['semanas_ciclo'] }} semanas):</span>
            <span class="payment-value" style="font-size: 18px; color: #1e8449;">S/ {{ number_format($data['pago_total_ciclo'], 2) }}</span>
        </div>
    </div>

    <div style="margin-top: 15px; font-size: 9px; color: #7f8c8d;">
        <p><strong>Nota:</strong> Este es un reporte informativo basado en la carga horaria registrada y la tarifa vigente. El pago real puede estar sujeto a variaciones por asistencias, faltas o tardanzas registradas durante el ciclo.</p>
        @if($ciclo->incluye_sabados)
        <p><strong>Sábados Rotativos:</strong> Se considera la rotación semanal de sábados según el calendario académico. El total semanal es un promedio que incluye la proporción de horas dictadas en sábados.</p>
        @endif
    </div>

    <div class="footer">
        Este documento es un reporte generado automáticamente por el Sistema de Asistencia - {{ $fecha_generacion }}<br>
        Centro Preuniversitario - Universidad Nacional Amazónica de Madre de Dios
    </div>
</body>
</html>
