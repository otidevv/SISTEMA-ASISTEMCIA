<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Constancia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .validation-card {
            max-width: 800px;
            margin: 50px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .valid-icon {
            color: #28a745;
            font-size: 4rem;
        }
        .invalid-icon {
            color: #dc3545;
            font-size: 4rem;
        }
        .certificate-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card validation-card">
            <div class="card-body text-center">
                @if($valida)
                    <div class="valid-icon mb-3">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h2 class="card-title text-success">Constancia Válida</h2>
                    <p class="card-text">Esta constancia ha sido verificada y es auténtica.</p>

                    <div class="certificate-details text-start">
                        <h5>Detalles de la Constancia:</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tipo:</strong> {{ ucfirst($tipo) }}</p>
                                <p><strong>Número:</strong> {{ $datos['numero_constancia'] }}</p>
                                <p><strong>Estudiante:</strong> {{ $datos['estudiante']['nombre'] }} {{ $datos['estudiante']['apellido_paterno'] }} {{ $datos['estudiante']['apellido_materno'] }}</p>
                                <p><strong>DNI:</strong> {{ $datos['estudiante']['numero_documento'] }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Carrera:</strong> {{ $datos['carrera']['nombre'] }}</p>
                                <p><strong>Ciclo:</strong> {{ $datos['ciclo']['nombre'] }}</p>
                                @if(isset($datos['turno']))
                                    <p><strong>Turno:</strong> {{ $datos['turno']['nombre'] }}</p>
                                @endif
                                <p><strong>Fecha de Generación:</strong> {{ \Carbon\Carbon::parse($fecha_generacion)->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="invalid-icon mb-3">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <h2 class="card-title text-danger">Constancia Inválida</h2>
                    <p class="card-text">{{ $mensaje ?? 'El código de verificación no es válido o la constancia no existe.' }}</p>
                @endif

                <div class="mt-4">
                    <a href="{{ url('/') }}" class="btn btn-primary">Volver al Inicio</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
