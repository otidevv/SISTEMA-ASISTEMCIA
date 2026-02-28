<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { width: 100%; max-width: 600px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; padding: 30px 20px; text-align: center; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #e1e8ed;
        }
        .header {
            background: linear-gradient(135deg, #2b5a6f 0%, #1e4251 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header img {
            max-height: 80px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
            margin-top: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .status-entrada { background-color: #8cc63f; color: white; }
        .status-salida { background-color: #00aeef; color: white; }
        
        .content {
            padding: 30px;
        }
        .welcome {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2b5a6f;
            font-weight: 600;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: #fdfdfd;
            border-radius: 8px;
            overflow: hidden;
        }
        .details-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .label {
            font-weight: 600;
            color: #555;
            width: 40%;
            font-size: 14px;
        }
        .value {
            color: #222;
            font-weight: 500;
            font-size: 14px;
        }
        .alert-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        .alert-warning {
            background-color: #fff9e6;
            border-left: 4px solid #f39c12;
            color: #856404;
        }
        .alert-info {
            background-color: #eef9ff;
            border-left: 4px solid #00aeef;
            color: #004085;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
        }
        .btn-action {
            display: inline-block;
            background-color: #ec008c;
            color: white !important;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(236, 0, 140, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($data['logo_path']))
                <img src="{{ $message->embed($data['logo_path']) }}" alt="Logo CEPRE UNAMAD">
            @endif
            <h1>CEPRE UNAMAD</h1>
            <div class="status-badge status-{{ strtolower($data['estado']) }}">
                Marcación de {{ $data['estado'] }}
            </div>
        </div>
        
        <div class="content">
            <div class="welcome">Hola, {{ $data['nombre_docente'] }}</div>
            <p>Se ha registrado una nueva marcación en el sistema biométrico vinculada a tu cuenta.</p>
            
            <table class="details-table">
                <tr>
                    <td class="label">Fecha:</td>
                    <td class="value">{{ $data['fecha'] }}</td>
                </tr>
                <tr>
                    <td class="label">Hora de marcación:</td>
                    <td class="value">{{ $data['hora'] }}</td>
                </tr>
                @if(isset($data['curso']))
                <tr>
                    <td class="label">Curso:</td>
                    <td class="value">{{ $data['curso'] }}</td>
                </tr>
                <tr>
                    <td class="label">Horario:</td>
                    <td class="value">{{ $data['horario_programado'] }}</td>
                </tr>
                @endif
                @if(isset($data['aula']))
                <tr>
                    <td class="label">Aula:</td>
                    <td class="value">{{ $data['aula'] }}</td>
                </tr>
                @endif
                @if(isset($data['duracion_neta']))
                <tr>
                    <td class="label">Duración Efectiva:</td>
                    <td class="value"><strong>{{ $data['duracion_neta'] }}</strong> ({{ $data['horas_dictadas'] }} hrs)</td>
                </tr>
                @endif
            </table>

            @if(isset($data['minutos_tardanza']))
            <div class="alert-box alert-warning">
                <strong>⚠️ Tardanza detectada:</strong>&nbsp; Se han registrado {{ $data['minutos_tardanza'] }} minutos de retraso respecto al inicio programado.
            </div>
            @endif

            @if($data['estado'] === 'salida')
            <div class="alert-box alert-info">
                <div>
                    <strong>📝 Recordatorio Importante:</strong><br>
                    Por favor, no olvides registrar el <strong>Tema Desarrollado</strong> de tu sesión en tu panel docente para completar el proceso.
                </div>
            </div>
            <div style="text-align: center;">
                <a href="https://portalcepre.unamad.edu.pe/" class="btn-action">Registrar Tema Aquí</a>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Centro Pre-Universitario UNAMAD - Sistema de Control de Asistencia</p>
            <p>Este es un correo automático, por favor no lo respondas.</p>
        </div>
    </div>
</body>
</html>
