<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Postulación</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        h1 {
            color: #333;
            margin: 5px 0;
            font-size: 14pt;
            text-transform: uppercase;
        }
        h2 {
            color: #555;
            margin: 5px 0;
            font-size: 11pt;
        }
        .codigo-postulante {
            background: #f0f0f0;
            padding: 5px;
            border-radius: 3px;
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-section {
            margin: 10px 0;
        }
        .info-section h3 {
            background: #333;
            color: white;
            padding: 3px 8px;
            margin: 0 0 5px 0;
            font-size: 10pt;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 8pt;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 35%;
            background: #f9f9f9;
        }
        .firma-section {
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .firma-section h3 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        .firma-section p {
            font-size: 8pt;
            margin: 8px 0;
        }
        .firma-box {
            border: 1px solid #333;
            padding: 15px;
            margin: 10px 0;
            min-height: 60px;
        }
        .firma-box p {
            margin: 0;
            font-size: 8pt;
            color: #666;
        }
        .firma-texto {
            text-align: center;
            font-size: 8pt;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #333;
            font-size: 8pt;
            color: #666;
        }
        .importante {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 8px;
            margin: 10px 0;
            border-radius: 3px;
        }
        .importante h4 {
            margin: 0 0 5px 0;
            color: #856404;
            font-size: 9pt;
        }
        .importante ul {
            margin: 3px 0;
            padding-left: 20px;
        }
        .importante li {
            font-size: 8pt;
            margin: 2px 0;
        }
        .codigo-verificacion {
            text-align: right;
            font-size: 7pt;
            color: #999;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CENTRO PREUNIVERSITARIO</h1>
        <h2>CONSTANCIA DE POSTULACIÓN</h2>
        <p>{{ $ciclo->nombre }}</p>
    </div>

    <div class="codigo-postulante">
        CÓDIGO DE POSTULANTE: {{ $codigo_postulante }}
    </div>

    <div class="info-section">
        <h3>DATOS DEL POSTULANTE</h3>
        <table class="info-table">
            <tr>
                <td>Nombres y Apellidos:</td>
                <td>{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}</td>
            </tr>
            <tr>
                <td>DNI:</td>
                <td>{{ $estudiante->numero_documento }}</td>
            </tr>
            <tr>
                <td>Correo Electrónico:</td>
                <td>{{ $estudiante->email }}</td>
            </tr>
            <tr>
                <td>Teléfono:</td>
                <td>{{ $estudiante->telefono ?? 'No registrado' }}</td>
            </tr>
            <tr>
                <td>Dirección:</td>
                <td>{{ $estudiante->direccion ?? 'No registrado' }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h3>DATOS DE LA POSTULACIÓN</h3>
        <table class="info-table">
            <tr>
                <td>Carrera:</td>
                <td>{{ $carrera->nombre }}</td>
            </tr>
            <tr>
                <td>Turno:</td>
                <td>{{ $turno->nombre }}</td>
            </tr>
            <tr>
                <td>Tipo de Inscripción:</td>
                <td>{{ ucfirst($postulacion->tipo_inscripcion) }}</td>
            </tr>
            <tr>
                <td>Fecha de Postulación:</td>
                <td>{{ $postulacion->fecha_postulacion->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Estado:</td>
                <td>{{ ucfirst($postulacion->estado) }}</td>
            </tr>
        </table>
    </div>

    <div class="importante">
        <h4>IMPORTANTE:</h4>
        <ul style="margin: 5px 0;">
            <li>Esta constancia debe ser impresa, firmada y debe colocar su huella digital en el espacio indicado.</li>
            <li>Luego debe escanear o fotografiar el documento firmado y subirlo al sistema para completar su postulación.</li>
            <li>Conserve una copia de este documento para futuras referencias.</li>
            <li>Su código de postulante ({{ $codigo_postulante }}) es único y personal.</li>
        </ul>
    </div>

    <div class="firma-section">
        <h3>DECLARACIÓN Y COMPROMISO</h3>
        <p style="text-align: justify;">
            Yo, <strong>{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}</strong>, 
            identificado(a) con DNI N° <strong>{{ $estudiante->numero_documento }}</strong>, declaro que los datos 
            consignados son verídicos y me comprometo a cumplir con las normas del Centro Preuniversitario.
        </p>
        
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div class="firma-box">
                        <p>Firma del Postulante</p>
                    </div>
                    <div class="firma-texto" style="margin-top: 10px;">
                        {{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}<br>
                        DNI: {{ $estudiante->numero_documento }}
                    </div>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div class="firma-box">
                        <p>Huella Digital</p>
                    </div>
                    <div class="firma-texto" style="margin-top: 10px;">
                        Índice Derecho
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Fecha de generación:</strong> {{ $fecha_generacion }}</p>
        <p>Este documento es válido únicamente con firma y huella del postulante.</p>
    </div>

    <div class="codigo-verificacion">
        Código de verificación: {{ $codigo_verificacion }}
    </div>
</body>
</html>