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

    $colorAsist  = '#43a047';  // verde siempre para asistencias
    $puedeRendir = ($info['puede_rendir'] ?? 'NO') == 'SÍ';
    $esProyeccion = $info['es_proyeccion'] ?? false;

    // ── SVG DONUT con paths ──────────────────────────────────────────
    $r      = 30;   // radio externo
    $ri     = 17;   // radio interno (agujero)
    $cx     = 44;   $cy = 44;  // centro del SVG 88x88

    $pctAsist = min(99.99, max(0.01, (float)($info['porcentaje_asistencia'] ?? 0)));
    $pctFalta = min(99.99, max(0.01, (float)($info['porcentaje_falta']      ?? 0)));

    // Ángulos (partimos de -90°, tope del círculo)
    $startDeg  = -90;
    $midDeg    = $startDeg + $pctAsist * 3.6;   // donde termina asistencia / empieza falta
    $endDeg    = $startDeg + 360;               // vuelve al inicio

    // Función auxiliar para calcular x,y en la circunferencia
    $px = fn($ang, $rad) => round($cx + $rad * cos(deg2rad($ang)), 3);
    $py = fn($ang, $rad) => round($cy + $rad * sin(deg2rad($ang)), 3);

    // Puntos de los arcos
    $s_ox = $px($startDeg, $r);   $s_oy = $py($startDeg, $r);   // exterior inicio
    $s_ix = $px($startDeg, $ri);  $s_iy = $py($startDeg, $ri);  // interior inicio
    $m_ox = $px($midDeg,   $r);   $m_oy = $py($midDeg,   $r);   // exterior medio
    $m_ix = $px($midDeg,   $ri);  $m_iy = $py($midDeg,   $ri);  // interior medio

    $laAsist = ($pctAsist > 50) ? 1 : 0;
    $laFalta = ($pctFalta > 50) ? 1 : 0;

    // Path del sector ASISTENCIA (verde)
    // M inicio-ext → arco-ext → L interior-mid → arco-int-inv → Z
    $pathAsist = "M {$s_ox},{$s_oy} A {$r},{$r} 0 {$laAsist},1 {$m_ox},{$m_oy} L {$m_ix},{$m_iy} A {$ri},{$ri} 0 {$laAsist},0 {$s_ix},{$s_iy} Z";

    // Path del sector FALTAS (color condición)
    $pathFalta = "M {$m_ox},{$m_oy} A {$r},{$r} 0 {$laFalta},1 {$s_ox},{$s_oy} L {$s_ix},{$s_iy} A {$ri},{$ri} 0 {$laFalta},0 {$m_ix},{$m_iy} Z";
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
                {{-- ── IZQUIERDA: Donut SVG ── --}}
                <td style="width:38%; vertical-align:middle; text-align:center; padding-right:14px; border-right:1.5px dashed {{ $colorBorde }};">

                    <svg width="88" height="88" viewBox="0 0 88 88" xmlns="http://www.w3.org/2000/svg">
                        {{-- Fondo gris --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $r }}" fill="#e0e0e0"/>
                        {{-- Sector asistencias (verde) --}}
                        <path d="{{ $pathAsist }}" fill="{{ $colorAsist }}"/>
                        {{-- Sector faltas (color condición) --}}
                        <path d="{{ $pathFalta }}" fill="{{ $colorFalta }}"/>
                        {{-- Agujero interior (fondo tarjeta) --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $ri }}" fill="{{ $bgTarjeta }}"/>
                        {{-- Texto central: % faltas (PRINCIPAL) --}}
                        <text x="{{ $cx }}" y="{{ $cy - 4 }}"
                            text-anchor="middle" font-size="13" font-weight="bold" fill="{{ $colorPct }}">{{ $pctFalta }}%</text>
                        <text x="{{ $cx }}" y="{{ $cy + 8 }}"
                            text-anchor="middle" font-size="6.5" font-weight="bold" fill="{{ $colorTexto }}">FALTAS</text>
                    </svg>

                    {{-- Leyenda --}}
                    <table style="width:100%; border-collapse:collapse; margin-top:3px; font-size:7px;">
                        <tr>
                            <td style="text-align:center; padding:1px 3px;">
                                <span style="display:inline-block; width:8px; height:8px; background-color:{{ $colorAsist }}; border-radius:2px; vertical-align:middle;"></span>
                                <span style="color:{{ $colorTexto }}; font-weight:bold; vertical-align:middle;"> {{ $info['porcentaje_asistencia'] }}% Asist.</span>
                            </td>
                            <td style="text-align:center; padding:1px 3px;">
                                <span style="display:inline-block; width:8px; height:8px; background-color:{{ $colorFalta }}; border-radius:2px; vertical-align:middle;"></span>
                                <span style="color:{{ $colorTexto }}; font-weight:bold; vertical-align:middle;"> {{ $info['porcentaje_falta'] }}% Faltas</span>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- ── DERECHA: Métricas ── --}}
                <td style="width:62%; vertical-align:middle; padding-left:18px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="padding-bottom:8px; width:50%;">
                                <span style="display:block; font-size:7px; font-weight:800; color:{{ $colorTexto }}; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:2px;">Asistencia</span>
                                <span style="font-size:20px; font-weight:900; color:#2e7d32; line-height:1;">{{ $info['porcentaje_asistencia'] }}%</span>
                            </td>
                            <td style="padding-bottom:8px; width:50%;">
                                <span style="display:block; font-size:7px; font-weight:800; color:{{ $colorTexto }}; text-transform:uppercase; letter-spacing:0.3px;">Condición Académica</span>
                                <span style="display:inline-block; background-color:{{ $bgBadge }}; color:{{ $colorBadge }}; padding:3px 10px; border-radius:20px; font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:0.4px; border:1.5px solid {{ $colorBorde }}; margin-top:3px;">
                                    {{ $condicion }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top:6px; border-top:1px solid {{ $colorBorde }};">
                                <span style="display:block; font-size:7px; font-weight:800; color:{{ $colorTexto }}; text-transform:uppercase; letter-spacing:0.3px;">Habilitación</span>
                                <span style="display:inline-block; background-color:{{ $puedeRendir ? '#a5d6a7' : '#ef9a9a' }}; color:{{ $puedeRendir ? '#1b5e20' : '#7f0000' }}; padding:3px 10px; border-radius:3px; font-size:8px; font-weight:bold; text-transform:uppercase; letter-spacing:0.3px; border:1.5px solid {{ $puedeRendir ? '#2e7d32' : '#c62828' }}; margin-top:3px;">
                                    {{ $puedeRendir ? 'Habilitado SI' : 'Habilitado NO' }}
                                </span>
                            </td>
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
            ? '[OK] EL ESTUDIANTE SE ENCUENTRA HABILITADO PARA ESTA EVALUACION'
            : '[!] EL ESTUDIANTE SE ENCUENTRA INHABILITADO POR EXCESO DE INASISTENCIAS' }}
    </div>
</div>
