<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Constancia de Estudios - UNAMAD Premium</title>
    <style>
        /* 🔹 Fuentes Premium */
        @font-face {
            font-family: 'Cinzel Decorative';
            src: url("{{ public_path('fonts/CinzelDecorative-Bold.ttf') }}") format('truetype');
            font-weight: bold;
        }

        @font-face {
            font-family: 'Playfair Display';
            src: url("{{ public_path('fonts/PlayfairDisplay-Bold.ttf') }}") format('truetype');
            font-weight: bold;
        }

        @page {
            margin: 0;
            size: A4;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            font-family: 'Helvetica', sans-serif;
        }

        /* 🔹 Contenedor Maestro */
        .page-wrapper {
            width: 210mm;
            height: 297mm;
            position: relative;
            background-image: url("https://www.transparenttextures.com/patterns/cream-paper.png");
            box-sizing: border-box;
        }

        /* 🔹 Borde Azul (Navy) - Capa Base */
        .border-navy {
            position: absolute;
            top: 10mm;
            left: 10mm;
            right: 10mm;
            bottom: 12mm;
            border: 3px double #0f172a;
            z-index: 1;
        }

        /* 🔹 Borde Dorado - Capa Independiente */
        .border-gold {
            position: absolute;
            top: 12mm;
            left: 12mm;
            right: 12mm;
            bottom: 14mm;
            border: 1px solid #b45309;
            z-index: 2;
        }

        /* Esquinas Ornamentales */
        .corner {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 3px solid #b45309;
            z-index: 10;
        }

        .top-left {
            top: 12mm;
            left: 12mm;
            border-right: 0;
            border-bottom: 0;
            margin: -1px;
        }

        .top-right {
            top: 12mm;
            right: 12mm;
            border-left: 0;
            border-bottom: 0;
            margin: -1px;
        }

        .bottom-left {
            bottom: 14mm;
            left: 12mm;
            border-right: 0;
            border-top: 0;
            margin: -1px;
        }

        .bottom-right {
            bottom: 14mm;
            right: 12mm;
            border-left: 0;
            border-top: 0;
            margin: -1px;
        }

        /* Marca de Agua */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 450px;
            margin-left: -225px;
            margin-top: -225px;
            opacity: 0.05;
            z-index: 0;
        }

        /* 🔹 Contenedor de Texto */
        .content-container {
            position: absolute;
            top: 25mm;
            left: 25mm;
            right: 25mm;
            z-index: 20;
        }

        /* Header - Logos GRANDES */
        .header-table {
            width: 100%;
            margin-bottom: 8px;
        }

        .univ-title {
            font-family: 'Cinzel Decorative', serif;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            text-align: center;
        }

        .cepre-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 11.5pt;
            font-weight: bold;
            color: #b45309;
            text-align: center;
        }

        .pill-number {
            background: #0f172a;
            color: #fff;
            padding: 4px 18px;
            font-size: 9pt;
            font-weight: bold;
            border-radius: 4px;
            display: inline-block;
            margin: 10px 0;
            letter-spacing: 1px;
        }

        .main-title {
            text-align: center;
            margin: 10px 0;
            color: #0f172a;
        }

        .main-title h1 {
            font-family: 'Cinzel Decorative', serif;
            font-size: 34pt;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .main-title span {
            font-family: 'Playfair Display', serif;
            font-size: 24pt;
            color: #b45309;
            display: block;
            margin-top: -5px;
        }

        .director-box {
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 8px 0;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5pt;
            margin-bottom: 15px;
            color: #0f172a;
        }

        .constar-label {
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            color: #64748b;
            margin: 0;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .student-name {
            font-family: 'Playfair Display', serif;
            font-size: 26pt;
            font-weight: bold;
            color: #dc2626;
            text-align: center;
            margin: 12px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #fee2e2;
        }

        .body-text {
            text-align: justify;
            font-size: 11.5pt;
            line-height: 1.5;
            color: #1e293b;
        }

        /* 🔹 Footer Anclado - Al ras de las líneas */
        .footer-area {
            position: absolute;
            bottom: 16mm;
            left: 20mm;
            right: 20mm;
            z-index: 30;
        }

        .footer-line {
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .qr-box {
            width: 85px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            background: #fff;
            text-align: center;
        }

        .footer-info {
            text-align: right;
            font-size: 7.5pt;
            color: #64748b;
            line-height: 1.2;
        }
    </style>
</head>

<body>

    <div class="page-wrapper">

        <!-- Bordes Independientes -->
        <div class="border-navy"></div>
        <div class="border-gold"></div>

        <!-- Esquinas -->
        <div class="corner top-left"></div>
        <div class="corner top-right"></div>
        <div class="corner bottom-left"></div>
        <div class="corner bottom-right"></div>

        <!-- Marca de Agua -->
        <img src="{{ public_path('assets/images/logo unamad constancia.png') }}" class="watermark">

        <!-- Área de Contenido -->
        <div class="content-container">

            <table class="header-table">
                <tr>
                    <!-- Logo UNAMAD ajustado a 95px -->
                    <td width="105"><img src="{{ public_path('assets/images/logo unamad constancia.png') }}" width="95">
                    </td>
                    <td>
                        <div class="univ-title">Universidad Nacional Amazónica de Madre de Dios</div>
                        <div class="cepre-subtitle">"Centro Pre-Universitario"</div>
                        <p
                            style="font-size: 6.5pt; text-align: center; text-transform: uppercase; color: #64748b; margin-top: 5px; line-height: 1.1;">
                            "Año de la Recuperación y Consolidación de la Economía Peruana"<br>
                            "Madre de Dios Capital de la Biodiversidad del Perú"
                        </p>
                    </td>
                    <!-- Logo CEPRE ajustado a 95px -->
                    <td width="105" align="right"><img src="{{ public_path('assets/images/logo cepre costancia.png') }}"
                            width="95"></td>
                </tr>
            </table>

            <div style="text-align: center;">
                <div class="pill-number">
                    CONSTANCIA DE ESTUDIOS N.º {{ $numero_constancia }} – VRI-DIPROBIS-CEPRE
                </div>
            </div>

            <div class="main-title">
                <h1>CONSTANCIA DE</h1>
                <span>ESTUDIOS</span>
            </div>

            <div class="director-box">
                El Director del Centro Pre Universitario de la Universidad Nacional Amazónica de Madre de Dios.
            </div>

            <p class="constar-label">HACE CONSTAR, QUE EL/LA ESTUDIANTE:</p>

            <div class="student-name">
                {{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}
            </div>

            <div class="body-text">
                <p style="text-indent: 3em;">
                    Identificado(a) con DNI N° <b>{{ $estudiante->numero_documento }}</b>, se encuentra a la fecha
                    estudiando en el Centro Pre Universitario de la Universidad Nacional Amazónica de Madre de Dios en
                    el presente <b>{{ $ciclo->nombre }}</b> ({{ $ciclo->fecha_inicio->format('d/m/Y') }} –
                    {{ $ciclo->fecha_fin->format('d/m/Y') }}), con Código de Postulante N°
                    <b>{{ $inscripcion->codigo_inscripcion }}</b>, perteneciente a la Carrera Profesional de
                    <b>{{ $carrera->nombre }}</b>, en el grupo <b>{{ $aula ? $aula->nombre : 'N/A' }}</b> y en el turno
                    de la <b>{{ $turno->nombre }}</b>.
                </p>
                <p style="text-indent: 3em; margin-top: 12px;">
                    Se expide la presente constancia a solicitud del interesado(a) para los fines que estime por
                    conveniente.
                </p>
            </div>

            <div style="text-align: right; margin-top: 25px; font-weight: bold;">
                <span style="border-bottom: 1px solid #b45309; padding-bottom: 2px; font-size: 11pt;">
                    {{ $lugar }}, {{ $fecha }}
                </span>
            </div>

            <!-- Espacio para firma del Director optimizado -->
            <div style="height: 70px;"></div>
        </div>

        <!-- Footer Anclado - Al ras de las líneas -->
        <div class="footer-area">
            <div class="footer-line">
                <table width="100%">
                    <tr>
                        <td width="100">
                            <div class="qr-box">
                                <img src="data:image/png;base64,{{ $qr_code }}" width="75">
                                <div style="font-size: 4.5pt; font-family: monospace; margin-top: 1px;">
                                    {{ $codigo_verificacion }}
                                </div>
                            </div>
                            <div
                                style="font-size: 6.5pt; font-weight: bold; text-align: center; margin-top: 3px; color: #0f172a;">
                                VERIFICACIÓN DIGITAL</div>
                        </td>
                        <td class="footer-info">
                            <p style="margin: 0;"><b>UNAMAD:</b> Parque Científico Tecnológico Sostenible con
                                Investigación e Innovación</p>
                            <p style="margin: 2px 0;">Av. Dos de Mayo N° 960 — Puerto Maldonado — CEL: 993110927 —
                                993111037</p>
                            <p style="margin: 0; font-weight: bold; color: #0f172a; font-size: 8.5pt;">CEPRE-UNAMAD</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

</body>

</html>