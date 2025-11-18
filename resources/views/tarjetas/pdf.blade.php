<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarjetas Pre Universitario - UNAMAD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .tarjeta {
            width: 8.5cm;
            height: 5.5cm;
            border: 1px solid #000;
            margin: 10px;
            padding: 10px;
            display: inline-block;
            background-color: #f8f9fa;
            position: relative;
            font-family: Arial, sans-serif;
            font-size: 12px;
            page-break-inside: avoid;
        }

        .tarjeta-p { background-color: #007bff; color: white; }
        .tarjeta-q { background-color: #28a745; color: white; }
        .tarjeta-r { background-color: #ffc107; color: black; }

        .tarjetas-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .center-text {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .center-text-2 {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .flex-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .flex-row-2 {
            display: flex;
            justify-content: space-between;
        }

        .foto-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .foto-estudiante {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .no-foto {
            width: 60px;
            height: 60px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #6c757d;
            border: 2px solid #fff;
        }
    </style>
</head>
<body>
    <div class="tarjetas-container">
        @foreach($tarjetas as $tarjeta)
        <div class="tarjeta {{ 'tarjeta-' . strtolower($tarjeta['tema']) }}">
            <div class="center-text">
                UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS
            </div>
            <div class="center-text-2">
                CENTRO PRE UNIVERSITARIO
            </div>

            <div class="foto-container">
                @if($tarjeta['foto'])
                    <img src="{{ $tarjeta['foto'] }}" alt="Foto del estudiante" class="foto-estudiante">
                @else
                    <div class="no-foto">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
            </div>

            <div class="flex-row">
                <span><strong>GRUPO:</strong> {{ $tarjeta['grupo'] }}</span>
                <span><strong>TEMA:</strong> {{ $tarjeta['tema'] }}</span>
                <span><strong>CÓDIGO:</strong> {{ $tarjeta['codigo'] }}</span>
            </div>
            <div class="flex-row-2">
                <span><strong>AULA:</strong> {{ $tarjeta['aula'] }}</span>
                <span><strong>CARRERA:</strong> {{ $tarjeta['carrera'] }}</span>
                <span><strong>NOMBRES:</strong> {{ $tarjeta['nombres'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
</body>
</html>
