@extends('layouts.app')

@section('title', 'Dashboard Docente')

@push('css')
{{-- Incluir Google Fonts y Material Design Icons --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">

{{-- Flatpickr CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

{{-- Quill.js CSS para Editor Enriquecido --}}
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
    /* Estilos para resaltar días con clases en Flatpickr */
    .flatpickr-day.has-clases {
        background-color: var(--primary-light) !important;
        color: var(--primary-text) !important;
        border-color: var(--primary-color) !important;
        font-weight: bold;
    }
    .flatpickr-day.has-clases:hover {
        background-color: var(--primary-color) !important;
        color: #fff !important;
    }
    

    /* -------------------------------------------------------------------------- */
    /* Variables de Diseño Avanzado                                                */
    /* -------------------------------------------------------------------------- */
    :root {
        --font-family-sans-serif: 'Inter', sans-serif;
        --bg-color: #f8f9fa;
        --card-bg: rgba(255, 255, 255, 0.9);
        --text-color: #1e293b;
        --text-muted: #64748b;
        --primary-color: #e2007a; /* MAGENTA CEPRE */
        --primary-hover: #9b0058;
        --primary-light: rgba(226, 0, 122, 0.1);
        --primary-text: #e2007a;
        --navy-color: #1a237e; /* NAVY CEPRE */
        --success-color: #93c01f; /* VERDE CEPRE */
        --success-light: #f0fdf4;
        --success-text: #166534;
        --warning-color: #ffd700; /* ORO CEPRE */
        --warning-light: #fffbeb;
        --warning-text: #78350f;
        --danger-color: #ef4444;
        --danger-light: #fef2f2;
        --danger-text: #991b1b;
        --info-color: #00aeef; /* CIAN CEPRE */
        --info-light: #eff6ff;
        --info-text: #1e40af;
        --border-color: #edf2f9;
        --border-radius: 1.25rem;
        --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        --shadow: 0 10px 15px -3px rgb(0 0 0 / 0.07);
        --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Premium Custom Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { 
        background: linear-gradient(to bottom, var(--primary-color), var(--navy-color)); 
        border-radius: 10px; 
    }
    ::-webkit-scrollbar-thumb:hover { background: var(--navy-color); }

    /* Staggered Reveal Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .reveal-item { 
        opacity: 0; 
        animation: fadeInUp 0.5s cubic-bezier(0.23, 1, 0.32, 1) forwards; 
    }
    .reveal-delay-1 { animation-delay: 0.1s; }
    .reveal-delay-2 { animation-delay: 0.2s; }
    .reveal-delay-3 { animation-delay: 0.3s; }

    /* Glass Header Token */
    .agenda-glass-header {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(15px) !important;
        -webkit-backdrop-filter: blur(15px) !important;
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07) !important;
    }

    body {
        background-color: #f8fafc;
        font-family: 'Outfit', 'Inter', sans-serif;
        color: var(--text-color);
        overflow-x: hidden;
    }

    /* -------------------------------------------------------------------------- */
    /* Encabezado de Bienvenida (Efecto Obsidian Glass)                           */
    /* -------------------------------------------------------------------------- */
    .welcome-header {
        background: linear-gradient(135deg, var(--navy-color) 0%, var(--primary-hover) 100%);
        border-radius: var(--border-radius);
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 15px 40px rgba(26, 35, 126, 0.25);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.15);
        min-height: 260px;
        display: flex;
        align-items: center;
    }

    .welcome-header .welcome-text .welcome-title {
        font-size: 2.2rem;
        font-weight: 850;
        color: #ffffff;
        letter-spacing: -0.5px;
        text-shadow: 0 5px 15px rgba(0,0,0,0.3);
        line-height: 1.1;
        margin-bottom: 0.5rem;
    }

    .welcome-header .welcome-text {
        position: relative;
        z-index: 20;
    }

    .welcome-header .welcome-text .welcome-subtitle {
        font-size: 1.05rem;
        color: rgba(255,255,255,0.95) !important;
        font-weight: 500;
        margin-top: 0.5rem;
        text-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }

    /* Premium Stat Box Depth & Vibrancy */
    .sidebar-card .stat-box {
        background: #ffffff;
        border-radius: 18px;
        padding: 1.25rem 0.5rem;
        text-align: center;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 95px;
        position: relative;
        overflow: hidden;
    }

    .sidebar-card .stat-box::before {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 6px; height: 100%;
        background: currentColor;
    }

    .sidebar-card .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        border-color: currentColor;
    }

    .sidebar-card .stat-box .h5 {
        font-weight: 850;
        margin-bottom: 2px;
        line-height: 1;
        font-size: 1.4rem;
        color: var(--navy-color); /* Contraste fuerte */
    }

    /* Performance Circular Rings Upgrade */
    .performance-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        padding: 1rem 0;
    }

    .performance-item {
        position: relative;
        text-align: center;
    }

    .performance-ring {
        position: relative;
        width: 82px;
        height: 82px;
        margin: 0 auto 0.75rem;
        filter: drop-shadow(0 4px 10px rgba(0,0,0,0.08));
    }

    .performance-ring svg {
        transform: rotate(-90deg);
        width: 100%;
        height: 100%;
    }

    .performance-ring circle {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
    }

    .performance-ring .ring-bg {
        stroke: #f1f5f9;
        stroke-opacity: 0.8;
    }

    .performance-ring .ring-progress {
        transition: stroke-dashoffset 2s cubic-bezier(0.4, 0, 0.2, 1);
        stroke-dasharray: 219.9;
        stroke-dashoffset: 219.9;
        filter: drop-shadow(0 0 5px currentColor);
    }

    .performance-value {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.1rem;
        font-weight: 900;
        color: var(--navy-color);
        letter-spacing: -0.5px;
    }

    .performance-label {
        font-size: 0.7rem;
        font-weight: 800;
        color: var(--text-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.25rem;
    }

    .time-display {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        font-size: 1.2rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 30;
    }

    .mascot-stage {
        position: absolute;
        bottom: -20px;
        right: -60px;
        width: 250px;
        z-index: 10;
        pointer-events: none;
        transition: all 0.5s ease;
        opacity: 0.85;
    }

    .btn-pdf-download {
        background: linear-gradient(135deg, #ffd700 0%, #ffc107 100%);
        color: var(--navy-color) !important;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 850;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 10px 25px rgba(255, 215, 0, 0.35);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        text-decoration: none !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1px;
        position: relative;
        z-index: 30;
    }
    .btn-pdf-download:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 35px rgba(255, 215, 0, 0.4);
    }

    /* -------------------------------------------------------------------------- */
    /* Tarjetas de Estadísticas                                                   */
    /* -------------------------------------------------------------------------- */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .stat-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -5px rgba(0,0,0,0.1), 0 8px 20px -6px rgba(0,0,0,0.05);
    }
    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15);
        border-color: var(--primary-color);
    }

    .stat-card .stat-icon {
        flex-shrink: 0;
        padding: 0.75rem;
        border-radius: 0.75rem;
        font-size: 1.5rem;
        color: white;
        background-image: linear-gradient(135deg, var(--color-from), var(--color-to));
        box-shadow: 0 4px 8px -2px rgba(0,0,0,0.2);
    }
    .stat-card.primary .stat-icon { --color-from: var(--primary-color); --color-to: var(--primary-hover); }
    .stat-card.warning .stat-icon { --color-from: var(--warning-color); --color-to: #d9b700; }
    .stat-card.success .stat-icon { --color-from: var(--success-color); --color-to: #7da419; }
    .stat-card.info .stat-icon { --color-from: var(--info-color); --color-to: #0081b3; }

    .stat-card .stat-info .stat-value {
        font-size: 2.25rem;
        font-weight: 800;
        line-height: 1;
        color: var(--text-color);
    }
    .stat-card .stat-info .stat-label {
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 0.25rem;
    }

    /* NUEVO: Estilos para métricas secundarias */
    .stat-card .stat-info .stat-secondary {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .metric-trend {
        font-size: 0.7rem;
        padding: 0.15rem 0.4rem;
        border-radius: 0.25rem;
        font-weight: 600;
    }
    .metric-trend.up {
        background: var(--success-light);
        color: var(--success-text);
    }
    .metric-trend.down {
        background: var(--danger-light);
        color: var(--danger-text);
    }

    /* -------------------------------------------------------------------------- */
    /* Tarjetas de Sesión (Diseño Timeline + Tarjeta Completa y coloreada)        */
    /* -------------------------------------------------------------------------- */
    .session-timeline {
        position: relative;
    }

    /* Date Picker Styling */
    .date-picker-container {
        position: relative;
        max-width: 250px;
    }
    .date-picker-container input {
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        padding-left: 2.5rem;
        font-weight: 600;
        color: var(--navy-color);
        transition: var(--transition);
        cursor: pointer;
    }
    .date-picker-container input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px var(--primary-light);
        outline: none;
    }
    .date-picker-container i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-color);
        z-index: 5;
    }

    .session-card {
        position: relative;
        padding: 1.5rem;
        padding-left: 3.5rem;
        margin-bottom: 1.5rem;
        background: var(--card-bg);
        border-radius: var(--border-radius);
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        overflow: hidden;
    }
    .session-card:hover {
        transform: translateX(5px);
        box-shadow: var(--shadow);
        border-color: var(--primary-color);
    }

    .session-card::before { 
        content: '';
        position: absolute;
        left: 1.25rem;
        top: 2rem;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background-color: var(--dot-color);
        z-index: 1;
        box-shadow: 0 0 10px var(--dot-color);
    }

    .session-card.active {
        background: linear-gradient(to right, rgba(226, 0, 122, 0.05), white);
        border-left: 4px solid var(--primary-color);
    }

    /* Colores Institucionales para la Línea de Tiempo */
    .session-card.programmed { --dot-color: var(--navy-color); }
    .session-card.completed { --dot-color: var(--success-color); }
    .session-card.pending { --dot-color: var(--warning-color); }
    .session-card.active { --dot-color: var(--info-color); }
    .session-card.no-access { --dot-color: var(--danger-color); }

    .session-card-content {
        border-radius: var(--border-radius);
        padding: 1.5rem;
        transition: var(--transition);
        border: 1px solid rgba(0,0,0,0.05);
        background: white;
    }

    .session-card:hover .session-card-content {
        transform: translateY(-5px) scale(1.01);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    }

    /* Estilos por Estado con Colores Oficiales */
    .session-card.active .session-card-content { 
        background-color: #f0f9ff; 
        border-color: var(--info-color); 
        box-shadow: 0 0 25px rgba(0, 174, 239, 0.15); 
    }
    .session-card.completed .session-card-content { 
        background-color: #f7fee7; 
        border-color: var(--success-color); 
    }
    .session-card.pending .session-card-content { 
        background-color: #fffbeb; 
        border-color: var(--warning-color); 
    }
    
    .session-details {
        font-size: 0.95rem;
        color: var(--text-muted);
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .session-details strong {
        color: var(--navy-color);
        font-weight: 700;
    }
    
    .tema-registrado {
        background-color: var(--primary-light);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-top: 1rem;
        font-size: 0.9rem;
        border: 1px dashed var(--primary-color);
    }
    .tema-registrado strong {
        color: var(--primary-color);
    }

    /* NUEVO: Estilos para información avanzada de sesión (Glass Metrics) */
    .session-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 1rem;
        margin: 1.5rem 0;
        padding: 1rem;
        background: rgba(248, 249, 250, 0.5);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
    }

    .session-metric {
        text-align: center;
    }

    .session-metric-value {
        display: block;
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--navy-color);
    }

    .session-metric-label {
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 0.25rem;
    }

    /* Barra de progreso para clases en curso */
    .progress-container {
        margin: 1.5rem 0;
        padding: 1rem;
        background: white;
        border-radius: 1rem;
        border: 1px solid var(--info-color);
        box-shadow: 0 5px 15px rgba(0, 174, 239, 0.05);
    }

    .progress-bar-container {
        width: 100%;
        height: 10px;
        background: #f1f5f9;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0.75rem;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--info-color), var(--primary-color));
        border-radius: 10px;
        transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    /* Indicadores de estado de tiempo */
    .time-pill {
        font-size: 0.7rem;
        padding: 0.35rem 1rem;
        border-radius: 50px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        text-transform: uppercase;
    }
    .time-pill.current {
        background: rgba(0, 174, 239, 0.1);
        color: var(--info-color);
        border: 1px solid var(--info-color);
        box-shadow: 0 0 15px rgba(0, 174, 239, 0.2);
        animation: pulse-active 2s infinite;
    }
    @keyframes pulse-active {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }
    .time-pill.finished {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .time-pill.upcoming {
        background: var(--primary-light);
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
    }

    /* -------------------------------------------------------------------------- */
    /* Componentes UI (Botones, Badges, etc)                                      */
    /* -------------------------------------------------------------------------- */
    .action-button {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--navy-color) 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(226, 0, 122, 0.2);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .action-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(226, 0, 122, 0.3);
        filter: brightness(1.1);
        color: white;
    }

    .status-badge {
        padding: 0.45rem 1.25rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid transparent;
    }
    .status-badge.success { background-color: var(--success-light); color: var(--success-text); border-color: var(--success-color); }
    .status-badge.warning { background-color: var(--warning-light); color: var(--warning-text); border-color: var(--warning-color); }
    .status-badge.info { background-color: var(--info-light); color: var(--info-text); border-color: var(--info-color); }
    .status-badge.active { background-color: rgba(0, 174, 239, 0.1); color: var(--info-color); border-color: var(--info-color); box-shadow: 0 0 10px rgba(0, 174, 239, 0.2); }

    /* Modal */
    .modal-content {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--shadow-lg);
        background-color: var(--bg-color);
    }
    .modal-header {
        background: var(--primary-color);
        color: white;
        border-bottom: none;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        padding: 1.5rem;
    }
    .modal-title {
        font-weight: 700;
    }
    .form-control {
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgb(79 70 229 / 15%);
    }
    
    /* Estilos para el modal de anuncios */
    #modalAnuncios .modal-body ul {
        list-style: none;
        padding-left: 0;
    }
    #modalAnuncios .modal-body li {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    #modalAnuncios .modal-body .announcement-icon {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin-top: 0.25rem;
    }

    /* -------------------------------------------------------------------------- */
    /* Estilos Responsivos                                                        */
    /* -------------------------------------------------------------------------- */
    @media (max-width: 768px) {
        .welcome-header .welcome-text .welcome-title {
            font-size: 1.5rem;
        }
        .main-content-title {
            font-size: 1.25rem;
        }
        .session-metrics {
            grid-template-columns: repeat(2, 1fr);
        }
        .time-indicator {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
            display: block;
            text-align: center;
            margin-top: 0.5rem;
        }
        .session-time {
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }
        
        /* NUEVO: Responsive para tarjetas de curso */
        .courses-summary-container {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1rem;
        }
        
        .view-toggle {
            width: 100%;
        }
        
        .toggle-btn {
            flex: 1;
            justify-content: center;
            font-size: 0.75rem;
            padding: 0.4rem 0.75rem;
        }
        
        .toggle-btn i {
            font-size: 0.9rem;
        }
        
        .course-group-header {
            flex-direction: column;
            gap: 0.75rem;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        .dashboard-container {
            padding: 0 1rem;
            margin-top: 1rem;
        }
        .welcome-header {
            padding: 1.5rem;
            text-align: center;
            justify-content: center;
        }
        .main-content-card, .sidebar-card {
            padding: 1.5rem;
        }
        .session-card-content {
            padding: 1rem;
        }
        .session-timeline {
            padding-left: 0;
        }
        .session-timeline::before, .session-card::before {
            display: none; /* Ocultar línea de tiempo en móviles */
        }
        .session-card {
            padding-left: 0;
        }
        .session-metrics {
            grid-template-columns: 1fr;
        }
        .time-indicator {
            font-size: 0.6rem;
            padding: 0.15rem 0.4rem;
            margin-top: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }
        .session-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .session-time {
            align-self: flex-end;
            text-align: right;
        }
    }

    /* -------------------------------------------------------------------------- */
    /* NUEVO: Tarjetas de Resumen por Curso                                      */
    /* -------------------------------------------------------------------------- */
    .courses-summary-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .course-summary-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        border: 2px solid var(--border-color);
        cursor: pointer;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .course-summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--curso-color);
    }

    .course-summary-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
        border-color: var(--curso-color);
    }

    .course-summary-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .course-icon {
        width: 50px;
        height: 50px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .course-title h6 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-color);
    }

    .course-title small {
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    .course-summary-stats {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 0.5rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .stat-item i {
        color: var(--curso-color);
    }

    .course-summary-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .course-summary-status.completado {
        background: var(--success-light);
        color: var(--success-text);
    }

    .course-summary-status.en_curso {
        background: var(--info-light);
        color: var(--info-text);
    }

    .course-summary-status.pendiente {
        background: var(--warning-light);
        color: var(--warning-text);
    }

    .course-summary-status.sin_registro {
        background: var(--danger-light);
        color: var(--danger-text);
    }

    .course-summary-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
    }

    .course-summary-footer small {
        font-size: 0.75rem;
    }

    /* -------------------------------------------------------------------------- */
    /* NUEVO: Toggle de Vista                                                    */
    /* -------------------------------------------------------------------------- */
    .view-toggle {
        display: flex;
        gap: 0.25rem;
        background: var(--border-color);
        padding: 0.25rem;
        border-radius: 0.5rem;
    }

    .toggle-btn {
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .toggle-btn:hover {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary-color);
    }

    .toggle-btn.active {
        background: var(--primary-color);
        color: white;
        box-shadow: var(--shadow-sm);
    }

    .toggle-btn i {
        font-size: 1rem;
    }

    /* -------------------------------------------------------------------------- */
    /* NUEVO: Grupos de Curso                                                    */
    /* -------------------------------------------------------------------------- */
    .course-grouped-view {
        display: flex;
        flex-direction: column;
        gap: 2.5rem;
    }

    .course-group {
        position: relative;
    }

    .course-group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        color: white;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .course-group-header:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .course-group-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .course-group-title i {
        font-size: 1.5rem;
    }

    .course-group-title h6 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .course-group-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Animación de highlight para scroll */
    @keyframes highlight {
        0%, 100% { background: transparent; }
        50% { background: var(--primary-light); }
    }

    .course-group.highlight {
        animation: highlight 1s ease;
    }

    /* -------------------------------------------------------------------------- */
    /* NUEVO: Modal Moderno de Registro de Tema - CLEAN & PROFESSIONAL           */
    /* -------------------------------------------------------------------------- */
    .modern-modal .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        background: #ffffff;
    }

    .modern-modal-header {
        background: #ffffff;
        color: var(--navy-color);
        padding: 1.5rem 2.5rem;
        border-radius: 20px 20px 0 0;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
    }

    .modal-header-content {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex: 1;
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        background: var(--primary-light);
        color: var(--primary-color);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .modern-modal-header .modal-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--navy-color);
        letter-spacing: -0.5px;
    }

    .modern-modal-header small {
        color: var(--text-muted);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .modern-modal-header .btn-close {
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .modern-modal-body {
        padding: 2.5rem;
    }

    .session-info-card {
        display: flex;
        gap: 2rem;
        padding: 1.25rem;
        background: #f8fafc;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }

    .session-info-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-color);
    }

    .modern-label {
        font-weight: 800;
        color: var(--navy-color);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Editor Wrapper Styling (Quill) */
    .editor-wrapper {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        background: white;
        transition: all 0.3s ease;
    }

    .editor-wrapper:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px var(--primary-light);
    }

    .ql-toolbar.ql-snow {
        border: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
        background: #f8fafc;
        padding: 10px 15px !important;
    }

    .ql-container.ql-snow {
        border: none !important;
        font-family: 'Inter', sans-serif !important;
    }

    .textarea-footer {
        padding: 10px 15px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .tips-card {
        background: rgba(147, 192, 31, 0.05);
        border-left: 4px solid var(--success-color);
        border-radius: 12px;
        padding: 1.25rem;
        margin-top: 2rem;
    }

    .tips-header {
        font-weight: 700;
        color: #3f6212;
        margin-bottom: 8px;
        font-size: 0.85rem;
    }

    .modern-modal-footer {
        padding: 1.5rem 2.5rem;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
        border-radius: 0 0 20px 20px;
    }

    .btn-save-theme {
        background: var(--navy-color);
        color: white;
        border: none;
        padding: 0.85rem 2.5rem;
        font-weight: 700;
        border-radius: 12px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 15px -3px rgba(26, 35, 126, 0.2);
    }

    .btn-save-theme:hover {
        background: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(226, 0, 122, 0.25);
        color: white;
    }

    .btn-outline-secondary {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.85rem 1.5rem;
        border-color: #e2e8f0;
        color: var(--text-muted);
    }

    /* --- RESPONSIVE FIXES --- */
    @media (max-width: 992px) {
    @media (max-width: 992px) {
        .dashboard-container {
            padding: 1.5rem;
        }
        
        .main-content-header {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        /* Target the d-flex container holding toggle and form */
        .main-content-header > .d-flex {
            flex-direction: column;
            width: 100%;
            gap: 1rem !important; /* Force gap */
        }
        
        .date-selector-wrapper {
            width: 100%;
        }
        
        #form-agenda {
            width: 100%;
        }

        .view-toggle {
            width: 100%;
            display: flex;
        }

        .toggle-btn {
            flex: 1;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .welcome-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
            padding: 1.5rem;
        }

        .time-display {
            width: 100%;
            justify-content: center;
        }

        .stats-container {
            grid-template-columns: 1fr; /* Stack stats cards */
        }
        
        .session-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .session-time {
            align-self: flex-start;
            margin-top: 0.25rem;
        }
        
        .session-footer {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .session-footer .d-flex {
            justify-content: space-between;
            width: 100%;
        }
        
        .status-badge {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .dashboard-container {
            padding: 1rem;
        }
        
        .main-content-title {
            font-size: 1.25rem;
            text-align: center;
        }

        .date-selector-wrapper input {
            text-align: center;
            padding-left: 2.75rem !important; /* Ensure icon doesn't overlap text */
        }
    }

    /* Estilos para el banner de Sábado Rotativo */
    .saturday-rotation-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        animation: pulse-glow 2s ease-in-out infinite;
    }

    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); }
        50% { box-shadow: 0 4px 25px rgba(99, 102, 241, 0.5); }
    }

    .rotation-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        font-size: 1.5rem;
    }

    .rotation-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .rotation-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .rotation-details {
        font-size: 0.9rem;
        opacity: 0.95;
    }

    .rotation-details strong {
        color: #fbbf24;
    }

    @media (max-width: 576px) {
        .saturday-rotation-banner {
            flex-direction: column;
            text-align: center;
            padding: 1rem;
        }
        
        .rotation-info {
            align-items: center;
        }
    }

    /* Estilos para advertencia de ciclos conflictivos */
    .cycle-warning-banner {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 0.875rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        box-shadow: 0 3px 10px rgba(245, 158, 11, 0.3);
    }

    .cycle-warning-banner i {
        font-size: 1.25rem;
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Header de Bienvenida (NUEVO: Obsidian Stage con Jaguar Optimizado) -->
    <div class="welcome-header reveal-item">
        <div class="row w-100 align-items-center">
            <div class="col-xl-7 col-lg-6">
                <div class="welcome-text">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill px-3 py-1 fw-bold shadow-sm" 
                              style="background: rgba(255, 255, 255, 0.15); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.6rem; letter-spacing: 1px;">
                            <i class="mdi mdi-crown text-warning me-1"></i> DOCENTE MASTER CENTER
                        </span>
                    </div>
                    <h1 class="welcome-title text-uppercase">¡Bienvenido, {{ $user->nombre }}!</h1>
                    <div class="welcome-subtitle">
                        <i class="mdi mdi-calendar-clock me-1 text-white"></i> {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
                        @if(isset($eficiencia))
                            <span class="mx-2 opacity-50">|</span> <i class="mdi mdi-lightning-bolt text-warning me-1"></i> Eficiencia: <strong>{{ $eficiencia }}%</strong>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-4 text-lg-end d-flex flex-column align-items-lg-end gap-2 mt-3 mt-lg-0" style="z-index: 50;">
                <div class="time-display">
                    <i class="mdi mdi-clock-digital"></i>
                    <span id="current-time">{{ \Carbon\Carbon::now()->format('H:i:s A') }}</span>
                </div>
                <a href="#" class="btn-pdf-download">
                    <i class="mdi mdi-file-pdf-box"></i> REPORTE ACADÉMICO
                </a>
            </div>
        </div>

        <div class="mascot-stage d-none d-xl-block">
             <img src="{{ asset('assets/img/mascotadashboard.png') }}" 
                  style="width: 100%; height: auto; transform: scaleX(-1) rotate(5deg); filter: drop-shadow(-15px 15px 30px rgba(0,0,0,0.4));"
                  alt="Jaguar CEPRE">
        </div>
    </div>

    {{-- Indicador de Sábado Rotativo --}}
    @if(isset($infoRotacion) && $infoRotacion['es_sabado'] && isset($infoRotacion['dia_horario']) && $infoRotacion['dia_horario'] !== 'Sábado')
        <div class="saturday-rotation-banner">
            <div class="rotation-icon">
                <i class="mdi mdi-calendar-sync"></i>
            </div>
            <div class="rotation-info">
                <span class="rotation-title">📅 Sábado Rotativo</span>
                <span class="rotation-details">
                    Hoy corresponde horario de <strong>{{ $infoRotacion['dia_horario'] }}</strong>
                    @if(isset($infoRotacion['semana']))
                        • <strong>Semana {{ $infoRotacion['semana'] }}</strong> del ciclo
                    @endif
                </span>
            </div>
        </div>
    @endif

    {{-- Advertencia de Ciclos Conflictivos --}}
    @if(isset($advertenciaCiclos))
        <div class="cycle-warning-banner">
            <i class="mdi mdi-alert-circle"></i>
            <span>{{ $advertenciaCiclos }}</span>
        </div>
    @endif

    <!-- Estadísticas Mejoradas -->
    <div class="stats-container">
        <div class="stat-card primary">
            <div class="stat-icon"><i class="mdi mdi-calendar-multiselect"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $sesionesHoy }}</div>
                <div class="stat-label">Sesiones para Hoy</div>
                @if(isset($horasReales) && isset($horasProgramadas))
                    <div class="stat-secondary">
                        <i class="mdi mdi-chart-line"></i>
                        Real: {{ $horasReales }}h / Programadas: {{ $horasProgramadas }}h
                    </div>
                @endif
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon"><i class="mdi mdi-file-document-edit-outline"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $sesionesPendientes }}</div>
                <div class="stat-label">Pendientes de Tema</div>
                @if(isset($puntualidad))
                    <div class="stat-secondary">
                        <i class="mdi mdi-timer-outline"></i>
                        Puntualidad: {{ $puntualidad }}%
                        @if($puntualidad >= 95)
                            <span class="metric-trend up">Excelente</span>
                        @elseif($puntualidad >= 85)
                            <span class="metric-trend">Buena</span>
                        @else
                            <span class="metric-trend down">Mejorar</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon"><i class="mdi mdi-clock-check-outline"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $horasHoy }}</div>
                <div class="stat-label">Total Horas Hoy</div>
                @if(isset($horasReales) && $horasReales > 0)
                    <div class="stat-secondary">
                        <i class="mdi mdi-check-circle-outline"></i>
                        Confirmadas por registros
                    </div>
                @else
                    <div class="stat-secondary">
                        <i class="mdi mdi-clock-alert-outline"></i>
                        Horas programadas
                    </div>
                @endif
            </div>
        </div>
        
        <div class="stat-card info">
            <div class="stat-icon"><i class="mdi mdi-cash-multiple"></i></div>
            <div class="stat-info">
                <div class="stat-value">S/. {{ number_format($pagoEstimadoHoy, 2) }}</div>
                <div class="stat-label">Pago Estimado</div>
                @if(isset($horasReales) && $horasReales > 0)
                    <div class="stat-secondary">
                        <i class="mdi mdi-check-circle-outline"></i>
                        Confirmado por registros
                    </div>
                @else
                    <div class="stat-secondary">
                        <i class="mdi mdi-clock-alert-outline"></i>
                        Estimado programado
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- NUEVO: Tarjetas de Resumen por Curso --}}
    @if(isset($sesionesAgrupadasPorCurso) && count($sesionesAgrupadasPorCurso) > 0)
        <div class="courses-summary-container">
            @foreach($sesionesAgrupadasPorCurso as $cursoId => $cursoData)
                @php
                    $curso = $cursoData['curso'];
                    $stats = $cursoData['estadisticas'];
                    
                    // Determinar color y estado general del curso
                    $colores = ['#6366f1', '#3b82f6', '#22c55e', '#f59e0b', '#ec4899', '#8b5cf6'];
                    $colorIndex = $cursoId % count($colores);
                    $colorCurso = $colores[$colorIndex];
                    
                    $estadoGeneral = 'completado';
                    $estadoTexto = 'Todas completas';
                    $estadoIcono = 'mdi-check-circle';
                    
                    if ($stats['en_curso'] > 0) {
                        $estadoGeneral = 'en_curso';
                        $estadoTexto = 'En curso';
                        $estadoIcono = 'mdi-play-circle';
                    } elseif ($stats['temas_pendientes'] > 0) {
                        $estadoGeneral = 'pendiente';
                        $estadoTexto = $stats['temas_pendientes'] . ' tema' . ($stats['temas_pendientes'] > 1 ? 's' : '') . ' pendiente' . ($stats['temas_pendientes'] > 1 ? 's' : '');
                        $estadoIcono = 'mdi-alert-circle';
                    } elseif ($stats['sin_registro'] > 0) {
                        $estadoGeneral = 'sin_registro';
                        $estadoTexto = 'Sin registro';
                        $estadoIcono = 'mdi-close-circle';
                    }
                @endphp
                
                <div class="course-summary-card {{ $estadoGeneral }}" data-curso-id="{{ $cursoId }}" style="--curso-color: {{ $colorCurso }};">
                    <div class="course-summary-header">
                        <div class="course-icon" style="background: linear-gradient(135deg, {{ $colorCurso }}, {{ $colorCurso }}dd);">
                            <i class="mdi mdi-book-open-variant"></i>
                        </div>
                        <div class="course-title">
                            <h6>{{ $curso->nombre }}</h6>
                            <small>{{ $stats['total_sesiones'] }} sesión{{ $stats['total_sesiones'] != 1 ? 'es' : '' }} hoy</small>
                        </div>
                    </div>
                    
                    <div class="course-summary-stats">
                        <div class="stat-item">
                            <i class="mdi mdi-clock-outline"></i>
                            <span>{{ $stats['total_horas_programadas'] }}h programadas</span>
                        </div>
                        @if($stats['total_horas_reales'] > 0)
                            <div class="stat-item">
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>{{ $stats['total_horas_reales'] }}h reales</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="course-summary-status {{ $estadoGeneral }}">
                        <i class="mdi {{ $estadoIcono }}"></i>
                        <span>{{ $estadoTexto }}</span>
                    </div>
                    
                    <div class="course-summary-footer">
                        <small class="text-muted">Click para ver sesiones</small>
                        <i class="mdi mdi-chevron-down"></i>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row">
        <!-- Columna Principal: Sesiones de Hoy -->
        <div class="col-lg-8">
            <div class="main-content-card" id="agenda-section">
                <!-- Agenda Header con Selector Premium -->
                <!-- Agenda Header con Selector Premium -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 p-3 agenda-glass-header reveal-item">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">
                            Agenda del <span class="text-primary">{{ $fechaSeleccionada->translatedFormat('d \d\e F') }}</span>
                        </h4>
                        <p class="text-muted small mb-0">
                            <i class="mdi mdi-history me-1"></i> {{ ucfirst($fechaSeleccionada->diffForHumans()) }}
                        </p>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        @if(isset($sesionesAgrupadasPorCurso) && count($sesionesAgrupadasPorCurso) > 0)
                            <div class="view-toggle d-none d-md-flex">
                                <button class="toggle-btn active" data-view="course" title="Vista por Cursos">
                                    <i class="mdi mdi-view-module"></i>
                                </button>
                                <button class="toggle-btn" data-view="timeline" title="Vista Cronológica">
                                    <i class="mdi mdi-timeline-clock"></i>
                                </button>
                            </div>
                        @endif

                        <form action="{{ route('dashboard') }}" method="GET" id="form-agenda" class="date-picker-container m-0">
                            <i class="mdi mdi-calendar-search"></i>
                            <input type="date" name="fecha" id="fecha-agenda" 
                                   value="{{ $fechaSeleccionada->format('Y-m-d') }}"
                                   onchange="this.form.submit()"
                                   title="Cambiar fecha">
                        </form>
                    </div>
                </div>

                {{-- NUEVO: Vista por Curso (por defecto) --}}
                @if(isset($sesionesAgrupadasPorCurso) && count($sesionesAgrupadasPorCurso) > 0)
                    <div id="course-view" class="course-grouped-view">
                        @foreach($sesionesAgrupadasPorCurso as $cursoId => $cursoData)
                            @php
                                $curso = $cursoData['curso'];
                                $stats = $cursoData['estadisticas'];
                                // PALETA INSTITUCIONAL DINÁMICA
                                $colores = [
                                    'var(--navy-color)',
                                    'var(--primary-color)',
                                    'var(--success-color)',
                                    'var(--info-color)',
                                    'var(--warning-color)',
                                    'var(--primary-hover)'
                                ];
                                $colorIndex = $loop->index % count($colores);
                                $colorCurso = $colores[$colorIndex];
                            @endphp
                            
                            <div class="course-group reveal-item reveal-delay-{{ ($loop->index % 4) + 1 }}" id="course-{{ $cursoId }}">
                                <div class="course-group-header" style="background: linear-gradient(135deg, {{ $colorCurso }}, {{ $colorCurso }}dd);">
                                    <div class="course-group-title">
                                        <i class="mdi mdi-book-open-variant"></i>
                                        <h6>{{ $curso->nombre }}</h6>
                                    </div>
                                    <div class="course-group-badge">
                                        {{ $stats['total_sesiones'] }} sesión{{ $stats['total_sesiones'] != 1 ? 'es' : '' }}
                                    </div>
                                </div>
                                
                                <div class="session-timeline">
                                    @foreach($cursoData['sesiones'] as $item)
                                        @php
                                            $horario = $item['horario'];
                                            $asistencia = $item['asistencia'];
                                            $horaInicio = \Carbon\Carbon::parse($horario->hora_inicio);
                                            $horaFin = \Carbon\Carbon::parse($horario->hora_fin);

                                            // Determinar estado de la sesión
                                            $estadoConfig = ['clase' => 'programmed', 'texto' => 'PROGRAMADA', 'color' => 'info', 'icono' => 'mdi-clock-outline'];
                                            if ($asistencia) { 
                                                $estadoConfig = ['clase' => 'completed', 'texto' => 'COMPLETADA', 'color' => 'success', 'icono' => 'mdi-check-all']; 
                                            } elseif ($item['dentro_horario']) { 
                                                $estadoConfig = ['clase' => 'active', 'texto' => 'EN CURSO', 'color' => 'active', 'icono' => 'mdi-play-circle']; 
                                            } elseif ($item['clase_terminada'] && $item['tiene_registros']) { 
                                                $estadoConfig = ['clase' => 'pending', 'texto' => 'PENDIENTE', 'color' => 'warning', 'icono' => 'mdi-alert-circle-check-outline']; 
                                            } elseif ($item['clase_terminada'] && !$item['tiene_registros']) { 
                                                $estadoConfig = ['clase' => 'no-access', 'texto' => 'SIN REGISTRO', 'color' => 'danger', 'icono' => 'mdi-close-circle-outline']; 
                                            }

                                            $tiempoInfo = $item['tiempo_info'] ?? null;
                                            $progreso = $item['progreso_clase'] ?? 0;
                                            $eficienciaClase = $item['eficiencia'] ?? null;
                                        @endphp

                                        <div class="session-card {{ $estadoConfig['clase'] }} reveal-item reveal-delay-{{ ($loop->index % 4) + 1 }}" id="session-{{ $horario->id }}">
                                            <div class="session-card-content">
                                                <div class="session-header">
                                                    <h6 class="course-name">{{ $horario->curso->nombre ?? 'Sin curso' }}</h6>
                                                    <div class="session-time">
                                                        <i class="mdi mdi-clock-outline"></i>
                                                        {{ $horaInicio->format('h:i A') }} - {{ $horaFin->format('h:i A') }}
                                                        @if($tiempoInfo)
                                                            <div id="timer-{{ $horario->id }}" class="time-indicator {{ $tiempoInfo['estado'] == 'por_empezar' ? 'upcoming' : ($tiempoInfo['estado'] == 'en_curso' ? 'current' : 'finished') }}">
                                                                <i class="mdi mdi-{{ $tiempoInfo['estado'] == 'por_empezar' ? 'clock-fast' : ($tiempoInfo['estado'] == 'en_curso' ? 'clock' : 'clock-check') }}"></i>
                                                                <span class="timer-text">{{ $tiempoInfo['texto'] }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if(isset($item['dentro_horario']) && $item['dentro_horario'] && $progreso > 0)
                                                    <div class="progress-container">
                                                        <div class="progress-bar-container">
                                                            <div id="progress-{{ $horario->id }}" class="progress-bar" style="width: {{ $progreso }}%"></div>
                                                        </div>
                                                        <div class="progress-text">
                                                            <span><i class="mdi mdi-play-circle"></i> Clase en progreso</span>
                                                            <span><strong id="progress-text-{{ $horario->id }}">{{ $progreso }}%</strong> completado</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="session-details">
                                                    <div>
                                                        <i class="mdi mdi-login text-success"></i> 
                                                        <strong>Entrada:</strong> {{ $item['hora_entrada_registrada'] ?? '---' }}
                                                        @if(isset($item['dentro_tolerancia']) && !$item['dentro_tolerancia'])
                                                            <i class="mdi mdi-alert-circle text-warning tooltip-info" data-tooltip="Entrada fuera de tolerancia"></i>
                                                        @endif
                                                    </div>
                                                    <div><i class="mdi mdi-logout text-danger"></i> <strong>Salida:</strong> {{ $item['hora_salida_registrada'] ?? '---' }}</div>
                                                    <div><i class="mdi mdi-map-marker-outline text-info"></i> <strong>Aula:</strong> {{ $horario->aula->nombre ?? 'N/A' }}</div>
                                                    @if(isset($item['minutos_tardanza']) && $item['minutos_tardanza'] > 0)
                                                        <div class="text-danger"><i class="mdi mdi-timer-sand-empty"></i> <strong>Tardanza:</strong> {{ round($item['minutos_tardanza']) }} min</div>
                                                    @endif
                                                </div>

                                                @if(isset($item['duracion_programada']) || isset($item['duracion_real']) || $eficienciaClase)
                                                    <div class="session-metrics">
                                                        @if(isset($item['duracion_programada']))
                                                            <div class="session-metric">
                                                                <span class="session-metric-value">{{ round($item['duracion_programada']/60, 1) }}h</span>
                                                                <div class="session-metric-label">Programado</div>
                                                            </div>
                                                        @endif
                                                        @if(isset($item['duracion_real']))
                                                            <div class="session-metric">
                                                                <span class="session-metric-value">{{ round($item['duracion_real']/60, 1) }}h</span>
                                                                <div class="session-metric-label">Real</div>
                                                            </div>
                                                        @endif
                                                        @if($eficienciaClase)
                                                            <div class="session-metric">
                                                                <span class="session-metric-value efficiency-indicator {{ $eficienciaClase >= 95 ? 'excellent' : ($eficienciaClase >= 85 ? 'good' : ($eficienciaClase >= 70 ? 'average' : 'poor')) }}">
                                                                    <i class="mdi mdi-{{ $eficienciaClase >= 95 ? 'check-circle' : ($eficienciaClase >= 85 ? 'check' : ($eficienciaClase >= 70 ? 'minus-circle' : 'close-circle')) }}"></i>
                                                                    {{ $eficienciaClase }}%
                                                                </span>
                                                                <div class="session-metric-label">Eficiencia</div>
                                                            </div>
                                                        @endif
                                                        {{-- NUEVO: Tarifa, Pago y Ciclo por sesión --}}
                                                        @if(isset($item['tarifa_sesion']))
                                                            <div class="session-metric">
                                                                <span class="session-metric-value text-primary">
                                                                    <i class="mdi mdi-cash"></i>
                                                                    S/. {{ number_format($item['tarifa_sesion'], 2) }}
                                                                </span>
                                                                <div class="session-metric-label">Tarifa/Hora</div>
                                                            </div>
                                                        @endif
                                                        @if(isset($item['pago_sesion']) && $item['pago_sesion'] > 0)
                                                            <div class="session-metric">
                                                                <span class="session-metric-value text-success">
                                                                    <i class="mdi mdi-cash-multiple"></i>
                                                                    S/. {{ number_format($item['pago_sesion'], 2) }}
                                                                </span>
                                                                <div class="session-metric-label">Pago Sesión</div>
                                                            </div>
                                                        @endif
                                                        @if(isset($item['ciclo_nombre']) && $item['ciclo_nombre'] != 'Sin ciclo')
                                                            <div class="session-metric">
                                                                <span class="session-metric-value text-info" style="font-size: 0.75rem;">
                                                                    <i class="mdi mdi-school"></i>
                                                                    {{ Str::limit($item['ciclo_nombre'], 15) }}
                                                                </span>
                                                                <div class="session-metric-label">Ciclo</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                                @if($asistencia && $asistencia->tema_desarrollado)
                                                    <div class="tema-registrado">
                                                        <p class="mb-0">
                                                            <strong class="text-primary"><i class="mdi mdi-notebook-check-outline"></i> Tema:</strong>
                                                            <span id="display-tema-{{ $horario->id }}">{{ Str::limit($asistencia->tema_desarrollado, 100) }}</span>
                                                        </p>
                                                    </div>
                                                @else
                                                    <div class="tema-registrado text-muted fst-italic">
                                                        <p class="mb-0">
                                                            <strong class="text-primary"><i class="mdi mdi-notebook-check-outline"></i> Tema:</strong>
                                                            <span id="display-tema-{{ $horario->id }}">No registrado.</span>
                                                        </p>
                                                    </div>
                                                @endif

                                                <div class="session-footer">
                                                    <div class="status-badge {{ $estadoConfig['color'] }}">
                                                        <i class="mdi {{ $estadoConfig['icono'] }}"></i>
                                                        {{ $estadoConfig['texto'] }}
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        @if($item['puede_registrar_tema'] || ($asistencia && $asistencia->tema_desarrollado))
                                                            <button id="btn-tema-{{ $horario->id }}" class="action-button btn-sm" 
                                                                    onclick='abrirModalTema({{ $horario->id }}, @json($asistencia ? $asistencia->tema_desarrollado : "", JSON_HEX_APOS), {{ $asistencia ? $asistencia->id : "null" }}, @json($horario->curso->nombre ?? "", JSON_HEX_APOS), @json($horaInicio->format("h:i A") . " - " . $horaFin->format("h:i A"), JSON_HEX_APOS))'>
                                                                <i class="mdi mdi-{{ $asistencia && $asistencia->tema_desarrollado ? 'pencil' : 'plus' }}"></i>
                                                                {{ $asistencia && $asistencia->tema_desarrollado ? 'Editar Tema' : 'Registrar Tema' }}
                                                            </button>
                                                        @else
                                                            <button id="btn-tema-{{ $horario->id }}" class="action-button outline btn-sm" disabled title="Solo se puede registrar el tema de clases finalizadas y con registro de entrada/salida.">
                                                                <i class="mdi mdi-lock-outline"></i>
                                                                <span>Registrar Tema</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Vista Cronológica (oculta por defecto si hay cursos agrupados) --}}
                <div id="timeline-view" class="session-timeline" style="{{ isset($sesionesAgrupadasPorCurso) && count($sesionesAgrupadasPorCurso) > 0 ? 'display: none;' : '' }}">
                    @forelse($horariosDelDia as $item)
                        @php
                            $horario = $item['horario'];
                            $asistencia = $item['asistencia'];
                            $horaInicio = \Carbon\Carbon::parse($horario->hora_inicio);
                            $horaFin = \Carbon\Carbon::parse($horario->hora_fin);

                            // Determinar estado de la sesión
                            $estadoConfig = ['clase' => 'programmed', 'texto' => 'PROGRAMADA', 'color' => 'info', 'icono' => 'mdi-clock-outline'];
                            if ($asistencia) { 
                                $estadoConfig = ['clase' => 'completed', 'texto' => 'COMPLETADA', 'color' => 'success', 'icono' => 'mdi-check-all']; 
                            } elseif ($item['dentro_horario']) { 
                                $estadoConfig = ['clase' => 'active', 'texto' => 'EN CURSO', 'color' => 'active', 'icono' => 'mdi-play-circle']; 
                            } elseif ($item['clase_terminada'] && $item['tiene_registros']) { 
                                $estadoConfig = ['clase' => 'pending', 'texto' => 'PENDIENTE', 'color' => 'warning', 'icono' => 'mdi-alert-circle-check-outline']; 
                            } elseif ($item['clase_terminada'] && !$item['tiene_registros']) { 
                                $estadoConfig = ['clase' => 'no-access', 'texto' => 'SIN REGISTRO', 'color' => 'danger', 'icono' => 'mdi-close-circle-outline']; 
                            }

                            // Información de tiempo
                            $tiempoInfo = $item['tiempo_info'] ?? null;
                            $progreso = $item['progreso_clase'] ?? 0;
                            $eficienciaClase = $item['eficiencia'] ?? null;
                        @endphp

                        <div class="session-card {{ $estadoConfig['clase'] }}" id="session-{{ $horario->id }}">
                            <div class="session-card-content">
                                <div class="session-header">
                                    <h6 class="course-name">{{ $horario->curso->nombre ?? 'Sin curso' }}</h6>
                                    <div class="session-time">
                                        <i class="mdi mdi-clock-outline"></i>
                                        {{ $horaInicio->format('h:i A') }} - {{ $horaFin->format('h:i A') }}
                                        @if($tiempoInfo)
                                            <div class="time-pill {{ $tiempoInfo['estado'] == 'por_empezar' ? 'upcoming' : ($tiempoInfo['estado'] == 'en_curso' ? 'current' : 'finished') }}">
                                                <i class="mdi mdi-{{ $tiempoInfo['estado'] == 'por_empezar' ? 'clock-fast' : ($tiempoInfo['estado'] == 'en_curso' ? 'clock' : 'clock-check') }}"></i>
                                                {{ $tiempoInfo['texto'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Barra de progreso dinámico para clases en vivo -->
                                @if(isset($item['dentro_horario']) && $item['dentro_horario'] && $progreso > 0)
                                    <div class="progress-container">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar" style="width: {{ $progreso }}%"></div>
                                        </div>
                                        <div class="progress-text">
                                            <span><i class="mdi mdi-play-circle mdi-spin"></i> CLASE EN VIVO</span>
                                            <span><strong>{{ $progreso }}%</strong> del tiempo transcurrido</span>
                                        </div>
                                    </div>
                                @endif

                                <div class="session-details">
                                    <div>
                                        <i class="mdi mdi-login text-success"></i> 
                                        <strong>Entrada:</strong> {{ $item['hora_entrada_registrada'] ?? '---' }}
                                        @if(isset($item['dentro_tolerancia']) && !$item['dentro_tolerancia'])
                                            <i class="mdi mdi-alert-circle text-warning tooltip-info" data-tooltip="Entrada fuera de tolerancia"></i>
                                        @endif
                                    </div>
                                    <div><i class="mdi mdi-logout text-danger"></i> <strong>Salida:</strong> {{ $item['hora_salida_registrada'] ?? '---' }}</div>
                                    <div><i class="mdi mdi-map-marker-outline text-info"></i> <strong>Aula:</strong> {{ $horario->aula->nombre ?? 'N/A' }}</div>
                                    @if(isset($item['minutos_tardanza']) && $item['minutos_tardanza'] > 0)
                                        <div class="text-danger"><i class="mdi mdi-timer-sand-empty"></i> <strong>Tardanza:</strong> {{ round($item['minutos_tardanza']) }} min</div>
                                    @endif
                                </div>

                                <!-- NUEVO: Métricas avanzadas de la sesión -->
                                @if(isset($item['duracion_programada']) || isset($item['duracion_real']) || $eficienciaClase)
                                    <div class="session-metrics">
                                        @if(isset($item['duracion_programada']))
                                            <div class="session-metric">
                                                <span class="session-metric-value">{{ round($item['duracion_programada']/60, 1) }}h</span>
                                                <div class="session-metric-label">Programado</div>
                                            </div>
                                        @endif
                                        @if(isset($item['duracion_real']))
                                            <div class="session-metric">
                                                <span class="session-metric-value">{{ round($item['duracion_real']/60, 1) }}h</span>
                                                <div class="session-metric-label">Real</div>
                                            </div>
                                        @endif
                                        @if($eficienciaClase)
                                            <div class="session-metric">
                                                <span class="session-metric-value efficiency-indicator {{ $eficienciaClase >= 95 ? 'excellent' : ($eficienciaClase >= 85 ? 'good' : ($eficienciaClase >= 70 ? 'average' : 'poor')) }}">
                                                    <i class="mdi mdi-{{ $eficienciaClase >= 95 ? 'check-circle' : ($eficienciaClase >= 85 ? 'check' : ($eficienciaClase >= 70 ? 'minus-circle' : 'close-circle')) }}"></i>
                                                    {{ $eficienciaClase }}%
                                                </span>
                                                <div class="session-metric-label">Eficiencia</div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                
                                @if($asistencia && $asistencia->tema_desarrollado)
                                    <div class="tema-registrado">
                                        <p class="mb-0">
                                            <strong class="text-primary"><i class="mdi mdi-notebook-check-outline"></i> Tema:</strong>
                                            <span id="display-tema-{{ $horario->id }}">{{ Str::limit($asistencia->tema_desarrollado, 100) }}</span>
                                        </p>
                                    </div>
                                @else
                                    <div class="tema-registrado text-muted fst-italic">
                                        <p class="mb-0">
                                            <strong class="text-primary"><i class="mdi mdi-notebook-check-outline"></i> Tema:</strong>
                                            <span id="display-tema-{{ $horario->id }}">No registrado.</span>
                                        </p>
                                    </div>
                                @endif

                                <div class="session-footer">
                                    <div class="status-badge {{ $estadoConfig['color'] }}">
                                        <i class="mdi {{ $estadoConfig['icono'] }}"></i>
                                        {{ $estadoConfig['texto'] }}
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if($item['puede_registrar_tema'] || ($asistencia && $asistencia->tema_desarrollado))
                                            <button class="action-button btn-sm" 
                                                    onclick='abrirModalTema({{ $horario->id }}, @json($asistencia ? $asistencia->tema_desarrollado : "", JSON_HEX_APOS), {{ $asistencia ? $asistencia->id : "null" }}, @json($horario->curso->nombre ?? "", JSON_HEX_APOS), @json($horaInicio->format("h:i A") . " - " . $horaFin->format("h:i A"), JSON_HEX_APOS))'>
                                                <i class="mdi mdi-{{ $asistencia && $asistencia->tema_desarrollado ? 'pencil' : 'plus' }}"></i>
                                                {{ $asistencia && $asistencia->tema_desarrollado ? 'Editar Tema' : 'Registrar Tema' }}
                                            </button>
                                        @else
                                            <button class="action-button outline btn-sm" disabled title="Solo se puede registrar el tema de clases finalizadas y con registro de entrada/salida.">
                                                <i class="mdi mdi-lock-outline"></i>
                                                <span>Registrar Tema</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state-card mt-4 p-5 reveal-item">
                            <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center mb-4" style="width: 100px; height: 100px; border: 2px solid #f1f5f9;">
                                <i class="mdi mdi-calendar-blank-outline text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-2">Agenda Disponible</h3>
                            <p class="text-muted mb-0" style="max-width: 350px;">
                                No se detectan sesiones de enseñanza para la fecha seleccionada. 
                                Relájate o revisa tus reportes semanales.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Derecho Mejorado -->
        <div class="col-lg-4">
            @if($proximaClase)
                <div class="sidebar-card reveal-item reveal-delay-1">
                    <h6 class="sidebar-card-title"><i class="mdi mdi-skip-next-circle-outline"></i> Próxima Clase</h6>
                    <div class="next-class-info mt-3">
                        <div class="p-3 rounded-4 shadow-sm mb-3" style="background: var(--primary-light); border: 1px solid var(--primary-color); position: relative; overflow: hidden;">
                            <div class="position-absolute opacity-10" style="top: -10px; right: -10px; font-size: 3rem;">
                                <i class="mdi mdi-school"></i>
                            </div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $proximaClase->curso->nombre ?? 'Sin curso' }}</h6>
                            <p class="mb-2 text-muted small"><i class="mdi mdi-map-marker-outline me-1"></i> {{ $proximaClase->aula->nombre ?? 'Sin aula' }}</p>
                            <div class="badge w-100 p-2" style="background-color: var(--navy-color); color: white;">
                                <i class="mdi mdi-calendar-clock me-1"></i>
                                <strong>{{ ucfirst($proximaClase->dia_semana) }}</strong> - <strong>{{ \Carbon\Carbon::parse($proximaClase->hora_inicio)->format('h:i A') }}</strong>
                            </div>
                        </div>
                        
                        @if(isset($proximaClase->fecha_proxima))
                            @php
                                $horaProxima = $proximaClase->fecha_proxima->copy()->setTime(
                                    \Carbon\Carbon::parse($proximaClase->hora_inicio)->hour,
                                    \Carbon\Carbon::parse($proximaClase->hora_inicio)->minute
                                );
                                $ahora = \Carbon\Carbon::now();
                                $tiempoRestante = $ahora->diffInMinutes($horaProxima, false);
                            @endphp
                            
                            @if($tiempoRestante > 0 && $tiempoRestante <= 1440)
                                <div class="countdown-timer mt-3" id="countdown-timer" data-target-time="{{ $horaProxima->toISOString() }}"
                                     style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--navy-color) 100%);">
                                    <div class="countdown-time fw-bold" id="countdown-display">Calculando...</div>
                                    <div class="countdown-label x-small">para tu próxima sesión</div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            @if(count($recordatorios) > 0)
                <div class="sidebar-card reveal-item reveal-delay-2">
                    <h6 class="sidebar-card-title"><i class="mdi mdi-bell-ring-outline"></i> Notificaciones Críticas</h6>
                    <div class="d-flex flex-column gap-2">
                        @foreach($recordatorios as $recordatorio)
                            <div class="p-2 rounded-3 border-start border-4 border-{{ $recordatorio['tipo'] == 'danger' ? 'danger' : ($recordatorio['tipo'] == 'warning' ? 'warning' : 'primary') }}" 
                                 style="background: #f8f9fa; font-size: 0.8rem;">
                                <i class="mdi mdi-information-outline me-1 text-{{ $recordatorio['tipo'] }}"></i>
                                {{ $recordatorio['mensaje'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="sidebar-card reveal-item reveal-delay-3">
                <h6 class="sidebar-card-title"><i class="mdi mdi-chart-box-outline"></i> Logros de la Semana</h6>
                <div class="stat-grid mt-3">
                    <div class="stat-box" style="color: var(--primary-color)">
                        <div class="h5 m-0">{{ $resumenSemanal['sesiones'] }}</div>
                        <small>SESIONES</small>
                    </div>
                    <div class="stat-box" style="color: var(--success-color)">
                        <div class="h5 m-0">{{ $resumenSemanal['horas'] }}</div>
                        <small>HORAS</small>
                    </div>
                    <div class="stat-box" style="color: var(--info-color)">
                        <div class="h5 m-0" style="font-size: 1rem;">S/.{{ number_format($resumenSemanal['ingresos'], 0) }}</div>
                        <small>PAGO EST.</small>
                    </div>
                    <div class="stat-box" style="color: var(--warning-color)">
                        <div class="h5 m-0">{{ $resumenSemanal['asistencia'] }}%</div>
                        <small>DISCIPLINA</small>
                    </div>
                </div>
                
                @if(isset($resumenSemanal['tendencia']))
                    <div class="mt-3 text-center">
                        <div class="d-inline-flex align-items-center gap-2 py-1 px-3 rounded-pill" 
                             style="background: #ecfdf5; border: 1px solid #10b981;">
                            <i class="mdi mdi-trending-up text-success"></i>
                            <span class="text-success fw-bold" style="font-size: 0.6rem">TENDENCIA: {{ $resumenSemanal['tendencia'] }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- NUEVO: Métricas de rendimiento con Anillos SVG -->
            @if(isset($eficiencia) || isset($puntualidad))
                <div class="sidebar-card reveal-item reveal-delay-4">
                    <h6 class="sidebar-card-title"><i class="mdi mdi-speedometer"></i> Rendimiento General</h6>
                    <div class="performance-grid">
                        @if(isset($eficiencia))
                            <div class="performance-item">
                                <div class="performance-ring">
                                    <svg>
                                        <circle class="ring-bg" cx="38" cy="38" r="35"></circle>
                                        <circle class="ring-progress" cx="38" cy="38" r="35" 
                                                style="stroke: var(--primary-color);"
                                                data-offset="{{ 219.9 * (1 - $eficiencia/100) }}">
                                        </circle>
                                    </svg>
                                    <div class="performance-value">{{ $eficiencia }}%</div>
                                </div>
                                <div class="performance-label">Eficiencia</div>
                            </div>
                        @endif
                        
                        @if(isset($puntualidad))
                            <div class="performance-item">
                                <div class="performance-ring">
                                    <svg>
                                        <circle class="ring-bg" cx="38" cy="38" r="35"></circle>
                                        <circle class="ring-progress" cx="38" cy="38" r="35" 
                                                style="stroke: var(--success-color);"
                                                data-offset="{{ 219.9 * (1 - $puntualidad/100) }}">
                                        </circle>
                                    </svg>
                                    <div class="performance-value">{{ $puntualidad }}%</div>
                                </div>
                                <div class="performance-label">Puntualidad</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal para Registrar/Editar Tema Desarrollado --}}
<div class="modal fade" id="modalTemaDesarrollado" tabindex="-1" aria-labelledby="modalTemaDesarrolladoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
                <div class="modal-header-content">
                    <div class="modal-icon">
                        <i class="mdi mdi-notebook-edit-outline"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="modalTemaDesarrolladoLabel">Registrar Tema Desarrollado</h5>
                        <small class="text-muted" id="modal-subtitle">Documenta el contenido de tu sesión</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formTemaDesarrollado">
                @csrf
                <div class="modal-body modern-modal-body">
                    <input type="hidden" id="horario_id" name="horario_id">
                    <input type="hidden" id="asistencia_id_para_editar" name="asistencia_id">
                    <input type="hidden" id="fecha_seleccionada_input_oculto" name="fecha_seleccionada" value="{{ $fechaSeleccionada->format('Y-m-d') }}">
                    
                    <div id="alertContainer" class="mb-3"></div>
                    
                    {{-- Información de la sesión --}}
                    <div class="session-info-card mb-4">
                        <div class="session-info-item">
                            <i class="mdi mdi-book-open-variant text-primary"></i>
                            <span id="modal-curso-nombre">-</span>
                        </div>
                        <div class="session-info-item">
                            <i class="mdi mdi-clock-outline text-info"></i>
                            <span id="modal-horario">-</span>
                        </div>
                        <div class="session-info-item">
                            <i class="mdi mdi-calendar text-success"></i>
                            <span id="modal-fecha">{{ $fechaSeleccionada->locale('es')->isoFormat('D [de] MMMM') }}</span>
                        </div>
                    </div>
                    
                    {{-- Campo de tema con Quill Editor --}}
                    <div class="mb-3">
                        <label class="form-label modern-label">
                            <i class="mdi mdi-text-box-outline me-2"></i>
                            Tema y Actividades Realizadas *
                        </label>
                        <div class="editor-wrapper" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white;">
                            <div id="editor-container" style="height: 250px; font-size: 14px; border: none;"></div>
                            <input type="hidden" id="tema_desarrollado" name="tema_desarrollado">
                            <div class="textarea-footer p-2 border-top bg-light d-flex justify-content-between align-items-center">
                                <div class="char-info x-small text-muted">
                                    <i class="mdi mdi-information-outline"></i>
                                    <span>Presiona Ctrl+B para negrita</span>
                                </div>
                                <div class="char-counter x-small fw-bold">
                                    <span id="contador">0</span>/1000
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Tips rápidos --}}
                    <div class="tips-card">
                        <div class="tips-header">
                            <i class="mdi mdi-lightbulb-on-outline"></i>
                            <span>Tips para un buen registro</span>
                        </div>
                        <ul class="tips-list">
                            <li>Menciona los temas principales cubiertos</li>
                            <li>Incluye ejemplos o ejercicios realizados</li>
                            <li>Anota tareas o actividades asignadas</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer modern-modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-save-theme" id="btnGuardarTema">
                        <i class="mdi mdi-content-save me-1"></i>
                        <span>Guardar Tema</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (isset($anuncios) && $anuncios->count() > 0)
<!-- Modal de Anuncios -->
<div class="modal fade" id="anunciosModal" tabindex="-1" aria-labelledby="anunciosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(10px); border-radius: 20px; overflow: hidden; border: none;">
            <div class="modal-body p-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 1rem; right: 1rem; z-index: 1056;"></button>
                <div id="carouselAnuncios" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ($anuncios as $key => $anuncio)
                            <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                @if ($anuncio->imagen)
                                    <img src="{{ asset('storage/' . $anuncio->imagen) }}" class="d-block w-100" alt="{{ $anuncio->titulo }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if ($anuncios->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselAnuncios" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselAnuncios" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<!-- Quill.js JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    // Inicializar Quill Editor Globalmente
    var quill;
    document.addEventListener('DOMContentLoaded', function() {
        quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Describe los temas tratados, ejercicios realizados, etc...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Contador de caracteres para Quill
        quill.on('text-change', function() {
            actualizarContador();
        });

        function actualizarContador() {
            const contador = document.getElementById('contador');
            if (!contador) return;
            const actual = quill.getText().trim().length;
            const max = 1000;
            contador.textContent = `${actual}/${max}`;
            if (actual < 5) contador.style.color = 'var(--danger-text)';
            else if (actual > max * 0.9) contador.style.color = 'var(--warning-text)';
            else contador.style.color = 'var(--success-text)';
        }
        // Reloj en tiempo real
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            setInterval(() => {
                const now = new Date();
                timeElement.textContent = now.toLocaleTimeString('es-PE', { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit', 
                    hour12: true 
                });
            }, 1000);
        }

        // NUEVO: Inicialización de Flatpickr
        const diasConClases = @json($diasConClases ?? []);
        flatpickr("#fecha-agenda", {
            locale: "es",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            defaultDate: "{{ $fechaSeleccionada->format('Y-m-d') }}",
            onDayCreate: function(dObj, dStr, fp, dayElem){
                const dateStr = fp.formatDate(dayElem.dateObj, "Y-m-d");
                if (diasConClases.includes(dateStr)) {
                    dayElem.classList.add("has-clases");
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                // Enviar el formulario automáticamente al cambiar la fecha
                document.getElementById('form-agenda').submit();
            }
        });

        // NUEVO: Countdown timer para próxima clase
        const countdownTimer = document.getElementById('countdown-timer');
        if (countdownTimer) {
            const targetTime = new Date(countdownTimer.dataset.targetTime);
            
            function updateCountdown() {
                const now = new Date();
                const diff = targetTime - now;
                
                if (diff > 0) {
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    
                    let displayText = '';
                    if (hours > 0) {
                        displayText = `${hours}h ${minutes}m ${seconds}s`;
                    } else if (minutes > 0) {
                        displayText = `${minutes}m ${seconds}s`;
                    } else {
                        displayText = `${seconds}s`;
                    }
                    
                    document.getElementById('countdown-display').textContent = displayText;
                } else {
                    document.getElementById('countdown-display').textContent = '¡Es hora de clase!';
                    countdownTimer.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)';
                }
            }
            
            updateCountdown();
            setInterval(updateCountdown, 1000);
        }

        // NUEVO: Auto-refresh para clases en curso
        const activeSession = document.querySelector('.session-card.active');
        if (activeSession) {
            setInterval(() => {
                // Recargar página cada 5 minutos si hay una clase activa
                location.reload();
            }, 300000); // 5 minutos
        }

        // Formulario de tema desarrollado
        const form = document.getElementById('formTemaDesarrollado');
        if (form) form.addEventListener('submit', handleFormSubmit);

        const textarea = document.getElementById('tema_desarrollado');
        if (textarea) {
            textarea.addEventListener('input', actualizarContador);
            actualizarContador();
        }

        // NUEVO: Tooltips avanzados
        const tooltips = document.querySelectorAll('.tooltip-info');
        tooltips.forEach(tooltip => {
            tooltip.addEventListener('mouseenter', function() {
                // Agregar funcionalidad de tooltip adicional si es necesario
            });
        });

        // NUEVO: Toggle entre vistas (Por Curso / Cronológica)
        const toggleButtons = document.querySelectorAll('.toggle-btn');
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const view = this.dataset.view;
                
                // Actualizar botones activos
                toggleButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Mostrar/ocultar vistas
                const courseView = document.getElementById('course-view');
                const timelineView = document.getElementById('timeline-view');
                
                if (view === 'course' && courseView) {
                    courseView.style.display = 'flex';
                    if (timelineView) timelineView.style.display = 'none';
                } else if (view === 'timeline' && timelineView) {
                    if (courseView) courseView.style.display = 'none';
                    timelineView.style.display = 'block';
                }
            });
        });

        // NUEVO: Scroll a curso al hacer click en tarjeta de resumen
        const courseSummaryCards = document.querySelectorAll('.course-summary-card');
        courseSummaryCards.forEach(card => {
            card.addEventListener('click', function() {
                const cursoId = this.dataset.cursoId;
                const cursoGroup = document.getElementById('course-' + cursoId);
                
                if (cursoGroup) {
                    // Cambiar a vista por curso si no está activa
                    const courseViewBtn = document.querySelector('[data-view="course"]');
                    if (courseViewBtn && !courseViewBtn.classList.contains('active')) {
                        courseViewBtn.click();
                    }
                    
                    // Scroll suave al grupo del curso
                    setTimeout(() => {
                        cursoGroup.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        
                        // Efecto de highlight
                        cursoGroup.classList.add('highlight');
                        setTimeout(() => {
                            cursoGroup.classList.remove('highlight');
                        }, 1000);
                    }, 100);
                }
            });
        });

        // NUEVO: Animación de anillos de rendimiento
        setTimeout(() => {
            const rings = document.querySelectorAll('.ring-progress');
            rings.forEach(ring => {
                const targetOffset = ring.dataset.offset;
                if (targetOffset) {
                    ring.style.strokeDashoffset = targetOffset;
                }
            });
        }, 800);
    });


    function abrirModalTema(horarioId, temaExistente = '', asistenciaId = null, cursoNombre = '', horario = '') {
        const modalElement = document.getElementById('modalTemaDesarrollado');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        document.getElementById('horario_id').value = horarioId;
        document.getElementById('asistencia_id_para_editar').value = asistenciaId;

        // Cargar contenido en Quill
        if (temaExistente && temaExistente !== 'null' && temaExistente !== 'No registrado.') {
            quill.root.innerHTML = temaExistente;
        } else {
            quill.setContents([]);
        }

        const fechaAgendaInput = document.getElementById('fecha-agenda');
        if (fechaAgendaInput) {
            document.getElementById('fecha_seleccionada_input_oculto').value = fechaAgendaInput.value;
        }

        // NUEVO: Actualizar información de la sesión en el modal
        const sessionCard = modalElement.querySelector('.session-info-card');
        if (sessionCard) {
            const cursoSpan = document.getElementById('modal-curso-nombre');
            const horarioSpan = document.getElementById('modal-horario');
            
            if (cursoSpan && cursoNombre) cursoSpan.textContent = cursoNombre;
            if (horarioSpan && horario) horarioSpan.textContent = horario;
        }

        const titulo = document.getElementById('modalTemaDesarrolladoLabel');
        const subtitle = document.getElementById('modal-subtitle');
        const btnGuardarSpan = document.getElementById('btnGuardarTema').querySelector('span');
        
        if (temaExistente && temaExistente !== 'null') {
            titulo.textContent = 'Editar Tema Desarrollado';
            if (subtitle) subtitle.textContent = 'Actualiza el contenido de tu sesión';
            if (btnGuardarSpan) btnGuardarSpan.textContent = 'Actualizar Tema';
        } else {
            titulo.textContent = 'Registrar Tema Desarrollado';
            if (subtitle) subtitle.textContent = 'Documenta el contenido de tu sesión';
            if (btnGuardarSpan) btnGuardarSpan.textContent = 'Guardar Tema';
        }
        
        document.getElementById('alertContainer').innerHTML = '';
        actualizarContador();
        modal.show();
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        const btnGuardar = document.getElementById('btnGuardarTema');
        const btnText = btnGuardar.querySelector('span');
        const originalText = btnText.textContent;
        
        // Sincronizar Quill con el input oculto
        const temaHtml = quill.root.innerHTML;
        const temaText = quill.getText().trim();
        document.getElementById('tema_desarrollado').value = temaHtml;

        if (temaText.length < 5) {
            mostrarAlertEnModal('danger', 'El tema debe tener al menos 5 caracteres.');
            return;
        }
        
        btnGuardar.disabled = true;
        btnText.textContent = 'Guardando...';
        btnGuardar.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
        document.getElementById('alertContainer').innerHTML = '';
        
        const formData = new FormData(e.target);
        const fechaSeleccionada = document.getElementById('fecha_seleccionada_input_oculto').value;
        formData.append('fecha_seleccionada', fechaSeleccionada);

        const asistenciaId = document.getElementById('asistencia_id_para_editar').value;
        let targetUrl = '{{ route("docente.tema-guardar") }}';

        if (asistenciaId && asistenciaId !== 'null') {
            targetUrl = '{{ route("asistencia-docente.actualizar-tema") }}';
        }

        fetch(targetUrl, {
            method: 'POST',
            body: formData,
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
                'Accept': 'application/json' 
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                mostrarAlertEnModal('success', data.message);

                const horarioId = document.getElementById('horario_id').value;
                const displayTema = document.getElementById(`display-tema-${horarioId}`);
                if (displayTema && data.tema_desarrollado) {
                    displayTema.textContent = data.tema_desarrollado.substring(0, 100) + (data.tema_desarrollado.length > 100 ? '...' : '');
                }

                const modal = bootstrap.Modal.getInstance(document.getElementById('modalTemaDesarrollado'));
                modal.hide();

                // NUEVO: Mostrar SweetAlert de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Tema Guardado!',
                    text: data.message || 'El tema se ha registrado correctamente.',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end'
                }).then(() => {
                    location.reload();
                });
            } else {
                let mensaje = data.message || 'Error al guardar el tema.';
                if (data.errors) {
                    mensaje += '<ul class="mt-2 mb-0 ps-3">';
                    Object.values(data.errors).flat().forEach(error => mensaje += `<li>${error}</li>`);
                    mensaje += '</ul>';
                }
                mostrarAlertEnModal('danger', mensaje);
                resetButton();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Error de conexión. Por favor, inténtalo de nuevo.';
            if (error.message) {
                errorMessage = error.message;
            }
            mostrarAlertEnModal('danger', errorMessage);
            resetButton();
        });

        function resetButton() {
            btnGuardar.disabled = false;
            btnText.textContent = originalText;
            const spinner = btnGuardar.querySelector('.spinner-border');
            if(spinner) spinner.remove();
        }
    }

    function mostrarAlertEnModal(tipo, mensaje) {
        const alertContainer = document.getElementById('alertContainer');
        const alertClass = `alert-dismissible alert alert-${tipo} d-flex align-items-center`;
        const icon = tipo === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';
        alertContainer.innerHTML = `<div class="${alertClass}" role="alert"><i class="mdi ${icon} me-2 fs-5"></i><div>${mensaje}</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
        document.querySelector('.modal-body').scrollTop = 0;
    }

    // --- LÓGICA DE SINCRONIZACIÓN EN TIEMPO REAL (PROFESIONAL) ---
    function sincronizarDashboard() {
        const urlPoll = "{{ route('teacher.dashboard.poll') }}";
        const fechaActual = "{{ $fechaSeleccionada->format('Y-m-d') }}";

        fetch(`${urlPoll}?fecha=${fechaActual}`)
            .then(response => response.json())
            .then(data => {
                if (data.horarios) {
                    data.horarios.forEach(item => {
                        actualizarElementoSesion(item);
                    });
                }
            })
            .catch(error => console.error('Error sincronizando dashboard:', error));
    }

    function actualizarElementoSesion(item) {
        const sessionCard = document.getElementById(`session-${item.horario_id}`);
        const timerElement = document.getElementById(`timer-${item.horario_id}`);
        const progressElement = document.getElementById(`progress-${item.horario_id}`);
        const progressTextElement = document.getElementById(`progress-text-${item.horario_id}`);
        const btnTema = document.getElementById(`btn-tema-${item.horario_id}`);

        // --- Actualizar Tiempos e Iconos ---
        if (timerElement) {
            const timerText = timerElement.querySelector('.timer-text');
            const timerIcon = timerElement.querySelector('i');
            
            if (timerText) timerText.textContent = item.tiempo_info.texto;
            
            const nuevoEstado = item.tiempo_info.estado;
            const claseEstado = nuevoEstado === 'por_empezar' ? 'upcoming' : (nuevoEstado === 'en_curso' ? 'current' : 'finished');
            
            if (!timerElement.classList.contains(claseEstado)) {
                // Si el estado cambia de verdad, recargar para asegurar consistencia (opcional)
                if (!document.querySelector('.modal.show')) {
                    if (timerElement.classList.contains('upcoming') && nuevoEstado === 'en_curso') {
                         setTimeout(() => location.reload(), 500);
                    }
                }
                timerElement.className = `time-indicator ${claseEstado}`;
                if (timerIcon) {
                    const iconClass = nuevoEstado === 'por_empezar' ? 'mdi-clock-fast' : (nuevoEstado === 'en_curso' ? 'mdi-clock' : 'mdi-clock-check');
                    timerIcon.className = `mdi ${iconClass}`;
                }
            }
        }

        // --- Actualizar Registros (Entrada/Salida) en tiempo real ---
        if (sessionCard) {
            // Actualizar entrada
            const entradaStrong = sessionCard.querySelector('.mdi-login + strong');
            if (entradaStrong && entradaStrong.nextSibling) {
                entradaStrong.nextSibling.textContent = ' ' + (item.hora_entrada || '---');
            }
            // Actualizar salida
            const salidaStrong = sessionCard.querySelector('.mdi-logout + strong');
            if (salidaStrong && salidaStrong.nextSibling) {
                salidaStrong.nextSibling.textContent = ' ' + (item.hora_salida || '---');
            }
            
            // Actualizar Métrica "REAL"
            const metrics = sessionCard.querySelectorAll('.session-metric');
            metrics.forEach(metric => {
                const label = metric.querySelector('.session-metric-label');
                if (label && label.textContent.trim() === 'Real') {
                    const value = metric.querySelector('.session-metric-value');
                    if (value) value.textContent = item.duracion_real + 'h';
                }
            });

            // Actualizar Badge de Estado
            const badge = sessionCard.querySelector('.status-badge');
            if (badge && item.estado_ui) {
                badge.className = `status-badge ${item.estado_ui.color}`;
                badge.innerHTML = `<i class="mdi ${item.estado_ui.icono}"></i> ${item.estado_ui.texto}`;
            }
        }

        // --- Barra de Progreso ---
        if (progressElement && item.progreso_clase > 0) {
            progressElement.style.width = `${item.progreso_clase}%`;
            if (progressTextElement) progressTextElement.textContent = `${item.progreso_clase}%`;
        }

        // --- Botón de Registro de Tema ---
        if (btnTema) {
            if (item.puede_registrar_tema) {
                if (btnTema.disabled) {
                    btnTema.disabled = false;
                    btnTema.classList.remove('outline');
                    btnTema.title = "";
                    const icon = btnTema.querySelector('i');
                    if (icon) icon.className = `mdi mdi-${item.tema_desarrollado ? 'pencil' : 'plus'}`;
                    const btnText = btnTema.querySelector('span') || btnTema;
                    if (btnText === btnTema) {
                        // fallback si no hay span
                    } else {
                        btnText.textContent = item.tema_desarrollado ? ' Editar Tema' : ' Registrar Tema';
                    }
                }
            } else {
                if (!btnTema.disabled) {
                    btnTema.disabled = true;
                    btnTema.classList.add('outline');
                    btnTema.title = "Solo se puede registrar el tema de clases con registro de entrada/salida.";
                    const icon = btnTema.querySelector('i');
                    if (icon) icon.className = 'mdi mdi-lock-outline';
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Ejecutar sincronización cada 6 segundos (Más rápido)
        setInterval(sincronizarDashboard, 6000);
        
        // Sincronizar inmediatamente al cargar
        sincronizarDashboard();

        @if (isset($anuncios) && $anuncios->count() > 0)
            var anunciosModal = new bootstrap.Modal(document.getElementById('anunciosModal'));
            anunciosModal.show();
        @endif
    });
</script>
@endpush
