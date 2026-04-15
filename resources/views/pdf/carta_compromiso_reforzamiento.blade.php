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
        }
        .mt-5 { margin-top: 50px; }
        .mt-3 { margin-top: 30px; }
        .mb-3 { margin-bottom: 30px; }
        
        .footer-signatures { margin-top: 70px; width: 100%; padding-bottom: 40px;}
        .signature-box { text-align: center; width: 45%; display: inline-block; vertical-align: top; }
        .signature-line { border-top: 1px solid #333; width: 80%; margin: 60px auto 5px; }
        .fingerprint-box { border: 1px solid #ccc; width: 70px; height: 90px; margin: 10px auto; }
        
        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #777;
            padding-top: 10px;
            border-top: 1px dotted #ccc;
        }
    </style>
</head>
<body>

<!-- PÁGINA 1: FICHA DEL APODERADO O TUTOR -->
<div class="header">
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="UNAMAD">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="CEPRE">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p style="margin:2px 0; font-weight:bold; font-size: 10pt; color: #333;">Registros Generales - Nivel Secundaria {{ date('Y') }}</p>
    </div>
</div>

<div class="title">FICHA DEL APODERADO O TUTOR</div>

<div class="content">
    <p class="form-label">DATOS DEL TUTOR O APODERADO:</p>
    
    <div class="mt-3">
        <div class="form-row">
            <span class="form-label">1. Apellidos y Nombres:</span>
            <span class="form-value" style="width: 350px;">{{ $apoderado_nombre }}</span>
        </div>
        <div class="form-row">
            <span class="form-label">2. Número de documento de identidad:</span>
            <span class="form-value" style="width: 250px;">{{ $apoderado_dni }}</span>
        </div>
        <div class="form-row">
            <span class="form-label">3. Número de Celular:</span>
            <span class="form-value" style="width: 250px;">{{ $apoderado_celular }}</span>
        </div>
        <div class="form-row">
            <span class="form-label">4. Dirección:</span>
            <span class="form-value" style="width: 400px;">{{ $apoderado_direccion }}</span>
        </div>
    </div>
    
    <div class="mt-3">
        <p class="form-label">5. Relación del Tutor con el estudiante</p>
        <p>a. Padre/Madre</p>
        <p>b. Hermano/Hermana</p>
        <p>c. Familiar (Abuelo, tío, primo)</p>
        <p>d. No tiene (Trabaja, se solventa el mismo sus estudios)</p>
    </div>

    <div class="text-right mt-5">
        Puerto Maldonado, {{ date('d') }} de {{ $mes_actual }} del {{ date('Y') }}
    </div>

    <div class="signature-box mt-5">
        <div class="signature-line"></div>
        <strong>FIRMA DEL PADRE O <br> APODERADO</strong><br>
        DNI: _______________________
    </div>
</div>

<div class="page-break"></div>

<!-- PÁGINA 2: CARTA COMPROMISO -->
<div class="header">
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="UNAMAD">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="CEPRE">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p style="margin:2px 0; font-weight:bold; font-size: 10pt; color: #333;">Registros Generales - Nivel Secundaria {{ date('Y') }}</p>
    </div>
</div>

<div class="content">
    <div class="text-right mb-3">
        Puerto Maldonado, {{ date('d') }} de {{ $mes_actual }} del {{ date('Y') }}
    </div>

    <div class="title">CARTA COMPROMISO</div>

    <p>Yo <strong>{{ strtoupper($estudiante_nombre) }}</strong> estudiante de CEPRE me comprometo a:</p>

    <ol style="margin-bottom: 40px;">
        <li style="margin-bottom: 10px;"><strong>Cumplimiento del Reglamento:</strong> Leer atentamente y cumplir con todas las normas establecidas en el reglamento interno.</li>
        <li style="margin-bottom: 10px;"><strong>Asistencia:</strong> Asistir puntualmente y de manera obligatoria a todas las clases.</li>
        <li style="margin-bottom: 10px;"><strong>Cuidado de las Instalaciones:</strong> Se prohíbe pintar o rayar las paredes de las instalaciones y el mobiliario del centro.</li>
        <li style="margin-bottom: 10px;"><strong>Identificación:</strong> Portar en todo momento el CARNET de CEPRE como identificación oficial.</li>
        <li style="margin-bottom: 10px;"><strong>Uso del Celular:</strong> Se prohíbe el uso de teléfonos celulares durante las horas de clases.</li>
        <li style="margin-bottom: 10px;"><strong>Aceptación de Sanciones:</strong> El incumplimiento de estas normas podrá acarrear sanciones disciplinarias. La firma al final de este documento implica la aceptación de este reglamento y sus consecuencias.</li>
    </ol>

    <div class="signature-box" style="margin-top: 100px;">
        <div class="signature-line"></div>
        Firma del Alumno<br>
        N° DNI: <strong>{{ $estudiante_dni }}</strong>
        <div class="fingerprint">Huella Digital</div>
    </div>

    <div style="margin-top: 80px; font-size: 12px; background: #f9f9f9; padding: 15px; border: 1px solid #ccc;">
        <strong>REGLAMENTO CENTRO PREUNIVERSITARIO DE UNIVERSIDAD NACIONAL AMAZONICA DE MADRE DE DIOS</strong><br>
        <strong>TÍTULO IV</strong><br>
        <strong>Artículo. 40°</strong> La asistencia a clases es obligatoria y se registra diariamente. El registro de asistencia es responsabilidad del personal asistencial del CEPRE con colaboración del docente de aula.<br><br>
        <strong>Artículo. 41º</strong> El estudiante que acumula el 20% o más de inasistencias en el periodo previo a cada examen sumativo es AMONESTADO, medida que es comunicada al padre de familia o apoderado para conocimiento. El 30% de inasistencias INHABILITA al estudiante para rendir el examen sumativo respectivo.<br><br>
        <em>NOTA: EL INTEGRO DEL TEXTO DEL REGLAMENTO DE CEPRE SE ENCUENTRA EN NUESTRA PAGINA WEB DEL CEPRE UNAMAD.</em>
    </div>
</div>

<div class="page-break"></div>

<!-- PÁGINA 3: DECLARACIÓN JURADA -->
<div class="header">
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="UNAMAD">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="CEPRE">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p style="margin:2px 0; font-weight:bold; font-size: 10pt; color: #333;">Registros Generales - Nivel Secundaria {{ date('Y') }}</p>
    </div>
</div>

<div class="title" style="font-size: 14px;">DECLARACIÓN JURADA DE CONOCER Y ACEPTAR EL REGLAMENTO INTERNO DEL CEPRE</div>

<div class="content text-justify">
    <p>Yo <strong>{{ strtoupper($apoderado_nombre) }}</strong> identificado con Documento Nacional de Identidad N.° <strong>{{ $apoderado_dni }}</strong> con domicilio actual sito en <strong>{{ strtoupper($apoderado_direccion) }}</strong> del distrito de Tambopata, provincia de Tambopata, departamento de Madre de Dios, señalando número de celular <strong>{{ $apoderado_celular }}</strong>, padre de familia de mi menor hijo(a) <strong>{{ strtoupper($estudiante_nombre) }}</strong>.</p>
    
    <div class="title mt-5" style="text-decoration:none; text-align: left; margin-bottom: 10px;">DECLARO BAJO JURAMENTO</div>
    
    <p>Que he tomado conocimiento y haber leído el texto íntegro de los Artículos 37, 40 y 41 del Reglamento Interno del Centro Pre-Universitario de la UNAMAD aprobado por Resolución de Consejo Universitario Nª 510-2017-UNAMADS-CU de fecha 14 de agosto del 2017, por la cual DECLARO CONOCER Y ACEPTAR el Reglamento Interno del CEPRE – UNAMAD.</p>
    
    <p>Formulo la presente declaración en virtud del artículo 38° de la Constitución Política del Perú, en concordancia con el principio de presunción de veracidad previsto en los artículos IV numeral 1.7 y 42° del TUO de la Ley 27444 – Ley del Procedimiento Administrativo General, sujetándome a las acciones administrativas y/o legales que correspondan de acuerdo a la legislación nacional vigente.</p>
    
    <p class="mt-3">En fe de lo cual firmo la presente a los {{ date('d') }} días del mes de {{ $mes_actual }} del año {{ date('Y') }}.</p>

    <div class="signature-box" style="margin-top: 100px;">
        <div class="signature-line"></div>
        Firma del Alumno o Apoderado<br>
        Nombre: _________________________<br>
        N° DNI: _________________________
        <div class="fingerprint">Huella Digital</div>
    </div>
</div>

<div class="page-break"></div>

<!-- PÁGINA 4: AUTORIZACIÓN PARA EL RETIRO -->
<div class="title mt-3" style="font-size: 14px; padding: 0 40px; text-decoration:none;">AUTORIZACIÓN PARA EL RETIRO DE ESTUDIANTES DURANTE EL CICLO ACADEMICO DEL CEPRE DE LA UNAMAD</div>

<div class="content text-justify mt-3">
    <p>Yo, <strong>{{ strtoupper($apoderado_nombre) }}</strong> identificado con DNI N° <strong>{{ $apoderado_dni }}</strong> con domicilio en <strong>{{ strtoupper($apoderado_direccion) }}</strong> teléfono <strong>{{ $apoderado_celular }}</strong> manifiesto que soy el/la (padre, madre o tutor) del estudiante menor de edad de nombre <strong>{{ strtoupper($estudiante_nombre) }}</strong>; de conformidad con lo dispuesto en los artículos 74° y 75° de la Ley N° 27337 – Código de los Niños y Adolescentes, lo siguiente:</p>
    
    <div style="margin: 15px 0;">
        <strong>Marque su CICLO ACADÉMICO:</strong><br>
        [ &nbsp; ] Intensivo 2026-0 &nbsp;&nbsp;&nbsp; [ &nbsp; ] Ordinario 2026-1 &nbsp;&nbsp;&nbsp; [ &nbsp; ] Ordinario 2026-2 &nbsp;&nbsp;&nbsp; [ <strong>X</strong> ] Reforzamiento para Secundaria
    </div>

    <p>Marque con una X una de las dos casillas la opción que desee:</p>
    
    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
        <p><strong>[ &nbsp; ] AUTORIZO</strong> que mi hijo(a) o representado(a) legal se retire solo(a) a su domicilio al finalizar el horario de clases del Centro Preuniversitario, sin que ningún adulto se responsabilice de acompañarlo(a).</p>
        <p style="font-size: 12px; color: #333;">Que tengo pleno conocimiento de que el Centro Preuniversitario de la UNAMAD cuenta con un sistema de control de asistencia mediante detector de huella digital, el cual registra el ingreso y la salida del estudiante de las instalaciones, y que el registro es notificado en tiempo real al padre, madre o representante legal a través de mensaje electrónico. En tal sentido, reconozco expresamente que dicho registro constituye constancia suficiente del momento en que el estudiante ingresa y se retira del recinto institucional, delimitando de manera objetiva la responsabilidad de la institución.</p>
        <p>Domicilio del estudiante: _________________________________________________.</p>
    </div>

    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
        <p><strong>[ &nbsp; ] NO AUTORIZO</strong> a que mi hijo (a) o representado legal se vaya solo a casa cuando finalice la jornada escolar. La entrega se efectuará UNICAMENTE al familiar o persona autorizada para recogerlo es: ___________________________ celular ___________, quien deberá identificarse con DNI vigente.</p>
        <p style="font-size: 12px; color: #333;">Declaro estar consciente de que el Centro Preuniversitario de la UNAMAD únicamente entregará al menor de edad a los padres o apoderado y/o persona autorizada a través del presente documento con el fin de salvaguardar su seguridad y en cumplimiento de los protocolos de entrega de menores, no asumiendo responsabilidad por cualquier situación posterior.</p>
    </div>

    <p>En tal sentido, EXONERO EXPRESAMENTE DE TODA RESPONSABILIDAD a la UNAMAD, a su Centro Preuniversitario, autoridades, docentes y personal administrativo, por cualquier hecho, daño, accidente o contingencia que pudiera ocurrir con posterioridad a la salida del menor de la institución.</p>
    
    <div class="text-right mb-3">
        Puerto Maldonado, {{ date('d') }} de {{ $mes_actual }} del {{ date('Y') }}.
    </div>

    <div style="margin-top: 40px; border-bottom: 1px solid #000; width: 300px;">Firma del padre, madre o tutor(a) legal:</div>
    <div style="margin-top: 15px; border-bottom: 1px solid #000; width: 300px;">Nombre completo: </div>
    <div style="margin-top: 15px; border-bottom: 1px solid #000; width: 300px;">Telf. Casa o celular: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Emergencias:</div>
</div>

<div class="page-break"></div>

<!-- PÁGINA 5: FORMATO DATOS BIOMÉTRICOS -->
<div class="header">
    <img class="header-logo" src="{{ public_path('assets/images/logo unamad constancia.png') }}" alt="UNAMAD">
    <img class="header-logo-right" src="{{ public_path('assets/images/logo cepre costancia.png') }}" alt="CEPRE">
    <div class="header-text">
        <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
        <h2>Centro Preuniversitario - CEPRE UNAMAD</h2>
        <p style="margin:2px 0; font-weight:bold; font-size: 10pt; color: #333;">Registros Generales - Nivel Secundaria {{ date('Y') }}</p>
    </div>
</div>

<div class="title" style="font-size: 14px; text-decoration:none;">FORMATO DE AUTORIZACIÓN PARA TRATAMIENTO DE DATOS BIOMÉTRICOS - CEPRE UNAMAD</div>

<div class="content text-justify mt-3">
    <strong>I. DATOS DEL RESPONSABLE DEL TRATAMIENTO</strong>
    <ul style="margin-top: 5px; margin-bottom: 15px;">
        <li>Entidad: Centro Preuniversitario de la Universidad Nacional Amazónica de Madre de Dios (CEPRE-UNAMAD).</li>
        <li>Base Legal: Ley N° 29733 (Ley de Protección de Datos Personales) y su Reglamento.</li>
    </ul>

    <strong>II. DATOS DEL PADRE, MADRE O TUTOR LEGAL (Para menores de 18 años)</strong>
    <ul style="list-style: none; padding-left: 0; margin-top: 5px; margin-bottom: 15px;">
        <li>• Nombres y Apellidos: <strong>{{ strtoupper($apoderado_nombre) }}</strong></li>
        <li>• DNI/CE: <strong>{{ $apoderado_dni }}</strong></li>
        <li>• Domicilio: <strong>{{ strtoupper($apoderado_direccion) }}</strong></li>
        <li>• Teléfono de contacto: <strong>{{ $apoderado_celular }}</strong></li>
    </ul>

    <strong>III. DATOS DEL ESTUDIANTE (Titular del dato)</strong>
    <ul style="list-style: none; padding-left: 0; margin-top: 5px; margin-bottom: 15px;">
        <li>• Nombres y Apellidos: <strong>{{ strtoupper($estudiante_nombre) }}</strong></li>
        <li>• DNI: <strong>{{ $estudiante_dni }}</strong></li>
        <li>• Ciclo Académico: [ &nbsp; ] Int. 2026-0 &nbsp;&nbsp; [ &nbsp; ] Ord. 2026-1 &nbsp;&nbsp; [ &nbsp; ] Ord. 2026-2 &nbsp;&nbsp; [ <strong>X</strong> ] Reforzamiento para Secundaria.</li>
        <li>• Grupo: __________ &nbsp;&nbsp; Turno: __________</li>
    </ul>

    <strong>IV. CLÁUSULA DE CONSENTIMIENTO INFORMADO</strong>
    <p style="margin-top: 5px;">Mediante la firma del presente documento, yo, el padre/madre/tutor legal identificado en la sección II, OTORGO MI CONSENTIMIENTO EXPRESO al CEPRE-UNAMAD para que realice la captura, almacenamiento y procesamiento de la huella dactilar de mi menor hijo(a).</p>
    <p>Este tratamiento de datos sensibles tendrá las siguientes finalidades:</p>
    <ol style="margin-top: 5px; margin-bottom: 15px;">
        <li>Control de Asistencia: Registro de ingreso y salida de las sesiones de clase y simulacros.</li>
        <li>Identificación: Verificación de identidad para evitar suplantaciones en los procesos de evaluación interna.</li>
        <li>Seguridad: Control de acceso a las instalaciones del Centro Preuniversitario.</li>
    </ol>

    <strong>V. SEGURIDAD Y DERECHOS ARCO</strong>
    <p style="margin-top: 5px;">El CEPRE-UNAMAD se compromete a adoptar las medidas técnicas de seguridad para evitar la alteración, pérdida o acceso no autorizado de la información biométrica. Se informa que los datos serán eliminados al finalizar el ciclo académico respectivo. Asimismo, podrá ejercer sus derechos de Acceso, Rectificación, Cancelación y Oposición (ARCO) mediante solicitud escrita presentada en la oficina de secretaría del CEPRE.</p>

    <div class="text-right mt-3">
        Puerto Maldonado, {{ date('d') }} de {{ $mes_actual }} del {{ date('Y') }}.
    </div>

    <div class="signature-box" style="margin-top: 60px;">
        <div class="signature-line"></div>
        Firma del Padre/Madre / Tutor<br>
        DNI: _______________________
    </div>
</div>

<!-- FOOTER GOLBAL (Para el diseño visual en DomPdf se puede poner fixed body footer pero aquí es al final) -->
<div class="footer">
    “UNAMAD: Parque científico Tecnológico sostenible con Investigación e Innovación”<br>
    Av. Dos de Mayo Nº 960 – Puerto Maldonado – CEL: 993111037 – 993110327
</div>

</body>
</html>
