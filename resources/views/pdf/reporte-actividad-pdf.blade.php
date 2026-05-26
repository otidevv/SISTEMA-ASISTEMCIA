<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Actividad del Operador</title>
    <style>
        body {
            /* Usar DejaVu Sans para soporte completo de UTF-8 (tildes, eñes) en DomPDF */
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            line-height: 1.3;
            background-color: #ffffff;
        }
        @page {
            margin: 8px 25px 25px 25px;
        }
        .header {
            width: 100%;
            margin-bottom: 5px;
            border-bottom: 3px solid #d81b60; /* Color corporativo */
            padding-bottom: 4px;
            background-color: #ffffff;
        }
        .header table {
            width: 100%;
        }
        .logo-title-cell {
            vertical-align: middle;
            text-align: center;
        }
        .logo-text {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .sub-logo-text {
            font-size: 9px;
            color: #d81b60;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .logo-img-left {
            width: 50px;
            height: auto;
            vertical-align: middle;
        }
        .logo-img-right {
            width: 55px;
            height: auto;
            vertical-align: middle;
        }
        .qr-cell {
            text-align: right;
            vertical-align: middle;
        }
        .title-report {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #2c3e50;
            margin: 3px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Panel de Información */
        .info-panel {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 5px 15px;
            margin-bottom: 4px;
        }
        .info-panel table {
            width: 100%;
        }
        .info-label {
            font-weight: bold;
            color: #64748b;
            width: 15%;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .info-value {
            color: #1e293b;
            font-size: 9.5px;
            width: 35%;
        }
 
        /* KPIs */
        .kpis-table {
            width: 100%;
            margin-bottom: 6px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .kpi-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 4px;
            text-align: center;
            background-color: #ffffff;
        }
        .kpi-number {
            font-size: 14px;
            font-weight: bold;
            color: #d81b60;
            margin-top: 1px;
        }
        .kpi-title {
            font-size: 7.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
 
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #ffffff;
            background-color: #2c3e50;
            padding: 3px 8px;
            margin-top: 6px;
            margin-bottom: 4px;
            text-transform: uppercase;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
 
        /* DISEÑO DE TARJETAS (Cards) */
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 6px;
        }
        .grid-cell {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        
        .student-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            height: 145px;
        }
        
        .card-header-bar {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card-header-bar.reforzamiento {
            background-color: #1b5e20;
        }
        
        .card-body {
            padding: 6px;
        }
        
        .card-body table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .card-photo-cell {
            width: 60px;
            vertical-align: top;
            text-align: center;
            padding-right: 6px;
        }
        .card-photo {
            width: 55px;
            height: 65px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #94a3b8;
        }
        .card-photo-placeholder {
            width: 55px;
            height: 65px;
            border-radius: 4px;
            border: 1px dashed #94a3b8;
            background-color: #f8fafc;
            display: table;
        }
        .card-photo-placeholder span {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
        }
        
        .card-info-cell {
            vertical-align: top;
        }
        
        .card-info-table {
            width: 100%;
        }
        .card-info-table td {
            padding: 0.5px 0;
            font-size: 8px;
            line-height: 1.1;
        }
        .card-info-table td.label {
            font-weight: bold;
            color: #64748b;
            width: 75px;
        }
        .card-info-table td.value {
            color: #1e293b;
        }
        
        .badge {
            display: inline-block;
            padding: 1px 3px;
            font-size: 6px;
            font-weight: bold;
            border-radius: 2px;
            color: #ffffff;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #10b981;
        }
        .badge-primary {
            background-color: #3b82f6;
        }

        /* Firmas y Footer */
        .footer-signatures {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
            width: 45%;
            vertical-align: bottom;
        }
        .signature-line {
            width: 160px;
            border-bottom: 1px solid #94a3b8;
            margin: 0 auto 5px auto;
        }
        .signature-title {
            font-size: 8px;
            color: #64748b;
        }
    </style>
</head>
<body>

    @php
        // Función helper para limpiar emojis, símbolos especiales y signos "?" del texto
        if (!function_exists('limpiarTexto')) {
            function limpiarTexto($texto) {
                if (empty($texto)) return '';
                // Limpiar cualquier carácter al inicio que no sea letra, número o espacio (ej. emojis, símbolos, ?)
                $limpio = preg_replace('/^[^a-zA-Z0-9ÁÉÍÓÚáéíóúÑñ]+/u', '', $texto);
                return trim($limpio);
            }
        }

        // Cargar logos institucionales como Base64 para evitar errores de red en DomPDF
        $logoUnamadPath = public_path('assets/images/logo unamad constancia.png');
        $logoCeprePath = public_path('assets/images/logo cepre costancia.png');
        
        $logoUnamadBase64 = '';
        $logoCepreBase64 = '';
        
        if (file_exists($logoUnamadPath)) {
            $logoUnamadBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoUnamadPath));
        }
        if (file_exists($logoCeprePath)) {
            $logoCepreBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoCeprePath));
        }
    @endphp

    <!-- Encabezado Membretado -->
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 15%; text-align: left; vertical-align: middle;">
                    @if($logoUnamadBase64)
                        <img src="{{ $logoUnamadBase64 }}" class="logo-img-left" alt="UNAMAD">
                    @endif
                </td>
                <td class="logo-title-cell" style="width: 70%;">
                    <h1 class="logo-text">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</h1>
                    <p class="sub-logo-text">Centro de Estudios Preuniversitario - CEPRE UNAMAD</p>
                </td>
                <td class="qr-cell" style="width: 15%; vertical-align: middle; text-align: right;">
                    @if($logoCepreBase64)
                        <img src="{{ $logoCepreBase64 }}" class="logo-img-right" alt="CEPRE">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Título del Reporte -->
    <div class="title-report">Informe de Trabajo de Registro y Actividad</div>

    <!-- Datos del Operador -->
    <div class="info-panel">
        <table>
            <tr>
                <td class="info-label">Operador:</td>
                <td class="info-value text-bold" style="font-weight: bold; color: #d81b60;">{{ $operador->nombre }} {{ $operador->apellido_paterno }} {{ $operador->apellido_materno }}</td>
                <td class="info-label">Periodo:</td>
                <td class="info-value">{{ $rangoFechas }}</td>
            </tr>
            <tr>
                <td class="info-label">Rol / Cargo:</td>
                <td class="info-value">
                    @foreach($operador->roles as $role)
                        {{ strtoupper($role->nombre) }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </td>
                <td class="info-label">Ciclo Académico:</td>
                <td class="info-value">{{ $ciclo ? $ciclo->nombre : 'Todos los Ciclos' }}</td>
            </tr>
            <tr>
                <td class="info-label">Fecha Emisión:</td>
                <td class="info-value" colspan="3">{{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <!-- KPIs de Resumen -->
    <table class="kpis-table">
        <tr>
            <td class="kpi-card" style="border-left: 3px solid #d81b60;">
                <div class="kpi-title">Total Procesados</div>
                <div class="kpi-number">{{ $postulaciones->count() + $reforzamientos->count() }}</div>
            </td>
            <td class="kpi-card" style="border-left: 3px solid #3b82f6;">
                <div class="kpi-title">Postulaciones (CEPRE)</div>
                <div class="kpi-number">{{ $postulaciones->count() }}</div>
            </td>
            <td class="kpi-card" style="border-left: 3px solid #10b981;">
                <div class="kpi-title">Reforzamiento Escolar</div>
                <div class="kpi-number">{{ $reforzamientos->count() }}</div>
            </td>
            <td class="kpi-card" style="border-left: 3px solid #f59e0b;">
                <div class="kpi-title">Monto Recaudado</div>
                <div class="kpi-number">S/. {{ number_format($montoTotal, 2) }}</div>
            </td>
        </tr>
    </table>

    <!-- Detalle de Postulaciones (Ordinarias) -->
    <div class="section-title">Postulaciones Ordinarias (CEPRE)</div>
    @if($postulaciones->count() > 0)
        @php $chunks = $postulaciones->chunk(2); @endphp
        @foreach($chunks as $chunk)
            <table class="grid-table">
                <tr>
                    @foreach($chunk as $p)
                        @php
                            $imageSrc = null;
                            if (!empty($p->foto_path)) {
                                $imagePath = public_path('storage/' . $p->foto_path);
                                if (file_exists($imagePath)) {
                                    $imageSrc = 'data:image/' . pathinfo($imagePath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($imagePath));
                                }
                            }
                        @endphp
                        <td class="grid-cell">
                            <div class="student-card">
                                <div class="card-header-bar">
                                    <table style="width: 100%; border: none; margin: 0; padding: 0;">
                                        <tr>
                                            <td style="color: #ffffff; font-weight: bold; font-size: 8px; border: none; padding: 0; text-align: left; vertical-align: middle; background: none;">
                                                CÓDIGO: {{ $p->codigo_postulante ?? 'N/A' }}
                                            </td>
                                            <td style="text-align: right; border: none; padding: 0; vertical-align: middle; background: none;">
                                                <span class="badge badge-success">PAGO COMPLETO</span>
                                                <span class="badge badge-primary">APROBADO</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="card-body">
                                    <table>
                                        <tr>
                                            <td class="card-photo-cell">
                                                @if($imageSrc)
                                                    <img src="{{ $imageSrc }}" class="card-photo" alt="">
                                                @else
                                                    <div class="card-photo-placeholder">
                                                        <span>FOTO</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="card-info-cell">
                                                <table class="card-info-table">
                                                    <tr>
                                                        <td class="label">Estudiante:</td>
                                                        <td class="value text-bold" style="font-weight: bold;">{{ ($p->estudiante->nombre ?? '') . ' ' . ($p->estudiante->apellido_paterno ?? '') . ' ' . ($p->estudiante->apellido_materno ?? '') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">DNI:</td>
                                                        <td class="value">{{ $p->estudiante->numero_documento ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Carrera:</td>
                                                        <td class="value">{{ limpiarTexto($p->carrera->nombre ?? 'N/A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Turno:</td>
                                                        <td class="value">{{ limpiarTexto($p->turno->nombre ?? 'N/A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Pago Recibo:</td>
                                                        <td class="value">{{ $p->numero_recibo ?? 'N/A' }} (S/. {{ number_format($p->monto_total_pagado, 2) }})</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Registro:</td>
                                                        <td class="value">{{ $p->fecha_revision ? $p->fecha_revision->format('d/m/Y H:i') : 'N/A' }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    @endforeach
                    @if($chunk->count() == 1)
                        <td class="grid-cell" style="border: none; background: none;"></td>
                    @endif
                </tr>
            </table>
        @endforeach
    @else
        <p style="color: #7f8c8d; font-style: italic; padding-left: 12px;">No se registraron aprobaciones de postulaciones en el periodo.</p>
    @endif

    <!-- Detalle de Reforzamiento -->
    <div class="section-title">Reforzamiento Escolar</div>
    @if($reforzamientos->count() > 0)
        @php $chunksRef = $reforzamientos->chunk(2); @endphp
        @foreach($chunksRef as $chunk)
            <table class="grid-table">
                <tr>
                    @foreach($chunk as $r)
                        @php
                            $imageSrc = null;
                            if (!empty($r->foto_path)) {
                                $imagePath = public_path('storage/' . $r->foto_path);
                                if (file_exists($imagePath)) {
                                    $imageSrc = 'data:image/' . pathinfo($imagePath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($imagePath));
                                }
                            }
                            $pago = $r->pagos->where('estado_pago', 'aprobado')->first() ?? $r->pagos->first();
                        @endphp
                        <td class="grid-cell">
                            <div class="student-card">
                                <div class="card-header-bar reforzamiento">
                                    <table style="width: 100%; border: none; margin: 0; padding: 0;">
                                        <tr>
                                            <td style="color: #ffffff; font-weight: bold; font-size: 8px; border: none; padding: 0; text-align: left; vertical-align: middle; background: none;">
                                                CONSTANCIA: {{ $r->nro_constancia ?? 'N/A' }}
                                            </td>
                                            <td style="text-align: right; border: none; padding: 0; vertical-align: middle; background: none;">
                                                <span class="badge badge-success">VALIDADO</span>
                                                <span class="badge badge-primary">MATRICULADO</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="card-body">
                                    <table>
                                        <tr>
                                            <td class="card-photo-cell">
                                                @if($imageSrc)
                                                    <img src="{{ $imageSrc }}" class="card-photo" alt="">
                                                @else
                                                    <div class="card-photo-placeholder">
                                                        <span>FOTO</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="card-info-cell">
                                                <table class="card-info-table">
                                                    <tr>
                                                        <td class="label">Estudiante:</td>
                                                        <td class="value text-bold" style="font-weight: bold;">{{ ($r->estudiante->nombre ?? '') . ' ' . ($r->estudiante->apellido_paterno ?? '') . ' ' . ($r->estudiante->apellido_materno ?? '') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">DNI:</td>
                                                        <td class="value">{{ $r->estudiante->numero_documento ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Grado / Turno:</td>
                                                        <td class="value">{{ strtoupper(limpiarTexto($r->grado ?? 'N/A')) }} / {{ strtoupper(limpiarTexto($r->turno ?? 'N/A')) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Procedencia:</td>
                                                        <td class="value">{{ $r->colegio_procedencia ?? 'N/A' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Aula Asignada:</td>
                                                        <td class="value" style="font-weight: bold; color: #1b5e20;">{{ $r->aula->nombre ?? 'SIN AULA' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Pago Recibo:</td>
                                                        <td class="value">{{ $pago->numero_operacion ?? 'N/A' }} (S/. {{ number_format($pago->monto ?? 0, 2) }})</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    @endforeach
                    @if($chunk->count() == 1)
                        <td class="grid-cell" style="border: none; background: none;"></td>
                    @endif
                </tr>
            </table>
        @endforeach
    @else
        <p style="color: #7f8c8d; font-style: italic; padding-left: 12px;">No se registraron validaciones de reforzamiento en el periodo.</p>
    @endif

    <!-- QR de validación al final del reporte -->
    <div style="margin-top: 20px; text-align: center; font-size: 8px; color: #64748b;">
        @if($qrCode)
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width: 60px; height: 60px; display: block; margin: 0 auto 5px auto;" alt="QR Verification">
        @endif
        DOCUMENTO VALIDADO ELECTRÓNICAMENTE<br>
        Generado por: SGA-CEPRE UNAMAD | Operador: {{ $operador->nombre }} {{ $operador->apellido_paterno }}
    </div>

    <!-- Firmas -->
    <table class="footer-signatures">
        <tr>
            <td class="signature-box">
                <div class="signature-line"></div>
                <div class="text-bold">{{ $operador->nombre }} {{ $operador->apellido_paterno }}</div>
                <div class="signature-title">Firma del Operador / Registrador</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="signature-box">
                <div class="signature-line"></div>
                <div class="text-bold">Coordinador Académico</div>
                <div class="signature-title">Visto Bueno (V°B°) CEPRE UNAMAD</div>
            </td>
        </tr>
    </table>

    <!-- Script PHP inline para inyectar número de páginas dinámicamente en el render final de DomPDF -->
    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $pdf->page_text(480, 825, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 8, array(0.3, 0.3, 0.3));
            $pdf->page_text(30, 825, "Reporte de Actividad del Operador - CEPRE UNAMAD", $font, 8, array(0.3, 0.3, 0.3));
        }
    </script>

</body>
</html>
