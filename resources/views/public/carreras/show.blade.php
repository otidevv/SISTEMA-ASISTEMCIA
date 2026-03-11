@extends('layouts.cepre')

@section('title', $carrera->nombre . ' | CEPRE UNAMAD')

@section('content')
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    <!-- Estilos específicos para la página de detalle -->
    <style>
        :root {
            --azul-primario: #1f3e76;
            --azul-oscuro: #0d2838;
            --magenta-unamad: #e2006a;
            --cyan-acento: #00a19a;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--azul-primario), #1a1a4a);
            padding: 120px 0 100px;
            color: white;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .relative { position: relative; z-index: 2; }
        .kene-pattern-overlay { opacity: 0.1; }

        .back-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 15px;
            margin-bottom: 25px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: white;
            transform: translateX(-5px);
        }

        .career-logo-wrapper {
            background: white;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px auto;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            padding: 20px;
            border: 4px solid rgba(255,255,255,0.1);
        }

        .career-logo-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .career-title {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.2;
            color: white !important;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .career-badge {
            display: inline-block;
            background: var(--magenta-unamad);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(226, 0, 106, 0.4);
        }

        /* Stats Bar */
        .stats-bar-section { margin-top: -40px; margin-bottom: 50px; position: relative; z-index: 10; }
        
        .stats-bar {
            background: white;
            border-radius: 16px;
            padding: 30px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            max-width: 1100px;
            margin: 0 auto;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            min-width: 200px;
        }

        .stat-item i {
            font-size: 28px;
            color: var(--cyan-acento);
            background: rgba(0, 161, 154, 0.1);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .stat-label {
            display: block;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }

        .stat-value {
            display: block;
            font-size: 16px;
            color: var(--azul-oscuro);
            font-weight: 700;
        }

        /* Layout Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            padding-bottom: 60px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--azul-oscuro);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .section-title i {
            color: var(--cyan-acento);
            margin-right: 12px;
        }

        .description-text {
            font-size: 16px;
            line-height: 1.8;
            color: #475569;
        }

        /* Laboral List */
        .laboral-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .laboral-item {
            display: flex;
            gap: 12px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 10px;
            align-items: center;
        }

        .laboral-item i { color: var(--cyan-acento); }

        /* Sidebar */
        .sidebar-card {
            padding: 30px;
            text-align: center;
        }

        .btn-malla {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--azul-oscuro);
            color: white;
            font-weight: 700;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-malla:hover { background: var(--magenta-unamad); color: white; }

        .cta-card {
            background: linear-gradient(135deg, var(--azul-oscuro), var(--azul-primario));
            color: white;
            text-align: center;
            padding: 40px 30px;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
        }

        .btn-ingreso {
            display: inline-block;
            background: white;
            color: var(--azul-oscuro);
            font-weight: 800;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .btn-ingreso:hover { transform: scale(1.05); color: var(--magenta-unamad); }

        /* Animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .animate-on-scroll.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 991px) {
            .content-grid { grid-template-columns: 1fr; }
            .stats-bar { flex-direction: column; }
            .career-hero { padding: 100px 0 60px; }
        }
    </style>

    <!-- Hero Carrera -->
    <section class="hero-section">
        <div class="kene-pattern-overlay" style="opacity: 0.1;"></div>
        <div class="container relative">
            <a href="{{ route('public.carreras.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver a Carreras
            </a>

            <div class="career-logo-wrapper">
                @if($carrera->imagen_url)
                    <img src="{{ asset($carrera->imagen_url) }}" alt="{{ $carrera->nombre }}">
                @else
                    <i class="fas fa-graduation-cap" style="font-size: 50px; color: var(--cyan-acento);"></i>
                @endif
            </div>

            <h1 class="career-title">{{ $carrera->nombre }}</h1>
            <div class="career-badge">CARRERA PROFESIONAL DE LA UNAMAD</div>
        </div>
    </section>

    <!-- Stats Bar -->
    <section class="stats-bar-section">
        <div class="container">
            <div class="stats-bar animate-on-scroll">
                <div class="stat-item">
                    <i class="fas fa-certificate"></i>
                    <div>
                        <span class="stat-label">Grado Académico</span>
                        <span class="stat-value">{{ $carrera->grado ?? 'Bachiller' }}</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-user-tie"></i>
                    <div>
                        <span class="stat-label">Título Profesional</span>
                        <span class="stat-value">{{ $carrera->titulo ?? 'Licenciado/Ingeniero' }}</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <span class="stat-label">Duración</span>
                        <span class="stat-value">{{ $carrera->duracion ?? '10 Semestres' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Divisor oficial de la página -->
    <div class="torn-paper-edge"></div>

    <!-- Content Grid con el patrón oficial de cuaderno -->
    <section class="courses-section academic-notebook-pattern" style="padding: 60px 0;">
        <main class="relative">
            <div class="container">
                <div class="content-grid">
                <!-- Main Content -->
                <div class="main-content">
                    
                    <!-- Sobre la Carrera -->
                    <div class="card animate-on-scroll">
                        <h2 class="section-title"><i class="fas fa-info-circle"></i>Sobre la Carrera</h2>
                        <div class="description-text">
                            @if($carrera->descripcion)
                                {{ $carrera->descripcion }}
                            @else
                                <p class="text-muted" style="font-style: italic; opacity: 0.8; margin: 0;">
                                    La carrera profesional de <strong>{{ $carrera->nombre }}</strong> en la UNAMAD forma líderes altamente capacitados, competitivos y con sólidos valores éticos, preparados para contribuir al desarrollo integral y sostenible de la región Amazónica y el país.
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Perfil del Egresado -->
                    <div class="card animate-on-scroll">
                        <h2 class="section-title"><i class="fas fa-user-graduate"></i>Perfil del Egresado</h2>
                        <div class="description-text">
                            @if($carrera->perfil)
                                {{ $carrera->perfil }}
                            @else
                                <p class="text-muted" style="font-style: italic; opacity: 0.8; margin: 0;">
                                    El egresado posee una destacada formación científica, tecnológica y humanística. Ha desarrollado competencias para investigar, innovar y liderar soluciones complejas en su área de especialidad.
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Misión y Visión (Grid Premium) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;" class="mision-vision-grid">
                        <div class="card animate-on-scroll" style="margin-bottom: 0; background: linear-gradient(135deg, #ffffff, #f4fcsc); border-left: 4px solid var(--cyan-acento);">
                            <h2 class="section-title" style="font-size: 18px;"><i class="fas fa-bullseye"></i>Misión</h2>
                            <div class="description-text" style="font-size: 14px; line-height: 1.6;">
                                @if($carrera->mision)
                                    {{ $carrera->mision }}
                                @else
                                    <span style="opacity: 0.7; font-style: italic;">Formar profesionales íntegros, competitivos y con alto sentido humanista, capaces de transformar su entorno social.</span>
                                @endif
                            </div>
                        </div>

                        <div class="card animate-on-scroll" style="margin-bottom: 0; background: linear-gradient(135deg, #ffffff, #fff5f9); border-left: 4px solid var(--magenta-unamad);">
                            <h2 class="section-title" style="font-size: 18px;"><i class="fas fa-eye"></i>Visión</h2>
                            <div class="description-text" style="font-size: 14px; line-height: 1.6;">
                                @if($carrera->vision)
                                    {{ $carrera->vision }}
                                @else
                                    <span style="opacity: 0.7; font-style: italic;">Ser referentes de excelencia e innovación académica, destacando por el impacto positivo de nuestros egresados a nivel nacional.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Objetivos -->
                    <div class="card animate-on-scroll">
                        <h2 class="section-title"><i class="fas fa-list-check"></i>Objetivos Académicos</h2>
                        <div class="description-text">
                            @if($carrera->objetivos && is_array($carrera->objetivos) && count($carrera->objetivos) > 0)
                                <ul class="list-unstyled" style="padding-left: 0; margin-bottom: 0;">
                                    @foreach($carrera->objetivos as $objetivo)
                                        <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 10px;">
                                            <i class="fas fa-check-circle" style="color: var(--cyan-acento); margin-top: 4px;"></i>
                                            <span>{{ $objetivo }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif($carrera->objetivos && is_string($carrera->objetivos))
                                {!! nl2br(e($carrera->objetivos)) !!}
                            @else
                                <ul class="list-unstyled" style="padding-left: 0; margin-bottom: 0; opacity: 0.8;">
                                    <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 10px;">
                                        <i class="fas fa-check-circle" style="color: var(--cyan-acento); margin-top: 4px;"></i>
                                        <span style="font-style: italic;">Brindar formación científica, tecnológica y humanista de alta calidad.</span>
                                    </li>
                                    <li style="margin-bottom: 12px; display: flex; align-items: flex-start; gap: 10px;">
                                        <i class="fas fa-check-circle" style="color: var(--cyan-acento); margin-top: 4px;"></i>
                                        <span style="font-style: italic;">Fomentar la investigación para resolver problemas del entorno regional.</span>
                                    </li>
                                    <li style="display: flex; align-items: flex-start; gap: 10px;">
                                        <i class="fas fa-check-circle" style="color: var(--cyan-acento); margin-top: 4px;"></i>
                                        <span style="font-style: italic;">Promover la responsabilidad y compromiso social de nuestros estudiantes.</span>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </div>

                    <!-- Campo Laboral -->
                    <div class="card animate-on-scroll">
                        <h2 class="section-title"><i class="fas fa-briefcase"></i>Campo Laboral</h2>
                        <div class="laboral-grid">
                            @if($carrera->campo_laboral && is_array($carrera->campo_laboral) && count($carrera->campo_laboral) > 0)
                                @foreach($carrera->campo_laboral as $campo)
                                    <div class="laboral-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="description-text" style="font-size: 15px;">{{ $campo }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="laboral-item" style="opacity: 0.8;">
                                    <i class="fas fa-building"></i>
                                    <span class="description-text" style="font-size: 15px; font-style: italic;">Instituciones públicas y empresas privadas del sector a nivel nacional e internacional.</span>
                                </div>
                                <div class="laboral-item" style="opacity: 0.8;">
                                    <i class="fas fa-laptop-house"></i>
                                    <span class="description-text" style="font-size: 15px; font-style: italic;">Consultoría independiente, dirección, formulación y evaluación de proyectos especializados.</span>
                                </div>
                                <div class="laboral-item" style="opacity: 0.8;">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <span class="description-text" style="font-size: 15px; font-style: italic;">Docencia e investigación científica en el sistema de educación superior.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="sidebar-column">
                    @if($carrera->malla_url)
                    <div class="card sidebar-card animate-on-scroll">
                        <i class="fas fa-file-pdf" style="font-size: 50px; color: #cbd5e1; margin-bottom: 20px;"></i>
                        <h3 style="font-size: 20px; font-weight: 800; color: var(--azul-oscuro); margin-bottom: 15px;">Plan de Estudios</h3>
                        <p class="description-text" style="font-size: 14px; margin-bottom: 20px;">Descarga la currícula oficial completa de la carrera.</p>
                        <a href="{{ $carrera->malla_url }}" target="_blank" class="btn-malla">
                            <i class="fas fa-download"></i> Descargar PDF
                        </a>
                    </div>
                    @endif

                    <div class="cta-card animate-on-scroll">
                        <i class="fas fa-university" style="font-size: 50px; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3 style="font-size: 24px; font-weight: 800; margin-bottom: 15px;">Prepárate con los Mejores</h3>
                        <p style="opacity: 0.8; font-size: 15px;">Inicia tu camino hacia la UNAMAD con nuestra preparación especializada.</p>
                        <a href="{{ route('public.vacantes') }}" class="btn-ingreso">VER VACANTES</a>
                    </div>
                </div>
            </div> <!-- Closes .content-grid -->
            </div> <!-- Closes .container -->
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

            document.querySelectorAll('.animate-on-scroll').forEach((el) => {
                observer.observe(el);
            });
        });
    </script>
 
    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection
