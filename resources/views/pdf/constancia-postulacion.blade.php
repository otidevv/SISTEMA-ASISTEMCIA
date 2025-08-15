<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Postulación</title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            position: relative;
        }
        
        /* Header con logos a ambos lados y texto centrado */
        .header {
            position: relative;
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            text-align: center;
        }
        
        .header-logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 100px;
            text-align: left;
            padding-right: 10px;
        }
        
        .header-logo img {
            width: 80px;
            height: auto;
            max-width: 80px;
        }
        
        .header-logo-derecho {
            position: absolute;
            right: 0;
            top: 0;
            width: 100px;
            text-align: right;
            padding-left: 10px;
        }
        
        .header-logo-derecho img {
            width: 80px;
            height: auto;
            max-width: 80px;
        }
        
        .header-texto {
            display: inline-block;
            text-align: center;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin: 3px 0;
            font-size: 14pt;
            text-transform: uppercase;
        }
        h2 {
            color: #555;
            margin: 3px 0;
            font-size: 11pt;
        }
        .codigo-postulante {
            background: #f0f0f0;
            padding: 5px;
            border-radius: 3px;
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 8px 0;
        }
        .info-section {
            margin: 8px 0;
        }
        .info-section h3 {
            background: #333;
            color: white;
            padding: 4px 8px;
            margin: 0 0 5px 0;
            font-size: 10pt;
        }
        
        /* Sección de datos del postulante con foto */
        .postulante-container {
            display: table;
            width: 100%;
            margin: 8px 0;
        }
        
        .postulante-datos {
            display: table-cell;
            width: 70%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .postulante-foto {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: center;
            padding-left: 10px;
        }
        
        .foto-container {
            border: 2px solid #333;
            padding: 4px;
            background: white;
            display: inline-block;
            border-radius: 3px;
        }
        
        .foto-estudiante {
            width: 90px;
            height: 110px;
            object-fit: cover;
            display: block;
        }
        
        .foto-placeholder {
            width: 90px;
            height: 110px;
            background: #f8f9fa;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7pt;
            color: #666;
            text-align: center;
            line-height: 1.1;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #ddd;
            font-size: 8pt;
            vertical-align: top;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 35%;
            background: #f9f9f9;
        }
        .firma-section {
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .firma-section h3 {
            text-align: center;
            margin-bottom: 6px;
            font-size: 10pt;
        }
        .firma-section p {
            font-size: 8pt;
            margin: 5px 0;
        }
        .firma-box {
            border: 1px solid #333;
            padding: 10px;
            margin: 6px 0;
            min-height: 72px;
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
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #333;
            font-size: 8pt;
            color: #666;
        }
        .importante {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 6px;
            margin: 6px 0;
            border-radius: 3px;
        }
        .importante h4 {
            margin: 0 0 4px 0;
            color: #856404;
            font-size: 9pt;
        }
        .importante ul {
            margin: 3px 0;
            padding-left: 18px;
        }
        .importante li {
            font-size: 8pt;
            margin: 2px 0;
        }
        .codigo-verificacion {
            text-align: right;
            font-size: 7pt;
            color: #999;
            margin-top: 6px;
        }
        
        /* Pie de página */
        .pie-pagina {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 7pt;
            color: #666;
            text-align: center;
        }
        
        .pie-pagina .validez {
            margin-bottom: 3px;
            font-style: italic;
        }
        
        .pie-pagina .codigo {
            font-weight: bold;
            color: #999;
        }
        
        /* Para impresión, asegurar que el layout funcione */
        @media print {
            .header {
                position: relative;
                width: 100%;
                text-align: center;
            }
            .header-logo {
                position: absolute;
                left: 0;
                top: 0;
                width: 100px;
            }
            .header-logo-derecho {
                position: absolute;
                right: 0;
                top: 0;
                width: 100px;
            }
            .header-texto {
                display: inline-block;
                text-align: center;
                width: 100%;
            }
            .postulante-container {
                display: table;
                width: 100%;
            }
            .postulante-datos {
                display: table-cell;
                width: 75%;
                vertical-align: top;
            }
            .postulante-foto {
                display: table-cell;
                width: 25%;
                vertical-align: top;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header con logos a ambos lados -->
    <div class="header">
        <div class="header-logo">
            
            <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="Logo UNAMAD">
        </div>
        <div class="header-logo-derecho">
            <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="Logo CEPRE">
        </div>
        <div class="header-texto">
            <h1 style="font-size: 13pt; margin: 2px 0;">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</h1>
            <h1>CENTRO PREUNIVERSITARIO</h1>
            <h2>CONSTANCIA DE POSTULACIÓN</h2>
            <p style="margin: 1px 0 0 0;">{{ $ciclo->nombre }}</p>
        </div>
    </div>

    <div class="codigo-postulante">
        CÓDIGO DE POSTULANTE: {{ $codigo_postulante }}
    </div>

    <div class="info-section">
        <h3>DATOS DEL POSTULANTE</h3>
        <div class="postulante-container">
            <div class="postulante-datos">
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
            <div class="postulante-foto">
                <div class="foto-container">
                    @php
                        // Usar foto_path (nuevo) o foto_carnet_path (antiguo) o foto_perfil del estudiante
                        $fotoPath = $postulacion->foto_path ?: $postulacion->foto_carnet_path ?: $postulacion->estudiante->foto_perfil ?? null;
                    @endphp
                    
                    @if(!empty($fotoPath))
                        @php
                            // Intentar diferentes rutas de la imagen
                            $rutaFoto = null;
                            $posiblesRutas = [
                                public_path('storage/' . $fotoPath),
                                storage_path('app/public/' . $fotoPath),
                                storage_path('app/' . $fotoPath)
                            ];
                            
                            foreach ($posiblesRutas as $ruta) {
                                if (file_exists($ruta)) {
                                    $rutaFoto = $ruta;
                                    break;
                                }
                            }
                        @endphp
                        
                        @if($rutaFoto)
                            <img src="{{ $rutaFoto }}" 
                                 alt="Foto del estudiante" 
                                 class="foto-estudiante">
                        @else
                            <div class="foto-placeholder">
                                FOTO NO<br>ENCONTRADA<br><br>
                                <small>Archivo:<br>{{ basename($fotoPath) }}</small>
                            </div>
                        @endif
                    @else
                        <div class="foto-placeholder">
                            FOTO<br>DEL<br>ESTUDIANTE<br><br>
                            <small>No subida</small>
                        </div>
                    @endif
                </div>
                <div style="font-size: 7pt; margin-top: 4px; color: #666;">
                    Foto Postulante
                </div>
            </div>
        </div>
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
                <td>Aula Asignada:</td>
                <td>
                    @if($postulacion->aula)
                        {{ $postulacion->aula->codigo }} - {{ $postulacion->aula->nombre }}
                    @elseif($postulacion->estado === 'aprobado')
                        @php
                            $inscripcion = \App\Models\Inscripcion::where('estudiante_id', $postulacion->estudiante_id)
                                ->where('ciclo_id', $postulacion->ciclo_id)
                                ->where('carrera_id', $postulacion->carrera_id)
                                ->with('aula')
                                ->first();
                        @endphp
                        @if($inscripcion && $inscripcion->aula)
                            {{ $inscripcion->aula->codigo }} - {{ $inscripcion->aula->nombre }}
                        @else
                            Procesando asignación
                        @endif
                    @else
                        Pendiente de aprobación
                    @endif
                </td>
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

    <div class="info-section">
        <h3>DATOS DEL PADRE/MADRE DE FAMILIA</h3>
        @php
            // Buscar datos del padre y madre a través de la relación parentesco
            $padre = \App\Models\Parentesco::where('estudiante_id', $estudiante->id)
                ->where('tipo_parentesco', 'Padre')
                ->with('padre')
                ->first();
            
            $madre = \App\Models\Parentesco::where('estudiante_id', $estudiante->id)
                ->where('tipo_parentesco', 'Madre')
                ->with('padre')
                ->first();
        @endphp
        
        <table class="info-table">
            @if($padre && $padre->padre)
            <tr>
                <td>Nombre del Padre:</td>
                <td>{{ $padre->padre->nombre }} {{ $padre->padre->apellido_paterno }} {{ $padre->padre->apellido_materno }}</td>
            </tr>
            <tr>
                <td>Teléfono del Padre:</td>
                <td>{{ $padre->padre->telefono }}</td>
            </tr>
            @endif
            
            @if($madre && $madre->padre)
            <tr>
                <td>Nombre de la Madre:</td>
                <td>{{ $madre->padre->nombre }} {{ $madre->padre->apellido_paterno }} {{ $madre->padre->apellido_materno }}</td>
            </tr>
            <tr>
                <td>Teléfono de la Madre:</td>
                <td>{{ $madre->padre->telefono }}</td>
            </tr>
            @endif
            
            @if(!$padre && !$madre)
            <tr>
                <td colspan="2" style="text-align: center; color: #666;">
                    Información de padres no disponible
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div class="importante">
        <h4>IMPORTANTE:</h4>
        <ul style="margin: 5px 0;">
            <li>Esta constancia debe ser impresa, firmada y debe colocar su huella digital en el espacio indicado.</li>
            <li>Luego debe escanear o fotografiar el documento firmado y subirlo al sistema para completar su postulación.</li>
            <li>Conserve una copia de este documento para futuras referencias.</li>
        </ul>
    </div>

    <div class="firma-section">
        <h3>DECLARACIÓN Y COMPROMISO</h3>
        <p style="text-align: justify;">
            Yo, <strong>{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}</strong>, 
            identificado(a) con DNI N° <strong>{{ $estudiante->numero_documento }}</strong>, declaro que los datos 
            consignados son verídicos y me comprometo a cumplir con las normas del Centro Preuniversitario.
        </p>
        
        <table style="width: 100%; margin-top: 8px;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div class="firma-box">
                        <p>Firma del Postulante</p>
                    </div>
                    <div class="firma-texto" style="margin-top: 4px;">
                        {{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}<br>
                        DNI: {{ $estudiante->numero_documento }}
                    </div>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    <div class="firma-box">
                        <p>Huella Digital</p>
                    </div>
                    <div class="firma-texto" style="margin-top: 4px;">
                        Índice Derecho
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Fecha de generación:</strong> {{ $fecha_generacion }}</p>
    </div>

    <!-- Pie de página -->
    <div class="pie-pagina">
        <div class="validez">Este documento es válido únicamente con firma y huella del postulante</div>
        <div class="codigo">Código de verificación: {{ $codigo_verificacion }}</div>
    </div>
</body>
</html>