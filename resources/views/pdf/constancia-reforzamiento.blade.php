<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Inscripción - Reforzamiento</title>
    <style>
        @page { margin: 1cm; size: A4; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; line-height: 1.4; color: #333; }
        
        .header { position: relative; width: 100%; border-bottom: 3px solid #00aeef; padding-bottom: 15px; margin-bottom: 20px; text-align: center; }
        .header-logo { position: absolute; left: 0; top: 0; width: 90px; }
        .header-logo-right { position: absolute; right: 0; top: 0; width: 90px; }
        .header-text h1 { font-size: 14pt; margin: 0; color: #003366; text-transform: uppercase; }
        .header-text h2 { font-size: 12pt; margin: 5px 0; color: #00aeef; }
        
        .title-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; text-align: center; margin-bottom: 20px; border-radius: 8px; }
        .title-box h3 { margin: 0; font-size: 13pt; color: #ec008c; text-transform: uppercase; letter-spacing: 1px; }
        
        .main-container { display: table; width: 100%; margin-bottom: 20px; }
        .data-column { display: table-cell; width: 75%; vertical-align: top; padding-right: 20px; }
        .photo-column { display: table-cell; width: 25%; vertical-align: top; text-align: center; }
        
        .photo-box { border: 2px solid #003366; padding: 3px; display: inline-block; border-radius: 5px; background: #fff; }
        .photo-box img { width: 120px; height: 150px; object-fit: cover; display: block; }
        
        .section-title { background: #003366; color: white; padding: 5px 10px; font-weight: bold; font-size: 10pt; margin-bottom: 10px; border-radius: 4px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .info-table td.label { font-weight: bold; width: 35%; color: #555; background: #fcfcfc; }
        
        .payment-card { background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 10px; padding: 15px; margin-top: 10px; }
        
        .footer-signatures { margin-top: 50px; width: 100%; }
        .signature-box { text-align: center; width: 45%; display: inline-block; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; width: 80%; margin: 60px auto 5px; }
        .fingerprint-box { border: 1px solid #ccc; width: 70px; height: 90px; margin: 10px auto; }
        
        .watermark { position: fixed; top: 40%; left: 15%; font-size: 60pt; color: rgba(0, 0, 0, 0.03); transform: rotate(-45deg); z-index: -1; pointer-events: none; }
        .footer-document { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; font-size: 8pt; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
@php
    $nroConstancia = $inscripcion->nro_constancia ?? 'N-' . $inscripcion->id;
    $nombreAlumno = mb_strtoupper(($estudiante->apellido_paterno ?? '') . ' ' . ($estudiante->apellido_materno ?? '') . ', ' . ($estudiante->nombre ?? ''));
    
    // URL de validación oficial para que el escaneo funcione
    $urlValidacion = route('constancias.validar', 'REF-' . $estudiante->numero_documento);
    $qrCodeBase64 = base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->margin(0)->color(0, 0, 0)->generate($urlValidacion));
@endphp

<body>
    <div class="watermark">INSCRIPCIÓN REFORZAMIENTO</div>

    <div class="header">
        <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="UNAMAD">
        <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="CEPRE">
        <div class="header-text">
            <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
            <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
            <p style="margin:2px 0; font-weight:bold;">Programa de Reforzamiento Escolar {{ date('Y') }}</p>
        </div>
    </div>

    <div class="title-box">
        <h3 style="margin-bottom: 5px;">Constancia de Inscripción N° {{ $inscripcion->nro_constancia ?? 'N-'.$inscripcion->id }}</h3>
        <p style="margin: 0; font-size: 9pt; color: #003366; font-weight: bold;">{{ strtoupper($ciclo->nombre ?? 'Reforzamiento Académico Escolar') }}</p>
        @if($ciclo && $ciclo->fecha_inicio)
        <p style="margin: 5px 0 0; font-size: 8pt; color: #555;">Inicio de Actividades: {{ \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('d/m/Y') }}</p>
        @endif
    </div>

    <div class="main-container">
        <div class="data-column">
            <div class="section-title">DATOS DEL ESTUDIANTE</div>
            <table class="info-table">
                <tr>
                    <td class="label">Apellidos y Nombres:</td>
                    <td style="font-weight: bold; font-size: 11pt;">{{ mb_strtoupper(($estudiante->apellido_paterno ?? '') . ' ' . ($estudiante->apellido_materno ?? '') . ', ' . ($estudiante->nombre ?? '')) }}</td>
                </tr>
                <tr>
                    <td class="label">Documento Identidad:</td>
                    <td>DNI {{ $estudiante->numero_documento }}</td>
                </tr>
                <tr>
                    <td class="label">Grado / Turno:</td>
                    <td>{{ strtoupper($inscripcion->grado) }} Secundaria - Turno {{ strtoupper($inscripcion->turno) }}</td>
                </tr>
                <tr>
                    <td class="label">Aula Asignada:</td>
                    <td style="font-weight: bold; color: #003366; font-size: 11pt;">{{ $inscripcion->aula ? strtoupper($inscripcion->aula->nombre) : 'PENDIENTE DE ASIGNACIÓN' }}</td>
                </tr>
                <tr>
                    <td class="label">Colegio:</td>
                    <td>{{ strtoupper($inscripcion->colegio_procedencia) }}</td>
                </tr>
            </table>

            <div class="section-title">DATOS DEL PADRE O APODERADO</div>
            <table class="info-table">
                @foreach($inscripcion->apoderados as $apo)
                <tr>
                    <td class="label">{{ $apo->parentesco ?? 'Apoderado' }}:</td>
                    <td>{{ $apo->nombres }} (DNI: {{ $apo->numero_documento }}) - Cel: {{ $apo->celular }}</td>
                </tr>
                @endforeach
            </table>
        </div>
        
        <div class="photo-column">
            <div class="photo-box">
                @if($inscripcion->foto_path)
                    <img src="{{ storage_path('app/public/' . $inscripcion->foto_path) }}" alt="Foto">
                @else
                    <div style="width:120px; height:150px; background:#eee; display:flex; align-items:center; justify-content:center; color:#999;">SIN FOTO</div>
                @endif
            </div>
            
            <div style="margin-top: 15px; border: 1px solid #eee; padding: 5px; display: inline-block; background: #fff;">
                <img src="data:image/svg+xml;base64, {{ $qrCodeBase64 }} " style="width: 85px; height: 85px;">
                <p style="font-size: 7pt; margin: 5px 0 0; font-weight: bold; color: #003366;">VALIDACIÓN QR</p>
                <div style="font-size: 6pt; color: #999; margin-top: 3px; max-width: 100px; line-height: 1.1;">Veracidad verificable mediante escaneo digital</div>
            </div>
        </div>
    </div>

    <div class="section-title">DETALLES DEL PAGO</div>
    <div class="payment-card">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%;">
                    <strong>Concepto:</strong> 598 - REFORZAMIENTO ({{ strtoupper($pago->mes_pagado ?? 'GENERAL') }})<br>
                    <strong>Monto Pagado:</strong> S/. {{ number_format($pago->monto ?? 0, 2) }}
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Recibo N°:</strong> {{ $pago->numero_operacion ?? 'Pendiente' }}<br>
                    <strong>Fecha Pago:</strong> {{ $pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : '--/--/----' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-signatures" style="margin-top: 40px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 33%; text-align: center; vertical-align: bottom;">
                    <div class="signature-line" style="width: 85%; margin: 60px auto 5px;"></div>
                    <strong style="font-size: 9pt;">Firma del Estudiante</strong><br>
                    <span style="font-size: 8pt;">DNI: {{ $estudiante->numero_documento }}</span>
                </td>
                <td style="width: 34%; text-align: center; vertical-align: top;">
                    <div class="fingerprint-box" style="margin: 0 auto 5px;"></div>
                    <strong style="font-size: 9pt;">Huella Digital</strong><br>
                    <span style="font-size: 7pt;">(Índice Derecho)</span>
                </td>
                <td style="width: 33%; text-align: center; vertical-align: bottom;">
                    <div class="signature-line" style="width: 85%; margin: 60px auto 5px;"></div>
                    <strong style="font-size: 9pt;">Coordinación CEPRE</strong><br>
                    <span style="font-size: 8pt;">Dirección / Coordinador Académico</span>
                </td>
            </tr>
        </table>
    </div>


    <div class="footer-document">
        Esta constancia certifica la reserva de vacante y participación en el programa de reforzamiento académico.<br>
        Generado el {{ date('d/m/Y H:i:s') }} - Portal CEPRE UNAMAD
    </div>
</body>
</html>
