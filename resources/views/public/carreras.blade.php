@extends('layouts.cepre')

@section('title', 'Carreras Profesionales')

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
                    <div class="course-card animate-on-scroll">
                        <div class="course-icon" style="background: var(--cyan-acento);">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="course-content">
                            <h3 style="font-size: 20px; line-height: 1.2; margin-bottom: 10px;">{{ $carrera->nombre }}</h3>
                            <p style="font-size: 14px; opacity: 0.8; margin-bottom: 15px;">
                                {{ $carrera->descripcion ?? 'Formación académica de excelencia con los mejores docentes y laboratorios.' }}
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
                                <span style="font-size: 12px; font-weight: 700; color: var(--magenta-unamad); text-transform: uppercase;">
                                    {{ $carrera->facultad->nombre ?? 'UNAMAD' }}
                                </span>
                                <a href="#" class="btn btn-primary" style="padding: 8px 15px; font-size: 12px;">
                                    <span>DETALLES</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection
