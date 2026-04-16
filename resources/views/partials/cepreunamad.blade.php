@include('partials.cepre.head')

@include('partials.cepre.header')

<!-- Hero Section con Carrusel -->
<section class="hero-section" id="inicio">
    <!-- Contenedor para el Canvas de Partículas 3D (Fondo estático) -->
    <div id="hero-canvas-container"></div>
    <div class="hero-bg-overlay"></div>
    <div class="kene-pattern-overlay"></div>

    <!-- Contenedor del Carrusel -->
    <div class="carousel-container">
        <div class="carousel-slides" id="carouselSlides">
            <!-- SLIDE 1, 2 Y 3: CÓDIGO ORIGINAL COMENTADO -->
            <!--
            <div class="carousel-slide active">
                <div class="hero-content">
                    <div class="hero-text">
                        <p class="hero-subtitle">Bienvenido a la Universidad</p>
                        <h1 class="hero-title">
                            Portal CEPRE UNAMAD | Tu<br>
                            Camino Al <span>Éxito</span>
                        </h1>
                        <p class="hero-description">
                            ¡Prepárate para Ingresar a la UNAMAD con los Mejores Docentes y Metodologías de Enseñanza!
                            Contamos con más de 15 cursos especializados y un equipo académico de excelencia.
                        </p>
                        <div class="hero-buttons">
                            <a href="#cursos" class="btn btn-primary">
                                <i class="fas fa-book"></i>
                                <span>EXPLORAR PROGRAMAS</span>
                            </a>
                            <a href="javascript:void(0)" onclick="openPostulacionModal()"
                                class="btn btn-secondary btn-postulacion-main" id="hero-btn-postular">
                                <i class="fas fa-edit"></i>
                                <span>INSCRIBIRSE AHORA</span>
                            </a>
                            <div class="video-btn" onclick="showModal('videoModal')">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>

                    <div class="hero-image-wrapper">
                        <img src="{{ asset('assets_cepre/img/portada/portada.png') }}"
                            onerror="this.onerror=null; this.src='https://placehold.co/600x400/2C5F7C/A4C639?text=CEPRE+UNAMAD+Slide+1';"
                            alt="Estudiantes CEPRE UNAMAD" class="hero-image">
                        <div class="stats-badge">
                            <p>Ingresantes CEPRE</p>
                            <h2>+1000</h2>
                            <p>¡MATRICÚLATE YA!</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-slide">
                <div class="hero-content">
                    <div class="hero-text">
                        <p class="hero-subtitle">Modalidad Exclusiva CEPRE</p>
                        <h1 class="hero-title"
                            style="--verde-cepre: var(--magenta-unamad); --cyan-acento: var(--verde-claro);">
                            Tu Ingreso <span>Directo</span><br>
                            a la UNAMAD
                        </h1>
                        <p class="hero-description">
                            Asegura tu vacante con nuestro proceso de ingreso directo. Estudia con nosotros y olvídate
                            de la preocupación del examen de admisión general.
                        </p>
                        <div class="hero-buttons">
                            <a href="#" class="btn btn-primary">
                                <i class="fas fa-certificate"></i>
                                <span>VER REGLAMENTO</span>
                            </a>
                            <a href="javascript:void(0)" onclick="openPostulacionModal()" class="btn btn-secondary"
                                style="background: linear-gradient(135deg, var(--verde-cepre), var(--cyan-acento));">
                                <i class="fas fa-user-plus"></i>
                                <span>¡POSTULA AQUÍ!</span>
                            </a>
                            <div class="video-btn" onclick="showModal('videoModal')">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>

                    <div class="hero-image-wrapper">
                        <img src="{{ asset('assets_cepre/img/portada/estudiante.png') }}"
                            onerror="this.onerror=null; this.src='https://placehold.co/600x400/2C5F7C/E6007E?text=INGRESO+DIRECTO';"
                            alt="Ingreso Directo UNAMAD" class="hero-image">
                        <div class="stats-badge" style="border-top-color: var(--verde-cepre);">
                            <p>Costo Total</p>
                            <h2>S/. 1,150</h2>
                            <p>¡TODO INCLUIDO!</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-slide">
                <div class="hero-content">
                    <div class="hero-text">
                        <p class="hero-subtitle">Calidad Académica Asegurada</p>
                        <h1 class="hero-title"
                            style="--verde-cepre: var(--cyan-acento); --cyan-acento: var(--magenta-unamad);">
                            Docentes <span>Expertos</span><br>
                            a tu Disposición
                        </h1>
                        <p class="hero-description">
                            Contamos con la plana docente más experimentada y comprometida de la región, enfocada en
                            maximizar tu potencial y asegurar tu ingreso.
                        </p>
                        <div class="hero-buttons">
                            <a href="#nosotros" class="btn btn-primary">
                                <i class="fas fa-users"></i>
                                <span>CONOCE AL EQUIPO</span>
                            </a>
                            <a href="javascript:void(0)" onclick="openPostulacionModal()" class="btn btn-secondary">
                                <i class="fas fa-pencil-alt"></i>
                                <span>INICIAR INSCRIPCIÓN</span>
                            </a>
                            <div class="video-btn" onclick="showModal('videoModal')">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                    </div>

                    <div class="hero-image-wrapper">
                        <img src="{{ asset('assets_cepre/img/portada/docentes.png') }}"
                            onerror="this.onerror=null; this.src='https://placehold.co/600x400/2C5F7C/00A0E3?text=DOCENTES';"
                            alt="Docentes expertos" class="hero-image">
                        <div class="stats-badge">
                            <p>Cursos Especializados</p>
                            <h2>18</h2>
                            <p>¡EMPIEZA HOY!</p>
                        </div>
                    </div>
                </div>
            </div>
            -->

            <!-- PORTADA ACTUAL (Fija Plana) -->
            <div class="carousel-slide active" style="padding:0; position: relative;">
                <style>
                    #inicio.hero-section {
                        padding-top: 0 !important;
                        padding-bottom: 0 !important;
                        min-height: auto !important;
                        height: auto !important;
                        background: transparent !important;
                    }
                    #inicio .hero-bg-overlay, 
                    #inicio .kene-pattern-overlay, 
                    #inicio #hero-canvas-container {
                        display: none !important;
                    }
                    #inicio .carousel-container, 
                    #inicio .carousel-slide {
                        height: auto !important;
                        min-height: auto !important;
                        padding: 0 !important;
                    }
                    @media(max-width: 768px) {
                        #inicio .hero-buttons { bottom: 2% !important; gap: 5px !important; }
                        #inicio .hero-buttons .btn { padding: 8px 15px !important; font-size: 13px !important; }
                    }
                </style>

                <!-- Fondo Dinámico -->
                <picture style="width: 100%; height: auto; display: flex;">
                    <source media="(max-width: 768px)" srcset="{{ asset('assets_cepre/img/portada/flyer.webp') }}">
                    <img src="{{ asset('assets_cepre/img/portada/portada_principal.webp') }}" alt="Portal CEPRE UNAMAD" style="width: 100%; height: auto; object-fit: contain;" fetchpriority="high">
                </picture>

                <!-- Capa oscurecedora inferior para resaltar los botones HTML -->
                <div style="position: absolute; bottom: 0; width: 100%; height: 16%; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); z-index: 5;"></div>

                <!-- Botones HTML reales superpuestos sobre la imagen -->
                <div style="position: absolute; bottom: 6%; left: 0; right: 0; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; z-index: 10;" class="hero-buttons">
                    <a href="#cursos" class="btn btn-primary" style="box-shadow: 0 4px 15px rgba(0,0,0,0.8); backdrop-filter: blur(5px);">
                        <i class="fas fa-book"></i>
                        <span>EXPLORAR PROGRAMAS</span>
                    </a>
                    <a href="javascript:void(0)" onclick="openPostulacionModal()"
                        class="btn btn-secondary btn-postulacion-main" style="box-shadow: 0 4px 15px rgba(0,0,0,0.8); backdrop-filter: blur(5px);">
                        <i class="fas fa-edit"></i>
                        <span>¡INSCRIBIRSE AHORA!</span>
                    </a>
                </div>

                <!-- Enlace de consulta rápida (Estratégico) -->
                <div style="position: absolute; bottom: 2%; left: 0; right: 0; text-align: center; z-index: 10;">
                    <a href="javascript:void(0)" onclick="consultarEstadoDirecto()" style="color: white; text-decoration: none; font-size: 0.9rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5); display: inline-flex; align-items: center; gap: 6px; background: rgba(0,0,0,0.3); padding: 5px 15px; border-radius: 20px; backdrop-filter: blur(5px);">
                        <i class="fas fa-search-plus" style="color: #00aeef;"></i>
                        ¿Ya te postulaste? <span style="color: #8bc34a;">Consulta tu estado aquí</span>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Navegación del Carrusel -->
    <!--
    <button class="carousel-nav prev-nav" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="carousel-nav next-nav" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>

    <div class="carousel-dots" id="carouselDots">
    </div>
    -->

</section>

@include('partials.cepre.countdown-widget')


<!-- Marquee -->
<div class="marquee-section">
    <div class="marquee-content">
        <div class="marquee-item"><i class="fas fa-graduation-cap"></i> Educación de calidad</div>
        <div class="marquee-item"><i class="fas fa-university"></i> CEPRE UNAMAD</div>
        <div class="marquee-item"><i class="fas fa-door-open"></i> Ciclo {{ $cicloActivo->nombre ?? 'Vigente' }}</div>
        <div class="marquee-item"><i class="fas fa-calendar-check"></i> Inscripciones abiertas</div>
        <div class="marquee-item"><i class="fas fa-chalkboard-teacher"></i> Docentes expertos</div>
        <div class="marquee-item"><i class="fas fa-book"></i> Más de {{ $stats['cursos'] ?? 15 }} cursos</div>
        <div class="marquee-item"><i class="fas fa-trophy"></i> Ingreso directo garantizado</div>
        <div class="marquee-item"><i class="fas fa-graduation-cap"></i> Educación de calidad</div>
        <div class="marquee-item"><i class="fas fa-university"></i> CEPRE UNAMAD</div>
        <div class="marquee-item"><i class="fas fa-door-open"></i> Ciclo {{ $cicloActivo->nombre ?? 'Vigente' }}</div>
        <div class="marquee-item"><i class="fas fa-calendar-check"></i> Inscripciones abiertas</div>
        <div class="marquee-item"><i class="fas fa-chalkboard-teacher"></i> Docentes expertos</div>
        <div class="marquee-item"><i class="fas fa-book"></i> Más de {{ $stats['cursos'] ?? 15 }} cursos</div>
        <div class="marquee-item"><i class="fas fa-trophy"></i> Ingreso directo garantizado</div>
    </div>
</div>

<!-- Divisor de Papel Rasgado -->
<div class="torn-paper-edge"></div>

<!-- Courses Section -->
<section class="courses-section academic-notebook-pattern" id="cursos">
    <div class="section-title">
        <h6>NUESTROS CURSOS</h6>
        <h2>Preparación Exclusiva en:</h2>
    </div>
    <div class="courses-grid">
        @if(isset($cursos) && $cursos->count() > 0)
            @foreach($cursos as $curso)
                <div class="course-card animate-on-scroll" onclick="window.location.href='{{ route('public.cursos') }}'"
                    data-info="{{ $curso->descripcion ?? 'Preparación integral para el ingreso a la universidad.' }}">
                    <div class="course-icon">
                        @php
                            $icon = 'fa-book';
                            if (stripos($curso->nombre, 'matem') !== false)
                                $icon = 'fa-calculator';
                            if (stripos($curso->nombre, 'verb') !== false || stripos($curso->nombre, 'lengu') !== false)
                                $icon = 'fa-book-open';
                            if (stripos($curso->nombre, 'fisic') !== false)
                                $icon = 'fa-atom';
                            if (stripos($curso->nombre, 'quim') !== false)
                                $icon = 'fa-flask';
                            if (stripos($curso->nombre, 'biol') !== false)
                                $icon = 'fa-dna';
                            if (stripos($curso->nombre, 'hist') !== false || stripos($curso->nombre, 'geogr') !== false)
                                $icon = 'fa-globe-americas';
                        @endphp
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div class="course-content">
                        <h3>{{ $curso->nombre }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($curso->descripcion, 60) }}</p>
                    </div>
                </div>
            @endforeach
        @else
            <!-- Fallback solid cards if no courses found -->
            <div class="course-card animate-on-scroll" onclick="window.location.href='{{ route('public.cursos') }}'"
                data-info="Matemática avanzada y resolución de problemas.">
                <div class="course-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="course-content">
                    <h3>Razonamiento Matemático</h3>
                    <p>03 Boletines | 12 Sesiones</p>
                </div>
            </div>
            <div class="course-card animate-on-scroll" onclick="window.location.href='{{ route('public.cursos') }}'"
                data-info="Habilidades de comprensión lectora y análisis.">
                <div class="course-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="course-content">
                    <h3>Razonamiento Verbal</h3>
                    <p>03 Boletines | 12 Sesiones</p>
                </div>
            </div>
        @endif
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <a href="{{ route('public.cursos') }}" class="btn-cyan-cta"
            style="display: inline-flex; align-items: center; gap: 10px;">
            <span>Ver Más Cursos</span>
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section statistics-wave">
    <!-- Wave Top -->
    <div class="custom-shape-divider-top">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>

    <div class="stats-container">
        <div class="stat-box animate-on-scroll">
            <i class="fas fa-users"></i>
            <h3 class="counter" data-target="{{ $stats['estudiantes'] ?? 1250 }}">{{ $stats['estudiantes'] ?? 0 }}</h3>
            <p>Estudiantes Matriculados</p>
        </div>
        <div class="stat-box animate-on-scroll">
            <i class="fas fa-chalkboard-teacher"></i>
            <h3 class="counter" data-target="{{ $stats['docentes'] ?? 36 }}">{{ $stats['docentes'] ?? 0 }}</h3>
            <p>Docentes Expertos</p>
        </div>
        <div class="stat-box animate-on-scroll">
            <i class="fas fa-trophy"></i>
            <h3 class="counter" data-target="{{ $stats['ingresantes'] ?? 1000 }}">{{ $stats['ingresantes'] ?? 0 }}</h3>
            <p>Ingresantes a UNAMAD</p>
        </div>
        <div class="stat-box animate-on-scroll">
            <i class="fas fa-university"></i>
            <h3 class="counter" data-target="{{ \App\Models\Carrera::activas()->count() ?? 12 }}">0</h3>
            <p>Escuelas Profesionales</p>
        </div>
    </div>

    <!-- Wave Bottom -->
    <div class="custom-shape-divider-bottom">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill" transform="rotate(180 600 60)"></path>
        </svg>
    </div>
</section>

<!-- Teachers Section -->
<section class="teachers-section academic-notebook-pattern" id="nosotros">
    <div class="section-title">
        <h6>NUESTRO EQUIPO</h6>
        <h2>Docentes con Trayectoria</h2>
    </div>
    <!-- Swiper Container -->
    <div class="swiper teachers-swiper" style="padding: 20px 0 50px 0;">
        <div class="swiper-wrapper">
            @if(isset($docentes_destacados) && $docentes_destacados->count() > 0)
                @foreach($docentes_destacados as $docente)
                    <div class="swiper-slide">
                        <div class="teacher-card animate-on-scroll animated">
                            <div class="teacher-image">
                                @php
                                    $foto = $docente->foto_perfil ? asset('storage/' . $docente->foto_perfil) : null;
                                @endphp
                                <img src="{{ $foto ?? asset('assets_cepre/img/portada/docente_avatar.webp') }}"
                                    onerror="this.onerror=null; this.src='https://placehold.co/400x300/f0f0f0/666?text={{ urlencode($docente->nombre) }}';"
                                    alt="{{ $docente->nombreCompleto }}" loading="lazy">
                            </div>
                            <div class="teacher-info">
                                <h4>{{ $docente->nombreCompleto }}</h4>

                                @php
                                    // Extract unique courses from active schedules
                                    $cursos_docente = $docente->horarios->pluck('curso')->unique('id');
                                @endphp

                                @if($cursos_docente->count() > 0)
                                    <div class="teacher-courses">
                                        @foreach($cursos_docente as $curso)
                                            <span class="course-tag"
                                                title="{{ $curso->nombre }}">{{ Str::limit($curso->nombre, 20) }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <p>Docente Especializado</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Fallback if no teachers found -->
                <div class="swiper-slide">
                    <div class="teacher-card animate-on-scroll animated">
                        <div class="teacher-image">
                            <img src="{{ asset('assets_cepre/img/portada/docente_avatar.png') }}"
                                onerror="this.onerror=null; this.src='https://placehold.co/400x300/f0f0f0/666?text=Docente+1';"
                                alt="Ing. Juan Pérez">
                        </div>
                        <div class="teacher-info">
                            <h4>Ing. Juan Pérez</h4>
                            <div class="teacher-courses">
                                <span class="course-tag">Matemática</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ... additional fallbacks if needed -->
            @endif
        </div>

        <!-- Add Pagination -->
        <div class="swiper-pagination teachers-pagination"></div>
    </div>
</section>

<!-- Swiper Initialization for Teachers Section -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swiper !== 'undefined') {
            new Swiper('.teachers-swiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.teachers-pagination',
                    clickable: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                    1200: {
                        slidesPerView: 4,
                        spaceBetween: 30,
                    },
                }
            });
        }
    });
</script>

<!-- CTA Banner -->
<section class="cta-banner">
    <div class="cta-banner-content">
        <h2>¡SOMOS LOS <span class="highlight">ÚNICOS</span> EN OTORGARTE INGRESO DIRECTO A LA UNAMAD!</h2>
        <a href="#contacto" class="btn-cyan-cta">
            <i class="fas fa-info-circle"></i>
            <span>VER MÁS DETALLES DE INGRESO</span>
        </a>
    </div>
</section>

<!-- Contact Bar -->
<section class="contact-bar animate-on-scroll" id="contacto">
    <div class="contact-bar-content">
        <div class="contact-bar-left">
            <div class="contact-bar-icon">
                <i class="fas fa-phone-alt"></i>
            </div>
            <div class="contact-bar-text">
                <h4>Asesoría Educativa</h4>
                <p>Si tienes preguntas, solicita una consulta personalizada.</p>
            </div>
        </div>
        <div class="contact-bar-right">
            <span class="label">USA NUESTRA LÍNEA 24H</span>
            <div class="phones">
                <span>+51 993 110 927</span>
                <span>+51 993 111 037</span>
            </div>
        </div>
    </div>
</section>

@include('partials.cepre.footer')

<!-- Scroll to Top Button -->
<button id="scrollTop">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Scripts -->
@include('partials.cepre.scripts')
@include('partials.cepre.home-scripts')

@push('css')
    {{-- Toastr removido por SweetAlert2 Toasts --}}
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    @vite('resources/js/postulaciones/publico-modal.js')
@endpush

<!-- Script para abrir modal de postulación (usado por el floating button) -->
<script>
    function openPostulacionModal() {
        // Cerrar el tour si está activo
        if (typeof window.closeCepreTour === 'function') {
            window.closeCepreTour();
        }

        const modal = document.getElementById('postulacionModal');
        if (modal) {
            modal.style.display = 'flex';

            // Ocultar elementos flotantes que estorban en móvil
            const bubble = document.getElementById('countdown-bubble');
            const chatbot = document.getElementById('chatbot-launcher');
            const helpBtn = document.querySelector('.btn-ayuda-tour');

            if (bubble) bubble.style.display = 'none';
            if (chatbot) chatbot.style.display = 'none';
            if (helpBtn) helpBtn.style.display = 'none';

            // Marcar tour como completado definitivamente si abren el modal
            if (typeof window.markTourAsCompleted === 'function') {
                window.markTourAsCompleted();
            }

            // showStep is defined in publico-modal.js
            if (typeof showStep === 'function') {
                showStep(1);
            } else {
                console.error('showStep function not found. publico-modal.js might not be loaded.');
            }
            if (typeof createConfetti === 'function') {
                createConfetti();
            }
        }
    }
</script>

<!-- Animaciones estacionales (carnaval, navidad, etc.) -->
@include('partials.cepre.seasonal-animations')

<!-- Incluir Modal de Postulación -->
@include('partials.postulacion-modal')
@include('partials.cepre.results-modal')