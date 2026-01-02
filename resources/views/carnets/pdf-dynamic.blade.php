<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carnets</title>
    <style>
        @page {
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        
        .carnet {
            position: relative;
            width: {{ $template->ancho_mm }}mm;
            height: {{ $template->alto_mm }}mm;
            page-break-after: always;
            overflow: hidden;
        }
        
        .carnet:last-child {
            page-break-after: auto;
        }
        
        .fondo {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            object-fit: contain;
        }
        
        .campo {
            position: absolute;
            z-index: 1;
        }
        
        .campo-foto img,
        .campo-qr_code img {
            display: block;
        }
    </style>
</head>
<body>
    @foreach($carnets as $carnet)
        <div class="carnet">
            {{-- Fondo --}}
            @if($carnet['fondo'])
                <img src="{{ $carnet['fondo'] }}" class="fondo" alt="Fondo">
            @endif

            {{-- Campos dinámicos según configuración de la plantilla --}}
            @foreach($template->campos_config as $campo => $config)
                @if($config['visible'] ?? true)
                    @if($campo === 'foto' && $carnet['foto'])
                        {{-- Foto del estudiante --}}
                        <div class="campo campo-{{ $campo }}" style="
                            left: {{ $config['left'] }};
                            top: {{ $config['top'] }};
                            width: {{ $config['width'] ?? '24mm' }};
                            height: {{ $config['height'] ?? '26mm' }};
                        ">
                            <img src="{{ $carnet['foto'] }}" 
                                 style="width: 100%; height: 100%; object-fit: cover;" 
                                 alt="Foto">
                        </div>

                    @elseif($campo === 'qr_code' && $carnet['qr_code'])
                        {{-- Código QR --}}
                        <div class="campo campo-{{ $campo }}" style="
                            left: {{ $config['left'] }};
                            top: {{ $config['top'] }};
                            width: {{ $config['width'] ?? '10mm' }};
                            height: {{ $config['height'] ?? '10mm' }};
                        ">
                            <img src="{{ $carnet['qr_code'] }}" 
                                 style="width: 100%; height: 100%;" 
                                 alt="QR">
                        </div>

                    @elseif($campo === 'codigo_postulante')
                        {{-- Código de Postulante --}}
                        <div class="campo campo-{{ $campo }}" style="
                            left: {{ $config['left'] }};
                            top: {{ $config['top'] }};
                            font-size: {{ $config['fontSize'] ?? '11pt' }};
                            font-weight: {{ $config['fontWeight'] ?? 'bold' }};
                            color: {{ $config['color'] ?? 'white' }};
                        ">
                            {{ $carnet['codigo_postulante'] }}
                        </div>

                    @elseif($campo === 'nombre_completo')
                        {{-- Nombre Completo --}}
                        <div class="campo campo-{{ $campo }}" style="
                            left: {{ $config['left'] }};
                            top: {{ $config['top'] }};
                            font-size: {{ $config['fontSize'] ?? '7pt' }};
                            font-weight: {{ $config['fontWeight'] ?? '100' }};
                            color: {{ $config['color'] ?? 'white' }};
                            max-width: {{ $config['maxWidth'] ?? '40mm' }};
                        ">
                            {{ $carnet['nombre_completo'] }}
                        </div>

                    @elseif($campo === 'dni')
                        {{-- DNI --}}
                        <div class="campo campo-{{ $campo }}" style="
                            left: {{ $config['left'] }};
                            top: {{ $config['top'] }};
                            font-size: {{ $config['fontSize'] ?? '8pt' }};
                            font-weight: {{ $config['fontWeight'] ?? 'normal' }};
                            color: {{ $config['color'] ?? '#003d7a' }};
                        ">
                            {{ $carnet['dni'] }}
                        </div>

                    @elseif($campo === 'grupo')
                        {{-- Grupo --}}
                        <div class="campo campo-{{ $campo }}" style="
                            left: {{ $config['left'] }};
                            top: {{ $config['top'] }};
                            font-size: {{ $config['fontSize'] ?? '8pt' }};
                            font-weight: {{ $config['fontWeight'] ?? 'normal' }};
                            color: {{ $config['color'] ?? '#003d7a' }};
                        ">
                            {{ $carnet['grupo'] }}
                        </div>

                    @elseif($campo === 'modalidad')
                        {{-- Modalidad --}}
                        <div class="campo campo-{{ $campo }}" style="
                            left: {{ $config['left'] }};
                            top: {{ $config['top'] }};
                            font-size: {{ $config['fontSize'] ?? '7pt' }};
                            font-weight: {{ $config['fontWeight'] ?? 'normal' }};
                            color: {{ $config['color'] ?? '#003d7a' }};
                        ">
                            {{ $carnet['modalidad'] }}
                        </div>

                    @elseif($campo === 'carrera')
                        {{-- Carrera --}}
                        <div class="campo campo-{{ $campo }}" style="
                            left: {{ $config['left'] }};
                            top: {{ $config['top'] }};
                            font-size: {{ $config['fontSize'] ?? '7pt' }};
                            font-weight: {{ $config['fontWeight'] ?? 'bold' }};
                            color: {{ $config['color'] ?? '#003d7a' }};
                            max-width: {{ $config['maxWidth'] ?? '40mm' }};
                        ">
                            {{ $carnet['carrera'] }}
                        </div>
                    @endif
                @endif
            @endforeach
        </div>
    @endforeach
</body>
</html>
