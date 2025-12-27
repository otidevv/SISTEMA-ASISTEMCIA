<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Vacante</title>
    <style>
        @page {
            margin: 0.8cm;
            size: A4;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            position: relative;
            color: #2d3748;
            background: #ffffff;
            min-height: calc(100vh - 40px);
        }

        * {
            background: transparent !important;
        }

        /* Marca de agua mejorada */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            z-index: -999;
            pointer-events: none;
        }

        /* Encabezado mejorado */
        .header {
            text-align: center;
            position: relative;
            padding: 8px 0 12px 0;
            border-bottom: 4px double #1a365d;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 8px 8px 0 0;
        }
        
        .header-logo {
            position: absolute;
            left: 28px;
            top: 11px;
            width: 83px;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.1));
        }
        
        .header-logo-derecho {
            position: absolute;
            right: 28px;
            top: 11px;
            width: 83px;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.1));
        }
        
        .header-logo img,
        .header-logo-derecho img {
            width: 75px;
            height: auto;
        }
        
        .header-texto {
            margin: 0 auto;
            max-width: 400px;
            padding: 0 80px;
        }
        
        .university-name {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a365d;
            letter-spacing: 0.6px;
            text-shadow: 1px 1px 2px rgba(26, 54, 93, 0.1);
            margin-bottom: 2px;
        }
        
        .subtitle {
            font-size: 11pt;
            font-weight: bold;
            margin: 2px 0;
            color: #4a5568;
            font-style: italic;
        }
        
        .year {
            font-size: 8.5pt;
            font-style: italic;
            color: #718096;
            margin: 1px 0;
        }

        /* Marco general mejorado */
        .certificate-box {
            margin: 10px 15px 100px 15px;
            padding: 15px 20px;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            position: relative;
            min-height: calc(100vh - 200px);
        }

        /* Título principal */
        .certificate-title {
            text-align: center;
            font-size: 38pt;
            font-weight: bold;
            margin: 8px 0 5px 0;
            text-transform: uppercase;
            color: #1a365d;
            letter-spacing: 1.5px;
            text-shadow: 2px 2px 4px rgba(26, 54, 93, 0.2);
            position: relative;
        }

        .certificate-title::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 3px;
            background: linear-gradient(to right, #1a365d, #3182ce, #1a365d);
            border-radius: 2px;
        }
        
        .title-divider {
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, transparent 10%, #1a365d 30%, #3182ce 50%, #1a365d 70%, transparent 90%);
            margin: 5px 0 8px 0;
            border-radius: 2px;
        }

        /* Modalidad */
        .modality-badge {
            text-align: center;
            margin: 8px 0;
        }

        .modality-text {
            background: #930E29 !important;
            color: #ffffff !important;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 12pt;
            display: inline-block;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
            border: 2px solid #ffffff;
        }

        /* Contenido mejorado */
        .content {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.3;
            margin-bottom: 40px;
        }

        .intro-section {
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
            padding: 8px;
            border-radius: 6px;
            border-left: 4px solid #1a365d;
            margin: 10px 0;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            color: #1a365d;
        }
        
        .student-info {
            margin: 10px 0;
            text-align: center;
            font-weight: bold;
            font-size: 20pt;
            color: #930E29;
            text-transform: uppercase;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 4px solid #930E29;
            box-shadow: 0 3px 8px rgba(220, 38, 38, 0.15);
            letter-spacing: 0.6px;
        }

        .details-section {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 8px;
            margin: 10px 0;
            border-left: 4px solid #f59e0b;
        }
        
        .content strong {
            color: #1a365d;
            font-weight: bold;
        }

        .content p {
            margin: 8px 0;
        }

        /* Lugar y fecha mejorado */
        .date-place {
            text-align: right;
            margin: 20px 20px 10px 0;
            font-weight: bold;
            color: #1a365d;
            font-size: 12pt;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 8px 15px;
            border-radius: 6px;
            display: inline-block;
            box-shadow: 0 3px 8px rgba(26, 54, 93, 0.1);
            border: 1px solid #cbd5e0;
        }

        /* Sección de firma */
        .signature-section {
            text-align: center;
            margin-top: 15px;
            color: #2d3748;
            font-size: 11pt;
        }

        .signature-note {
            font-size: 10pt;
            color: #4a5568;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .signature-line {
            width: 250px;
            border-top: 2px solid #1a365d;
            margin: 0 auto 8px auto;
        }

        .signature-title {
            font-weight: bold;
            color: #1a365d;
            margin: 3px 0;
            font-size: 11pt;
        }

        /* QR mejorado */
        .qr-lateral {
            width: 80px;
            height: auto;
            position: absolute;
            bottom: 80px;
            right: 40px;
            text-align: center;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 8px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(26, 54, 93, 0.25);
            z-index: 10;
            /*border: 2px solid #1a365d;*/
        }
        
        .qr-title {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 4px;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .qr-code {
            width: 70px;
            height: 70px;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
        }
        
        .verification-code {
            font-size: 7pt;
            font-weight: bold;
            margin-top: 4px;
            color: #4a5568;
            font-family: 'Courier New', monospace;
            background: #f7fafc;
            padding: 2px 4px;
            border-radius: 2px;
        }

        /* Footer mejorado */
        .footer {
            position: absolute;
            bottom: 15px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 8pt;
            color: #4a5568;
            line-height: 1.3;
            border-top: 2px solid #1a365d;
            padding-top: 5px;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-radius: 4px 4px 0 0;
            z-index: 5;
        }

        .footer strong {
            color: #1a365d;
        }

        /* Bordes decorativos para toda la página */
        .page-border {
            position: fixed;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -20px;
            border: 6px solid;
            border-image: linear-gradient(45deg, #1a365d, #3182ce, #1a365d, #3182ce) 1;
            pointer-events: none;
            z-index: 1000;
        }

        .page-border::before {
            content: '';
            position: absolute;
            top: 6px;
            left: 6px;
            right: 6px;
            bottom: -25px;
            border: 1px solid #1a365d;
            border-radius: 4px;
        }

        /* Animaciones sutiles para vista */
        @media screen {
            .certificate-box {
                transition: box-shadow 0.3s ease;
            }
            
            .certificate-box:hover {
                box-shadow: 0 12px 35px rgba(26, 54, 93, 0.2);
            }
            
            .qr-lateral:hover {
                transform: scale(1.02);
                transition: transform 0.2s ease;
            }
        }
    </style>
</head>
<body>
    <!-- Borde decorativo de toda la página -->
    <div class="page-border"></div>

    <!-- Marca de agua -->
    <div class="watermark">
        <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" width="800" alt="Marca de agua">
    </div>

    <!-- Encabezado -->
    <div class="header">
        <div class="header-logo">
            <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="Logo UNAMAD" onerror="this.parentElement.innerHTML='<div style=\'width:75px;height:75px;border:2px solid #1a365d;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:8pt;color:#1a365d;font-weight:bold;text-align:center;\'>UNAMAD<br>LOGO</div>'">
        </div>
        <div class="header-logo-derecho">
            <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="Logo CEPRE" onerror="this.parentElement.innerHTML='<div style=\'width:75px;height:75px;border:2px solid #1a365d;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:8pt;color:#1a365d;font-weight:bold;text-align:center;\'>CEPRE<br>LOGO</div>'">
        </div>
        <div class="header-texto">
            <div class="university-name">Universidad Nacional Amazónica de Madre de Dios</div>
            <div class="subtitle">"Centro Pre-Universitario"</div>
            <div class="year">"Año de la Recuperación y Consolidación de la Economía Peruana"</div>
            <div class="year">"Madre de Dios Capital de la Biodiversidad del Perú"</div>
        </div>
    </div>

    <!-- Caja de constancia -->
    <div class="certificate-box">
        <div class="certificate-title">Constancia de Vacante</div>
        <div class="title-divider"></div>

        <!-- Modalidad -->
        <div class="modality-badge">
            <span class="modality-text">Modalidad CEPRE-UNAMAD</span>
        </div>

        <div class="content">
            <div class="intro-section">
                El Director del Centro Pre Universitario de la Universidad Nacional Amazónica de Madre de Dios.
            </div>

            <p style="font-weight: bold; text-align: center; font-size: 12pt; color: #2d3748; margin: 10px 0;">
                HACE CONSTAR, QUE EL/LA SEÑOR(ITA):
            </p>

            <div class="student-info">{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}</div>

            <div class="details-section">
                Estudiante del Centro Pre-Universitario <strong>CEPRE – UNAMAD</strong> con <strong>CÓDIGO N.° {{ $inscripcion->codigo_inscripcion }}</strong>, inscrito en la Carrera Profesional de <strong>{{ $carrera->nombre }}</strong> correspondiente al <strong>{{ $ciclo->nombre }}</strong>, según el Orden de Mérito ocupó una vacante por <strong>Modalidad vía CEPRE</strong>.
            </div>

            <p style="text-indent: 2em; margin: 10px 0; font-weight: 500;">
                Se expide la presente Constancia para los fines correspondientes del interesado.
            </p>

            <!-- Lugar y fecha -->
            <div style="text-align: right; margin-top: 15px;">
                <div class="date-place">
                    {{ $lugar }}, {{ $fecha }}
                </div>
            </div>

            <!-- Sección de firma 
            <div class="signature-section">
                <div class="signature-note">(Firma y sello del director)</div>
                <div class="signature-line"></div>
                <div class="signature-title">Director</div>
                <div class="signature-title">Centro Pre-Universitario</div>
            </div>-->
        </div>

        <!-- QR -->
        <div class="qr-lateral">
            <div class="qr-title">Verificación</div>
            <img src="data:image/png;base64,{{ $qr_code }}" alt="QR Code" class="qr-code">
            <div class="verification-code">{{ $codigo_verificacion }}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>UNAMAD:</strong> Av. Dos de Mayo N° 960 — Puerto Maldonado — CEL: 993110927 — 993111037 | <strong>CEPRE-UNAMAD</strong>
            <br>
            <span style="color: #930E29; font-weight: bold; font-size: 9pt; margin-top: 3px; display: block;">
                ***NOTA: ESTA CONSTANCIA DE VACANTE DEBE SER SUSTITUIDA POR OTRO DOCUMENTO EMITIDO POR LA OFICINA DE ADMISIÓN.***
            </span>
        </div>
    </div>
</body>
</html>