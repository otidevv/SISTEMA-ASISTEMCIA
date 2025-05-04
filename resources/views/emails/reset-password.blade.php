<!DOCTYPE html>
<html>

<head>
    <title>Recuperación de Contraseña</title>
</head>

<body>
    <h1>Recuperación de Contraseña</h1>
    <p>Hola,</p>
    <p>Estás recibiendo este correo porque hemos recibido una solicitud de restablecimiento de contraseña para tu
        cuenta.</p>
    <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
    <p><a href="{{ $resetUrl }}">Restablecer Contraseña</a></p>
    <p>Este enlace de restablecimiento de contraseña caducará en 24 horas.</p>
    <p>Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna otra acción.</p>
    <p>Saludos,<br>{{ config('app.name') }}</p>
</body>

</html>
