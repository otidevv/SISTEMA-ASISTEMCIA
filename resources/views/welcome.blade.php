@extends('layouts.cepre')

@section('title', 'CEPRE UNAMAD')

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
    </style>
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
@endsection

