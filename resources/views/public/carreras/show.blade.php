@extends('layouts.cepre')

@section('title', $carrera->nombre . ' | CEPRE UNAMAD')

@section('content')
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    <style>
        /* ============================== */
        /* Variables                       */
        /* ============================== */
        :root {
            --det-azul: #0d2838;
            --det-azul2: #1f3e76;
            --det-magenta: #e2006a;
            --det-cyan: #00a19a;
            --det-cyan-light: #00aeef;
        }

        /* ============================== */
        /* Hero Premium                    */
        /* ============================== */
        .det-hero {
            background: linear-gradient(135deg, #0d2838 0%, #1f3e76 50%, #1a1a4a 100%);
            padding: 110px 0 90px;
            color: white;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .det-hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 0;
        }

        .det-hero-bg::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(13,40,56,0.75) 0%, rgba(31,62,118,0.70) 50%, rgba(26,26,74,0.75) 100%);
        }

        .det-hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: rgba(0,161,154,0.08);
            z-index: 1;
        }

        .det-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(226,0,106,0.06);
            z-index: 1;
        }

        .det-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .det-relative { position: relative; z-index: 2; }

        /* Back Link */
        .det-back {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 6px 16px;
            border-radius: 20px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .det-back:hover {
            color: white;
            background: rgba(255,255,255,0.15);
            transform: translateX(-5px);
        }

        /* Logo */
        .det-logo-wrap {
            background: white;
            width: 130px;
            height: 130px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px auto;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            padding: 18px;
            border: 3px solid rgba(255,255,255,0.15);
            transition: transform 0.4s ease;
        }

        .det-logo-wrap:hover {
            transform: scale(1.05) rotate(-2deg);
        }

        .det-logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* Title */
        .det-title {
            font-size: clamp(30px, 5vw, 46px);
            font-weight: 900;
            margin-bottom: 18px;
            line-height: 1.15;
            color: white !important;
            text-shadow: 0 0 25px rgba(255,255,255,0.3), 0 3px 15px rgba(0,0,0,0.3);
            letter-spacing: -0.02em;
        }

        .det-badge-row {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .det-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(226,0,106, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            padding: 7px 18px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(226,0,106,0.3);
        }

        .det-badge-cyan {
            background: rgba(0,161,154, 0.2);
            border-color: rgba(0,161,154,0.3);
        }

        /* ============================== */
        /* Stats Bar Flotante              */
        /* ============================== */
        .det-stats-section {
            margin-top: -45px;
            margin-bottom: 50px;
            position: relative;
            z-index: 10;
        }

        .det-stats {
            background: white;
            border-radius: 18px;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            max-width: 900px;
            margin: 0 auto;
            overflow: hidden;
        }

        .det-stat {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 24px 28px;
            border-right: 1px solid rgba(0,0,0,0.06);
            transition: background 0.3s ease;
        }

        .det-stat:last-child { border-right: none; }

        .det-stat:hover {
            background: #f8fafc;
        }

        .det-stat-icon {
            font-size: 22px;
            color: var(--det-cyan);
            background: linear-gradient(135deg, rgba(0,161,154,0.1), rgba(0,174,239,0.1));
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            flex-shrink: 0;
        }

        .det-stat-label {
            display: block;
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .det-stat-value {
            display: block;
            font-size: 15px;
            color: var(--det-azul);
            font-weight: 800;
        }

        /* ============================== */
        /* Content Layout                  */
        /* ============================== */
        .det-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 35px;
            padding-bottom: 60px;
        }

        /* Cards */
        .det-card {
            background: white;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 24px;
            border: 1px solid rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .det-card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }

        .det-card-accent-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .det-section-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--det-azul);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .det-section-title .det-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
            flex-shrink: 0;
        }

        .det-icon-cyan { background: linear-gradient(135deg, #00a19a, #00aeef); }
        .det-icon-magenta { background: linear-gradient(135deg, #e2006a, #ff4d94); }
        .det-icon-azul { background: linear-gradient(135deg, #0d2838, #1f3e76); }
        .det-icon-green { background: linear-gradient(135deg, #059669, #34d399); }

        .det-text {
            font-size: 15px;
            line-height: 1.8;
            color: #475569;
        }

        /* Misión / Visión Grid */
        .det-mv-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 24px;
        }

        .det-mv-card {
            background: white;
            border-radius: 16px;
            padding: 26px;
            border: 1px solid rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .det-mv-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }

        .det-mv-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .det-mv-mision::before { background: linear-gradient(to bottom, #00a19a, #00aeef); }
        .det-mv-vision::before { background: linear-gradient(to bottom, #e2006a, #ff4d94); }

        .det-mv-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--det-azul);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .det-mv-title i { font-size: 14px; }

        /* Laboral Items */
        .det-laboral-grid {
            display: grid;
            gap: 10px;
        }

        .det-laboral-item {
            display: flex;
            gap: 14px;
            padding: 14px 18px;
            border-radius: 12px;
            align-items: center;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }

        .det-laboral-item:hover {
            background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
            border-color: rgba(0,161,154,0.15);
            transform: translateX(5px);
        }

        .det-laboral-item i {
            color: var(--det-cyan);
            font-size: 14px;
            flex-shrink: 0;
        }

        /* Objetivos */
        .det-objetivo-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,0,0,0.04);
        }

        .det-objetivo-item:last-child { border-bottom: none; margin-bottom: 0; }

        .det-objetivo-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00a19a, #00aeef);
            color: white;
            font-size: 12px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* ============================== */
        /* Sidebar                         */
        /* ============================== */
        .det-sidebar-card {
            background: white;
            border-radius: 18px;
            padding: 28px;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .det-sidebar-card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .det-pdf-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 28px;
            color: #ef4444;
        }

        .det-btn-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--det-azul), var(--det-azul2));
            color: white;
            font-weight: 700;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
        }

        .det-btn-download:hover {
            background: linear-gradient(135deg, #00a19a, #00aeef);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,161,154,0.3);
        }

        /* CTA Card */
        .det-cta-card {
            background: linear-gradient(135deg, #0d2838 0%, #1f3e76 100%);
            color: white;
            text-align: center;
            padding: 35px 25px;
            border-radius: 18px;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .det-cta-card::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(0,161,154,0.15);
        }

        .det-cta-card::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(226,0,106,0.1);
        }

        .det-btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: var(--det-azul);
            font-weight: 800;
            padding: 12px 28px;
            border-radius: 30px;
            text-decoration: none;
            margin-top: 18px;
            transition: all 0.3s;
            font-size: 14px;
            position: relative;
            z-index: 2;
        }

        .det-btn-cta:hover {
            transform: scale(1.05);
            color: var(--det-magenta);
            box-shadow: 0 8px 20px rgba(255,255,255,0.2);
        }

        /* Info Card */
        .det-info-card {
            border-radius: 18px;
            padding: 24px;
            background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
            border: 1px solid rgba(0,161,154,0.15);
            margin-bottom: 24px;
        }

        .det-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0,161,154,0.08);
            font-size: 14px;
            color: #334155;
        }

        .det-info-item:last-child { border-bottom: none; }

        .det-info-item i {
            color: var(--det-cyan);
            width: 20px;
            text-align: center;
        }

        /* ============================== */
        /* Animations                      */
        /* ============================== */
        .det-anim {
            opacity: 0;
            transform: translateY(25px);
            transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .det-anim.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ============================== */
        /* Responsive                      */
        /* ============================== */
        @media (max-width: 991px) {
            .det-grid { grid-template-columns: 1fr; }
            .det-stats { grid-template-columns: 1fr; }
            .det-stat { border-right: none; border-bottom: 1px solid rgba(0,0,0,0.06); }
            .det-stat:last-child { border-bottom: none; }
            .det-mv-grid { grid-template-columns: 1fr; }
            .det-hero { padding: 90px 0 70px; }
        }
    </style>

    <!-- ============================== -->
    <!-- Hero Premium                    -->
    <!-- ============================== -->
    @php
        // Mapeo de portadas por slug de carrera
        // Solo agrega la ruta de la imagen en public/assets_cepre/img/ para cada carrera
        $portadas = [
            'ingenieria-agroindustrial'                     => '/assets_cepre/img/preloader.gif'
            'ingenieria-de-sistemas-e-informatica'          => '/assets_cepre/img/portada-ing-sist.webp',
            'ingenieria-forestal-y-medio-ambiente'          => '',
            'enfermeria'                                    => '',
            'medicina-veterinaria-zootecnia'                => '',
            'administracion-y-negocios-internacionales'     => '',
            'contabilidad-y-finanzas'                       => '',
            'derecho-y-ciencias-politicas'                  => '',
            'ecoturismo'                                    => '',
            'educacion-especialidad-inicial-y-especial'     => '',
            'educacion-especialidad-matematica-y-computacion' => '',
            'educacion-especialidad-primaria-e-informatica' => '',
        ];
        $portadaUrl = !empty($portadas[$carrera->slug]) ? $portadas[$carrera->slug] : null;
    @endphp
    <section class="det-hero">
        @if($portadaUrl)
            <div class="det-hero-bg" style="background-image: url('{{ asset($portadaUrl) }}');"></div>
        @endif
        <div class="kene-pattern-overlay-2" style="opacity: 0.05;"></div>
        <div class="det-container det-relative">
            <a href="{{ route('public.carreras.index') }}" class="det-back">
                <i class="fas fa-arrow-left"></i> Volver a Carreras
            </a>

            <div class="det-logo-wrap">
                @if($carrera->imagen_url)
                    <img src="{{ asset($carrera->imagen_url) }}" alt="{{ $carrera->nombre }}">
                @else
                    <i class="fas fa-graduation-cap" style="font-size: 50px; color: var(--det-cyan);"></i>
                @endif
            </div>

            <h1 class="det-title">{{ $carrera->nombre }}</h1>

            <div class="det-badge-row">
                <span class="det-badge">
                    <i class="fas fa-university"></i> Carrera Profesional — UNAMAD
                </span>
                <span class="det-badge det-badge-cyan">
                    <i class="fas fa-map-marker-alt"></i> Madre de Dios, Perú
                </span>
            </div>
        </div>
    </section>

    <!-- ============================== -->
    <!-- Stats Bar Flotante              -->
    <!-- ============================== -->
    <section class="det-stats-section">
        <div class="det-container">
            <div class="det-stats det-anim">
                <div class="det-stat">
                    <div class="det-stat-icon"><i class="fas fa-certificate"></i></div>
                    <div>
                        <span class="det-stat-label">Grado Académico</span>
                        <span class="det-stat-value">{{ $carrera->grado ?? 'Bachiller' }}</span>
                    </div>
                </div>
                <div class="det-stat">
                    <div class="det-stat-icon"><i class="fas fa-user-tie"></i></div>
                    <div>
                        <span class="det-stat-label">Título Profesional</span>
                        <span class="det-stat-value">{{ $carrera->titulo ?? 'Licenciado/Ingeniero' }}</span>
                    </div>
                </div>
                <div class="det-stat">
                    <div class="det-stat-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <span class="det-stat-label">Duración</span>
                        <span class="det-stat-value">{{ $carrera->duracion ?? '10 Semestres' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Divisor -->
    <div class="torn-paper-edge"></div>

    <!-- ============================== -->
    <!-- Content                         -->
    <!-- ============================== -->
    <section class="courses-section academic-notebook-pattern" style="padding: 60px 0;">
        <main class="det-relative">
            <div class="det-container">
                <div class="det-grid">

                    <!-- ======================== -->
                    <!-- Main Content              -->
                    <!-- ======================== -->
                    <div class="det-main">

                        <!-- Sobre la Carrera -->
                        <div class="det-card det-anim">
                            <div class="det-card-accent-top" style="background: linear-gradient(90deg, #00a19a, #00aeef);"></div>
                            <h2 class="det-section-title">
                                <span class="det-icon det-icon-cyan"><i class="fas fa-info-circle"></i></span>
                                Sobre la Carrera
                            </h2>
                            <div class="det-text">
                                @if($carrera->descripcion)
                                    {{ $carrera->descripcion }}
                                @else
                                    <p style="font-style: italic; opacity: 0.8; margin: 0;">
                                        La carrera profesional de <strong>{{ $carrera->nombre }}</strong> en la UNAMAD forma líderes altamente capacitados, competitivos y con sólidos valores éticos, preparados para contribuir al desarrollo integral y sostenible de la región Amazónica y el país.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Perfil del Egresado -->
                        <div class="det-card det-anim">
                            <div class="det-card-accent-top" style="background: linear-gradient(90deg, #e2006a, #ff4d94);"></div>
                            <h2 class="det-section-title">
                                <span class="det-icon det-icon-magenta"><i class="fas fa-user-graduate"></i></span>
                                Perfil del Egresado
                            </h2>
                            <div class="det-text">
                                @if($carrera->perfil)
                                    {{ $carrera->perfil }}
                                @else
                                    <p style="font-style: italic; opacity: 0.8; margin: 0;">
                                        El egresado posee una destacada formación científica, tecnológica y humanística. Ha desarrollado competencias para investigar, innovar y liderar soluciones complejas en su área de especialidad.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Misión y Visión -->
                        <div class="det-mv-grid">
                            <div class="det-mv-card det-mv-mision det-anim">
                                <h3 class="det-mv-title">
                                    <i class="fas fa-bullseye" style="color: var(--det-cyan);"></i> Misión
                                </h3>
                                <div class="det-text" style="font-size: 14px; line-height: 1.65;">
                                    @if($carrera->mision)
                                        {{ $carrera->mision }}
                                    @else
                                        <span style="opacity: 0.7; font-style: italic;">Formar profesionales íntegros, competitivos y con alto sentido humanista, capaces de transformar su entorno social.</span>
                                    @endif
                                </div>
                            </div>

                            <div class="det-mv-card det-mv-vision det-anim">
                                <h3 class="det-mv-title">
                                    <i class="fas fa-eye" style="color: var(--det-magenta);"></i> Visión
                                </h3>
                                <div class="det-text" style="font-size: 14px; line-height: 1.65;">
                                    @if($carrera->vision)
                                        {{ $carrera->vision }}
                                    @else
                                        <span style="opacity: 0.7; font-style: italic;">Ser referentes de excelencia e innovación académica, destacando por el impacto positivo de nuestros egresados a nivel nacional.</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Objetivos Académicos -->
                        <div class="det-card det-anim">
                            <div class="det-card-accent-top" style="background: linear-gradient(90deg, #059669, #34d399);"></div>
                            <h2 class="det-section-title">
                                <span class="det-icon det-icon-green"><i class="fas fa-list-check"></i></span>
                                Objetivos Académicos
                            </h2>
                            <div class="det-text">
                                @if($carrera->objetivos && is_array($carrera->objetivos) && count($carrera->objetivos) > 0)
                                    @foreach($carrera->objetivos as $index => $objetivo)
                                        <div class="det-objetivo-item">
                                            <span class="det-objetivo-num">{{ $index + 1 }}</span>
                                            <span>{{ $objetivo }}</span>
                                        </div>
                                    @endforeach
                                @elseif($carrera->objetivos && is_string($carrera->objetivos))
                                    {!! nl2br(e($carrera->objetivos)) !!}
                                @else
                                    <div class="det-objetivo-item" style="opacity: 0.8;">
                                        <span class="det-objetivo-num">1</span>
                                        <span style="font-style: italic;">Brindar formación científica, tecnológica y humanista de alta calidad.</span>
                                    </div>
                                    <div class="det-objetivo-item" style="opacity: 0.8;">
                                        <span class="det-objetivo-num">2</span>
                                        <span style="font-style: italic;">Fomentar la investigación para resolver problemas del entorno regional.</span>
                                    </div>
                                    <div class="det-objetivo-item" style="opacity: 0.8;">
                                        <span class="det-objetivo-num">3</span>
                                        <span style="font-style: italic;">Promover la responsabilidad y compromiso social de nuestros estudiantes.</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Campo Laboral -->
                        <div class="det-card det-anim">
                            <div class="det-card-accent-top" style="background: linear-gradient(90deg, #0d2838, #1f3e76);"></div>
                            <h2 class="det-section-title">
                                <span class="det-icon det-icon-azul"><i class="fas fa-briefcase"></i></span>
                                Campo Laboral
                            </h2>
                            <div class="det-laboral-grid">
                                @if($carrera->campo_laboral && is_array($carrera->campo_laboral) && count($carrera->campo_laboral) > 0)
                                    @foreach($carrera->campo_laboral as $campo)
                                        <div class="det-laboral-item">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="det-text" style="font-size: 14px;">{{ $campo }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="det-laboral-item" style="opacity: 0.8;">
                                        <i class="fas fa-building"></i>
                                        <span class="det-text" style="font-size: 14px; font-style: italic;">Instituciones públicas y empresas privadas del sector a nivel nacional e internacional.</span>
                                    </div>
                                    <div class="det-laboral-item" style="opacity: 0.8;">
                                        <i class="fas fa-laptop-house"></i>
                                        <span class="det-text" style="font-size: 14px; font-style: italic;">Consultoría independiente, dirección, formulación y evaluación de proyectos especializados.</span>
                                    </div>
                                    <div class="det-laboral-item" style="opacity: 0.8;">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <span class="det-text" style="font-size: 14px; font-style: italic;">Docencia e investigación científica en el sistema de educación superior.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ======================== -->
                    <!-- Sidebar                   -->
                    <!-- ======================== -->
                    <div class="det-sidebar">

                        <!-- Info rápida -->
                        <div class="det-info-card det-anim">
                            <h3 style="font-size: 16px; font-weight: 800; color: var(--det-azul); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-info-circle" style="color: var(--det-cyan);"></i> Información Rápida
                            </h3>
                            <div class="det-info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><strong>Sede:</strong> Puerto Maldonado</span>
                            </div>
                            <div class="det-info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><strong>Modalidad:</strong> Presencial</span>
                            </div>
                            <div class="det-info-item">
                                <i class="fas fa-clock"></i>
                                <span><strong>Duración:</strong> {{ $carrera->duracion ?? '10 Semestres' }}</span>
                            </div>
                        </div>

                        <!-- Plan de Estudios PDF -->
                        @if($carrera->malla_url)
                        <div class="det-sidebar-card det-anim">
                            <div class="det-pdf-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <h3 style="font-size: 18px; font-weight: 800; color: var(--det-azul); margin-bottom: 8px;">Plan de Estudios</h3>
                            <p class="det-text" style="font-size: 13px; margin-bottom: 18px;">Descarga la currícula oficial completa de la carrera.</p>
                            <a href="{{ $carrera->malla_url }}" target="_blank" class="det-btn-download">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                        @endif

                        <!-- CTA -->
                        <div class="det-cta-card det-anim">
                            <div style="position: relative; z-index: 2;">
                                <i class="fas fa-rocket" style="font-size: 40px; margin-bottom: 15px; opacity: 0.8; color: #00d4aa;"></i>
                                <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 10px; color: white; text-shadow: 0 2px 8px rgba(0,0,0,0.3);">Prepárate con los Mejores</h3>
                                <p style="opacity: 0.9; font-size: 14px; line-height: 1.5; color: rgba(255,255,255,0.95);">Inicia tu camino hacia la UNAMAD con nuestra preparación especializada.</p>
                                <a href="{{ route('public.vacantes') }}" class="det-btn-cta">
                                    <i class="fas fa-arrow-right"></i> VER VACANTES
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.det-anim').forEach((el) => {
                observer.observe(el);
            });
        });
    </script>

    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection
