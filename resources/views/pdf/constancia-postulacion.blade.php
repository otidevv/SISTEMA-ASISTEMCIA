<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Postulación - CEPRE UNAMAD</title>
    <style>
        @page {
            margin: 0.8cm 1.2cm 3cm 1.2cm;
            size: A4;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5pt;
            line-height: 1.1;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        /* Colores Institucionales */
        .text-pink { color: #ec008c; }
        .bg-pink { background-color: #ec008c; color: white; }
        .border-pink { border: 1px solid #ec008c; }
        
        .header {
            width: 100%;
            height: 90px;
            border-bottom: 2px solid #ec008c;
            margin-bottom: 10px;
            position: relative;
        }

        .header-logo-left {
            position: absolute;
            left: 0;
            top: 5px;
            width: 65px;
        }
        .header-logo-right {
            position: absolute;
            right: 0;
            top: 5px;
            width: 105px;
        }

        .header-logo-left img {
            width: 65px;
            height: auto;
        }
        .header-logo-right img {
            width: 105px;
            height: auto;
        }
        .header-texto {
            width: 100%;
            text-align: center;
            padding-top: 5px;
        }
        .header-texto h1 {
            color: #2b5a6f;
            margin: 0;
            font-size: 11pt;
            text-transform: uppercase;
            font-weight: 800;
        }
        .header-texto h2 {
            color: #ec008c;
            margin: 1px 0;
            font-size: 14pt;
            font-weight: 900;
        }
        .header-texto .ciclo-nombre {
            font-size: 9pt;
            color: #444;
            margin: 0;
            font-weight: bold;
        }



        .codigo-container {
            margin: 10px 0;
            text-align: center;
        }
        .codigo-postulante {
            display: inline-block;
            background-color: #ec008c;
            padding: 8px 25px;
            border-radius: 8px;
            font-size: 15pt;
            font-weight: 800;
            color: white;
        }
        .codigo-label {
            display: block;
            font-size: 8pt;
            color: #2b5a6f;
            margin-bottom: 3px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .section-title {
            background-color: #2b5a6f;
            color: white;
            padding: 4px 10px;
            font-size: 9pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 8px;
            border-left: 4px solid #ec008c;
        }

        
        .grid-container {
            width: 100%;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .info-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f1f1;
            vertical-align: middle;
        }
        .info-table td.label {
            font-weight: bold;
            width: 25%;
            color: #2b5a6f;
            background: #f8fbfd;
            font-size: 8pt;
        }

        /* Foto del postulante */
        .photo-box {
            border: 2px solid #ec008c;
            padding: 4px;
            width: 100px;
            height: 125px;
            text-align: center;
            background: white;
            border-radius: 4px;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .flex-row {
            display: table;
            width: 100%;
        }
        .flex-col {
            display: table-cell;
            vertical-align: top;
        }

        .importante-box {
            background: #fff8fb;
            border: 1px solid #ffcce6;
            padding: 10px 15px;
            margin-top: 20px;
            border-radius: 8px;
        }
        .importante-box h4 {
            margin: 0 0 5px 0;
            color: #ec008c;
            font-size: 9pt;
        }
        .importante-box ul {
            margin: 0;
            padding-left: 20px;
        }
        .importante-box li {
            font-size: 8pt;
            color: #444;
            margin-bottom: 3px;
        }

        .compromiso-text {
            font-size: 8.5pt;
            text-align: justify;
            margin: 15px 0;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 6px;
            border-left: 4px solid #ec008c;
            line-height: 1.4;
        }

        .firma-table {
            width: 100%;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        .firma-celda {
            width: 50%;
            text-align: center;
        }
        .firma-espacio {
            border: 1px solid #777;
            height: 90px;
            width: 200px;
            margin: 0 auto 5px auto;
            background-color: transparent;
        }



        .footer-qr-container {
            position: fixed;
            bottom: -2.4cm;
            left: 0;
            right: 0;
            height: 2.2cm;
            border-top: 1px solid #eee;
            padding-top: 10px;
            width: 100%;
        }

        .qr-code-img {
            width: 70px;
            height: 70px;
        }
        .qr-code-img svg {
            width: 70px;
            height: 70px;
        }

        
        .footer-text {
            font-size: 7.5pt;
            color: #666;
            text-align: left;
        }
        .valid-info {
            font-style: italic;
            color: #ec008c;
            font-weight: bold;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-logo-left">
            <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="Logo UNAMAD">
        </div>
        <div class="header-logo-right">
            <img src="{{ public_path('assets_cepre/img/logo/logo2_0.png') }}" alt="Logo CEPRE">
        </div>
        <div class="header-texto">
            <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
            <h2>CENTRO PREUNIVERSITARIO - CEPRE</h2>
            <div class="ciclo-nombre">CONSTANCIA DE INSCRIPCIÓN: {{ $ciclo->nombre }}</div>
        </div>
    </div>



    <div class="codigo-container">
        <span class="codigo-label">Identificador Único del Postulante</span>
        <div class="codigo-postulante">
            CÓDIGO DE POSTULANTE: {{ $codigo_postulante }}
        </div>
    </div>


    <!-- Datos Postulante y Foto -->
    <div class="section-title">Datos Personales del Postulante</div>
    <div class="flex-row">
        <div class="flex-col" style="width: 78%;">
            <table class="info-table">
                <tr>
                    <td class="label">Apellidos y Nombres:</td>
                    <td><strong>{{ strtoupper($estudiante->apellido_paterno) }} {{ strtoupper($estudiante->apellido_materno) }}, {{ strtoupper($estudiante->nombre) }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Documento de Identidad:</td>
                    <td>{{ $estudiante->numero_documento }} (DNI)</td>
                </tr>
                <tr>
                    <td class="label">Correo y Teléfono:</td>
                    <td>{{ $estudiante->email }} / {{ $estudiante->telefono ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="label">Dirección:</td>
                    <td>{{ $estudiante->direccion ?? '---' }}</td>
                </tr>
            </table>
        </div>
        <div class="flex-col" style="width: 22%; text-align: right;">
            <div class="photo-box" style="float: right;">
                @php
                    $fotoPath = $postulacion->foto_path ?: $postulacion->foto_carnet_path ?: $postulacion->estudiante->foto_perfil ?? null;
                    $rutaFoto = null;
                    if(!empty($fotoPath)){
                        $posiblesRutas = [public_path('storage/'.$fotoPath), storage_path('app/public/'.$fotoPath), storage_path('app/'.$fotoPath)];
                        foreach ($posiblesRutas as $ruta) { if (file_exists($ruta)) { $rutaFoto = $ruta; break; } }
                    }
                @endphp
                @if($rutaFoto)
                    <img src="{{ $rutaFoto }}" alt="Foto">
                @else
                    <div style="font-size: 6pt; padding-top: 30px;">FOTO DEL<br>POSTULANTE</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Datos Postulación -->
    <div class="section-title">Información de la Postulación Académica</div>
    <table class="info-table">
        <tr>
            <td class="label">Carrera Profesional:</td>
            <td><strong>{{ $carrera_nombre }}</strong></td>
            <td class="label">Turno / Aula:</td>
            <td>{{ $turno_nombre }} / {{ $aula_nombre }}</td>

        </tr>

        <tr>
            <td class="label">Tipo de Inscripción:</td>
            <td>{{ strtoupper($postulacion->tipo_inscripcion) }}</td>
            <td class="label">Fecha / Hora:</td>
            <td>{{ $postulacion->fecha_postulacion->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <!-- Datos Apoderados -->
    <div class="section-title">Datos de Contacto (Padres / Apoderados)</div>
    @php
        $padre = \App\Models\Parentesco::where('estudiante_id', $estudiante->id)->where('tipo_parentesco', 'Padre')->with('padre')->first();
        $madre = \App\Models\Parentesco::where('estudiante_id', $estudiante->id)->where('tipo_parentesco', 'Madre')->with('padre')->first();
    @endphp
    <table class="info-table">
        @if($padre && $padre->padre)
            <tr>
                <td class="label">Nombre Padre:</td>
                <td>{{ $padre->padre->nombre }} {{ $padre->padre->apellido_paterno }}</td>
                <td class="label">Teléfono Padre:</td>
                <td>{{ $padre->padre->telefono }}</td>
            </tr>
        @endif
        @if($madre && $madre->padre)
            <tr>
                <td class="label">Nombre Madre:</td>
                <td>{{ $madre->padre->nombre }} {{ $madre->padre->apellido_paterno }}</td>
                <td class="label">Teléfono Madre:</td>
                <td>{{ $madre->padre->telefono }}</td>
            </tr>
        @endif
        @if(!$padre && !$madre)
            <tr><td colspan="4" style="text-align: center; color: #888;">No se registraron datos de apoderados</td></tr>
        @endif
    </table>

    <!-- Compromiso -->
    <div class="compromiso-text">
        <strong>DECLARACIÓN JURADA:</strong> Yo, <strong>{{ strtoupper($estudiante->nombre) }} {{ strtoupper($estudiante->apellido_paterno) }} {{ strtoupper($estudiante->apellido_materno) }}</strong>, identificado(a) con el documento arriba descrito, declaro bajo juramento que los datos proporcionados son verídicos. Me comprometo a cumplir con el reglamento del CEPRE durante mi permanencia académica.
    </div>



    <!-- Firmas -->
    <table class="firma-table">
        <tr>
            <td class="firma-celda">
                <div class="firma-espacio"></div>
                <div style="font-size: 7.5pt; color: #777; margin-bottom: 3px;">Firma del Postulante</div>
                <div style="font-size: 7.5pt;"><strong>{{ strtoupper($estudiante->nombre) }} {{ strtoupper($estudiante->apellido_paterno) }} {{ strtoupper($estudiante->apellido_materno) }}</strong><br>DNI: {{ $estudiante->numero_documento }}</div>

            </td>
            <td class="firma-celda">
                <div class="firma-espacio" style="width: 80px;"></div>
                <div style="font-size: 7.5pt; color: #777; margin-bottom: 3px;">Huella Digital</div>
                <div style="font-size: 7pt; color: #666;">Índice Derecho</div>
            </td>
        </tr>
    </table>


    <div class="importante-box">
        <h4>INDICACIONES IMPORTANTES:</h4>
        <ul>
            <li>Debe imprimirlo, firmarlo, colocar su huella digital y subirlo escaneado al sistema institucional.</li>

            <li>Cualquier alteración o falsedad en los datos anulará automáticamente su inscripción.</li>
        </ul>
    </div>

    <!-- Footer con QR -->
    <div class="footer-qr-container">
        <table style="width: 100%;">
            <tr>
                <td style="width: 80px; vertical-align: top;">
                    <div class="qr-code-img">
                        @if(!empty($qr_code))
                            <img src="data:image/png;base64,{{ $qr_code }}" style="width: 70px; height: 70px;">
                        @endif
                    </div>

                </td>

                <td style="vertical-align: middle; padding-left: 10px;">
                    <div class="footer-text">
                        <strong>DOCUMENTO VALIDADO ELECTRÓNICAMENTE</strong><br>
                        Código de Verificación: {{ $codigo_verificacion }}<br>
                        Fecha de Generación: {{ $fecha_generacion }}<br>
                        <span class="valid-info">Escanee el código QR para verificar la autenticidad de esta constancia.</span>
                    </div>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <div style="font-size: 6.5pt; color: #aaa;">Generado por: SGA-CEPRE UNAMAD</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>