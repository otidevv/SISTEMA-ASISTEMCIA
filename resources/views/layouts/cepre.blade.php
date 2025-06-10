<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="modinatheme">
    <meta name="description" content="Eduspace - Online Course, Education & University Html Template">
    <meta name="keywords" content="CEPRE UNAMAD, UNAMAD, Preuniversitario">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CEPRE UNAMAD')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets_cepre/img/favicon.svg') }}">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/meanmenu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/style.css') }}">

    @stack('css')
</head>

<body>
    <!-- Preloader opcional (si deseas mostrarlo en todas las páginas) -->
    @yield('preloader')

    <!-- Contenido principal -->
    @yield('content')

    <!-- Footer opcional -->
    @yield('footer')

    <!-- JS -->
    <script src="{{ asset('assets_cepre/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/odometer.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/jquery.appear.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/jquery.meanmenu.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/circle-progress.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets_cepre/js/main.js') }}"></script>

    @stack('js')
</body>

</html>
