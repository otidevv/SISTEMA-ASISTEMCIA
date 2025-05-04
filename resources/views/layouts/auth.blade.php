<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Sistema de Asistencia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistema de control de asistencia para instituciones educativas" name="description" />
    <meta content="Sistema de Asistencia" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Icons CSS -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- App CSS -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-default-stylesheet" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Config js -->
    <script src="{{ asset('assets/js/config.js') }}"></script>

</head>

<body class="authentication-bg">

    @yield('content')

    <!-- Vendor js -->
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>

    <!-- Feather icons js -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
        });
    </script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

</body>

</html>
