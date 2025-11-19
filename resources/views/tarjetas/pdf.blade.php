<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas de Examen - UNAMAD</title>
    <!-- Carga Font Awesome (ICONOS) - Necesario para la foto placeholder -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Variables y configuración de color principal */
        :root {
            --color-unama-blue: #0A3C59; /* Azul Oscuro Institucional */
            --color-tema-p: #0d6efd;
            --color-tema-q: #198754;
            --color-tema-r: #ffc107;
        }
        
        /* ------------------------------------------------ */
        /* CONFIGURACIÓN BASE Y PÁGINA A4 (CRUCIAL PARA PDF)*/
        /* ------------------------------------------------ */
        @page {
            size: A4;
            margin: 0.5cm; /* Márgenes de la hoja */
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-weight: 500;
        }

        /* ------------------------------------------------ */
        /* LÍNEAS DE CORTE Y CONTENEDOR */
        /* ------------------------------------------------ */
        .tarjetas-container {
            overflow: hidden; 
        }

        .tarjeta-wrapper {
            position: relative;
            /* Margen total por tarjeta (8.5cm + 0.3cm) x (5.5cm + 0.3cm) */
            width: 8.8cm;
            height: 5.8cm;
            float: left; /* CRUCIAL: Usar flotante para disposición en el PDF */
            margin: 0; 
            page-break-inside: avoid;
        }

        /* ------------------------------------------------ */
        /* Estilos de la TARJETA (Diseño Final basado en tablas) */
        /* ------------------------------------------------ */

        .tarjeta {
            width: 8.5cm;
            height: 5.5cm;
            border: 1px solid #000; /* Línea de corte visible */
            position: absolute; 
            top: 1.5mm; /* Margen de corte */
            left: 1.5mm; /* Margen de corte */
            padding: 0;
            
            font-size: 10px;
            border-radius: 0.65rem;
            overflow: hidden;
            background-color: #fff;
        }

        /* Colores y Franjas Laterales */
        .franja-tema {
            width: 15px;
            height: 100%;
            float: left; 
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .tarjeta-p .franja-tema { background-color: var(--color-tema-p); }
        .tarjeta-q .franja-tema { background-color: var(--color-tema-q); }
        .tarjeta-r .franja-tema { background-color: var(--color-tema-r); }
        .tarjeta-p { color: white; }
        .tarjeta-q { color: white; }
        .tarjeta-r { color: #1f2937; } 

        /* Contenedor principal de datos */
        .contenido-principal {
            width: calc(100% - 15px); 
            height: 100%;
            float: left; 
            position: relative;
        }
        
        /* 1. HEADER INSTITUCIONAL */
        .header-institucional {
            background-color: var(--color-unama-blue);
            color: white;
            padding: 3px 8px;
            height: 20px;
            line-height: 1.2;
            font-size: 7px;
            font-weight: 700;
            overflow: hidden;
        }
        .header-institucional img { 
            float: left;
            height: 14px; 
            width: 14px; 
            object-fit: contain; 
            background-color: white; 
            border-radius: 50%; 
            padding: 1px; 
            margin-right: 4px;
        }
        .header-institucional .ciclo { float: right; margin-top: 3px; }

        /* 2. UBICACIÓN CLAVE (USANDO TABLA) */
        .ubicacion-clave-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Mantiene ancho fijo */
            margin-top: 2px;
        }
        .ubicacion-clave-table td {
            text-align: center;
            vertical-align: top;
            padding: 2px 0;
        }
        .ubicacion-clave-table .separator {
            border-left: 1px solid #d1d5db;
        }

        .ubicacion-clave-table span { 
            color: #9ca3af; 
            font-weight: 800; 
            font-size: 8px; 
            display: block; 
            line-height: 1; 
            margin-bottom: 2px; 
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


        /* 3. IDENTIFICACIÓN FOTOGRÁFICA Y DETALLES (USANDO TABLA) */
        .identificacion-detalle-table {
            width: 100%;
            background-color: #f3f4f6;
            border-collapse: collapse;
            table-layout: fixed;
            height: 85px;
        }

        .identificacion-detalle-table .foto-cell {
            width: 85px; /* Ancho fijo para la foto y margen */
            padding: 4px 0 4px 8px;
            text-align: left;
            vertical-align: top;
        }
        .identificacion-detalle-table .datos-cell {
            padding: 4px 8px 4px 0;
            vertical-align: top;
        }
        
        .identificacion-detalle-table img {
            width: 75px; 
            height: 75px; 
            object-fit: cover;
            border-radius: 0.25rem;
            border: 2px solid #60a5fa;
        }

        .datos-postulante span { color: #9ca3af; font-size: 8px; display: block; text-transform: uppercase; line-height: 1.1; }

        .nombre-postulante {
            font-weight: 800;
            font-size: 0.7rem; 
            line-height: 1.1; 
            height: 1.5rem; 
            overflow: hidden;
            color: #1f2937;
        }
        
        .carrera-postulante {
            font-weight: 600; 
            font-size: 0.65rem; 
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #1d4ed8; 
            margin-top: 2px;
        }

        /* Footer (Última fila de la tabla) */
        .footer-grupo-tema td {
            font-size: 8px;
            font-weight: 600;
            padding: 2px 8px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            clear: both; 
        }
        .footer-grupo-tema strong { color: #1f2937; }

    </style>
</head>
<body>
    <div class="tarjetas-container">
        <!-- Itera sobre los datos proporcionados por el controlador -->
        @foreach($tarjetas as $tarjeta)
        <div class="tarjeta-wrapper">
            
            @php
                // Asegura la clase CSS para el tema
                $claseTema = 'tarjeta-' . strtolower($tarjeta['tema'] ?? 'r');
                // URL de la foto: DEBE ser una URL Base64 o una URL absoluta PÚBLICA.
                $fotoSrc = $tarjeta['foto'] ?? 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png';
            @endphp

            <div class="tarjeta {{ $claseTema }}">
                <!-- Franja de Color Vertical (Tema) -->
                <div class="franja-tema"></div>

                <div class="contenido-principal">
                    
                    <!-- 1. HEADER INSTITUCIONAL -->
                    <div class="header-institucional" style="background-color: var(--color-unama-blue);">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/UNAMAD_LOGO.png/200px-UNAMAD_LOGO.png" alt="UNAMAD Logo"/>
                        <div style="float: left; margin-top: 3px;">
                            UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS<br>
                            CENTRO PRE UNIVERSITARIO
                        </div>
                        <div class="ciclo">CICLO 2024-II</div>
                    </div>

                    <!-- CONTENEDOR DE DATOS PRINCIPALES (TABLA) -->
                    <table class="ubicacion-clave-table">
                        <tbody>
                            <!-- Fila de Aula y Código -->
                            <tr>
                                <td style="width: 50%;">
                                    <span>AULA / ROOM</span>
                                    <div class="aula">{{ $tarjeta['aula'] ?? '---' }}</div>
                                </td>
                                <td class="separator" style="width: 50%;">
                                    <span>CÓDIGO / CODE</span>
                                    <div class="codigo">{{ $tarjeta['codigo'] ?? '---' }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- 3. IDENTIFICACIÓN FOTOGRÁFICA Y DETALLES (USANDO TABLA) -->
                    <table class="identificacion-detalle-table">
                        <tbody>
                            <tr>
                                <!-- Celda para la FOTO -->
                                <td class="foto-cell" rowspan="2">
                                    <img
                                        src="{{ $fotoSrc }}"
                                        alt="Foto del estudiante"
                                    />
                                </td>
                                <!-- Celda para los DATOS -->
                                <td class="datos-cell">
                                    <div class="datos-postulante">
                                        <span>Postulante</span>
                                        <div class="nombre-postulante">{{ $tarjeta['nombres'] ?? 'SIN NOMBRE' }}</div>
                                        <div class="carrera-postulante">{{ $tarjeta['carrera'] ?? 'SIN CARRERA' }}</div>
                                    </div>
                                </td>
                            </tr>
                            <!-- Fila para el Footer de Grupo/Tema -->
                            <tr class="footer-grupo-tema">
                                <td colspan="2">
                                    <strong>GRUPO:</strong> {{ $tarjeta['grupo'] ?? '---' }} | <strong>TEMA ASIGNADO:</strong> {{ $tarjeta['tema'] ?? '---' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</body>
</html>