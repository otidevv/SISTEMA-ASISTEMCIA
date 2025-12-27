<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Exámenes - CEPRE UNAMAD</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ==================================== */
        /* Variables y Reset Global - IGUAL QUE CEPREUNAMAD */
        /* ==================================== */
        :root {
            --verde-cepre: #A4C639;
            --magenta-unamad: #E6007E;
            --cyan-acento: #00A0E3;
            --azul-oscuro: #2C5F7C;
            --verde-claro: #C8D92F;
            --fondo-cursos: #f7f7f7;
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
            background: linear-gradient(135deg, var(--verde-cepre) 0%, var(--verde-claro) 100%);
            color: white;
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: slideDown 0.5s ease-out;
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
        }

        .help-desk i {
            color: var(--magenta-unamad);
        }

        .info-links {
            display: flex;
            gap: 20px;
        }

        .info-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .info-links a:hover {
            color: var(--cyan-acento);
            transform: translateY(-2px);
        }

        /* ==================================== */
        /* Header */
        /* ==================================== */
        .main-header {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .logo {
            height: 60px;
            transition: transform 0.3s;
        }

        .logo:hover {
            transform: scale(1.05) rotate(-2deg);
        }

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

        .nav-menu a:hover {
            color: var(--verde-cepre);
            transform: translateY(-2px);
        }

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

        .nav-menu a:hover::after {
            width: 100%;
        }

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

        .mobile-nav-menu ul li a:hover {
            background: var(--fondo-cursos);
            color: var(--verde-cepre);
            padding-left: 30px;
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
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-size: 15px;
            min-width: 300px;
            transition: all 0.3s;
            background: white;
            cursor: pointer;
        }

        .filter-group select:focus {
            outline: none;
            border-color: var(--verde-cepre);
            box-shadow: 0 0 0 3px rgba(164, 198, 57, 0.1);
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
            padding: 30px;
            background: linear-gradient(135deg, var(--azul-oscuro), #3a7fa2);
            border-radius: 15px;
            color: white;
            box-shadow: 0 10px 30px rgba(44, 95, 124, 0.3);
        }

        .ciclo-header h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .ciclo-header p {
            font-size: 18px;
            opacity: 0.9;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        /* Tarjetas de Resultados */
        .result-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-top: 4px solid var(--verde-cepre);
            cursor: pointer;
        }

        .result-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(164, 198, 57, 0.2);
        }

        .result-card:nth-child(2) { border-top-color: var(--magenta-unamad); }
        .result-card:nth-child(3) { border-top-color: var(--cyan-acento); }
        .result-card:nth-child(4) { border-top-color: var(--azul-oscuro); }
        .result-card:nth-child(5) { border-top-color: var(--magenta-unamad); }
        .result-card:nth-child(6) { border-top-color: var(--cyan-acento); }

        .result-header {
            padding: 30px;
            background: linear-gradient(135deg, var(--verde-cepre), var(--verde-claro));
            color: white;
            text-align: center;
        }

        .result-card:nth-child(2) .result-header {
            background: linear-gradient(135deg, var(--magenta-unamad), #ff1a8c);
        }

        .result-card:nth-child(3) .result-header {
            background: linear-gradient(135deg, var(--cyan-acento), #00bfff);
        }

        .result-card:nth-child(4) .result-header {
            background: linear-gradient(135deg, var(--azul-oscuro), #3a7fa2);
        }

        .result-card:nth-child(5) .result-header {
            background: linear-gradient(135deg, var(--magenta-unamad), #ff1a8c);
        }

        .result-card:nth-child(6) .result-header {
            background: linear-gradient(135deg, var(--cyan-acento), #00bfff);
        }

        .result-header i {
            font-size: 48px;
            margin-bottom: 15px;
            transition: transform 0.5s;
        }

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
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 12px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-view-pdf:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .btn-download {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 12px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-download:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-link {
            background: linear-gradient(135deg, var(--cyan-acento), #0288d1);
            color: white;
            padding: 12px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 160, 227, 0.4);
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
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideDown 0.3s;
            position: relative;
            z-index: 100000;
        }

        .modal-header {
            padding: 20px 30px;
            background: linear-gradient(135deg, var(--azul-oscuro), #3a7fa2);
            color: white;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 24px;
            font-weight: 700;
        }

        .modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
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
            background: var(--azul-oscuro);
            color: white;
            padding: 40px 0 20px;
            margin-top: 80px;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            text-align: center;
        }

        .footer-content p {
            opacity: 0.8;
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
            bottom: 30px;
            right: 30px;
            background: var(--verde-cepre);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 5px 20px rgba(164, 198, 57, 0.4);
            transition: all 0.3s;
            z-index: 999;
        }

        #scrollTop:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(164, 198, 57, 0.6);
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
                font-size: 12px;
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
                height: 45px;
            }

            .nav-menu {
                display: none;
            }

            .mobile-menu-btn,
            .search-btn {
                display: block;
            }

            .header-buttons .btn-primary {
                padding: 10px 18px;
                font-size: 13px;
                border-radius: 25px;
            }

            .header-buttons .btn-primary span {
                display: inline;
            }

            /* Hero Section */
            .results-hero {
                padding: 40px 0;
            }

            .results-hero h1 {
                font-size: 32px;
                text-shadow: 1px 1px var(--verde-cepre), 2px 2px var(--cyan-acento);
            }

            .results-hero p {
                font-size: 16px;
            }

            /* Filters */
            .filters-section {
                padding: 20px 0;
            }

            .filters-container {
                padding: 0 15px;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .filter-group label {
                font-size: 14px;
            }

            .filter-group select {
                width: 100%;
                min-width: auto;
                padding: 10px 15px;
                font-size: 14px;
            }

            /* Results Section */
            .results-section {
                padding: 40px 0;
            }

            .results-container {
                padding: 0 15px;
            }

            .ciclo-group {
                margin-bottom: 40px;
            }

            .ciclo-header {
                padding: 20px 15px;
                margin-bottom: 25px;
            }

            .ciclo-header h2 {
                font-size: 24px;
            }

            .ciclo-header p {
                font-size: 14px;
            }

            .results-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .result-card {
                border-top-width: 3px;
            }

            .result-header {
                padding: 20px;
            }

            .result-header i {
                font-size: 36px;
            }

            .result-title {
                font-size: 18px;
            }

            .result-content {
                padding: 20px;
            }

            .result-meta {
                gap: 15px;
            }

            .meta-item {
                font-size: 13px;
            }

            .result-description {
                font-size: 14px;
            }

            .result-actions {
                gap: 8px;
            }

            .btn-view-pdf,
            .btn-download,
            .btn-link {
                padding: 10px 16px;
                font-size: 13px;
                flex: 1;
                justify-content: center;
            }

            /* Modal */
            .modal-content {
                width: 95%;
                height: 95vh;
                border-radius: 10px;
            }

            .modal-header {
                padding: 15px 20px;
            }

            .modal-header h3 {
                font-size: 18px;
            }

            .modal-close {
                width: 35px;
                height: 35px;
                font-size: 24px;
            }

            /* No Results */
            .no-results {
                padding: 40px 20px;
            }

            .no-results i {
                font-size: 60px;
            }

            .no-results h3 {
                font-size: 22px;
            }

            .no-results p {
                font-size: 14px;
            }

            /* Footer */
            footer {
                padding: 30px 0 15px;
                margin-top: 40px;
            }

            .footer-content {
                padding: 0 15px;
            }

            .footer-content p {
                font-size: 14px;
            }

            /* Floating Buttons */
            #floating-postular-btn {
                bottom: 90px;
                right: 15px;
                min-width: 90px;
                padding: 10px 12px;
            }

            #floating-postular-btn .btn-icon {
                font-size: 18px;
            }

            #floating-postular-btn .btn-text {
                font-size: 10px;
            }

            .floating-badge {
                font-size: 8px;
                padding: 4px 8px;
                top: -8px;
                right: -15px;
            }

            #scrollTop {
                bottom: 20px;
                right: 15px;
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
        }
    </style>
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
                    <li><a href="{{ route('resultados-examenes.public') }}">Resultados</a></li>
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
            <li><a href="{{ route('resultados-examenes.public') }}"><i class="fas fa-trophy"></i> Resultados</a></li>
            <li><a href="{{ route('home') }}#nosotros"><i class="fas fa-users"></i> Nosotros</a></li>
            <li><a href="{{ route('home') }}#contacto"><i class="fas fa-envelope"></i> Contacto</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="results-hero">
        <div class="results-hero-content">
            <h1>Resultados de <span>Exámenes</span></h1>
            <p>Centro Pre Universitario UNAMAD - Consulta tus resultados</p>
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
    <section class="results-section">
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
                                    if($resultado->tiene_pdf) {
                                        $cardAction = "openPdfModal('" . route('resultados-examenes.view', $resultado->id) . "', '" . addslashes($resultado->nombre_examen) . "')";
                                    } elseif($resultado->tiene_link) {
                                        $cardAction = "window.open('" . $resultado->link_externo . "', '_blank')";
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
                                            @if($resultado->tiene_pdf)
                                                <button onclick="openPdfModal('{{ route('resultados-examenes.view', $resultado->id) }}', '{{ addslashes($resultado->nombre_examen) }}')"; class="btn-view-pdf">
                                                    <i class="fas fa-eye"></i> Ver PDF
                                                </button>
                                                <a href="{{ route('resultados-examenes.download', $resultado->id) }}" class="btn-download" download>
                                                    <i class="fas fa-download"></i> Descargar
                                                </a>
                                            @endif

                                            @if($resultado->tiene_link)
                                                <a href="{{ $resultado->link_externo }}" target="_blank" class="btn-link">
                                                    <i class="fas fa-external-link-alt"></i> Ver Enlace
                                                </a>
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
                <button class="modal-close" onclick="closePdfModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="pdfViewer" src=""></iframe>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <p>Copyright © 2024 CEPRE UNAMAD. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Floating Postulation Button -->
    <a href="{{ route('home') }}#postulacion" id="floating-postular-btn" title="¡Postula Ahora!">
        <i class="fas fa-edit btn-icon"></i>
        <span class="btn-text">Postular</span>
        <span class="floating-badge">¡Abierto!</span>
    </a>

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

        // Confetti Animation
        function createConfetti() {
            console.log('createConfetti function called');
            const canvas = document.getElementById('confetti-canvas');
            if (!canvas) {
                console.error('Canvas element not found');
                return;
            }
            console.log('Canvas found:', canvas);
            
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                console.error('Could not get canvas context');
                return;
            }
            console.log('Canvas context obtained');
            
            try {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                canvas.style.display = 'block';

                const confettiCount = 150;
                const confetti = [];
                const colors = ['#A4C639', '#E6007E', '#00A0E3', '#2C5F7C', '#C8D92F', '#FFD700', '#FF6B6B'];

                for (let i = 0; i < confettiCount; i++) {
                    confetti.push({
                        x: Math.random() * canvas.width,
                        y: Math.random() * canvas.height - canvas.height,
                        r: Math.random() * 6 + 4,
                        d: Math.random() * confettiCount,
                        color: colors[Math.floor(Math.random() * colors.length)],
                        tilt: Math.floor(Math.random() * 10) - 10,
                        tiltAngleIncremental: Math.random() * 0.07 + 0.05,
                        tiltAngle: 0
                    });
                }

                function draw() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    confetti.forEach((c, i) => {
                        ctx.beginPath();
                        ctx.lineWidth = c.r / 2;
                        ctx.strokeStyle = c.color;
                        ctx.moveTo(c.x + c.tilt + c.r / 4, c.y);
                        ctx.lineTo(c.x + c.tilt, c.y + c.tilt + c.r / 4);
                        ctx.stroke();

                        c.tiltAngle += c.tiltAngleIncremental;
                        c.y += (Math.cos(c.d) + 3 + c.r / 2) / 2;
                        c.x += Math.sin(c.d);
                        c.tilt = Math.sin(c.tiltAngle - i / 3) * 15;

                        if (c.y > canvas.height) {
                            confetti[i] = {
                                x: Math.random() * canvas.width,
                                y: -10,
                                r: c.r,
                                d: c.d,
                                color: c.color,
                                tilt: c.tilt,
                                tiltAngleIncremental: c.tiltAngleIncremental,
                                tiltAngle: c.tiltAngle
                            };
                        }
                    });
                }

                let animationId;
                let duration = 4000;
                let startTime = Date.now();

                function animate() {
                    draw();
                    if (Date.now() - startTime < duration) {
                        animationId = requestAnimationFrame(animate);
                    } else {
                        canvas.style.display = 'none';
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                    }
                }

                animate();
            } catch (error) {
                console.error('Error creating confetti:', error);
            }
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
        function openPdfModal(pdfUrl, title) {
            console.log('Opening PDF modal:', pdfUrl, title);
            const modal = document.getElementById('pdfModal');
            const viewer = document.getElementById('pdfViewer');
            const modalTitle = document.getElementById('modalTitle');
            
            if (!modal || !viewer || !modalTitle) {
                console.error('Modal elements not found!');
                return;
            }
            
            viewer.src = pdfUrl;
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
            viewer.src = '';
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
    </script>
</body>
</html>
