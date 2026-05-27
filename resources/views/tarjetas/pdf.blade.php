<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Etiquetas de Examen - UNAMAD (2x3)</title>
    
    <style>
        /* CONFIGURACIÓN BASE Y PÁGINA A4 HORIZONTAL */
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-weight: 500;
            background-color: #ffffff;
            color: #1e293b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
  
        .tarjetas-container {
            width: 100%;
            text-align: center;
        }
  
        .tarjeta-fila {
            width: 100%;
            margin-bottom: 0;
            page-break-inside: avoid;
            text-align: left;
            margin-bottom: 1mm; 
        }
  
        .tarjeta-wrapper {
            display: inline-block;
            width: 14.1cm; 
            height: 6.4cm; 
            vertical-align: top;
            position: relative;
            page-break-inside: avoid;
            margin-right: 2mm; 
        }
        
        .tarjeta-fila .tarjeta-wrapper:nth-child(2n) {
            margin-right: 0;
        }
  
        /* Líneas de corte sutiles */
        .linea-corte {
            position: absolute;
            background-color: #cbd5e1;
            z-index: 10;
        }
        .linea-corte-h { height: 0.5px; width: 6mm; }
        .linea-corte-v { width: 0.5px; height: 6mm; }
        
        .corte-tl-h { top: 0; left: 0; }
        .corte-tl-v { top: 0; left: 0; }
        .corte-tr-h { top: 0; right: 0; }
        .corte-tr-v { top: 0; right: 0; }
        .corte-bl-h { bottom: 0; left: 0; }
        .corte-bl-v { bottom: 0; left: 0; }
        .corte-br-h { bottom: 0; right: 0; }
        .corte-br-v { bottom: 0; right: 0; }
  
        .tarjeta {
            width: 13.8cm; 
            height: 6.0cm;
            border: 1.5px solid #64748b;
            margin: 2mm; 
            padding: 0;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
  
        /* Franja de Color Lateral y TEMA (Alto contraste para B&W) */
        .franja-tema {
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 38px; 
            height: 100%;
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
            z-index: 2;
            text-align: center;
        }
        .tarjeta-p .franja-tema { background-color: #1e40af; } /* Azul oscuro */
        .tarjeta-q .franja-tema { background-color: #15803d; } /* Verde oscuro */
        .tarjeta-r .franja-tema { background-color: #b45309; } /* Naranja/Marrón oscuro */
        
        .tema-indicator { 
            position: absolute; 
            left: 0; 
            top: 50%; 
            width: 38px; 
            transform: translateY(-50%); 
            text-align: center; 
        }
        .tema-indicator .tema-letra { 
            font-size: 44px; 
            font-weight: 900; 
            line-height: 1; 
            color: #ffffff; 
            display: block; 
        }
        .tema-indicator .tema-label { 
            font-size: 7px; 
            font-weight: 800; 
            color: #ffffff; 
            text-transform: uppercase; 
            display: block; 
            margin-top: 2px; 
            letter-spacing: 0.5px; 
        }
  
        .contenido-principal {
            margin-left: 38px; 
            height: 100%;
            position: relative;
            z-index: 1;
        }
        
        /* Header en Fondo Blanco para máxima nitidez en B&W */
        .header-institucional {
            background-color: #ffffff;
            border-bottom: 2px solid #0f172a;
            color: #0f172a;
            padding: 3px 8px; 
            height: 46px; 
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
  
        .header-logo-container {
            width: 36px;
            height: 36px;
            float: left;
            margin-top: 2px;
        }
        .header-logo-container-right {
            width: 36px;
            height: 36px;
            float: right;
            margin-top: 2px;
        }
        .header-logo-container img, .header-logo-container-right img {
            width: 100%; 
            height: 100%; 
            display: block;
        }
        
        .texto-header-container {
            margin-left: 40px;
            margin-right: 40px;
            text-align: center;
            line-height: 1.1;
        }
        
        .texto-header-container .universidad-title {
            font-size: 9px; 
            font-weight: 800;
            display: block;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.1px;
        }
        
        .texto-header-container .cepre-title {
            font-size: 11px;
            font-weight: 800;
            display: block;
            color: #1e3a8a;
        }
        
        .texto-header-container .ciclo {
            font-size: 8.5px;
            font-weight: 700;
            display: block;
            color: #475569;
        }
  
        .clearfix::after { content: ""; display: table; clear: both; }
    </style>
</head>
<body>
    <div class="tarjetas-container">
        @foreach($tarjetas as $index => $tarjeta)
            @php
                $claseTema = 'tarjeta-' . strtolower($tarjeta['tema'] ?? 'r');
                $fotoSrc = $tarjeta['foto'] ?? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='; 
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
                    <!-- Sello de Inhabilitado (Centrado absoluto) -->
                    @if(!empty($tarjeta['inhabilitado']))
                        <div style="position: absolute; top: 1.8cm; left: 2.8cm; width: 8.5cm; z-index: 999; text-align: center;">
                            <div style="color: #dc2626; font-size: 24px; font-weight: 900; border: 4px solid #dc2626; padding: 6px 14px; display: inline-block; transform: rotate(-12deg); background-color: #ffffff; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.25);">
                                INHABILITADO
                            </div>
                        </div>
                    @endif

                    <!-- Franja de Color Vertical -->
                    <div class="franja-tema">
                        <div class="tema-indicator">
                            <span class="tema-label">TEMA</span>
                            <span class="tema-letra">{{ $tarjeta['tema'] ?? 'R' }}</span>
                        </div>
                    </div>

                    <div class="contenido-principal">
                        <!-- 1. HEADER INSTITUCIONAL (Centrado perfecto con tabla de 3 columnas) -->
                        <table style="width: 100%; height: 48px; border-collapse: collapse; border: none; border-bottom: 2px solid #0f172a; margin-bottom: 2px; background-color: #ffffff;">
                            <tr style="border: none;">
                                <td style="width: 44px; vertical-align: middle; text-align: left; border: none; padding: 2px 0 2px 8px;">
                                    <img src="{{ public_path('assets/images/logo unamad constancia_optimized.png') }}" style="width: 36px; height: 36px; display: block;" alt="Logo UNAMAD"/> 
                                </td>
                                <td style="vertical-align: middle; text-align: center; border: none; padding: 2px 0;">
                                    <span style="font-size: 8.5px; font-weight: 800; display: block; text-transform: uppercase; color: #0f172a; letter-spacing: 0.1px; line-height: 1.15;">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</span>
                                    <span style="font-size: 12.5px; font-weight: 900; display: block; color: #1e3a8a; line-height: 1.2; text-transform: uppercase; margin-top: 1px;">CENTRO PRE UNIVERSITARIO</span>
                                    <span style="font-size: 8.5px; font-weight: 800; display: block; color: #475569; line-height: 1.1; margin-top: 1px;">{{ $ciclo_nombre ?? 'CICLO ACTUAL' }}</span>
                                </td>
                                <td style="width: 44px; vertical-align: middle; text-align: right; border: none; padding: 2px 8px 2px 0;">
                                    <img src="{{ public_path('assets/images/logo cepre costancia_optimized.png') }}" style="width: 36px; height: 36px; display: block;" alt="Logo CEPRE"/> 
                                </td>
                            </tr>
                        </table>

                        <!-- TABLA DE DATOS (Perfecta maquetación sin desbordes) -->
                        <table style="width: 100%; height: 172px; border-collapse: collapse; border: none; table-layout: fixed;">
                            <!-- Fila 1: Carrera y Código -->
                            <tr style="height: 48px;">
                                <td style="width: 63%; padding: 4px 8px; vertical-align: middle; text-align: left; border: none; overflow: hidden;">
                                    <span style="color: #475569; font-weight: 800; font-size: 7.5px; text-transform: uppercase; display: block; margin-bottom: 2px; letter-spacing: 0.3px;">CARRERA PROFESIONAL</span>
                                    <div style="font-weight: 800; font-size: 11px; line-height: 1.2; color: #000000; text-transform: uppercase;">{{ $carreraSuperior }}</div>
                                </td>
                                <td style="width: 37%; padding: 4px 8px; vertical-align: middle; text-align: center; border-left: 1.5px solid #000000; border-top: none; border-bottom: none; border-right: none;">
                                    <span style="color: #475569; font-weight: 800; font-size: 7.5px; text-transform: uppercase; display: block; margin-bottom: 1px; letter-spacing: 0.3px;">CÓDIGO POSTULANTE</span>
                                    <div style="font-weight: 900; font-size: 21px; color: #000000; letter-spacing: 0.5px;">{{ $tarjeta['codigo'] ?? '---' }}</div>
                                </td>
                            </tr>
                            
                            <!-- Fila 2: Foto y Datos del Postulante -->
                            <tr style="height: 108px; border-top: 1.5px solid #000000; border-bottom: 1.5px solid #000000; border-left: none; border-right: none;">
                                <td colspan="2" style="padding: 6px 8px; vertical-align: middle; border: none; background-color: #f8fafc;">
                                    <table style="width: 100%; border-collapse: collapse; border: none;">
                                        <tr style="border: none;">
                                            <!-- Columna Foto -->
                                            <td style="width: 82px; vertical-align: middle; text-align: left; border: none; padding: 0;">
                                                <div style="width: 78px; height: 90px; border: 2px solid #000000; border-radius: 4px; overflow: hidden; background-color: #ffffff;">
                                                    <img src="{{ $fotoSrc }}" onerror="this.onerror=null;this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';" style="width: 100%; height: 100%; display: block; object-fit: cover;"/>
                                                </div>
                                            </td>
                                            <!-- Columna Datos -->
                                            <td style="padding-left: 10px; vertical-align: middle; text-align: left; border: none;">
                                                <span style="color: #475569; font-size: 8px; text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 2px; letter-spacing: 0.2px;">POSTULANTE</span>
                                                <div style="font-weight: 800; font-size: 13.5px; line-height: 1.25; color: #000000; text-transform: uppercase; margin-bottom: 3px;">{{ $tarjeta['nombres'] ?? 'SIN NOMBRE' }}</div>
                                                <div style="font-size: 9.5px; font-weight: 700; color: #475569; margin-bottom: 5px;">DNI: <strong style="color: #000000; font-weight: 800; font-size: 11px;">{{ $tarjeta['dni'] ?? '--------' }}</strong></div>
                                                
                                                <!-- Fila de Información Clave (Aula y Asiento en Badges de alto contraste) -->
                                                <table style="width: 100%; border-collapse: collapse; border: none;">
                                                    <tr>
                                                        <td style="padding-right: 6px; border: none;">
                                                            <div style="border: 1.5px solid #000000; background-color: #ffffff; padding: 3px 6px; border-radius: 4px; text-align: center;">
                                                                <span style="font-size: 7px; font-weight: 800; display: block; color: #475569; line-height: 1;">AULA</span>
                                                                <span style="font-size: 13px; font-weight: 900; color: #000000; display: block; margin-top: 1px;">{{ $tarjeta['aula'] ?? '---' }}</span>
                                                            </div>
                                                        </td>
                                                        <td style="border: none;">
                                                            <div style="border: 1.5px solid #000000; background-color: #0f172a; padding: 3px 6px; border-radius: 4px; text-align: center;">
                                                                <span style="font-size: 7px; font-weight: 800; display: block; color: #cbd5e1; line-height: 1;">N° ASIENTO</span>
                                                                <span style="font-size: 13px; font-weight: 900; color: #ffffff; display: block; margin-top: 1px;">{{ $tarjeta['asiento'] ?? $tarjeta['numero_asiento'] ?? '---' }}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <!-- Fila 3: Footer Grupo y Tema -->
                            <tr style="height: 28px; background-color: #ffffff; border: none;">
                                <td colspan="2" style="text-align: center; vertical-align: middle; font-size: 9.5px; font-weight: 700; color: #0f172a; border: none;">
                                    GRUPO: <strong style="font-weight: 900; font-size: 11px;">{{ $tarjeta['grupo'] ?? '---' }}</strong> &nbsp;&nbsp;|&nbsp;&nbsp; TEMA ASIGNADO: <strong style="font-weight: 900; font-size: 11px;">{{ $tarjeta['tema'] ?? '---' }}</strong>
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