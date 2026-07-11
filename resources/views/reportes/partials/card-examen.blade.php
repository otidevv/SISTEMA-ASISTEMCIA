@php
    $condicion = $info['condicion'] ?? 'Pendiente';

    // Paleta por condición
    if ($condicion == 'Regular') {
        $colorBorde  = '#2e7d32'; $colorTexto  = '#1b5e20';
        $bgTarjeta   = '#f1f8e9'; $bgBanner    = '#c8e6c9'; $colorBanner = '#1b5e20';
        $bgBadge     = '#a5d6a7'; $colorBadge  = '#1b5e20';
        $colorFalta  = '#e53935'; $colorPct    = '#2e7d32';
    } elseif ($condicion == 'Amonestado') {
        $colorBorde  = '#e65100'; $colorTexto  = '#bf360c';
        $bgTarjeta   = '#fff8e1'; $bgBanner    = '#ffe0b2'; $colorBanner = '#bf360c';
        $bgBadge     = '#ffcc80'; $colorBadge  = '#e65100';
        $colorFalta  = '#fb8c00'; $colorPct    = '#e65100';
    } elseif ($condicion == 'Inhabilitado') {
        $colorBorde  = '#c62828'; $colorTexto  = '#880e4f';
        $bgTarjeta   = '#fce4ec'; $bgBanner    = '#ef9a9a'; $colorBanner = '#7f0000';
        $bgBadge     = '#ef9a9a'; $colorBadge  = '#b71c1c';
        $colorFalta  = '#e53935'; $colorPct    = '#c62828';
    } else {
        $colorBorde  = '#546e7a'; $colorTexto  = '#37474f';
        $bgTarjeta   = '#f5f5f5'; $bgBanner    = '#eceff1'; $colorBanner = '#37474f';
        $bgBadge     = '#cfd8dc'; $colorBadge  = '#37474f';
        $colorFalta  = '#78909c'; $colorPct    = '#546e7a';
    }

    $colorAsist  = '#43a047';
    $puedeRendir = ($info['puede_rendir'] ?? 'NO') == 'SÍ';
    $esProyeccion = $info['es_proyeccion'] ?? false;

    $pctAsist = (float)($info['porcentaje_asistencia'] ?? 0);
    $pctFalta = (float)($info['porcentaje_falta']      ?? 0);

    // Anchos para la barra proporcional (mínimo 2% para que sea visible)
    $wAsist = max(2, min(98, $pctAsist));
    $wFalta = 100 - $wAsist;

    // Barra de límite del 30% (posición desde la derecha)
    // Si pctFalta supera 30%, la barra llega hasta ahí
    $limitePos = 70; // 70% asistencia = 30% faltas
@endphp

<div style="border: 1px solid {{ $colorBorde }}; border-left: 6px solid {{ $colorBorde }}; border-radius: 6px; margin-bottom: 20px; overflow: hidden; background-color: {{ $bgTarjeta }};">

    {{-- ══ HEADER ══ --}}
    <table style="width:100%; border-collapse:collapse; background-color:#2b5a6f; color:white;">
        <tr>
            <td style="padding:10px 14px; vertical-align:middle;">
                <span style="font-weight:800; font-size:10px; text-transform:uppercase; letter-spacing:0.5px;">
                    &raquo; {{ $titulo }}
                    @if(\Carbon\Carbon::hasFormat($fecha,'Y-m-d H:i:s') || \Carbon\Carbon::hasFormat($fecha,'Y-m-d'))
                        &mdash; FECHA: {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                    @else
                        &mdash; FECHA: {{ $fecha }}
                    @endif
                </span>
            </td>
            <td style="padding:10px 14px; text-align:right; vertical-align:middle;">
                @if($esProyeccion)
                    <span style="background:#00aeef; border:1.5px solid #fff; padding:1px 6px; border-radius:3px; font-size:7.5px; font-weight:800; text-transform:uppercase;">PROYECCIÓN</span>
                @endif
                <span style="font-size:8px; margin-left:8px; font-family:monospace; font-weight:bold; background-color:rgba(255,255,255,0.15); padding:2px 6px; border-radius:3px; letter-spacing:0.3px;">
                    ASISTIDOS: {{ $info['dias_asistidos'] }} &nbsp;|&nbsp; FALTAS: {{ $info['dias_falta'] }} &nbsp;|&nbsp; TOTAL: {{ $info['dias_habiles'] }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ══ CUERPO ══ --}}
    <div style="padding:14px 16px;">
        <table style="width:100%; border-collapse:collapse;">
            <tr>

                {{-- ── IZQUIERDA: KPI Visual con barra proporcional ── --}}
                <td style="width:40%; vertical-align:middle; padding-right:14px; border-right:1.5px dashed {{ $colorBorde }};">

                    {{-- % FALTAS como KPI principal --}}
                    <div style="text-align:center; padding:8px 6px; background-color:rgba(255,255,255,0.6); border-radius:5px; border:1px solid {{ $colorBorde }};">

                        <span style="display:block; font-size:7px; font-weight:800; color:{{ $colorTexto }}; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:1px;">
                            INASISTENCIAS
                        </span>
                        <span style="font-size:26px; font-weight:900; color:{{ $colorPct }}; line-height:1.1;">
                            {{ $pctFalta }}%
                        </span>
                        <span style="display:block; font-size:7px; color:{{ $colorTexto }}; margin-bottom:6px;">
                            de faltas acumuladas
                        </span>

                        {{-- ─ Barra proporcional ─ --}}
                        <table style="width:100%; border-collapse:collapse; border-radius:3px; overflow:hidden; margin-bottom:4px;">
                            <tr>
                                <td style="width:{{ $wAsist }}%; background-color:{{ $colorAsist }}; height:11px; padding:0;"></td>
                                <td style="width:{{ $wFalta }}%; background-color:{{ $colorFalta }}; height:11px; padding:0;"></td>
                            </tr>
                        </table>

                        {{-- Leyenda de barra --}}
                        <table style="width:100%; border-collapse:collapse;">
                            <tr>
                                <td style="text-align:left; padding:0 2px;">
                                    <span style="display:inline-block; width:7px; height:7px; background-color:{{ $colorAsist }}; border-radius:1px; vertical-align:middle;"></span>
                                    <span style="font-size:6.5px; font-weight:bold; color:{{ $colorTexto }}; vertical-align:middle;"> {{ $pctAsist }}% Asist.</span>
                                </td>
                                <td style="text-align:right; padding:0 2px;">
                                    <span style="display:inline-block; width:7px; height:7px; background-color:{{ $colorFalta }}; border-radius:1px; vertical-align:middle;"></span>
                                    <span style="font-size:6.5px; font-weight:bold; color:{{ $colorTexto }}; vertical-align:middle;"> {{ $pctFalta }}% Faltas</span>
                                </td>
                            </tr>
                        </table>

                        {{-- Límite del 30% --}}
                        <div style="margin-top:5px; font-size:6.5px; color:{{ $colorTexto }}; font-weight:bold; border-top:1px solid {{ $colorBorde }}; padding-top:4px; text-align:center;">
                            LIMITE REGLAMENTARIO: 30% de faltas
                        </div>
                    </div>

                </td>

                {{-- ── DERECHA: Métricas ── --}}
                <td style="width:60%; vertical-align:middle; padding-left:18px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            {{-- % Asistencia secundario --}}
                            <td style="padding-bottom:8px; width:50%;">
                                <span style="display:block; font-size:7px; font-weight:800; color:{{ $colorTexto }}; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Asistencia</span>
                                <span style="font-size:20px; font-weight:900; color:{{ $colorAsist }}; line-height:1;">{{ $pctAsist }}%</span>
                            </td>
                            {{-- Condición académica --}}
                            <td style="padding-bottom:8px; width:50%;">
                                <span style="display:block; font-size:7px; font-weight:800; color:{{ $colorTexto }}; text-transform:uppercase; letter-spacing:0.3px;">Condición Académica</span>
                                <span style="display:inline-block; background-color:{{ $bgBadge }}; color:{{ $colorBadge }}; padding:3px 10px; border-radius:20px; font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:0.4px; border:1.5px solid {{ $colorBorde }}; margin-top:3px;">
                                    {{ $condicion }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            {{-- Habilitación --}}
                            <td style="padding-top:6px; border-top:1px solid {{ $colorBorde }};">
                                <span style="display:block; font-size:7px; font-weight:800; color:{{ $colorTexto }}; text-transform:uppercase; letter-spacing:0.3px;">Habilitación</span>
                                <span style="display:inline-block; background-color:{{ $puedeRendir ? '#a5d6a7' : '#ef9a9a' }}; color:{{ $puedeRendir ? '#1b5e20' : '#7f0000' }}; padding:3px 10px; border-radius:3px; font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:0.3px; border:1.5px solid {{ $puedeRendir ? '#2e7d32' : '#c62828' }}; margin-top:3px;">
                                    {{ $puedeRendir ? 'Habilitado SI' : 'Habilitado NO' }}
                                </span>
                            </td>
                            {{-- Límite permitido --}}
                            <td style="padding-top:6px; border-top:1px solid {{ $colorBorde }};">
                                <span style="display:block; font-size:7px; font-weight:800; color:{{ $colorTexto }}; text-transform:uppercase; letter-spacing:0.3px;">Inasistencias Permitidas</span>
                                <span style="font-size:11px; font-weight:bold; color:{{ $colorTexto }}; display:block; margin-top:2px;">Máximo 30%</span>
                            </td>
                        </tr>
                    </table>
                </td>

            </tr>
        </table>
    </div>

    {{-- ══ BANNER FINAL ══ --}}
    <div style="padding:8px 14px; font-size:8.5px; text-align:center; background-color:{{ $bgBanner }}; color:{{ $colorBanner }}; border-top:1.5px solid {{ $colorBorde }}; letter-spacing:0.5px; text-transform:uppercase; font-weight:bold;">
        {{ $puedeRendir
            ? 'EL ESTUDIANTE SE ENCUENTRA HABILITADO PARA ESTA EVALUACION'
            : 'EL ESTUDIANTE SE ENCUENTRA INHABILITADO POR EXCESO DE INASISTENCIAS' }}
    </div>

</div>
