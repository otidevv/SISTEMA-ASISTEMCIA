<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Etiquetas de Examen - UNAMAD (2x3)</title>
    
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
            /* A4 Landscape: 297mm x 210mm */
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
            /* Asegura que los colores se impriman correctamente */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* CONTENEDOR PRINCIPAL */
        .tarjetas-container {
            width: 100%;
            text-align: center;
        }

        /* FILA DE TARJETAS (2 por fila) */
        .tarjeta-fila {
            width: 100%;
            margin-bottom: 0;
            page-break-inside: avoid;
            text-align: left;
            margin-bottom: 2mm; /* Espacio entre filas */
        }

        /* WRAPPER CON LÍNEAS DE CORTE - APROX. 13.5CM DE ANCHO */
        .tarjeta-wrapper {
            display: inline-block;
            width: 13.5cm; 
            height: 6.3cm; 
            vertical-align: top;
            position: relative;
            page-break-inside: avoid;
            margin-right: 5mm; 
        }
        
        /* Eliminar margen extra en la segunda tarjeta de cada fila (la última) */
        .tarjeta-fila .tarjeta-wrapper:nth-child(2n) {
            margin-right: 0;
        }

        /* LÍNEAS DE CORTE (Esquinas) */
        .linea-corte {
            position: absolute;
            background-color: #666;
        }
        
        /* Líneas horizontales */
        .linea-corte-h { height: 0.5px; width: 8mm; }
        /* Líneas verticales */
        .linea-corte-v { width: 0.5px; height: 8mm; }
        
        /* Posiciones de las líneas */
        .corte-tl-h { top: 0; left: 0; }
        .corte-tl-v { top: 0; left: 0; }
        .corte-tr-h { top: 0; right: 0; }
        .corte-tr-v { top: 0; right: 0; }
        .corte-bl-h { bottom: 0; left: 0; }
        .corte-bl-v { bottom: 0; left: 0; }
        .corte-br-h { bottom: 0; right: 0; }
        .corte-br-v { bottom: 0; right: 0; }

        /* TARJETA BASE - NUEVAS DIMENSIONES AMPLIADAS */
        .tarjeta {
            width: 13.1cm; 
            height: 5.9cm;
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

        /* Franja de Color Lateral y TEMA */
        .franja-tema {
            position: absolute; left: 0; top: 0; width: 35px; height: 100%;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .tarjeta-p .franja-tema { background-color: var(--color-tema-p); }
        .tarjeta-q .franja-tema { background-color: var(--color-tema-q); }
        .tarjeta-r .franja-tema { background-color: var(--color-tema-r); }
        .tema-indicator { position: absolute; left: 0; top: 50%; width: 35px; transform: translateY(-50%); text-align: center; }
        .tema-indicator .tema-letra { font-size: 64px; font-weight: 900; line-height: 1; color: white; text-shadow: 3px 3px 6px rgba(0,0,0,0.4); display: block; }
        .tema-indicator .tema-label { font-size: 8px; font-weight: 800; color: white; text-transform: uppercase; display: block; margin-top: 6px; letter-spacing: 1px; }

        /* Contenedor principal de datos */
        .contenido-principal {
            margin-left: 38px; 
            height: 100%;
            position: relative;
        }
        
        /* 1. HEADER INSTITUCIONAL - REESTRUCTURADO */
        .header-institucional {
            background-color: var(--color-unama-blue);
            color: white;
            /* AJUSTE 1: Reducimos el padding superior a 1px para subir el texto */
            padding: 1px 5px 2px 5px; 
            height: 28px; 
            font-size: 8px; 
            font-weight: 700;
            overflow: hidden;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            display: flex; 
            /* AJUSTE 2: Alineamos el contenido al inicio (arriba) */
            align-items: flex-start;
            justify-content: space-between;
        }

        /* Contenedores de Logos */
        .header-logo-container {
            /* Fija el tamaño del contenedor */
            width: 24px; /* Aumentado de 20px a 24px */
            height: 24px; /* Aumentado de 20px a 24px */
            flex-shrink: 0;
            /* Si la alineación es flex-start, necesitamos asegurarnos que los logos no estén muy arriba. */
            /* Dándoles un margen superior de 1px para compensar la reducción del padding del padre */
            margin-top: 1px;
        }
        .header-logo-container img {
            width: 100%; 
            height: 100%; 
            border-radius: 50%; 
            padding: 1px;
            object-fit: contain; /* Asegura que la imagen quepa sin desbordar */
            display: block;
        }
        
        /* Contenedor de Texto Central */
        .texto-header-container {
            flex-grow: 1;
            text-align: center;
            line-height: 1.1;
            /* AJUSTE CLAVE 1: Reducir el padding horizontal para más espacio */
            padding: 0 2px; 
        }
        
        /* === ESTILOS PARA QUE EL TÍTULO DE LA UNIVERSIDAD ENCAJE === */
        .texto-header-container .universidad-title {
            /* Reducimos la fuente para asegurar que entre */
            font-size: 6.5px; 
            font-weight: 800;
            display: block;
            line-height: 1; 
            text-transform: uppercase;
        }
        
        .texto-header-container .cepre-title {
            /* Título del CEPRE en segunda línea, ajustado ligeramente */
            font-size: 7.5px;
            font-weight: 700;
            line-height: 1; /* Reducimos la altura de línea */
            display: block;
            /* AJUSTE CLAVE 2: Reducir el margen superior para compactar el espacio */
            margin-top: 0px; 
        }
        
        .texto-header-container .ciclo {
            font-size: 7.5px;
            font-weight: 700;
            /* AJUSTE CLAVE 3: Reducir el margen superior para compactar el espacio */
            margin-top: 1px;
            line-height: 1;
        }
        /* ========================================================== */

        /* 2. UBICACIÓN CLAVE (Carrera y Código) */
        .ubicacion-clave-table { width: 100%; border-collapse: collapse; margin-top: 1px; }
        .ubicacion-clave-table td { text-align: center; vertical-align: top; padding: 2px 0; width: 50%; }
        .ubicacion-clave-table .separator { border-left: 1px solid #d1d5db; }
        .ubicacion-clave-table .label { color: #9ca3af; font-weight: 800; font-size: 8px; display: block; line-height: 1; margin-bottom: 2px; letter-spacing: 0.3px; }
        
        .ubicacion-clave-table .carrera-profesional { 
            font-weight: 900; font-size: 20px; line-height: 1.2; color: #dc2626; 
            padding: 0 4px; text-transform: uppercase; max-height: 96px; overflow: hidden; display: inline-block;
        }
        
        .ubicacion-clave-table .codigo { 
            font-weight: 900; font-size: 26px; line-height: 1; color: #1f2937; 
        }

        /* 3. IDENTIFICACIÓN FOTOGRÁFICA */
        .identificacion-detalle-table {
            width: 100%; background-color: #f3f4f6; border-collapse: collapse; margin-top: 1px; 
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .identificacion-detalle-table .foto-cell { width: 85px; padding: 1px 0 1px 8px; vertical-align: top; }
        .identificacion-detalle-table .datos-cell { padding: 1px 4px 1px 4px; vertical-align: top; }
        .identificacion-detalle-table img { 
            width: 80px; height: 80px; border-radius: 4px; border: 2px solid #60a5fa; display: block; 
        }
        .datos-postulante .label { color: #9ca3af; font-size: 8px; display: block; text-transform: uppercase; line-height: 1.1; margin-bottom: 1px; font-weight: 700; }
        .nombre-postulante { font-weight: 800; font-size: 16px; line-height: 1.2; color: #1f2937; margin-bottom: 1px; max-height: 38px; overflow: hidden; }
        .carrera-postulante { display: none; }

        /* Footer */
        .footer-grupo-tema {
            font-size: 9px; font-weight: 700; padding: 2px 8px; border-top: 1px solid #d1d5db;
            text-align: center; background-color: #f3f4f6; -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .footer-grupo-tema strong { color: #1f2937; font-weight: 800; }
        
        /* Utilidades de limpieza */
        .clearfix::after { content: ""; display: table; clear: both; }
    </style>
</head>
<body>
    <div class="tarjetas-container">
        @foreach($tarjetas as $index => $tarjeta)
            @php
                $claseTema = 'tarjeta-' . strtolower($tarjeta['tema'] ?? 'r');
                $fotoSrc = $tarjeta['foto'] ?? 'https://placehold.co/80x80/E0E7FF/1E40AF?text=FOTO'; 
                
                $carreraSuperior = strtoupper($tarjeta['carrera'] ?? 'SIN CARRERA');
                $abrirFila = ($index % 2 == 0);
                $cerrarFila = ($index % 2 == 1) || ($index == count($tarjetas) - 1);
            @endphp

            @if($abrirFila)
                <div class="tarjeta-fila clearfix">
            @endif

            <div class="tarjeta-wrapper">
                <!-- LÍNEAS DE CORTE -->
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
                            <span class="tema-label">TEMA</span>
                            <span class="tema-letra">{{ $tarjeta['tema'] ?? 'R' }}</span>
                        </div>
                    </div>

                    <div class="contenido-principal">
                        
                        <!-- 1. HEADER INSTITUCIONAL - CON DOS LOGOS Y RUTAS ORIGINALES -->
                        <div class="header-institucional">
                            
                            <!-- LOGO IZQUIERDO (UNAMAD) -->
                            <div class="header-logo-container">
                                <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="Logo UNAMAD"/> 
                            </div>
                            
                            <!-- TEXTO CENTRAL (Ajustado para que el nombre de la U entre en una línea y se mueva hacia arriba) -->
                            <div class="texto-header-container">
                                <span class="universidad-title">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</span>
                                <span class="cepre-title">CENTRO PRE UNIVERSITARIO</span>
                                <div class="ciclo">CICLO 2024-II</div>
                            </div>
                            
                            <!-- LOGO DERECHO (CEPRE) -->
                            <div class="header-logo-container">
                                <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="Logo CEPRE"/> 
                            </div>

                        </div>

                        <!-- 2. UBICACIÓN CLAVE (CARRERA PROFESIONAL) -->
                        <table class="ubicacion-clave-table">
                            <tr>
                                <td>
                                    <span class="label">CARRERA PROFESIONAL</span>
                                    <div class="carrera-profesional">{{ $carreraSuperior }}</div>
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
                                    <img src="{{ $fotoSrc }}" onerror="this.onerror=null;this.src='https://placehold.co/80x80/E0E7FF/1E40AF?text=FOTO';" alt="Foto"/>
                                </td>
                                <td class="datos-cell">
                                    <div class="datos-postulante">
                                        <span class="label">Postulante</span>
                                        <div class="nombre-postulante">{{ $tarjeta['nombres'] ?? 'SIN NOMBRE' }}</div>
                                        <div class="carrera-postulante"></div>
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