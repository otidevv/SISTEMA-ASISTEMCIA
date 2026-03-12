@extends('layouts.cepre')

@section('title', 'Resultados de Exámenes | CEPRE UNAMAD')

@section('content')
    @include('partials.cepre.head')
    @include('partials.cepre.header')

    @php
        // Paleta Institucional Premium
        $colorVerde = '#8cc63f';
        $colorMagenta = '#ec008c';
        $colorCyan = '#00aeef';
        $colorNavy = '#0d2838';
    @endphp

    <!-- Hero Resultados Premium (Consistente con Carreras/Cursos) -->
    <section class="hero-section" style="min-height: 300px; background: linear-gradient(135deg, #0d2838 0%, #1f3e76 50%, #1a1a4a 100%); position: relative; overflow: hidden;">
        <div class="kene-pattern-overlay-2" style="opacity: 0.08;"></div>
        <!-- Círculos decorativos -->
        <div style="position: absolute; top: -80px; right: -80px; width: 300px; height: 300px; border-radius: 50%; background: rgba(140,198,63,0.08); z-index: 1;"></div>
        <div style="position: absolute; bottom: -60px; left: -60px; width: 200px; height: 200px; border-radius: 50%; background: rgba(236,0,140,0.06); z-index: 1;"></div>
        <div style="position: absolute; top: 40%; left: 10%; width: 150px; height: 150px; border-radius: 50%; background: rgba(0,174,239,0.05); z-index: 1;"></div>

        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 50px 20px 45px; position: relative; z-index: 2; text-align: center; color: white;">
            <div style="display: inline-block; background: rgba(140,198,63,0.2); backdrop-filter: blur(10px); padding: 6px 20px; border-radius: 30px; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 20px; border: 1px solid rgba(140,198,63,0.3);">
                <i class="fas fa-trophy" style="margin-right: 6px;"></i> CEPRE UNAMAD — Área de Resultados
            </div>
            <h1 class="animate-on-scroll animated" style="font-size: clamp(36px, 5vw, 52px); font-weight: 900; margin-bottom: 15px; letter-spacing: -0.02em; line-height: 1.1;">
                <span style="color: #ffffff; text-shadow: 0 0 20px rgba(255,255,255,0.4), 0 2px 10px rgba(0,0,0,0.3);">Resultados de</span> <span style="background: linear-gradient(90deg, #8cc63f, #a4c639, #00aeef); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 2px 4px rgba(140,198,63,0.3));">Exámenes</span>
            </h1>
            <p class="animate-on-scroll animated" style="font-size: 17px; opacity: 0.85; max-width: 650px; margin: 0 auto; line-height: 1.6; font-weight: 400;">
                Accede a las listas oficiales y puntajes del Centro Pre-Universitario de la Universidad Nacional Amazónica de Madre de Dios.
            </p>
        </div>
    </section>

    <!-- Divisor Estilo Institucional -->
    <div class="torn-paper-edge" style="background: white;"></div>

    <!-- Filtro Superior Simplificado -->
    <section style="background: #f8fafc; padding: 20px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <form method="GET" action="{{ route('resultados-examenes.public') }}" id="filterForm">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; justify-content: center;">
                    <div style="font-weight: 800; color: #0d2838; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-filter" style="color: #8cc63f;"></i> Filtrar por Ciclo:
                    </div>
                    <select name="ciclo" id="cicloSelectNative" onchange="this.form.submit()" style="padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 14px; font-weight: 600; color: #0d2838; background: white; cursor: pointer; min-width: 200px; max-width: 100%; display: block !important;">
                        <option value="">Mostrar Todos los Ciclos</option>
                        @foreach($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}" {{ $cicloId == $ciclo->id ? 'selected' : '' }}>
                                {{ $ciclo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </section>

    <!-- Resultados Section -->
    <section class="courses-section academic-notebook-pattern" style="padding: 80px 0; background: #fff; position: relative;">
        <div class="kene-pattern-overlay" style="opacity: 0.1; pointer-events: none;"></div>
        
        <div class="container" style="max-width: 1250px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 2;">
            @if($resultados->count() > 0)
                @foreach($resultados as $index => $grupo)
                    <div class="animate-on-scroll" style="margin-bottom: 80px;">
                        
                        <!-- Header de Ciclo (Igual a Cursos) -->
                        <div class="section-title" style="text-align: left; margin-bottom: 45px; display: flex; align-items: center; gap: 20px;">
                            <h2 style="font-size: 28px; font-weight: 850; color: #0d2838; margin: 0; white-space: nowrap; font-family: 'Inter', sans-serif;">
                                {{ $grupo->first()->ciclo->nombre }}
                            </h2>
                            <div style="height: 5px; flex-grow: 1; background: linear-gradient(90deg, #8cc63f, #00aeef, transparent); border-radius: 5px; opacity: 0.4;"></div>
                            <div style="background: rgba(13, 102, 53, 0.05); color: #0d2838; padding: 5px 15px; border-radius: 50px; font-size: 11px; font-weight: 800; letter-spacing: 1px;">
                                {{ $grupo->count() }} PUBLICACIONES
                            </div>
                        </div>

                        <!-- Grid de Tarjetas Premium -->
                        <div class="results-premium-grid">
                            @foreach($grupo as $resIdx => $resultado)
                                @php
                                    if ($resultado->tipo_resultado == 'link' && $resultado->tiene_link) {
                                        $cardUrl = $resultado->link_externo; $isLink = 'true';
                                    } elseif ($resultado->tiene_pdf) {
                                        $cardUrl = route('resultados-examenes.view', $resultado->id); $isLink = 'false';
                                    } else {
                                        $cardUrl = $resultado->link_externo ?? '#'; $isLink = $resultado->tiene_link ? 'true' : 'false';
                                    }

                                    $colors = ['#8cc63f', '#ec008c', '#00aeef', '#1f3e76'];
                                    $cardColor = $colors[($index + $resIdx) % count($colors)];
                                    
                                    $typeIcon = match($resultado->tipo_resultado) {
                                        'pdf' => 'fa-file-pdf',
                                        'link' => 'fa-external-link-alt',
                                        'ambos' => 'fa-clone',
                                        default => 'fa-file-alt'
                                    };

                                    $meses = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
                                    $dia = $resultado->fecha_examen->format('d');
                                    $mesLabel = $meses[$resultado->fecha_examen->month - 1];

                                    // Extraer el número del examen (Priorizar campo 'orden')
                                    $ordenRaw = ($resultado->orden && $resultado->orden > 0) ? $resultado->orden : null;
                                    
                                    if (!$ordenRaw) {
                                        $nomeLower = mb_strtolower($resultado->nombre_examen, 'UTF-8');
                                        if (preg_match('/primer/u', $nomeLower)) $ordenRaw = 1;
                                        elseif (preg_match('/segundo/u', $nomeLower)) $ordenRaw = 2;
                                        elseif (preg_match('/tercer/u', $nomeLower)) $ordenRaw = 3;
                                        elseif (preg_match('/cuarto/u', $nomeLower)) $ordenRaw = 4;
                                        elseif (preg_match('/\d+/', $nomeLower, $matches)) $ordenRaw = (int)$matches[0];
                                    }

                                    // Formatear como 01, 02...
                                    $ordenLabel = $ordenRaw ? str_pad($ordenRaw, 2, '0', STR_PAD_LEFT) : '';

                                    // DETECTAR "EDICIÓN DE ORO" (Resultados Finales / Tercer Examen)
                                    $nomeNormalize = mb_strtolower($resultado->nombre_examen, 'UTF-8');
                                    $isFinal = (preg_match('/tercer/u', $nomeNormalize) || preg_match('/final/u', $nomeNormalize) || preg_match('/ingresante/u', $nomeNormalize));
                                    
                                    // Colores Base
                                    $colors = ['#8cc63f', '#ec008c', '#00aeef', '#1f3e76'];
                                    $baseColor = $colors[($index + $resIdx) % count($colors)];
                                    
                                    // Si es final, aplicamos el tema de ORO
                                    $cardAccentColor = $isFinal ? '#d4af37' : $baseColor;
                                    $cardThemeClass = $isFinal ? 'gold-edition' : '';
                                @endphp

                                <!-- TARJETA GLASS-PREMIUM -->
                                <div class="glass-res-card animate-on-scroll {{ $cardThemeClass }}" 
                                     onclick="openResultModal('{{ $cardUrl }}', '{{ addslashes($resultado->nombre_examen) }}', {{ $isLink }}, {{ $isFinal ? 'true' : 'false' }})">
                                    
                                    @if($isFinal)
                                        <div class="merito-badge-innovative">
                                            <span class="pulse-ring"></span>
                                            <i class="fas fa-graduation-cap"></i> ÉXITO ACADÉMICO
                                        </div>
                                    @endif

                                    <div class="glass-bg"></div>
                                    <div class="card-accent" style="background: {{ $cardAccentColor }};"></div>
                                    
                                    @if($ordenLabel)
                                        <div class="exam-number-bg" style="color: {{ $cardAccentColor }}; opacity: {{ $isFinal ? '0.15' : '0.25' }};">{{ $ordenLabel }}</div>
                                    @endif
                                    
                                    <!-- Header: Fecha + Logo -->
                                    <div class="card-top">
                                        <div class="card-logo-box">
                                            <img src="{{ asset('assets/images/logo cepre.png') }}" alt="CEPRE Logo" class="cepre-card-logo">
                                        </div>
                                        <div class="date-tag">
                                            <div class="date-label" style="color: {{ $cardAccentColor }};">FECHA DEL EXAMEN</div>
                                            <div class="date-values">
                                                <span class="day" style="{{ $isFinal ? 'color: #8a6d3b;' : '' }}">{{ $dia }}</span>
                                                <span class="month" style="{{ $isFinal ? 'color: #d4af37;' : '' }}">{{ $mesLabel }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="card-info">
                                        <div class="official-label">
                                            <i class="fas {{ $isFinal ? 'fa-graduation-cap' : 'fa-shield-alt' }}" style="{{ $isFinal ? 'color: #d4af37;' : '' }}"></i> 
                                            {{ $isFinal ? 'ADMISIÓN DIRECTA' : 'OFICIAL CEPRE' }}
                                        </div>
                                        <h3 class="res-title" style="{{ $isFinal ? 'color: #0d2838;' : '' }}">{{ $resultado->nombre_examen }}</h3>
                                        <p class="res-desc">
                                            {{ $isFinal ? 'Lista oficial de ingresantes autorizada por el Consejo Universitario. ¡Felicidades a los nuevos cachimbos!' : Str::limit($resultado->descripcion ?? 'Publicación oficial de puntajes y méritos alcanzados en el proceso de admisión.', 100) }}
                                        </p>
                                    </div>

                                    <!-- Footer -->
                                    <div class="card-action">
                                        <div class="action-btn" style="background: {{ $isFinal ? 'linear-gradient(135deg, #d4af37 0%, #a67c00 100%)' : $cardAccentColor }};">
                                            <span>
                                                <i class="fas {{ $typeIcon }}" style="margin-right: 8px;"></i>
                                                {{ $isLink == 'true' ? 'ACCEDER A LA LISTA' : 'VER RESULTADOS OFICIALES' }}
                                            </span>
                                            <div class="shine-effect"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 100px 20px;">
                    <i class="fas fa-folder-open" style="font-size: 60px; color: #cbd5e1; margin-bottom: 25px; opacity: 0.5;"></i>
                    <h3 style="font-size: 26px; color: #0d2838; font-weight: 850;">No hay registros disponibles</h3>
                    <p style="color: #64748b; max-width: 500px; margin: 0 auto;">Estamos actualizando el servidor con los últimos resultados. Por favor, selecciona otro ciclo histórico.</p>
                </div>
            @endif

            <!-- SECCIÓN EXTERNA DE INVITACIÓN (FLYER RECLUTAMIENTO) -->
            <div class="external-recruitment-flyer" style="margin-top: 80px; background: white; border-radius: 40px; overflow: hidden; box-shadow: 0 40px 100px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.05); position: relative;">
                <div class="row g-0 align-items-center">
                    <!-- Foto de la Alumna -->
                    <div class="col-lg-5 order-2 order-lg-1" style="background: #f8fafc; display: flex; align-items: flex-end; justify-content: center; overflow: hidden; min-height: 400px; position: relative;">
                        <div class="kene-pattern-overlay" style="opacity: 0.04;"></div>
                        <img src="{{ asset('assets_cepre/img/portada/estudiante.png') }}" style="height: 90%; width: auto; object-fit: contain; position: relative; z-index: 2; filter: drop-shadow(0 15px 50px rgba(0,0,0,0.15));">
                    </div>
                    
                    <!-- Contenido Informativo -->
                    <div class="col-lg-7 order-1 order-lg-2" style="padding: 60px 80px; background: white;">
                        <div style="display: inline-block; background: rgba(255, 0, 128, 0.08); color: #ff0080; padding: 8px 20px; border-radius: 50px; font-size: 11px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 25px; border: 1px solid rgba(255, 0, 128, 0.15);">PRÓXIMO CICLO 2026-I</div>
                        
                        <h2 style="font-size: 44px; color: #0d2838; font-weight: 900; line-height: 1.1; margin-bottom: 25px; letter-spacing: -2px;">¿No alcanzaste vacante?<br><span style="background: linear-gradient(90deg, #ff0080, #7928ca); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">¡Es momento de levantarse!</span></h2>
                        
                        <p style="color: #64748b; font-size: 18px; line-height: 1.6; margin-bottom: 40px; max-width: 550px;">El éxito es la suma de pequeños esfuerzos repetidos día tras día. Inicia tu preparación para el nuevo ciclo con los mejores docentes de la región.</p>
                        
                        <!-- Contador Externo -->
                        <div style="background: #f1f5f9; padding: 25px; border-radius: 25px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center; margin-bottom: 40px;">
                            <span style="font-size: 11px; font-weight: 800; color: #0d2838; opacity: 0.6; text-transform: uppercase; letter-spacing: 1px; border-right: 2px solid rgba(0,0,0,0.1); padding-right: 20px; margin-right: 5px;">INICIO DE<br>CLASES</span>
                            <div id="extCountdown" style="display: flex; gap: 15px;">
                                <div class="time-block-ext">
                                    <span id="extDays">00</span>
                                    <label>DÍAS</label>
                                </div>
                                <div class="time-block-ext">
                                    <span id="extHours">00</span>
                                    <label>HORAS</label>
                                </div>
                                <div class="time-block-ext">
                                    <span id="extMinutes">00</span>
                                    <label>MIN</label>
                                </div>
                                <div class="time-block-ext">
                                    <span id="extSeconds">00</span>
                                    <label>SEG</label>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                            <a href="/contacto" class="btn-enroll-pulse" style="padding: 22px 40px; font-size: 16px; min-width: 280px; justify-content: center;">
                                <i class="fas fa-paper-plane" style="margin-right: 12px;"></i> QUIERO MÁS INFORMACIÓN
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Modal Premium Mejorado -->
    <div id="resultModal" style="display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(13, 40, 56, 0.4); backdrop-filter: blur(20px);">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 32px; width: 95%; max-width: 1450px; height: 92vh; display: flex; flex-direction: column; box-shadow: 0 80px 160px rgba(0,0,0,0.7); overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
            
            <!-- Header Profesional -->
            <div style="padding: 15px 30px; background: linear-gradient(90deg, #0d2838 0%, #1a3a5a 100%); color: white; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #8cc63f; position: relative; overflow: hidden; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 15px; position: relative; z-index: 2; flex: 1; min-width: 0;">
                    <div style="flex-shrink: 0; background: transparent;">
                        <img src="{{ asset('assets/images/logo cepre.png') }}" style="height: 38px; width: auto; object-fit: contain;">
                    </div>
                    <div style="display: flex; flex-direction: column; min-width: 0;">
                        <span style="font-size: 8px; font-weight: 800; color: #8cc63f; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 2px;" class="d-none d-sm-block">VISOR DE RESULTADOS OFICIAL</span>
                        <h3 id="resultModalTitle" class="modal-title-dynamic">Cargando documento...</h3>
                    </div>
                </div>

                <button onclick="closeResultModal()" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1); color: white; width: 38px; height: 38px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; flex-shrink: 0;" onmouseover="this.style.background='#ef4444'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                    <i class="fas fa-times" style="font-size: 16px;"></i>
                </button>
            </div>

            <div style="flex: 1; background: #eef2f7; position: relative; overflow: hidden;">
                <!-- Cargador -->
                <div id="loaderSpinner" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #0d2838; z-index: 10;">
                    <i class="fas fa-circle-notch fa-spin" style="font-size: 40px; color: #8cc63f; margin-bottom: 15px;"></i>
                    <p style="font-weight: 700; font-size: 14px;">Preparando visualización segura...</p>
                </div>

                <!-- Saludo Innovador (Para Resultados Finales) -->
                <div id="goldGreeting" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 100; background: linear-gradient(135deg, rgba(13, 40, 56, 0.4) 0%, rgba(26, 58, 90, 0.4) 100%); flex-direction: column; align-items: center; justify-content: center; text-align: center; color: white; padding: 40px; transition: all 1s ease-out;">
                    <div class="greeting-content" style="transform: scale(0.9); opacity: 0; transition: all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                        <i class="fas fa-university" style="font-size: 80px; color: #d4af37; margin-bottom: 30px; filter: drop_shadow(0 0 20px rgba(212, 175, 55, 0.4));"></i>
                        <h2 style="font-size: 42px; font-weight: 900; letter-spacing: -1px; margin-bottom: 20px;">
                            <span style="color: white; display: block; font-size: 28px; font-weight: 700; margin-bottom: 10px; opacity: 0.9;">¡FELICIDADES,</span>
                            <span style="color: #d4af37; text-shadow: 0 0 30px rgba(212, 175, 55, 0.5);">FUTURO UNIVERSITARIO!</span>
                        </h2>
                        <p style="font-size: 18px; color: #cbd5e1; max-width: 600px; line-height: 1.6; margin-bottom: 40px;">Tu esfuerzo hoy se convierte en la recompensa del mañana. Bienvenido a una nueva etapa llena de éxitos en la <strong>UNAMAD</strong>.</p>
                        
                        <!-- Barra de Carga Innovadora -->
                        <div style="width: 300px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 10px; margin: 0 auto 40px; position: relative; overflow: hidden;">
                            <div id="greetingProgress" style="position: absolute; top: 0; left: 0; height: 100%; width: 0%; background: linear-gradient(90deg, #8cc63f, #d4af37); transition: width 4.5s linear; border-radius: 10px; box-shadow: 0 0 15px rgba(212, 175, 55, 0.5);"></div>
                        </div>

                        <div style="display: flex; gap: 15px; justify-content: center;">
                            <span style="background: rgba(212, 175, 55, 0.2); border: 1px solid #d4af37; color: #d4af37; padding: 8px 20px; border-radius: 50px; font-size: 13px; font-weight: 800;">INTEGRIDAD</span>
                            <span style="background: rgba(140, 198, 63, 0.2); border: 1px solid #8cc63f; color: #8cc63f; padding: 8px 20px; border-radius: 50px; font-size: 13px; font-weight: 800;">EXCELENCIA</span>
                            <span style="background: rgba(0, 174, 239, 0.2); border: 1px solid #00aeef; color: #00aeef; padding: 8px 20px; border-radius: 50px; font-size: 13px; font-weight: 800;">MÉRITO</span>
                        </div>
                    </div>
                </div>

                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden;">
                    <iframe id="resultViewer" src="" style="width: 100%; height: calc(100% + 55px); border: none; position: absolute; top: -55px; z-index: 2;" onload="document.getElementById('loaderSpinner').style.display='none'"></iframe>
                </div>
            </div>

            <div class="modal-footer-responsive" style="padding: 18px 45px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; flex-shrink: 0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-shield-check" style="color: #8cc63f; font-size: 14px;"></i>
                    <span class="footer-text-mobile">CERTIFICACIÓN OFICIAL DE RESULTADOS UNAMAD — {{ date('Y') }}</span>
                </div>
                <div style="display: flex; gap: 15px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #8cc63f; opacity: 0.5;"></div>
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #00aeef; opacity: 0.5;"></div>
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #ec008c; opacity: 0.5;"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');

        /* --- Fix Filtro Ciclos (Desactivar NiceSelect Global) --- */
        #cicloSelectNative {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            width: auto !important;
            height: auto !important;
            pointer-events: auto !important;
        }

        /* --- Global Customizations --- */
        .academic-notebook-pattern {
            background-color: #ffffff;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 30px 30px;
        }

        /* --- Premium Results Grid --- */
        .results-premium-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 30px;
        }

        /* --- Glass Res Card --- */
        .glass-res-card {
            background: white;
            border-radius: 24px;
            padding: 0;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            min-height: 380px;
        }

        .glass-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.8), rgba(255,255,255,0.4));
            backdrop-filter: blur(5px);
            z-index: 1;
            opacity: 0;
            transition: opacity 0.4s;
        }

        .card-accent {
            height: 6px;
            width: 100%;
            transition: height 0.3s;
        }

        .glass-res-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 40px 80px rgba(13, 40, 56, 0.15);
            border-color: rgba(0,0,0,0.1);
        }

        .glass-res-card:hover .glass-bg { opacity: 1; }
        .glass-res-card:hover .card-accent { height: 10px; }

        .exam-number-bg {
            position: absolute;
            bottom: -5px;
            right: -10px;
            font-size: 190px;
            font-weight: 900;
            line-height: 0.5;
            z-index: 2;
            font-family: 'Playfair Display', 'Georgia', serif;
            font-style: italic;
            pointer-events: none;
            transition: all 0.8s cubic-bezier(0.19, 1, 0.22, 1);
            letter-spacing: -12px;
            /* Suprimimos opacidad aquí para controlarla en inline style del blade */
        }

        .glass-res-card:hover .exam-number-bg {
            transform: scale(1.05) translateX(-15px) rotate(-1deg);
            opacity: 0.45 !important;
        }

        /* Card Top Area */
        .card-top {
            padding: 25px 30px 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            z-index: 2;
        }

        .card-logo-box {
            width: 130px;
            height: auto;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .cepre-card-logo {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .glass-res-card:hover .card-logo-box {
            transform: scale(1.05);
        }

        .date-tag {
            text-align: right;
            line-height: 1.1;
        }

        .date-label {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 5px;
            text-transform: uppercase;
            opacity: 0.8;
        }

        .date-values {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .date-tag .day {
            font-size: 32px;
            font-weight: 900;
            color: #0d2838;
        }

        .date-tag .month {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        /* Card Info Area */
        .card-info {
            padding: 20px 30px;
            flex-grow: 1;
            position: relative;
            z-index: 2;
        }

        .official-label {
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 2px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .res-title {
            font-size: 20px;
            font-weight: 850;
            color: #0d2838;
            line-height: 1.3;
            margin: 0 0 12px 0;
            font-family: 'Inter', sans-serif;
        }

        .res-desc {
            font-size: 14.5px;
            color: #64748b;
            line-height: 1.6;
            margin: 0;
        }

        /* Card Action Area */
        .card-action {
            padding: 25px 30px 40px;
            position: relative;
            z-index: 2;
        }

        .action-btn {
            width: 100%;
            height: 52px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: white;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .glass-res-card:hover .action-btn {
            transform: scale(1.03);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .action-btn i { transition: transform 0.3s; }
        .glass-res-card:hover .action-btn i { transform: translateX(5px); }

        .shine-effect {
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.4), transparent);
            transform: skewX(-25deg);
            transition: left 0.7s;
        }

        .glass-res-card:hover .shine-effect { left: 150%; }

        /* --- Gold Edition Premium Styles --- */
        .glass-res-card.gold-edition {
            background: linear-gradient(to bottom, #ffffff, #fffdf0);
            border: 2px solid #d4af37;
            box-shadow: 0 15px 45px rgba(212, 175, 55, 0.12);
        }
        
        .gold-edition:hover {
            box-shadow: 0 40px 80px rgba(212, 175, 55, 0.25) !important;
            border-color: #f9d71c !important;
        }

        /* --- Innovative Badge --- */
        .merito-badge-innovative {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #d4af37 0%, #a67c00 100%);
            color: white;
            padding: 6px 20px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 1.5px;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(166, 124, 0, 0.4);
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pulse-ring {
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            position: relative;
        }

        .pulse-ring::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #fff;
            border-radius: 50%;
            animation: badge-pulse 2s infinite;
        }

        @keyframes badge-pulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(3.5); opacity: 0; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .results-premium-grid { grid-template-columns: 1fr; gap: 25px; }
            .glass-res-card { min-height: auto; }
            .section-title { flex-direction: column; align-items: flex-start !important; }
            .merito-badge-innovative { font-size: 9px; padding: 4px 12px; }
            
            /* Responsive greeting */
            #goldGreeting { padding: 20px !important; }
            #goldGreeting h2 { font-size: 28px !important; }
            #goldGreeting h2 span:first-child { font-size: 18px !important; }
            #goldGreeting p { font-size: 14px !important; margin-bottom: 25px !important; }
            #goldGreeting i.fa-university { font-size: 50px !important; margin-bottom: 20px !important; }
            .greeting-content div[style*="width: 300px"] { width: 80% !important; margin-bottom: 30px !important; }
            .greeting-content div[style*="display: flex"] { flex-wrap: wrap; gap: 10px !important; }
            .greeting-content span[style*="padding: 8px 20px"] { padding: 6px 15px !important; font-size: 11px !important; }
        }

        @media (max-width: 480px) {
            #goldGreeting h2 { font-size: 24px !important; }
            #goldGreeting p { font-size: 13px !important; }
            .greeting-content span[style*="padding: 6px 15px"] { flex: 1 1 40%; text-align: center; }
        }

        /* --- New Cycle Promo Styles --- */
        .btn-enroll-pulse {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            background: linear-gradient(135deg, #8cc63f 0%, #76ad32 100%);
            color: #0d2838;
            padding: 22px 45px;
            border-radius: 60px;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-decoration: none;
            box-shadow: 0 15px 35px rgba(140, 198, 63, 0.3);
            transition: all 0.4s;
            position: relative;
            animation: pulse-border 2.5s infinite;
        }

        /* --- Header Integrated Timer --- */
        .modal-title-dynamic {
            font-size: 22px; 
            font-weight: 900; 
            margin: 0; 
            color: white; 
            font-family: 'Inter', sans-serif; 
            letter-spacing: -1px;
            line-height: 1.1;
            word-break: break-word;
        }

        @media (max-width: 991px) {
            .modal-title-dynamic {
                font-size: 14px !important;
                letter-spacing: -0.5px !important;
                font-weight: 800 !important;
            }
            .modal-content-premium {
                padding: 10px 20px !important;
            }
            .modal-footer-responsive {
                padding: 10px 20px !important;
            }
            .footer-text-mobile {
                font-size: 9px !important;
            }
        }

        .btn-enroll-pulse:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 45px rgba(140, 198, 63, 0.4);
            color: #0d2838;
        }

        /* --- Pink Premium Button --- */
        .btn-pink-premium {
            background: linear-gradient(135deg, #ff0080 0%, #7928ca 100%);
            color: white;
            border: none;
            padding: 22px 30px;
            border-radius: 60px;
            font-weight: 900;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.4s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 15px 35px rgba(255, 0, 128, 0.3);
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
            width: 100%;
        }

        .btn-pink-premium:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 25px 45px rgba(255, 0, 128, 0.5);
            letter-spacing: 1px;
            color: white;
        }

        .btn-pink-premium::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(45deg);
            transition: 0.8s;
            pointer-events: none;
        }

        .btn-pink-premium:hover::after {
            left: 100%;
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(140, 198, 63, 0.4); }
            70% { box-shadow: 0 0 0 25px rgba(140, 198, 63, 0); }
            100% { box-shadow: 0 0 0 0 rgba(140, 198, 63, 0); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(15deg); }
            50% { transform: translateY(-15px) rotate(15deg); }
        }

        @media (max-width: 991px) {
            .promo-box { padding: 40px !important; }
            .promo-box h2 { font-size: 34px !important; }
            .btn-enroll-pulse { padding: 18px 30px !important; font-size: 13px !important; width: 100%; justify-content: center; }
            
            .external-recruitment-flyer { margin-top: 50px !important; border-radius: 25px !important; }
            .external-recruitment-flyer h2 { font-size: 32px !important; }
            .external-recruitment-flyer p { font-size: 15px !important; }
            .external-recruitment-flyer div[style*="padding: 60px 80px"] { padding: 40px 30px !important; }
        }

        /* --- External Timer Styles --- */
        .time-block-ext {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 65px;
            background: white;
            padding: 10px 5px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .time-block-ext span {
            font-size: 24px;
            font-weight: 900;
            color: #ff0080;
            line-height: 1;
        }
        .time-block-ext label {
            font-size: 8px;
            font-weight: 800;
            color: #94a3b8;
            margin-top: 5px;
            text-transform: uppercase;
        }

        /* --- Global Customizations --- */
    </style>



    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        function openResultModal(url, title, isLink, isGold = false) {
            const modal = document.getElementById('resultModal');
            const viewer = document.getElementById('resultViewer');
            const modalTitle = document.getElementById('resultModalTitle');
            const loader = document.getElementById('loaderSpinner');
            const greeting = document.getElementById('goldGreeting');

            // Limpieza de título profesional
            let cleanedTitle = title.replace(/\?|✨|✅|🔥|🚀|📈/g, '').replace(/\s+/g, ' ').trim();
            
            let finalUrl = url;
            if (isLink && url.includes('drive.google.com')) {
                if (url.includes('/view') || url.includes('/edit')) {
                    finalUrl = url.replace(/\/view.*|\/edit.*/, '/preview');
                }
            } else if (!isLink) {
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                if (isMobile) {
                    finalUrl = `https://docs.google.com/gview?url=${encodeURIComponent(url)}&embedded=true`;
                } else {
                    finalUrl += '#toolbar=0';
                }
            }

            if (loader) loader.style.display = 'block';
            viewer.src = finalUrl;
            modalTitle.textContent = cleanedTitle;
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';

            // Greeting Logic for Gold Edition
            if (isGold && greeting) {
                greeting.style.display = 'flex';
                const content = greeting.querySelector('.greeting-content');
                const progressBar = document.getElementById('greetingProgress');
                
                if (progressBar) progressBar.style.width = '0%';
                
                setTimeout(() => {
                    content.style.opacity = '1';
                    content.style.transform = 'scale(1)';
                    if (progressBar) progressBar.style.width = '100%';
                }, 100);

                // Auto-fade greeting to show results after "loading"
                setTimeout(() => {
                    greeting.style.opacity = '0';
                    setTimeout(() => {
                        greeting.style.display = 'none';
                        greeting.style.opacity = '1'; // Reset for next time
                        if (progressBar) progressBar.style.width = '0%';
                        content.style.opacity = '0';
                        content.style.transform = 'scale(0.9)';
                    }, 1000);
                }, 5500);
            } else if (greeting) {
                greeting.style.display = 'none';
            }

            // Celebration
            setTimeout(() => {
                const end = Date.now() + (isGold ? 4000 : 2000);
                const colors = isGold ? ['#d4af37', '#f9d71c', '#ffffff', '#8cc63f'] : ['#8cc63f', '#ec008c', '#00aeef', '#ffffff'];
                (function frame() {
                    confetti({ 
                        particleCount: isGold ? 6 : 3, 
                        angle: 60, 
                        spread: 60, 
                        origin: { x: 0 }, 
                        colors: colors, 
                        zIndex: 20000,
                        scalar: isGold ? 1.4 : 1
                    });
                    confetti({ 
                        particleCount: isGold ? 6 : 3, 
                        angle: 120, 
                        spread: 60, 
                        origin: { x: 1 }, 
                        colors: colors, 
                        zIndex: 20000,
                        scalar: isGold ? 1.4 : 1
                    });
                    if (Date.now() < end) requestAnimationFrame(frame);
                }());
            }, 200);
        }

        function closeResultModal() {
            document.getElementById('resultModal').style.display = 'none';
            document.getElementById('resultViewer').src = 'about:blank';
            document.body.style.overflow = 'auto';
            clearInterval(countdownInterval);
        }

        /* --- Countdown Logic Only --- */
        let countdownInterval;

        function startExternalCountdown() {
            const targetDate = new Date('April 1, 2026 00:00:00').getTime();
            function update() {
                const now = new Date().getTime();
                const distance = targetDate - now;
                if (distance < 0) { clearInterval(countdownInterval); return; }
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                const d = document.getElementById('extDays'), h = document.getElementById('extHours'), m = document.getElementById('extMinutes'), s = document.getElementById('extSeconds');
                if(d) d.innerText = String(days).padStart(2, '0');
                if(h) h.innerText = String(hours).padStart(2, '0');
                if(m) m.innerText = String(minutes).padStart(2, '0');
                if(s) s.innerText = String(seconds).padStart(2, '0');
            }
            update();
            countdownInterval = setInterval(update, 1000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            startExternalCountdown();
        });

        document.addEventListener('keydown', (e) => { 
            if (e.key === 'Escape') closeResultModal();
        });
    </script>

    @push('js')
    <script>
        $(document).ready(function() {
            // Desactivar NiceSelect para el filtro de ciclos (evita fondo azul y recortes)
            if ($.fn.niceSelect) {
                $('#cicloSelectNative').niceSelect('destroy');
                // Forzar visualización nativa y ocultar el clon de NiceSelect si se creó
                $('#cicloSelectNative').show().css('display', 'block');
                $('#cicloSelectNative').next('.nice-select').remove();
            }
        });
    </script>
    @endpush

    @include('partials.cepre.footer')
    @include('partials.cepre.scripts')
@endsection
