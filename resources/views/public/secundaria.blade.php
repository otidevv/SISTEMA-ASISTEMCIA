@extends('layouts.cepre')

@section('title', 'Nivel Secundaria | CEPRE UNAMAD - Inicia tu preparación')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/style.css') }}">
    
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --cepre-magenta: #ec008c;
            --cepre-cyan: #00aeef;
            --cepre-dark: #0c1e2f;
        }

        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, .hero-title, .hero-badge { font-family: 'Sora', sans-serif; }

        /* ===== PRELOADER Glassmorphism (Specific for Secundaria) ===== */
        .preloader {
            background: rgba(12, 30, 47, 0.85) !important;
            backdrop-filter: blur(15px);
        }
        .preloader .edu-preloader-icon::before {
            border-top: 2px solid var(--cepre-magenta);
            border-right: 2px solid var(--cepre-cyan);
        }

        /* ===== HERO SECTION SECUNDARIA ===== */
        .hero-secundaria {
            position: relative;
            padding: 160px 0 100px;
            background: linear-gradient(135deg, #0c1e2f 0%, #1a3a5a 100%);
            overflow: hidden;
            color: white;
        }
        .hero-img-container {
            position: relative;
            z-index: 5;
        }
        .hero-img-container img {
            border: 10px solid white;
            border-radius: 30px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.3);
            background: white;
            transition: transform 0.3s ease;
        }
        .hero-img-container img:hover {
            transform: scale(1.02) rotate(1deg);
        }
        .hero-secundaria::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 50%; height: 100%;
            background: radial-gradient(circle at 70% 30%, rgba(236, 0, 140, 0.15) 0%, transparent 70%);
            z-index: 1;
        }
        .hero-secundaria::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 50%; height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(0, 174, 239, 0.1) 0%, transparent 70%);
            z-index: 1;
        }

        .hero-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(236, 0, 140, 0.15);
            border: 1px solid var(--cepre-magenta);
            color: var(--cepre-magenta);
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 10;
        }

        .floating-icon {
            position: absolute;
            color: rgba(255, 255, 255, 0.08);
            font-size: 50px;
            pointer-events: none;
            z-index: 2;
            animation: float-slow 6s ease-in-out infinite;
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0) rotate(0) scale(1); }
            50% { transform: translateY(-30px) rotate(15deg) scale(1.1); }
        }

        .hero-title {
            color: white !important;
            font-size: 64px;
            font-weight: 850;
            line-height: 1.05;
            margin-bottom: 25px;
            position: relative;
            z-index: 10;
            text-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .hero-title span {
            color: var(--cepre-magenta);
            display: block;
        }

        .hero-desc {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 40px;
            max-width: 600px;
        }

        /* ===== INFO CARDS (Flyer 1) ===== */
        .info-grid {
            margin-top: 60px;
            position: relative;
            z-index: 5;
        }
        .info-card {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            height: 100%;
            border-top: 4px solid var(--cepre-cyan);
            transition: transform 0.3s ease;
        }
        .info-card:hover { transform: translateY(-10px); }
        .info-card.magenta { border-top-color: var(--cepre-magenta); }
        
        .info-card h4 {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 20px;
            color: var(--cepre-dark);
        }
        .info-list { list-style: none; padding: 0; margin: 0; }
        .info-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            font-size: 15px;
        }
        .info-list li i {
            color: var(--cepre-cyan);
            margin-right: 12px;
            font-size: 18px;
        }
        .info-card.magenta .info-list li i { color: var(--cepre-magenta); }

        /* ===== CURSOS SECTION (Flyer 2) ===== */
        .section-padding { padding: 100px 0; }
        .bg-gradient-soft { background: #f8fafc; }
        
        .course-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }
        .course-card:hover {
            border-color: var(--cepre-magenta);
            background: var(--cepre-magenta);
            color: white;
        }
        .course-card i {
            font-size: 40px;
            color: var(--cepre-magenta);
            margin-bottom: 20px;
            display: block;
        }
        .course-card:hover i { color: white; }
        .course-card h5 { font-weight: 700; margin: 0; }

        /* ===== REFORZAMIENTO PLUS ===== */
        .reforzamiento-cta {
            background: var(--cepre-dark);
            border-radius: 30px;
            padding: 60px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .reforzamiento-cta::before {
            content: 'PLUS';
            position: absolute;
            top: -20px; right: -20px;
            font-size: 120px;
            font-weight: 900;
            color: rgba(255,255,255,0.05);
        }

        .btn-premium {
            padding: 15px 35px;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            display: inline-block;
            border: none;
        }
        .btn-magenta {
            background: var(--cepre-magenta);
            color: white;
            box-shadow: 0 10px 25px rgba(236, 0, 140, 0.3);
        }
        .btn-magenta:hover {
            transform: scale(1.05);
            background: #d4007d;
            color: white;
        }

        /* ===== FAQ / PREGUNTAS FRECUENTES ===== */
        .faq-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            border: 1px solid #efefef;
            overflow: hidden;
            transition: all 0.3s;
        }
        .faq-card:hover { border-color: var(--cepre-magenta); }
        .faq-header {
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            color: var(--cepre-dark);
        }
        .faq-body {
            padding: 0 25px 20px;
            display: none;
            color: #666;
            font-size: 14px;
        }
        .faq-card.active .faq-body { display: block; }
        .faq-card.active .faq-header { color: var(--cepre-magenta); }
        .faq-header i { transition: transform 0.3s; }
        .faq-card.active .faq-header i { transform: rotate(180deg); }


        @media (max-width: 768px) {
            .hero-title { font-size: 38px; }
            .info-grid { margin-top: 20px; }
            .reforzamiento-cta { padding: 40px 20px; }
        }
        /* ===== RESPONSIVE POLISH ===== */
        @media (max-width: 768px) {
            .hero-secundaria { padding: 120px 0 60px; text-align: center; }
            .hero-title { font-size: 42px; }
            .hero-desc { margin: 0 auto 30px; }
            .section-padding { padding: 60px 0; }
            .info-card { padding: 25px; }
            .stats-secundaria-bar { padding: 30px 0; }
            .torn-paper-edge { height: 20px; }
            /* Reduce pattern opacity for better legibility on mobile */
            .kene-pattern-overlay, .kene-pattern-overlay-2 { opacity: 0.03 !important; }
            .academic-notebook-pattern::before { left: 20px; }
        }
    </style>
@endpush

@section('content')
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    <!-- SECCIÓN HERO -->
    <section class="hero-secundaria">
        <!-- Partículas 3D -->
        <div id="hero-particles" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; opacity: 0.6; pointer-events: none;"></div>
        
        <!-- Decoradores Flotantes -->
        <i class="fas fa-book floating-icon" style="top: 15%; left: 5%; animation-delay: 0s;"></i>
        <i class="fas fa-pencil-alt floating-icon" style="top: 60%; left: 15%; animation-delay: 1s; font-size: 30px;"></i>
        <i class="fas fa-atom floating-icon" style="top: 25%; right: 10%; animation-delay: 2s; font-size: 45px;"></i>
        <i class="fas fa-microscope floating-icon" style="bottom: 15%; right: 25%; animation-delay: 3s; font-size: 35px;"></i>

        <div class="container position-relative" style="z-index: 10;">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="hero-badge wow fadeInUp">Nivel 1° a 5° de Secundaria</div>
                    <h1 class="hero-title wow fadeInUp" data-wow-delay="0.2s">
                        Inicia tu preparación <span>desde el colegio</span>
                    </h1>
                    <p class="hero-desc wow fadeInUp" data-wow-delay="0.4s">
                        Asegura tu ingreso directo a la UNAMAD. Te preparamos con el mejor nivel académico 
                        mientras terminas tus estudios secundarios. ¡El futuro comienza hoy!
                    </p>
                    <div class="hero-btns wow fadeInUp" data-wow-delay="0.6s">
                        <button class="btn-premium btn-magenta btn-postulacion-main" onclick="openReforzamientoModal()">
                            <i class="fas fa-edit mr-2"></i> ¡INSCRIBIRSE AHORA!
                        </button>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="hero-img-container wow zoomIn">
                        <img src="{{ asset('assets_cepre/img/portada/estudiantes_secundaria_hero.png') }}" alt="Estudiantes Secundaria CEPRE" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BARRA DE ESTADÍSTICAS -->
    <section class="stats-secundaria-bar py-5" style="background: white; border-bottom: 1px solid #efefef; position: relative; z-index: 20; overflow: hidden;">
        <div class="kene-pattern-overlay" style="opacity: 0.05;"></div>
        <div class="container" style="position: relative; z-index: 2;">
            <div class="row text-center">
                @foreach($stats_secundaria as $stat)
                    <div class="col-6 col-md-3 mb-4 mb-md-0">
                        <div class="stat-item wow zoomIn">
                            <div class="d-flex align-items-center justify-content-center">
                                <h2 class="odometer h1 mb-0 font-weight-bold" data-value="{{ $stat['value'] }}">0</h2>
                                <span class="h2 mb-0 ml-1" style="color: var(--cepre-magenta); font-weight: 800;">{{ $stat['suffix'] }}</span>
                            </div>
                            <p class="text-muted font-weight-bold mb-0 text-uppercase small" style="letter-spacing: 1px;">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- INFO CARDS (Flyer 1) -->
    <section class="section-padding" style="background: radial-gradient(circle at 10% 20%, rgba(236, 0, 140, 0.03) 0%, transparent 40%), radial-gradient(circle at 90% 80%, rgba(0, 174, 239, 0.03) 0%, transparent 40%);">
        <div class="container">
        <div class="row info-grid">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="info-card wow fadeInUp">
                    <h4><i class="fas fa-clock mr-2"></i> Horarios Disponibles</h4>
                    <p class="text-muted small">Lunes a Viernes</p>
                    <ul class="info-list">
                        <li><i class="fas fa-sun"></i> <strong>Turno Mañana:</strong> 8:00 A.M. - 11:00 A.M.</li>
                        <li><i class="fas fa-moon"></i> <strong>Turno Tarde:</strong> 4:00 P.M. - 7:00 P.M.</li>
                    </ul>
                    <div class="mt-4 pt-3 border-top">
                        <span class="h3 font-weight-bold text-dark">S/ 200.00</span>
                        <span class="text-muted">/ costo mensual</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="info-card magenta wow fadeInUp" data-wow-delay="0.2s">
                    <h4><i class="fas fa-file-invoice mr-2"></i> Requisitos</h4>
                    <ul class="info-list">
                        <li><i class="fas fa-check-circle"></i> Copia de DNI del Estudiante</li>
                        <li><i class="fas fa-check-circle"></i> Copia de DNI del Apoderado</li>
                        <li><i class="fas fa-check-circle"></i> Voucher de pago (Original y Copia)</li>
                        <li><i class="fas fa-check-circle"></i> 2 Fotos tamaño carnet</li>
                        <li><i class="fas fa-check-circle"></i> Traer mica transparente</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-12 col-lg-4 mb-4">
                <div class="info-card wow fadeInUp" data-wow-delay="0.4s">
                    <h4><i class="fas fa-map-marker-alt mr-2"></i> Inscripciones</h4>
                    <p class="mb-2"><strong>Oficina CEPRE UNAMAD</strong></p>
                    <p class="text-muted small">Av. Dos de Mayo N° 960 (Segundo Piso)</p>
                    <hr>
                    <p class="mb-1"><strong>Lugar de Pago:</strong></p>
                    <p class="text-muted small">Caja de la Av. Dos de Mayo (Primer Piso)</p>
                    <div class="mt-4">
                        <a href="tel:993110927" class="text-dark font-weight-bold"><i class="fab fa-whatsapp text-success mr-1"></i> 993 110 927</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="torn-paper-edge"></div>

    <!-- BENEFICIOS / POR QUÉ ELEGIRNOS -->
    <section class="section-padding academic-notebook-pattern">
        <div class="container">
            <div class="section-title text-center mb-50">
                <span class="hero-badge">¿Por qué elegirnos?</span>
                <h2 class="h1 font-weight-bold">Ventajas de nuestra preparación</h2>
                <p class="text-muted">Inicia tu camino universitario con una base sólida y mentoría experta.</p>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="info-card wow fadeInUp">
                        <i class="fas fa-chalkboard-teacher mb-3 d-block h2" style="color: var(--cepre-cyan)"></i>
                        <h4>Plana Docente Selecta</h4>
                        <p class="text-muted" style="font-size: 14px;">Contamos con catedráticos de la UNAMAD y especialistas en preparación preuniversitaria para adolescentes.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="info-card magenta wow fadeInUp" data-wow-delay="0.2s">
                        <i class="fas fa-laptop-code mb-3 d-block h2"></i>
                        <h4>Recursos Digitales</h4>
                        <p class="text-muted" style="font-size: 14px;">Acceso a materiales exclusivos, simulacros tipo admisión y asesoría continua en nuestra plataforma.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="info-card wow fadeInUp" data-wow-delay="0.4s">
                        <i class="fas fa-university mb-3 d-block h2" style="color: var(--cepre-cyan)"></i>
                        <h4>Ingreso Directo</h4>
                        <p class="text-muted" style="font-size: 14px;">Preparamos tu base académica desde el colegio para asegurar tu vacante mediante la modalidad de examen CEPRE.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="torn-paper-edge" style="transform: scaleY(-1); margin-top: -1px; margin-bottom: -15px;"></div>

    <!-- CURSOS DE REFORZAMIENTO (Flyer 2) -->
    <section class="section-padding bg-gradient-soft" style="position: relative; overflow: hidden;">
        <div class="kene-pattern-overlay-2" style="opacity: 0.04;"></div>
        <div class="container" style="position: relative; z-index: 2;">
            <div class="section-title text-center mb-50">
                <span class="hero-badge">Reforzamiento Escolar</span>
                <h2 class="h1 font-weight-bold">Potencia tus habilidades</h2>
                <p class="text-muted">Contamos con los mejores docentes para ayudarte en las materias clave.</p>
            </div>
            <div class="row">
                @foreach ($cursos_reforzamiento as $curso)
                    <div class="col-md-4 col-sm-6 mb-30">
                        <div class="course-card wow fadeInUp">
                            <i class="fas {{ $curso['icono'] }}"></i>
                            <h5>{{ $curso['nombre'] }}</h5>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- SECCIÓN FAQ -->
    <section class="section-padding" style="background: #fdfdfd; position: relative; overflow: hidden;">
        <div class="kene-pattern-overlay" style="opacity: 0.03;"></div>
        <div class="container" style="position: relative; z-index: 2;">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-5 mb-lg-0">
                    <span class="hero-badge">FAQ</span>
                    <h2 class="h1 font-weight-bold mb-4">Preguntas Frecuentes</h2>
                    <p class="text-muted">Si tienes más dudas, nuestro equipo de soporte está listo para ayudarte a través de WhatsApp o llamada telefónica.</p>
                    <a href="https://wa.me/51993110927" target="_blank" class="btn-premium btn-cyan mt-3">
                        <i class="fab fa-whatsapp mr-2"></i> CONSULTA POR WHATSAPP
                    </a>
                </div>
                <div class="col-lg-7">
                    <div class="faq-container">
                        <div class="faq-card active">
                            <div class="faq-header" onclick="this.parentElement.classList.toggle('active')">
                                ¿En qué horarios son las clases? 
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-body">
                                Contamos con turnos mañana (8:00 A.M. - 11:00 A.M.) y tarde (4:00 P.M. - 7:00 P.M.), adaptándonos a la jornada escolar del estudiante.
                            </div>
                        </div>
                        <div class="faq-card">
                            <div class="faq-header" onclick="this.parentElement.classList.toggle('active')">
                                ¿Qué requisitos necesito para inscribirme?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-body">
                                Solo necesitas la copia de DNI del estudiante y del apoderado, el voucher de pago y 2 fotos tamaño carnet. ¡El proceso es 100% asistido!
                            </div>
                        </div>
                        <div class="faq-card">
                            <div class="faq-header" onclick="this.parentElement.classList.toggle('active')">
                                ¿Brindan material de estudio?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-body">
                                Sí, entregamos guías académicas exclusivas por curso y acceso a nuestra plataforma virtual con simulacros y recursos adicionales.
                            </div>
                        </div>
                        <div class="faq-card">
                            <div class="faq-header" onclick="this.parentElement.classList.toggle('active')">
                                ¿Cómo ayuda este programa al ingreso directo?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-body">
                                Al reforzar las bases desde el colegio, el estudiante llega con una ventaja competitiva al ciclo ordinario de la UNAMAD, donde se otorgan las vacantes de ingreso directo.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- REFORZAMIENTO PLUS CTA -->
    <section class="section-padding">
        <div class="container">
            <div class="reforzamiento-cta wow zoomIn">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="h1 font-weight-bold mb-3">¿Dificultades con las tareas?</h2>
                        <p class="lead mb-4">
                            ¡Nosotros te ayudamos a darle solución! Nuestro programa incluye 
                            <strong>Asesoramiento en resolución de tareas escolares</strong> para que 
                            destaques en tu colegio.
                        </p>
                        <ul class="list-unstyled mb-4">
                            <li><i class="fas fa-plus-circle text-info mr-2"></i> Prepárate para las carreras más demandadas.</li>
                            <li><i class="fas fa-plus-circle text-info mr-2"></i> Plus de asesoramiento personalizado.</li>
                        </ul>
                        <button class="btn-premium btn-magenta" onclick="openReforzamientoModal()">
                            Quiero inscribirme ahora
                        </button>
                    </div>
                    <div class="col-lg-4 text-center mt-4 mt-lg-0">
                        <div class="plus-img-container wow fadeInRight">
                             <img src="{{ asset('assets_cepre/img/portada/estudiantes_secundaria_working.png') }}" alt="Trabajo en equipo" class="img-fluid rounded-circle shadow-lg" style="width: 200px; height: 200px; object-fit: cover; border: 5px solid white;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- INCLUIR MODALES Y SCRIPTS -->
    @include('partials.reforzamiento-modal')
    @include('partials.cepre.countdown-widget')
    
    @include('partials.cepre.footer')

    <!-- Scroll to Top Button -->
    <button id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Inyectar departamentos y URL base
        window.DEPARTAMENTOS_INICIALES = @json($departamentos ?? []);
        window.APP_URL = "{{ url('/') }}";

        /**
         * Inicialización de Three.js Particles para el Hero de Secundaria
         */
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('hero-particles');
            if (!container) return;

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
            
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);

            const particleCount = 1500;
            const geometry = new THREE.BufferGeometry();
            const positions = [];
            const colors = [];
            
            const color1 = new THREE.Color(0xec008c); // Magenta
            const color2 = new THREE.Color(0x00aeef); // Cyan
            
            for (let i = 0; i < particleCount; i++) {
                positions.push((Math.random() - 0.5) * 15, (Math.random() - 0.5) * 15, (Math.random() - 0.5) * 15);
                const color = (Math.random() > 0.5) ? color1 : color2;
                colors.push(color.r, color.g, color.b);
            }
            
            geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
            geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));

            const material = new THREE.PointsMaterial({
                size: 0.08,
                vertexColors: true,
                transparent: true,
                opacity: 0.6,
                blending: THREE.AdditiveBlending
            });

            const particles = new THREE.Points(geometry, material);
            scene.add(particles);

            camera.position.z = 5;

            function animate() {
                requestAnimationFrame(animate);
                particles.rotation.x += 0.0003;
                particles.rotation.y += 0.0008;
                renderer.render(scene, camera);
            }

            animate();

            window.addEventListener('resize', () => {
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            });

            // Inicializar Odómetros cuando entren en el viewport
            const initOdometers = () => {
                const odometers = document.querySelectorAll('.odometer');
                odometers.forEach(od => {
                    const value = od.getAttribute('data-value');
                    setTimeout(() => {
                        od.innerHTML = value;
                    }, 500);
                });
            };

            // Usar IntersectionObserver o simplemente un timeout
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            initOdometers();
                            observer.disconnect();
                        }
                    });
                }, { threshold: 0.5 });
                const statsSection = document.querySelector('.stats-secundaria-bar');
                if (statsSection) observer.observe(statsSection);
            } else {
                setTimeout(initOdometers, 1000);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/js/reforzamiento/publico-modal.js'])
    @include('partials.cepre.scripts')
@endsection
