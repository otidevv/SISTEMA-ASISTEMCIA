<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Recepción - {{ $solicitud->codigo }}</title>
    <style>
        @page { margin: 1.4cm; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #e2007a; padding-bottom: 8px; margin-bottom: 6px; position: relative; min-height: 60px; }
        .header h1 { margin: 0; color: #1a237e; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #666; font-size: 11px; }
        .logo-left { position: absolute; left: 0; top: 0; width: 55px; }
        .logo-right { position: absolute; right: 0; top: 0; width: 55px; }
        .logo-left img, .logo-right img { width: 55px; height: auto; }
        .titulo { text-align: center; background: #1a237e; color: #fff; padding: 8px; border-radius: 4px; font-weight: bold; letter-spacing: 1px; margin: 14px 0; }
        .box { border: 1px solid #dee2e6; border-radius: 6px; padding: 12px 14px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px 4px; vertical-align: top; }
        td.lbl { font-weight: bold; color: #1a237e; width: 32%; text-transform: uppercase; font-size: 10px; }
        .codigo { font-size: 20px; font-weight: bold; color: #e2007a; letter-spacing: 1px; }
        .nota { background: #f8f9fa; border-left: 4px solid #93c01f; padding: 8px 12px; font-size: 11px; color: #555; border-radius: 3px; }
        .firma { border-top: 1px solid #000; width: 230px; margin: 36px auto 4px; text-align: center; padding-top: 4px; font-size: 10px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-left"><img src="{{ public_path('assets/images/logo unamad constancia_optimized.png') }}" alt=""></div>
        <div class="logo-right"><img src="{{ public_path('assets/images/logo cepre costancia_optimized.png') }}" alt=""></div>
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <p>Centro Pre Universitario - CEPRE UNAMAD</p>
    </div>

    <div class="titulo">COMPROBANTE DE RECEPCIÓN DE TRÁMITE</div>

    <div class="box" style="text-align:center;">
        <div style="font-size:10px; color:#888; text-transform:uppercase;">N° de Expediente</div>
        <div class="codigo">{{ $solicitud->codigo }}</div>
    </div>

    <div class="box">
        <table>
            <tr><td class="lbl">Trámite</td><td>{{ $solicitud->tipo->nombre ?? '—' }}</td></tr>
            <tr><td class="lbl">Solicitante</td><td>{{ $solicitud->estudiante->nombre ?? '' }} {{ $solicitud->estudiante->apellido_paterno ?? '' }} {{ $solicitud->estudiante->apellido_materno ?? '' }}</td></tr>
            <tr><td class="lbl">DNI</td><td>{{ $solicitud->numero_documento }}</td></tr>
            <tr><td class="lbl">Fecha de recepción</td><td>{{ $solicitud->created_at?->format('d/m/Y H:i') }}</td></tr>
            <tr><td class="lbl">Estado</td><td>{{ strtoupper($solicitud->estado) }}</td></tr>
            @if($solicitud->serial_voucher)
                <tr><td class="lbl">Voucher de pago</td><td>{{ $solicitud->serial_voucher }} {{ $solicitud->pago_validado ? '(validado)' : '' }}</td></tr>
            @endif
            @if(!empty($solicitud->datos))
                @foreach($solicitud->datos as $k => $v)
                    <tr><td class="lbl">{{ ucfirst(str_replace('_', ' ', $k)) }}</td><td>{{ is_array($v) ? implode(', ', $v) : $v }}</td></tr>
                @endforeach
            @endif
        </table>
    </div>

    <p class="nota">
        Este comprobante acredita que su solicitud fue <b>recibida</b> por el Centro Pre Universitario - CEPRE UNAMAD.
        Conserve este documento y su código de expediente para hacer el seguimiento del trámite.
    </p>

    <table style="width:100%; margin-top:14px;">
        <tr>
            <td style="width:130px; vertical-align:top; text-align:center;">
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width:100px;height:100px;">
                <div style="font-size:8px; color:#999;">Validación digital</div>
            </td>
            <td style="vertical-align:bottom;">
                <div class="firma">Sello y firma de recepción</div>
            </td>
        </tr>
    </table>

    <div class="footer">Generado el {{ $fecha_generacion }} · Mesa de Partes Digital - CEPRE UNAMAD</div>
</body>
</html>
