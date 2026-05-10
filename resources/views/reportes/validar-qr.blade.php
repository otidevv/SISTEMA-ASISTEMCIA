<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Horario - CEPRE-UNAMAD</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #003366;
            --secondary: #cc0066;
            --success: #008f39;
            --bg: #f4f7f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
            border-top: 8px solid var(--success);
        }

        .logo {
            width: 100px;
            margin-bottom: 20px;
        }

        .status-icon {
            font-size: 60px;
            color: var(--success);
            margin-bottom: 10px;
        }

        h1 {
            color: var(--primary);
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .subtitle {
            color: var(--secondary);
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            text-align: left;
            margin-bottom: 30px;
            border: 1px solid #eee;
        }

        .info-row {
            margin-bottom: 15px;
        }

        .info-row:last-child { margin-bottom: 0; }

        .label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            font-weight: 900;
            display: block;
        }

        .value {
            font-size: 16px;
            color: var(--primary);
            font-weight: 700;
        }

        .certified-badge {
            background: var(--success);
            color: white;
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .footer {
            font-size: 11px;
            color: #aaa;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="{{ asset('assets/images/logo unamad constancia.png') }}" class="logo" alt="UNAMAD">
        
        <div class="status-icon">✓</div>
        <div class="certified-badge">Documento Auténtico</div>
        
        <h1>Horario Validado</h1>
        <div class="subtitle">CEPRE-UNAMAD | PORTAL OFICIAL</div>

        <div class="info-box">
            <div class="info-row">
                <span class="label">Asignado a:</span>
                <span class="value">{{ $nombre }}</span>
            </div>
            <div class="info-row">
                <span class="label">Ciclo Académico:</span>
                <span class="value">{{ $ciclo->nombre }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tipo de Reporte:</span>
                <span class="value">{{ strtoupper($tipo) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Fecha de Verificación:</span>
                <span class="value">{{ $fecha_validacion }}</span>
            </div>
        </div>

        <div class="footer">
            Generado por el sistema Portal cepre unamad oficial<br>
            &copy; {{ date('Y') }} Universidad Nacional Amazónica de Madre de Dios
        </div>
    </div>
</body>
</html>
