@extends('layouts.cepre')

@section('title', 'CEPRE UNAMAD | Ingreso Directo a la Universidad')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/style.css') }}">
@endpush

@section('content')
    <style>
        /* ===== PRELOADER EVOLUTION 2.0 CEPRE UNAMAD ===== */
        .preloader {
            background: #0c1e2f !important;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Fondo Animado Mesh Gradient (Efecto Aurora) */
        .preloader::before {
            content: '';
            position: absolute;
            width: 150%; height: 150%;
            background: radial-gradient(circle at 20% 30%, rgba(236, 0, 140, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 80% 70%, rgba(0, 174, 239, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 50% 50%, rgba(140, 198, 63, 0.1) 0%, transparent 50%);
            animation: meshGradient 12s ease-in-out infinite alternate;
            z-index: 1;
        }

        @keyframes meshGradient {
            0% { transform: translate(-10%, -10%) rotate(0deg); }
            100% { transform: translate(10%, 10%) rotate(5deg); }
        }

        .preloader .animation-preloader {
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.8s cubic-bezier(0.645, 0.045, 0.355, 1);
        }

        /* Logo con respiración */
        .preloader .edu-preloader-icon {
            position: relative;
            width: 220px; height: 180px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preloader .edu-preloader-icon img {
            width: 180px;
            z-index: 2;
            animation: logoBreath 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(0, 174, 239, 0.4));
        }

        @keyframes logoBreath {
            0%, 100% { transform: scale(1); filter: brightness(1) drop-shadow(0 0 20px rgba(0, 174, 239, 0.3)); }
            50% { transform: scale(1.08); filter: brightness(1.2) drop-shadow(0 0 35px rgba(236, 0, 140, 0.4)); }
        }

        /* Anillo orbital avanzado */
        .preloader .edu-preloader-icon::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.05);
            border-top: 3px solid #00aeef;
            border-right: 3px solid #ec008c;
            animation: cepreSpin 2s linear infinite;
        }

        /* Tipografía */
        .preloader .txt-loading {
            font: 800 3.2em "Sora", sans-serif !important;
            letter-spacing: 8px !important;
            margin-bottom: 5px;
            text-shadow: 0 10px 30px rgba(0,0,0,0.5);
            position: relative;
        }

        .preloader .txt-loading .letters-loading {
            color: rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .preloader .txt-loading .letters-loading::before {
            content: attr(data-text-preloader);
            position: absolute;
            top: 0; left: 0;
            color: #00aeef;
            opacity: 0;
            animation: letters-loading 2.8s infinite;
        }

        @keyframes letters-loading {
            0%, 75%, 100% { opacity: 0; transform: rotateY(-90deg); }
            25%, 50% { opacity: 1; transform: rotateY(0deg); }
        }

        /* Delays para las letras */
        .preloader .txt-loading .letters-loading:nth-child(1)::before { animation-delay: 0s; }
        .preloader .txt-loading .letters-loading:nth-child(2)::before { animation-delay: 0.1s; }
        .preloader .txt-loading .letters-loading:nth-child(3)::before { animation-delay: 0.2s; }
        .preloader .txt-loading .letters-loading:nth-child(4)::before { animation-delay: 0.3s; }
        .preloader .txt-loading .letters-loading:nth-child(5)::before { animation-delay: 0.4s; }
        .preloader .txt-loading .letters-loading:nth-child(6)::before { animation-delay: 0.6s; }
        .preloader .txt-loading .letters-loading:nth-child(7)::before { animation-delay: 0.7s; }
        .preloader .txt-loading .letters-loading:nth-child(8)::before { animation-delay: 0.8s; }
        .preloader .txt-loading .letters-loading:nth-child(9)::before { animation-delay: 0.9s; }
        .preloader .txt-loading .letters-loading:nth-child(10)::before { animation-delay: 1.0s; }
        .preloader .txt-loading .letters-loading:nth-child(11)::before { animation-delay: 1.1s; }

        /* Frases de carga dinámicas */
        #preloader-phrase {
            font-family: "Sora", sans-serif;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 15px;
            height: 20px;
            transition: opacity 0.5s;
        }

        /* Salida Cinemática (Zoom Out) */
        .preloader.loaded {
            background: transparent !important;
        }

        .preloader.loaded .animation-preloader {
            opacity: 0;
            transform: scale(1.5);
            filter: blur(10px);
        }

        /* Ocultamos las barras blancas viejas en el HTML para usar solo el nuevo efecto */
        .preloader .loader { display: none !important; }

        @media (max-width: 767px) {
            .preloader .txt-loading { font-size: 2em !important; }
            .preloader .edu-preloader-icon { width: 130px; height: 130px; }
            .preloader .edu-preloader-icon img { width: 70px; }
        }

        /* ===== POSTULACIÓN HIGHLIGHTS & FAB ===== */
        @keyframes btn-glow-pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 160, 227, 0.5); }
            70% { box-shadow: 0 0 0 15px rgba(0, 160, 227, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 160, 227, 0); }
        }

        .btn-postulacion-main {
            animation: btn-glow-pulse 2s infinite !important;
            position: relative;
            z-index: 10;
        }

        .fab-postulacion {
            position: fixed;
            bottom: 90px;
            left: 30px;
            z-index: 9991;
            display: flex;
            align-items: center;
            flex-direction: row-reverse;
            gap: 12px;
            pointer-events: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
            transform: translateY(20px);
        }

        .fab-postulacion.active {
            pointer-events: auto;
            opacity: 1;
            transform: translateY(0);
        }

        .fab-label {
            background: #0c1e2f;
            color: white;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            white-space: nowrap;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .fab-button {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #8cc63f, #00aeef);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            animation: btn-glow-pulse 2s infinite;
        }

        .fab-button:hover {
            transform: scale(1.1) rotate(5deg);
        }

        @media (max-width: 768px) {
            .fab-postulacion { bottom: 80px; left: 20px; }
            .fab-button { width: 55px; height: 55px; font-size: 22px; }
            .fab-label { display: none; }
        }

        /* Driver.js Premium Overrides */
        .cepre-premium-popover, 
        .cepre-premium-popover *, 
        .cepre-premium-popover ::before, 
        .cepre-premium-popover ::after {
            background-image: none !important;
        }
        
        .cepre-premium-popover {
            background: #0c1e2f !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.8) !important;
            padding: 20px !important;
            width: 350px !important; /* Increased width */
            max-width: 90vw !important;
            height: auto !important;
            min-height: auto !important;
            z-index: 2000000000 !important;
        }
        .cepre-premium-popover .driver-popover-title {
            font-family: 'Sora', sans-serif !important;
            font-size: 18px !important;
            font-weight: 800 !important;
            color: #00aeef !important;
            margin: 0 0 10px 0 !important;
            line-height: 1.2 !important;
        }
        .cepre-premium-popover .driver-popover-description {
            font-family: 'Inter', sans-serif !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            color: #ffffff !important;
            margin: 0 0 15px 0 !important;
        }
        .cepre-premium-popover .driver-popover-footer {
            margin-top: 15px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px !important;
            padding: 0 !important;
        }
        .cepre-premium-popover .driver-popover-progress-text {
            color: rgba(255, 255, 255, 0.7) !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            margin: 0 !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }
        .cepre-premium-popover .driver-popover-navigation-btns {
            display: flex !important;
            gap: 8px !important;
        }
        .cepre-premium-popover .driver-popover-close-btn {
            color: rgba(255, 255, 255, 0.7) !important;
            transition: all 0.3s;
        }
        .cepre-premium-popover .driver-popover-arrow {
            border-bottom-color: #0c1e2f !important;
            border-top-color: #0c1e2f !important;
        }
        .cepre-premium-popover button {
            text-shadow: none !important;
            border-radius: 8px !important;
            padding: 8px 15px !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            opacity: 1 !important;
            cursor: pointer !important;
            pointer-events: auto !important;
            white-space: nowrap !important;
        }
        .driver-popover-next-btn { 
            background-color: #00aeef !important; 
            color: #ffffff !important; 
            border: none !important; 
        }
        .driver-popover-prev-btn { 
            background-color: rgba(255,255,255,0.1) !important; 
            color: #ffffff !important; 
            border: 1px solid rgba(255,255,255,0.1) !important; 
        }

        .cepre-premium-popover i {
            font-family: "Font Awesome 5 Free", "Font Awesome 6 Free", "Font Awesome 5 Pro", "Font Awesome 6 Pro", "Font Awesome" !important;
            font-weight: 900 !important;
            display: inline-block !important;
        }

        /* Botón de Ayuda Flotante (Pequeño y discreto) */
        .btn-ayuda-tour {
            position: fixed;
            top: 40%;
            right: 30px;
            bottom: auto;
            background: rgba(12, 30, 47, 0.9);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 999999;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            font-size: 18px;
            font-family: "Font Awesome 5 Free", "Font Awesome 6 Free", "Font Awesome 5 Pro", "Font Awesome 6 Pro", "Font Awesome" !important;
            font-weight: 900 !important;
        }
        .btn-ayuda-tour:hover {
            background: #ec008c;
            transform: scale(1.1) rotate(15deg);
            color: white;
        }

        /* Optimizaciones para Celulares */
        @media (max-width: 767px) {
            .cepre-premium-popover {
                width: 280px !important;
                max-width: 85vw !important;
                padding: 15px !important;
            }
            .cepre-premium-popover .driver-popover-title {
                font-size: 16px !important;
            }
            .cepre-premium-popover .driver-popover-description {
                font-size: 13px !important;
                line-height: 1.4 !important;
            }
            .cepre-premium-popover .driver-popover-footer {
                flex-direction: column !important;
                align-items: center !important;
                gap: 12px !important;
            }
            .cepre-premium-popover .driver-popover-navigation-btns {
                width: 100% !important;
                justify-content: center !important;
            }
            .cepre-premium-popover button {
                width: 100% !important;
                text-align: center !important;
                padding: 10px !important;
            }
            .btn-ayuda-tour {
                width: 40px;
                height: 40px;
                top: 50%;
                right: 15px;
                bottom: auto;
            }
            .cepre-premium-popover .driver-popover-close-btn {
                top: 8px !important;
                right: 12px !important;
                font-size: 20px !important;
            }
        }
    </style>

    <!-- Botón de ayuda para repetir tour -->
    <div class="btn-ayuda-tour" onclick="initOnboarding(true)" title="Ver ayuda interactiva">
        <i class="fas fa-question"></i>
    </div>
    <div id="preloader" class="preloader">
        <div class="animation-preloader">
            <div class="edu-preloader-icon">
                <img src="{{ asset('assets_cepre/img/logo/logo2_0.png') }}" alt="CEPRE UNAMAD Logo 2.0">
            </div>
            <div class="txt-loading">
                @foreach (['C', 'E', 'P', 'R', 'E'] as $letter)
                    <span class="letters-loading" data-text-preloader="{{ $letter }}">{{ $letter }}</span>
                @endforeach
                @foreach (['U', 'N', 'A', 'M', 'A', 'D'] as $i => $letter)
                    <span class="letters-loading {{ $i === 0 ? 'letter-gap' : '' }}" data-text-preloader="{{ $letter }}">{{ $letter }}</span>
                @endforeach
            </div>
            <p id="preloader-phrase">Preparando excelencia...</p>
        </div>
    </div>

    <script>
        // Logica de frases dinamicas para el preloader
        (function() {
            const phrases = [
                "Preparando excelencia...",
                "Calculando tu futuro...",
                "Conectando vacantes...",
                "Iniciando plataforma...",
                "Cargando Centro Preuniversitario..."
            ];
            let current = 0;
            const p = document.getElementById('preloader-phrase');
            if (p) {
                const interval = setInterval(() => {
                    p.style.opacity = 0;
                    setTimeout(() => {
                        current = (current + 1) % phrases.length;
                        p.innerText = phrases[current];
                        p.style.opacity = 1;
                    }, 500);
                }, 1500);
                
                window.addEventListener('cepre_ready', () => clearInterval(interval));
            }
        })();
    </script>

    @include('partials.cepreunamad')
    @include('partials.cepre.modal-detalles-ciclo')

    <script>
        // Inyectar departamentos desde el servidor para carga instantánea (Profesional Hydration)
        window.DEPARTAMENTOS_INICIALES = @json($departamentos ?? []);
    </script>
@endsection

