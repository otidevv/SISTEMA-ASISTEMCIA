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

            <!-- SLIDE 1: Éxito (Original) -->
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
                        <!-- RUTA DINÁMICA RESTAURADA -->
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

            <!-- SLIDE 2: Ingreso Directo -->
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

            <!-- SLIDE 3: Docentes Expertos -->
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

        </div>
    </div>

    <!-- Navegación del Carrusel -->
    <button class="carousel-nav prev-nav" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="carousel-nav next-nav" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>

    <!-- Puntos de Paginación -->
    <div class="carousel-dots" id="carouselDots">
        <!-- Los puntos se inyectan aquí por JavaScript -->
    </div>

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
<section class="stats-section">
    <div class="stats-container">
        <div class="stat-box animate-on-scroll">
            <i class="fas fa-users"></i>
            <h3 class="counter" data-target="{{ $stats['estudiantes'] ?? 1250 }}">{{ $stats['estudiantes'] ?? 0 }}</h3>
            <p>Estudiantes Matriculados</p>
        </div>
        <div class="stat-box animate-on-scroll">
            <i class="fas fa-chalkboard-teacher"></i>
            <h3 class="counter" data-target="{{ $stats['docentes'] ?? 25 }}">{{ $stats['docentes'] ?? 0 }}</h3>
            <p>Docentes Expertos</p>
        </div>
        <div class="stat-box animate-on-scroll">
            <i class="fas fa-trophy"></i>
            <h3 class="counter" data-target="{{ $stats['ingresantes'] ?? 1000 }}">{{ $stats['ingresantes'] ?? 0 }}</h3>
            <p>Ingresantes a UNAMAD</p>
        </div>
        <div class="stat-box animate-on-scroll">
            <i class="fas fa-university"></i>
            <h3 class="counter" data-target="{{ \App\Models\Carrera::activas()->count() }}">0</h3>
            <p>Escuelas Profesionales</p>
        </div>
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
                                <img src="{{ $foto ?? asset('assets_cepre/img/portada/docente_avatar.png') }}"
                                    onerror="this.onerror=null; this.src='https://placehold.co/400x300/f0f0f0/666?text={{ urlencode($docente->nombre) }}';"
                                    alt="{{ $docente->nombreCompleto }}">
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
    <div class="kene-pattern-overlay" style="opacity: 0.4;"></div>
    <div class="cta-banner-content">
        <h2>¡SOMOS LOS <span style="color:var(--cyan-acento); text-shadow: none;">ÚNICOS</span> EN OTORGARTE INGRESO
            DIRECTO A LA UNAMAD!</h2>
        <a href="#" class="btn-cyan-cta" style="margin-top: 20px;">
            <i class="fas fa-info-circle"></i>
            <span>VER MÁS DETALLES DE INGRESO</span>
        </a>
    </div>
</section>

<!-- Contact Bar -->
<section class="contact-bar" id="contacto">
    <div class="contact-bar-content">
        <div class="contact-bar-left">
            <div class="contact-bar-icon">
                <i class="fas fa-phone-alt"></i>
            </div>
            <div>
                <p style="margin: 0; font-size: 16px; opacity: 0.9; color: var(--azul-oscuro);">Si tienes preguntas,
                    solicita una consulta</p>
                <p style="margin: 0; font-size: 14px; font-weight: 700; color: var(--azul-oscuro);">con nuestro asesor
                    educativo.</p>
            </div>
        </div>
        <div class="contact-bar-right">
            <p style="margin: 0; font-size: 14px; opacity: 0.9; color: var(--azul-oscuro);">USA NUESTRA LÍNEA 24H</p>
            <h3>+51 993 110 927<br>+51 993 111 037</h3>
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