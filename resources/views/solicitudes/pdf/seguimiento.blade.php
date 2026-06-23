<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Seguimiento - {{ $solicitud->codigo }}</title>
    <style>
        @page { margin: 1.2cm; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 3px solid #e2007a; padding-bottom: 8px; margin-bottom: 14px; position: relative; min-height: 60px; }
        .header h1 { margin: 0; color: #1a237e; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #666; font-size: 11px; }
        .header .sub { color: #e2007a; font-weight: bold; font-size: 13px; margin-top: 6px; }
        .logo-left { position: absolute; left: 0; top: 0; width: 55px; }
        .logo-right { position: absolute; right: 0; top: 0; width: 55px; }
        .logo-left img, .logo-right img { width: 55px; height: auto; }
        .box { border: 1px solid #dee2e6; border-radius: 4px; padding: 8px 10px; margin-bottom: 12px; }
        .box h3 { margin: 0 0 6px; font-size: 12px; color: #1a237e; border-left: 4px solid #e2007a; padding-left: 6px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 3px 4px; vertical-align: top; }
        .info td.lbl { font-weight: bold; color: #1a237e; width: 28%; text-transform: uppercase; font-size: 10px; }
        .tl td { padding: 5px 4px; border-bottom: 1px solid #eee; font-size: 10px; }
        .tl th { background: #1a237e; color: #fff; padding: 5px 4px; font-size: 10px; text-align: left; }
        .badge { background: #1a237e; color: #fff; padding: 1px 6px; border-radius: 8px; font-size: 9px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 4px; }
        .firma { margin-top: 24px; }
        .firma .linea { border-top: 1px solid #000; width: 220px; margin: 30px auto 4px; text-align: center; padding-top: 4px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-left"><img src="{{ public_path('assets/images/logo unamad constancia_optimized.png') }}" alt=""></div>
        <div class="logo-right"><img src="{{ public_path('assets/images/logo cepre costancia_optimized.png') }}" alt=""></div>
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <p>Centro Pre Universitario - CEPRE UNAMAD</p>
        <p class="sub">CONSTANCIA DE SEGUIMIENTO DE TRÁMITE</p>
    </div>

    <div class="box">
        <table class="info">
            <tr><td class="lbl">Expediente</td><td><b>{{ $solicitud->codigo }}</b></td>
                <td class="lbl">Estado</td><td><span class="badge">{{ strtoupper($solicitud->estado) }}</span></td></tr>
            <tr><td class="lbl">Trámite</td><td>{{ $solicitud->tipo->nombre ?? '—' }}</td>
                <td class="lbl">Fecha</td><td>{{ $solicitud->created_at?->format('d/m/Y H:i') }}</td></tr>
            <tr><td class="lbl">Solicitante</td><td colspan="3">{{ $solicitud->estudiante->nombre ?? '' }} {{ $solicitud->estudiante->apellido_paterno ?? '' }} {{ $solicitud->estudiante->apellido_materno ?? '' }} — DNI {{ $solicitud->numero_documento }}</td></tr>
            @if($solicitud->serial_voucher)
                <tr><td class="lbl">Pago</td><td colspan="3">{{ $solicitud->serial_voucher }} {{ $solicitud->pago_validado ? '(validado)' : '(sin validar)' }}</td></tr>
            @endif
            @if($solicitud->vbDirector)
                <tr><td class="lbl">V°B° Director</td><td colspan="3">{{ $solicitud->vbDirector->nombre }} {{ $solicitud->vbDirector->apellido_paterno }} · {{ $solicitud->vb_director_at?->format('d/m/Y H:i') }}</td></tr>
            @endif
        </table>
    </div>

    @if(!empty($solicitud->datos))
        <div class="box">
            <h3>Detalle</h3>
            <table class="info">
                @foreach($solicitud->datos as $k => $v)
                    <tr><td class="lbl">{{ ucfirst(str_replace('_', ' ', $k)) }}</td><td>{{ is_array($v) ? implode(', ', $v) : $v }}</td></tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="box">
        <h3>Seguimiento del Expediente</h3>
        <table class="tl">
            <thead><tr><th>Fecha</th><th>Estado</th><th>Detalle</th><th>Responsable</th></tr></thead>
            <tbody>
                @foreach($solicitud->historial->sortBy('created_at') as $h)
                    <tr>
                        <td>{{ $h->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ strtoupper($h->estado_nuevo) }}</td>
                        <td>{{ $h->comentario }}</td>
                        <td>{{ $h->usuario ? ($h->usuario->nombre.' '.$h->usuario->apellido_paterno) : 'Sistema' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($solicitud->derivaciones->count())
        <div class="box">
            <h3>Derivaciones (Hoja de Trámite)</h3>
            <table class="tl">
                <thead><tr><th>Fecha</th><th>De</th><th>A</th><th>Acción</th></tr></thead>
                <tbody>
                    @foreach($solicitud->derivaciones->sortBy('created_at') as $d)
                        <tr>
                            <td>{{ $d->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $d->deUsuario ? ($d->deUsuario->nombre.' '.$d->deUsuario->apellido_paterno) : '—' }}</td>
                            <td>{{ $d->usuarioDestino ? ($d->usuarioDestino->nombre.' '.$d->usuarioDestino->apellido_paterno) : ($d->rolDestino->nombre ?? '—') }}</td>
                            <td>{{ \App\Models\SolicitudDerivacion::ACCIONES[$d->accion] ?? $d->accion }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <table style="width:100%; margin-top:10px;">
        <tr>
            <td style="width:130px; vertical-align:top;">
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" style="width:100px;height:100px;">
                <div style="font-size:8px; color:#999;">Validación digital</div>
            </td>
            <td style="vertical-align:bottom;">
                <div class="firma"><div class="linea">Sello y firma</div></div>
            </td>
        </tr>
    </table>

    <div class="footer">Generado el {{ $fecha_generacion }} · Mesa de Partes Digital - CEPRE UNAMAD</div>
</body>
</html>
