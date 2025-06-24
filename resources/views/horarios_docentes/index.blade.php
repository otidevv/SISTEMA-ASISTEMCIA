@extends('layouts.app')

@section('title', 'Gestión de Horarios Académicos')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --accent-color: #3b82f6;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --bg-light: #f8fafc;
        --bg-white: #ffffff;
        --border-color: #e5e7eb;
        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
    }

    /* Header Principal Mejorado */
    .academic-header {
        background: var(--primary-gradient);
        color: white;
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
    }

    .academic-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.3;
    }

    .academic-header .container {
        position: relative;
        z-index: 2;
    }

    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .header-info h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        letter-spacing: -0.025em;
    }

    .header-info .subtitle {
        font-size: 1.125rem;
        opacity: 0.9;
        font-weight: 400;
        margin-bottom: 1rem;
    }

    .breadcrumb-custom {
        background: rgba(255, 255, 255, 0.1);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .breadcrumb-custom .breadcrumb-item {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.875rem;
    }

    .breadcrumb-custom .breadcrumb-item.active {
        color: white;
        font-weight: 500;
    }

    .header-actions .btn-primary-custom {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.875rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .header-actions .btn-primary-custom:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    /* Estadísticas Mejoradas */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin: -2rem 0 3rem 0;
        position: relative;
        z-index: 10;
    }

    .metric-card {
        background: var(--bg-white);
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--accent-color);
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
    }

    .metric-card:nth-child(1)::before { background: var(--accent-color); }
    .metric-card:nth-child(2)::before { background: var(--success-color); }
    .metric-card:nth-child(3)::before { background: var(--warning-color); }
    .metric-card:nth-child(4)::before { background: #8b5cf6; }

    .metric-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .metric-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
        color: var(--accent-color);
    }

    .metric-icon.success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
        color: var(--success-color);
    }

    .metric-icon.warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
        color: var(--warning-color);
    }

    .metric-icon.purple {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
        color: #8b5cf6;
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        line-height: 1;
    }

    .metric-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .metric-trend {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-weight: 600;
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }

    /* Card Principal Mejorado */
    .main-panel {
        background: var(--bg-white);
        border-radius: 1.25rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .panel-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 2rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .panel-title h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .panel-title .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        background: var(--accent-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
    }

    /* Buscador Avanzado */
    .search-panel {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .search-container {
        position: relative;
        min-width: 320px;
    }

    .search-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3rem;
        border: 2px solid var(--border-color);
        border-radius: 0.75rem;
        font-size: 0.95rem;
        background: var(--bg-white);
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        font-size: 1.125rem;
    }

    .filter-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .filter-btn {
        padding: 0.875rem 1.25rem;
        border: 1px solid var(--border-color);
        background: var(--bg-white);
        color: var(--text-secondary);
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: var(--accent-color);
        color: white;
        border-color: var(--accent-color);
    }

    /* Tabla Profesional */
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table thead th {
        background: var(--primary-gradient);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: none;
        position: sticky;
        top: 0;
        z-index: 5;
    }

    .data-table thead th:first-child {
        border-top-left-radius: 0.75rem;
    }

    .data-table thead th:last-child {
        border-top-right-radius: 0.75rem;
    }

    .data-table tbody td {
        padding: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        transition: all 0.3s ease;
    }

    .data-table tbody tr {
        transition: all 0.3s ease;
    }

    .data-table tbody tr:hover {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        transform: scale(1.01);
        box-shadow: var(--shadow-md);
    }

    .data-table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 0.75rem;
    }

    .data-table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 0.75rem;
    }

    /* Componentes de la Tabla */
    .teacher-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .teacher-avatar {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.75rem;
        background: var(--secondary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
        box-shadow: var(--shadow-md);
    }

    .teacher-info h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.25rem 0;
    }

    .teacher-info .teacher-id {
        font-size: 0.8rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .day-badge {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: center;
        min-width: 80px;
        display: inline-block;
    }

    .day-badge.lunes { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af; }
    .day-badge.martes { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #166534; }
    .day-badge.miercoles { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; }
    .day-badge.jueves { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #7c2d12; }
    .day-badge.viernes { background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%); color: #be185d; }
    .day-badge.sabado { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0c4a6e; }
    .day-badge.domingo { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #991b1b; }

    .time-display {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 100px;
        justify-content: center;
    }

    .location-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        font-size: 0.9rem;
        font-weight: 500;
    }

    .location-badge .building-icon {
        color: var(--accent-color);
        font-size: 1rem;
    }

    .course-details {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .course-name {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.95rem;
        line-height: 1.3;
    }

    .course-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--text-secondary);
        font-size: 0.8rem;
    }

    .cycle-indicator {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        color: #0369a1;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Botones de Acción Mejorados */
    .action-group {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .action-btn {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .action-btn.edit {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .action-btn.edit:hover {
        background: var(--warning-color);
        color: white;
    }

    .action-btn.delete {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    .action-btn.delete:hover {
        background: var(--danger-color);
        color: white;
    }

    .action-btn.view {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
    }

    .action-btn.view:hover {
        background: var(--accent-color);
        color: white;
    }

    /* Estado Vacío Mejorado */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-secondary);
    }

    .empty-icon {
        font-size: 5rem;
        margin-bottom: 2rem;
        opacity: 0.3;
        background: var(--secondary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .empty-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-primary);
    }

    .empty-message {
        font-size: 1.125rem;
        margin-bottom: 2.5rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }

    .empty-action {
        background: var(--secondary-gradient);
        color: white;
        padding: 1rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-md);
    }

    .empty-action:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        color: white;
    }

    /* Paginación Moderna */
    .pagination-container {
        padding: 2rem;
        border-top: 1px solid var(--border-color);
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .pagination-modern {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .pagination-modern .page-item .page-link {
        border: none;
        color: var(--text-secondary);
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        background: transparent;
    }

    .pagination-modern .page-item .page-link:hover {
        background: var(--accent-color);
        color: white;
        transform: translateY(-1px);
    }

    .pagination-modern .page-item.active .page-link {
        background: var(--accent-color);
        color: white;
        box-shadow: var(--shadow-md);
    }

    /* Alertas Mejoradas */
    .alert-modern {
        border: none;
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: var(--shadow-md);
    }

    .alert-success {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
        border-left: 4px solid var(--success-color);
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .metrics-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin-top: -1rem;
        }

        .panel-header {
            flex-direction: column;
            align-items: stretch;
        }

        .search-panel {
            flex-direction: column;
            align-items: stretch;
        }

        .search-container {
            min-width: 100%;
        }
    }

    @media (max-width: 768px) {
        .academic-header {
            padding: 2rem 0;
        }

        .header-info h1 {
            font-size: 2rem;
        }

        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .metric-card {
            padding: 1.5rem;
        }

        .data-table {
            font-size: 0.875rem;
        }

        .data-table thead th,
        .data-table tbody td {
            padding: 1rem;
        }

        .teacher-profile {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }

        .action-group {
            flex-direction: column;
        }
    }

    /* Animaciones */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Tooltips Personalizados */
    .tooltip-inner {
        background: var(--text-primary);
        border-radius: 0.5rem;
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.5rem 0.75rem;
    }

    .bs-tooltip-top .tooltip-arrow::before {
        border-top-color: var(--text-primary);
    }

    .bs-tooltip-bottom .tooltip-arrow::before {
        border-bottom-color: var(--text-primary);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Académico Principal -->
    <div class="academic-header">
        <div class="container">
            <div class="header-content">
                <div class="header-info">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-custom mb-3">
                            <li class="breadcrumb-item">
                                <i class="uil uil-estate me-1"></i>Centro Preuniversitario
                            </li>
                            <li class="breadcrumb-item">Gestión Académica</li>
                            <li class="breadcrumb-item active">Horarios Docentes</li>
                        </ol>
                    </nav>
                    <h1>
                        <i class="uil uil-schedule me-3"></i>
                        Gestión de Horarios Académicos
                    </h1>
                    <p class="subtitle">
                        Sistema integral de programación y administración de horarios para el cuerpo docente
                    </p>
                </div>
                <div class="header-actions">
    <a href="{{ route('horarios.calendario') }}" class="btn btn-primary-custom me-2">
        <i class="uil uil-calendar-alt me-2"></i>
        Ver Calendario
    </a>
    <a href="{{ route('horarios-docentes.create') }}" class="btn btn-primary-custom">
        <i class="uil uil-plus me-2"></i>
        Programar Nuevo Horario
    </a>
</div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Panel de Métricas Académicas -->
        <div class="metrics-grid">
            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon">
                        <i class="uil uil-calendar-alt"></i>
                    </div>
                    <div class="metric-trend">+12%</div>
                </div>
                <div class="metric-value">{{ $horarios->total() }}</div>
                <div class="metric-label">
                    <i class="uil uil-chart-line me-1"></i>
                    Horarios Programados
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon success">
                        <i class="uil uil-users-alt"></i>
                    </div>
                    <div class="metric-trend">Activos</div>
                </div>
                <div class="metric-value">{{ $horarios->unique('docente_id')->count() }}</div>
                <div class="metric-label">
                    <i class="uil uil-user-check me-1"></i>
                    Docentes Asignados
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon warning">
                        <i class="uil uil-books"></i>
                    </div>
                    <div class="metric-trend">En curso</div>
                </div>
                <div class="metric-value">{{ $horarios->unique('curso_id')->count() }}</div>
                <div class="metric-label">
                    <i class="uil uil-graduation-cap me-1"></i>
                    Cursos Programados
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon purple">
                        <i class="uil uil-building"></i>
                    </div>
                    <div class="metric-trend">100%</div>
                </div>
                <div class="metric-value">{{ $horarios->unique('aula_id')->count() }}</div>
                <div class="metric-label">
                    <i class="uil uil-map-marker me-1"></i>
                    Aulas Utilizadas
                </div>
            </div>
        </div>

        <!-- Alerta de Éxito -->
        @if (session('success'))
            <div class="alert alert-modern alert-success">
                <i class="uil uil-check-circle" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>¡Operación exitosa!</strong>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <!-- Panel Principal de Datos -->
        <div class="main-panel fade-in-up">
            <!-- Header del Panel -->
            <div class="panel-header">
                <div class="panel-title">
                    <div class="icon">
                        <i class="uil uil-list-ul"></i>
                    </div>
                    <div>
                        <h2>Registro de Horarios Académicos</h2>
                        <p class="text-muted mb-0">Gestión completa de la programación docente</p>
                    </div>
                </div>

                <!-- Panel de Búsqueda y Filtros -->
                <div class="search-panel">
                    <div class="search-container">
                        <i class="uil uil-search search-icon"></i>
                        <input type="text" 
                               class="search-input" 
                               placeholder="Buscar por docente, curso, aula..." 
                               id="searchInput">
                    </div>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">
                            <i class="uil uil-apps me-1"></i>Todos
                        </button>
                        <button class="filter-btn" data-filter="lunes">
                            <i class="uil uil-calendar-alt me-1"></i>Lunes
                        </button>
                        <button class="filter-btn" data-filter="martes">
                            <i class="uil uil-calendar-alt me-1"></i>Martes
                        </button>
                        <button class="filter-btn" data-filter="miercoles">
                            <i class="uil uil-calendar-alt me-1"></i>Miércoles
                        </button>
                    </div>
                </div>
            </div>

            <!-- Contenido de la Tabla -->
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="data-table" id="scheduleTable">
                        <thead>
                            <tr>
                                <th>
                                    <i class="uil uil-user me-2"></i>
                                    Docente
                                </th>
                                <th>
                                    <i class="uil uil-calendar-alt me-2"></i>
                                    Día Académico
                                </th>
                                <th>
                                    <i class="uil uil-clock-three me-2"></i>
                                    Hora Inicio
                                </th>
                                <th>
                                    <i class="uil uil-clock-nine me-2"></i>
                                    Hora Fin
                                </th>
                                <th>
                                    <i class="uil uil-building me-2"></i>
                                    Aula
                                </th>
                                <th>
                                    <i class="uil uil-book-open me-2"></i>
                                    Curso Académico
                                </th>
                                <th>
                                    <i class="uil uil-layer-group me-2"></i>
                                    Ciclo
                                </th>
                                <th class="text-center">
                                    <i class="uil uil-setting me-2"></i>
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($horarios as $horario)
                                <tr data-day="{{ strtolower($horario->dia_semana) }}" class="schedule-row">
                                    <td>
                                        <div class="teacher-profile">
                                            <div class="teacher-avatar">
                                                {{ substr($horario->docente->nombre_completo ?? 'N/A', 0, 1) }}
                                            </div>
                                            <div class="teacher-info">
                                                <h4>{{ $horario->docente->nombre_completo ?? 'Sin asignar' }}</h4>
                                                <div class="teacher-id">ID: {{ $horario->docente->id ?? '---' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="day-badge {{ strtolower($horario->dia_semana) }}">
                                            {{ ucfirst($horario->dia_semana) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="time-display">
                                            <i class="uil uil-clock-three"></i>
                                            {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="time-display">
                                            <i class="uil uil-clock-nine"></i>
                                            {{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="location-badge">
                                            <i class="uil uil-map-marker building-icon"></i>
                                            <span>{{ $horario->aula->nombre ?? 'Sin asignar' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="course-details">
                                            <div class="course-name">
                                                {{ $horario->curso->nombre ?? 'Curso no asignado' }}
                                            </div>
                                            <div class="course-meta">
                                                <span class="cycle-indicator">
                                                    {{ $horario->ciclo->nombre ?? 'Sin ciclo' }}
                                                </span>
                                                <span>•</span>
                                                <span>
                                                    <i class="uil uil-users-alt me-1"></i>
                                                    Preuniversitario
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="cycle-indicator">
                                            <i class="uil uil-layer-group me-1"></i>
                                            {{ $horario->ciclo->nombre ?? 'Sin definir' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <button class="action-btn view" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Ver detalles del horario">
                                                <i class="uil uil-eye"></i>
                                            </button>
                                            <a href="{{ route('horarios-docentes.edit', $horario->id) }}" 
                                               class="action-btn edit" 
                                               data-bs-toggle="tooltip" 
                                               title="Editar horario académico">
                                                <i class="uil uil-edit"></i>
                                            </a>
                                            <form action="{{ route('horarios-docentes.destroy', $horario->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirmDeletion(event)">
                                                @csrf @method('DELETE')
                                                <button type="submit" 
                                                        class="action-btn delete" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Eliminar horario">
                                                    <i class="uil uil-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="uil uil-calendar-slash"></i>
                                            </div>
                                            <div class="empty-title">
                                                No hay horarios programados
                                            </div>
                                            <div class="empty-message">
                                                El sistema de horarios está listo para comenzar. Programa el primer horario académico 
                                                para organizar las clases del centro preuniversitario.
                                            </div>
                                            <a href="{{ route('horarios-docentes.create') }}" class="empty-action">
                                                <i class="uil uil-plus me-2"></i>
                                                Programar Primer Horario
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($horarios->hasPages())
                    <div class="pagination-container">
                        <nav class="pagination-modern">
                            {{ $horarios->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>

        <!-- Panel de Estadísticas Adicionales -->
        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="main-panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <div class="icon" style="background: var(--success-color);">
                                <i class="uil uil-chart-bar"></i>
                            </div>
                            <div>
                                <h2>Distribución por Días</h2>
                                <p class="text-muted mb-0">Análisis de carga académica semanal</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            @php
                                $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                                $colores = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
                            @endphp
                            @foreach($diasSemana as $index => $dia)
                                @php
                                    $cantidadDia = $horarios->where('dia_semana', $dia)->count();
                                    $porcentaje = $horarios->count() > 0 ? ($cantidadDia / $horarios->count()) * 100 : 0;
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="day-stat-card" style="border-left: 4px solid {{ $colores[$index] }};">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold text-capitalize">{{ $dia }}</span>
                                            <span class="badge" style="background: {{ $colores[$index] }}20; color: {{ $colores[$index] }};">
                                                {{ $cantidadDia }} horarios
                                            </span>
                                        </div>
                                        <div class="progress" style="height: 6px; background: #f1f5f9;">
                                            <div class="progress-bar" 
                                                 style="width: {{ $porcentaje }}%; background: {{ $colores[$index] }};"></div>
                                        </div>
                                        <small class="text-muted">{{ number_format($porcentaje, 1) }}% del total</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="main-panel">
                    <div class="panel-header">
                        <div class="panel-title">
                            <div class="icon" style="background: var(--warning-color);">
                                <i class="uil uil-info-circle"></i>
                            </div>
                            <div>
                                <h2>Información del Sistema</h2>
                                <p class="text-muted mb-0">Estado actual del sistema</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="system-info">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="uil uil-database" style="color: var(--success-color);"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Base de Datos</div>
                                    <div class="info-value">Operativa</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="uil uil-sync" style="color: var(--accent-color);"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Última Actualización</div>
                                    <div class="info-value">{{ now()->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="uil uil-shield-check" style="color: var(--success-color);"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Sistema</div>
                                    <div class="info-value">Seguro y Estable</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de Funcionalidad -->
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips con configuración mejorada
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 500, hide: 100 },
            animation: true
        });
    });

    // Sistema de búsqueda avanzada
    const searchInput = document.getElementById('searchInput');
    const scheduleTable = document.getElementById('scheduleTable');
    const scheduleRows = scheduleTable.querySelectorAll('.schedule-row');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        scheduleRows.forEach(row => {
            const searchableText = [
                row.querySelector('.teacher-info h4')?.textContent || '',
                row.querySelector('.course-name')?.textContent || '',
                row.querySelector('.location-badge span')?.textContent || '',
                row.querySelector('.day-badge')?.textContent || '',
                row.querySelector('.cycle-indicator')?.textContent || ''
            ].join(' ').toLowerCase();
            
            const shouldShow = searchableText.includes(searchTerm);
            row.style.display = shouldShow ? '' : 'none';
            
            // Animación suave
            if (shouldShow) {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 50);
            }
        });

        // Mostrar mensaje si no hay resultados
        updateEmptyState();
    });

    // Sistema de filtros por día
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Actualizar botones activos
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            
            scheduleRows.forEach(row => {
                const dayValue = row.getAttribute('data-day');
                const shouldShow = filterValue === 'all' || dayValue === filterValue;
                row.style.display = shouldShow ? '' : 'none';
            });

            // Limpiar búsqueda cuando se aplica filtro
            searchInput.value = '';
            updateEmptyState();
        });
    });

    // Función para mostrar/ocultar estado vacío
    function updateEmptyState() {
        const visibleRows = Array.from(scheduleRows).filter(row => 
            row.style.display !== 'none'
        );
        
        const emptyRow = scheduleTable.querySelector('.empty-state')?.closest('tr');
        if (emptyRow) {
            emptyRow.style.display = visibleRows.length === 0 ? '' : 'none';
        }
    }

    // Confirmación elegante para eliminar
    window.confirmDeletion = function(event) {
        event.preventDefault();
        
        const result = confirm(
            '⚠️ ¿Estás seguro de eliminar este horario?\n\n' +
            'Esta acción no se puede deshacer y afectará la programación académica.'
        );
        
        if (result) {
            // Agregar animación antes de enviar
            const form = event.target.closest('form');
            const row = form.closest('tr');
            
            row.style.transition = 'all 0.5s ease';
            row.style.opacity = '0.5';
            row.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                form.submit();
            }, 300);
        }
        
        return false;
    };

    // Animaciones de entrada escalonadas
    const metricCards = document.querySelectorAll('.metric-card');
    metricCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });

    // Efecto de hover mejorado para filas de tabla
    scheduleRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.1)';
            this.style.borderRadius = '0.75rem';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
            this.style.borderRadius = '';
        });
    });

    // Contador animado para las métricas
    const animateCounter = (element, target) => {
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 30);
    };

    // Iniciar contadores cuando las tarjetas sean visibles
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const valueElement = entry.target.querySelector('.metric-value');
                const targetValue = parseInt(valueElement.textContent);
                animateCounter(valueElement, targetValue);
                observer.unobserve(entry.target);
            }
        });
    });

    metricCards.forEach(card => observer.observe(card));

    // Actualización periódica de la hora
    function updateTime() {
        const timeElements = document.querySelectorAll('.info-value');
        timeElements.forEach(element => {
            if (element.textContent.includes(':')) {
                element.textContent = new Date().toLocaleString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        });
    }

    // Actualizar cada minuto
    setInterval(updateTime, 60000);
});

// Función para exportar datos (opcional)
function exportSchedules() {
    // Implementar exportación de horarios
    console.log('Exportando horarios...');
}

// Función para imprimir horarios (opcional)
function printSchedules() {
    window.print();
}
</script>

<style>
/* Estilos adicionales para los nuevos componentes */
.day-stat-card {
    background: var(--bg-white);
    padding: 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.day-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.system-info {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 0.75rem;
    border: 1px solid var(--border-color);
}

.info-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    background: var(--bg-white);
    border-radius: 0.5rem;
    box-shadow: var(--shadow-sm);
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 0.8rem;
    color: var(--text-secondary);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 0.95rem;
    color: var(--text-primary);
    font-weight: 600;
}
</style>
@endpush
@endsection