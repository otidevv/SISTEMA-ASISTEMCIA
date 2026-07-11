@php
    $condicion = $info['condicion'] ?? 'Pendiente';

    // Paleta por condición
    if ($condicion == 'Regular') {
        $colorBorde      = '#2e7d32';
        $colorTexto      = '#1b5e20';
        $bgTarjeta       = '#f1f8e9';
        $bgBanner        = '#c8e6c9';
        $colorBanner     = '#1b5e20';
        $bgBadge         = '#a5d6a7';
        $colorBadge      = '#1b5e20';
        $colorPct        = '#2e7d32';
        $colorBarra      = '#43a047';
        $borderBarra     = '#e2e8f0';
    } elseif ($condicion == 'Amonestado') {
        $colorBorde      = '#e65100';
        $colorTexto      = '#bf360c';
        $bgTarjeta       = '#fff8e1';
        $bgBanner        = '#ffe0b2';
        $colorBanner     = '#bf360c';
        $bgBadge         = '#ffcc80';
        $colorBadge      = '#e65100';
        $colorPct        = '#e65100';
        $colorBarra      = '#fb8c00';
        $borderBarra     = '#ffe0b2';
    } elseif ($condicion == 'Inhabilitado') {
        $colorBorde      = '#c62828';
        $colorTexto      = '#880e4f';
        $bgTarjeta       = '#fce4ec';
        $bgBanner        = '#ef9a9a';
        $colorBanner     = '#7f0000';
        $bgBadge         = '#ef9a9a';
        $colorBadge      = '#b71c1c';
        $colorPct        = '#c62828';
        $colorBarra      = '#e53935';
        $borderBarra     = '#ffcdd2';
    } else {
        $colorBorde      = '#546e7a';
        $colorTexto      = '#37474f';
        $bgTarjeta       = '#f5f5f5';
        $bgBanner        = '#eceff1';
        $colorBanner     = '#37474f';
        $bgBadge         = '#cfd8dc';
        $colorBadge      = '#37474f';
        $colorPct        = '#546e7a';
        $colorBarra      = '#78909c';
        $borderBarra     = '#e0e0e0';
    }

    $puedeRendir  = ($info['puede_rendir'] ?? 'NO') == 'SÍ';
    $esProyeccion = $info['es_proyeccion'] ?? false;
@endphp

<div style="border: 1px solid {{ $colorBorde }}; border-left: 6px solid {{ $colorBorde }}; border-radius: 6px; margin-bottom: 20px; overflow: hidden; background-color: {{ $bgTarjeta }};">

    {{-- Header del Examen --}}
    <table style="width: 100%; border-collapse: collapse; background-color: #2b5a6f; color: white;">
        <tr>
            <td style="padding: 10px 14px; vertical-align: middle;">
                <span style="font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                    &raquo; {{ $titulo }}
                    @if(\Carbon\Carbon::hasFormat($fecha, 'Y-m-d H:i:s') || \Carbon\Carbon::hasFormat($fecha, 'Y-m-d'))
                        &mdash; FECHA: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                    @else
                        &mdash; FECHA: {{ $fecha }}
                    @endif
                </span>
            </td>
            <td style="padding: 10px 14px; text-align: right; vertical-align: middle;">
                @if ($esProyeccion)
                    <span style="background: #00aeef; border: 1.5px solid #fff; padding: 1px 6px; border-radius: 3px; font-size: 7.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px;">PROYECCIÓN</span>
                @endif
                <span style="font-size: 8px; margin-left: 8px; font-family: monospace; font-weight: bold; background-color: rgba(255,255,255,0.15); padding: 2px 6px; border-radius: 3px; letter-spacing: 0.3px;">
                    ASISTIDOS: {{ $info['dias_asistidos'] }} &nbsp;|&nbsp; FALTAS: {{ $info['dias_falta'] }} &nbsp;|&nbsp; TOTAL: {{ $info['dias_habiles'] }}
                </span>
            </td>
        </tr>
    </table>

    <div style="padding: 15px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                {{-- LADO IZQUIERDO: KPI Principal --}}
                <td style="width: 42%; vertical-align: middle; padding-right: 15px; border-right: 1px dashed {{ $colorBorde }};">
                    <div style="text-align: center; padding: 6px; background-color: rgba(255,255,255,0.55); border-radius: 6px; border: 1px solid {{ $colorBorde }};">
                        <span style="display: block; font-size: 7.5px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Porcentaje de Asistencia</span>
                        <span style="font-size: 24px; font-weight: 900; color: {{ $colorPct }}; line-height: 1;">{{ $info['porcentaje_asistencia'] }}%</span>

                        {{-- Barra de Progreso --}}
                        <div style="height: 7px; background-color: {{ $borderBarra }}; border-radius: 4px; overflow: hidden; position: relative; margin-top: 8px;">
                            <div style="height: 100%; background-color: {{ $colorBarra }}; width: {{ $info['porcentaje_asistencia'] }}%; border-radius: 4px;"></div>
                        </div>
                    </div>
                </td>

                {{-- LADO DERECHO: Detalle --}}
                <td style="width: 58%; vertical-align: middle; padding-left: 20px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding-bottom: 8px; width: 50%;">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.3px;">Inasistencias</span>
                                <span style="font-size: 11px; font-weight: bold; color: {{ $colorPct }};">{{ $info['porcentaje_falta'] }}% <small style="font-size: 8px; font-weight: normal; color: {{ $colorTexto }};">de faltas</small></span>
                            </td>
                            <td style="padding-bottom: 8px; width: 50%;">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.3px;">Condición Académica</span>
                                <span style="display: inline-block; background-color: {{ $bgBadge }}; color: {{ $colorBadge }}; padding: 3px 10px; border-radius: 20px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; border: 1.5px solid {{ $colorBorde }}; margin-top: 2px;">
                                    {{ $condicion }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 6px; border-top: 1px solid {{ $colorBorde }};">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.3px;">Habilitación</span>
                                <span style="display: inline-block; background-color: {{ $puedeRendir ? '#a5d6a7' : '#ef9a9a' }}; color: {{ $puedeRendir ? '#1b5e20' : '#7f0000' }}; padding: 3px 10px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; border: 1.5px solid {{ $puedeRendir ? '#2e7d32' : '#c62828' }}; margin-top: 2px;">
                                    {{ $puedeRendir ? 'Habilitado SÍ' : 'Habilitado NO' }}
                                </span>
                            </td>
                            <td style="padding-top: 6px; border-top: 1px solid {{ $colorBorde }};">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.3px;">Inasistencias Permitidas</span>
                                <span style="font-size: 9.5px; font-weight: bold; color: {{ $colorTexto }};">Máximo 30%</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- Banner de Estado Final --}}
    <div style="padding: 8px 14px; font-size: 8.5px; text-align: center; background-color: {{ $bgBanner }}; color: {{ $colorBanner }}; border-top: 1.5px solid {{ $colorBorde }}; letter-spacing: 0.5px; text-transform: uppercase; font-weight: bold;">
        {{ $puedeRendir ? '[OK] EL ESTUDIANTE SE ENCUENTRA HABILITADO PARA ESTA EVALUACION' : '[!] EL ESTUDIANTE SE ENCUENTRA INHABILITADO POR EXCESO DE INASISTENCIAS' }}
    </div>
</div>
