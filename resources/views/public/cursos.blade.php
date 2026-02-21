@extends('layouts.cepre')

@section('title', 'Nuestros Cursos')

@section('content')
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    @php
        // Mapeo dinámico de iconos según el nombre del curso
        function getCourseIcon($nombre) {
            $nombre = strtolower($nombre);
            if (str_contains($nombre, 'álgebra') || str_contains($nombre, 'arit') || str_contains($nombre, 'mate')) return 'fa-calculator';
            if (str_contains($nombre, 'geom') || str_contains($nombre, 'trig')) return 'fa-ruler-combined';
            if (str_contains($nombre, 'físic')) return 'fa-atom';
            if (str_contains($nombre, 'quím')) return 'fa-flask';
            if (str_contains($nombre, 'biol')) return 'fa-dna';
            if (str_contains($nombre, 'lengu') || str_contains($nombre, 'liter') || str_contains($nombre, 'verbal')) return 'fa-book-open';
            if (str_contains($nombre, 'hist') || str_contains($nombre, 'geo')) return 'fa-globe-americas';
            if (str_contains($nombre, 'psic') || str_contains($nombre, 'filo')) return 'fa-brain';
            if (str_contains($nombre, 'cívic') || str_contains($nombre, 'econ')) return 'fa-balance-scale';
            return 'fa-graduation-cap';
        }

        // Colores temáticos dinámicos (Paleta Institucional Refinada)
        function getCourseColor($index) {
            $colors = ['var(--verde-cepre)', 'var(--magenta-unamad)', 'var(--cyan-acento)', 'var(--azul-oscuro)'];
            return $colors[$index % count($colors)];
        }
    @endphp

    <!-- Hero Cursos Premium -->
    <section class="hero-section" style="min-height: 280px; background: linear-gradient(135deg, var(--azul-oscuro) 0%, #1a3a4a 100%);">
        <div class="kene-pattern-overlay" style="opacity: 0.15;"></div>
        <div class="container" style="max-width: 1100px; margin: 0 auto; padding: 50px 20px; position: relative; z-index: 2; text-align: center; color: white;">
            <div style="display: inline-block; padding: 5px 15px; background: rgba(255,255,255,0.1); border-radius: 50px; backdrop-filter: blur(5px); margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.1);">
                <span style="font-size: 13px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--cyan-acento);">Nuestra Oferta Académica</span>
            </div>
            <h1 class="animate-on-scroll animated" style="font-size: 52px; font-weight: 850; margin-bottom: 15px; line-height: 1.1;">Formación de <span style="text-primary-gradient; color: var(--verde-cepre);">Excelencia</span></h1>
            <p class="animate-on-scroll animated" style="font-size: 19px; opacity: 0.85; max-width: 750px; margin: 0 auto; line-height: 1.6;">Desarrolla tus habilidades con los cursos más completos diseñados para el ingreso directo.</p>
        </div>
    </section>

    <!-- Divisor Estilo Institucional -->
    <div class="torn-paper-edge" style="background: white;"></div>

    <!-- Seccion de Cursos "Premium Institucional" -->
    <section class="courses-section academic-notebook-pattern" id="cursos" style="padding: 90px 0; background: #fff; position: relative; overflow: hidden;">
        <div class="kene-pattern-overlay" style="opacity: 0.12; pointer-events: none;"></div>
        
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 2;">
            
            <div class="section-title" style="text-align: center; margin-bottom: 70px;">
                <h6 style="color: var(--magenta-unamad); font-size: 14px; font-weight: 800; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 12px; display: block;">NUESTROS CURSOS</h6>
                <h2 style="font-size: 42px; font-weight: 850; color: var(--azul-oscuro); margin: 0; position: relative; display: inline-block;">
                    Preparación Exclusiva en:
                    <span style="position: absolute; bottom: -15px; left: 50%; transform: translateX(-50%); width: 80px; height: 5px; background: linear-gradient(90deg, var(--verde-cepre), var(--cyan-acento)); border-radius: 5px;"></span>
                </h2>
            </div>

            <div class="courses-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 35px;">
                @foreach($cursos as $index => $curso)
                    @php 
                        $iconClass = getCourseIcon($curso->nombre); 
                        $nombreJs = json_encode($curso->nombre);
                        $descJs = json_encode($curso->descripcion ?? 'Formación académica de alto rendimiento con expertos en el examen de admisión UNAMAD.');
                    @endphp
                    <div class="course-card-premium animate-on-scroll" 
                         onclick='showModal("courseInfo", {{ $nombreJs }}, {{ $descJs }}, "{{ $iconClass }}")'
                         style="background: white; border-radius: 32px; padding: 45px 40px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 20px 40px rgba(0,0,0,0.02); transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden; display: flex; flex-direction: column; height: 100%;">
                        
                        <!-- Borde Gradiente Sutil (Pseudo-border) -->
                        <div class="card-border-glow" style="position: absolute; inset: 0; border-radius: 32px; padding: 2px; background: linear-gradient(135deg, {{ getCourseColor($index) }}44, transparent 50%); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; opacity: 0; transition: opacity 0.4s ease;"></div>
                        
                        <!-- Elemento decorativo de fondo -->
                        <div class="card-glow-bg" style="position: absolute; top: -10%; right: -10%; width: 140px; height: 140px; background: {{ getCourseColor($index) }}; opacity: 0.04; border-radius: 50%; filter: blur(40px); transition: all 0.6s ease;"></div>
                        
                        <div class="course-icon-container" style="position: relative; margin-bottom: 35px; width: 85px; height: 85px;">
                            <div class="icon-bg" style="position: absolute; inset: 0; background: {{ getCourseColor($index) }}15; border-radius: 24px; transform: rotate(-6deg); transition: all 0.4s ease;"></div>
                            <div class="course-icon-wrapper" style="position: relative; width: 100%; height: 100%; background: white; color: {{ getCourseColor($index) }}; display: flex; align-items: center; justify-content: center; border-radius: 24px; font-size: 34px; box-shadow: 0 10px 25px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.02); transition: all 0.4s ease;">
                                <i class="fas {{ $iconClass }}"></i>
                            </div>
                        </div>

                        <div class="course-content" style="flex-grow: 1; position: relative; z-index: 2;">
                            <h3 style="font-size: 26px; font-weight: 850; color: var(--azul-oscuro); margin-bottom: 15px; line-height: 1.2; letter-spacing: -0.02em;">{{ $curso->nombre }}</h3>
                            <p style="font-size: 15.5px; color: #526484; line-height: 1.7; margin-bottom: 30px; opacity: 0.85;">
                                {{ Str::limit($curso->descripcion ?? 'Formación académica de alto rendimiento con expertos en el examen de admisión UNAMAD.', 85) }}
                            </p>
                        </div>

                        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding-top: 30px; border-top: 2px solid #f8fafc; position: relative; z-index: 2;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <span style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Modalidad</span>
                                <span style="font-size: 14px; font-weight: 700; color: var(--azul-oscuro);">Presencial</span>
                            </div>
                            <div class="action-btn" style="padding: 10px 22px; border-radius: 14px; background: #f1f5f9; display: flex; align-items: center; gap: 10px; color: var(--azul-oscuro); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; cursor: pointer; font-weight: 800; font-size: 11px; white-space: nowrap;">
                                <span>VER MÁS</span>
                                <i class="fas fa-plus" style="font-size: 10px;"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($cursos->isEmpty())
                <div style="text-align: center; padding: 80px; background: white; border-radius: 40px; box-shadow: 0 25px 50px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.04); margin-top: 40px;">
                    <div style="width: 140px; height: 140px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 35px; border: 1px solid #e2e8f0;">
                         <i class="fas fa-layer-group" style="font-size: 50px; color: #94a3b8; opacity: 0.5;"></i>
                    </div>
                    <h3 style="color: var(--azul-oscuro); font-size: 28px; font-weight: 850; margin-bottom: 20px;">Próxima Apertura de Cursos</h3>
                    <p style="color: #64748b; max-width: 500px; margin: 0 auto 40px; font-size: 17px; line-height: 1.7;">Estamos organizando nuestra plana docente para brindarte la mejor preparación. Muy pronto publicaremos el listado oficial de cursos habilitados.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary" style="padding: 16px 40px; font-weight: 700; border-radius: 20px; box-shadow: 0 10px 25px rgba(140, 198, 63, 0.3);">VOLVER A INICIO</a>
                </div>
            @endif

        </div>
    </section>

    <!-- Banner Acción Premium con Diseño Mejorado -->
    <section style="padding: 100px 0; background: #fff;">
        <div class="container" style="max-width: 1100px; margin: 0 auto; padding: 0 20px;">
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 80px 40px; border-radius: 48px; color: white; position: relative; overflow: hidden; box-shadow: 0 40px 80px rgba(0,0,0,0.18); text-align: center;">
                <div class="kene-pattern-overlay" style="opacity: 0.08;"></div>
                <div style="position: relative; z-index: 2;">
                    <span style="color: var(--verde-cepre); font-weight: 800; text-transform: uppercase; letter-spacing: 4px; font-size: 13px; margin-bottom: 25px; display: block;">ADMISIÓN CEPRE UNAMAD</span>
                    <h2 style="font-size: 46px; font-weight: 900; margin-bottom: 30px; letter-spacing: -1px; color: #ffffff;">Asegura tu Ingreso con <span style="background: linear-gradient(90deg, var(--verde-cepre), var(--cyan-acento)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Expertos</span></h2>
                    <p style="font-size: 21px; opacity: 0.8; margin-bottom: 50px; max-width: 680px; margin-left: auto; margin-right: auto; line-height: 1.7;">Inicia tu preparación hoy mismo y únete a los miles de cachimbos que lograron su meta con nosotros.</p>
                    <a href="{{ route('register') }}" class="btn btn-secondary" style="background: linear-gradient(90deg, #ec008c, #ff1a8c); padding: 20px 50px; font-size: 18px; font-weight: 850; border-radius: 24px; box-shadow: 0 15px 40px rgba(236, 0, 140, 0.5); transition: transform 0.3s ease;">
                        <span>RESERVAR MI VACANTE AQUÍ</span>
                    </a>
                </div>
                
                <!-- Destellos de luz decorativos -->
                <div style="position: absolute; top: -150px; right: -150px; width: 400px; height: 400px; background: radial-gradient(circle, var(--magenta-unamad) 0%, transparent 70%); opacity: 0.15; filter: blur(60px);"></div>
                <div style="position: absolute; bottom: -150px; left: -150px; width: 400px; height: 400px; background: radial-gradient(circle, var(--verde-cepre) 0%, transparent 70%); opacity: 0.15; filter: blur(60px);"></div>
            </div>
        </div>
    </section>

    <style>
        .course-card-premium {
            cursor: pointer;
        }
        .course-card-premium:hover {
            transform: translateY(-18px);
            box-shadow: 0 45px 90px rgba(0,0,0,0.1);
            border-color: rgba(0,0,0,0.1);
        }
        .course-card-premium:hover .card-border-glow {
            opacity: 1;
        }
        .course-card-premium:hover .card-glow-bg {
            transform: scale(1.5);
            opacity: 0.08;
        }
        .course-card-premium:hover .icon-bg {
            transform: rotate(0deg) scale(1.1);
            opacity: 0.3;
        }
        .course-card-premium:hover .course-icon-wrapper {
            background: {{ getCourseColor(0) }};
            color: white !important;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        /* Ajuste específico para cada color de hover del icono */
        @foreach($cursos as $index => $curso)
            .courses-grid > div:nth-child({{ $index + 1 }}):hover .course-icon-wrapper {
                background: {{ getCourseColor($index) }} !important;
            }
            .courses-grid > div:nth-child({{ $index + 1 }}):hover .action-btn {
                background: {{ getCourseColor($index) }} !important;
                color: white !important;
                box-shadow: 0 8px 20px {{ getCourseColor($index) }}44;
            }
        @endforeach

        .course-card-premium:hover .action-btn {
            transform: scale(1.05);
        }
        
        h1, h2, h3 {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        @media (max-width: 768px) {
            h1 { font-size: 40px !important; }
            h2 { font-size: 32px !important; }
            .courses-grid { grid-template-columns: 1fr; }
            .course-card-premium { padding: 35px 30px !important; }
        }
    </style>

    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection
