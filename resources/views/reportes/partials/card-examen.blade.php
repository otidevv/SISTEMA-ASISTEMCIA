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
        $colorAsist      = '#43a047';   // arco asistencias (verde)
        $colorFalta      = '#ef9a9a';   // arco faltas (suave)
        $colorPct        = '#2e7d32';
        $bgKpi           = 'rgba(255,255,255,0.55)';
    } elseif ($condicion == 'Amonestado') {
        $colorBorde      = '#e65100';
        $colorTexto      = '#bf360c';
        $bgTarjeta       = '#fff8e1';
        $bgBanner        = '#ffe0b2';
        $colorBanner     = '#bf360c';
        $bgBadge         = '#ffcc80';
        $colorBadge      = '#e65100';
        $colorAsist      = '#43a047';
        $colorFalta      = '#fb8c00';
        $colorPct        = '#e65100';
        $bgKpi           = 'rgba(255,255,255,0.55)';
    } elseif ($condicion == 'Inhabilitado') {
        $colorBorde      = '#c62828';
        $colorTexto      = '#880e4f';
        $bgTarjeta       = '#fce4ec';
        $bgBanner        = '#ef9a9a';
        $colorBanner     = '#7f0000';
        $bgBadge         = '#ef9a9a';
        $colorBadge      = '#b71c1c';
        $colorAsist      = '#43a047';
        $colorFalta      = '#e53935';
        $colorPct        = '#c62828';
        $bgKpi           = 'rgba(255,255,255,0.55)';
    } else {
        $colorBorde      = '#546e7a';
        $colorTexto      = '#37474f';
        $bgTarjeta       = '#f5f5f5';
        $bgBanner        = '#eceff1';
        $colorBanner     = '#37474f';
        $bgBadge         = '#cfd8dc';
        $colorBadge      = '#37474f';
        $colorAsist      = '#78909c';
        $colorFalta      = '#b0bec5';
        $colorPct        = '#546e7a';
        $bgKpi           = 'rgba(255,255,255,0.55)';
    }

    $puedeRendir  = ($info['puede_rendir'] ?? 'NO') == 'SÍ';
    $esProyeccion = $info['es_proyeccion'] ?? false;

    // SVG Donut chart
    $r              = 32;          // radio del anillo
    $cx = $cy       = 44;          // centro del SVG (44x44 de margen)
    $circunferencia = round(2 * M_PI * $r, 2);  // ≈ 201.06

    $pctAsist = (float) ($info['porcentaje_asistencia'] ?? 0);
    $pctFalta = (float) ($info['porcentaje_falta']      ?? 0);

    // dash arrays
    $dashAsist = round(($pctAsist / 100) * $circunferencia, 2);
    $gapAsist  = round($circunferencia - $dashAsist, 2);
    $dashFalta = round(($pctFalta / 100) * $circunferencia, 2);
    $gapFalta  = round($circunferencia - $dashFalta, 2);

    // El arco de faltas empieza donde termina el de asistencias (offset)
    $offsetFalta = round($circunferencia - $dashAsist, 2);
@endphp

<div style="border: 1px solid {{ $colorBorde }}; border-left: 6px solid {{ $colorBorde }}; border-radius: 6px; margin-bottom: 20px; overflow: hidden; background-color: {{ $bgTarjeta }};">

    {{-- ══ HEADER ══ --}}
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

    {{-- ══ CUERPO ══ --}}
    <div style="padding: 14px 16px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>

                {{-- ── IZQUIERDA: Gráfico Donut SVG ── --}}
                <td style="width: 38%; vertical-align: middle; text-align: center; padding-right: 14px; border-right: 1.5px dashed {{ $colorBorde }};">
                    <svg width="88" height="88" viewBox="0 0 88 88" xmlns="http://www.w3.org/2000/svg">

                        {{-- Pista de fondo (gris claro) --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                            fill="none"
                            stroke="#e0e0e0"
                            stroke-width="14"/>

                        {{-- Arco: ASISTENCIAS (verde) — parte inferior --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                            fill="none"
                            stroke="{{ $colorAsist }}"
                            stroke-width="14"
                            stroke-dasharray="{{ $dashAsist }} {{ $gapAsist }}"
                            stroke-linecap="butt"
                            transform="rotate(-90 {{ $cx }} {{ $cy }})"/>

                        {{-- Arco: FALTAS (color condición) — encima --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}"
                            fill="none"
                            stroke="{{ $colorFalta }}"
                            stroke-width="14"
                            stroke-dasharray="{{ $dashFalta }} {{ $gapFalta }}"
                            stroke-linecap="butt"
                            stroke-dashoffset="-{{ $dashAsist }}"
                            transform="rotate(-90 {{ $cx }} {{ $cy }})"/>

                        {{-- Centro: % inasistencias (PRINCIPAL) --}}
                        <text x="{{ $cx }}" y="{{ $cy - 5 }}"
                            text-anchor="middle"
                            font-size="15"
                            font-weight="bold"
                            fill="{{ $colorPct }}">{{ $pctFalta }}%</text>
                        <text x="{{ $cx }}" y="{{ $cy + 8 }}"
                            text-anchor="middle"
                            font-size="7"
                            font-weight="bold"
                            fill="{{ $colorTexto }}">FALTAS</text>
                    </svg>

                    {{-- Leyenda debajo del donut --}}
                    <table style="width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 7px;">
                        <tr>
                            <td style="text-align: center; padding: 2px 3px;">
                                <span style="display: inline-block; width: 8px; height: 8px; background-color: {{ $colorAsist }}; border-radius: 2px; vertical-align: middle;"></span>
                                <span style="color: {{ $colorTexto }}; font-weight: bold; vertical-align: middle;"> {{ $pctAsist }}% Asist.</span>
                            </td>
                            <td style="text-align: center; padding: 2px 3px;">
                                <span style="display: inline-block; width: 8px; height: 8px; background-color: {{ $colorFalta }}; border-radius: 2px; vertical-align: middle;"></span>
                                <span style="color: {{ $colorTexto }}; font-weight: bold; vertical-align: middle;"> {{ $pctFalta }}% Faltas</span>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- ── DERECHA: Métricas ── --}}
                <td style="width: 62%; vertical-align: middle; padding-left: 18px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            {{-- % Asistencia (secundario) --}}
                            <td style="padding-bottom: 8px; width: 50%;">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px;">Asistencia</span>
                                <span style="font-size: 18px; font-weight: 900; color: #2e7d32; line-height: 1;">{{ $pctAsist }}%</span>
                            </td>
                            {{-- Condición académica --}}
                            <td style="padding-bottom: 8px; width: 50%;">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.3px;">Condición Académica</span>
                                <span style="display: inline-block; background-color: {{ $bgBadge }}; color: {{ $colorBadge }}; padding: 3px 10px; border-radius: 20px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; border: 1.5px solid {{ $colorBorde }}; margin-top: 3px;">
                                    {{ $condicion }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            {{-- Habilitación --}}
                            <td style="padding-top: 6px; border-top: 1px solid {{ $colorBorde }};">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.3px;">Habilitación</span>
                                <span style="display: inline-block; background-color: {{ $puedeRendir ? '#a5d6a7' : '#ef9a9a' }}; color: {{ $puedeRendir ? '#1b5e20' : '#7f0000' }}; padding: 3px 10px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; border: 1.5px solid {{ $puedeRendir ? '#2e7d32' : '#c62828' }}; margin-top: 3px;">
                                    {{ $puedeRendir ? 'Habilitado SI' : 'Habilitado NO' }}
                                </span>
                            </td>
                            {{-- Límite permitido --}}
                            <td style="padding-top: 6px; border-top: 1px solid {{ $colorBorde }};">
                                <span style="display: block; font-size: 7px; font-weight: 800; color: {{ $colorTexto }}; text-transform: uppercase; letter-spacing: 0.3px;">Inasistencias Permitidas</span>
                                <span style="font-size: 11px; font-weight: bold; color: {{ $colorTexto }}; margin-top: 2px; display: block;">Máximo 30%</span>
                            </td>
                        </tr>
                    </table>
                </td>

            </tr>
        </table>
    </div>

    {{-- ══ BANNER FINAL ══ --}}
    <div style="padding: 8px 14px; font-size: 8.5px; text-align: center; background-color: {{ $bgBanner }}; color: {{ $colorBanner }}; border-top: 1.5px solid {{ $colorBorde }}; letter-spacing: 0.5px; text-transform: uppercase; font-weight: bold;">
        {{ $puedeRendir
            ? '[OK] EL ESTUDIANTE SE ENCUENTRA HABILITADO PARA ESTA EVALUACION'
            : '[!] EL ESTUDIANTE SE ENCUENTRA INHABILITADO POR EXCESO DE INASISTENCIAS' }}
    </div>

</div>
