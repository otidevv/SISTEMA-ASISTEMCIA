@extends('layouts.app')

@section('title', 'Dashboard Estudiante - CEPRE UNAMAD')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css">
    
    <style>
        /* Paleta de colores institucional basada en el logo CEPRE UNAMAD */
        .dashboard-estudiante-container {
            --cepre-magenta: #e91e63;
            --cepre-cyan: #00bcd4;
            --cepre-green: #8bc34a;
            --cepre-gold: #ffd700;
            --cepre-navy: #1a237e;
            --cepre-dark-blue: #0d47a1;
            --cepre-light-gray: #f8f9fa;
            --cepre-dark-gray: #455a64;
            --cepre-shadow: rgba(26, 35, 126, 0.15);
            
            background: linear-gradient(135deg, #f8f9fa 0%, #eceff1 100%);
            min-height: calc(100vh - 200px);
            padding: 2rem;
            border-radius: 0;
            margin: 0;
        }

        /* Header institucional mejorado */
        .dashboard-estudiante-container .institutional-header {
            background: linear-gradient(135deg, var(--cepre-navy) 0%, var(--cepre-dark-blue) 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px var(--cepre-shadow);
            position: relative;
            overflow: hidden;
        }

        .dashboard-estudiante-container .institutional-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .dashboard-estudiante-container .institutional-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -15%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(139, 195, 74, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* Logo y branding institucional */
        .dashboard-estudiante-container .institutional-brand {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .dashboard-estudiante-container .cepre-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-cyan) 100%);
            color: white;
            border: 3px solid var(--cepre-gold);
        }

        .dashboard-estudiante-container .institutional-info h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 0;
            color: white;
            letter-spacing: -0.5px;
        }

        .dashboard-estudiante-container .institutional-info .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0.5rem 0;
            font-weight: 400;
        }

        .dashboard-estudiante-container .institutional-motto {
            font-style: italic;
            font-size: 0.95rem;
            opacity: 0.8;
            margin-top: 0.5rem;
            border-left: 3px solid var(--cepre-gold);
            padding-left: 1rem;
        }

        /* Breadcrumb institucional */
        .dashboard-estudiante-container .institutional-breadcrumb {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 0.8rem 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: absolute;
            top: 2rem;
            right: 2rem;
            z-index: 3;
        }

        .dashboard-estudiante-container .institutional-breadcrumb .breadcrumb {
            margin: 0;
            background: none;
            padding: 0;
        }

        .dashboard-estudiante-container .institutional-breadcrumb .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
        }

        .dashboard-estudiante-container .institutional-breadcrumb .breadcrumb-item.active {
            color: var(--cepre-gold);
            font-weight: 600;
        }

        /* Cards institucionales mejoradas */
        .dashboard-estudiante-container .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 32px var(--cepre-shadow);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .dashboard-estudiante-container .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px var(--cepre-shadow);
        }

        .dashboard-estudiante-container .card-header {
            background: linear-gradient(135deg, var(--cepre-navy) 0%, var(--cepre-dark-blue) 100%);
            color: white;
            border: none;
            border-radius: 20px 20px 0 0 !important;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .dashboard-estudiante-container .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(139, 195, 74, 0.2);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }

        .dashboard-estudiante-container .card-header h4,
        .dashboard-estudiante-container .card-header h5 {
            position: relative;
            z-index: 1;
            margin: 0;
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: -0.3px;
        }

        /* Header de bienvenida estudiantil */
        .dashboard-estudiante-container .student-welcome {
            background: linear-gradient(135deg, var(--cepre-green) 0%, var(--cepre-cyan) 100%);
            color: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 12px 40px rgba(139, 195, 74, 0.3);
            position: relative;
            overflow: hidden;
        }

        .dashboard-estudiante-container .student-welcome::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .dashboard-estudiante-container .student-welcome h3 {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            letter-spacing: -0.8px;
        }

        .dashboard-estudiante-container .student-welcome p {
            position: relative;
            z-index: 1;
            font-size: 1.2rem;
            font-weight: 500;
            opacity: 0.95;
            line-height: 1.6;
        }

        /* Métricas destacadas */
        .dashboard-estudiante-container .metrics-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 32px var(--cepre-shadow);
            border: 2px solid transparent;
            background-clip: padding-box;
            position: relative;
            overflow: hidden;
        }

        .dashboard-estudiante-container .metrics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--cepre-magenta), var(--cepre-cyan), var(--cepre-green));
        }

        .dashboard-estudiante-container .metric-number {
            font-size: 3.5rem;
            font-weight: 900;
            margin: 1rem 0;
            background: linear-gradient(135deg, var(--cepre-navy), var(--cepre-magenta));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }

        .dashboard-estudiante-container .metric-label {
            font-weight: 600;
            color: var(--cepre-dark-gray);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        /* Cycle info mejorado */
        .dashboard-estudiante-container .cycle-info {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
        }

        /* Formularios institucionales */
        .dashboard-estudiante-container .form-select,
        .dashboard-estudiante-container .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            font-size: 1rem;
            font-weight: 500;
        }

        .dashboard-estudiante-container .form-select:focus,
        .dashboard-estudiante-container .form-control:focus {
            border-color: var(--cepre-cyan);
            box-shadow: 0 0 0 0.25rem rgba(0, 188, 212, 0.15);
            background: white;
            transform: translateY(-2px);
        }

        .dashboard-estudiante-container .form-label {
            font-weight: 700;
            color: var(--cepre-navy);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            letter-spacing: -0.2px;
        }

        /* Botones institucionales */
        .dashboard-estudiante-container .btn-inscribir {
            background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-navy) 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 1.5rem 4rem;
            border-radius: 50px;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(233, 30, 99, 0.3);
        }

        .dashboard-estudiante-container .btn-inscribir:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 16px 48px rgba(233, 30, 99, 0.4);
            color: white;
        }

        .dashboard-estudiante-container .btn-inscribir::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.8s;
        }

        .dashboard-estudiante-container .btn-inscribir:hover::before {
            left: 100%;
        }

        /* Botones secundarios */
        .dashboard-estudiante-container .btn-primary {
            background: linear-gradient(135deg, var(--cepre-cyan) 0%, var(--cepre-navy) 100%);
            border: none;
            border-radius: 12px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .dashboard-estudiante-container .btn-secondary {
            background: linear-gradient(135deg, var(--cepre-dark-gray) 0%, #607d8b 100%);
            border: none;
            border-radius: 12px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .dashboard-estudiante-container .btn-primary:hover,
        .dashboard-estudiante-container .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        /* Estados de asistencia institucionales */
        .dashboard-estudiante-container .border-success {
            border: 3px solid var(--cepre-green) !important;
            background: linear-gradient(135deg, rgba(139, 195, 74, 0.1) 0%, rgba(139, 195, 74, 0.05) 100%);
        }

        .dashboard-estudiante-container .border-warning {
            border: 3px solid #ff9800 !important;
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.1) 0%, rgba(255, 152, 0, 0.05) 100%);
        }

        .dashboard-estudiante-container .border-danger {
            border: 3px solid var(--cepre-magenta) !important;
            background: linear-gradient(135deg, rgba(233, 30, 99, 0.1) 0%, rgba(233, 30, 99, 0.05) 100%);
        }

        .dashboard-estudiante-container .bg-success {
            background: linear-gradient(135deg, var(--cepre-green) 0%, #689f38 100%) !important;
        }

        .dashboard-estudiante-container .bg-warning {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%) !important;
        }

        .dashboard-estudiante-container .bg-danger {
            background: linear-gradient(135deg, var(--cepre-magenta) 0%, #c2185b 100%) !important;
        }

        .dashboard-estudiante-container .bg-secondary {
            background: linear-gradient(135deg, var(--cepre-dark-gray) 0%, #546e7a 100%) !important;
        }

        .dashboard-estudiante-container .bg-info {
            background: linear-gradient(135deg, var(--cepre-cyan) 0%, #0097a7 100%) !important;
        }

        /* Alertas institucionales */
        .dashboard-estudiante-container .alert {
            border: none;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px var(--cepre-shadow);
            font-weight: 500;
            line-height: 1.7;
        }

        .dashboard-estudiante-container .alert-info {
            background: linear-gradient(135deg, rgba(0, 188, 212, 0.1) 0%, rgba(0, 188, 212, 0.05) 100%);
            border-left: 5px solid var(--cepre-cyan);
            color: var(--cepre-navy);
        }

        .dashboard-estudiante-container .alert-success {
            background: linear-gradient(135deg, rgba(139, 195, 74, 0.1) 0%, rgba(139, 195, 74, 0.05) 100%);
            border-left: 5px solid var(--cepre-green);
            color: #2e7d32;
        }

        .dashboard-estudiante-container .alert-warning {
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.1) 0%, rgba(255, 152, 0, 0.05) 100%);
            border-left: 5px solid #ff9800;
            color: #ef6c00;
        }

        .dashboard-estudiante-container .alert-danger {
            background: linear-gradient(135deg, rgba(233, 30, 99, 0.1) 0%, rgba(233, 30, 99, 0.05) 100%);
            border-left: 5px solid var(--cepre-magenta);
            color: var(--cepre-magenta);
        }

        /* Badges institucionales */
        .dashboard-estudiante-container .badge {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.85rem;
            border: 2px solid transparent;
        }

        /* Progress bars mejoradas */
        .dashboard-estudiante-container .progress {
            height: 16px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.3);
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .dashboard-estudiante-container .progress-bar {
            border-radius: 50px;
            transition: all 0.6s ease;
            background: linear-gradient(90deg, var(--cepre-cyan), var(--cepre-green));
        }

        /* Loading spinner institucional */
        .dashboard-estudiante-container .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            gap: 1.5rem;
        }

        .dashboard-estudiante-container .cepre-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(233, 30, 99, 0.1);
            border-left: 4px solid var(--cepre-magenta);
            border-radius: 50%;
            animation: cepreSpinAnimation 1s linear infinite;
        }

        @keyframes cepreSpinAnimation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .dashboard-estudiante-container .loading-text {
            color: var(--cepre-navy);
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Tipografía mejorada */
        .dashboard-estudiante-container h1 {
            font-size: 2.8rem;
            font-weight: 900;
            letter-spacing: -1px;
            line-height: 1.1;
        }

        .dashboard-estudiante-container h2 {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .dashboard-estudiante-container h3 {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            line-height: 1.3;
        }

        .dashboard-estudiante-container h4 {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.2px;
        }

        .dashboard-estudiante-container h5 {
            font-size: 1.3rem;
            font-weight: 600;
            letter-spacing: -0.1px;
        }

        /* Espaciado mejorado entre secciones */
        .dashboard-estudiante-container .section-divider {
            margin: 4rem 0;
            position: relative;
        }

        .dashboard-estudiante-container .section-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 6px;
            background: linear-gradient(90deg, var(--cepre-magenta), var(--cepre-cyan), var(--cepre-green));
            border-radius: 3px;
        }

        /* Iconos institucionales */
        .dashboard-estudiante-container .mdi {
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        /* Responsive institucional */
        @media (max-width: 768px) {
            .dashboard-estudiante-container {
                padding: 1rem;
            }

            .dashboard-estudiante-container .institutional-header {
                padding: 2rem;
                text-align: center;
            }

            .dashboard-estudiante-container .institutional-brand {
                flex-direction: column;
                text-align: center;
            }

            .dashboard-estudiante-container .institutional-breadcrumb {
                position: relative;
                top: auto;
                right: auto;
                margin-top: 1rem;
            }

            .dashboard-estudiante-container .student-welcome h3 {
                font-size: 2rem;
            }

            .dashboard-estudiante-container .btn-inscribir {
                padding: 1.2rem 2.5rem;
                font-size: 1rem;
            }

            .dashboard-estudiante-container .metric-number {
                font-size: 2.5rem;
            }
        }

        /* Animaciones institucionales */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-estudiante-container .card {
            animation: slideInUp 0.6s ease-out;
        }

        /* SweetAlert2 personalizado */
        .swal2-popup {
            border-radius: 20px !important;
            font-family: inherit !important;
        }

        .swal2-title {
            color: var(--cepre-navy) !important;
            font-weight: 700 !important;
        }

        .swal2-confirm {
            background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-navy) 100%) !important;
            border-radius: 12px !important;
            padding: 0.8rem 2rem !important;
            font-weight: 600 !important;
        }

        .swal2-cancel {
            background: linear-gradient(135deg, var(--cepre-dark-gray) 0%, #607d8b 100%) !important;
            border-radius: 12px !important;
            padding: 0.8rem 2rem !important;
            font-weight: 600 !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="dashboard-estudiante-container">
            <div class="institutional-header tw-transition-all tw-duration-300 hover:tw-shadow-2xl">
                <div class="institutional-breadcrumb">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="tw-transition-colors hover:tw-text-white">Portal</a></li>
                        <li class="breadcrumb-item active">Dashboard Estudiantil</li>
                    </ol>
                </div>
                
                <div class="institutional-brand">
                    <div class="cepre-logo tw-transition-transform hover:tw-scale-110 hover:tw-rotate-6">
                        C
                    </div>
                    <div class="institutional-info">
                        <h2 class="tw-tracking-tight">CEPRE UNAMAD</h2>
                        <p class="subtitle">Centro de Estudios Preuniversitarios</p>
                        <p class="institutional-motto">"Forjando el futuro académico de la región"</p>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card student-welcome">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="mb-1 tw-tracking-tighter">¡Bienvenido, {{ Auth::user()->nombre }}!</h3>
                                    <p class="mb-0 tw-max-w-3xl">
                                        @if (Auth::user()->hasRole('postulante'))
                                            Tu camino hacia la universidad comienza aquí. Inscríbete en el ciclo actual para iniciar tu preparación académica de excelencia.
                                        @else
                                            Continúa construyendo tu futuro académico con nosotros. Mantén tu compromiso con la excelencia educativa.
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div id="ciclo-info" class="cycle-info tw-transition-all hover:tw-shadow-lg hover:tw-border-white/50">
                                        <div class="loading-container">
                                            <div class="cepre-spinner"></div>
                                            <div class="loading-text">Cargando ciclo...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notificaciones Institucionales --}}
            @if (isset($notifications) && $notifications->count() > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading mb-3 tw-flex tw-items-center">
                                <i class="mdi mdi-bell-outline me-2"></i>Comunicados Institucionales
                            </h4>
                            <div class="section-divider"></div>
                            @foreach ($notifications as $notification)
                                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded-3 tw-shadow-sm hover:tw-shadow-md tw-transition">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-primary">{{ $notification->data['titulo'] ?? 'Comunicado Importante' }}</h6>
                                        <p class="mb-1">{{ $notification->data['message'] }}</p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <a href="{{ route('notifications.read', $notification->id) }}" class="btn btn-primary btn-sm">
                                        <i class="mdi mdi-check me-1"></i>Marcar como leída
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- SECCIÓN 1: PROCESO DE POSTULACIÓN/INSCRIPCIÓN --}}
            @if (!isset($constanciaSubida) || !$constanciaSubida)
                <div class="row mb-4">
                    <div class="col-12">
                        <div id="contenedor-postulacion" class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0 tw-flex tw-items-center">
                                    <i class="mdi mdi-file-document-outline me-2"></i>Estado de tu Postulación Académica
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="estado-postulacion">
                                    <div class="loading-container">
                                        <div class="cepre-spinner"></div>
                                        <div class="loading-text">Verificando estado de postulación...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div id="contenedor-inscripcion">
                            </div>
                    </div>
                </div>

            @else
                {{-- SECCIÓN 2: INFORMACIÓN ACADÉMICA (ESTUDIANTE INSCRITO) --}}
                
                {{-- Información del Ciclo Actual --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0 tw-flex tw-items-center">
                                    <i class="mdi mdi-school me-2"></i>Información de Inscripción Académica
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-3">
                                        <div class="metrics-card tw-transition-transform hover:tw-scale-105">
                                            <h6 class="metric-label">Ciclo Académico</h6>
                                            <div class="metric-number" style="font-size: 1.8rem;">{{ $inscripcionActiva->ciclo->nombre }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metrics-card tw-transition-transform hover:tw-scale-105">
                                            <h6 class="metric-label">Carrera Profesional</h6>
                                            <div class="metric-number" style="font-size: 1.8rem;">{{ $inscripcionActiva->carrera->nombre }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metrics-card tw-transition-transform hover:tw-scale-105">
                                            <h6 class="metric-label">Turno de Estudios</h6>
                                            <div class="metric-number" style="font-size: 1.8rem;">{{ $inscripcionActiva->turno->nombre }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="metrics-card tw-transition-transform hover:tw-scale-105">
                                            <h6 class="metric-label">Aula Asignada</h6>
                                            <div class="metric-number" style="font-size: 1.8rem;">{{ $inscripcionActiva->aula->codigo }}</div>
                                            <small class="text-muted">{{ $inscripcionActiva->aula->nombre }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (isset($infoAsistencia) && !empty($infoAsistencia))
                    {{-- Resumen General de Asistencia --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0 tw-flex tw-items-center">
                                        <i class="mdi mdi-calendar-check me-2"></i>Control de Asistencia Académica
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if (isset($infoAsistencia['total_ciclo']))
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-3">
                                                <div class="metrics-card">
                                                    <div class="metric-number @if ($infoAsistencia['total_ciclo']['estado'] == 'regular') text-success @elseif($infoAsistencia['total_ciclo']['estado'] == 'amonestado') text-warning @else text-danger @endif">
                                                        {{ $infoAsistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $infoAsistencia['total_ciclo']['porcentaje_asistencia'] }}%
                                                    </div>
                                                    <h6 class="metric-label">Asistencia Total</h6>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="metrics-card">
                                                    <div class="metric-number text-success">{{ $infoAsistencia['total_ciclo']['dias_asistidos'] }}</div>
                                                    <h6 class="metric-label">Días Asistidos</h6>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="metrics-card">
                                                    <div class="metric-number text-danger">{{ $infoAsistencia['total_ciclo']['dias_falta'] }}</div>
                                                    <h6 class="metric-label">Días de Falta</h6>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="metrics-card">
                                                    <div class="metric-number text-info">{{ $infoAsistencia['total_ciclo']['dias_habiles_transcurridos'] ?? $infoAsistencia['total_ciclo']['dias_habiles'] }}</div>
                                                    <h6 class="metric-label">Días Transcurridos</h6>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="progress" style="height: 30px;">
                                            <div class="progress-bar @if ($infoAsistencia['total_ciclo']['estado'] == 'regular') bg-success @elseif($infoAsistencia['total_ciclo']['estado'] == 'amonestado') bg-warning @else bg-danger @endif"
                                                role="progressbar"
                                                style="width: {{ $infoAsistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $infoAsistencia['total_ciclo']['porcentaje_asistencia'] }}%;"
                                                aria-valuenow="{{ $infoAsistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $infoAsistencia['total_ciclo']['porcentaje_asistencia'] }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <span class="fw-bold fs-6">{{ $infoAsistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $infoAsistencia['total_ciclo']['porcentaje_asistencia'] }}%</span>
                                            </div>
                                        </div>
                                        
                                        @if (isset($infoAsistencia['total_ciclo']['es_proyeccion']) && $infoAsistencia['total_ciclo']['es_proyeccion'])
                                            <div class="mt-3 text-center">
                                                <small class="text-muted fw-medium">Datos actualizados hasta hoy. El ciclo académico finaliza el {{ \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_fin)->format('d/m/Y') }}</small>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Información por Examen --}}
                    <div class="row mb-4">
                        {{-- Primer Examen --}}
                        @if (isset($infoAsistencia['primer_examen']))
                            <div class="col-lg-4 mb-3">
                                <div class="card border @if ($infoAsistencia['primer_examen']['estado'] == 'inhabilitado') border-danger @elseif($infoAsistencia['primer_examen']['estado'] == 'amonestado') border-warning @else border-success @endif h-100 tw-group">
                                    <div class="card-header @if ($infoAsistencia['primer_examen']['estado'] == 'inhabilitado') bg-danger @elseif($infoAsistencia['primer_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                        <h5 class="card-title mb-0">Primera Evaluación</h5>
                                        <small class="fw-medium">{{ \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_primer_examen)->format('d/m/Y') }}</small>
                                        @if ($infoAsistencia['primer_examen']['es_proyeccion'])
                                            <span class="badge bg-light text-dark ms-2">Proyección</span>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center mb-4">
                                            <div class="metric-number @if ($infoAsistencia['primer_examen']['estado'] == 'inhabilitado') text-danger @elseif($infoAsistencia['primer_examen']['estado'] == 'amonestado') text-warning @else text-success @endif" style="font-size: 3rem;">
                                                {{ $infoAsistencia['primer_examen']['porcentaje_asistencia_actual'] ?? $infoAsistencia['primer_examen']['porcentaje_asistencia'] }}%
                                            </div>
                                            <h6 class="metric-label">Asistencia Actual</h6>
                                            @if ($infoAsistencia['primer_examen']['es_proyeccion'])
                                                <small class="text-muted">({{ $infoAsistencia['primer_examen']['dias_habiles_transcurridos'] }} de {{ $infoAsistencia['primer_examen']['dias_habiles'] }} días)</small>
                                            @endif
                                        </div>

                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="fw-medium">Días asistidos:</span>
                                                <span class="fw-bold text-success">{{ $infoAsistencia['primer_examen']['dias_asistidos'] }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="fw-medium">Faltas actuales:</span>
                                                <span class="fw-bold text-danger">{{ $infoAsistencia['primer_examen']['dias_falta'] }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="fw-medium">Límite amonestación:</span>
                                                <span class="fw-bold text-warning">{{ $infoAsistencia['primer_examen']['limite_amonestacion'] }} faltas</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-medium">Límite inhabilitación:</span>
                                                <span class="fw-bold text-danger">{{ $infoAsistencia['primer_examen']['limite_inhabilitacion'] }} faltas</span>
                                            </div>
                                        </div>

                                        @if ($infoAsistencia['primer_examen']['estado'] == 'inhabilitado')
                                            <div class="alert alert-danger mb-3">
                                                <i class="mdi mdi-close-circle me-2"></i>
                                                <strong>{{ $infoAsistencia['primer_examen']['mensaje'] }}</strong>
                                            </div>
                                        @elseif($infoAsistencia['primer_examen']['estado'] == 'amonestado')
                                            <div class="alert alert-warning mb-3">
                                                <i class="mdi mdi-alert-circle me-2"></i>
                                                <strong>{{ $infoAsistencia['primer_examen']['mensaje'] }}</strong>
                                            </div>
                                        @else
                                            <div class="alert alert-success mb-3">
                                                <i class="mdi mdi-check-circle me-2"></i>
                                                <strong>{{ $infoAsistencia['primer_examen']['mensaje'] }}</strong>
                                            </div>
                                        @endif

                                        {{-- Estado de rendición --}}
                                        @if (isset($infoAsistencia['primer_examen']) && $infoAsistencia['primer_examen']['estado'] != 'pendiente')
                                            @php
                                                $fechaExamen = \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_primer_examen);
                                                $examenYaPaso = \Carbon\Carbon::now()->greaterThan($fechaExamen);
                                            @endphp
                                            
                                            @if ($infoAsistencia['primer_examen']['puede_rendir'])
                                                @if ($infoAsistencia['primer_examen']['estado'] == 'regular')
                                                    <div class="alert alert-success text-center fw-bold">
                                                        @if ($examenYaPaso)
                                                            <i class="mdi mdi-check-circle-outline me-2"></i>Rendiste este examen sin restricciones.
                                                        @else
                                                            <i class="mdi mdi-check-circle-outline me-2"></i>Habilitado para rendir el examen.
                                                        @endif
                                                    </div>
                                                @elseif ($infoAsistencia['primer_examen']['estado'] == 'amonestado')
                                                    <div class="alert alert-warning text-center fw-bold">
                                                        @if ($examenYaPaso)
                                                            <i class="mdi mdi-alert-outline me-2"></i>Rendiste el examen con amonestación por inasistencias.
                                                        @else
                                                            <i class="mdi mdi-alert-outline me-2"></i>Habilitado con amonestación por faltas.
                                                        @endif
                                                    </div>
                                                @endif
                                            @else
                                                <div class="alert alert-danger text-center fw-bold">
                                                    <i class="mdi mdi-close-circle-outline me-2"></i>INHABILITADO: NO PUEDE RENDIR EXAMEN SEGÚN REGLAMENTO ACADÉMICO
                                                </div>
                                            @endif
                                        @endif
                                        <div class="text-center mt-3">
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detalleAsistenciaModal" 
                                                    data-periodo="Primera Evaluación"
                                                    data-asistencias='{{ json_encode($infoAsistencia['primer_examen']['asistencias'] ?? []) }}'
                                                    data-faltas='{{ json_encode($infoAsistencia['primer_examen']['faltas'] ?? []) }}'>
                                                <i class="mdi mdi-format-list-bulleted me-1"></i> Ver Detalle
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Segundo Examen --}}
                        @if (isset($infoAsistencia['segundo_examen']))
                            <div class="col-lg-4 mb-3">
                                <div class="card border @if ($infoAsistencia['segundo_examen']['estado'] == 'pendiente') border-secondary @elseif($infoAsistencia['segundo_examen']['estado'] == 'inhabilitado') border-danger @elseif($infoAsistencia['segundo_examen']['estado'] == 'amonestado') border-warning @else border-success @endif h-100 tw-group">
                                    <div class="card-header @if ($infoAsistencia['segundo_examen']['estado'] == 'pendiente') bg-secondary @elseif($infoAsistencia['segundo_examen']['estado'] == 'inhabilitado') bg-danger @elseif($infoAsistencia['segundo_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                        <h5 class="card-title mb-0">Segunda Evaluación</h5>
                                        <small class="fw-medium">{{ \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_segundo_examen)->format('d/m/Y') }}</small>
                                        @if ($infoAsistencia['segundo_examen']['estado'] != 'pendiente' && $infoAsistencia['segundo_examen']['es_proyeccion'])
                                            <span class="badge bg-light text-dark ms-2">Proyección</span>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        @if ($infoAsistencia['segundo_examen']['estado'] == 'pendiente')
                                            <div class="text-center py-4">
                                                <i class="mdi mdi-clock-outline" style="font-size: 4rem; color: #6c757d;"></i>
                                                <h5 class="mt-3 text-muted">{{ $infoAsistencia['segundo_examen']['mensaje'] }}</h5>
                                                <small class="text-muted">Comenzará después de la primera evaluación</small>
                                            </div>
                                        @else
                                            {{-- INICIO DE CÓDIGO CORREGIDO --}}
                                            <div class="text-center mb-4">
                                                <div class="metric-number @if ($infoAsistencia['segundo_examen']['estado'] == 'inhabilitado') text-danger @elseif($infoAsistencia['segundo_examen']['estado'] == 'amonestado') text-warning @else text-success @endif" style="font-size: 3rem;">
                                                    {{ $infoAsistencia['segundo_examen']['porcentaje_asistencia_actual'] ?? $infoAsistencia['segundo_examen']['porcentaje_asistencia'] }}%
                                                </div>
                                                <h6 class="metric-label">Asistencia Actual</h6>
                                                @if ($infoAsistencia['segundo_examen']['es_proyeccion'])
                                                    <small class="text-muted">({{ $infoAsistencia['segundo_examen']['dias_habiles_transcurridos'] }} de {{ $infoAsistencia['segundo_examen']['dias_habiles'] }} días)</small>
                                                @endif
                                            </div>

                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-medium">Días asistidos:</span>
                                                    <span class="fw-bold text-success">{{ $infoAsistencia['segundo_examen']['dias_asistidos'] }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-medium">Faltas actuales:</span>
                                                    <span class="fw-bold text-danger">{{ $infoAsistencia['segundo_examen']['dias_falta'] }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-medium">Límite amonestación:</span>
                                                    <span class="fw-bold text-warning">{{ $infoAsistencia['segundo_examen']['limite_amonestacion'] }} faltas</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-medium">Límite inhabilitación:</span>
                                                    <span class="fw-bold text-danger">{{ $infoAsistencia['segundo_examen']['limite_inhabilitacion'] }} faltas</span>
                                                </div>
                                            </div>

                                            @if ($infoAsistencia['segundo_examen']['estado'] == 'inhabilitado')
                                                <div class="alert alert-danger mb-3">
                                                    <i class="mdi mdi-close-circle me-2"></i>
                                                    <strong>{{ $infoAsistencia['segundo_examen']['mensaje'] }}</strong>
                                                </div>
                                            @elseif($infoAsistencia['segundo_examen']['estado'] == 'amonestado')
                                                <div class="alert alert-warning mb-3">
                                                    <i class="mdi mdi-alert-circle me-2"></i>
                                                    <strong>{{ $infoAsistencia['segundo_examen']['mensaje'] }}</strong>
                                                </div>
                                            @else
                                                <div class="alert alert-success mb-3">
                                                    <i class="mdi mdi-check-circle me-2"></i>
                                                    <strong>{{ $infoAsistencia['segundo_examen']['mensaje'] }}</strong>
                                                </div>
                                            @endif

                                            {{-- Estado de rendición --}}
                                            @if (isset($infoAsistencia['segundo_examen']) && $infoAsistencia['segundo_examen']['estado'] != 'pendiente')
                                                @php
                                                    $fechaExamen = \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_segundo_examen);
                                                    $examenYaPaso = \Carbon\Carbon::now()->greaterThan($fechaExamen);
                                                @endphp
                                                
                                                @if ($infoAsistencia['segundo_examen']['puede_rendir'])
                                                    @if ($infoAsistencia['segundo_examen']['estado'] == 'regular')
                                                        <div class="alert alert-success text-center fw-bold">
                                                            @if ($examenYaPaso)
                                                                <i class="mdi mdi-check-circle-outline me-2"></i>Rendiste este examen sin restricciones.
                                                            @else
                                                                <i class="mdi mdi-check-circle-outline me-2"></i>Habilitado para rendir el examen.
                                                            @endif
                                                        </div>
                                                    @elseif ($infoAsistencia['segundo_examen']['estado'] == 'amonestado')
                                                        <div class="alert alert-warning text-center fw-bold">
                                                            @if ($examenYaPaso)
                                                                <i class="mdi mdi-alert-outline me-2"></i>Rendiste el examen con amonestación por inasistencias.
                                                            @else
                                                                <i class="mdi mdi-alert-outline me-2"></i>Habilitado con amonestación por faltas.
                                                            @endif
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="alert alert-danger text-center fw-bold">
                                                        <i class="mdi mdi-close-circle-outline me-2"></i>INHABILITADO: NO PUEDE RENDIR EXAMEN SEGÚN REGLAMENTO ACADÉMICO
                                                    </div>
                                                @endif
                                            @endif
                                            <div class="text-center mt-3">
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detalleAsistenciaModal" 
                                                        data-periodo="Segunda Evaluación"
                                                        data-asistencias='{{ json_encode($infoAsistencia['segundo_examen']['asistencias'] ?? []) }}'
                                                        data-faltas='{{ json_encode($infoAsistencia['segundo_examen']['faltas'] ?? []) }}'>
                                                    <i class="mdi mdi-format-list-bulleted me-1"></i> Ver Detalle
                                                </button>
                                            </div>
                                            {{-- FIN DE CÓDIGO CORREGIDO --}}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        {{-- Tercer Examen --}}
                        @if (isset($infoAsistencia['tercer_examen']))
                            <div class="col-lg-4 mb-3">
                                <div class="card border @if ($infoAsistencia['tercer_examen']['estado'] == 'pendiente') border-secondary @elseif($infoAsistencia['tercer_examen']['estado'] == 'inhabilitado') border-danger @elseif($infoAsistencia['tercer_examen']['estado'] == 'amonestado') border-warning @else border-success @endif h-100 tw-group">
                                    <div class="card-header @if ($infoAsistencia['tercer_examen']['estado'] == 'pendiente') bg-secondary @elseif($infoAsistencia['tercer_examen']['estado'] == 'inhabilitado') bg-danger @elseif($infoAsistencia['tercer_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                        <h5 class="card-title mb-0">Tercera Evaluación</h5>
                                        <small class="fw-medium">{{ \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_tercer_examen)->format('d/m/Y') }}</small>
                                        @if ($infoAsistencia['tercer_examen']['estado'] != 'pendiente' && $infoAsistencia['tercer_examen']['es_proyeccion'])
                                            <span class="badge bg-light text-dark ms-2">Proyección</span>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        @if ($infoAsistencia['tercer_examen']['estado'] == 'pendiente')
                                            <div class="text-center py-4">
                                                <i class="mdi mdi-clock-outline" style="font-size: 4rem; color: #6c757d;"></i>
                                                <h5 class="mt-3 text-muted">{{ $infoAsistencia['tercer_examen']['mensaje'] }}</h5>
                                                <small class="text-muted">Comenzará después de la segunda evaluación</small>
                                            </div>
                                        @else
                                            {{-- INICIO DE CÓDIGO CORREGIDO --}}
                                            <div class="text-center mb-4">
                                                <div class="metric-number @if ($infoAsistencia['tercer_examen']['estado'] == 'inhabilitado') text-danger @elseif($infoAsistencia['tercer_examen']['estado'] == 'amonestado') text-warning @else text-success @endif" style="font-size: 3rem;">
                                                    {{ $infoAsistencia['tercer_examen']['porcentaje_asistencia_actual'] ?? $infoAsistencia['tercer_examen']['porcentaje_asistencia'] }}%
                                                </div>
                                                <h6 class="metric-label">Asistencia Actual</h6>
                                                @if ($infoAsistencia['tercer_examen']['es_proyeccion'])
                                                    <small class="text-muted">({{ $infoAsistencia['tercer_examen']['dias_habiles_transcurridos'] }} de {{ $infoAsistencia['tercer_examen']['dias_habiles'] }} días)</small>
                                                @endif
                                            </div>

                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-medium">Días asistidos:</span>
                                                    <span class="fw-bold text-success">{{ $infoAsistencia['tercer_examen']['dias_asistidos'] }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-medium">Faltas actuales:</span>
                                                    <span class="fw-bold text-danger">{{ $infoAsistencia['tercer_examen']['dias_falta'] }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-medium">Límite amonestación:</span>
                                                    <span class="fw-bold text-warning">{{ $infoAsistencia['tercer_examen']['limite_amonestacion'] }} faltas</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-medium">Límite inhabilitación:</span>
                                                    <span class="fw-bold text-danger">{{ $infoAsistencia['tercer_examen']['limite_inhabilitacion'] }} faltas</span>
                                                </div>
                                            </div>

                                            @if ($infoAsistencia['tercer_examen']['estado'] == 'inhabilitado')
                                                <div class="alert alert-danger mb-3">
                                                    <i class="mdi mdi-close-circle me-2"></i>
                                                    <strong>{{ $infoAsistencia['tercer_examen']['mensaje'] }}</strong>
                                                </div>
                                            @elseif($infoAsistencia['tercer_examen']['estado'] == 'amonestado')
                                                <div class="alert alert-warning mb-3">
                                                    <i class="mdi mdi-alert-circle me-2"></i>
                                                    <strong>{{ $infoAsistencia['tercer_examen']['mensaje'] }}</strong>
                                                </div>
                                            @else
                                                <div class="alert alert-success mb-3">
                                                    <i class="mdi mdi-check-circle me-2"></i>
                                                    <strong>{{ $infoAsistencia['tercer_examen']['mensaje'] }}</strong>
                                                </div>
                                            @endif

                                            {{-- Estado de rendición --}}
                                            @if (isset($infoAsistencia['tercer_examen']) && $infoAsistencia['tercer_examen']['estado'] != 'pendiente')
                                                @php
                                                    $fechaExamen = \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_tercer_examen);
                                                    $examenYaPaso = \Carbon\Carbon::now()->greaterThan($fechaExamen);
                                                @endphp
                                                
                                                @if ($infoAsistencia['tercer_examen']['puede_rendir'])
                                                    @if ($infoAsistencia['tercer_examen']['estado'] == 'regular')
                                                        <div class="alert alert-success text-center fw-bold">
                                                            @if ($examenYaPaso)
                                                                <i class="mdi mdi-check-circle-outline me-2"></i>Rendiste este examen sin restricciones.
                                                            @else
                                                                <i class="mdi mdi-check-circle-outline me-2"></i>Habilitado para rendir el examen.
                                                            @endif
                                                        </div>
                                                    @elseif ($infoAsistencia['tercer_examen']['estado'] == 'amonestado')
                                                        <div class="alert alert-warning text-center fw-bold">
                                                            @if ($examenYaPaso)
                                                                <i class="mdi mdi-alert-outline me-2"></i>Rendiste el examen con amonestación por inasistencias.
                                                            @else
                                                                <i class="mdi mdi-alert-outline me-2"></i>Habilitado con amonestación por faltas.
                                                            @endif
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="alert alert-danger text-center fw-bold">
                                                        <i class="mdi mdi-close-circle-outline me-2"></i>INHABILITADO: NO PUEDE RENDIR EXAMEN SEGÚN REGLAMENTO ACADÉMICO
                                                    </div>
                                                @endif
                                            @endif
                                            <div class="text-center mt-3">
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detalleAsistenciaModal" 
                                                        data-periodo="Tercera Evaluación"
                                                        data-asistencias='{{ json_encode($infoAsistencia['tercer_examen']['asistencias'] ?? []) }}'
                                                        data-faltas='{{ json_encode($infoAsistencia['tercer_examen']['faltas'] ?? []) }}'>
                                                    <i class="mdi mdi-format-list-bulleted me-1"></i> Ver Detalle
                                                </button>
                                            </div>
                                            {{-- FIN DE CÓDIGO CORREGIDO --}}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Reglamento Académico --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5 class="alert-heading mb-3 tw-flex tw-items-center">
                                    <i class="mdi mdi-information-outline me-2"></i>Reglamento Académico - Control de Asistencia
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li class="mb-2"><strong>Horario Académico:</strong> Clases de Lunes a Viernes</li>
                                            <li class="mb-2"><strong>Inicio de Cómputo:</strong> Desde tu primer registro: <strong>{{ $primerRegistro ? \Carbon\Carbon::parse($primerRegistro->fecha_registro)->format('d/m/Y') : 'Sin registro aún' }}</strong></li>
                                            <li class="mb-2"><strong>Amonestación:</strong> Al superar el 20% de inasistencias</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li class="mb-2"><strong>Inhabilitación:</strong> Al superar el 30% de inasistencias</li>
                                            <li class="mb-2"><strong>Períodos de Evaluación:</strong> Asistencia se reinicia después de cada examen</li>
                                            <li class="mb-2"><strong>Responsabilidad:</strong> Cumplir con la asistencia mínima requerida</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- Sin registros de asistencia --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <h4 class="alert-heading mb-3 tw-flex tw-items-center">
                                    <i class="mdi mdi-alert-outline me-2"></i>Control de Asistencia Académica
                                </h4>
                                <p class="mb-0">Aún no tienes registros de asistencia en este ciclo académico. Tu control de asistencia comenzará a partir de tu primer registro en el sistema biométrico de la institución.</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- INICIO: Modal para Detalles de Asistencia --}}
    <div class="modal fade" id="detalleAsistenciaModal" tabindex="-1" aria-labelledby="detalleAsistenciaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleAsistenciaModalLabel">Detalle de Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    {{-- El contenido se llenará dinámicamente con JavaScript --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    {{-- FIN: Modal para Detalles de Asistencia --}}

    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-check-circle me-2"></i>Confirmar Inscripción Académica
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="mdi mdi-information-outline me-2"></i>
                        <strong>Revise cuidadosamente los datos antes de confirmar su inscripción</strong>
                    </div>
                    <div id="resumen-inscripcion"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarInscripcion">
                        <i class="mdi mdi-check me-1"></i>Confirmar Inscripción
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="subirConstanciaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-file-upload me-2"></i>Subir Constancia de Inscripción Firmada
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading fw-bold">
                            <i class="mdi mdi-information-outline me-2"></i>Instrucciones para el Proceso:
                        </h6>
                        <ol class="mb-0">
                            <li class="mb-2">Descargue e imprima la constancia de inscripción generada por el sistema</li>
                            <li class="mb-2">Firme el documento en el espacio designado y coloque su huella digital</li>
                            <li class="mb-2">Escanee o fotografíe el documento firmado con buena calidad y resolución</li>
                            <li>Suba el archivo utilizando el formulario siguiente</li>
                        </ol>
                    </div>

                    <form id="formSubirConstancia" enctype="multipart/form-data">
                        <input type="hidden" id="postulacion_id" name="postulacion_id">

                        <div class="mb-4">
                            <label for="documento_constancia" class="form-label">
                                <i class="mdi mdi-file-document me-2"></i>Seleccionar archivo de constancia firmada:
                            </label>
                            <input type="file" class="form-control" id="documento_constancia" name="documento_constancia"
                                accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted mt-2 d-block">
                                <i class="mdi mdi-information me-1"></i>Formatos aceptados: PDF, JPG, PNG | Tamaño máximo: 5MB
                            </small>
                        </div>

                        <div id="preview-constancia" class="mb-3" style="display: none;">
                            <h6 class="fw-bold mb-3">Vista Previa del Documento:</h6>
                            <div class="border rounded-3 p-3 bg-light">
                                <img id="imagen-preview" src="" alt="Vista previa" class="img-fluid rounded"
                                    style="max-height: 400px; display: none;">
                                <div id="pdf-preview" class="text-center p-4" style="display: none;">
                                    <i class="mdi mdi-file-pdf-box text-danger" style="font-size: 64px;"></i>
                                    <h6 id="pdf-nombre" class="mt-2 fw-bold"></h6>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>Cancelar Proceso
                    </button>
                    <button type="button" class="btn btn-success" id="btnSubirConstancia">
                        <i class="mdi mdi-upload me-1"></i>Subir Constancia Firmada
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    
    @vite('resources/js/dashboardestudiante/index.js')

    {{-- Script para la modal de detalle de asistencia --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var detalleAsistenciaModal = document.getElementById('detalleAsistenciaModal');
            detalleAsistenciaModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var periodo = button.getAttribute('data-periodo');
                var asistencias = JSON.parse(button.getAttribute('data-asistencias'));
                var faltas = JSON.parse(button.getAttribute('data-faltas'));
                
                var modalTitle = detalleAsistenciaModal.querySelector('.modal-title');
                var modalBody = detalleAsistenciaModal.querySelector('.modal-body');
                
                modalTitle.textContent = 'Detalle de Asistencia - ' + periodo;
                
                // Limpiar contenido anterior
                modalBody.innerHTML = '';

                // Asistencias
                var asistenciasHtml = '<h5><i class="mdi mdi-check-circle text-success"></i> Asistencias (' + asistencias.length + ')</h5>';
                if (asistencias && asistencias.length > 0) {
                    asistenciasHtml += '<ul class="list-group list-group-flush mb-3">';
                    asistencias.forEach(function(fecha) {
                        var date = new Date(fecha + 'T00:00:00');
                        var formattedDate = date.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                        asistenciasHtml += `<li class="list-group-item">${formattedDate}</li>`;
                    });
                    asistenciasHtml += '</ul>';
                } else {
                    asistenciasHtml += '<p class="text-muted">No hay asistencias registradas para este período.</p>';
                }
                modalBody.innerHTML += asistenciasHtml;

                // Faltas
                var faltasHtml = '<hr><h5><i class="mdi mdi-close-circle text-danger"></i> Faltas (' + faltas.length + ')</h5>';
                if (faltas && faltas.length > 0) {
                    faltasHtml += '<ul class="list-group list-group-flush">';
                    faltas.forEach(function(fecha) {
                        var date = new Date(fecha + 'T00:00:00');
                        var formattedDate = date.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                        faltasHtml += `<li class="list-group-item">${formattedDate}</li>`;
                    });
                    faltasHtml += '</ul>';
                } else {
                    faltasHtml += '<p class="text-muted">No se han registrado faltas para este período.</p>';
                }
                modalBody.innerHTML += faltasHtml;
            });
        });
    </script>

    @if (isset($anuncios) && $anuncios->count() > 0)
        <div class="modal fade" id="anunciosModal" tabindex="-1" aria-labelledby="anunciosModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(10px); border-radius: 20px; overflow: hidden; border: none;">
                    <div class="modal-body p-0">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 1rem; right: 1rem; z-index: 2;"></button>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var anunciosModal = new bootstrap.Modal(document.getElementById('anunciosModal'));
                anunciosModal.show();
            });
        </script>
    @endif
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Iniciando integración SweetAlert2 con CEPRE UNAMAD');
            
            // ========== CONFIGURACIÓN BÁSICA SWEETALERT2 ==========
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal2-toast-cepre',
                    title: 'swal2-toast-title',
                    content: 'swal2-toast-content'
                }
            });

            // ========== FUNCIÓN PARA EXTRAER DATOS DEL FORMULARIO ==========
            function extraerDatosFormularioCompleto() {
                console.log('📋 Extrayendo datos completos del formulario...');
                
                const datos = {};
                const documentos = {};
                
                // 1. DATOS ACADÉMICOS
                const tipoInscripcion = document.querySelector('[name="tipo_inscripcion"]');
                if (tipoInscripcion) {
                    datos.tipo = tipoInscripcion.value === 'postulante' ? 'Postulante' : 'Reforzamiento';
                }
                
                const carrera = document.querySelector('[name="carrera_id"]');
                if (carrera && carrera.selectedIndex > 0) {
                    datos.carrera = carrera.options[carrera.selectedIndex].text;
                }
                
                const turno = document.querySelector('[name="turno_id"]');
                if (turno && turno.selectedIndex > 0) {
                    datos.turno = turno.options[turno.selectedIndex].text;
                }
                
                const colegio = document.querySelector('[name="centro_educativo_id"]');
                if (colegio && colegio.selectedIndex > 0) {
                    datos.colegio = colegio.options[colegio.selectedIndex].text;
                }
                
                // 2. DATOS DEL VOUCHER  
                const recibo = document.querySelector('[name="numero_recibo"]');
                if (recibo) datos.recibo = recibo.value;
                
                const fecha = document.querySelector('[name="fecha_emision_voucher"]');
                if (fecha) datos.fecha = fecha.value;
                
                const matricula = document.querySelector('[name="monto_matricula"]');
                if (matricula) datos.matricula = matricula.value;
                
                const ensenanza = document.querySelector('[name="monto_ensenanza"]');
                if (ensenanza) datos.ensenanza = ensenanza.value;
                
                if (datos.matricula && datos.ensenanza) {
                    datos.total = (parseFloat(datos.matricula) + parseFloat(datos.ensenanza)).toFixed(2);
                }
                
                // 3. DOCUMENTOS REQUERIDOS
                const fileInputs = [
                    { name: 'voucher_pago', label: 'Voucher de Pago' },
                    { name: 'certificado_estudios', label: 'Certificado de Estudios' },
                    { name: 'carta_compromiso', label: 'Carta de Compromiso' },
                    { name: 'constancia_estudios', label: 'Constancia de Estudios' },
                    { name: 'dni_documento', label: 'Documento DNI' },
                    { name: 'foto_carnet', label: 'Foto Carnet' }
                ];
                
                fileInputs.forEach(doc => {
                    const input = document.querySelector(`[name="${doc.name}"]`);
                    if (input && input.files && input.files[0]) {
                        documentos[doc.name] = {
                            label: doc.label,
                            archivo: input.files[0].name,
                            tamaño: (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB'
                        };
                    }
                });
                
                console.log('✅ Datos extraídos:', { datos, documentos });
                
                // Crear HTML del resumen
                return crearHTMLResumen(datos, documentos);
            }
            
            // ========== FUNCIÓN PARA CREAR HTML DEL RESUMEN ==========
            function crearHTMLResumen(datos, documentos) {
                return `
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                        <div style="margin-bottom: 2rem;">
                            <div style="display: flex; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e91e63;">
                                <span style="font-size: 1.3rem; margin-right: 0.5rem; color: #e91e63;">🎓</span>
                                <h6 style="margin: 0; color: #1a237e; font-weight: 700;">Información Académica</h6>
                            </div>
                            <div style="background: white; padding: 1rem; border-radius: 6px; border: 1px solid #e9ecef;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    ${datos.tipo ? `
                                    <div>
                                        <strong style="color: #1a237e;">Tipo de Inscripción:</strong><br>
                                        <span style="color: #495057; font-weight: 500;">${datos.tipo}</span>
                                    </div>
                                    ` : ''}
                                    
                                    ${datos.carrera ? `
                                    <div>
                                        <strong style="color: #1a237e;">Carrera Profesional:</strong><br>
                                        <span style="color: #495057; font-weight: 500;">${datos.carrera}</span>
                                    </div>
                                    ` : ''}
                                    
                                    ${datos.turno ? `
                                    <div>
                                        <strong style="color: #1a237e;">Turno de Estudios:</strong><br>
                                        <span style="color: #495057; font-weight: 500;">${datos.turno}</span>
                                    </div>
                                    ` : ''}
                                    
                                    ${datos.colegio ? `
                                    <div>
                                        <strong style="color: #1a237e;">Institución Educativa:</strong><br>
                                        <span style="color: #495057; font-weight: 500; font-size: 0.9rem;">${datos.colegio}</span>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>

                        ${(datos.recibo || datos.fecha || datos.matricula || datos.ensenanza) ? `
                        <div style="margin-bottom: 2rem;">
                            <div style="display: flex; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #00bcd4;">
                                <span style="font-size: 1.3rem; margin-right: 0.5rem; color: #00bcd4;">💳</span>
                                <h6 style="margin: 0; color: #1a237e; font-weight: 700;">Información de Pago</h6>
                            </div>
                            <div style="background: white; padding: 1rem; border-radius: 6px; border: 1px solid #e9ecef;">
                                ${datos.recibo ? `
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>N° Recibo:</strong></span>
                                    <span style="font-weight: 600; color: #495057;">${datos.recibo}</span>
                                </div>
                                ` : ''}
                                
                                ${datos.fecha ? `
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>Fecha Emisión:</strong></span>
                                    <span style="font-weight: 600; color: #495057;">${datos.fecha}</span>
                                </div>
                                ` : ''}
                                
                                ${datos.matricula ? `
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>Matrícula:</strong></span>
                                    <span style="font-weight: 600; color: #28a745;">S/ ${datos.matricula}</span>
                                </div>
                                ` : ''}
                                
                                ${datos.ensenanza ? `
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>Enseñanza:</strong></span>
                                    <span style="font-weight: 600; color: #28a745;">S/ ${datos.ensenanza}</span>
                                </div>
                                ` : ''}
                                
                                ${datos.total ? `
                                <div style="display: flex; justify-content: space-between; border-top: 1px solid #dee2e6; padding-top: 0.5rem; font-weight: 700; font-size: 1.1rem;">
                                    <span>Total a Pagar:</span>
                                    <span style="color: #e91e63;">S/ ${datos.total}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        ` : ''}

                        ${Object.keys(documentos).length > 0 ? `
                        <div style="margin-bottom: 2rem;">
                            <div style="display: flex; align-items: center; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #8bc34a;">
                                <span style="font-size: 1.3rem; margin-right: 0.5rem; color: #8bc34a;">📎</span>
                                <h6 style="margin: 0; color: #1a237e; font-weight: 700;">Documentos Adjuntos</h6>
                            </div>
                            <div style="background: white; padding: 1rem; border-radius: 6px; border: 1px solid #e9ecef;">
                                ${Object.entries(documentos).map(([key, doc]) => `
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 4px;">
                                        <div>
                                            <strong style="color: #1a237e;">${doc.label}:</strong><br>
                                            <small style="color: #6c757d;">${doc.archivo} (${doc.tamaño})</small>
                                        </div>
                                        <span style="color: #28a745; font-size: 1.2rem;">✓</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}
                        
                        <div style="padding: 1rem; background: #d1ecf1; border-radius: 6px; border-left: 4px solid #00bcd4;">
                            <small style="color: #0c5460; font-weight: 500;">
                                ✓ Verifique que todos los datos sean correctos. Una vez confirmada la postulación, será enviada para revisión y aprobación por parte de la administración académica.
                            </small>
                        </div>
                    </div>
                `;
            }
            
            // ========== FUNCIÓN PRINCIPAL SWEETALERT2 INSTITUCIONAL ==========
            function mostrarConfirmacionInscripcionInstitucional(datosHTML) {
                return Swal.fire({
                    title: `
                        <div style="display: flex; align-items: center; gap: 1rem; justify-content: center; margin-bottom: 1rem;">
                            <div style="font-size: 3rem; color: #ffd700;">🛡️</div>
                            <div style="text-align: left;">
                                <h3 style="margin: 0; color: #1a237e; font-size: 1.8rem; font-weight: 800;">Confirmar Inscripción Académica</h3>
                                <p style="margin: 0; color: #455a64; font-size: 1rem; opacity: 0.9;">Centro de Estudios Preuniversitarios UNAMAD</p>
                            </div>
                        </div>
                    `,
                    html: `
                        <div style="text-align: left; margin-top: 1rem;">
                            <div style="background: linear-gradient(135deg, rgba(0, 188, 212, 0.15) 0%, rgba(0, 188, 212, 0.08) 100%); 
                                        border: 2px solid #00bcd4; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;
                                        display: flex; align-items: flex-start; gap: 1rem;">
                                <div style="background: #00bcd4; color: white; width: 40px; height: 40px; border-radius: 50%;
                                            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                                            box-shadow: 0 4px 16px rgba(0, 188, 212, 0.3); font-size: 1.2rem;">ℹ️</div>
                                <div>
                                    <h6 style="color: #1a237e; font-weight: 700; margin-bottom: 0.5rem; font-size: 1.1rem;">¡Atención Estudiante!</h6>
                                    <p style="color: #455a64; margin: 0; font-weight: 500; line-height: 1.6;">
                                        Revise cuidadosamente toda la información antes de confirmar su inscripción. 
                                        Una vez confirmada, no podrá realizar cambios.
                                    </p>
                                </div>
                            </div>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.8rem; padding: 0.8rem;
                                            background: rgba(255, 255, 255, 0.8); border-radius: 8px; border: 1px solid rgba(26, 35, 126, 0.1);">
                                    <div style="background: linear-gradient(135deg, #e91e63 0%, #1a237e 100%); color: white; 
                                                width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; 
                                                justify-content: center; font-weight: 700; font-size: 0.8rem;">1</div>
                                    <span style="color: #1a237e; font-weight: 600;">Verifique sus datos personales y académicos</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.8rem; padding: 0.8rem;
                                            background: rgba(255, 255, 255, 0.8); border-radius: 8px; border: 1px solid rgba(26, 35, 126, 0.1);">
                                    <div style="background: linear-gradient(135deg, #e91e63 0%, #1a237e 100%); color: white; 
                                                width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; 
                                                justify-content: center; font-weight: 700; font-size: 0.8rem;">2</div>
                                    <span style="color: #1a237e; font-weight: 600;">Confirme la carrera y turno seleccionado</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.8rem; padding: 0.8rem;
                                            background: rgba(255, 255, 255, 0.8); border-radius: 8px; border: 1px solid rgba(26, 35, 126, 0.1);">
                                    <div style="background: linear-gradient(135deg, #e91e63 0%, #1a237e 100%); color: white; 
                                                width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; 
                                                justify-content: center; font-weight: 700; font-size: 0.8rem;">3</div>
                                    <span style="color: #1a237e; font-weight: 600;">Proceda con la confirmación final</span>
                                </div>
                            </div>

                            <div style="background: linear-gradient(135deg, rgba(139, 195, 74, 0.1) 0%, rgba(139, 195, 74, 0.05) 100%); 
                                        border: 2px solid #8bc34a; border-radius: 12px; padding: 1.5rem;">
                                <h6 style="color: #1a237e; font-weight: 700; margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center;">
                                    <span style="font-size: 1.3rem; color: #8bc34a; margin-right: 0.5rem;">📋</span> Resumen de Inscripción
                                </h6>
                                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    ${datosHTML || '<p style="color: #455a64; text-align: center;">Cargando datos de inscripción...</p>'}
                                </div>
                            </div>
                        </div>
                    `,
                    width: '750px',
                    showCancelButton: true,
                    confirmButtonText: '<i class="mdi mdi-check-circle me-1"></i>Confirmar mi Postulación',
                    cancelButtonText: '<i class="mdi mdi-close me-1"></i>Cancelar Proceso',
                    reverseButtons: true,
                    focusConfirm: true,
                    customClass: {
                        popup: 'animate__animated animate__zoomIn',
                        confirmButton: 'btn-confirm-sweet',
                        cancelButton: 'btn-cancel-sweet'
                    },
                    didOpen: () => {
                        // Inyectar estilos personalizados
                        const style = document.createElement('style');
                        style.textContent = `
                            .btn-confirm-sweet {
                                background: linear-gradient(135deg, #8bc34a 0%, #00bcd4 100%) !important;
                                border: none !important;
                                font-weight: 700 !important;
                                padding: 1rem 2.5rem !important;
                                border-radius: 12px !important;
                                box-shadow: 0 8px 24px rgba(139, 195, 74, 0.4) !important;
                                transition: all 0.3s ease !important;
                                font-size: 1rem !important;
                            }
                            .btn-confirm-sweet:hover {
                                background: linear-gradient(135deg, #689f38 0%, #0097a7 100%) !important;
                                transform: translateY(-2px) scale(1.02) !important;
                                box-shadow: 0 12px 32px rgba(139, 195, 74, 0.5) !important;
                            }
                            .btn-cancel-sweet {
                                background: linear-gradient(135deg, #455a64 0%, #607d8b 100%) !important;
                                border: none !important;
                                font-weight: 600 !important;
                                padding: 1rem 2rem !important;
                                border-radius: 12px !important;
                                box-shadow: 0 6px 20px rgba(69, 90, 100, 0.3) !important;
                                transition: all 0.3s ease !important;
                                font-size: 1rem !important;
                            }
                            .btn-cancel-sweet:hover {
                                transform: translateY(-2px) !important;
                                box-shadow: 0 8px 24px rgba(69, 90, 100, 0.4) !important;
                            }
                            .swal2-toast-cepre {
                                border-radius: 12px !important;
                            }
                            .swal2-toast-title {
                                color: #1a237e !important;
                                font-weight: 700 !important;
                            }
                        `;
                        document.head.appendChild(style);
                    }
                });
            }
            
            // ========== INTERCEPTOR INMEDIATO DE SHOWCONFIRMMODAL ==========
            
            // Método 1: Interceptor inmediato y persistente
            let interceptorActivo = false;
            
            function interceptarShowConfirmModal() {
                if (typeof window.showConfirmModal === 'function' && !interceptorActivo) {
                    console.log('🔄 Interceptando showConfirmModal para SweetAlert2');
                    interceptorActivo = true;
                    
                    const originalShowConfirm = window.showConfirmModal;
                    
                    window.showConfirmModal = function(data) {
                        console.log('📞 showConfirmModal interceptado con datos:', data);
                        
                        // Crear HTML del resumen
                        let resumenHTML = '';
                        if (data) {
                            // Si los datos vienen como string HTML, usarlos directamente
                            if (typeof data === 'string') {
                                resumenHTML = data;
                            } else {
                                // Si vienen como objeto, intentar extraerlos del DOM
                                resumenHTML = extraerDatosFormularioCompleto();
                            }
                        } else {
                            // Fallback: extraer del formulario actual
                            resumenHTML = extraerDatosFormularioCompleto();
                        }
                        
                        // Mostrar SweetAlert2 institucional
                        mostrarConfirmacionInscripcionInstitucional(resumenHTML).then((result) => {
                            if (result.isConfirmed) {
                                console.log('✅ Usuario confirmó la inscripción');
                                procesarInscripcionConfirmada();
                            } else {
                                console.log('❌ Usuario canceló la inscripción');
                                Toast.fire({
                                    icon: 'info',
                                    title: 'Proceso Cancelado',
                                    text: 'No se realizó ningún cambio en su postulación.',
                                    background: '#cce7ff',
                                    color: '#004085'
                                });
                            }
                        });
                    };
                    
                    console.log('✅ showConfirmModal sobrescrito exitosamente');
                    return true;
                }
                return false;
            }
            
            // Intentos múltiples de interceptar
            interceptarShowConfirmModal(); // Inmediato
            setTimeout(interceptarShowConfirmModal, 100);  // 100ms
            setTimeout(interceptarShowConfirmModal, 500);  // 500ms
            setTimeout(interceptarShowConfirmModal, 1000); // 1s
            setTimeout(interceptarShowConfirmModal, 2000); // 2s
            setTimeout(interceptarShowConfirmModal, 3000); // 3s
            
            // Método 2: Observer para detectar cuando se carga el script original
            const observer = new MutationObserver(() => {
                interceptarShowConfirmModal();
            });
            observer.observe(document.head, { childList: true, subtree: true });
            
            // Método 3: Interceptor por evento de botón directo
            document.addEventListener('click', function(e) {
                const button = e.target;
                
                // Si es un botón de inscripción y no hemos interceptado showConfirmModal
                if ((button.textContent.toLowerCase().includes('inscrib') || 
                     button.classList.contains('btn-inscribir') ||
                     button.id.toLowerCase().includes('inscrib')) &&
                    !interceptorActivo) {
                    
                    console.log('🎯 Botón de inscripción detectado, interceptando evento');
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Verificar formulario antes de mostrar confirmación
                    const form = button.closest('form') || document.querySelector('#contenedor-inscripcion form');
                    if (form && !form.checkValidity()) {
                        console.log('⚠️ Formulario inválido');
                        form.reportValidity();
                        return false;
                    }
                    
                    // Extraer datos y mostrar SweetAlert2 directamente
                    const resumenHTML = extraerDatosFormularioCompleto();
                    mostrarConfirmacionInscripcionInstitucional(resumenHTML).then((result) => {
                        if (result.isConfirmed) {
                            console.log('✅ Usuario confirmó la inscripción (vía interceptor directo)');
                            procesarInscripcionConfirmada();
                        }
                    });
                    
                    return false;
                }
            }, true); // Usar capture para interceptar antes
            
            // ========== FUNCIÓN PARA PROCESAR INSCRIPCIÓN CONFIRMADA ==========
            function procesarInscripcionConfirmada() {
                console.log('🔄 Procesando inscripción confirmada...');
                
                // Mostrar loading
                showLoading('Procesando Inscripción', 'Enviando datos de postulación...');
                
                // Buscar y ejecutar la función real de envío
                setTimeout(() => {
                    // Opción 1: Buscar función del botón de confirmación original
                    const btnConfirmar = document.getElementById('btnConfirmarInscripcion');
                    if (btnConfirmar) {
                        console.log('🎯 Ejecutando click en botón original');
                        btnConfirmar.click();
                        return;
                    }
                    
                    // Opción 2: Buscar función de envío de formulario
                    if (typeof window.enviarFormularioInscripcion === 'function') {
                        console.log('🎯 Ejecutando enviarFormularioInscripcion');
                        window.enviarFormularioInscripcion();
                        return;
                    }
                    
                    // Opción 3: Envío directo del formulario
                    const form = document.querySelector('#contenedor-inscripcion form, form[id*="inscripcion"], form[id*="postulacion"]');
                    if (form) {
                        console.log('🎯 Enviando formulario directamente:', form);
                        
                        // Crear evento de submit
                        const submitEvent = new Event('submit', {
                            'bubbles': true,
                            'cancelable': true
                        });
                        
                        form.dispatchEvent(submitEvent);
                        return;
                    }
                    
                    // Opción 4: Fallback - buscar función confirmarInscripcion
                    if (typeof window.confirmarInscripcion === 'function') {
                        // Verificar que no sea la función de mostrar modal
                        const funcionString = window.confirmarInscripcion.toString();
                        if (funcionString.indexOf('showConfirmModal') === -1) {
                            console.log('🎯 Ejecutando confirmarInscripcion');
                            window.confirmarInscripcion();
                            return;
                        }
                    }
                    
                    // Si nada funciona
                    console.error('❌ No se encontró método de envío válido');
                    closeLoading();
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Error de Sistema',
                        text: 'No se pudo procesar la inscripción. Contacte al administrador del sistema.',
                        background: '#f8d7da',
                        color: '#721c24'
                    });
                }, 1000); // Simular tiempo de procesamiento
            }
            
            // ========== REEMPLAZAR TOASTR CON SWEETALERT2 ==========
            if (typeof window.toastr === 'undefined') {
                window.toastr = {};
            }
            
            window.toastr.success = function(message, title = 'Éxito') {
                Toast.fire({ 
                    icon: 'success', 
                    title: title, 
                    text: message, 
                    background: '#d4edda', 
                    color: '#155724' 
                });
            };
            
            window.toastr.error = function(message, title = 'Error') {
                Toast.fire({ 
                    icon: 'error', 
                    title: title, 
                    text: message, 
                    background: '#f8d7da', 
                    color: '#721c24' 
                });
            };
            
            window.toastr.warning = function(message, title = 'Advertencia') {
                Toast.fire({ 
                    icon: 'warning', 
                    title: title, 
                    text: message, 
                    background: '#fff3cd', 
                    color: '#856404' 
                });
            };
            
            window.toastr.info = function(message, title = 'Información') {
                Toast.fire({ 
                    icon: 'info', 
                    title: title, 
                    text: message, 
                    background: '#cce7ff', 
                    color: '#004085' 
                });
            };
            
            // ========== FUNCIONES DE LOADING ==========
            window.showLoading = function(title = 'Procesando...', text = 'Por favor espere') {
                Swal.fire({ 
                    title: title, 
                    text: text, 
                    allowOutsideClick: false, 
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'swal2-loading-cepre'
                    },
                    didOpen: () => { 
                        Swal.showLoading(); 
                    } 
                });
            };

            window.closeLoading = function() { 
                Swal.close(); 
            };
            
            // ========== MEJORAR VALIDACIÓN DE FORMULARIOS ==========
            document.addEventListener('invalid', function(e) {
                e.preventDefault();
                const field = e.target;
                const fieldName = field.closest('.mb-3')?.querySelector('label')?.textContent?.replace(' *', '') || 'Campo requerido';
                
                Toast.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: `Debe completar: ${fieldName}`,
                    background: '#fff3cd',
                    color: '#856404'
                });
                
                // Aplicar estilos de validación
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                
                // Scroll al campo
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                field.focus();
            }, true);

            // Feedback positivo al completar campos
            document.addEventListener('change', function(e) {
                const field = e.target;
                if ((field.tagName.toLowerCase() === 'select' || field.tagName.toLowerCase() === 'input') && field.value.trim()) {
                    const fieldName = field.closest('.mb-3')?.querySelector('label')?.textContent?.replace(' *', '') || 'Campo';
                    
                    field.classList.add('is-valid');
                    field.classList.remove('is-invalid');
                    
                    // Toast de confirmación más discreto
                    const miniToast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: false
                    });
                    
                    miniToast.fire({
                        icon: 'success',
                        title: `${fieldName} ✓`,
                        background: '#d4edda',
                        color: '#155724'
                    });
                }
            });
            
            // ========== INTERCEPTOR ADICIONAL PARA BOTONES ==========
            document.addEventListener('click', function(e) {
                const button = e.target;
                
                // Si es un botón que contiene texto relacionado con inscripción
                if (button.tagName === 'BUTTON' && 
                    (button.textContent.toLowerCase().includes('inscrib') || 
                     button.textContent.toLowerCase().includes('confirm') ||
                     button.id === 'btnConfirmarInscripcion')) {
                    
                    console.log('🎯 Botón de inscripción detectado:', button);
                    
                    // Si es el botón de confirmación del modal, dejarlo pasar
                    if (button.id === 'btnConfirmarInscripcion' && button.closest('#confirmModal')) {
                        return true;
                    }
                    
                    // Para otros botones de inscripción, verificar si tienen validación
                    const form = button.closest('form');
                    if (form && !form.checkValidity()) {
                        e.preventDefault();
                        console.log('⚠️ Formulario inválido, mostrando errores de validación');
                        return false;
                    }
                }
            });
            
            // ========== CAMBIAR SPINNERS EXISTENTES ==========
            setTimeout(() => {
                const spinners = document.querySelectorAll('.spinner-border');
                spinners.forEach(spinner => {
                    spinner.classList.remove('spinner-border');
                    spinner.classList.add('cepre-spinner');
                });
                console.log(`🔄 Cambiados ${spinners.length} spinners a estilo CEPRE`);
            }, 1000);
            
            console.log('✅ Integración SweetAlert2 CEPRE UNAMAD completada exitosamente');
        });
    </script>
@endpush