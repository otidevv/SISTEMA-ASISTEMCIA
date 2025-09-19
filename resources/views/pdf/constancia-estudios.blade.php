<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Estudios</title>
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

        /* 🔹 Marca de agua mejorada */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            z-index: -999;
            pointer-events: none;
        }

        /* 🔹 Encabezado mejorado */
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

        /* 🔹 Marco general mejorado */
        .certificate-box {
            margin: 10px 15px 100px 15px;
            padding: 15px 20px;
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            position: relative;
            min-height: calc(100vh - 200px);
        }

        /* 🔹 Numeración y título mejorados */
        .certificate-number {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 10px auto;
            color: #1a365d;
            padding: 8px 15px;
            border: 2px solid #1a365d;
            display: inline-block;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 25px;
            box-shadow: 0 4px 10px rgba(26, 54, 93, 0.15);
            position: relative;
        }

        .certificate-number::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #1a365d, #3182ce);
            border-radius: 27px;
            z-index: -1;
        }
        
        .certificate-title {
            text-align: center;
            font-size: 34pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
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
            margin: 10px 0 15px 0;
            border-radius: 2px;
        }

        /* 🔹 Contenido mejorado */
        .content {
            text-align: justify;
            font-size: 11pt;
            line-height: 1.4;
            margin-bottom: 60px;
        }
        
        .student-info {
            margin: 12px 0;
            text-align: center;
            font-weight: bold;
            font-size: 22pt;
            color: #dc2626;
            text-transform: uppercase;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            padding: 8px 15px;
            border-radius: 6px;
            border-left: 4px solid #dc2626;
            box-shadow: 0 3px 8px rgba(220, 38, 38, 0.15);
            letter-spacing: 0.6px;
        }
        
        .content strong {
            color: #1a365d;
            font-weight: bold;
        }

        .content p {
            margin: 8px 0;
        }

        .highlighted-text {
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%);
            padding: 8px;
            border-radius: 6px;
            border-left: 3px solid #1a365d;
            margin: 10px 0;
        }

        /* 🔹 Lugar y fecha mejorado */
        .date-place {
            text-align: right;
            margin: 15px 20px 10px 0;
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

        /* 🔹 QR mejorado */
        .qr-lateral {
            width: 80px;  /* prueba con 70px o 60px */
            height: auto;
            position: absolute;
            bottom: 80px;
            right: 40px;
            text-align: center;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            /*border: 2px solid #1a365d;*/
            padding: 2px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(26, 54, 93, 0.25);
            z-index: 10;
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
            width: 80px;
            height: 80px;
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

        /* 🔹 Footer mejorado */
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

        /* 🔹 Bordes decorativos para toda la página */
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

        /* 🔹 Esquinas decorativas eliminadas para dar espacio */

        /* 🔹 Animaciones sutiles para vista */
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
            <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="Logo UNAMAD">
        </div>
        <div class="header-logo-derecho">
            <img src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="Logo CEPRE">
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
        <div style="text-align: center;">
            <div class="certificate-number">
                CONSTANCIA DE ESTUDIOS N.° {{ $numero_constancia }} – VRI-DIPROBIS-CEPRE
            </div>
        </div>

        <div class="certificate-title">Constancia de Estudios</div>
        <div class="title-divider"></div>

        <div class="content">
            <div class="highlighted-text">
                <p style="font-weight: bold; text-transform: uppercase; text-align: center; color:#1a365d; margin: 0;">
                    El Director del Centro Pre Universitario de la Universidad Nacional Amazónica de Madre de Dios.
                </p>
            </div>

            <p style="font-weight: bold; text-align: center; font-size: 12pt; color: #2d3748;">
                HACE CONSTAR, QUE EL/LA ESTUDIANTE:
            </p>

            <div class="student-info">{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}</div>

            <p style="text-indent: 2em; margin-top: 10px;">
                Identificado(a) con DNI N° <strong>{{ $estudiante->numero_documento }}</strong>, se encuentra a la fecha estudiando en el Centro Pre Universitario de la Universidad Nacional Amazónica de Madre de Dios en el presente <strong>{{ $ciclo->nombre }}</strong> ({{ $ciclo->fecha_inicio->format('d/m/Y') }} – {{ $ciclo->fecha_fin->format('d/m/Y') }}), con Código de Postulante N° <strong>{{ $inscripcion->codigo_inscripcion }}</strong>, perteneciente a la Carrera Profesional de <strong>{{ $carrera->nombre }}</strong>, en el grupo <strong>{{ $aula ? $aula->nombre : 'N/A' }}</strong> y en el turno de la <strong>{{ $turno->nombre }}</strong>.
            </p>

            <p style="text-indent: 2em; margin-top: 10px; margin-bottom: 15px;">
                Se expide la presente constancia a solicitud del interesado(a) para los fines que estime por conveniente.
            </p>

            <!-- Lugar y fecha -->
            <div style="text-align: right; padding-right: -200px; margin-top: -20px;">
                <div class="date-place">
                    {{ $lugar }}, {{ $fecha }}
                </div>
            </div>
        </div>

        <!-- QR -->
        <div class="qr-lateral">
            <div class="qr-title">Verificación Digital</div>
            <img src="data:image/png;base64,{{ $qr_code }}" alt="QR de Validación" class="qr-code">
            <div class="verification-code">{{ $codigo_verificacion }}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>UNAMAD:</strong> Parque Científico Tecnológico Sostenible con Investigación e Innovación<br>
            Av. Dos de Mayo N° 960 — Puerto Maldonado — CEL: 917061893 — 975844977<br>
            <strong>CEPRE-UNAMAD</strong> CEL: 993110927
        
    </div>


</body>
</html>