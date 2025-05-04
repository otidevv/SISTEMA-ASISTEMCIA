<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Sistema de Asistencia</title>
    <!-- CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    @include('partials.header')

    <!-- Content -->
    <main class="container">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')

    <!-- JavaScript -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>