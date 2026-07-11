@php
if (!function_exists('clean_emoji')) {
    function clean_emoji($texto) {
        if (!$texto) return '';
        $clean = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1FAFF}\x{2600}-\x{27BF}\x{1F1E0}-\x{1F1FF}\x{2B00}-\x{2BFF}\x{2300}-\x{23FF}\x{2190}-\x{21FF}\x{25A0}-\x{25FF}\x{2000}-\x{3300}\x{FE00}-\x{FE0F}]/u', '', $texto);
        return trim($clean);
    }
}
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inhabilitados — {{ clean_emoji($ciclo->nombre) }}</title>
    <style>
        /* =============================================================
           CEPRE-UNAMAD — Reporte Oficial de Estudiantes Inhabilitados
           Diseño: imprimible en color y blanco/negro
           ============================================================= */
        @page {
            margin: 1.5cm 1.8cm 2.2cm 1.8cm;
            size: A4 portrait;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #000;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* ──────────────────────────────────────────
           CABECERA INSTITUCIONAL
        ────────────────────────────────────────── */
        .hdr-outer {
            border: 2.5px solid #000;
            border-radius: 4px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .hdr-top {
            background: #2b5a6f; /* se imprime oscuro en B&W */
            padding: 10px 14px;
        }
        .hdr-top table { width: 100%; border-collapse: collapse; }
        .hdr-logo { width: 62px; vertical-align: middle; }
        .hdr-logo img { width: 56px; }
        .hdr-title { text-align: center; vertical-align: middle; }
        .hdr-title h1 {
            margin: 0 0 3px;
            font-size: 15px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .hdr-title .sub {
            font-size: 9.5px;
            color: #e0e0e0;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .hdr-title .ciclo-tag {
            display: inline-block;
            border: 1.5px solid #fff;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            padding: 1px 10px;
            border-radius: 20px;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }
        /* Franja tricolor — visible en color, en B&W da 3 franjas grises distintas */
        .hdr-stripe {
            height: 5px;
            background: #000; /* fallback B&W */
            background: linear-gradient(to right, #cc0000 0% 33%, #00aeef 33% 66%, #8cc63f 66% 100%);
        }
        /* Fila de subtítulo del reporte */
        .hdr-sub-row {
            background: #f0f0f0;
            text-align: center;
            padding: 4px;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 0.4px;
            border-top: 1px solid #aaa;
            text-transform: uppercase;
            color: #111;
        }

        /* ──────────────────────────────────────────
           SECCIÓN DE DATOS DEL REPORTE
        ────────────────────────────────────────── */
        .info-box {
            border: 1.5px solid #000;
            border-radius: 3px;
            margin-bottom: 10px;
            overflow: hidden;
        }
        .info-box-header {
            background: #2b5a6f;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 10px;
        }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 3px 10px; font-size: 8.5px; border-bottom: 1px solid #ddd; }
        .info-box tr:last-child td { border-bottom: none; }
        .info-lbl { font-weight: bold; color: #111; width: 140px; }
        .info-val { color: #000; }
        .info-val.red { font-weight: bold; }   /* no usamos color para B&W */

        /* Badge de periodo destacado */
        .periodo-badge {
            display: inline;
            background: none;
            color: #ec008c;
            font-weight: bold;
            font-size: 10px;
            border: none;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            text-decoration: underline;
        }

        /* ──────────────────────────────────────────
           CUADROS ESTADÍSTICOS
        ────────────────────────────────────────── */
        .stats-wrap { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .stat-cell { width: 24%; text-align: center; padding: 8px 4px; border: 2px solid #000; border-radius: 3px; }
        .stat-cell .num { font-size: 26px; font-weight: bold; color: #fff; display: block; line-height: 1; }
        .stat-cell .lbl { font-size: 7px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold; color: rgba(255,255,255,0.85); display: block; margin-top: 3px; }
        .stat-spacer { width: 1.2%; }

        /* Cajas de estadísticas con colores CEPRE */
        .st-total  { background: #2b5a6f; border-color: #1a3d52; }
        .st-reg    { background: #5a8a1f; border-color: #4a7218; }
        .st-amo    { background: #c07800; border-color: #a06000; }
        .st-inh    { background: #cc0000; border-color: #aa0000; border-width: 2px; }

        /* ──────────────────────────────────────────
           TÍTULO DE SECCIÓN
        ────────────────────────────────────────── */
        .sec-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
            border-top: 2.5px solid #000;
            border-bottom: 1px solid #888;
            padding: 4px 0;
            margin: 12px 0 8px;
        }
        .sec-title .sec-num {
            display: inline-block;
            background: #2b5a6f;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            padding: 1px 7px;
            border-radius: 2px;
            margin-right: 6px;
        }
        .sec-title .sec-count {
            font-size: 8px;
            font-weight: normal;
            color: #444;
            margin-left: 6px;
        }

        /* ──────────────────────────────────────────
           TABLA RESUMEN POR CARRERA
        ────────────────────────────────────────── */
        table.t-summary {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 14px;
        }
        table.t-summary thead th {
            background: #2b5a6f;
            color: #fff;
            padding: 6px 7px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #000;
            font-weight: bold;
        }
        table.t-summary tbody td {
            padding: 5px 7px;
            border: 1px solid #888;
            vertical-align: middle;
        }
        table.t-summary tbody tr:nth-child(even) td { background: #f5f5f5; }
        table.t-summary .row-total td {
            font-weight: bold;
            background: #e0e0e0 !important;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        /* Modalidad en tabla resumen — texto con prefijo para B&W */
        .mod-post { font-weight: bold; }
        .mod-refo { font-weight: bold; }

        /* ──────────────────────────────────────────
           TABLA DETALLADA
        ────────────────────────────────────────── */
        table.t-detail {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        table.t-detail thead th {
            background: #2b5a6f;
            color: #fff;
            padding: 6px 5px;
            text-align: left;
            font-size: 7.5px;
            text-transform: uppercase;
            border: 1px solid #000;
            font-weight: bold;
        }
        table.t-detail thead th.tc { text-align: center; }
        table.t-detail tbody td {
            padding: 5px 5px;
            border: 1px solid #999;
            vertical-align: middle;
        }
        /* Filas alternas — visibles en B&W */
        table.t-detail tbody tr.row-even td { background: #f5f5f5; }
        table.t-detail tbody tr.row-odd  td { background: #fff; }

        /* Encabezado de carrera */
        table.t-detail .career-hdr td {
            background: #d8d8d8 !important;
            font-weight: bold;
            font-size: 8.5px;
            color: #000;
            padding: 6px 7px;
            border-top: 2px solid #000;
            border-bottom: 1px solid #555;
            letter-spacing: 0.2px;
        }

        /* Badges de modalidad — en B&W usan borde + texto (sin depender del color) */
        .badge {
            display: inline-block;
            font-size: 7px;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1.5px solid #000;
        }
        /* En color: azul / verde / rosa; en B&W: blanco con borde negro */
        .badge-p { background: #00aeef; color: #fff; border-color: #00aeef; }
        .badge-r { background: #8cc63f; color: #fff; border-color: #8cc63f; }
        .badge-i { background: #cc0000; color: #fff; border-color: #cc0000; }
        /* Prefijo de texto para impresión B&W (legible sin color) */
        .badge-p::before { content: "[P] "; }
        .badge-r::before { content: "[R] "; }
        .badge-i::before { content: "[!] "; }

        /* Número de falta — destacado */
        .faltas-num { font-weight: bold; font-size: 9.5px; }

        /* ──────────────────────────────────────────
           FIRMAS
        ────────────────────────────────────────── */
        .firma-section {
            margin-top: 35px;
            border-top: 1.5px solid #000;
            padding-top: 12px;
            page-break-inside: avoid;
        }
        .firma-table { width: 100%; border-collapse: collapse; }
        .firma-cell { text-align: center; vertical-align: bottom; width: 33%; padding: 0 12px; }
        .firma-line {
            border-top: 1.5px solid #000;
            margin: 0 10px 4px;
            padding-top: 4px;
        }
        .firma-cargo { font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; }
        .firma-nombre { font-size: 7px; color: #333; }

        /* ──────────────────────────────────────────
           NOTA INFORMATIVA
        ────────────────────────────────────────── */
        .nota-box {
            margin-top: 14px;
            border: 1.5px solid #000;
            border-left: 5px solid #000;
            padding: 7px 10px;
            font-size: 7.5px;
            color: #000;
            background: #f8f8f8;
            page-break-inside: avoid;
        }
        .nota-box strong { text-decoration: underline; }

        /* ──────────────────────────────────────────
           PIE DE PÁGINA FIJO
        ────────────────────────────────────────── */
        .pdf-footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            border-top: 2px solid #000;
            padding: 4px 0 0;
            background: #fff;
            font-size: 7.5px;
        }
        .pdf-footer table { width: 100%; border-collapse: collapse; }
        .f-left  { text-align: left;  color: #000; }
        .f-mid   { text-align: center; color: #555; }
        .f-right { text-align: right;  color: #000; font-weight: bold; }
        .page-num:after { content: counter(page); }

        /* Helpers */
        .tc { text-align: center; }
        .tr { text-align: right; }
        .bold { font-weight: bold; }
        .small { font-size: 7.5px; }
        .mono { font-family: 'Courier New', monospace; }
    </style>
</head>
<body>

{{-- ══ PIE DE PÁGINA FIJO ══ --}}
<div class="pdf-footer">
    <table>
        <tr>
            <td class="f-left">CEPRE-UNAMAD &nbsp;|&nbsp; Generado por: {{ Auth::user()->nombre_completo }} &nbsp;|&nbsp; {{ $fecha_generacion }}</td>
            <td class="f-mid">Sistema de Control de Asistencia Académica</td>
            <td class="f-right">Página <span class="page-num"></span></td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════
     I. CABECERA INSTITUCIONAL
══════════════════════════════════════ --}}
<div class="hdr-outer">
    <div class="hdr-top">
        <table>
            <tr>
                <td class="hdr-logo">
                    <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="UNAMAD">
                </td>
                <td class="hdr-title">
                    <h1>Reporte Oficial de Estudiantes Inhabilitados</h1>
                    <div class="sub">CEPRE-UNAMAD — Centro Pre-Universitario — Universidad Nacional Amazónica de Madre de Dios</div>
                    <span class="ciclo-tag">{{ clean_emoji($ciclo->nombre) }} | {{ $ciclo->codigo }}</span>
                </td>
                <td class="hdr-logo" style="text-align:right;">
                    <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="CEPRE">
                </td>
            </tr>
        </table>
    </div>
    <div class="hdr-stripe"></div>
    <div class="hdr-sub-row">Documento generado el: {{ $fecha_generacion }} &nbsp;|&nbsp; Modalidad: {{ clean_emoji($modalidad_label) }} &nbsp;|&nbsp; Periodo: <strong>{{ clean_emoji($periodo_label) }}</strong></div>
</div>

{{-- ══════════════════════════════════════
     II. DATOS DEL REPORTE
══════════════════════════════════════ --}}
<div class="info-box">
    <div class="info-box-header">&#9656; Datos del Reporte</div>
    <table>
        <tr>
            <td class="info-lbl">Ciclo Académico:</td>
            <td class="info-val">{{ clean_emoji($ciclo->nombre) }} &nbsp;(Código: {{ $ciclo->codigo }})</td>
            <td class="info-lbl">Fecha de Generación:</td>
            <td class="info-val">{{ $fecha_generacion }}</td>
        </tr>
        <tr>
            <td class="info-lbl">Periodo de Cálculo:</td>
            <td class="info-val"><span class="periodo-badge">&#9658; {{ clean_emoji($periodo_label) }}</span></td>
            <td class="info-lbl">Modalidad Filtrada:</td>
            <td class="info-val bold">{{ clean_emoji($modalidad_label) }}</td>
        </tr>
        <tr>
            <td class="info-lbl">% Umbral Inhabilitación:</td>
            <td class="info-val bold">{{ $ciclo->porcentaje_inhabilitacion }}% de inasistencias</td>
            <td class="info-lbl">% Umbral Amonestación:</td>
            <td class="info-val bold">{{ $ciclo->porcentaje_amonestacion ?? 20 }}% de inasistencias</td>
        </tr>
        <tr>
            <td class="info-lbl">Total Inscritos:</td>
            <td class="info-val">{{ $total_general }} estudiantes</td>
            <td class="info-lbl">% Inhabilitados / Total:</td>
            <td class="info-val bold">{{ $resumen['porcentaje_inhabilitados'] }}%</td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════
     III. CUADRO ESTADÍSTICO
══════════════════════════════════════ --}}
<table class="stats-wrap">
    <tr>
        <td class="stat-cell st-total">
            <span class="num">{{ $total_general }}</span>
            <span class="lbl">Total Analizados</span>
        </td>
        <td class="stat-spacer"></td>
        <td class="stat-cell st-reg">
            <span class="num">{{ $total_regulares }}</span>
            <span class="lbl">Regulares</span>
        </td>
        <td class="stat-spacer"></td>
        <td class="stat-cell st-amo">
            <span class="num">{{ $total_amonestados }}</span>
            <span class="lbl">Amonestados</span>
        </td>
        <td class="stat-spacer"></td>
        <td class="stat-cell st-inh">
            <span class="num">{{ $total_inhabilitados }}</span>
            <span class="lbl">Inhabilitados</span>
        </td>
    </tr>
</table>

@php
    $sorted    = collect($inhabilitados)->sortBy(fn($i) => $i['carrera'] . $i['nombres'])->values();
    $byCarrera = $sorted->groupBy('carrera');
    $totalPost = $sorted->filter(fn($i) => ($i['tipo_inscripcion'] ?? '') === 'postulante')->count();
    $totalRefo = $sorted->filter(fn($i) => ($i['tipo_inscripcion'] ?? '') !== 'postulante')->count();
@endphp

{{-- ══════════════════════════════════════
     IV. RESUMEN POR CARRERA
══════════════════════════════════════ --}}
@if($byCarrera->isNotEmpty())
<div class="sec-title">
    <span class="sec-num">I</span> Resumen por Carrera
    <span class="sec-count">&mdash; {{ $sorted->count() }} inhabilitados en total</span>
</div>
<table class="t-summary">
    <thead>
        <tr>
            <th>Carrera / Programa Académico</th>
            <th class="tc" style="width:90px;">[P] Postulantes</th>
            <th class="tc" style="width:90px;">[R] Reforzamiento</th>
            <th class="tc" style="width:55px;">Total</th>
            <th class="tc" style="width:60px;">% del Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($byCarrera as $carreraNom => $carreraItems)
            @php
                $pc  = $carreraItems->filter(fn($i) => ($i['tipo_inscripcion'] ?? '') === 'postulante')->count();
                $rc  = $carreraItems->filter(fn($i) => ($i['tipo_inscripcion'] ?? '') !== 'postulante')->count();
                $pct = $sorted->count() > 0 ? round($carreraItems->count() / $sorted->count() * 100, 1) : 0;
            @endphp
            <tr>
                <td class="bold">{{ clean_emoji($carreraNom) }}</td>
                <td class="tc mod-post">{{ $pc }}</td>
                <td class="tc mod-refo">{{ $rc }}</td>
                <td class="tc bold">{{ $carreraItems->count() }}</td>
                <td class="tc">{{ $pct }}%</td>
            </tr>
        @endforeach
        <tr class="row-total">
            <td class="bold">TOTAL GENERAL</td>
            <td class="tc bold">{{ $totalPost }}</td>
            <td class="tc bold">{{ $totalRefo }}</td>
            <td class="tc bold">{{ $sorted->count() }}</td>
            <td class="tc bold">100%</td>
        </tr>
    </tbody>
</table>
@endif

{{-- ══════════════════════════════════════
     V. RELACIÓN DETALLADA
══════════════════════════════════════ --}}
<div class="sec-title">
    <span class="sec-num">II</span> Relación Detallada de Estudiantes Inhabilitados
    <span class="sec-count">&mdash; [P] = Postulante &nbsp; [R] = Reforzamiento &nbsp; [!] = Inhabilitado</span>
</div>

<table class="t-detail">
    <thead>
        <tr>
            <th class="tc" style="width:22px;">N°</th>
            <th style="width:*;">Apellidos y Nombres</th>
            <th class="tc" style="width:58px;">DNI</th>
            <th class="tc" style="width:65px;">Modalidad</th>
            <th style="width:55px;">Aula / Turno</th>
            <th class="tc" style="width:35px;">Faltas</th>
            <th class="tc" style="width:35px;">Límite</th>
            <th class="tc" style="width:48px;">% Inas.</th>
            <th class="tc" style="width:62px;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @if($byCarrera->isNotEmpty())
            @php $rowNum = 1; $isEven = false; @endphp
            @foreach($byCarrera as $carreraNom => $carreraItems)
                @php
                    $pc = $carreraItems->filter(fn($i) => ($i['tipo_inscripcion'] ?? '') === 'postulante')->count();
                    $rc = $carreraItems->filter(fn($i) => ($i['tipo_inscripcion'] ?? '') !== 'postulante')->count();
                @endphp
                {{-- Cabecera de carrera --}}
                <tr class="career-hdr">
                    <td colspan="9">
                        &#9654; {{ strtoupper(clean_emoji($carreraNom)) }}
                        &nbsp;&mdash;&nbsp;
                        {{ $carreraItems->count() }} estudiante{{ $carreraItems->count() > 1 ? 's' : '' }} inhabilitado{{ $carreraItems->count() > 1 ? 's' : '' }}
                        &nbsp;|&nbsp; <span class="badge badge-p">{{ $pc }}</span>
                        &nbsp; <span class="badge badge-r">{{ $rc }}</span>
                    </td>
                </tr>
                @foreach($carreraItems as $item)
                    @php
                        $isEven = !$isEven;
                        $pctInas = round(100 - $item['porcentaje'], 1);
                        $esPost  = ($item['tipo_inscripcion'] ?? '') === 'postulante';
                    @endphp
                    <tr class="{{ $isEven ? 'row-even' : 'row-odd' }}">
                        <td class="tc small" style="color:#444;">{{ $rowNum }}</td>
                        <td class="bold">{{ $item['nombres'] }}</td>
                        <td class="tc mono">{{ $item['dni'] }}</td>
                        <td class="tc">
                            @if($esPost)
                                <span class="badge badge-p">Postulante</span>
                            @else
                                <span class="badge badge-r">Reforzamiento</span>
                            @endif
                        </td>
                        <td style="font-size:7.5px;">
                            <span class="bold">{{ clean_emoji($item['aula']) }}</span><br>
                            {{ clean_emoji($item['turno']) }}
                        </td>
                        <td class="tc faltas-num">{{ $item['faltas'] }}</td>
                        <td class="tc">{{ $item['limite'] }}</td>
                        <td class="tc bold">{{ $pctInas }}%</td>
                        <td class="tc">
                            <span class="badge badge-i">Inhabilitado</span>
                        </td>
                    </tr>
                    @php $rowNum++; @endphp
                @endforeach
            @endforeach
        @else
            <tr>
                <td colspan="9" class="tc" style="padding:20px;color:#444;font-style:italic;">
                    No se encontraron estudiantes inhabilitados con los filtros seleccionados.
                </td>
            </tr>
        @endif
    </tbody>
</table>

{{-- ══════════════════════════════════════
     VI. FIRMAS Y VALIDACIÓN
══════════════════════════════════════ --}}
<div class="firma-section">
    <table class="firma-table">
        <tr>
            <td class="firma-cell">
                <div style="height:75px;"></div>
                <div class="firma-line"></div>
                <div class="firma-cargo">Director(a) CEPRE-UNAMAD</div>
                <div class="firma-nombre">&nbsp;</div>
            </td>
            <td class="firma-cell">
                <div style="height:75px;"></div>
                <div class="firma-line"></div>
                <div class="firma-cargo">Coordinador(a) Académico</div>
                <div class="firma-nombre">&nbsp;</div>
            </td>
            <td class="firma-cell">
                <div style="height:75px;"></div>
                <div class="firma-line"></div>
                <div class="firma-cargo">Responsable del Sistema</div>
                <div class="firma-nombre">{{ Auth::user()->nombre_completo }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════
     VII. NOTA LEGAL
══════════════════════════════════════ --}}
<div class="nota-box">
    <strong>Nota Informativa:</strong>
    El presente documento es generado automáticamente por el Sistema de Control de Asistencia Académica del CEPRE-UNAMAD.
    El estado de <strong>Inhabilitado</strong> se asigna al estudiante que ha superado el
    <strong>{{ $ciclo->porcentaje_inhabilitacion }}%</strong> de inasistencias al total de clases programadas.
    El estado de <strong>Amonestado</strong> corresponde a superar el
    <strong>{{ $ciclo->porcentaje_amonestacion ?? 20 }}%</strong>.
    Estos criterios se rigen según el <em>Reglamento Académico vigente del CEPRE-UNAMAD</em>.
    Los datos presentados corresponden al ciclo académico <strong>{{ clean_emoji($ciclo->nombre) }}</strong>.
    Este reporte tiene validez oficial únicamente con las firmas correspondientes.
</div>

</body>
</html>
