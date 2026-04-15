<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pack de Inscripción - {{ $programa_nombre ?? 'CEPRE UNAMAD' }}</title>
    <style>
        @page { margin: 1cm; size: A4; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        
        .header { position: relative; width: 100%; border-bottom: 3px solid #00aeef; padding-bottom: 5px; margin-bottom: 10px; text-align: center; }
        .header-logo { position: absolute; left: 0; top: 0; width: 85px; }
        .header-logo-right { position: absolute; right: 0; top: 0; width: 90px; }
        .header-text h1 { font-size: 14pt; margin: 0; color: #003366; text-transform: uppercase; font-family: 'Arial Black', 'Arial Bold', sans-serif; }
        .header-text h2 { font-size: 12pt; margin: 2px 0; color: #00aeef; font-weight: bold; }
        .header-text p { margin: 0; font-size: 10pt; }
        
        .watermark { 
            position: fixed; 
            top: 40%; 
            left: 10%; 
            font-size: 60pt; 
            color: rgba(0, 0, 0, 0.04); 
            transform: rotate(-45deg); 
            z-index: -1; 
            pointer-events: none; 
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }

        .title-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; text-align: center; margin-bottom: 20px; border-radius: 8px; }
        .title-box h3 { margin: 0; font-size: 13pt; color: #ec008c; text-transform: uppercase; letter-spacing: 1px; }

        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 20px;
            color: #ec008c;
            text-transform: uppercase;
        }
        .content {
            margin: 0;
            text-align: justify;
        }
        .section-title { background: #003366; color: white; padding: 5px 10px; font-weight: bold; font-size: 11pt; margin-top: 15px; margin-bottom: 10px; border-radius: 4px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .info-table td.label { font-weight: bold; width: 35%; color: #555; background: #fcfcfc; }
        .info-table td.value { font-weight: bold; font-size: 11pt; text-transform: uppercase; color: #000; }

        .form-row {
            margin-bottom: 12px;
        }
        .form-label {
            font-weight: bold;
            display: inline-block;
        }
        .form-value {
            display: inline-block;
            border-bottom: 1px dotted #000;
            min-width: 200px;
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }

        .content { margin: 0; text-align: justify; line-height: 1.6; }
        
        .signature-container { margin-top: 50px; width: 100%; text-align: center; }
        .signature-box { 
            text-align: center; 
            width: 300px; 
            margin: 60px auto 0 auto; 
            display: inline-block;
        }
        .signature-line { border-top: 1.5px solid #333; width: 100%; margin-bottom: 5px; }
        .signature-text { font-size: 9pt; font-weight: bold; color: #333; }
        
        .fingerprint-box { 
            border: 1px solid #999; 
            width: 75px; 
            height: 95px; 
            margin: 10px auto; 
            display: block; 
            background: #fff;
            position: relative;
        }
        .fingerprint-text { font-size: 7pt; color: #999; position: absolute; bottom: 5px; width: 100%; text-align: center; }

        .footer-doc {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #777;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }

        .check-box { 
            display: inline-block; 
            width: 15px; 
            height: 15px; 
            border: 1px solid #000; 
            vertical-align: middle; 
            text-align: center; 
            line-height: 15px; 
            font-weight: bold;
            margin-right: 5px;
        }
        
        .qr-seal {
            position: fixed;
            bottom: -22px;
            right: 15px;
            width: 105px;
            text-align: center;
            z-index: 1000;
            background: #fff;
            border: 1px solid #eee;
            padding: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .qr-seal img {
            width: 85px;
            height: 85px;
            display: block;
            margin: 0 auto;
        }
        .qr-label {
            font-size: 7pt;
            font-weight: bold;
            color: #003366;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .qr-desc {
            font-size: 5.5pt;
            color: #999;
            margin-top: 2px;
            line-height: 1.1;
        }
        
        strong { color: #000; }
        ol li { margin-bottom: 10px; padding-left: 5px; }
    </style>
</head>
<body>

@php
    // URL de validación oficial (usando el código del programa como prefijo)
    $prefix = $programa_id == 2 ? 'REF' : 'CEP';
    $urlValidacion = route('constancias.validar', $prefix . '-' . $estudiante_dni);
    
    // Generación del código QR con la URL de validación
    $qrCodeBase64 = base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(400)->margin(0)->generate($urlValidacion));
@endphp

<div class="qr-seal">
    <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Validation">
    <div class="qr-label">VALIDACIÓN QR</div>
    <div class="qr-desc">Veracidad verificable mediante escaneo digital</div>
</div>

<div class="footer-doc">
    “UNAMAD: Parque científico Tecnológico sostenible con Investigación e Innovación”<br>
    Av. Dos de Mayo Nº 960 – Puerto Maldonado – CEL: 993111037 – 993110327
</div>

<div class="watermark">{{ strtoupper($programa_nombre ?? 'INSCRIPCIÓN CEPRE') }}</div>

<!-- PÁGINA 1: FICHA DEL APODERADO -->
<div class="header">
    <div style="font-size: 8pt; margin-bottom: 5px; font-weight: bold; color: #555;">“Año de la Esperanza y el Fortalecimiento de la Democracia”</div>
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>{{ $programa_titulo ?? 'Ciclo Académico CEPRE' }} {{ date('Y') }}</p>
    </div>
</div>

<div class="title-box">
    <h3>FICHA DEL APODERADO O TUTOR</h3>
    <p>{{ strtoupper($programa_descripcion ?? 'CENTRO PREUNIVERSITARIO') }}</p>
</div>

<div class="content">
    <div class="section-header">DATOS DEL TUTOR O APODERADO</div>
    <table class="info-table">
        <tr>
            <td class="label">1. Apellidos y Nombres:</td>
            <td class="value">{{ $apoderado_nombre }}</td>
        </tr>
        <tr>
            <td class="label">2. Número de documento:</td>
            <td class="value">{{ $apoderado_dni }}</td>
        </tr>
        <tr>
            <td class="label">3. Número de Celular:</td>
            <td class="value">{{ $apoderado_celular }}</td>
        </tr>
        <tr>
            <td class="label">4. Dirección Actual:</td>
            <td class="value">{{ $apoderado_direccion }}</td>
        </tr>
    </table>

    <div class="section-header">5. Relación del Tutor con el estudiante</div>
    <div style="padding: 10px 20px;">
        @php
            $parDoc = strtoupper($apoderado_parentesco ?? '');
            $isPadreMadre = str_contains($parDoc, 'PADRE') || str_contains($parDoc, 'MADRE');
            $isHermano = str_contains($parDoc, 'HERMANO');
            $isFamiliar = str_contains($parDoc, 'ABUELO') || str_contains($parDoc, 'TIO') || str_contains($parDoc, 'PRIMO') || str_contains($parDoc, 'TUTOR');
        @endphp
        <p><span class="check-box" {!! $isPadreMadre ? 'style="background: #eef;"' : '' !!}>{{ $isPadreMadre ? 'X' : '' }}</span> Padre/Madre</p>
        <p><span class="check-box" {!! $isHermano ? 'style="background: #eef;"' : '' !!}>{{ $isHermano ? 'X' : '' }}</span> Hermano/Hermana</p>
        <p><span class="check-box" {!! $isFamiliar ? 'style="background: #eef;"' : '' !!}>{{ $isFamiliar ? 'X' : '' }}</span> Familiar (Abuelo, tío, primo)</p>
        <p><span class="check-box"></span> No tiene (Trabaja o se solventa sus estudios)</p>
    </div>

    <div class="section-header">DATOS DEL ESTUDIANTE POSTULANTE</div>
    <table class="info-table">
        <tr>
            <td class="label">6. Estudiante:</td>
            <td class="value"><strong>{{ strtoupper($estudiante_nombre) }}</strong></td>
        </tr>
        <tr>
            <td class="label">7. DNI Estudiante:</td>
            <td class="value"><strong>{{ $estudiante_dni }}</strong></td>
        </tr>
        @if($programa_id == 1)
            <tr>
                <td class="label">8. Carrera a Postular:</td>
                <td class="value"><strong>{{ strtoupper($carrera_nombre ?? 'N/A') }}</strong></td>
            </tr>
            <tr>
                <td class="label">9. Turno Elegido:</td>
                <td class="value"><strong>{{ strtoupper($turno_nombre ?? 'N/A') }}</strong></td>
            </tr>
        @else
            <tr>
                <td class="label">8. Grado Escolar:</td>
                <td class="value"><strong>{{ strtoupper($estudiante_grado ?? 'N/A') }}</strong></td>
            </tr>
            <tr>
                <td class="label">9. Turno/Sección:</td>
                <td class="value"><strong>{{ strtoupper($turno_nombre ?? 'N/A') }}</strong></td>
            </tr>
        @endif
    </table>

    <div style="text-align: right; margin-top: 80px; margin-bottom: 50px; font-weight: bold; color: #003366;">
        Puerto Maldonado, {{ date('d') }} de {{ $mes_actual }} del {{ date('Y') }}
    </div>

    <div class="signature-container" style="margin-top: 50px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <!-- Bloque Firma -->
                <td style="width: 33%; vertical-align: bottom; text-align: center; padding: 0 10px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 5px;"></div>
                    <div class="signature-text">FIRMA DEL APODERADO</div>
                    <div style="font-size: 9pt; margin-top: 5px;">DNI: <strong>{{ $apoderado_dni }}</strong></div>
                </td>
                
                <!-- Bloque Huella -->
                <td style="width: 33%; text-align: center; padding: 0 10px;">
                    <div class="fingerprint-box" style="margin: 0 auto;">
                        <div class="fingerprint-text">Huella Digital</div>
                    </div>
                    <div style="font-size: 8pt; margin-top: 5px; color: #666;">(Índice Derecho)</div>
                </td>
                
                <!-- Bloque Sello CEPRE -->
                <td style="width: 33%; vertical-align: bottom; text-align: center; padding: 0 10px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 5px;"></div>
                    <div class="signature-text">Visto Bueno</div>
                    <div style="font-size: 9pt; font-weight: bold; color: #003366;">COORDINACIÓN CEPRE</div>
                    <div style="font-size: 8pt; margin-top: 2px; color: #666;">Dirección / Coordinador Académico</div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="page-break"></div>

<!-- PÁGINA 2: CARTA COMPROMISO -->
<div class="header">
    <div style="font-size: 8pt; margin-bottom: 5px; font-weight: bold; color: #555;">“Año de la Esperanza y el Fortalecimiento de la Democracia”</div>
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>{{ $programa_titulo ?? 'Ciclo Académico CEPRE' }} {{ date('Y') }}</p>
    </div>
</div>

<div class="title-box">
    <h3>CARTA COMPROMISO</h3>
    <p>ACEPTACIÓN DE REGLAMENTO Y NORMAS INTERNAS</p>
</div>

<div class="content">
    <div style="text-align: right; margin-bottom: 25px; font-weight: bold; color: #003366;">
        Puerto Maldonado, {{ date('d') }} de {{ $mes_actual }} del {{ date('Y') }}
    </div>

    <p style="margin-bottom: 15px;">Yo <strong>{{ strtoupper($estudiante_nombre) }}</strong> estudiante de CEPRE me comprometo a:</p>

    <ol style="margin-bottom: 30px;">
        <li style="margin-bottom: 8px;"><strong>Cumplimiento del Reglamento:</strong> Leer atentamente y cumplir con todas las normas establecidas en el reglamento interno.</li>
        <li style="margin-bottom: 8px;"><strong>Asistencia:</strong> Asistir puntualmente y de manera obligatoria a todas las clases.</li>
        <li style="margin-bottom: 8px;"><strong>Cuidado de las Instalaciones:</strong> Se prohíbe pintar o rayar las paredes de las instalaciones y el mobiliario del centro.</li>
        <li style="margin-bottom: 8px;"><strong>Identificación:</strong> Portar en todo momento el CARNET de CEPRE como identificación oficial.</li>
        <li style="margin-bottom: 8px;"><strong>Uso del Celular:</strong> Se prohíbe el uso de teléfonos celulares durante las horas de clases.</li>
        <li style="margin-bottom: 8px;"><strong>Aceptación de Sanciones:</strong> El incumplimiento de estas normas podrá acarrear sanciones disciplinarias. La firma al final de este documento implica la aceptación de este reglamento y sus consecuencias.</li>
    </ol>

    <div class="signature-container" style="margin-top: 40px; margin-bottom: 40px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <!-- Bloque Firma Alumno -->
                <td style="width: 38%; vertical-align: bottom; text-align: center; padding: 0 10px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 8px;"></div>
                    <div class="signature-text">FIRMA DEL ESTUDIANTE</div>
                    <div style="font-size: 10pt; margin-top: 5px;">DNI: <strong>{{ $estudiante_dni }}</strong></div>
                </td>
                
                <!-- Bloque Huella -->
                <td style="width: 24%; text-align: center; padding: 0 10px;">
                    <div class="fingerprint-box" style="margin: 0 auto; width: 80px; height: 100px;">
                        <div class="fingerprint-text" style="margin-top: 40px;">Huella Digital</div>
                    </div>
                </td>
                
                <!-- Bloque Sello CEPRE -->
                <td style="width: 38%; vertical-align: bottom; text-align: center; padding: 0 10px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 8px;"></div>
                    <div class="signature-text" style="font-size: 10pt; font-weight: bold;">V° B°</div>
                    <div style="font-size: 10pt; font-weight: bold; color: #003366;">COORDINACIÓN CEPRE</div>
                    <div style="font-size: 8pt; color: #666;">Dirección / Coordinador Académico</div>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 40px; font-size: 9pt; background: #fff; border: 2px solid #00aeef; padding: 15px; border-radius: 10px;">
        <div style="text-align: center; font-weight: bold; color: #003366; margin-bottom: 10px; text-transform: uppercase; font-size: 10pt; border-bottom: 1px solid #00aeef; padding-bottom: 5px;">REGLAMENTO CENTRO PREUNIVERSITARIO UNAMAD</div>
        <p style="margin-bottom: 8px;"><strong>Artículo. 40°:</strong> La asistencia a clases es obligatoria y se registra diariamente. El registro de asistencia es responsabilidad del personal asistencial del CEPRE con colaboración del docente de aula.</p>
        <p style="margin-bottom: 8px;"><strong>Artículo. 41º:</strong> El estudiante que acumula el 20% o más de inasistencias en el periodo previo a cada examen sumativo es AMONESTADO, medida que es comunicada al padre de familia o apoderado para conocimiento. El 30% de inasistencias INHABILITA al estudiante para rendir el examen sumativo respectivo.</p>
        <p style="font-style: italic; color: #666; border-top: 1px dashed #ccc; padding-top: 5px; margin-top: 5px;"><strong>NOTA:</strong> EL INTEGRO DEL TEXTO DEL REGLAMENTO DE CEPRE SE ENCUENTRA EN NUESTRA PAGINA WEB DEL CEPRE UNAMAD.</p>
    </div>
</div>

<div class="page-break"></div>

<!-- PÁGINA 3: DECLARACIÓN JURADA -->
<div class="header">
    <div style="font-size: 8pt; margin-bottom: 5px; font-weight: bold; color: #555;">“Año de la Esperanza y el Fortalecimiento de la Democracia”</div>
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>{{ $programa_titulo ?? 'Ciclo Académico CEPRE' }} {{ date('Y') }}</p>
    </div>
</div>

<div class="title-box">
    <h3>DECLARACIÓN JURADA</h3>
    <p>CONOCIMIENTO Y ACEPTACIÓN DEL REGLAMENTO INTERNO</p>
</div>

<div class="content" style="margin-top: 20px;">
    <p style="margin-bottom: 20px;">Yo <strong>{{ strtoupper($apoderado_nombre) }}</strong> identificado con Documento Nacional de Identidad N.° <strong>{{ $apoderado_dni }}</strong> con domicilio actual sito en <strong>{{ strtoupper($apoderado_direccion) }}</strong> del distrito de Tambopata, provincia de Tambopata, departamento de Madre de Dios, señalando número de celular <strong>{{ $apoderado_celular }}</strong>, padre de familia de mi menor hijo(a) <strong>{{ strtoupper($estudiante_nombre) }}</strong>.</p>
    
    <div class="section-header" style="text-align: center; background: #eef7ff; color: #003366; border: 1.5px solid #00aeef; padding: 8px; font-weight: bold; margin-bottom: 25px;">DECLARO BAJO JURAMENTO</div>
    
    <p style="margin-bottom: 20px;">Que he tomado conocimiento y haber leido el texto íntegro de los Artículos 37, 40 y 41 del Reglamento Interno del Centro Pre-Universitario de la UNAMAD aprobado por Resolución de Consejo Universitario Nª 510-2017-UNAMADS-CU de fecha 14 de agosto del 2017, por la cual DECLARO CONOCER Y ACEPTAR el Reglamento Interno del CEPRE – UNAMAD.</p>
    
    <p style="margin-bottom: 20px;">Formulo la presente declaración en virtud del artículo 38° de la Constitución Política del Perú, en concordancia con el principio de presunción de veracidad previsto en los artículos IV numeral 1.7 y 42° del TUO de la Ley 27444 – Ley del Procedimiento Administrativo General, sujetándome a las acciones administrativas y/o legales que correspondan de acuerdo a la legislación nacional vigente.</p>
    
    <p style="margin-top: 30px; margin-bottom: 30px; font-weight: bold; color: #003366; text-align: left;">En fe de lo cual firmo la presente a los {{ date('d') }} días del mes de {{ $mes_actual }} del año {{ date('Y') }}.</p>

    <div class="signature-container" style="margin-top: 120px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <!-- Bloque Firma Apoderado -->
                <td style="width: 38%; vertical-align: bottom; text-align: center; padding: 0 10px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 8px;"></div>
                    <div class="signature-text">FIRMA DEL APODERADO</div>
                    <div style="font-size: 9pt; margin-top: 5px;">
                        Nombre: <strong>{{ strtoupper($apoderado_nombre) }}</strong><br>
                        DNI: <strong>{{ $apoderado_dni }}</strong>
                    </div>
                </td>
                
                <!-- Bloque Huella -->
                <td style="width: 24%; text-align: center; padding: 0 10px;">
                    <div class="fingerprint-box" style="margin: 0 auto; width: 80px; height: 100px;">
                        <div class="fingerprint-text" style="margin-top: 40px;">Huella Digital</div>
                    </div>
                </td>
                
                <!-- Bloque Sello CEPRE -->
                <td style="width: 38%; vertical-align: bottom; text-align: center; padding: 0 10px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 8px;"></div>
                    <div class="signature-text" style="font-size: 10pt; font-weight: bold;">V° B°</div>
                    <div style="font-size: 10pt; font-weight: bold; color: #003366;">COORDINACIÓN CEPRE</div>
                    <div style="font-size: 8pt; color: #666;">Dirección / Coordinador Académico</div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="page-break"></div>

<!-- PÁGINA 4: AUTORIZACIÓN RETIRO -->
<div class="header">
    <div style="font-size: 8pt; margin-bottom: 5px; font-weight: bold; color: #555;">“Año de la Esperanza y el Fortalecimiento de la Democracia”</div>
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>{{ $programa_titulo ?? 'Ciclo Académico CEPRE' }} {{ date('Y') }}</p>
    </div>
</div>

<div class="title-box" style="margin-top: 5px; padding: 8px;">
    <h3 style="font-size: 11pt; margin-bottom: 0;">AUTORIZACIÓN PARA EL RETIRO DE ESTUDIANTES DURANTE EL CICLO ACADEMICO DEL CEPRE DE LA UNAMAD</h3>
</div>

<div class="content" style="font-size: 8.5pt; line-height: 1.25;">
    <p style="margin-bottom: 10px;">Yo, <strong>{{ strtoupper($apoderado_nombre) }}</strong> identificado con DNI N° <strong>{{ $apoderado_dni }}</strong> con domicilio en <strong>{{ strtoupper($apoderado_direccion) }}</strong> teléfono <strong>{{ $apoderado_celular }}</strong> manifiesto que soy el/la <strong>{{ $apoderado_parentesco ?? 'Tutor' }}</strong> (padre, madre o tutor) del estudiante menor de edad de nombre <strong>{{ strtoupper($estudiante_nombre) }}</strong> de <strong>{{ $estudiante_edad ?? '____' }}</strong> años de edad; de conformidad con lo dispuesto en los artículos 74° y 75° de la Ley N° 27337 – Código de los Niños y Adolescentes, lo siguiente:</p>
    
    <div style="margin: 10px 0;">
        <span style="font-weight: bold;">Marque su CICLO ACADÉMICO:</span>
        @php
            $nombreCicloDoc = strtoupper($ciclo_nombre ?? '');
            $esIntensivo = str_contains($nombreCicloDoc, 'INTENSIVO');
            $esOrdinario1 = str_contains($nombreCicloDoc, 'ORDINARIO') && str_contains($nombreCicloDoc, '1');
            $esOrdinario2 = str_contains($nombreCicloDoc, 'ORDINARIO') && str_contains($nombreCicloDoc, '2');
            $esReforzamiento = ($programa_id == 2) || str_contains($nombreCicloDoc, 'REFORZAMIENTO');
        @endphp
        <span style="margin-left: 10px;">
            <span class="check-box" {!! $esIntensivo ? 'style="background: #eef;"' : '' !!}>{{ $esIntensivo ? 'X' : '' }}</span> 
            <span style="{{ $esIntensivo ? 'color: #003366; font-weight: bold;' : 'color: #777;' }}">Intensivo 2026-0</span>
        </span>
        <span style="margin-left: 10px;">
            <span class="check-box" {!! $esOrdinario1 ? 'style="background: #eef;"' : '' !!}>{{ $esOrdinario1 ? 'X' : '' }}</span> 
            <span style="{{ $esOrdinario1 ? 'color: #003366; font-weight: bold;' : 'color: #777;' }}">Ordinario 2026-1</span>
        </span><br>
        <span style="margin-left: 168px;">
            <span class="check-box" {!! $esOrdinario2 ? 'style="background: #eef;"' : '' !!}>{{ $esOrdinario2 ? 'X' : '' }}</span> 
            <span style="{{ $esOrdinario2 ? 'color: #003366; font-weight: bold;' : 'color: #777;' }}">Ordinario 2026-2</span>
        </span>
        <span style="margin-left: 10px;">
            <span class="check-box" {!! $esReforzamiento ? 'style="background: #eef;"' : '' !!}>{{ $esReforzamiento ? 'X' : '' }}</span> 
            <span style="{{ $esReforzamiento ? 'color: #ec008c; font-weight: bold;' : 'color: #777;' }}">Reforzamiento para Secundaria</span>
        </span>
    </div>

    <p style="font-weight: bold; margin-bottom: 10px;">Marque con una X una de las dos casillas la opción que desee:</p>
    
    <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-bottom: 10px; background: #fff;">
        <p style="margin-bottom: 5px;"><strong>[ &nbsp; ] AUTORIZO</strong> que mi hijo(a) o representado(a) legal se retire solo(a) a su domicilio al finalizar el horario de clases del Centro Preuniversitario, sin que ningún adulto se responsabilice de acompañarlo(a).</p>
        <p style="font-size: 8pt; color: #444; margin-bottom: 5px;">Que tengo pleno conocimiento de que el Centro Preuniversitario de la UNAMAD cuenta con un sistema de control de asistencia mediante detector de huella digital, el cual registra el ingreso y la salida del estudiante de las instalaciones, y que el registro es notificado en tiempo real al padre, madre o representante legal a través de mensaje electrónico.</p>
        <p style="font-size: 8pt; color: #444; margin-bottom: 5px;">En tal sentido, reconozco expresamente que dicho registro constituye constancia suficiente del momento en que el estudiante ingresa y se retira del recinto institucional, delimitando de manera objetiva la responsabilidad de la institución.</p>
        <p>Domicilio del estudiante: _________________________________________________________________</p>
    </div>

    <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
        <p style="margin-bottom: 5px;"><strong>[ &nbsp; ] NO AUTORIZO</strong> a que mi hijo (a) o representado legal se vaya solo a casa cuando finalice la jornada escolar. La entrega se efectuará UNICAMENTE al familiar o persona autorizada para recogerlo es: _________________________________________________ celular ___________________ quien deberá identificarse con DNI vigente.</p>
        <p style="font-size: 8pt; color: #444;">Declaro estar consciente de que el Centro Preuniversitario de la UNAMAD únicamente entregará al menor de edad a los padres o apoderado y/o persona autorizada a través del presente documento con el fin de salvaguardar su seguridad y en cumplimiento de los protocolos de entrega de menores, no asumiendo responsabilidad por cualquier situación posterior.</p>
    </div>

    <p style="font-size: 8.5pt; background: #fdf2f2; padding: 8px; border-left: 4px solid #cc0000; color: #000; margin-bottom: 10px;"><strong>En tal sentido, EXONERO EXPRESAMENTE DE TODA RESPONSABILIDAD</strong> a la UNAMAD, a su Centro Preuniversitario, autoridades, docentes y personal administrativo, por cualquier hecho, daño, accidente o contingencia que pudiera ocurrir con posterioridad a la salida del menor de la institución.</p>
    
    <p style="text-align: right; margin-bottom: 40px; font-weight: bold; color: #003366;">
        Puerto Maldonado, {{ date('d') }} de {{ $mes_actual }} del {{ date('Y') }}.
    </p>

    <div class="signature-container">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <!-- Bloque Firma Alumno -->
                <td style="width: 38%; vertical-align: bottom; text-align: left; padding: 0 5px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 5px;"></div>
                    <div class="signature-text" style="font-size: 8pt; text-align: left;">Firma del padre, madre o tutor(a) legal:</div>
                    <div style="font-size: 8pt; margin-top: 4px;">Nombre completo: <strong>{{ strtoupper($apoderado_nombre) }}</strong></div>
                    <div style="font-size: 8pt;">Telf. Casa o celular: <strong>{{ $apoderado_celular }}</strong></div>
                    <div style="font-size: 8pt;">Telf. Emergencias: _________________</div>
                </td>
                
                <!-- Bloque Huella -->
                <td style="width: 24%; text-align: center; padding: 0 5px;">
                    <div class="fingerprint-box" style="margin: 0 auto; width: 70px; height: 90px;">
                        <div class="fingerprint-text" style="font-size: 7pt; margin-top: 35px;">Huella Digital</div>
                    </div>
                </td>
                
                <!-- Bloque Sello CEPRE -->
                <td style="width: 38%; vertical-align: bottom; text-align: center; padding: 0 5px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 8px;"></div>
                    <div class="signature-text" style="font-size: 9pt; font-weight: bold;">V° B°</div>
                    <div style="font-size: 9pt; font-weight: bold; color: #003366;">COORDINACIÓN CEPRE</div>
                    <div style="font-size: 7.5pt; color: #666;">Dirección / Coordinador Académico</div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="page-break"></div>

<!-- PÁGINA 5: DATOS BIOMÉTRICOS -->
<div class="header">
    <div style="font-size: 8pt; margin-bottom: 5px; font-weight: bold; color: #555; text-align: center;">“Año de la Esperanza y el Fortalecimiento de la Democracia”</div>
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>{{ $programa_titulo ?? 'Ciclo Académico CEPRE' }} {{ date('Y') }}</p>
    </div>
</div>

<div class="title-box" style="margin-top: 5px; padding: 12px; border: 1.5px solid #ec008c; background: #fff9fb; border-radius: 10px;">
    <h3 style="font-size: 11.5pt; color: #ec008c; margin-bottom: 0; text-transform: uppercase; font-weight: 900;">FORMATO DE AUTORIZACIÓN PARA TRATAMIENTO DE DATOS BIOMÉTRICOS CEPRE UNAMAD</h3>
</div>

<div class="content" style="font-size: 8.5pt; line-height: 1.35; margin-top: 10px;">
    <p style="font-weight: bold; color: #003366; margin-bottom: 4px; border-bottom: 1px solid #eee; padding-bottom: 2px;">I. DATOS DEL RESPONSABLE DEL TRATAMIENTO</p>
    <ul style="list-style: disc; padding-left: 20px; margin-bottom: 12px;">
        <li><strong>Entidad:</strong> Centro Preuniversitario de la Universidad Nacional Amazónica de Madre de Dios (CEPRE-UNAMAD).</li>
        <li><strong>Base Legal:</strong> Ley N° 29733 (Ley de Protección de Datos Personales) y su Reglamento.</li>
    </ul>

    <p style="font-weight: bold; color: #003366; margin-bottom: 6px; border-bottom: 1px solid #eee; padding-bottom: 2px;">II. DATOS DEL PADRE, MADRE O TUTOR LEGAL (Para menores de 18 años)</p>
    <div style="margin-bottom: 12px; padding-left: 10px;">
        <div style="margin-bottom: 5px;">• <strong>Nombres y Apellidos:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 450px;"><strong>{{ strtoupper($apoderado_nombre) }}</strong></span></div>
        <div style="margin-bottom: 5px;">• <strong>DNI/CE:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 150px;"><strong>{{ $apoderado_dni }}</strong></span></div>
        <div style="margin-bottom: 5px;">• <strong>Domicilio:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 450px;"><strong>{{ strtoupper($apoderado_direccion) }}</strong></span></div>
        <div style="margin-bottom: 5px;">• <strong>Teléfono de contacto:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 200px;"><strong>{{ $apoderado_celular }}</strong></span></div>
    </div>

    <p style="font-weight: bold; color: #003366; margin-bottom: 6px; border-bottom: 1px solid #eee; padding-bottom: 2px;">III. DATOS DEL ESTUDIANTE (Titular del dato)</p>
    <div style="margin-bottom: 12px; padding-left: 10px;">
        <div style="margin-bottom: 5px;">• <strong>Nombres y Apellidos:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 450px;"><strong>{{ strtoupper($estudiante_nombre) }}</strong></span></div>
        <div style="margin-bottom: 5px;">• <strong>DNI:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 150px;"><strong>{{ $estudiante_dni }}</strong></span></div>
        <div style="margin-bottom: 5px;">
            • <strong>Ciclo Académico:</strong> 
            <span style="margin-left: 5px; {!! $programa_id == 2 ? 'background: #fff4f9; padding: 2px 5px; border-radius: 4px;' : '' !!}">
                <span class="check-box" {!! $programa_id == 2 ? 'style="background: #ec008c; color: white; border-color: #ec008c;"' : '' !!}>{{ $programa_id == 2 ? 'X' : '' }}</span> 
                <strong {!! $programa_id == 2 ? 'style="color: #ec008c;"' : 'style="color: #777;"' !!}>Reforzamiento para Secundaria.</strong>
            </span>
        </div>
        <div style="margin-top: 5px;">
            @if($programa_id == 1)
                • <strong>Carrera:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 170px;"><strong>{{ strtoupper($carrera_nombre ?? '__________') }}</strong></span>
            @else
                • <strong>Grado:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 170px;"><strong>{{ strtoupper($estudiante_grado ?? '__________') }}</strong></span>
            @endif
            &nbsp;&nbsp; <strong>Turno:</strong> <span style="border-bottom: 1px solid #666; display: inline-block; min-width: 120px;"><strong>{{ strtoupper($turno_nombre ?? '__________') }}</strong></span>
        </div>
    </div>

    <p style="text-align: justify; margin-bottom: 6px;">
        <strong>IV. CLÁUSULA DE CONSENTIMIENTO INFORMADO</strong> Mediante la firma del presente documento, yo, el padre/madre/tutor legal identificado en la sección II, <strong>OTORGO MI CONSENTIMIENTO EXPRESO</strong> al CEPRE-UNAMAD para que realice la captura, almacenamiento y procesamiento de la <strong>huella dactilar</strong> de mi menor hijo(a).<br>
        Este tratamiento de datos sensibles tendrá las siguientes finalidades:
    </p>

    <ol style="margin-bottom: 8px; padding-left: 40px; font-weight: bold; color: #444;">
        <li>Control de Asistencia: <span style="font-weight: normal;">Registro de ingreso y salida diarios.</span></li>
        <li>Identificación: <span style="font-weight: normal;">Evitar suplantaciones en evaluaciones.</span></li>
        <li>Seguridad: <span style="font-weight: normal;">Control de acceso a instalaciones.</span></li>
    </ol>

    <p style="text-align: justify; margin-bottom: 12px; background: #f9fbff; padding: 8px; border-radius: 6px; border-left: 4px solid #00aeef;">
        <strong>V. SEGURIDAD Y DERECHOS ARCO</strong> El CEPRE-UNAMAD se compromete a adoptar las medidas técnicas de seguridad para evitar la alteración, pérdida o acceso no autorizado de la información biométrica. Se informa que los datos serán eliminados al finalizar el ciclo académico respectivo. Asimismo, podrá ejercer sus derechos de <strong>Acceso, Rectificación, Cancelación y Oposición (ARCO)</strong> mediante solicitud escrita presentada en la oficina de secretaría del CEPRE.
    </p>

    <div style="text-align: right; font-weight: bold; color: #003366; margin-bottom: 10px;">
        Puerto Maldonado, <strong>{{ date('d') }}</strong> de <strong>{{ $mes_actual }}</strong> del <strong>2026</strong>.
    </div>

    <div class="signature-container" style="margin-top: 35px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 38%; vertical-align: bottom; text-align: left; padding: 0 5px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 8px;"></div>
                    <div class="signature-text" style="font-size: 8.5pt; font-weight: bold; color: #003366;">FIRMA DEL PADRE/MADRE / TUTOR</div>
                    <div style="font-size: 8pt; margin-top: 3px;">Nombre: <strong>{{ strtoupper($apoderado_nombre) }}</strong></div>
                    <div style="font-size: 8pt;">DNI: <strong>{{ $apoderado_dni }}</strong></div>
                </td>
                <td style="width: 24%; text-align: center; padding: 0 5px;">
                    <div class="fingerprint-box" style="margin: 0 auto; width: 68px; height: 88px; border: 1px solid #ccc;">
                        <div class="fingerprint-text" style="font-size: 7pt; margin-top: 35px; color: #999;">Huella Digital</div>
                    </div>
                </td>
                <td style="width: 38%; vertical-align: bottom; text-align: center; padding: 0 5px;">
                    <div style="border-top: 1.5px solid #333; width: 100%; margin-bottom: 10px;"></div>
                    <div class="signature-text" style="font-size: 10pt; font-weight: bold; color: #ec008c;">V° B°</div>
                    <div style="font-size: 9.5pt; font-weight: bold; color: #003366;">COORDINACIÓN CEPRE</div>
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
