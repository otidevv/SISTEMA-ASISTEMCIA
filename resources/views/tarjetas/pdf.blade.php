<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Etiquetas de Examen - UNAMAD</title>
    
    <style>
        /* Variables y configuración de color principal */
        :root {
            --color-unama-blue: #0A3C59;
            --color-tema-p: #0d6efd;
            --color-tema-q: #198754;
            --color-tema-r: #ffc107;
        }
        
        /* CONFIGURACIÓN BASE Y PÁGINA A4 HORIZONTAL */
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-weight: 500;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* CONTENEDOR PRINCIPAL */
        .tarjetas-container {
            width: 100%;
        }

        /* FILA DE TARJETAS (3 por fila en A4 horizontal) */
        .tarjeta-fila {
            width: 100%;
            margin-bottom: 0;
            page-break-inside: avoid;
        }

        /* WRAPPER CON LÍNEAS DE CORTE - MÁS ANCHO */
        .tarjeta-wrapper {
            display: inline-block;
            width: 9.3cm;
            height: 6.2cm;
            vertical-align: top;
            position: relative;
            page-break-inside: avoid;
            margin-right: 2mm;
        }
        
        .tarjeta-wrapper:nth-child(3n) {
            margin-right: 0;
        }

        /* LÍNEAS DE CORTE (Esquinas) */
        .linea-corte {
            position: absolute;
            background-color: #666;
        }
        
        /* Líneas horizontales */
        .linea-corte-h {
            height: 0.5px;
            width: 8mm;
        }
        
        /* Líneas verticales */
        .linea-corte-v {
            width: 0.5px;
            height: 8mm;
        }
        
        /* Posiciones de las líneas */
        .corte-tl-h { top: 0; left: 0; }
        .corte-tl-v { top: 0; left: 0; }
        .corte-tr-h { top: 0; right: 0; }
        .corte-tr-v { top: 0; right: 0; }
        .corte-bl-h { bottom: 0; left: 0; }
        .corte-bl-v { bottom: 0; left: 0; }
        .corte-br-h { bottom: 0; right: 0; }
        .corte-br-v { bottom: 0; right: 0; }

        /* TARJETA BASE - MUCHO MÁS ANCHA */
        .tarjeta {
            width: 9cm;
            height: 5.8cm;
            border: 1px dashed #999;
            margin: 2mm;
            padding: 0;
            font-size: 10px;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Franja de Color Lateral - EXTRA ANCHA */
        .franja-tema {
            position: absolute;
            left: 0;
            top: 0;
            width: 35px;
            height: 100%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .tarjeta-p .franja-tema { background-color: #0d6efd; }
        .tarjeta-q .franja-tema { background-color: #198754; }
        .tarjeta-r .franja-tema { background-color: #ffc107; }

        /* INDICADOR DE TEMA EN LA FRANJA - EXTRA GRANDE */
        .tema-indicator {
            position: absolute;
            left: 0;
            top: 50%;
            width: 35px;
            transform: translateY(-50%);
            text-align: center;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .tema-indicator .tema-letra {
            font-size: 56px;
            font-weight: 900;
            line-height: 1;
            color: white;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
            display: block;
        }
        
        .tema-indicator .tema-label {
            font-size: 8px;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            display: block;
            margin-top: 6px;
            letter-spacing: 1px;
        }

        /* Contenedor principal de datos */
        .contenido-principal {
            margin-left: 35px;
            height: 100%;
            position: relative;
        }
        
        /* 1. HEADER INSTITUCIONAL */
        .header-institucional {
            background-color: #0A3C59;
            color: white;
            padding: 4px 10px;
            height: 22px;
            line-height: 1.2;
            font-size: 7px;
            font-weight: 700;
            overflow: hidden;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .header-institucional .logo-container {
            float: left;
            margin-right: 5px;
        }
        
        .header-institucional img { 
            height: 14px; 
            width: 14px; 
            background-color: white; 
            border-radius: 50%; 
            padding: 1px;
            display: block;
        }
        
        .header-institucional .texto-header {
            float: left;
            margin-top: 3px;
            line-height: 1.1;
        }
        
        .header-institucional .ciclo { 
            float: right; 
            margin-top: 3px;
            font-size: 7.5px;
        }

        /* 2. UBICACIÓN CLAVE (TABLA) */
        .ubicacion-clave-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        
        .ubicacion-clave-table td {
            text-align: center;
            vertical-align: top;
            padding: 2px 0;
            width: 50%;
        }
        
        .ubicacion-clave-table .separator {
            border-left: 1px solid #d1d5db;
        }

        .ubicacion-clave-table .label { 
            color: #9ca3af; 
            font-weight: 800; 
            font-size: 8px; 
            display: block; 
            line-height: 1; 
            margin-bottom: 2px; 
            letter-spacing: 0.3px;
        }
        
        .ubicacion-clave-table .aula { 
            font-weight: 900; 
            font-size: 32px; 
            line-height: 1; 
            color: #dc2626; 
        }
        
        .ubicacion-clave-table .codigo { 
            font-weight: 900; 
            font-size: 20px; 
            line-height: 1; 
            color: #1f2937; 
        }

        /* 3. IDENTIFICACIÓN FOTOGRÁFICA (TABLA) */
        .identificacion-detalle-table {
            width: 100%;
            background-color: #f3f4f6;
            border-collapse: collapse;
            margin-top: 2px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .identificacion-detalle-table .foto-cell {
            width: 85px;
            padding: 4px 0 4px 8px;
            vertical-align: top;
        }
        
        .identificacion-detalle-table .datos-cell {
            padding: 4px 8px 4px 4px;
            vertical-align: top;
        }
        
        .identificacion-detalle-table img {
            width: 75px; 
            height: 75px; 
            border-radius: 4px;
            border: 2px solid #60a5fa;
            display: block;
        }

        .datos-postulante .label { 
            color: #9ca3af; 
            font-size: 8px; 
            display: block; 
            text-transform: uppercase; 
            line-height: 1.1;
            margin-bottom: 2px;
            font-weight: 700;
        }

        .nombre-postulante {
            font-weight: 800;
            font-size: 11px; 
            line-height: 1.2; 
            color: #1f2937;
            margin-bottom: 2px;
            max-height: 28px;
            overflow: hidden;
        }
        
        .carrera-postulante {
            font-weight: 600; 
            font-size: 10px; 
            color: #1d4ed8; 
            line-height: 1.25;
            max-height: 26px;
            overflow: hidden;
        }

        /* Footer */
        .footer-grupo-tema {
            font-size: 8px;
            font-weight: 700;
            padding: 4px 8px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            background-color: #f3f4f6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .footer-grupo-tema strong { 
            color: #1f2937; 
            font-weight: 800;
        }
        
        /* Utilidades de limpieza */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="tarjetas-container">
        @foreach($tarjetas as $index => $tarjeta)
            @php
                $claseTema = 'tarjeta-' . strtolower($tarjeta['tema'] ?? 'r');
                $fotoSrc = $tarjeta['foto'] ?? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
                
                // Abrir nueva fila cada 3 tarjetas (horizontal)
                $abrirFila = ($index % 3 == 0);
                $cerrarFila = ($index % 3 == 2) || ($index == count($tarjetas) - 1);
            @endphp

            @if($abrirFila)
                <div class="tarjeta-fila clearfix">
            @endif

            <div class="tarjeta-wrapper">
                <!-- LÍNEAS DE CORTE (8 líneas, 4 esquinas) -->
                <div class="linea-corte linea-corte-h corte-tl-h"></div>
                <div class="linea-corte linea-corte-v corte-tl-v"></div>
                <div class="linea-corte linea-corte-h corte-tr-h"></div>
                <div class="linea-corte linea-corte-v corte-tr-v"></div>
                <div class="linea-corte linea-corte-h corte-bl-h"></div>
                <div class="linea-corte linea-corte-v corte-bl-v"></div>
                <div class="linea-corte linea-corte-h corte-br-h"></div>
                <div class="linea-corte linea-corte-v corte-br-v"></div>

                <div class="tarjeta {{ $claseTema }}">
                    <!-- Franja de Color Vertical con TEMA EXTRA GRANDE -->
                    <div class="franja-tema">
                        <div class="tema-indicator">
                            <span class="tema-letra">{{ $tarjeta['tema'] ?? 'R' }}</span>
                            <span class="tema-label">TEMA</span>
                        </div>
                    </div>

                    <div class="contenido-principal">
                        
                        <!-- 1. HEADER INSTITUCIONAL -->
                        <div class="header-institucional clearfix">
                            <div class="logo-container">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/UNAMAD_LOGO.png/200px-UNAMAD_LOGO.png" alt="UNAMAD"/>
                            </div>
                            <div class="texto-header">
                                UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS<br>
                                CENTRO PRE UNIVERSITARIO
                            </div>
                            <div class="ciclo">CICLO 2024-II</div>
                        </div>

                        <!-- 2. UBICACIÓN CLAVE -->
                        <table class="ubicacion-clave-table">
                            <tr>
                                <td>
                                    <span class="label">AULA / ROOM</span>
                                    <div class="aula">{{ $tarjeta['aula'] ?? '---' }}</div>
                                </td>
                                <td class="separator">
                                    <span class="label">CÓDIGO / CODE</span>
                                    <div class="codigo">{{ $tarjeta['codigo'] ?? '---' }}</div>
                                </td>
                            </tr>
                        </table>

                        <!-- 3. IDENTIFICACIÓN FOTOGRÁFICA -->
                        <table class="identificacion-detalle-table">
                            <tr>
                                <td class="foto-cell" rowspan="2">
                                    <img src="{{ $fotoSrc }}" alt="Foto"/>
                                </td>
                                <td class="datos-cell">
                                    <div class="datos-postulante">
                                        <span class="label">Postulante</span>
                                        <div class="nombre-postulante">{{ $tarjeta['nombres'] ?? 'SIN NOMBRE' }}</div>
                                        <div class="carrera-postulante">{{ $tarjeta['carrera'] ?? 'SIN CARRERA' }}</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="footer-grupo-tema">
                                    <strong>GRUPO:</strong> {{ $tarjeta['grupo'] ?? '---' }} | <strong>TEMA:</strong> {{ $tarjeta['tema'] ?? '---' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            @if($cerrarFila)
                </div>
            @endif
        @endforeach
    </div>
</body>
</html>