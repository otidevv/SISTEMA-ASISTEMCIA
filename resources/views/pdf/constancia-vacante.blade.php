<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Vacante</title>
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

        .certificate-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .modality {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 15px 0;
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
        <div class="year">"Año de la Recuperación y Consolidación de la Economía Peruana"</div>
        <div class="year">"Madre de Dios Capital de la Biodiversidad del Perú"</div>
        <div class="subtitle">"CENTRO PREUNIVERSITARIO"</div>
    </div>

    <div class="certificate-title">CONSTANCIA DE VACANTE</div>

    <div class="modality">MODALIDAD CEPRE-UNAMAD</div>

    <div class="content">
        Mediante la presente, el director del Centro Pre-Universitario de la UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS, hace constar que:<br><br>

        El o (La) Señor (ita):<br><br>

        <div class="student-info">{{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}</div><br>

        Estudiante del Centro Pre-Universitario CEPRE – UNAMAD con CÓDIGO N.° {{ $inscripcion->codigo_inscripcion }}, inscrito en la Carrera Profesional de {{ $carrera->nombre }} correspondiente al {{ $ciclo->nombre }}, según el Orden de Mérito ocupó una vacante por Modalidad vía CEPRE.<br><br>

        Se expide la presente Constancia para los fines correspondientes del interesado.
    </div>

    <div class="date-place">
        {{ $lugar }}, {{ $fecha }}
    </div>

    <div class="signature-section">
        (Firma y sello del director)<br><br><br>
        <div class="signature-line"></div>
        Director<br>
        Centro Pre-Universitario
    </div>
</body>
</html>
