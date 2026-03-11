@extends('layouts.cepre')

@section('title', 'Carreras Profesionales | CEPRE UNAMAD')

@section('content')
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    <!-- Hero Carreras -->
    <section class="hero-section" style="min-height: 250px; background: linear-gradient(135deg, var(--azul-oscuro) 0%, #1a3a4a 100%);">
        <div class="kene-pattern-overlay" style="opacity: 0.1;"></div>
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px; position: relative; z-index: 2; text-align: center; color: white;">
            <h1 class="animate-on-scroll animated" style="font-size: 48px; font-weight: 800; margin-bottom: 10px;">Carreras Profesionales</h1>
            <p class="animate-on-scroll animated" style="font-size: 18px; opacity: 0.9; max-width: 700px; margin: 0 auto;">Descubre tu vocación en la UNAMAD. Contamos con una amplia oferta académica para tu futuro profesional.</p>
        </div>
    </section>

    <!-- Divisor -->
    <div class="torn-paper-edge"></div>

    <!-- Filtros y Grid -->
    <section class="courses-section academic-notebook-pattern" style="padding: 60px 0;">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            
            <div class="courses-grid" id="carrerasGrid">
                @foreach($carreras as $carrera)
                    <div class="course-card animate-on-scroll" style="display: flex; flex-direction: column; overflow: hidden; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: white; transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative;">
                        <!-- Imagen/Icono Header -->
                        <div style="height: 140px; background: linear-gradient(135deg, rgba(31, 62, 118, 0.05), rgba(0, 161, 154, 0.05)); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding: 20px;">
                            @if($carrera->imagen_url)
                                <img src="{{ asset($carrera->imagen_url) }}" alt="{{ $carrera->nombre }}" style="max-height: 80px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); transition: transform 0.3s ease;" class="course-img-hover">
                            @else
                                <div class="course-icon" style="background: var(--cyan-acento); width: 80px; height: 80px; font-size: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white; margin: 0;">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Contenido -->
                        <div class="course-content" style="padding: 25px; flex-grow: 1; display: flex; flex-direction: column;">
                            <h3 style="font-size: 18px; line-height: 1.3; margin-bottom: 12px; font-weight: 700; color: var(--azul-oscuro);">
                                {{ $carrera->nombre }}
                            </h3>
                            <p style="font-size: 14px; opacity: 0.7; margin-bottom: 20px; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $carrera->descripcion ?? 'Formación académica de excelencia con los mejores docentes y laboratorios equipados.' }}
                            </p>
                            
                            <!-- Botón Detalles -->
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px; margin-top: auto;">
                                <span style="font-size: 12px; font-weight: 700; color: var(--magenta-unamad); text-transform: uppercase;">
                                    UNAMAD
                                </span>
                                <a href="{{ route('public.carreras.show', $carrera->slug ?: 'inactiva') }}" class="btn btn-primary" style="padding: 8px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: var(--azul-oscuro); color: white; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background 0.2s ease;">
                                    <span>Ver Detalles</span> <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <style>
                .course-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 15px 35px rgba(0,0,0,0.12) !important;
                }
                .course-card:hover .course-img-hover {
                    transform: scale(1.1);
                }
                .courses-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                    gap: 30px;
                }
            </style>

        </div>
    </section>

    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection
