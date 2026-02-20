<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Carga de Three.js para el fondo 3D -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        /* ==================================== */
        /* 1. Variables y Reset Global */
        /* ==================================== */
        :root {
            --verde-cepre: #8cc63f; /* Refinado a valor institucional exacto */
            --magenta-unamad: #ec008c; /* Refinado a valor institucional exacto */
            --cyan-acento: #00aeef; /* Refinado a valor institucional exacto */
            --azul-oscuro: #2b5a6f; /* Refinado a valor institucional exacto */
            --verde-claro: #a4c639;
            --fondo-cursos: #ffffff;
            --white-glass: rgba(255, 255, 255, 0.8);
            --shadow-premium: 0 10px 30px rgba(0, 0, 0, 0.05);
            --transition-smooth: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            
            /* Colores de Fondo Académico */
            --paper-bg: #ffffff;
            --grid-line: rgba(0, 160, 227, 0.08);
            --margin-line: rgba(236, 0, 140, 0.15);
        }

        /* ==================================== */
        /* Patrón Amazónico (Kené) */
        /* ==================================== */
        .kene-pattern-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("{{ asset('assets_cepre/img/tejido-kene-final.png') }}");
            background-size: 200px 200px;
            pointer-events: none;
            z-index: 1;
            opacity: 0.12; /* Ajustado para la imagen real en secciones blancas/claras */
        }

        /* ==================================== */
        /* Patrón de Cuaderno Académico */
        /* ==================================== */
        .academic-notebook-pattern {
            background-color: var(--paper-bg) !important;
            background-image: 
                /* Líneas horizontales */
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                /* Líneas verticales */
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px) !important;
            background-size: 30px 30px !important;
            position: relative;
        }

        /* Margen de Cuaderno Institucional */
        .academic-notebook-pattern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 60px;
            width: 2px;
            height: 100%;
            background-color: var(--margin-line);
            z-index: 0;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .academic-notebook-pattern::before {
                left: 30px;
            }
        }

        /* Efecto de Papel Rasgado (Torn Paper) */
        .torn-paper-edge {
            position: relative;
            height: 30px;
            background: var(--paper-bg);
            margin-top: -15px;
            z-index: 10;
            clip-path: polygon(0% 0%, 5% 40%, 10% 0%, 15% 50%, 20% 0%, 25% 30%, 30% 0%, 35% 60%, 40% 0%, 45% 20%, 50% 0%, 55% 40%, 60% 0%, 65% 50%, 70% 0%, 75% 30%, 80% 0%, 85% 60%, 90% 0%, 95% 20%, 100% 0%, 100% 100%, 0% 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden; 
            background-color: #f0f2f5;
        }
        
        /* Aseguramos que los contenedores no excedan el ancho del viewport */
        section, header, footer, div {
            max-width: 100vw;
        }

        /* ==================================== */
        /* 2. Keyframes (Animaciones) */
        /* ==================================== */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } }
        @keyframes shake { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(-3deg); } 75% { transform: rotate(3deg); } }
        @keyframes scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes ripple { 0% { box-shadow: 0 0 0 0 rgba(164, 198, 57, 0.7); } 100% { box-shadow: 0 0 0 20px rgba(164, 198, 57, 0); } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        @keyframes rainbow { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes slideInContent { 
            0% { opacity: 0; transform: translateY(30px); } 
            100% { opacity: 1; transform: translateY(0); } 
        }
        /* Animación de latido para el asistente virtual */
        @keyframes softPulse { 0% { box-shadow: 0 0 0 0 rgba(0, 160, 227, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(0, 160, 227, 0); } 100% { box-shadow: 0 0 0 0 rgba(0, 160, 227, 0); } }
        /* Animación para la burbuja de notificación */
        @keyframes bubbleInOut {
            0% { opacity: 0; transform: scale(0.8) translateX(20px); }
            10% { opacity: 1; transform: scale(1) translateX(0); }
            90% { opacity: 1; transform: scale(1) translateX(0); }
            100% { opacity: 0; transform: scale(0.8) translateX(20px); }
        }
        /* Animación de parpadeo de bombilla (nueva) */
        @keyframes lightbulbBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }


        .animate-on-scroll {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* ==================================== */
        /* 3. Top Bar y Header */
        /* ==================================== */
        .top-bar {
            background: linear-gradient(135deg, var(--verde-cepre) 0%, var(--verde-claro) 100%);
            color: white;
            padding: 8px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            animation: slideDown 0.5s ease-out;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .top-bar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0l15 15-15 15-15-15zM30 60l15-15-15-15-15 15z' fill='%23ffffff' fill-opacity='0.12'/%3E%3C/svg%3E");
            background-size: 30px 30px;
            pointer-events: none;
        }

        .top-bar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .help-desk {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .help-desk i {
            color: var(--magenta-unamad);
            animation: shake 2s ease-in-out infinite;
        }

        .info-links {
             display: flex; /* Asegura que se muestren en desktop */
             gap: 20px;
        }
        .info-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding: 5px 0;
        }
        .info-links a:hover { color: var(--cyan-acento); transform: translateY(-2px); }

        /* Header Principal */
        .main-header {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition-smooth);
        }
        .main-header.scrolled {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 5px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: nowrap;
            gap: 15px;
        }

        .logo {
            height: 60px;
            transition: transform 0.3s;
            display: block;
            flex-shrink: 0;
        }
        .logo:hover {
            transform: scale(1.05) rotate(-2deg);
        }

        /* Menú de Navegación */
        .nav-menu {
            display: flex;
            gap: 25px;
            list-style: none;
            align-items: center;
        }
        .nav-menu a {
            color: var(--azul-oscuro);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            position: relative;
            padding: 5px 0;
            transition: all 0.3s;
        }
        .nav-menu a:hover { color: var(--verde-cepre); transform: translateY(-2px); }
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--magenta-unamad), var(--cyan-acento));
            transition: width 0.3s;
            border-radius: 2px;
        }
        .nav-menu a:hover::after { width: 100%; }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }
        .btn span { position: relative; z-index: 2; }
        .btn-primary { 
            background: linear-gradient(135deg, var(--verde-cepre), var(--verde-claro)); 
            color: white;
            box-shadow: 0 4px 15px rgba(164, 198, 57, 0.3);
        }
        .btn-primary:active { transform: scale(0.95); }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }
        .btn-primary:hover::after {
            left: 100%;
        }
        .btn-secondary { background: linear-gradient(135deg, var(--magenta-unamad), #ff1a8c); color: white; }
        .btn-primary:hover, .btn-secondary:hover { transform: translateY(-3px); }

        .search-icon {
            font-size: 20px;
            color: var(--azul-oscuro);
            cursor: pointer;
            transition: all 0.3s;
        }

        /* ==================================== */
        /* 4. Hero Section (MODIFICADO PARA CARRUSEL) */
        /* ==================================== */
        .hero-section {
            background: var(--azul-oscuro); 
            min-height: 340px; 
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 0;
        }
        
        /* Contenedor para el Canvas 3D (Fondo estático) */
        #hero-canvas-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0; /* Fondo */
            opacity: 1.0; 
        }
        
        /* El overlay se mantiene para el color base */
        .hero-bg-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(44, 95, 124, 0.2); 
            backdrop-filter: blur(0);
            z-index: 1; 
        }

        /* --- CAROUSEL STYLES --- */
        .carousel-container {
            position: relative;
            width: 100%;
            height: 100%; /* Ocupa todo el espacio de .hero-section */
            overflow: hidden;
            z-index: 2; /* Sobre el fondo 3D */
        }

        .carousel-slides {
            display: flex;
            height: 100%;
            transition: transform 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .carousel-slide {
            min-width: 100%;
            box-sizing: border-box;
            padding: 10px 0; 
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
        
        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px; 
            align-items: center;
            position: relative;
            width: 100%;
            /* Animación de entrada para el contenido */
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
        }

        .carousel-slide.active .hero-content {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* --- NAVEGACIÓN (FLECHAS) --- */
        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: none;
            padding: 15px;
            cursor: pointer;
            z-index: 3;
            font-size: 24px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background 0.3s;
            backdrop-filter: blur(3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .carousel-nav:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .prev-nav { left: 30px; }
        .next-nav { right: 30px; }
        
        /* --- PUNTOS (DOTS) --- */
        .carousel-dots {
            position: absolute;
            bottom: 20px; 
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 100;
        }
        .dot {
            width: 12px;
            height: 12px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .dot.active {
            background-color: var(--verde-cepre);
            border: 2px solid white;
            transform: scale(1.2);
        }
        /* --- FIN CAROUSEL STYLES --- */


        .hero-text { color: white; text-align: left; }
        
        .hero-subtitle { 
            /* ESTILO CURSIVO Y RESALTADO */
            font-family: cursive; 
            font-size: 28px; 
            color: var(--cyan-acento); 
            margin-bottom: 15px;
            font-style: italic;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .hero-title { 
            font-size: 64px; 
            font-weight: 850; 
            line-height: 1.05; 
            margin-bottom: 20px;
            color: white;
            text-shadow: 0 10px 20px rgba(0,0,0,0.2);
            letter-spacing: -0.02em;
        }
        
        /* Interacción: Hace que el título brille un poco más al hacer hover */
        .hero-title:hover {
            text-shadow: 
                0 0 15px rgba(255,255,255,0.8), 
                2px 2px var(--verde-cepre), 
                4px 4px var(--cyan-acento),
                6px 6px var(--azul-oscuro); 
        }

        .hero-title span {
            /* Mantiene el gradiente y el bounce solo para la palabra clave */
            background: linear-gradient(90deg, var(--verde-cepre), var(--verde-claro), var(--cyan-acento));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
            animation: bounce 2s ease-in-out infinite;
            text-shadow: none; /* Elimina la sombra 3D del span para que se vea el gradiente */
        }

        .hero-image-wrapper {
            position: relative; 
            padding: 0; 
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.4s ease-in-out;
            cursor: pointer;
            overflow: hidden; 
            min-height: 400px; 
            background-color: var(--azul-oscuro);
        }
        
        /* INTERACTIVIDAD HERO: Mueve la imagen y el badge al hacer hover */
        .hero-image-wrapper:hover {
            transform: scale(1.02); 
        }

        .hero-image {
            width: 100%;
            height: 100%;
            border-radius: 12px; 
            box-shadow: none; 
            transition: all 0.4s ease-in-out;
            display: block;
            object-fit: contain;
            object-position: center;
        }
        
        .hero-image-wrapper:hover .hero-image {
            transform: scale(1.05); 
            filter: brightness(1.05);
        }
        
        /* --- CAJA DE ESTADÍSTICAS RECTANGULAR Y MODERNA --- */
        .stats-badge {
            position: absolute;
            bottom: 20px; 
            left: 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(240, 240, 240, 0.95));
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            text-align: center;
            border-top: 4px solid var(--magenta-unamad);
            max-width: 250px;
            transition: transform 0.4s ease-in-out, box-shadow 0.4s;
            transform: none; 
            width: auto; 
            height: auto; 
            display: block;
            box-sizing: border-box; 
        }
        
        .stats-badge > * {
            transform: none; 
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }

        .hero-image-wrapper:hover .stats-badge {
            /* Se eleva y se mueve sutilmente en el hover de la imagen */
            transform: translate(0, -10px) scale(1.05); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .stats-badge p {
            font-size: 14px;
            color: var(--azul-oscuro);
            font-weight: 600;
        }

        .stats-badge h2 {
            background: linear-gradient(135deg, var(--verde-cepre), var(--cyan-acento));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 38px;
            font-weight: 900;
            margin: 5px 0;
        }
        /* --- FIN CAJA DE ESTADÍSTICAS --- */

        .video-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--magenta-unamad);
            font-size: 20px;
            position: relative;
            cursor: pointer;
        }
        .video-btn::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid var(--magenta-unamad);
            animation: ripple 2s infinite;
        }

        /* ==================================== */
        /* 14. Asistente Flotante (Avatar) */
        /* ==================================== */
        #floating-assistant {
            position: fixed;
            bottom: 100px; /* Separado del botón ScrollTop */
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--cyan-acento), #00bfff);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1500;
            box-shadow: 0 4px 15px rgba(0, 160, 227, 0.5);
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            animation: softPulse 2s infinite;
        }
        #floating-assistant:hover {
            transform: scale(1.1);
            animation: none;
            box-shadow: 0 8px 20px rgba(0, 160, 227, 0.8);
        }
        /* Nueva animación de parpadeo para el icono de la bombilla */
        #floating-assistant i {
            animation: lightbulbBlink 1.5s ease-in-out infinite;
        }
        
        /* Burbuja de Notificación */
        #assistant-bubble {
            position: fixed;
            bottom: 110px; /* Alineado verticalmente con el asistente */
            right: 100px; /* Separado del asistente */
            background: white;
            color: var(--azul-oscuro);
            padding: 10px 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 200px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            z-index: 1500;
            opacity: 0; /* Inicialmente oculto */
            pointer-events: none; /* No interfiere con clics */
            animation: bubbleInOut 5s ease-in-out 1s forwards; /* Muestra por 5s después de 1s */
        }
        #assistant-bubble::after {
            content: '';
            position: absolute;
            right: -8px;
            bottom: 15px;
            width: 0;
            height: 0;
            border-left: 10px solid white;
            border-top: 10px solid transparent;
            border-bottom: 10px solid transparent;
        }


        /* Ajuste de posición en móvil para no colisionar con el botón ScrollTop */
        @media (max-width: 768px) {
            #floating-assistant {
                bottom: 90px;
                right: 15px;
            }
            #assistant-bubble {
                bottom: 100px;
                right: 80px;
                max-width: 150px;
                font-size: 12px;
                padding: 8px 10px;
            }
            #assistant-bubble::after {
                bottom: 10px;
            }
        }
        
        /* ==================================== */
        /* 5. Marquee */
        /* ==================================== */
        .marquee-section {
            background: linear-gradient(90deg, var(--magenta-unamad), var(--verde-cepre), var(--cyan-acento), var(--magenta-unamad));
            background-size: 200% 100%;
            animation: rainbow 12s linear infinite;
            padding: 8px 0;
            overflow: hidden;
            box-shadow: inset 0 5px 15px rgba(0,0,0,0.05), 0 4px 15px rgba(0,0,0,0.1);
            border-top: 1px solid rgba(255,255,255,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
        }

        .marquee-content {
            display: flex;
            animation: scroll 45s linear infinite;
            width: 200%; 
        }
        .marquee-content:hover { animation-play-state: paused; }

        .marquee-item {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 0 40px;
            white-space: nowrap;
            font-weight: 600;
            color: white;
            transition: var(--transition-smooth);
            font-size: 14px;
            letter-spacing: 0.5px;
            position: relative;
        }
        .marquee-item::after {
            content: '';
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background: rgba(255,255,255,0.3);
        }
        .marquee-item i { 
            font-size: 18px;
            opacity: 0.9;
            transition: transform 0.3s ease;
        }
        .marquee-item:hover i {
            transform: scale(1.2) rotate(10deg);
            opacity: 1;
        }

        /* ==================================== */
        /* 6. Secciones Generales y Títulos */
        /* ==================================== */
        .courses-section, .teachers-section, .stats-section {
            padding: 80px 0;
        }
        
        .courses-section {
            background: var(--paper-bg);
            position: relative;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-title h6 { color: var(--magenta-unamad); text-transform: uppercase; letter-spacing: 2px; }
        .section-title h2 { font-size: 42px; font-weight: 800; color: var(--azul-oscuro); }

        /* ==================================== */
        /* 7. Courses Section (Cursos) */
        /* ==================================== */
        .courses-grid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .course-card {
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05); 
            transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-top: 5px solid var(--verde-cepre);
            cursor: pointer;
            position: relative;
        }
        .course-card::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);
            transform: skewX(-25deg);
            transition: 0.7s;
        }
        .course-card:hover::after {
            left: 125%;
        }
        .course-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
            border-color: rgba(255, 255, 255, 0.8);
        }

        /* Configuración de colores de las tarjetas de curso */
        .course-card:nth-child(2) { border-top-color: var(--magenta-unamad); }
        .course-card:nth-child(3) { border-top-color: var(--cyan-acento); }
        .course-card:nth-child(4) { border-top-color: var(--azul-oscuro); }
        .course-card:nth-child(5) { border-top-color: var(--magenta-unamad); }
        .course-card:nth-child(6) { border-top-color: var(--cyan-acento); }

        .course-card:nth-child(1) .course-icon { background: linear-gradient(135deg, var(--verde-cepre), var(--verde-claro)); }
        .course-card:nth-child(2) .course-icon { background: linear-gradient(135deg, var(--magenta-unamad), #ff1a8c); }
        .course-card:nth-child(3) .course-icon { background: linear-gradient(135deg, var(--cyan-acento), #00bfff); }
        .course-card:nth-child(4) .course-icon { background: linear-gradient(135deg, var(--azul-oscuro), #3a7fa2); }
        .course-card:nth-child(5) .course-icon { background: linear-gradient(135deg, var(--magenta-unamad), #ff1a8c); }
        .course-card:nth-child(6) .course-icon { background: linear-gradient(135deg, var(--cyan-acento), #00bfff); }

        .course-icon { 
            padding: 40px; 
            text-align: center; 
            color: white; 
            position: relative;
            overflow: hidden;
        }
        .course-icon i { 
            font-size: 52px; 
            transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); 
            position: relative;
            z-index: 2;
        }
        .course-card:hover .course-icon i { 
            transform: scale(1.15) rotate(10deg); 
        }

        .course-content { padding: 25px; }
        .course-content h3 { color: var(--azul-oscuro); }
        .course-content p { color: #666; font-weight: 600; }

        /* ==================================== */
        /* 8. Stats Section (Estadísticas) */
        /* ==================================== */
        .stats-section {
            background: var(--verde-cepre);
            background: linear-gradient(135deg, var(--verde-cepre), var(--verde-claro));
            color: white;
            position: relative;
            overflow: hidden;
            padding: 100px 0;
        }

        /* Formas flotantes de fondo para darle profundidad */
        .stats-section::before, .stats-section::after {
            content: '';
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 0;
            animation: float 15s infinite alternate;
        }
        .stats-section::before {
            width: 300px; height: 300px;
            top: -100px; left: -100px;
        }
        .stats-section::after {
            width: 200px; height: 200px;
            bottom: -50px; right: -50px;
            animation-delay: -5s;
        }

        @keyframes float {
            from { transform: translate(0, 0) rotate(0deg); }
            to { transform: translate(50px, 100px) rotate(45deg); }
        }

        .stats-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            position: relative;
            z-index: 1;
        }
        .stat-box {
            text-align: center;
            background: rgba(255,255,255,0.15);
            padding: 45px 30px;
            border-radius: 24px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.25);
            transition: all 0.4s ease;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .stat-box:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .stat-box i { font-size: 48px; margin-bottom: 20px; }
        .stat-box h3 { font-size: 48px; font-weight: 900; }

        /* ==================================== */
        /* 9. Teachers Section (Docentes) */
        /* ==================================== */
        .teachers-grid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .teacher-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-top: 5px solid var(--verde-cepre);
            cursor: pointer;
        }
        .teacher-card:hover { 
            transform: translateY(-15px); 
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }

        .teacher-image { width: 100%; height: 300px; overflow: hidden; }
        .teacher-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .teacher-card:hover .teacher-image img { transform: scale(1.1); }
        .teacher-info { padding: 25px; text-align: center; }
        .teacher-info h4 { color: var(--azul-oscuro); }
        .teacher-info p { color: var(--magenta-unamad); }


        /* ==================================== */
        /* 10. CTA Banner (Ingreso Directo) */
        /* ==================================== */
        .cta-banner { 
            margin-top: 40px; 
            overflow: hidden; 
            background: var(--azul-oscuro); 
            background-image: 
                radial-gradient(at 0% 0%, rgba(236, 0, 140, 0.15) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(0, 160, 227, 0.15) 0, transparent 50%),
                radial-gradient(at 50% 100%, rgba(140, 198, 63, 0.1) 0, transparent 50%);
            padding: 100px 0;
            text-align: center;
            position: relative;
        }

        .cta-banner::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("{{ asset('assets_cepre/img/tejido-kene-final.png') }}");
            background-size: 300px 300px;
            z-index: 1;
            opacity: 0.08;
        }
        
        .cta-banner-content { 
            position: relative; 
            z-index: 10; 
            text-align: center; 
            color: white; 
            max-width: 900px; 
            margin: 0 auto;
            padding: 0 30px;
        }
        .cta-banner-content h2 { 
            font-size: 48px; 
            font-weight: 800; 
            margin-bottom: 30px; 
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        /* Botón de CTA estilizado */
        .btn-cyan-cta {
            background: linear-gradient(135deg, var(--cyan-acento), #00bfff);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 160, 227, 0.5);
            padding: 15px 35px;
            font-size: 16px;
            border-radius: 50px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-weight: 700;
        }

        .btn-cyan-cta:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 30px rgba(0, 160, 227, 0.7);
        }

        /* ==================================== */
        /* 10.5 Contact Bar (Integrado y Limpio) */
        /* ==================================== */
        .contact-bar {
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 40px 0;
            color: var(--azul-oscuro);
            box-shadow: 0 -10px 30px rgba(0,0,0,0.03);
            border-top: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            z-index: 20;
            margin-top: -30px; /* Se traslapa sutilmente con el banner anterior para un look premium */
            border-radius: 40px 40px 0 0;
        }

        .contact-bar-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .contact-bar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .contact-bar-icon {
            width: 60px;
            height: 60px;
            background: var(--magenta-unamad); 
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 5px 15px rgba(236, 0, 140, 0.3);
        }

        .contact-bar:hover .contact-bar-icon {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 8px 25px rgba(236, 0, 140, 0.5);
        }

        .contact-bar-icon i {
            color: white;
            font-size: 24px;
        }
        
        .contact-bar-right {
            text-align: right;
        }

        .contact-bar-right h3 {
            font-size: 32px;
            font-weight: 800;
            margin: 5px 0 0 0;
            color: var(--magenta-unamad);
            transition: color 0.3s;
            cursor: pointer;
        }


        /* ==================================== */
        /* 11. Footer */
        /* ==================================== */
        footer {
            background: linear-gradient(135deg, var(--azul-oscuro), #1a3d52);
            padding: 80px 0 0 0;
            color: white;
            position: relative;
            overflow: hidden;
        }

        footer::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("{{ asset('assets_cepre/img/tejido-kene-final.png') }}");
            background-size: 300px 300px;
            z-index: 0;
            pointer-events: none;
            opacity: 0.04;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--verde-cepre), var(--magenta-unamad), var(--cyan-acento), var(--verde-cepre));
            background-size: 200% 100%;
            animation: rainbow 3s linear infinite;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        /* Estilos de Logo */
        .footer-logo { 
            height: 60px; 
            margin-bottom: 20px;
            filter: brightness(0) invert(1); 
            transition: transform 0.3s;
        }
        .footer-logo:hover { transform: scale(1.05) rotate(2deg); }

        /* Títulos */
        .footer-column h3 { 
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            display: inline-block;
            position: relative;
            color: var(--verde-cepre);
        }
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--verde-cepre), var(--cyan-acento));
            border-radius: 2px;
        }
        
        /* Enlaces */
        .footer-column ul { list-style: none; padding: 0; margin: 0; }
        .footer-column ul li { margin-bottom: 10px; transition: all 0.3s; }
        .footer-column ul li:hover { transform: translateX(5px); }
        .footer-column ul li a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            position: relative;
            padding-left: 20px;
            display: block;
        }
        .footer-column ul li a::before {
            content: '▶';
            position: absolute;
            left: 0;
            opacity: 0;
            transition: opacity 0.3s;
            color: var(--magenta-unamad);
            font-size: 10px;
            top: 4px;
        }
        .footer-column ul li a:hover::before { opacity: 1; }
        .footer-column ul li a:hover { color: var(--cyan-acento); }

        /* Redes Sociales */
        .social-links { display: flex; gap: 10px; margin-top: 20px; }
        .social-links a {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-size: 18px;
        }
        .social-links a:hover { 
            transform: translateY(-8px); 
            background: var(--verde-cepre); 
            box-shadow: 0 10px 20px rgba(140, 198, 63, 0.4);
            border-color: var(--verde-cepre);
        }
        .social-links a:nth-child(2):hover { background: var(--cyan-acento); box-shadow: 0 10px 20px rgba(0, 160, 239, 0.4); border-color: var(--cyan-acento); }
        .social-links a:nth-child(3):hover { background: var(--magenta-unamad); box-shadow: 0 10px 20px rgba(236, 0, 140, 0.4); border-color: var(--magenta-unamad); }
        
        /* Botones del Footer */
        .footer-buttons { display: flex; flex-direction: column; gap: 10px; }
        .footer-buttons a {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            font-size: 13px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .footer-buttons a::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.1); /* Brillo interno */
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
            z-index: 0;
        }
        .footer-buttons a:hover::before { width: 300px; height: 300px; }
        .footer-buttons a:hover { border-color: var(--verde-cepre); transform: scale(1.03); }
        .footer-buttons a span { position: relative; z-index: 1; }


        .copyright {
            border-top: 1px solid rgba(255,255,255,0.2);
            padding: 25px 0;
            text-align: center;
        }

        /* ==================================== */
        /* 12. Modals */
        /* ==================================== */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 2000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: #fefefe;
            padding: 30px;
            border-radius: 15px;
            border-top: 5px solid var(--verde-cepre);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: slideUp 0.3s;
        }
        
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.3s;
        }
        .close-button:hover, .close-button:focus {
            color: var(--magenta-unamad);
            text-decoration: none;
            cursor: pointer;
        }

        /* ==================================== */
        /* 13. Responsive Design y Móvil */
        /* ==================================== */
        /* Menú Móvil (Toggle) */
        .menu-toggle { 
            display: none; 
            flex-direction: column; 
            gap: 4px; 
            cursor: pointer; 
            transition: all 0.3s;
            justify-content: center;
            align-items: center;
            padding: 8px;
        }
        .menu-toggle span { 
            width: 25px; 
            height: 3px; 
            background: var(--azul-oscuro); 
            border-radius: 2px; 
            transition: all 0.3s;
            display: block;
        }
        .nav-menu.active {
            display: flex;
            flex-direction: column;
            position: absolute;
            top: 100%; 
            left: 0;
            right: 0;
            background: white;
            padding: 20px 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 999;
            align-items: flex-start;
        }
        
        @media (max-width: 1024px) {
            /* General Responsive Changes (Tablet View) */
            .top-bar-content { flex-direction: column; text-align: center; }
            /* Corrección: Ocultar enlaces de la Top Bar en tablet/móvil para evitar desbordamiento */
            .info-links { display: none; } 

            .hero-content { 
                grid-template-columns: 1fr; 
                text-align: center;
                gap: 20px;
            }
            .hero-text { order: 1; text-align: center; }
            .hero-title { font-size: 48px; }
            .nav-menu { display: none; }
            .menu-toggle { display: flex; }
            
            /* Flechas de Navegación del Carrusel */
            .prev-nav { left: 10px; padding: 10px; }
            .next-nav { right: 10px; padding: 10px; }

            /* Cuadrículas */
            .courses-grid, .teachers-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
            .stats-container { grid-template-columns: repeat(2, 1fr); gap: 20px; }
            
            /* Hero Image/Badge */
            .stats-badge {
                position: static; 
                margin: 20px auto 0; 
                max-width: 90%;
                transform: none; 
                width: auto;
                height: auto;
                padding: 20px;
                display: block; 
            }
             .stats-badge > * { transform: none; margin: 5px 0; }
            .hero-image-wrapper:hover .stats-badge { transform: scale(1.05) translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.4); }
            
            /* CTA/Contact */
            .cta-banner-content h2 { font-size: 36px; }
            .contact-bar-content { flex-direction: column; }
            .contact-bar-right { text-align: center; }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            /* Mobile View (Smartphone) */
            
            /* Top Bar Mobile */
            .top-bar-content { 
                padding: 0 15px; 
                flex-direction: column; 
                text-align: center; 
                gap: 5px;
            }
            .help-desk { 
                font-size: 12px; 
                padding: 3px 8px;
                justify-content: center;
                width: 100%;
            }
            
            /* Header Mobile */
            .header-content { 
                padding: 10px 15px; 
                gap: 8px;
                position: relative;
                flex-wrap: nowrap;
            } 
            .logo { 
                height: 40px;
                flex-shrink: 0;
            }
            .nav-menu {
                display: none;
            }
            .header-buttons { 
                gap: 8px; 
                width: auto; 
                justify-content: flex-end;
                align-items: center;
                flex-shrink: 0;
            }
            .btn { 
                width: auto; 
                padding: 8px 12px; 
                font-size: 11px; 
                gap: 4px;
                white-space: nowrap;
            }
            .search-icon { 
                font-size: 18px;
            }
            .menu-toggle {
                display: flex;
                flex-shrink: 0;
            }

            /* Hero Section Mobile */
            .hero-section { 
                min-height: auto; 
                padding: 0;
            }
            .carousel-slide { 
                padding: 40px 0; 
            }
            .hero-content { 
                padding: 0 15px; 
                gap: 20px;
            }
            .hero-title { 
                font-size: 28px; 
                line-height: 1.2;
                text-shadow: 
                    0 0 3px rgba(255,255,255,0.5), 
                    1px 1px var(--verde-cepre), 
                    2px 2px var(--cyan-acento),
                    3px 3px var(--azul-oscuro);
            }
            .hero-title:hover {
                text-shadow: 
                    0 0 10px rgba(255,255,255,0.8), 
                    1px 1px var(--verde-cepre), 
                    2px 2px var(--cyan-acento),
                    3px 3px var(--azul-oscuro);
            }
            .hero-subtitle { 
                font-size: 18px; 
                margin-bottom: 10px;
            }
            .hero-description { 
                font-size: 14px; 
                line-height: 1.5;
            }
            .hero-buttons { 
                width: 100%; 
                flex-direction: column; 
                gap: 10px; 
            }
            .hero-buttons .btn {
                width: 100%;
                justify-content: center;
            }
            
            /* Hero Image Mobile */
            .hero-image-wrapper { 
                min-height: 280px;
                max-height: 350px;
                margin-top: 20px;
                display: flex;
                flex-direction: column;
                overflow: visible;
            }
            .hero-image {
                object-fit: contain;
                max-height: 100%;
                flex: 1;
            }
            .stats-badge {
                position: static;
                bottom: auto;
                left: auto;
                margin: 15px auto 0;
                padding: 15px 20px;
                max-width: 90%;
                width: auto;
                transform: none !important;
            }
            .stats-badge p { 
                font-size: 12px; 
                margin: 3px 0;
            }
            .stats-badge h2 { 
                font-size: 28px;
                margin: 5px 0;
            }
            .hero-image-wrapper:hover .stats-badge { 
                transform: none !important;
            }
            
            /* Carousel Navigation Mobile */
            .carousel-nav {
                width: 40px;
                height: 40px;
                font-size: 18px;
                padding: 10px;
            }
            .prev-nav { left: 10px; }
            .next-nav { right: 10px; }
            .carousel-dots { 
                bottom: 10px; 
                gap: 6px;
            }
            .dot { 
                width: 8px; 
                height: 8px; 
            }
            
            /* Marquee Mobile */
            .marquee-section { padding: 10px 0; }
            .marquee-item { 
                padding: 0 20px; 
                font-size: 14px;
            }
            
            /* Sections Mobile */
            .courses-section, .teachers-section, .stats-section { 
                padding: 40px 0; 
            }
            .section-title { 
                margin-bottom: 30px; 
                padding: 0 15px;
            }
            .section-title h6 { 
                font-size: 12px; 
                letter-spacing: 1px;
            }
            .section-title h2 { 
                font-size: 28px; 
                line-height: 1.3;
            }
            
            /* Courses Grid Mobile */
            .courses-grid { 
                grid-template-columns: 1fr; 
                gap: 20px; 
                padding: 0 15px;
            }
            .course-card { 
                margin-bottom: 10px; 
            }
            .course-icon { 
                padding: 30px; 
            }
            .course-icon i { 
                font-size: 36px; 
            }
            .course-content { 
                padding: 20px; 
            }
            .course-content h3 { 
                font-size: 18px; 
            }
            .course-content p { 
                font-size: 14px; 
            }
            
            /* Stats Section Mobile */
            .stats-container { 
                grid-template-columns: 1fr; 
                gap: 15px; 
                padding: 0 15px;
            }
            .stat-box { 
                padding: 20px; 
            }
            .stat-box i { 
                font-size: 36px; 
                margin-bottom: 10px;
            }
            .stat-box h3 { 
                font-size: 36px; 
            }
            .stat-box p { 
                font-size: 14px; 
            }
            
            /* Teachers Grid Mobile */
            .teachers-grid { 
                grid-template-columns: 1fr; 
                gap: 20px; 
                padding: 0 15px;
            }
            .teacher-card { 
                margin-bottom: 10px; 
            }
            .teacher-image { 
                height: 250px; 
            }
            .teacher-info { 
                padding: 20px; 
            }
            .teacher-info h4 { 
                font-size: 18px; 
            }
            .teacher-info p { 
                font-size: 14px; 
            }

            /* CTA Banner Mobile */
            .cta-banner { 
                padding: 40px 0; 
            }
            .cta-banner-content { 
                padding: 0 15px; 
            }
            .cta-banner-content h2 { 
                font-size: 24px; 
                margin-bottom: 20px;
                line-height: 1.3;
            }
            .cta-banner-content p { 
                font-size: 14px; 
                margin-bottom: 20px;
            }
            .btn-cyan-cta {
                padding: 12px 25px;
                font-size: 14px;
                width: 100%;
                justify-content: center;
            }
            
            /* Contact Bar Mobile */
            .contact-bar { 
                padding: 30px 0; 
            }
            .contact-bar-content { 
                flex-direction: column; 
                align-items: center; 
                padding: 0 15px;
                text-align: center;
            }
            .contact-bar-left { 
                flex-direction: column; 
                text-align: center;
            }
            .contact-bar-icon { 
                width: 45px; 
                height: 45px; 
            }
            .contact-bar-icon i { 
                font-size: 18px; 
            }
            .contact-bar-right { 
                text-align: center; 
            }
            .contact-bar-right h3 { 
                font-size: 24px; 
            }
            .contact-bar-right p { 
                font-size: 14px; 
            }
            
            /* Footer Mobile */
            .footer-grid { 
                grid-template-columns: 1fr; 
                gap: 30px;
            }
            .footer-content { 
                padding: 0 15px; 
            }
            .footer-column h3 { 
                font-size: 16px; 
            }
            .footer-column ul li a { 
                font-size: 13px; 
            }
            .footer-buttons a { 
                font-size: 12px; 
                padding: 10px 15px;
            }
            .copyright { 
                padding: 20px 15px; 
                font-size: 12px;
            }
            
            /* Modal Mobile */
            .modal-content { 
                width: 95%; 
                padding: 20px; 
                margin: 10px;
            }
            .modal-content h3 { 
                font-size: 20px; 
            }
            .modal-content p { 
                font-size: 14px; 
            }
            
            /* Scroll Top Button Mobile */
            #scrollTop { 
                bottom: 20px; 
                right: 15px; 
                width: 45px; 
                height: 45px;
            }
            
            /* Floating Postular Button Mobile */
            #floating-postular-btn {
                bottom: 160px;
                right: 15px;
                min-width: 100px;
                padding: 10px 14px;
                gap: 3px;
            }
            
            #floating-postular-btn .btn-icon {
                font-size: 18px;
            }
            
            #floating-postular-btn .btn-text {
                font-size: 10px;
            }
            
            .floating-tooltip {
                display: none; /* Ocultar tooltip en móvil */
            }
            
            .floating-badge {
                font-size: 9px;
                padding: 3px 6px;
            }
        }
        
        /* Extra Small Devices (< 480px) */
        @media (max-width: 480px) {
            .hero-title { 
                font-size: 24px; 
            }
            .hero-subtitle { 
                font-size: 16px; 
            }
            .section-title h2 { 
                font-size: 24px; 
            }
            .cta-banner-content h2 { 
                font-size: 20px; 
            }
            .contact-bar-right h3 { 
                font-size: 20px; 
            }
            .btn { 
                padding: 6px 10px; 
                font-size: 11px; 
            }
        }
        
        /* Botón Scroll Top visible solo si está en JS */
        #scrollTop { 
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--magenta-unamad), #ff1a8c);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            z-index: 999;
            box-shadow: 0 5px 15px rgba(230, 0, 126, 0.4);
            transition: all 0.3s;
            animation: bounce 2s ease-in-out infinite;
            display: none; 
            justify-content: center;
            align-items: center;
        }
        
        /* ==================================== */
        /* BOTÓN FLOTANTE DE POSTULACIÓN */
        /* ==================================== */
        #floating-postular-btn {
            position: fixed;
            bottom: 180px;
            right: 30px;
            background: linear-gradient(135deg, var(--verde-cepre), #689f38);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
            font-weight: 700;
            animation: pulseFloat 2s ease-in-out infinite;
            min-width: 110px;
            overflow: visible;
        }
        
        #floating-postular-btn .btn-icon {
            font-size: 20px;
            margin-bottom: 1px;
        }
        
        #floating-postular-btn .btn-text {
            font-size: 11px;
            letter-spacing: 0.5px;
            line-height: 1;
            text-transform: uppercase;
        }
        
        #floating-postular-btn:hover {
            transform: translateY(-5px) scale(1.05);
            background: linear-gradient(135deg, #689f38, var(--verde-cepre));
        }
        
        #floating-postular-btn:active {
            transform: translateY(-2px) scale(1.02);
        }
        
        /* Badge de notificación */
        .floating-badge {
            position: absolute;
            top: -10px;
            right: -20px;
            background: var(--magenta-unamad);
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(230, 0, 126, 0.5);
            animation: badgePulse 1.5s ease-in-out infinite;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 1002;
        }
        
        /* Tooltip flotante */
        .floating-tooltip {
            position: absolute;
            right: 85px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--azul-oscuro);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s, transform 0.3s;
        }
        
        .floating-tooltip::after {
            content: '';
            position: absolute;
            right: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 8px solid var(--azul-oscuro);
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
        }
        
        #floating-postular-btn:hover .floating-tooltip {
            opacity: 1;
            transform: translateY(-50%) translateX(-5px);
        }
        
        /* Animaciones para el botón flotante */
        @keyframes pulseFloat {
            0%, 100% {
                transform: translateY(0) scale(1);
                box-shadow: 0 8px 25px rgba(139, 195, 74, 0.5);
            }
            50% {
                transform: translateY(-10px) scale(1.05);
                box-shadow: 0 15px 35px rgba(139, 195, 74, 0.7);
            }
        }
        
        @keyframes glowPulse {
            0%, 100% {
                opacity: 0;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        @keyframes badgePulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        /* ==================================== */
        /* MEJORAS AL BOTÓN HERO "POSTULAR AHORA" */
        /* ==================================== */
        .btn-secondary {
            position: relative;
            overflow: hidden;
        }
        
        .btn-secondary::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }
        
        /* Efecto de brillo mejorado para botones de postulación */
        .hero-buttons .btn-secondary {
            box-shadow: 0 5px 20px rgba(230, 0, 126, 0.4);
            animation: heroButtonGlow 2s ease-in-out infinite;
        }
        
        .hero-buttons .btn-secondary:hover {
            box-shadow: 0 8px 30px rgba(230, 0, 126, 0.7);
            transform: translateY(-3px) scale(1.05);
        }
        
        @keyframes heroButtonGlow {
            0%, 100% {
                box-shadow: 0 5px 20px rgba(230, 0, 126, 0.4);
            }
            50% {
                box-shadow: 0 8px 25px rgba(230, 0, 126, 0.6);
            }
        }
        
        /* ==================================== */
        /* ANIMACIONES INTERACTIVAS DEL MODAL */
        /* ==================================== */
        
        /* Confetti con iconos académicos */
        .confetti-icon {
            position: fixed;
            top: -30px;
            z-index: 3000;
            pointer-events: none;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        @keyframes confettiFall {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        /* Modal bounce in animation */
        @keyframes modalBounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        /* Sparkle effect on button click */
        @keyframes sparkle {
            0%, 100% {
                transform: scale(1);
                filter: brightness(1);
            }
            50% {
                transform: scale(1.1);
                filter: brightness(1.3);
            }
        }
        
        #floating-postular-btn:active {
            animation: sparkle 0.3s ease;
        }
        
        /* Fix para Nice Select en Modal */
        .nice-select {
            background-color: #fff !important;
            color: #333 !important;
            border: 1px solid #ced4da !important;
        }
        .nice-select .list {
            background-color: #fff !important;
            color: #333 !important;
        }
        .nice-select .option {
            color: #333 !important;
        }
        .nice-select .option:hover, .nice-select .option.selected {
            background-color: #f0f0f0 !important;
        }
        
        /* ==================================== */
        /* Results Modal */
        /* ==================================== */
        .results-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            animation: fadeIn 0.3s ease-out;
        }

        .results-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .results-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .results-modal-content {
            position: relative;
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: scaleIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 10000;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.7);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .results-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--azul-oscuro);
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .results-modal-close:hover {
            background: var(--magenta-unamad);
            color: white;
            transform: rotate(90deg);
        }

        .results-modal-image {
            width: 100%;
            max-height: 400px;
            overflow: hidden;
            cursor: pointer;
            position: relative;
        }

        .results-modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .results-modal-image:hover img {
            transform: scale(1.05);
        }

        .results-modal-image::after {
            content: '👁️ Click para ver resultados completos';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            padding: 20px;
            text-align: center;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .results-modal-image:hover::after {
            opacity: 1;
        }

        .results-modal-footer {
            padding: 25px;
            text-align: center;
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
        }

        .results-modal-footer h3 {
            color: var(--azul-oscuro);
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .results-modal-footer p {
            color: #666;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .btn-view-results {
            background: linear-gradient(135deg, var(--verde-cepre), #689f38);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(164, 198, 57, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }

        .btn-view-results:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(164, 198, 57, 0.6);
            color: white;
        }

        /* Floating Results Button */
        #floating-results-btn {
            position: fixed;
            bottom: 180px;
            left: 30px;
            background: linear-gradient(135deg, var(--magenta-unamad), #ff1a8c);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
            font-weight: 700;
            animation: pulseFloat 2s ease-in-out infinite;
            min-width: 110px;
            overflow: visible;
            box-shadow: 0 5px 20px rgba(230, 0, 126, 0.4);
        }

        #floating-results-btn .btn-icon {
            font-size: 20px;
            margin-bottom: 1px;
        }

        #floating-results-btn .btn-text {
            font-size: 11px;
            letter-spacing: 0.5px;
            line-height: 1;
            text-transform: uppercase;
        }

        #floating-results-btn:hover {
            transform: translateY(-5px) scale(1.05);
            background: linear-gradient(135deg, #ff1a8c, var(--magenta-unamad));
            box-shadow: 0 8px 25px rgba(230, 0, 126, 0.6);
        }

        .results-badge {
            position: absolute;
            top: -10px;
            right: -20px;
            background: var(--cyan-acento);
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0, 160, 227, 0.5);
            animation: pulse 2s ease-in-out infinite;
            white-space: nowrap;
        }

        /* Mobile Responsive for Results Modal */
        @media (max-width: 768px) {
            .results-modal-content {
                width: 95%;
                max-width: none;
            }
            
            .results-modal-image {
                max-height: 300px;
            }
            
            .results-modal-footer h3 {
                font-size: 20px;
            }
            
            .results-modal-footer p {
                font-size: 14px;
            }
            
            #floating-results-btn {
                bottom: 170px;
                left: 15px;
                min-width: 90px;
                padding: 10px 12px;
            }
            
            .carousel-nav {
                font-size: 16px;
            }
        }
        
        /* Carousel Navigation Arrows */
        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10002;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--azul-oscuro);
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .carousel-nav:hover {
            background: var(--verde-cepre);
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-prev {
            left: 15px;
        }

        .carousel-next {
            right: 15px;
        }

        .carousel-counter {
            margin: 15px 0;
            font-size: 14px;
            color: #666;
            font-weight: 600;
        }

        .carousel-counter span {
            color: var(--azul-oscuro);
            font-weight: 700;
        }
        
    </style>
</head>
<body>
    <!-- Custom Modal Structure -->
    <div id="infoModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('infoModal')">&times;</span>
            <h3 id="modalTitle" style="color: var(--azul-oscuro); margin-bottom: 10px;">Detalle de Interacción</h3>
            <p id="modalBody" style="font-size: 16px; color: #555;"></p>
            <button class="btn btn-secondary" style="margin-top: 20px; width: auto;" onclick="closeModal('infoModal')">Cerrar</button>
        </div>
    </div>
    
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="top-bar-content">
            <div class="help-desk">
                <i class="fas fa-phone-alt"></i>
                <span><strong>HELP DESK:</strong> +51 974 122 813</span>
            </div>
            <div class="info-links">
                <a href="#"><i class="fas fa-graduation-cap"></i> Estudiantes</a>
                <a href="#"><i class="fas fa-chalkboard-teacher"></i> Docentes</a>
                <a href="#"><i class="fas fa-user-graduate"></i> Alumni</a>
                <a href="#"><i class="fas fa-flask"></i> Investigación</a>
                <a href="#"><i class="fas fa-users"></i> Comunidad</a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <!-- RUTA DINÁMICA RESTAURADA -->
            <img src="{{ asset('assets_cepre/img/logo/logocepre1.svg') }}" onerror="this.onerror=null; this.src='https://placehold.co/150x60/ffffff/2C5F7C?text=CEPRE';" alt="CEPRE UNAMAD" class="logo">

            <nav>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#cursos">Cursos</a></li>
                    <li><a href="#eventos">Eventos</a></li>
                    <li><a href="{{ route('resultados-examenes.public') }}">Resultados</a></li>
                    <li><a href="#nosotros">Nosotros</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
            </nav>

            <div class="header-buttons">
                <i class="search-icon fas fa-search"></i>
                <!-- RUTA DINÁMICA RESTAURADA -->
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="far fa-user"></i>
                    <span>Acceso</span>
                </a>
                <div class="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section con Carrusel -->
    <section class="hero-section" id="inicio">
        <!-- Contenedor para el Canvas de Partículas 3D (Fondo estático) -->
        <div id="hero-canvas-container"></div>
        <div class="hero-bg-overlay"></div>
        
        <!-- Contenedor del Carrusel -->
        <div class="carousel-container">
            <div class="carousel-slides" id="carouselSlides">
                
                <!-- SLIDE 1: Éxito (Original) -->
                <div class="carousel-slide active">
                    <div class="hero-content">
                        <div class="hero-text">
                            <p class="hero-subtitle">Bienvenido a la Universidad</p>
                            <h1 class="hero-title">
                                CEPRE UNAMAD Tu<br>
                                Camino Al <span>Éxito</span>
                            </h1>
                            <p class="hero-description">
                                ¡Prepárate para Ingresar a la UNAMAD con los Mejores Docentes y Metodologías de Enseñanza! Contamos con más de 15 cursos especializados y un equipo académico de excelencia.
                            </p>
                            <div class="hero-buttons">
                                <a href="#cursos" class="btn btn-primary">
                                    <i class="fas fa-book"></i>
                                    <span>EXPLORAR PROGRAMAS</span>
                                </a>
                             

                                <div class="video-btn" onclick="showModal('videoModal')">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        </div>

                        <div class="hero-image-wrapper">
                            <!-- RUTA DINÁMICA RESTAURADA -->
                            <img src="{{ asset('assets_cepre/img/portada/portada.png') }}" onerror="this.onerror=null; this.src='https://placehold.co/600x400/2C5F7C/A4C639?text=CEPRE+UNAMAD+Slide+1';" alt="Estudiantes CEPRE UNAMAD" class="hero-image">
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
                            <h1 class="hero-title" style="--verde-cepre: var(--magenta-unamad); --cyan-acento: var(--verde-claro);">
                                Tu Ingreso <span>Directo</span><br>
                                a la UNAMAD
                            </h1>
                            <p class="hero-description">
                                Asegura tu vacante con nuestro proceso de ingreso directo. Estudia con nosotros y olvídate de la preocupación del examen de admisión general.
                            </p>
                            <div class="hero-buttons">
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-certificate"></i>
                                    <span>VER REGLAMENTO</span>
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-secondary" style="background: linear-gradient(135deg, var(--verde-cepre), var(--cyan-acento));">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>PRÓXIMAS FECHAS</span>
                                </a>
                                <div class="video-btn" onclick="showModal('videoModal')">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        </div>

                        <div class="hero-image-wrapper">
                            <img src="{{ asset('assets_cepre/img/portada/estudiante.png') }}" onerror="this.onerror=null; this.src='https://placehold.co/600x400/2C5F7C/E6007E?text=INGRESO+DIRECTO';" alt="Ingreso Directo UNAMAD" class="hero-image">
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
                            <h1 class="hero-title" style="--verde-cepre: var(--cyan-acento); --cyan-acento: var(--magenta-unamad);">
                                Docentes <span>Expertos</span><br>
                                a tu Disposición
                            </h1>
                            <p class="hero-description">
                                Contamos con la plana docente más experimentada y comprometida de la región, enfocada en maximizar tu potencial y asegurar tu ingreso.
                            </p>
                            <div class="hero-buttons">
                                <a href="#nosotros" class="btn btn-primary">
                                    <i class="fas fa-users"></i>
                                    <span>CONOCE AL EQUIPO</span>
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-secondary">
                                    <i class="fas fa-comment-dots"></i>
                                    <span>SOLICITAR INFO</span>
                                </a>
                                <div class="video-btn" onclick="showModal('videoModal')">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        </div>

                        <div class="hero-image-wrapper">
                            <img src="{{ asset('assets_cepre/img/portada/docentes.png') }}" onerror="this.onerror=null; this.src='https://placehold.co/600x400/2C5F7C/00A0E3?text=DOCENTES';" alt="Docentes expertos" class="hero-image">
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


    <!-- Marquee -->
    <div class="marquee-section">
        <div class="marquee-content">
            <div class="marquee-item"><i class="fas fa-graduation-cap"></i> Educación de calidad</div>
            <div class="marquee-item"><i class="fas fa-university"></i> CEPRE UNAMAD</div>
            <div class="marquee-item"><i class="fas fa-door-open"></i> Inscripciones abiertas</div>
            <div class="marquee-item"><i class="fas fa-chalkboard-teacher"></i> Docentes expertos</div>
            <div class="marquee-item"><i class="fas fa-book"></i> Más de 15 cursos</div>
            <div class="marquee-item"><i class="fas fa-tag"></i> S/. 1,150.00</div>
            <div class="marquee-item"><i class="fas fa-trophy"></i> Ingreso directo garantizado</div>
            <div class="marquee-item"><i class="fas fa-graduation-cap"></i> Educación de calidad</div>
            <div class="marquee-item"><i class="fas fa-university"></i> CEPRE UNAMAD</div>
            <div class="marquee-item"><i class="fas fa-door-open"></i> Inscripciones abiertas</div>
            <div class="marquee-item"><i class="fas fa-chalkboard-teacher"></i> Docentes expertos</div>
            <div class="marquee-item"><i class="fas fa-book"></i> Más de 15 cursos</div>
            <div class="marquee-item"><i class="fas fa-tag"></i> S/. 1,150.00</div>
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
            <div class="course-card animate-on-scroll" data-info="Matemática avanzada y resolución de problemas.">
                <div class="course-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="course-content">
                    <h3>Razonamiento Matemático</h3>
                    <p>03 Boletines | 12 Sesiones</p>
                </div>
            </div>
            <div class="course-card animate-on-scroll" data-info="Habilidades de comprensión lectora y análisis.">
                <div class="course-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="course-content">
                    <h3>Razonamiento Verbal</h3>
                    <p>03 Boletines | 12 Sesiones</p>
                </div>
            </div>
            <div class="course-card animate-on-scroll" data-info="Leyes de la física y ejercicios prácticos.">
                <div class="course-icon">
                    <i class="fas fa-atom"></i>
                </div>
                <div class="course-content">
                    <h3>Física</h3>
                    <p>03 Boletines | 12 Sesiones</p>
                </div>
            </div>
            <div class="course-card animate-on-scroll" data-info="Estructura molecular y reacciones químicas.">
                <div class="course-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="course-content">
                    <h3>Química</h3>
                    <p>03 Boletines | 12 Sesiones</p>
                </div>
            </div>
            <div class="course-card animate-on-scroll" data-info="Genética, ecosistemas y procesos biológicos.">
                <div class="course-icon">
                    <i class="fas fa-dna"></i>
                </div>
                <div class="course-content">
                    <h3>Biología</h3>
                    <p>03 Boletines | 12 Sesiones</p>
                </div>
            </div>
            <div class="course-card animate-on-scroll" data-info="Fundamentos de ecuaciones y funciones.">
                <div class="course-icon">
                    <i class="fas fa-square-root-alt"></i>
                </div>
                <div class="course-content">
                    <h3>Álgebra</h3>
                    <p>03 Boletines | 12 Sesiones</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-box animate-on-scroll">
                <i class="fas fa-users"></i>
                <h3 class="counter" data-target="1250">0</h3>
                <p>Estudiantes Matriculados</p>
            </div>
            <div class="stat-box animate-on-scroll">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3 class="counter" data-target="25">0</h3>
                <p>Docentes Expertos</p>
            </div>
            <div class="stat-box animate-on-scroll">
                <i class="fas fa-trophy"></i>
                <h3 class="counter" data-target="1000">0</h3>
                <p>Ingresantes a UNAMAD</p>
            </div>
            <div class="stat-box animate-on-scroll">
                <i class="fas fa-book"></i>
                <h3 class="counter" data-target="18">0</h3>
                <p>Cursos Disponibles</p>
            </div>
        </div>
    </section>

    <!-- Teachers Section -->
    <section class="teachers-section academic-notebook-pattern" id="nosotros">
        <div class="section-title">
            <h6>NUESTRO EQUIPO</h6>
            <h2>Docentes con Trayectoria</h2>
        </div>
        <div class="teachers-grid">
            <div class="teacher-card animate-on-scroll">
                <div class="teacher-image">
                    <!-- RUTA DINÁMICA RESTAURADA -->
                    <img src="{{ asset('assets_cepre/img/portada/docente_avatar.png') }}" onerror="this.onerror=null; this.src='https://placehold.co/400x300/f0f0f0/666?text=Docente+1';" alt="Ing. Juan Pérez">
                </div>
                <div class="teacher-info">
                    <h4>Ing. Juan Pérez</h4>
                    <p>Docente de Matemática</p>
                </div>
            </div>
            <div class="teacher-card animate-on-scroll">
                <div class="teacher-image">
                    <!-- RUTA DINÁMICA RESTAURADA -->
                    <img src="{{ asset('assets_cepre/img/portada/docente_avatar.png') }}" onerror="this.onerror=null; this.src='https://placehold.co/400x300/f0f0f0/666?text=Docente+2';" alt="Lic. María García">
                </div>
                <div class="teacher-info">
                    <h4>Lic. María García</h4>
                    <p>Docente de Lenguaje</p>
                </div>
            </div>
            <div class="teacher-card animate-on-scroll">
                <div class="teacher-image">
                    <!-- RUTA DINÁMICA RESTAURADA -->
                    <img src="{{ asset('assets_cepre/img/portada/docente_avatar.png') }}" onerror="this.onerror=null; this.src='https://placehold.co/400x300/f0f0f0/666?text=Docente+3';" alt="Dr. Carlos López">
                </div>
                <div class="teacher-info">
                    <h4>Dr. Carlos López</h4>
                    <p>Docente de Química</p>
                </div>
            </div>
            <div class="teacher-card animate-on-scroll">
                <div class="teacher-image">
                    <!-- RUTA DINÁMICA RESTAURADA -->
                    <img src="{{ asset('assets_cepre/img/portada/docente_avatar.png') }}" onerror="this.onerror=null; this.src='https://placehold.co/400x300/f0f0f0/666?text=Docente+4';" alt="Ing. Ana Torres">
                </div>
                <div class="teacher-info">
                    <h4>Ing. Ana Torres</h4>
                    <p>Docente de Física</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <div class="kene-pattern-overlay" style="opacity: 0.4;"></div>
        <div class="cta-banner-content">
            <h2>¡SOMOS LOS <span style="color:var(--cyan-acento); text-shadow: none;">ÚNICOS</span> EN OTORGARTE INGRESO DIRECTO A LA UNAMAD!</h2>
            <a href="#" class="btn-cyan-cta" style="margin-top: 20px;">
                <i class="fas fa-info-circle"></i>
                <span>VER MÁS DETALLES DE INGRESO</span>
            </a>
        </div>
    </section>

    <!-- Contact Bar (Ahora más limpia y separada) -->
    <section class="contact-bar" id="contacto">
        <div class="contact-bar-content">
            <div class="contact-bar-left">
                <div class="contact-bar-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div>
                    <p style="margin: 0; font-size: 16px; opacity: 0.9; color: var(--azul-oscuro);">Si tienes preguntas, solicita una consulta</p>
                    <p style="margin: 0; font-size: 14px; font-weight: 700; color: var(--azul-oscuro);">con nuestro asesor educativo.</p>
                </div>
            </div>
            <div class="contact-bar-right">
                <p style="margin: 0; font-size: 14px; opacity: 0.9; color: var(--azul-oscuro);">USA NUESTRA LÍNEA 24H</p>
                <h3>+51 974 122 813</h3>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-grid">
                <!-- Logo & Info -->
                <div class="footer-column animate-on-scroll" style="background: rgba(255,255,255,0.03); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
                    <!-- RUTA DINÁMICA RESTAURADA -->
                    <img src="{{ asset('assets_cepre/img/logo/logocepre1.svg') }}" onerror="this.onerror=null; this.src='https://placehold.co/150x60/2C5F7C/ffffff?text=LOGO';" alt="CEPRE UNAMAD" class="footer-logo" style="filter: brightness(0) invert(1);">
                    <p style="font-size: 14px; line-height: 1.6; opacity: 0.8; margin-bottom: 25px;">
                        Centro Pre Universitario de la UNAMAD<br>
                        Av. Dos de Mayo N° 960<br>
                        Puerto Maldonado - Tambopata<br>
                        Madre de Dios - Perú
                    </p>
                    <div class="social-links-container">
                        <p style="font-weight: 700; margin-bottom: 15px; color: var(--verde-cepre); font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Siguenos</p>
                        <div class="social-links">
                            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Admissions -->
                <div class="footer-column animate-on-scroll" style="transition-delay: 0.1s;">
                    <h3>Admisiones</h3>
                    <ul>
                        <li><a href="#">Cómo Postular</a></li>
                        <li><a href="#">Cronograma</a></li>
                        <li><a href="#">Requisitos</a></li>
                        <li><a href="#">Elegibilidad</a></li>
                        <li><a href="#">Estructura de Costos</a></li>
                        <li><a href="#">Becas</a></li>
                    </ul>
                </div>

                <!-- Quick Links -->
                <div class="footer-column animate-on-scroll" style="transition-delay: 0.2s;">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="#">Prensa & Media</a></li>
                        <li><a href="#">Portal de Alumni</a></li>
                        <li><a href="#">Boletines</a></li>
                        <li><a href="#">Departamentos</a></li>
                        <li><a href="#">Directorio</a></li>
                        <li><a href="#">Carreras</a></li>
                    </ul>
                </div>

                <!-- Additional Links & Botones -->
                <div class="footer-column animate-on-scroll" style="transition-delay: 0.3s;">
                    <h3>Enlaces Adicionales</h3>
                    <ul>
                        <li><a href="#">Casa Abierta</a></li>
                        <li><a href="#">Escuela de Verano</a></li>
                        <li><a href="#">Eventos 2024</a></li>
                        <li><a href="#">Foro Académico</a></li>
                        <li><a href="#">Términos y Condiciones</a></li>
                    </ul>
                   
                </div>
            </div>

            <!-- Copyright -->
            <div class="copyright">
                <p>Copyright © 2026 CEPRE UNAMAD. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Incluir Modal de Postulación -->
    @include('partials.postulacion-modal')

    <script>
        // Función para abrir el modal de postulación (llamada desde botones de la página)
        function openPostulacionModal() {
            document.getElementById('postulacionModal').style.display = 'flex';
            // La función showStep() está definida en publico-modal.js
            if (typeof showStep === 'function') {
                showStep(1);
            }
            
            // ¡ANIMACIÓN INTERACTIVA DE CONFETTI!
            createConfetti();
            
            // Animación de entrada del modal
            const modalContent = document.querySelector('#postulacionModal .modal-content');
            if (modalContent) {
                modalContent.style.animation = 'modalBounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            }
        }
        
        // Función para crear efecto de confetti con iconos académicos
        function createConfetti() {
            // Iconos académicos de FontAwesome
            const academicIcons = [
                'fa-graduation-cap',  // Gorro de graduación
                'fa-book',            // Libro
                'fa-book-open',       // Libro abierto
                'fa-pencil-alt',      // Lápiz
                'fa-pen',             // Pluma
                'fa-certificate',     // Certificado
                'fa-award',           // Premio
                'fa-star',            // Estrella
                'fa-bookmark'         // Marcador
            ];
            
            const colors = ['#8bc34a', '#e91e63', '#03a9f4', '#ffc107', '#ff5722'];
            const confettiCount = 30; // Reducido para mejor rendimiento
            
            for (let i = 0; i < confettiCount; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('i');
                    confetti.className = 'fas ' + academicIcons[Math.floor(Math.random() * academicIcons.length)] + ' confetti-icon';
                    
                    // Distribución por toda la pantalla
                    confetti.style.left = Math.random() * 100 + '%';
                    
                    confetti.style.color = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.fontSize = (Math.random() * 12 + 18) + 'px'; // Tamaños 18-30px
                    confetti.style.animationDelay = Math.random() * 0.3 + 's';
                    confetti.style.animationDuration = (Math.random() * 2 + 2.5) + 's'; // 2.5-4.5s
                    
                    // Aplicar la animación simple
                    confetti.style.animation = `confettiFall ${confetti.style.animationDuration} linear forwards`;
                    confetti.style.animationDelay = confetti.style.animationDelay;
                    
                    document.body.appendChild(confetti);
                    
                    // Remover después de la animación
                    setTimeout(() => confetti.remove(), 5000);
                }, i * 30); // Intervalo de 30ms
            }
        }
    </script>

    <!-- Burbuja de Notificación (Nueva) -->
    <div id="assistant-bubble">
        ¡Pregúntale a nuestro asistente!
    </div>
    
    <!-- Asistente Flotante (Avatar Guía - Ícono actualizado) -->
    <button id="floating-assistant" onclick="showModal('assistantModal')">
        <i class="fas fa-lightbulb"></i>
    </button>
    
    
    
    <!-- Results Modal -->
    <div id="resultsModal" class="results-modal">
        <div class="results-modal-overlay" onclick="closeResultsModal()"></div>
        <div class="results-modal-content">
            <button class="results-modal-close" onclick="closeResultsModal()">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Carousel Navigation -->
            <button class="carousel-nav carousel-prev" onclick="previousAnnouncement()" style="display: none;">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="carousel-nav carousel-next" onclick="nextAnnouncement()" style="display: none;">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <div class="results-modal-image" onclick="window.location.href='{{ route('resultados-examenes.public') }}'">
                <img id="modal-announcement-image" src="" alt="Anuncio">
            </div>
            <div class="results-modal-footer">
                <h3 id="modal-announcement-title">¡Resultados Publicados!</h3>
                <p id="modal-announcement-description">Consulta los resultados de los exámenes</p>
                
                <!-- Carousel Counter -->
                <div class="carousel-counter" style="display: none;">
                    <span id="current-announcement">1</span> / <span id="total-announcements">1</span>
                </div>
                
                <a href="{{ route('resultados-examenes.public') }}" class="btn-view-results">
                    <i class="fas fa-eye"></i>
                    Ver Resultados Completos
                </a>
            </div>
        </div>
    </div>

    <!-- Floating Results Button -->
    <button id="floating-results-btn" onclick="openResultsModal()" title="Ver Anuncios" style="display: none;">
        <i class="fas fa-bullhorn btn-icon"></i>
        <span class="btn-text">Anuncios</span>
        <span class="results-badge">Nuevo</span>
    </button>
    
    <!-- Scroll to Top Button -->
    <button id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // --- Referencias DOM ---
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        const header = document.querySelector('.main-header');
        const infoModal = document.getElementById('infoModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');

        // ====================================
        // CARRUSEL (HERO SECTION) LOGIC
        // ====================================
        let currentSlide = 0;
        let slides;
        let totalSlides;
        let slidesContainer;
        let dotsContainer;
        let slideInterval;
        const autoPlayTime = 5000; // 5 segundos

        function initCarousel() {
            slides = document.querySelectorAll('.carousel-slide');
            totalSlides = slides.length;
            slidesContainer = document.getElementById('carouselSlides');
            dotsContainer = document.getElementById('carouselDots');
            
            if (!slidesContainer) return;

            createDots();
            showSlide(currentSlide);
            startAutoPlay();
        }

        function createDots() {
            dotsContainer.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('span');
                dot.classList.add('dot');
                dot.setAttribute('data-slide-index', i);
                dot.onclick = () => showSlide(i);
                dotsContainer.appendChild(dot);
            }
        }

        function showSlide(index) {
            // Reiniciar autoplay cada vez que se cambia de slide manualmente
            clearInterval(slideInterval);
            startAutoPlay();

            if (index >= totalSlides) {
                currentSlide = 0;
            } else if (index < 0) {
                currentSlide = totalSlides - 1;
            } else {
                currentSlide = index;
            }

            const offset = -currentSlide * 100;
            slidesContainer.style.transform = `translateX(${offset}%)`;

            // Actualizar clase 'active' para el efecto de animación
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === currentSlide) {
                    slide.classList.add('active');
                }
            });

            // Actualizar dots
            document.querySelectorAll('.dot').forEach((dot, i) => {
                dot.classList.remove('active');
                if (i === currentSlide) {
                    dot.classList.add('active');
                }
            });
        }

        function changeSlide(n) {
            showSlide(currentSlide + n);
        }

        function startAutoPlay() {
            slideInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, autoPlayTime);
        }

        // ====================================
        // 1. Funcionalidad del Menú Móvil
        // ====================================
        function toggleMobileMenu() {
            menuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');

            if (navMenu.classList.contains('active')) {
                const headerHeight = header.offsetHeight;
                navMenu.style.top = `${headerHeight}px`;
            }
        }
        
        menuToggle.addEventListener('click', toggleMobileMenu);

        // Cierra el menú móvil al hacer clic en un enlace de navegación
        document.querySelectorAll('.nav-menu a').forEach(anchor => {
            anchor.addEventListener('click', function () {
                if (window.innerWidth <= 1024 && navMenu.classList.contains('active')) {
                    toggleMobileMenu();
                }
            });
        });

        window.addEventListener('resize', () => {
             if (window.innerWidth > 1024) {
                navMenu.classList.remove('active');
                menuToggle.classList.remove('active');
             }
        });
        
        // ====================================
        // 2. Animación de Contadores
        // ====================================
        const counters = document.querySelectorAll('.counter');
        const speed = 250; 

        function startCounterAnimation(counter) {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target + '+';
                }
            };
            updateCount();
        }

        const counterObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    startCounterAnimation(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 }); 

        counters.forEach(counter => {
            counterObserver.observe(counter);
        });

        // ====================================
        // 3. Animación al Scroll (Fade In)
        // ====================================
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const scrollObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            scrollObserver.observe(el);
        });

        document.querySelectorAll('.footer-column').forEach(el => {
            scrollObserver.observe(el);
        });


        // ====================================
        // 4. Botón Scroll to Top
        // ====================================
        const scrollTopBtn = document.getElementById('scrollTop');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.style.display = 'flex'; // Usar flex para centrar el ícono
            } else {
                scrollTopBtn.style.display = 'none';
            }
        });

        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // ====================================
        // 5. Control de Modals
        // ====================================
        function showModal(type, title = '', body = '') {
            if (type === 'videoModal') {
                modalTitle.textContent = '¡Video Promocional!';
                modalBody.innerHTML = 'Aquí iría el video de presentación del CEPRE UNAMAD. Por ahora, imagina un video inspirador sobre el camino al éxito.';
            } else if (type === 'courseInfo') {
                modalTitle.textContent = title;
                modalBody.innerHTML = '<strong>Información Adicional:</strong> ' + body;
            } else if (type === 'assistantModal') {
                // Título actualizado con la bombilla
                modalTitle.textContent = '💡 Asistente Virtual CEPRE';
                modalBody.innerHTML = '<p>¡Hola! Soy el asistente virtual de la CEPRE UNAMAD. Estoy aquí para guiarte en tu camino al éxito.</p><p><strong>¿Qué te gustaría saber hoy?</strong></p><ul><li>Requisitos de Admisión</li><li>Horarios y Costos</li><li>Programas de Estudio</li></ul>';
            }
            infoModal.style.display = 'flex';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
             if (event.target === infoModal) {
                 closeModal('infoModal');
             }
        });

        // Asignar función de modal a tarjetas de curso y botón de video
        document.querySelectorAll('.course-card').forEach(card => {
            card.addEventListener('click', function() {
                const courseName = this.querySelector('h3').textContent;
                const courseInfo = this.getAttribute('data-info');
                showModal('courseInfo', courseName, courseInfo);
            });
        });

        // Asignar modal a los botones del footer
        document.querySelectorAll('.footer-buttons a').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                showModal('courseInfo', this.querySelector('span').textContent.toUpperCase(), 
                    `Has hecho clic en el botón de acción para ${this.querySelector('span').textContent}. ¡Excelente iniciativa!`);
            });
        });

        // ====================================
        // 6. Inicialización 3D y Carga
        // ====================================
        let scene, camera, renderer, particles, particleMaterial, particleCount;
        let container = document.getElementById('hero-canvas-container');

        // Inicialización de la escena 3D
        function initThreeJS() {
            if (!container) return;
            
            // Escena
            scene = new THREE.Scene();
            scene.background = null; // Fondo transparente
            
            // Cámara
            camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 1, 1000);
            camera.position.z = 5;

            // Renderizador
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            container.appendChild(renderer.domElement);
            
            // Partículas - MODIFICADO para más densidad y tamaño
            particleCount = 750; // Aumentado de 500
            const geometry = new THREE.BufferGeometry();
            const positions = [];
            const colors = [];
            
            const color1 = new THREE.Color(0x00a0e3); // Cyan
            const color2 = new THREE.Color(0xa4c639); // Verde
            
            for (let i = 0; i < particleCount; i++) {
                // Posiciones aleatorias en un cubo
                positions.push(
                    (Math.random() - 0.5) * 20, // x
                    (Math.random() - 0.5) * 20, // y
                    (Math.random() - 0.5) * 20  // z
                );
                
                // Color interpolado o fijo
                const color = (Math.random() > 0.5) ? color1 : color2;
                colors.push(color.r, color.g, color.b);
            }
            
            geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
            geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));

            // Material - MODIFICADO para más visibilidad y suavidad
            particleMaterial = new THREE.PointsMaterial({
                size: 0.1, // Aumentado de 0.05
                vertexColors: true,
                blending: THREE.AdditiveBlending,
                transparent: true,
                opacity: 0.8 // Reducido ligeramente para un look más suave
            });

            particles = new THREE.Points(geometry, particleMaterial);
            scene.add(particles);

            // Manejar resize
            window.addEventListener('resize', onWindowResize, false);
            
            animate();
        }
        
        function onWindowResize() {
            if (!container) return;
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        }

        // Bucle de animación
        function animate() {
            requestAnimationFrame(animate);
            
            // Rotación sutil
            if (particles) {
                particles.rotation.x += 0.0005;
                particles.rotation.y += 0.001;
            }
            
            renderer.render(scene, camera);
        }
        
        // Inicializar al cargar la ventana
        window.onload = function() {
            // Inicializar Three.js después de que el DOM esté listo
            initThreeJS();
            
            // Inicializar el carrusel
            initCarousel();

            // Mostrar el cuerpo después de la inicialización
            document.body.style.opacity = '1';

            // Click listener para el botón de video (asegura que funciona)
             document.querySelector('.video-btn').addEventListener('click', function() {
                 showModal('videoModal');
             });
        };
    </script>
    <!-- jQuery (Required for Select2, Nice Select, Toastr) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Canvas Confetti para efecto de celebración -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    
    <!-- Custom JS for Public Modal -->
    <script src="{{ Vite::asset('resources/js/postulaciones/publico-modal.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Select2 logic moved to publico-modal.js
        });
        
        // ==========================================
        // RESULTS MODAL FUNCTIONS WITH CAROUSEL
        // ==========================================
        let allAnnouncements = [];
        let currentAnnouncementIndex = 0;

        // Fetch active announcements
        async function fetchActiveAnnouncements() {
            try {
                const response = await fetch('/api/anuncios/activos');
                const data = await response.json();
                return data.length > 0 ? data : null;
            } catch (error) {
                console.error('Error fetching announcements:', error);
                return null;
            }
        }

        // Open Results Modal
        function openResultsModal() {
            const modal = document.getElementById('resultsModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Mark as viewed in session
            sessionStorage.setItem('announcementViewed', 'true');
        }

        // Close Results Modal
        function closeResultsModal() {
            const modal = document.getElementById('resultsModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Navigate to next announcement
        function nextAnnouncement() {
            if (currentAnnouncementIndex < allAnnouncements.length - 1) {
                currentAnnouncementIndex++;
                displayCurrentAnnouncement();
            }
        }

        // Navigate to previous announcement
        function previousAnnouncement() {
            if (currentAnnouncementIndex > 0) {
                currentAnnouncementIndex--;
                displayCurrentAnnouncement();
            }
        }

        // Display current announcement
        function displayCurrentAnnouncement() {
            const announcement = allAnnouncements[currentAnnouncementIndex];
            
            const image = document.getElementById('modal-announcement-image');
            const title = document.getElementById('modal-announcement-title');
            const description = document.getElementById('modal-announcement-description');
            const currentSpan = document.getElementById('current-announcement');
            
            // Set image (use placeholder if no image)
            if (announcement.imagen) {
                image.src = `/storage/${announcement.imagen}`;
            } else {
                image.src = 'https://placehold.co/600x400/2C5F7C/ffffff?text=Resultados+Disponibles';
            }
            
            title.textContent = announcement.titulo;
            description.textContent = announcement.descripcion || 'Consulta los resultados de los exámenes';
            currentSpan.textContent = currentAnnouncementIndex + 1;
            
            // Update navigation buttons visibility
            updateNavigationButtons();
        }

        // Update navigation buttons
        function updateNavigationButtons() {
            const prevBtn = document.querySelector('.carousel-prev');
            const nextBtn = document.querySelector('.carousel-next');
            
            if (allAnnouncements.length > 1) {
                prevBtn.style.display = currentAnnouncementIndex > 0 ? 'flex' : 'none';
                nextBtn.style.display = currentAnnouncementIndex < allAnnouncements.length - 1 ? 'flex' : 'none';
            } else {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            }
        }

        // Load all announcements
        function loadAllAnnouncements(announcements) {
            allAnnouncements = announcements;
            currentAnnouncementIndex = 0;
            
            const floatingBtn = document.getElementById('floating-results-btn');
            const totalSpan = document.getElementById('total-announcements');
            const counter = document.querySelector('.carousel-counter');
            
            // Update total count
            totalSpan.textContent = announcements.length;
            
            // Show counter if more than 1 announcement
            if (announcements.length > 1) {
                counter.style.display = 'block';
            }
            
            // Display first announcement
            displayCurrentAnnouncement();
            
            // Show floating button
            floatingBtn.style.display = 'flex';
            
            // Update badge text
            const badge = floatingBtn.querySelector('.results-badge');
            if (announcements.length > 1) {
                badge.textContent = `${announcements.length} Nuevos`;
            } else {
                badge.textContent = 'Nuevo';
            }
        }

        // Initialize results modal and other effects on page load
        async function initPremiumEffects() {
            // 1. Header Scroll Effect
            const header = document.querySelector('.main-header');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // 2. Animate Counters
            const counters = document.querySelectorAll('.counter');
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = +entry.target.getAttribute('data-target');
                        const count = +entry.target.innerText;
                        const increment = target / 50; 

                        const updateCount = () => {
                            const current = +entry.target.innerText;
                            if (current < target) {
                                entry.target.innerText = Math.ceil(current + increment);
                                setTimeout(updateCount, 20);
                            } else {
                                entry.target.innerText = target;
                            }
                        };
                        updateCount();
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(counter => counterObserver.observe(counter));

            // 3. Results Modal
            initResultsModal();
        }

        // Call initialization when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPremiumEffects);
        } else {
            initPremiumEffects();
        }
    </script>