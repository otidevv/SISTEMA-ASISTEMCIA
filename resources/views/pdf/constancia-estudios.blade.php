<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Estudios</title>
    <style>
        @page {
            margin: 1cm;
            size: A4;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .university-name {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }

        .subtitle {
            font-size: 12pt;
            font-weight: bold;
            margin: 3px 0;
        }

        .year {
            font-size: 10pt;
            font-style: italic;
            margin: 3px 0;
        }

        .divider {
            text-align: center;
            margin: 15px 0;
            font-size: 12pt;
        }

        .certificate-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .certificate-number {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0;
        }

        .content {
            text-align: justify;
            margin: 20px 0;
            line-height: 1.6;
        }

        .student-info {
            margin: 15px 0;
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
        }

        .signature-section {
            margin-top: 40px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 0 auto;
            margin-top: 20px;
        }

        .date-place {
            text-align: right;
            margin: 30px 0;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            line-height: 1.2;
        }

        .qr-section {
            position: absolute;
            top: 20px;
            right: 20px;
            text-align: center;
        }

        .qr-code {
            width: 80px;
            height: 80px;
        }

        .verification-code {
            font-size: 8pt;
            margin-top: 5px;
            color: #666;
        }

        .photo-section {
            position: absolute;
            top: 100px;
            right: 20px;
            text-align: center;
        }

        .student-photo {
            width: 80px;
            height: 100px;
            border: 1px solid #000;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- QR Code -->
    <div class="qr-section">
        <img src="data:image/png;base64,{{ $qr_code }}" alt="QR Code" class="qr-code">
        <div class="verification-code">Código: {{ $codigo_verificacion }}</div>
    </div>

    <!-- Foto del estudiante -->
    @if($estudiante->foto_perfil && Storage::disk('public')->exists($estudiante->foto_perfil))
    <div class="photo-section">
        <img src="{{ Storage::disk('public')->url($estudiante->foto_perfil) }}" alt="Foto" class="student-photo">
    </div>
    @endif

    <div class="header">
        <div class="university-name">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</div>
        <div class="subtitle">"Centro Pre-Universitario"</div>
        <div class="year">"Año de la Recuperación y Consolidación de la Economía Peruana"</div>
        <div class="year">"Madre de Dios Capital de la Biodiversidad del Perú"</div>
    </div>

    <div class="divider">************************************************************</div>

    <div class="certificate-number">
        CONSTANCIA DE ESTUDIOS N.° {{ $numero_constancia }} – VRI-DIPROBIS-CEPRE
    </div>

    <div class="certificate-title">CONSTANCIA DE ESTUDIOS</div>

    <div class="content">
        EL DIRECTOR DEL CENTRO PRE UNIVERSITARIO DE LA UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS.<br><br>

        HACE CONSTAR, QUE LA ESTUDIANTE:<br><br>

        <div class="student-info">{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}</div><br>

        Identificado con DNI: {{ $estudiante->numero_documento }}, se encuentra a la fecha estudiando en el Centro Pre Universitario de la Universidad Nacional Amazónica de Madre de Dios en el presente {{ $ciclo->nombre }} ({{ $ciclo->fecha_inicio->format('d/m/Y') }} – {{ $ciclo->fecha_fin->format('d/m/Y') }}), con Código de Postulante N.° {{ $inscripcion->codigo_inscripcion }}, perteneciente a la Carrera Profesional de {{ $carrera->nombre }}, en el grupo {{ $aula ? $aula->nombre : 'N/A' }} y en el turno de la {{ $turno->nombre }}.<br><br>

        Se expide la presente constancia a solicitud de la interesada para los fines que estime por conveniente.
    </div>

    <div class="date-place">
        {{ $lugar }}, {{ $fecha }}
    </div>

    <div class="signature-section">
        Atentamente;<br><br><br>
        <div class="signature-line"></div>
        Director<br>
        Centro Pre-Universitario
    </div>

    <div class="footer">
        UNAMAD: Parque científico Tecnológico sostenible con Investigación e Innovación<br>
        Av. Dos de Mayo N° 960 — Puerto Maldonado — CEL: 917061893 — 975844977<br>
        CEPRE-UNAMAD CEL: 993110927
    </div>
</body>
</html>
