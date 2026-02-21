<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Exámenes - CEPRE UNAMAD</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('faviconcepre.svg') }}?v=2">
    <link rel="icon" type="image/x-icon" href="{{ asset('faviconcepre.ico') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('faviconcepre.ico') }}?v=2">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ==================================== */
        /* Variables y Reset Global - IGUAL QUE CEPREUNAMAD */
        /* ==================================== */
        :root {
            --verde-cepre: #8cc63f; 
            --magenta-unamad: #ec008c; 
            --cyan-acento: #00aeef; 
            --azul-oscuro: #2b5a6f; 
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
            opacity: 0.12;
        }

        .results-hero .kene-pattern-overlay {
            opacity: 0.18;
            mix-blend-mode: overlay;
        }

        /* ==================================== */
        /* Patrón de Cuaderno Académico */
        /* ==================================== */
        .academic-notebook-pattern {
            background-color: var(--paper-bg) !important;
            background-image: 
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px) !important;
            background-size: 30px 30px !important;
            position: relative;
        }

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

        /* ==================================== */
        /* Animaciones */
        /* ==================================== */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        @keyframes slideInContent { 0% { opacity: 0; transform: translateY(30px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }

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
        /* Top Bar */
        /* ==================================== */
        .top-bar {
            background: linear-gradient(135deg, var(--azul-oscuro) 0%, #1a3d52 100%);
            color: white;
            padding: 8px 0;
            font-size: 13px;
            font-weight: 500;
            border-bottom: 3px solid var(--verde-cepre);
            position: relative;
            z-index: 1001;
        }

        .top-bar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 40px;
        }

        .help-desk span strong { color: var(--verde-cepre); }

        .info-links { display: flex; gap: 24px; }
        .info-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: var(--transition-smooth);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-links a:hover { color: white; transform: translateY(-1px); }
        .info-links a i { color: var(--cyan-acento); font-size: 14px; }

        /* ==================================== */
        /* Header */
        /* ==================================== */
        .main-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: var(--transition-smooth);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 0;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo { height: 50px; transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05)); }
        .logo:hover { transform: scale(1.05) translateY(-2px); }

        .nav-menu { display: flex; gap: 35px; list-style: none; }
        .nav-menu a {
            color: #475569;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: var(--transition-smooth);
            position: relative;
            padding: 8px 0;
        }

        .nav-menu a:hover,
        .nav-menu a.active { color: var(--azul-oscuro); }
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 0; height: 3px;
            background: linear-gradient(90deg, var(--verde-cepre), var(--cyan-acento));
            border-radius: 5px;
            transition: var(--transition-smooth);
        }
        .nav-menu a:hover::after,
        .nav-menu a.active::after { width: 100%; }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Search Button */
        .search-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--azul-oscuro);
            cursor: pointer;
            padding: 8px;
            transition: all 0.3s;
            display: none;
        }

        .search-btn:hover {
            color: var(--verde-cepre);
            transform: scale(1.1);
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--azul-oscuro);
            cursor: pointer;
            padding: 8px;
            transition: all 0.3s;
        }

        .mobile-menu-btn:hover {
            color: var(--verde-cepre);
            transform: scale(1.1);
        }

        /* Mobile Menu Overlay */
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
        }

        .mobile-menu-overlay.active {
            display: block;
        }

        .mobile-nav-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 280px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            z-index: 9999;
            transition: right 0.3s ease;
            overflow-y: auto;
        }

        .mobile-nav-menu.active {
            right: 0;
        }

        .mobile-menu-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--verde-cepre), var(--verde-claro));
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-menu-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
        }

        .mobile-nav-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .mobile-nav-menu ul li {
            border-bottom: 1px solid #f0f0f0;
        }

        .mobile-nav-menu ul li a {
            display: block;
            padding: 15px 20px;
            color: var(--azul-oscuro);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .mobile-nav-menu ul li a:hover,
        .mobile-nav-menu ul li a.active {
            background: var(--fondo-cursos);
            color: var(--verde-cepre);
            padding-left: 30px;
            border-left: 4px solid var(--verde-cepre);
        }

        .mobile-nav-menu ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
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
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--verde-cepre), var(--verde-claro));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(164, 198, 57, 0.4);
        }

        /* ==================================== */
        /* Hero Section para Resultados */
        /* ==================================== */
        .results-hero {
            background: var(--azul-oscuro);
            padding: 80px 0;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .results-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(164, 198, 57, 0.1), rgba(0, 160, 227, 0.1));
            z-index: 0;
        }

        .results-hero-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            position: relative;
            z-index: 1;
        }

        .results-hero h1 {
            font-size: 58px;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px var(--verde-cepre), 4px 4px var(--cyan-acento);
        }

        .results-hero h1 span {
            background: linear-gradient(90deg, var(--verde-cepre), var(--verde-claro), var(--cyan-acento));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
            animation: bounce 2s ease-in-out infinite;
        }

        .results-hero p {
            font-size: 20px;
            opacity: 0.9;
        }

        /* ==================================== */
        /* Filtros Section */
        /* ==================================== */
        .filters-section {
            background: white;
            padding: 30px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .filters-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-group label {
            font-weight: 700;
            color: var(--azul-oscuro);
            font-size: 16px;
        }

        .filter-group select {
            padding: 12px 25px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 50px;
            font-size: 15px;
            font-weight: 500;
            min-width: 320px;
            transition: var(--transition-smooth);
            background: white;
            cursor: pointer;
            box-shadow: var(--shadow-premium);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%232b5a6f' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 18px;
        }

        .filter-group select:focus {
            outline: none;
            border-color: var(--verde-cepre);
            box-shadow: 0 0 0 4px rgba(140, 198, 63, 0.15);
            transform: translateY(-2px);
        }

        /* ==================================== */
        /* Results Section - AGRUPADO POR CICLO */
        /* ==================================== */
        .results-section {
            padding: 80px 0;
            background: var(--fondo-cursos);
        }

        .results-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }

        /* Título de Ciclo */
        .ciclo-group {
            margin-bottom: 60px;
        }

        .ciclo-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px;
            background: linear-gradient(135deg, var(--azul-oscuro) 0%, #1a3d52 100%);
            border-radius: 24px;
            color: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .ciclo-header::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("{{ asset('assets_cepre/img/tejido-kene-final.png') }}");
            background-size: 200px 200px;
            opacity: 0.05;
            z-index: -1;
        }

        .ciclo-header h2 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -0.01em;
        }

        .ciclo-header p {
            font-size: 16px;
            opacity: 0.8;
            font-weight: 500;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        /* Tarjetas de Resultados */
        /* Tarjetas de Resultados Premium */
        .result-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            position: relative;
        }

        .result-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 4px;
            background: var(--verde-cepre);
            z-index: 2;
        }

        .result-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            border-color: rgba(140, 198, 63, 0.3);
        }

        .result-card:nth-child(2n)::before { background: var(--magenta-unamad); }
        .result-card:nth-child(3n)::before { background: var(--cyan-acento); }
        .result-card:nth-child(4n)::before { background: var(--azul-oscuro); }

        .result-header {
            padding: 40px 30px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--azul-oscuro);
            text-align: center;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.4s;
        }

        .result-card:hover .result-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .result-header i {
            font-size: 54px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--verde-cepre), var(--verde-claro));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .result-card:nth-child(2n) .result-header i { background: linear-gradient(135deg, var(--magenta-unamad), #ff1a8c); -webkit-background-clip: text; }
        .result-card:nth-child(3n) .result-header i { background: linear-gradient(135deg, var(--cyan-acento), #00aeef); -webkit-background-clip: text; }
        .result-card:nth-child(4n) .result-header i { background: linear-gradient(135deg, var(--azul-oscuro), #1a3d52); -webkit-background-clip: text; }

        .result-card:hover .result-header i {
            transform: scale(1.1) rotateY(360deg);
        }

        .result-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .result-content {
            padding: 25px;
        }

        .result-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }

        .meta-item i {
            color: var(--cyan-acento);
            font-size: 16px;
        }

        .result-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .result-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-view-pdf {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            color: #475569;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition-smooth);
            border: 1px solid rgba(0,0,0,0.05);
            cursor: pointer;
            font-size: 14px;
        }

        .btn-view-pdf:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-color: transparent;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.2);
        }

        .btn-download {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #64748b;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition-smooth);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .btn-download:hover {
            background: var(--azul-oscuro);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(43, 90, 111, 0.2);
        }

        .btn-link {
            background: linear-gradient(135deg, var(--cyan-acento) 0%, #0096d1 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition-smooth);
            border: none;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 174, 239, 0.2);
        }

        .btn-link:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 174, 239, 0.4);
            filter: brightness(1.1);
        }

        /* ==================================== */
        /* Modal para PDF */
        /* ==================================== */
        .modal {
            display: none;
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            animation: fadeIn 0.3s;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 40px 100px rgba(0,0,0,0.4);
            animation: scaleIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            z-index: 100000;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }

        @keyframes scaleIn {
            from { transform: scale(0.9) translateY(20px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }

        .modal-header {
            padding: 25px 40px;
            background: linear-gradient(135deg, var(--azul-oscuro) 0%, #1a3d52 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .modal-header::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("{{ asset('assets_cepre/img/tejido-kene-final.png') }}");
            background-size: 200px 200px;
            opacity: 0.1;
            z-index: 0;
            pointer-events: none;
        }

        .modal-header h3 {
            font-size: 24px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }

        .modal-close {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-smooth);
            position: relative;
            z-index: 1;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.2);
            transform: rotate(90deg) scale(1.1);
        }



        .modal-body {
            flex: 1;
            padding: 0;
            overflow: hidden;
        }

        .modal-body iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* ==================================== */
        /* No Results */
        /* ==================================== */
        .no-results {
            text-align: center;
            padding: 80px 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            max-width: 600px;
            margin: 0 auto;
        }

        .no-results i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-results h3 {
            color: var(--azul-oscuro);
            font-size: 28px;
            margin-bottom: 10px;
        }

        .no-results p {
            color: #666;
            font-size: 16px;
        }

        /* ==================================== */
        /* Footer */
        /* ==================================== */
        footer {
            background: #111827;
            color: white;
            padding: 80px 0 40px;
            position: relative;
            overflow: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("{{ asset('assets_cepre/img/tejido-kene-final.png') }}");
            background-size: 300px 300px;
            opacity: 0.03;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .footer-content p {
            color: rgba(255,255,255,0.6);
            font-size: 14px;
            letter-spacing: 0.02em;
        }

        /* ==================================== */
        /* Floating Postulation Button */
        /* ==================================== */
        @keyframes pulseFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-5px) scale(1.02); }
        }

        #floating-postular-btn {
            position: fixed;
            bottom: 100px;
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
            box-shadow: 0 5px 20px rgba(164, 198, 57, 0.4);
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
            box-shadow: 0 8px 25px rgba(164, 198, 57, 0.6);
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
            animation: pulse 2s ease-in-out infinite;
            white-space: nowrap;
        }

        /* ==================================== */
        /* Scroll to Top */
        /* ==================================== */
        #scrollTop {
            position: fixed;
            bottom: 40px;
            right: 40px;
            background: white;
            color: var(--azul-oscuro);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.05);
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: var(--transition-smooth);
            z-index: 999;
        }

        #scrollTop:hover {
            transform: translateY(-5px);
            background: var(--verde-cepre);
            color: white;
            box-shadow: 0 15px 30px rgba(140, 198, 63, 0.3);
        }

        /* ==================================== */
        /* Responsive */
        /* ==================================== */
        @media (max-width: 768px) {
            /* Top Bar */
            .top-bar-content {
                padding: 0 15px;
                gap: 8px;
            }

            .help-desk {
                font-size: 11px;
                gap: 5px;
            }

            .info-links {
                display: none;
            }

            /* Header */
            .header-content {
                padding: 10px 15px;
                flex-wrap: wrap;
            }

            .logo {
                height: 40px;
            }

            .nav-menu {
                display: none;
            }

            .mobile-menu-btn,
            .search-btn {
                display: block;
            }

            .header-buttons .btn-primary {
                padding: 8px 15px;
                font-size: 12px;
                border-radius: 20px;
            }

            .header-buttons .btn-primary span {
                display: inline;
            }

            /* Hero Section */
            .results-hero {
                padding: 30px 0;
            }

            .results-hero h1 {
                font-size: 28px;
                text-shadow: 1px 1px var(--verde-cepre), 2px 2px var(--cyan-acento);
            }

            .results-hero p {
                font-size: 14px;
            }

            /* Filters */
            .filters-section {
                padding: 15px 0;
            }

            .filters-container {
                padding: 0 15px;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .filter-group label {
                font-size: 13px;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .filter-group select {
                width: 100%;
                min-width: auto;
                padding: 10px 15px;
                font-size: 14px;
                border-radius: 12px;
            }

            /* Results Section */
            .results-section {
                padding: 30px 0;
            }

            .results-container {
                padding: 0 15px;
            }

            .ciclo-group {
                margin-bottom: 30px;
            }

            .ciclo-header {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 10px;
            }

            .ciclo-header h2 {
                font-size: 20px;
            }

            .ciclo-header p {
                font-size: 13px;
            }

            .results-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .result-card {
                border-radius: 12px;
            }

            .result-header {
                padding: 15px;
            }

            .result-header i {
                font-size: 30px;
                margin-bottom: 8px;
            }

            .result-title {
                font-size: 16px;
            }

            .result-content {
                padding: 15px;
            }

            .result-meta {
                gap: 10px;
            }

            .meta-item {
                font-size: 12px;
            }

            .result-description {
                font-size: 13px;
                margin-bottom: 15px;
            }

            .result-actions {
                flex-direction: column;
                gap: 8px;
            }

            .btn-view-pdf,
            .btn-download,
            .btn-link {
                padding: 10px 15px;
                font-size: 13px;
                width: 100%;
                justify-content: center;
                border-radius: 10px;
            }

            /* Modal */
            .modal-content {
                width: 100%;
                height: 100%;
                border-radius: 0;
                max-width: none;
            }

            .modal-header {
                padding: 12px 15px;
                border-radius: 0;
            }

            .modal-header h3 {
                font-size: 16px;
            }

            .modal-close {
                width: 30px;
                height: 30px;
                font-size: 20px;
            }

            /* Floating Buttons */
            #floating-postular-btn {
                bottom: 80px;
                right: 15px;
                min-width: 80px;
                padding: 8px 10px;
                border-radius: 30px;
            }

            #floating-postular-btn .btn-icon {
                font-size: 16px;
            }

            #floating-postular-btn .btn-text {
                font-size: 9px;
            }

            .floating-badge {
                font-size: 8px;
                padding: 3px 6px;
                top: -5px;
                right: -5px;
            }

            #scrollTop {
                bottom: 20px;
                right: 15px;
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }

        /* Responsive extra small */
        @media (max-width: 480px) {
            .results-hero h1 {
                font-size: 24px;
            }
            
            .ciclo-header h2 {
                font-size: 18px;
            }
        }
    </style>
    <!-- Canvas Confetti Library -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</head>
<body>
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
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <a href="{{ route('home') }}" title="Ir a la página principal">
                <img src="{{ asset('assets_cepre/img/logo/logocepre1.svg') }}" onerror="this.onerror=null; this.src='https://placehold.co/150x60/ffffff/2C5F7C?text=CEPRE';" alt="CEPRE UNAMAD" class="logo">
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="{{ route('home') }}">Inicio</a></li>
                    <li><a href="{{ route('home') }}#cursos">Cursos</a></li>
                    <li><a href="{{ route('home') }}#eventos">Eventos</a></li>
                    <li><a href="{{ route('resultados-examenes.public') }}" class="{{ request()->routeIs('resultados-examenes.public') ? 'active' : '' }}">Resultados</a></li>
                    <li><a href="{{ route('home') }}#nosotros">Nosotros</a></li>
                    <li><a href="{{ route('home') }}#contacto">Contacto</a></li>
                </ul>
            </nav>

            <div class="header-buttons">
                <button class="search-btn" aria-label="Buscar">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fas fa-user"></i>
                    <span>Acceso</span>
                </a>
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Menú">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMobileMenu()"></div>

    <!-- Mobile Navigation Menu -->
    <nav class="mobile-nav-menu" id="mobileNavMenu">
        <div class="mobile-menu-header">
            <h3 style="margin: 0; font-size: 18px;">Menú</h3>
            <button class="mobile-menu-close" onclick="toggleMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul>
            <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Inicio</a></li>
            <li><a href="{{ route('home') }}#cursos"><i class="fas fa-book"></i> Cursos</a></li>
            <li><a href="{{ route('home') }}#eventos"><i class="fas fa-calendar-alt"></i> Eventos</a></li>
            <li><a href="{{ route('resultados-examenes.public') }}" class="{{ request()->routeIs('resultados-examenes.public') ? 'active' : '' }}"><i class="fas fa-trophy"></i> Resultados</a></li>
            <li><a href="{{ route('home') }}#nosotros"><i class="fas fa-users"></i> Nosotros</a></li>
            <li><a href="{{ route('home') }}#contacto"><i class="fas fa-envelope"></i> Contacto</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="results-hero">
        <div class="kene-pattern-overlay"></div>
        <div class="results-hero-content">
            <h1>Resultados de <span>Exámenes</span></h1>
            <p>Centro Pre Universitario UNAMAD - Excelencia Académica para tu Ingreso</p>
        </div>
    </section>

    <!-- Filtros -->
    <section class="filters-section">
        <div class="filters-container">
            <form method="GET" action="{{ route('resultados-examenes.public') }}">
                <div class="filter-group">
                    <label for="ciclo"><i class="fas fa-filter"></i> Filtrar por Ciclo Académico:</label>
                    <select name="ciclo" id="ciclo" onchange="this.form.submit()">
                        <option value="">📚 Todos los ciclos</option>
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

    <!-- Results Section - AGRUPADO POR CICLO -->
    <section class="results-section academic-notebook-pattern">
        <div class="results-container">
            @if($resultados->count() > 0)
                @foreach($resultados as $cicloId => $resultadosCiclo)
                    <div class="ciclo-group animate-on-scroll">
                        <div class="ciclo-header">
                            <h2><i class="fas fa-graduation-cap"></i> {{ $resultadosCiclo->first()->ciclo->nombre }}</h2>
                            <p>{{ $resultadosCiclo->count() }} resultado(s) disponible(s)</p>
                        </div>

                        <div class="results-grid">
                            @foreach($resultadosCiclo as $resultado)
                                @php
                                    // Determine the primary action for the card
                                    $cardAction = '';
                                    if ($resultado->tipo_resultado == 'link' && $resultado->tiene_link) {
                                        $cardAction = "openResourceModal('" . $resultado->link_externo . "', '" . addslashes($resultado->nombre_examen) . "', true)";
                                    } elseif ($resultado->tiene_pdf) {
                                        $cardAction = "openResourceModal('" . route('resultados-examenes.view', $resultado->id) . "', '" . addslashes($resultado->nombre_examen) . "', false)";
                                    } elseif ($resultado->tiene_link) {
                                        $cardAction = "openResourceModal('" . $resultado->link_externo . "', '" . addslashes($resultado->nombre_examen) . "', true)";
                                    }
                                @endphp
                                <div class="result-card animate-on-scroll" @if($cardAction) onclick="{{ $cardAction }}" style="cursor: pointer;" @endif>
                                    <div class="result-header">
                                        <i class="fas fa-file-alt"></i>
                                        <h3 class="result-title">{{ $resultado->nombre_examen }}</h3>
                                    </div>

                                    <div class="result-content">
                                        <div class="result-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-calendar"></i>
                                                <span>{{ $resultado->fecha_examen->format('d/m/Y') }}</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-clock"></i>
                                                <span>{{ $resultado->fecha_publicacion->format('d/m/Y') }}</span>
                                            </div>
                                        </div>

                                        @if($resultado->descripcion)
                                            <p class="result-description">{{ $resultado->descripcion }}</p>
                                        @endif

                                        <div class="result-actions" onclick="event.stopPropagation();">
                                            @if($resultado->tiene_pdf && ($resultado->tipo_resultado == 'pdf' || $resultado->tipo_resultado == 'ambos'))
                                                <button type="button" onclick="openResourceModal('{{ $resultado->archivo_pdf_url }}', '{{ addslashes($resultado->nombre_examen) }}', false)" class="btn-view-pdf">
                                                    <i class="fas fa-eye"></i> Ver PDF
                                                </button>
                                            @endif
                                            
                                            @if($resultado->tiene_link && ($resultado->tipo_resultado == 'link' || $resultado->tipo_resultado == 'ambos'))
                                                <button type="button" onclick="openResourceModal('{{ $resultado->link_externo }}', '{{ addslashes($resultado->nombre_examen) }}', true)" class="btn-link">
                                                    <i class="fas fa-external-link-alt"></i> Ver Enlace
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-results animate-on-scroll">
                    <i class="fas fa-inbox"></i>
                    <h3>No hay resultados disponibles</h3>
                    <p>No se encontraron resultados de exámenes para el ciclo seleccionado.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Modal para ver PDF -->
    <div id="pdfModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Visualizar PDF</h3>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button class="modal-close" onclick="closePdfModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="modal-body">
                <iframe id="pdfViewer" src=""></iframe>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <p>Copyright © 2026 CEPRE UNAMAD. Todos los derechos reservados.</p>
        </div>
    </footer>



    <!-- Scroll to Top -->
    <button id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Canvas for Confetti -->
    <canvas id="confetti-canvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 50000; display: none;"></canvas>

    <script>
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileNavMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            mobileMenu.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Prevent body scroll when menu is open
            if (mobileMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = 'auto';
            }
        }

        // Confetti Animation using canvas-confetti
        function createConfetti() {
            const duration = 3 * 1000;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 100000 };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            const interval = setInterval(function() {
                const timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                const particleCount = 50 * (timeLeft / duration);
                // since particles fall down, start a bit higher than random
                confetti(Object.assign({}, defaults, { 
                    particleCount, 
                    origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
                    colors: ['#A4C639', '#E6007E', '#00A0E3', '#2C5F7C', '#C8D92F']
                }));
                confetti(Object.assign({}, defaults, { 
                    particleCount, 
                    origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
                    colors: ['#A4C639', '#E6007E', '#00A0E3', '#2C5F7C', '#C8D92F']
                }));
            }, 250);
        }

        // Scroll to top functionality
        const scrollTop = document.getElementById('scrollTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollTop.style.display = 'flex';
            } else {
                scrollTop.style.display = 'none';
            }
        });

        scrollTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Animate on scroll
        const animateElements = document.querySelectorAll('.animate-on-scroll');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, { threshold: 0.1 });

        animateElements.forEach(el => observer.observe(el));

        // Modal functions
        function openResourceModal(url, title, isLink = false) {
            console.log('Opening resource modal:', url, title, isLink);
            const modal = document.getElementById('pdfModal');
            const viewer = document.getElementById('pdfViewer');
            const modalTitle = document.getElementById('modalTitle');
            
            if (!modal || !viewer || !modalTitle) {
                console.error('Modal elements not found!');
                return;
            }
            
            let finalUrl = url;
            
            // Optimize Google Drive links for iframe
            if (isLink && url.includes('drive.google.com')) {
                if (url.includes('/view') || url.includes('/edit')) {
                    finalUrl = url.replace(/\/view.*|\/edit.*/, '/preview');
                    console.log('Optimized Drive URL:', finalUrl);
                }
            } else if (!isLink) {
                // Check if mobile device
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                
                if (isMobile) {
                    // Use Google Docs Viewer for mobile PDF display
                    finalUrl = `https://docs.google.com/gview?url=${encodeURIComponent(url)}&embedded=true`;
                } else {
                    // Append toolbar=0 only for internal PDF views on desktop
                    finalUrl += '#toolbar=0';
                }
            }
            
            viewer.src = finalUrl;
            modalTitle.textContent = title;
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            console.log('Modal opened successfully');
            
            // Trigger confetti animation
            setTimeout(() => {
                console.log('Triggering confetti...');
                createConfetti();
            }, 100);
        }

        function closePdfModal() {
            const modal = document.getElementById('pdfModal');
            const viewer = document.getElementById('pdfViewer');
            
            modal.classList.remove('active');
            viewer.src = 'about:blank';
            document.body.style.overflow = 'auto';
        }

        // Close modal on click outside
        document.getElementById('pdfModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePdfModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePdfModal();
            }
        });

        // Trigger confetti on page load
        window.addEventListener('load', () => {
            console.log('Page loaded, triggering initial confetti...');
            setTimeout(createConfetti, 500);
        });
    </script>
</body>
</html>
