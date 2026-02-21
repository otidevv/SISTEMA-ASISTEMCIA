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

    <title>@yield('title', 'Inicio') | CEPRE UNAMAD</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('faviconcepre.svg') }}?v=2">
    <link rel="icon" type="image/x-icon" href="{{ asset('faviconcepre.ico') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('faviconcepre.ico') }}?v=2">

    <!-- Tailwind (con Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
    <!-- Preloader opcional -->
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

    <!-- Script para el apartado de noticias facebook -->
    <script>
        const carousel = document.getElementById("facebookCarousel");
        const indicators = document.querySelectorAll(".indicator-dot");
        let current = 0;
        const cards = carousel.children.length;

        function updateIndicator() {
            indicators.forEach((dot, i) => {
                dot.classList.toggle("bg-secondary", i === current);
                dot.classList.toggle("bg-light", i !== current);
            });
        }

        function scrollToCard(index) {
            const cardWidth = carousel.children[0].offsetWidth + 16; // ancho + gap
            carousel.style.transform = `translateX(-${index * cardWidth}px)`;
            current = index;
            updateIndicator();
        }

        function nextSlide() {
            current = (current + 1) % cards;
            scrollToCard(current);
        }

        function prevSlide() {
            current = (current - 1 + cards) % cards;
            scrollToCard(current);
        }

        setInterval(() => nextSlide(), 5000);

        window.addEventListener("resize", () => scrollToCard(current));
    </script>
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/es_ES/sdk.js#xfbml=1&version=v18.0"
        nonce="CEPREUNAMAD">
    </script>
    
    @include('partials.cepre.info-modal')
</body>
</html>
