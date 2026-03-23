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
        /* ===== PRELOADER GLASSMORPHISM CEPRE UNAMAD ===== */
        .preloader {
            background: rgba(12, 30, 47, 0.72) !important;
            backdrop-filter: blur(18px) saturate(1.2);
            -webkit-backdrop-filter: blur(18px) saturate(1.2);
        }
        .preloader .loader .loader-section .bg {
            background-color: rgba(12, 30, 47, 0.72) !important;
        }
        .preloader .animation-preloader {
            z-index: 1000;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Logo con spinner orbital */
        .preloader .edu-preloader-icon {
            display: flex !important;
            align-items: center;
            justify-content: center;
            margin: 0 auto 35px auto;
            position: relative;
            width: 150px;
            height: 150px;
        }
        .preloader .edu-preloader-icon img {
            width: 80px;
            height: auto;
            position: relative;
            z-index: 2;
            display: block;
            margin: 0 auto;
            filter: drop-shadow(0 4px 15px rgba(236, 0, 140, 0.15));
        }
        /* Anillo giratorio */
        .preloader .edu-preloader-icon::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.08);
            border-top: 2px solid #ec008c;
            border-right: 2px solid #00aeef;
            animation: cepreSpin 1.5s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        @keyframes cepreSpin {
            to { transform: rotate(360deg); }
        }

        /* Tipografía */
        .preloader .animation-preloader .txt-loading {
            font: 700 2.8em "Sora", system-ui, sans-serif !important;
            letter-spacing: 6px !important;
        }
        .preloader .animation-preloader .txt-loading .letters-loading {
            color: rgba(255, 255, 255, 0.1) !important;
        }
        .preloader .animation-preloader .txt-loading .letters-loading::before {
            animation: letters-loading 2.4s infinite !important;
            color: #ec008c !important;
            top: 0 !important;
            text-shadow: 0 0 12px rgba(236, 0, 140, 0.25);
        }

        /* Delays para las 11 letras */
        .preloader .txt-loading .letters-loading:nth-child(1)::before { animation-delay: 0s !important; }
        .preloader .txt-loading .letters-loading:nth-child(2)::before { animation-delay: 0.1s !important; }
        .preloader .txt-loading .letters-loading:nth-child(3)::before { animation-delay: 0.2s !important; }
        .preloader .txt-loading .letters-loading:nth-child(4)::before { animation-delay: 0.3s !important; }
        .preloader .txt-loading .letters-loading:nth-child(5)::before { animation-delay: 0.4s !important; }
        .preloader .txt-loading .letters-loading.letter-gap { margin-left: 16px; }
        .preloader .txt-loading .letters-loading:nth-child(6)::before { animation-delay: 0.6s !important; }
        .preloader .txt-loading .letters-loading:nth-child(7)::before { animation-delay: 0.7s !important; }
        .preloader .txt-loading .letters-loading:nth-child(8)::before { animation-delay: 0.8s !important; }
        .preloader .txt-loading .letters-loading:nth-child(9)::before { animation-delay: 0.9s !important; }
        .preloader .txt-loading .letters-loading:nth-child(10)::before { animation-delay: 1.0s !important; }
        .preloader .txt-loading .letters-loading:nth-child(11)::before { animation-delay: 1.1s !important; }

        /* Subtítulo */
        .cepre-subtitle {
            font-family: "Sora", sans-serif;
            font-size: 11px;
            font-weight: 400;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.35);
            margin-top: 8px;
        }

        /* Línea de carga tricolor CEPRE */
        .cepre-loader-line {
            margin-top: 40px;
            width: 180px;
            height: 2px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 2px;
            overflow: hidden;
            position: relative;
        }
        .cepre-loader-line::after {
            content: '';
            position: absolute;
            left: 0; top: 0;
            height: 100%;
            width: 45%;
            border-radius: 2px;
            background: linear-gradient(90deg, #ec008c, #00aeef, #8cc63f);
            box-shadow: 0 0 6px rgba(236, 0, 140, 0.2);
            animation: cepreLineSlide 1.8s ease-in-out infinite;
        }
        @keyframes cepreLineSlide {
            0%   { left: -45%; }
            100% { left: 100%; }
        }

        /* Ocultar p genérico */
        .preloader > .animation-preloader > p { display: none !important; }

        @media (max-width: 767px) {
            .preloader .animation-preloader .txt-loading {
                font-size: 1.8em !important;
                letter-spacing: 4px !important;
            }
            .preloader .edu-preloader-icon {
                width: 120px; height: 120px;
            }
            .preloader .edu-preloader-icon img { width: 60px; }
            .cepre-subtitle { font-size: 9px; letter-spacing: 3px; }
            .cepre-loader-line { width: 140px; }
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
                <img src="{{ asset('assets/images/logo cepre black.svg') }}" alt="CEPRE UNAMAD">
            </div>
            <div class="txt-loading">
                @foreach (['C', 'E', 'P', 'R', 'E'] as $letter)
                    <span class="letters-loading" data-text-preloader="{{ $letter }}">{{ $letter }}</span>
                @endforeach
                @foreach (['U', 'N', 'A', 'M', 'A', 'D'] as $i => $letter)
                    <span class="letters-loading {{ $i === 0 ? 'letter-gap' : '' }}" data-text-preloader="{{ $letter }}">{{ $letter }}</span>
                @endforeach
            </div>
            <div class="cepre-subtitle">Centro Preuniversitario</div>
            <div class="cepre-loader-line"></div>
        </div>
        <div class="loader">
            <div class="row">
                <div class="col-3 loader-section section-left"><div class="bg"></div></div>
                <div class="col-3 loader-section section-left"><div class="bg"></div></div>
                <div class="col-3 loader-section section-right"><div class="bg"></div></div>
                <div class="col-3 loader-section section-right"><div class="bg"></div></div>
            </div>
        </div>
    </div>

    @include('partials.cepreunamad')

    <script>
        // Inyectar departamentos desde el servidor para carga instantánea (Profesional Hydration)
        window.DEPARTAMENTOS_INICIALES = @json($departamentos ?? []);
    </script>
@endsection

