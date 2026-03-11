@extends('layouts.cepre')

@section('title', 'Carreras Profesionales | CEPRE UNAMAD')

@section('content')
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    <!-- Hero Carreras Premium -->
    <section class="hero-section" style="min-height: 300px; background: linear-gradient(135deg, #0d2838 0%, #1f3e76 50%, #1a1a4a 100%); position: relative; overflow: hidden;">
        <div class="kene-pattern-overlay-2" style="opacity: 0.08;"></div>
        <!-- Círculos decorativos -->
        <div style="position: absolute; top: -80px; right: -80px; width: 300px; height: 300px; border-radius: 50%; background: rgba(0,161,154,0.08); z-index: 1;"></div>
        <div style="position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; border-radius: 50%; background: rgba(226,0,106,0.06); z-index: 1;"></div>

        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 50px 20px 40px; position: relative; z-index: 2; text-align: center; color: white;">
            <div style="display: inline-block; background: rgba(226,0,106,0.2); backdrop-filter: blur(10px); padding: 6px 20px; border-radius: 30px; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px; border: 1px solid rgba(226,0,106,0.3);">
                <i class="fas fa-university" style="margin-right: 6px;"></i> UNAMAD — Oferta Académica
            </div>
            <h1 class="animate-on-scroll animated" style="font-size: clamp(36px, 5vw, 52px); font-weight: 900; margin-bottom: 15px; letter-spacing: -0.02em; line-height: 1.1;">
                <span style="color: #ffffff; text-shadow: 0 0 20px rgba(255,255,255,0.4), 0 2px 10px rgba(0,0,0,0.3);">Carreras</span> <span style="background: linear-gradient(90deg, #00d4aa, #00aeef, #00d4aa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 2px 4px rgba(0,174,239,0.3));">Profesionales</span>
            </h1>
            <p class="animate-on-scroll animated" style="font-size: 17px; opacity: 0.85; max-width: 600px; margin: 0 auto; line-height: 1.6; font-weight: 400;">
                Descubre tu vocación en la UNAMAD. Contamos con una amplia oferta académica
                para tu futuro profesional.
            </p>
            <div style="margin-top: 25px; display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.7;">
                    <i class="fas fa-graduation-cap" style="color: #00a19a;"></i>
                    <span>{{ $carreras->count() }} Carreras Disponibles</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; opacity: 0.7;">
                    <i class="fas fa-award" style="color: #e2006a;"></i>
                    <span>Acreditación Institucional</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Divisor -->
    <div class="torn-paper-edge"></div>

    <!-- Grid de Carreras -->
    <section class="courses-section academic-notebook-pattern" style="padding: 60px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            
            <!-- Header de sección -->
            <div style="text-align: center; margin-bottom: 45px;" class="animate-on-scroll">
                <h2 style="font-size: 28px; font-weight: 800; color: #0d2838; margin-bottom: 8px;">
                    Explora Nuestras Carreras
                </h2>
                <div style="width: 60px; height: 4px; background: linear-gradient(90deg, #e2006a, #00a19a); margin: 0 auto; border-radius: 2px;"></div>
            </div>

            <div class="carreras-grid-premium" id="carrerasGrid">
                @foreach($carreras as $carrera)
                    <a href="{{ route('public.carreras.show', $carrera->slug ?: 'inactiva') }}" class="carrera-card-premium animate-on-scroll" style="text-decoration: none; color: inherit;">
                        <!-- Logo prominente -->
                        <div class="carrera-card-logo">
                            @if($carrera->imagen_url)
                                <img src="{{ asset($carrera->imagen_url) }}" alt="{{ $carrera->nombre }}">
                            @else
                                <div class="carrera-card-icon-fallback">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Contenido -->
                        <div class="carrera-card-body">
                            <h3 class="carrera-card-title">{{ $carrera->nombre }}</h3>
                            <p class="carrera-card-desc">
                                {{ Str::limit($carrera->descripcion ?? 'Formación académica de excelencia con los mejores docentes y laboratorios equipados.', 110) }}
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="carrera-card-footer">
                            <span class="carrera-badge-unamad">UNAMAD</span>
                            <span class="carrera-ver-mas-btn">
                                Ver Detalles <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

        </div>
    </section>

    <style>
        /* ============================== */
        /* Grid de Carreras Premium       */
        /* ============================== */
        .carreras-grid-premium {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 28px;
        }

        .carrera-card-premium {
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .carrera-card-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0d2838, #00a19a);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .carrera-card-premium:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.12);
            border-color: rgba(0,161,154,0.2);
        }

        .carrera-card-premium:hover::before {
            opacity: 1;
        }

        /* Logo Area */
        .carrera-card-logo {
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
            background: linear-gradient(150deg, #f8fafc 0%, #f1f5f9 100%);
            position: relative;
            overflow: hidden;
        }

        .carrera-card-logo::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,161,154,0.2), transparent);
        }

        .carrera-card-logo img {
            max-height: 100px;
            max-width: 85%;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(0,0,0,0.08));
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .carrera-card-premium:hover .carrera-card-logo img {
            transform: scale(1.12);
        }

        .carrera-card-icon-fallback {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d2838, #1f3e76);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            box-shadow: 0 8px 25px rgba(13,40,56,0.2);
        }

        /* Body */
        .carrera-card-body {
            padding: 22px 25px 15px;
            flex-grow: 1;
        }

        .carrera-card-title {
            font-size: 17px;
            font-weight: 800;
            color: #0d2838;
            line-height: 1.35;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .carrera-card-premium:hover .carrera-card-title {
            color: #00a19a;
        }

        .carrera-card-desc {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.65;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin: 0;
        }

        /* Footer */
        .carrera-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 25px;
            border-top: 1px solid rgba(0,0,0,0.05);
            margin-top: auto;
        }

        .carrera-badge-unamad {
            font-size: 11px;
            font-weight: 800;
            color: #e2006a;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            background: rgba(226,0,106,0.06);
            padding: 4px 12px;
            border-radius: 20px;
        }

        .carrera-ver-mas-btn {
            font-size: 12px;
            font-weight: 700;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 20px;
            background: linear-gradient(135deg, #0d2838, #1f3e76);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .carrera-ver-mas-btn i {
            font-size: 10px;
            transition: transform 0.3s ease;
        }

        .carrera-card-premium:hover .carrera-ver-mas-btn {
            background: linear-gradient(135deg, #00a19a, #00aeef);
            box-shadow: 0 4px 15px rgba(0,161,154,0.3);
        }

        .carrera-card-premium:hover .carrera-ver-mas-btn i {
            transform: translateX(4px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .carreras-grid-premium {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .carrera-card-logo {
                height: 130px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .carreras-grid-premium {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection
