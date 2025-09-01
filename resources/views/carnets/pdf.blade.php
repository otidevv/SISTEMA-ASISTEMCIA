<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carnets - CEPRE UNAMAD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: 53.98mm 85.6mm; /* Tamaño CR80 vertical */
            margin: 0;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .carnet-container {
            width: 53.98mm;
            height: 85.6mm;
            position: relative;
            page-break-after: always;
            overflow: hidden;
            background: white;
        }
        
        .carnet-container:last-child {
            page-break-after: auto;
        }
        
        .carnet-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .carnet-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            padding: 5mm;
        }
        
        .foto-container {
            position: absolute;
            top: 50%;
            left: 10mm;
            transform: translateY(-50%);
            width: 25mm;
            height: 30mm;
            border: 2px solid #003d7a;
            background: white;
            overflow: hidden;
        }
        
        .foto-estudiante {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .info-container {
            position: absolute;
            bottom: 8mm;
            left: 40mm;
            color: #003d7a;
            font-weight: bold;
        }
        
        .info-row {
            margin-bottom: 2mm;
            font-size: 9pt;
        }
        
        .info-label {
            display: inline-block;
            min-width: 20mm;
        }
        
        .info-value {
            display: inline-block;
            font-weight: normal;
        }
        
        .qr-container {
            position: absolute;
            top: 23mm;
            right: 11mm;
            width: 10mm;
            height: 10mm;
        }
        
        .qr-code {
            width: 100%;
            height: 100%;
        }
        
        .ciclo-badge {
            position: absolute;
            top: 28mm;
            left: 32mm;
            background: rgba(255, 255, 255, 0.9);
            padding: 2mm 4mm;
            border-radius: 3mm;
            font-size: 10pt;
            font-weight: bold;
            color: #003d7a;
        }
        
        .codigo-carnet {
            position: absolute;
            bottom: 2mm;
            right: 5mm;
            font-size: 7pt;
            color: #666;
        }
    </style>
</head>
<body>
    @foreach($carnets as $carnet)
    <div class="carnet-container">
        <!-- Fondo del carnet -->
        @if($carnet['fondo'])
        <img src="{{ $carnet['fondo'] }}" class="carnet-background" alt="Fondo">
        @endif
        
        <!-- Contenido del carnet - Solo elementos dinámicos que se sobreponen al fondo -->
        <div class="carnet-content">
            
            @if($carnet['foto'])
            <!-- Foto del estudiante - ancho reducido un poco -->
            <div style="position: absolute; left: 50%; transform: translateX(-70%); top: 13.5mm; width: 24mm; height: 26mm; overflow: hidden;">
                <img src="{{ $carnet['foto'] }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Foto">
            </div>
            @endif

            <!-- QR Code -->
            @if($carnet['qr_code'])
            <div class="qr-container">
                <img src="{{ $carnet['qr_code'] }}" class="qr-code" alt="QR Code">
            </div>
            @endif
            
            <!-- Ciclo - más a la izquierda -->
            <div style="position: absolute; left: 50%; transform: translateX(-65%); top: 33mm; width: 35mm; text-align: center; font-size: 7pt; font-weight: bold; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.9);">
                CICLO ORDINARIO<br>{{ str_replace('CICLO ORDINARIO ', '', strtoupper($carnet['ciclo'])) }}
            </div>
            
            <!-- Código del postulante - aún más a la izquierda y bajado -->
            <div style="position: absolute; left: 50%; transform: translateX(-70%); top: 39.5mm; text-align: center; font-size: 11pt; font-weight: bold; color: white; letter-spacing: 1mm;">
                {{ $carnet['codigo_postulante'] }}
            </div>
            
            <!-- Nombre del estudiante - tamaño reducido -->
            <div style="position: absolute; left: 46%; transform: translateX(-55%); top: 44.9mm; color: white; text-align: center; font-weight: 100; font-size: 9pt; letter-spacing: 0.2mm;">
                {{ strtoupper($carnet['nombre_completo']) }}
            </div>
            
            <!-- Datos del estudiante - ajustados según indicaciones -->
            <!-- DNI - bajar un poquito -->
            <div style="position: absolute; left: 17mm; top: 55mm; color: #003d7a; font-size: 8pt;">
                {{ $carnet['dni'] }}
            </div>
            
            <!-- GRUPO - bajar un poquito -->
            <div style="position: absolute; left: 22mm; top: 60mm; color: #003d7a; font-size: 8pt;">
                {{ $carnet['grupo'] }}
            </div>
            
            <!-- MODALIDAD - bajar un poquito -->
            <div style="position: absolute; left: 30mm; top: 64.5mm; color: #003d7a; font-size: 8pt;">
                POSTULANTE
            </div>
            
            <!-- CARRERA PROFESIONAL - más a la izquierda, tamaño reducido y letras gruesas -->
            <div style="position: absolute; left: 50%; transform: translateX(-60%); top: 77mm; color: #003d7a; font-size: 7pt; font-weight: bold; text-align: center;">
                {{ strtoupper($carnet['carrera']) }}
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>