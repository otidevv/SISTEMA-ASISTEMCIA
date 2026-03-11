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
    <section class="hero-section" style="min-height: 300px; background: linear-gradient(135deg, #0d2838 0%, #1f3e76 50%, #1a1a4a 100%); position: relative; overflow: hidden;">
        <div class="kene-pattern-overlay-2" style="opacity: 0.08;"></div>
        <!-- Círculos decorativos -->
        <div style="position: absolute; top: -80px; right: -80px; width: 300px; height: 300px; border-radius: 50%; background: rgba(140,198,63,0.08); z-index: 1;"></div>
        <div style="position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; border-radius: 50%; background: rgba(236,0,140,0.06); z-index: 1;"></div>
        <div style="position: absolute; top: 40%; left: 10%; width: 150px; height: 150px; border-radius: 50%; background: rgba(0,174,239,0.05); z-index: 1;"></div>

        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 50px 20px 45px; position: relative; z-index: 2; text-align: center; color: white;">
            <div style="display: inline-block; background: rgba(140,198,63,0.2); backdrop-filter: blur(10px); padding: 6px 20px; border-radius: 30px; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px; border: 1px solid rgba(140,198,63,0.3);">
                <i class="fas fa-book-reader" style="margin-right: 6px;"></i> CEPRE UNAMAD — Oferta Académica
            </div>
            <h1 class="animate-on-scroll animated" style="font-size: clamp(36px, 5vw, 52px); font-weight: 900; margin-bottom: 15px; letter-spacing: -0.02em; line-height: 1.1;">
                <span style="color: #ffffff; text-shadow: 0 0 20px rgba(255,255,255,0.4), 0 2px 10px rgba(0,0,0,0.3);">Formación de</span> <span style="background: linear-gradient(90deg, #8cc63f, #a4c639, #00aeef); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 2px 4px rgba(140,198,63,0.3));">Excelencia</span>
            </h1>
            <p class="animate-on-scroll animated" style="font-size: 17px; opacity: 0.85; max-width: 650px; margin: 0 auto; line-height: 1.6; font-weight: 400;">
                Desarrolla tus habilidades con los cursos más completos diseñados para el ingreso directo a la UNAMAD.
            </p>
            <div style="margin-top: 25px; display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.7;">
                    <i class="fas fa-chalkboard-teacher" style="color: #8cc63f;"></i>
                    <span>{{ $cursos->count() }} Cursos Disponibles</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.7;">
                    <i class="fas fa-star" style="color: #ec008c;"></i>
                    <span>Docentes Especializados</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.7;">
                    <i class="fas fa-trophy" style="color: #00aeef;"></i>
                    <span>Preparación de Alto Nivel</span>
                </div>
            </div>
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

            <div class="cur-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;">
                @foreach($cursos as $index => $curso)
                    @php 
                        $iconClass = getCourseIcon($curso->nombre); 
                        $nombreJs = json_encode($curso->nombre);
                        $descJs = json_encode($curso->descripcion ?? 'Formación académica de alto rendimiento con expertos en el examen de admisión UNAMAD.');
                        $color = getCourseColor($index);
                    @endphp
                    <div class="cur-card animate-on-scroll" 
                         onclick='showModal("courseInfo", {{ $nombreJs }}, {{ $descJs }}, "{{ $iconClass }}")'>
                        
                        <!-- Acento superior con color -->
                        <div class="cur-card-accent" style="background: {{ $color }};"></div>
                        
                        <!-- Header: Icono + Título -->
                        <div class="cur-card-header">
                            <div class="cur-icon" style="background: {{ $color }}15; color: {{ $color }};">
                                <i class="fas {{ $iconClass }}"></i>
                            </div>
                            <div class="cur-header-text">
                                <h3 class="cur-title">{{ $curso->nombre }}</h3>
                                <span class="cur-tag" style="color: {{ $color }}; background: {{ $color }}10; border-color: {{ $color }}30;">Presencial</span>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <p class="cur-desc">
                            {{ Str::limit($curso->descripcion ?? 'Formación académica de alto rendimiento con expertos en el examen de admisión UNAMAD.', 120) }}
                        </p>

                        <!-- Footer -->
                        <div class="cur-footer">
                            <div class="cur-info">
                                <i class="fas fa-clock" style="color: {{ $color }};"></i>
                                <span>CEPRE UNAMAD</span>
                            </div>
                            <div class="cur-btn" style="background: {{ $color }};">
                                <span>Ver Más</span>
                                <i class="fas fa-arrow-right"></i>
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

    <!-- Banner Acción Premium -->
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
                <div style="position: absolute; top: -150px; right: -150px; width: 400px; height: 400px; background: radial-gradient(circle, var(--magenta-unamad) 0%, transparent 70%); opacity: 0.15; filter: blur(60px);"></div>
                <div style="position: absolute; bottom: -150px; left: -150px; width: 400px; height: 400px; background: radial-gradient(circle, var(--verde-cepre) 0%, transparent 70%); opacity: 0.15; filter: blur(60px);"></div>
            </div>
        </div>
    </section>

    <style>
        /* ============================== */
        /* Course Cards Premium            */
        /* ============================== */
        .cur-card {
            background: white;
            border-radius: 16px;
            padding: 0;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }

        .cur-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.12);
            border-color: rgba(0,0,0,0.1);
        }

        .cur-card-accent {
            height: 4px;
            width: 100%;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .cur-card:hover .cur-card-accent {
            opacity: 1;
        }

        .cur-card-header {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 24px 24px 0;
        }

        .cur-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .cur-card:hover .cur-icon {
            transform: scale(1.1) rotate(-3deg);
        }

        .cur-header-text {
            flex: 1;
            min-width: 0;
        }

        .cur-title {
            font-size: 17px;
            font-weight: 800;
            color: #0d2838;
            line-height: 1.3;
            margin: 0 0 6px 0;
        }

        .cur-tag {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid;
        }

        .cur-desc {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.65;
            padding: 14px 24px 0;
            margin: 0;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cur-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            margin-top: 16px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .cur-info {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
        }

        .cur-info i { font-size: 11px; }

        .cur-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .cur-btn i {
            font-size: 10px;
            transition: transform 0.3s ease;
        }

        .cur-card:hover .cur-btn {
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
            transform: scale(1.05);
        }

        .cur-card:hover .cur-btn i {
            transform: translateX(3px);
        }

        h1, h2, h3 {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        @media (max-width: 768px) {
            h1 { font-size: 40px !important; }
            h2 { font-size: 32px !important; }
            .cur-grid { grid-template-columns: 1fr !important; }
        }
    </style>

    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection
