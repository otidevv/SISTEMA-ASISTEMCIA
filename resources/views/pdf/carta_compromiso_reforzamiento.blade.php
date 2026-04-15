<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pack de Inscripción - Reforzamiento</title>
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
        
        .header { position: relative; width: 100%; border-bottom: 3px solid #00aeef; padding-bottom: 15px; margin-bottom: 20px; text-align: center; }
        .header-logo { position: absolute; left: 0; top: 0; width: 85px; }
        .header-logo-right { position: absolute; right: 0; top: 0; width: 90px; }
        .header-text h1 { font-size: 14pt; margin: 0; color: #003366; text-transform: uppercase; font-family: 'Arial Black', 'Arial Bold', sans-serif; }
        .header-text h2 { font-size: 12pt; margin: 5px 0; color: #00aeef; font-weight: bold; }
        
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
        .info-table td { padding: 8px 10px; border-bottom: 1px solid #f0f0f0; }
        .info-table td.label { font-weight: bold; width: 35%; color: #555; background: #fafafa; font-size: 10pt; }
        .info-table td.value { font-weight: bold; font-size: 11pt; color: #000; text-transform: uppercase; }

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
        
        strong { color: #000; }
        ol li { margin-bottom: 10px; padding-left: 5px; }
    </style>
</head>
<body>

<div class="footer-doc">
    “UNAMAD: Parque científico Tecnológico sostenible con Investigación e Innovación”<br>
    Av. Dos de Mayo Nº 960 – Puerto Maldonado – CEL: 993111037 – 993110327
</div>

<div class="watermark">INSCRIPCIÓN REFORZAMIENTO</div>

<!-- PÁGINA 1: FICHA DEL APODERADO -->
<div class="header">
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>Programa de Reforzamiento Escolar {{ date('Y') }}</p>
    </div>
</div>

<div class="title-box">
    <h3>FICHA DEL APODERADO O TUTOR</h3>
    <p>PROGRAMA DE REFORZAMIENTO PARA NIVEL SECUNDARIA</p>
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
        <p><span class="check-box"></span> Padre/Madre</p>
        <p><span class="check-box"></span> Hermano/Hermana</p>
        <p><span class="check-box"></span> Familiar (Abuelo, tío, primo)</p>
        <p><span class="check-box"></span> No tiene (Trabaja o se solventa sus estudios)</p>
    </div>

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
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>Programa de Reforzamiento Escolar {{ date('Y') }}</p>
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
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>Programa de Reforzamiento Escolar {{ date('Y') }}</p>
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
<div class="header" style="border-bottom: 2px solid #003366; margin-bottom: 10px;">
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text" style="width: 70%; margin: 0 auto; text-align: center;">
        <h1 style="font-size: 14pt; color: #003366; margin-bottom: 2px;">Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2 style="font-size: 11pt; color: #00aeef; margin-bottom: 2px;">“Centro Pre Universitario”</h2>
        <p style="font-size: 8pt; color: #003366; font-style: italic; margin-top: 0;">“Madre de Dios, Capital de la Biodiversidad del Perú”</p>
    </div>
</div>

<div class="title-box" style="margin-top: 5px; padding: 8px;">
    <h3 style="font-size: 11pt; margin-bottom: 0;">AUTORIZACIÓN PARA EL RETIRO DE ESTUDIANTES DURANTE EL CICLO ACADEMICO DEL CEPRE DE LA UNAMAD</h3>
</div>

<div class="content" style="font-size: 8.5pt; line-height: 1.25;">
    <p style="margin-bottom: 10px;">Yo, <strong>{{ strtoupper($apoderado_nombre) }}</strong> identificado con DNI N° <strong>{{ $apoderado_dni }}</strong> con domicilio en <strong>{{ strtoupper($apoderado_direccion) }}</strong> teléfono <strong>{{ $apoderado_celular }}</strong> manifiesto que soy el/la <strong>{{ $apoderado_parentesco ?? 'Tutor' }}</strong> (padre, madre o tutor) del estudiante menor de edad de nombre <strong>{{ strtoupper($estudiante_nombre) }}</strong> de <strong>{{ $estudiante_edad ?? '____' }}</strong> años de edad; de conformidad con lo dispuesto en los artículos 74° y 75° de la Ley N° 27337 – Código de los Niños y Adolescentes, lo siguiente:</p>
    
    <div style="margin: 10px 0;">
        <span style="font-weight: bold;">Marque su CICLO ACADÉMICO:</span>
        <span style="margin-left: 10px;"><span class="check-box"></span> Intensivo 2026-0</span>
        <span style="margin-left: 10px;"><span class="check-box"></span> Ordinario 2026-1</span><br>
        <span style="margin-left: 168px;"><span class="check-box"></span> Ordinario 2026-2</span>
        <span style="margin-left: 10px;"><span class="check-box" style="background: #eef;" align="center">X</span> <span style="color: #ec008c; font-weight: bold;">Reforzamiento para Secundaria</span></span>
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
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p>Tratamiento de Datos Personales y Biométricos</p>
    </div>
</div>

<div class="title-box">
    <h3>AUTORIZACIÓN TRATAMIENTO DATOS BIOMÉTRICOS</h3>
    <p>LEY N° 29733 - PROTECCIÓN DE DATOS PERSONALES</p>
</div>

<div class="content" style="font-size: 9.5pt;">
    <div class="section-header">I. DATOS DEL RESPONSABLE Y BASE LEGAL</div>
    <ul style="list-style: none; padding-left: 5px;">
        <li>• Entidad: Centro Preuniversitario de la Universidad Nacional Amazónica de Madre de Dios.</li>
        <li>• Base Legal: Ley N° 29733 and su Reglamento.</li>
    </ul>

    <div class="section-header">II. DATOS DEL PADRE, MADRE O TUTOR LEGAL</div>
    <table class="info-table">
        <tr>
            <td class="label">Nombres y Apellidos:</td>
            <td class="value">{{ $apoderado_nombre }}</td>
        </tr>
        <tr>
            <td class="label">DNI / CE:</td>
            <td class="value">{{ $apoderado_dni }}</td>
        </tr>
        <tr>
            <td class="label">Teléfono:</td>
            <td class="value">{{ $apoderado_celular }}</td>
        </tr>
    </table>

    <div class="section-header">III. DATOS DEL ESTUDIANTE</div>
    <table class="info-table">
        <tr>
            <td class="label">Apellidos y Nombres:</td>
            <td class="value">{{ strtoupper($estudiante_nombre) }}</td>
        </tr>
        <tr>
            <td class="label">DNI:</td>
            <td class="value">{{ $estudiante_dni }}</td>
        </tr>
        <tr>
            <td class="label">Ciclo Académico:</td>
            <td class="value">REFORZAMIENTO SECUNDARIA {{ date('Y') }}</td>
        </tr>
    </table>

    <div class="section-header">IV. CONSENTIMIENTO INFORMADO</div>
    <p>Otorgo mi <strong>CONSENTIMIENTO EXPRESO</strong> al CEPRE-UNAMAD para la captura and procesamiento de la huella dactilar de mi menor hijo(a) con fines de control de asistencia, identificación and seguridad durante el presente ciclo académico.</p>

    <div class="section-header">V. DERECHO DE INFORMACIÓN</div>
    <p>Se informa que los datos serán utilizados exclusivamente para los fines mencionados y que se garantiza el ejercicio de los derechos ARCO.</p>

    <div style="text-align: right; margin-top: 25px; font-weight: bold; color: #003366;">
        Puerto Maldonado, {{ date('d') }} de {{ $mes_actual }} del {{ date('Y') }}.
    </div>

    <div class="signature-container" style="margin-top: 60px;">
        <div class="signature-box" style="width: 300px; margin: 0 auto;">
            <div class="signature-line"></div>
            <div class="signature-text">Firma del Padre/Madre / Tutor</div>
            <div style="font-size: 10pt; margin-top: 5px;">DNI: <strong>{{ $apoderado_dni }}</strong></div>
        </div>
    </div>
</div>

</body>
</html>
