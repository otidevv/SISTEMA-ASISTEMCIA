<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="CEPRE UNAMAD">
    <meta name="google-site-verification" content="9BjJJcR6TdrhZrXMG1g9k96vHRgOktWvgDyPqWmyJz4" />
    <meta name="description" content="Portal oficial del Centro Preuniversitario de la Universidad Nacional Amazónica de Madre de Dios. Prepárate para el ingreso directo con los mejores docentes.">
    <meta name="keywords" content="CEPRE UNAMAD, UNAMAD, Preuniversitario, Admisión, Ingreso Directo, Madre de Dios">
    <link rel="canonical" href="https://portalcepre.unamad.edu.pe/">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://portalcepre.unamad.edu.pe/">
    <meta property="og:title" content="Portal CEPRE UNAMAD | Admisión e Ingreso Directo">
    <meta property="og:description" content="Portal oficial de CEPRE UNAMAD. Revisa notas, vacantes y programas académicos para el ingreso directo a la universidad.">
    <meta property="og:image" content="{{ asset('assets_cepre/img/logo/logo.png') }}">
    <meta property="og:site_name" content="CEPRE UNAMAD">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://portalcepre.unamad.edu.pe/">
    <meta property="twitter:title" content="Portal CEPRE UNAMAD | Admisión e Ingreso Directo">
    <meta property="twitter:description" content="Portal oficial de CEPRE UNAMAD. Revisa notas, vacantes y programas académicos.">
    <meta property="twitter:image" content="{{ asset('assets_cepre/img/logo/logo.png') }}">
    <meta name="twitter:site" content="@CEPREUNAMAD">

    <!-- JSON-LD Structured Data para Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "CEPRE UNAMAD",
        "alternateName": ["Portal CEPRE UNAMAD", "Centro Preuniversitario UNAMAD"],
        "url": "https://portalcepre.unamad.edu.pe/"
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "CEPRE UNAMAD",
        "alternateName": "Centro Preuniversitario de la Universidad Nacional Amazónica de Madre de Dios",
        "url": "https://portalcepre.unamad.edu.pe/",
        "logo": "https://portalcepre.unamad.edu.pe/assets_cepre/img/logo/logo.png",
        "description": "Centro Preuniversitario de la Universidad Nacional Amazónica de Madre de Dios. Prepárate para el ingreso directo con los mejores docentes.",
        "telephone": "+51 993 110 927",
        "parentOrganization": {
            "@type": "CollegeOrUniversity",
            "name": "Universidad Nacional Amazónica de Madre de Dios",
            "alternateName": "UNAMAD"
        }
    }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">

    <!-- DNS Preconnect para CDNs (Acelera la conexión a scripts externos) -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <title>@yield('title', 'CEPRE UNAMAD | Centro Preuniversitario - Ingreso Directo')</title>

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
        
        if (carousel) {
            let current = 0;
            const cards = carousel.children.length;

            function updateIndicator() {
                indicators.forEach((dot, i) => {
                    dot.classList.toggle("bg-secondary", i === current);
                    dot.classList.toggle("bg-light", i !== current);
                });
            }

            function scrollToCard(index) {
                if (carousel.children[0]) {
                    const cardWidth = carousel.children[0].offsetWidth + 16; // ancho + gap
                    carousel.style.transform = `translateX(-${index * cardWidth}px)`;
                    current = index;
                    updateIndicator();
                }
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
            
            // Initializing indicators if any
            updateIndicator();
        }
    </script>
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/es_ES/sdk.js#xfbml=1&version=v18.0"
        nonce="CEPREUNAMAD">
    </script>
    
    @include('partials.cepre.info-modal')
    @include('partials.chatbot')
    @vite(['resources/js/assistant/chatbot.js', 'resources/css/assistant/chatbot.css'])
</body>
</html>
