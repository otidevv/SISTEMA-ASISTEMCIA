<!-- Sección de Modalidades y Ciclos Premium Institucional -->
<section class="modalidades-premium academic-notebook-pattern" id="modalidades">
    <!-- Patrón Kené de fondo (Sutil) -->
    <div class="kene-pattern-overlay"></div>
    
    <div class="container position-relative z-index-10">
        <div class="section-header wow fadeInUp">
            <h6 class="premium-tagline">PROGRAMAS ACADÉMICOS</h6>
            <h2 class="premium-title">MODALIDADES <span>Y CICLOS</span></h2>
            <p class="premium-subtitle">Compromiso con tu ingreso a la UNAMAD</p>
        </div>

        <div class="modalidades-flex">
            <!-- Modalidad 1: Ciclo Ordinario -->
            @php
                $ordinario = \App\Models\Ciclo::activo()->where('nombre', 'LIKE', '%Ordinario%')->first();
                $statusOrd = $ordinario ? 'Vigente' : 'Próximamente';
                $classOrd = $ordinario ? 'vigente' : 'proximamente';
                $nombreOrd = $ordinario ? $ordinario->nombre : 'Ciclo Ordinario';
            @endphp
            <div class="modalidad-item wow fadeInUp" data-wow-delay="0.1s">
                <span class="modalidad-number">01</span>
                <div class="modalidad-image-wrapper">
                    <div class="washi-tape washi-magenta"></div>
                    <img src="{{ asset('assets_cepre/img/portada/portada.png') }}" alt="Ciclo Ordinario">
                </div>
                <div class="modalidad-info-box">
                    <h3 class="modalidad-name">{{ $nombreOrd }}</h3>
                    <span class="modalidad-status {{ $classOrd }}">{{ $statusOrd }}</span>
                    @php
                        $dataOrd = json_encode([
                            'nombre' => $nombreOrd,
                            'status' => $statusOrd,
                            'descripcion' => $ordinario ? $ordinario->descripcion : 'Preparación exclusiva para el examen ordinario UNAMAD.',
                            'fecha_inicio_fmt' => $ordinario && $ordinario->fecha_inicio ? $ordinario->fecha_inicio->format('d M, Y') : 'Por definir',
                            'fecha_fin_fmt' => $ordinario && $ordinario->fecha_fin ? $ordinario->fecha_fin->format('d M, Y') : 'Por definir',
                            'exam1' => $ordinario && $ordinario->fecha_primer_examen ? $ordinario->fecha_primer_examen->format('d/m') : 'TBD',
                            'exam2' => $ordinario && $ordinario->fecha_segundo_examen ? $ordinario->fecha_segundo_examen->format('d/m') : 'TBD',
                            'exam3' => $ordinario && $ordinario->fecha_tercer_examen ? $ordinario->fecha_tercer_examen->format('d/m') : 'TBD',
                            'imagen' => asset('assets_cepre/img/portada/portada.png')
                        ]);
                    @endphp
                    <a href="javascript:void(0)" onclick='openCicloDetails(@json($dataOrd))' class="btn-premium-action">
                        <span>Leer más</span>
                    </a>
                </div>
            </div>

            <!-- Modalidad 2: Primera Oportunidad -->
            @php
                $primera = \App\Models\Ciclo::activo()->where('nombre', 'LIKE', '%Primera%')->first();
                $statusPri = $primera ? 'Vigente' : 'Próximamente';
                $classPri = $primera ? 'vigente' : 'proximamente';
                $nombrePri = $primera ? $primera->nombre : 'Ciclo Primera Oportunidad';
            @endphp
            <div class="modalidad-item wow fadeInUp" data-wow-delay="0.2s">
                <span class="modalidad-number">02</span>
                <div class="modalidad-image-wrapper">
                    <div class="washi-tape washi-cyan"></div>
                    <img src="{{ asset('assets_cepre/img/portada/estudiante.png') }}" alt="Primera Oportunidad">
                </div>
                <div class="modalidad-info-box">
                    <h3 class="modalidad-name">{{ $nombrePri }}</h3>
                    <span class="modalidad-status {{ $classPri }}">{{ $statusPri }}</span>
                    @php
                        $dataPri = json_encode([
                            'nombre' => $nombrePri,
                            'status' => $statusPri,
                            'descripcion' => $primera ? $primera->descripcion : 'Dirigido a estudiantes de 5to de secundaria que buscan su ingreso temprano.',
                            'fecha_inicio_fmt' => $primera && $primera->fecha_inicio ? $primera->fecha_inicio->format('d M, Y') : 'Por definir',
                            'fecha_fin_fmt' => $primera && $primera->fecha_fin ? $primera->fecha_fin->format('d M, Y') : 'Por definir',
                            'exam1' => $primera && $primera->fecha_primer_examen ? $primera->fecha_primer_examen->format('d/m') : 'TBD',
                            'exam2' => $primera && $primera->fecha_segundo_examen ? $primera->fecha_segundo_examen->format('d/m') : 'TBD',
                            'exam3' => $primera && $primera->fecha_tercer_examen ? $primera->fecha_tercer_examen->format('d/m') : 'TBD',
                            'imagen' => asset('assets_cepre/img/portada/estudiante.png')
                        ]);
                    @endphp
                    <a href="javascript:void(0)" onclick='openCicloDetails(@json($dataPri))' class="btn-premium-action">
                        <span>Leer más</span>
                    </a>
                </div>
            </div>

            <!-- Modalidad 3: Reforzamiento Escolar -->
            @php
                $reforzamiento = \App\Models\Ciclo::activo()->where('nombre', 'LIKE', '%Reforzamiento%')->first();
                $statusRef = $reforzamiento ? 'Inscripciones Abiertas' : 'Próximamente';
                $classRef = $reforzamiento ? 'vigente' : 'proximamente';
                $nombreRef = $reforzamiento ? $reforzamiento->nombre : 'Reforzamiento Escolar';
            @endphp
            <div class="modalidad-item wow fadeInUp" data-wow-delay="0.3s">
                <span class="modalidad-number">03</span>
                <div class="modalidad-image-wrapper">
                    <div class="washi-tape washi-green"></div>
                    <img src="{{ asset('assets_cepre/img/portada/estudiantes_secundaria_hero.png') }}" alt="Reforzamiento Escolar">
                </div>
                <div class="modalidad-info-box">
                    <h3 class="modalidad-name">{{ $nombreRef }}</h3>
                    <span class="modalidad-status {{ $classRef }}">{{ $statusRef }}</span>
                    @php
                        $dataRef = json_encode([
                            'nombre' => $nombreRef,
                            'status' => $statusRef,
                            'descripcion' => $reforzamiento ? $reforzamiento->descripcion : 'Nivelación académica para escolares de 1ro a 5to de secundaria.',
                            'fecha_inicio_fmt' => $reforzamiento && $reforzamiento->fecha_inicio ? $reforzamiento->fecha_inicio->format('d M, Y') : 'Por definir',
                            'fecha_fin_fmt' => $reforzamiento && $reforzamiento->fecha_fin ? $reforzamiento->fecha_fin->format('d M, Y') : 'Por definir',
                            'exam1' => $reforzamiento && $reforzamiento->fecha_primer_examen ? $reforzamiento->fecha_primer_examen->format('d/m') : 'TBD',
                            'exam2' => $reforzamiento && $reforzamiento->fecha_segundo_examen ? $reforzamiento->fecha_segundo_examen->format('d/m') : 'TBD',
                            'exam3' => $reforzamiento && $reforzamiento->fecha_tercer_examen ? $reforzamiento->fecha_tercer_examen->format('d/m') : 'TBD',
                            'imagen' => asset('assets_cepre/img/portada/estudiantes_secundaria_hero.png')
                        ]);
                    @endphp
                    <a href="javascript:void(0)" onclick='openCicloDetails(@json($dataRef))' class="btn-premium-action">
                        <span>Leer más</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* ===== SECCIÓN PREMIUM MODALIDADES (FIX RESPONSIVE + CUADRICULADO) ===== */
    .modalidades-premium {
        padding: 40px 0 80px; /* Reducido de 80px 0 120px */
        position: relative;
        overflow: visible;
        font-family: 'Sora', sans-serif;
        background: transparent !important; /* Permitir que se vea el fondo maestro */
    }

    .modalidades-premium .container {
        max-width: 1400px; /* Alineado con el header y otras secciones */
        margin: 0 auto;
        padding: 0 30px;
    }

    /* Flex Layout para Responsividad */
    .modalidades-flex {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 80px 40px; /* Más espacio vertical para el overlap */
    }

    .modalidad-item {
        position: relative;
        flex: 0 1 380px;
        min-width: 300px;
        display: flex;
        flex-direction: column;
    }

    /* Imagen con Corte */
    .modalidad-image-wrapper {
        width: 100%;
        height: 420px;
        border-radius: 50px 50px 180px 50px;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(0,0,0,0.12);
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        border: 4px solid #ffffff;
        background: #f1f5f9;
        z-index: 1;
    }

    .modalidad-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1s ease;
    }

    .modalidad-item:hover .modalidad-image-wrapper img {
        transform: scale(1.1);
    }

    /* Caja de Información Overlap */
    .modalidad-info-box {
        position: absolute;
        bottom: -50px;
        right: -20px;
        width: 85%;
        background: #ffffff;
        padding: 35px 25px;
        border-radius: 50px 15px 50px 15px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: all 0.4s ease;
        z-index: 2;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .modalidad-item:hover .modalidad-info-box {
        transform: translateY(-10px);
        box-shadow: 0 35px 80px rgba(236, 0, 140, 0.2);
    }

    .modalidad-name {
        font-size: 22px;
        font-weight: 850;
        color: var(--azul-oscuro, #0c1e2f);
        margin-bottom: 12px;
        line-height: 1.1;
    }

    .modalidad-status {
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 25px;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 6px 16px;
        border-radius: 20px;
    }

    .modalidad-status.vigente { 
        color: var(--cyan-acento, #00aeef); 
        background: rgba(0, 174, 239, 0.08);
    }
    
    .modalidad-status.proximamente { 
        color: var(--magenta-unamad, #ec008c); 
        background: rgba(236, 0, 140, 0.08);
    }

    .btn-premium-action {
        background: var(--magenta-unamad, #ec008c);
        color: #ffffff !important;
        padding: 12px 30px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        text-decoration: none !important;
        transition: all 0.3s;
        box-shadow: 0 8px 20px rgba(236, 0, 140, 0.3);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-premium-action::after {
        content: '→';
        transition: transform 0.3s;
    }

    .btn-premium-action:hover {
        background: var(--azul-oscuro, #0c1e2f);
        transform: scale(1.05);
    }

    .btn-premium-action:hover::after {
        transform: translateX(5px);
    }

    /* RESPONSIVE FIXES */
    @media (max-width: 1200px) {
        .modalidad-item { flex: 0 1 320px; }
        .modalidad-image-wrapper { height: 350px; }
        .modalidad-name { font-size: 18px; }
    }

    @media (max-width: 992px) {
        .modalidades-flex { gap: 100px 30px; }
        .premium-title { font-size: 38px; }
    }

    @media (max-width: 768px) {
        .modalidades-premium { padding: 80px 0 120px; }
        .modalidad-item { 
            flex: 0 1 100%; 
            max-width: 400px;
        }
        .modalidad-info-box {
            right: 0;
            width: 90%;
            bottom: -40px;
        }
        .premium-title { font-size: 32px; }
    }

    @media (max-width: 480px) {
        .modalidad-image-wrapper { height: 300px; border-radius: 30px 30px 100px 30px; }
        .modalidad-info-box { padding: 25px 15px; }
    }

    /* ===== DETALLES DE CINTA ADHESIVA (WASHI TAPE) ===== */
    .washi-tape {
        position: absolute;
        width: 80px;
        height: 30px;
        z-index: 10;
        opacity: 0.6;
        backdrop-filter: blur(1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .washi-magenta {
        background: rgba(236, 0, 140, 0.4);
        top: 20px;
        right: -25px;
        transform: rotate(35deg);
        border-left: 2px dashed rgba(255,255,255,0.5);
        border-right: 2px dashed rgba(255,255,255,0.5);
    }
    
    .washi-cyan {
        background: rgba(0, 174, 239, 0.4);
        top: 20px;
        right: -25px;
        transform: rotate(35deg);
        border-left: 2px dashed rgba(255,255,255,0.5);
        border-right: 2px dashed rgba(255,255,255,0.5);
    }
    
    .washi-green {
        background: rgba(140, 198, 63, 0.4);
        top: 20px;
        right: -25px;
        transform: rotate(35deg);
        border-left: 2px dashed rgba(255,255,255,0.5);
        border-right: 2px dashed rgba(255,255,255,0.5);
    }

    /* NUMERACIÓN DE FONDO */
    .modalidad-number {
        position: absolute;
        top: -30px;
        left: -20px;
        font-size: 120px;
        font-weight: 900;
        color: var(--azul-oscuro);
        opacity: 0.04;
        z-index: 0;
        font-family: 'Sora', sans-serif;
        pointer-events: none;
    }
</style>
