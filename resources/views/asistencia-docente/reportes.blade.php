@extends('layouts.app')

@section('title', 'Reportes de Asistencia Docente') {{-- Título de la página --}}

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* Variables CSS */
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

    /* Fuentes y Fondo del Cuerpo */
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, var(--bg-light) 0%, #e2e8f0 100%);
        min-height: 100vh;
        color: var(--text-primary);
    }

    /* Contenedor principal de la página */
    .page-container {
        padding-top: 2rem; /* Espaciado superior general */
        padding-bottom: 2rem;
    }

    /* Encabezado Académico Principal */
    .academic-header {
        background: var(--primary-gradient);
        color: white;
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
        border-radius: 0.75rem; /* Bordes redondeados */
        margin-bottom: 2rem; /* Margen inferior */
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

    /* Cuadrícula de Métricas */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: -5rem; /* Ajuste para superponer con el header */
        position: relative;
        z-index: 10;
        padding: 0 15px; /* Padding para evitar que se pegue a los bordes del container-fluid */
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

    /* Panel Principal Mejorado */
    .main-panel {
        background: var(--bg-white);
        border-radius: 1.25rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .panel-header {
        background: linear-gradient(135deg, var(--bg-light) 0%, #f1f5f9 100%);
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

    /* Estilos del Formulario de Filtros */
    .filter-form .form-label {
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }
    .filter-form .form-control, .filter-form .form-select {
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
    }
    .filter-form .form-control:focus, .filter-form .form-select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }
    .filter-form .btn-primary {
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
    }


    /* Tabla Profesional */
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 1rem; /* Bordes redondeados para la tabla completa */
        overflow: hidden; /* Asegura que los bordes redondeados se apliquen al contenido */
        box-shadow: var(--shadow-md); /* Sombra para la tabla */
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
        padding: 1rem 1.5rem; /* Ajuste de padding */
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        background-color: var(--bg-white); /* Fondo para las celdas de datos */
    }

    .data-table tbody tr:hover td { /* Estilo de hover para las celdas individuales */
        background-color: #f6faff; /* Un ligero cambio de color al pasar el ratón */
    }
    
    .data-table tbody tr:last-child td {
        border-bottom: none; /* No borde inferior en la última fila */
    }

    /* Estilos para Agrupación de Tabla (como en Excel) */
    .table-group-header {
        background-color: #e6eef8; /* Docente header */
        font-weight: bold;
        color: var(--text-primary);
    }

    .table-group-month {
        background-color: #f0f7ff; /* Mes header */
        font-weight: bold;
        color: var(--text-primary);
    }

    .table-group-week {
        background-color: #f8faff; /* Semana header */
        font-weight: bold;
        color: var(--text-secondary);
    }
    
    /* Totales de fila */
    .table-total-row td {
        background-color: #e2efda !important; /* Verde claro para totales de semana/mes */
        font-weight: bold;
        color: var(--text-primary);
        border-top: 2px solid #c6e0b4; /* Borde superior más grueso para totales */
    }

    .table-grand-total-row td {
        background: var(--primary-gradient) !important;
        color: white !important;
        font-weight: bold;
        border-top: 3px solid #1e3c72; /* Borde superior más grueso para total general */
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
        flex-shrink: 0;
    }
    .teacher-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 0.75rem;
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

    .day-badge { /* Mantengo los colores para días si se usan en otra parte */
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

    .time-display {
        background: linear-gradient(135deg, var(--bg-light) 0%, #f1f5f9 100%);
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
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); /* Cambiado a un verde sutil */
        color: #166534;
    }

    .action-btn.edit:hover {
        background: var(--success-color);
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
        background-color: var(--bg-white); /* Fondo para el estado vacío */
        border-radius: 1rem;
        box-shadow: var(--shadow-md);
        margin-top: 2rem;
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

    /* Responsive Design */
    @media (max-width: 1024px) {
        .header-content {
            flex-direction: column;
            text-align: center;
        }
        .metrics-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin-top: -3rem; /* Ajuste para tablets */
        }
        .panel-header {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-form .row.g-3 > div { /* Para hacer los filtros apilables en MD */
            flex-grow: 1;
        }
        .filter-form .col-md-2 button {
            margin-top: 1rem !important; /* Ajuste para el botón Generar */
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
            margin-top: -2rem; /* Ajuste para móviles */
        }
        .metric-card {
            padding: 1.5rem;
        }
        .data-table {
            font-size: 0.875rem;
        }
        .data-table thead th,
        .data-table tbody td {
            padding: 0.75rem; /* Menor padding para móviles */
        }
        .teacher-profile {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }
        .action-group {
            flex-direction: row; /* Volver a fila para más espacio */
            flex-wrap: wrap;
            justify-content: center;
        }
        .filter-form .col-md-4, .filter-form .col-md-3, .filter-form .col-md-2 {
            width: 100%; /* Columnas full width en móviles */
        }
        .filter-form .col-md-4.offset-md-4 {
            margin-left: 0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid page-container"> {{-- Contenedor principal con padding --}}
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
                            <li class="breadcrumb-item active">Reportes de Asistencia</li>
                        </ol>
                    </nav>
                    <h1>
                        <i class="fas fa-chart-bar me-3"></i>
                        Reportes de Asistencia Docente
                    </h1>
                    <p class="subtitle">
                        Análisis detallado de la asistencia del cuerpo académico por periodo y ciclo.
                    </p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('asistencia-docente.exportar', [
                        'docente_id' => $selectedDocenteId,
                        'mes' => $selectedMonth,
                        'anio' => $selectedYear,
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'ciclo_academico' => $selectedCicloAcademico
                    ]) }}" class="btn btn-primary-custom me-2">
                        <i class="fas fa-download me-2"></i> Exportar Reporte
                    </a>
                    <a href="{{ route('asistencia-docente.index') }}" class="btn btn-primary-custom">
                        <i class="fas fa-arrow-left me-2"></i> Volver a Registros
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor para métricas y paneles -->
    <div class="container">
        <!-- Panel de Métricas Académicas (Adaptado para reportes) -->
        <div class="metrics-grid">
            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="metric-value">{{ $totalRegistrosPeriodo }}</div>
                <div class="metric-label">
                    <i class="fas fa-chart-line me-1"></i>
                    Registros en el Periodo
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon success">
                        <i class="fas fa-users-alt"></i>
                    </div>
                </div>
                <div class="metric-value">{{ $asistenciaPorDocente->count() }}</div>
                <div class="metric-label">
                    <i class="fas fa-user-check me-1"></i>
                    Docentes con Asistencia
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="metric-value">{{ number_format($asistenciaPorDocente->sum('total_horas'), 1) }}h</div>
                <div class="metric-label">
                    <i class="fas fa-hourglass-start me-1"></i>
                    Horas Totales Dictadas
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon purple">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="metric-value">S/ {{ number_format($asistenciaPorDocente->sum('total_pagos'), 2) }}</div>
                <div class="metric-label">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    Monto Total Estimado
                </div>
            </div>
        </div>

        <!-- Alerta de Éxito (mantener si aplica a esta vista) -->
        @if (session('success'))
            <div class="alert alert-modern alert-success fade-in-up">
                <i class="uil uil-check-circle" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>¡Operación exitosa!</strong>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <!-- Panel Principal de Datos (Filtros) -->
        <div class="main-panel fade-in-up">
            <div class="panel-header">
                <div class="panel-title">
                    <div class="icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <div>
                        <h2>Opciones de Filtrado</h2>
                        <p class="text-muted mb-0">Selecciona los criterios para generar el reporte</p>
                    </div>
                </div>
            </div>
            <div class="panel-body p-4 filter-form"> {{-- Agregada clase filter-form --}}
                <form method="GET" action="{{ route('asistencia-docente.reports') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="docente_id" class="form-label">Docente</label>
                            <select class="form-select" id="docente_id" name="docente_id">
                                <option value="">Todos los Docentes</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id }}" {{ (string)$selectedDocenteId === (string)$docente->id ? 'selected' : '' }}>
                                        {{ $docente->nombre }} {{ $docente->apellido_paterno }} ({{ $docente->numero_documento }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm mt-md-4">
                                <i class="fas fa-search me-1"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end mt-3"> {{-- mt-3 para espaciar filas --}}
                        <div class="col-md-4 offset-md-4">
                            <label for="mes" class="form-label">Mes (si no usa rango)</label>
                            <select class="form-select" id="mes" name="mes" {{ ($fechaInicio || $fechaFin) ? 'disabled' : '' }}>
                                <option value="">Seleccionar Mes</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (string)$selectedMonth === (string)$m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="anio" class="form-label">Año (si no usa rango)</label>
                            <select class="form-select" id="anio" name="anio" {{ ($fechaInicio || $fechaFin) ? 'disabled' : '' }}>
                                <option value="">Seleccionar Año</option>
                                @for ($y = Carbon\Carbon::now()->year; $y >= Carbon\Carbon::now()->year - 5; $y--)
                                    <option value="{{ $y }}" {{ (string)$selectedYear === (string)$y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="ciclo_academico" class="form-label">Ciclo Académico</label>
                            <select class="form-select" id="ciclo_academico" name="ciclo_academico">
                                <option value="">Todos los Ciclos</option>
                                @foreach($ciclosAcademicos as $key => $ciclo)
                                    <option value="{{ $key }}" {{ (string)$selectedCicloAcademico === (string)$key ? 'selected' : '' }}>
                                        {{ $ciclo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Panel de Gráficos y Resumen de Docentes -->
        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="main-panel fade-in-up">
                    <div class="panel-header">
                        <div class="panel-title">
                            <div class="icon" style="background: var(--success-color);">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <h2>Asistencia por Día</h2>
                                <p class="text-muted mb-0">
                                    @if($fechaInicio && $fechaFin)
                                        Periodo: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                                    @elseif(!empty($selectedMonth) && !empty($selectedYear))
                                        {{ \Carbon\Carbon::create((int)$selectedYear, (int)$selectedMonth, 1)->locale('es')->monthName }} {{ $selectedYear }}
                                    @else
                                        Todo el historial
                                    @endif
                                    @if($selectedDocenteId)
                                        para {{ $docentes->firstWhere('id', $selectedDocenteId)->nombre ?? 'N/A' }}
                                    @endif
                                    @if($selectedCicloAcademico)
                                        (Ciclo: {{ $selectedCicloAcademico }})
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body p-4">
                        <canvas id="graficoSemanal" style="max-height: 280px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="main-panel fade-in-up">
                    <div class="panel-header">
                        <div class="panel-title">
                            <div class="icon" style="background: var(--warning-color);">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h2>Resumen de Docentes</h2>
                                <p class="text-muted mb-0">Top 5 y estadísticas de asistencia</p>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body p-4">
                        @forelse($asistenciaPorDocente->sortByDesc('total_asistencias')->take(5) as $index => $docente)
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                <div class="me-3">
                                    <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : ($index == 2 ? 'dark' : 'primary')) }} rounded-pill p-2 fs-6">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark">
                                        {{ $docente->docente ? $docente->docente->nombre . ' ' . $docente->docente->apellido_paterno : 'N/A' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $docente->total_asistencias }} registros
                                        @if(isset($docente->total_horas))
                                            • {{ number_format($docente->total_horas, 1) }}h
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    {{-- Eliminado: @if(isset($docente->total_pagos))
                                        <span class="badge bg-success fs-6">S/ {{ number_format($docente->total_pagos, 2) }}</span>
                                    @endif --}}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p class="mb-0">No hay datos de docentes para el periodo seleccionado.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel de Tabla Detallada por Docente -->
        <div class="col-12 mt-4">
            <div class="main-panel fade-in-up">
                <div class="panel-header">
                    <div class="panel-title">
                        <div class="icon" style="background: var(--accent-color);">
                            <i class="fas fa-table"></i>
                        </div>
                        <div>
                            <h2>Detalle por Docente</h2>
                            <p class="text-muted mb-0">Resumen de registros de asistencia por docente y periodo</p>
                        </div>
                    </div>
                </div>
                <div class="panel-body p-0">
                    @if(count($processedDetailedAsistencias) > 0) {{-- Check if there's any data to display --}}
                        <div class="table-responsive">
                            <table class="data-table"> {{-- Usando la clase data-table definida en el CSS --}}
                                <thead>
                                    <tr>
                                        <th>DOCENTE</th>
                                        <th>MES</th>
                                        <th>SEMANA</th>
                                        <th>FECHA</th>
                                        <th>CURSO</th>
                                        <th>TEMA DESARROLLADO</th>
                                        <th>AULA</th>
                                        <th>TURNO</th>
                                        <th>HORA ENTRADA</th>
                                        <th>HORA SALIDA</th>
                                        <th>HORAS DICTADAS</th>
                                        {{-- Eliminado: <th>PAGO</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $grandTotalHoras = 0;
                                        // $grandTotalPago = 0; // No se necesita si se elimina del display
                                    @endphp

                                    @forelse($processedDetailedAsistencias as $docenteId => $docenteData)
                                        @php
                                            $docentePrinted = false;
                                        @endphp
                                        @foreach($docenteData['months'] as $monthKey => $monthData)
                                            @php
                                                $monthPrinted = false;
                                                // Ordenar semanas para que siempre salgan en orden creciente
                                                ksort($monthData['weeks']); 
                                            @endphp
                                            @foreach($monthData['weeks'] as $weekNumber => $weekData)
                                                @php
                                                    $weekPrinted = false;
                                                @endphp
                                                @foreach($weekData['details'] as $index => $detail)
                                                    <tr>
                                                        {{-- Columna DOCENTE --}}
                                                        @if(!$docentePrinted)
                                                            <td rowspan="{{ $docenteData['rowspan'] }}" class="table-group-header align-middle">
                                                                <div class="teacher-profile justify-content-start">
                                                                    @if($docenteData['docente_info'] && $docenteData['docente_info']->foto_perfil)
                                                                        <img src="{{ asset('storage/' . $docenteData['docente_info']->foto_perfil) }}" class="teacher-avatar" alt="Foto">
                                                                    @else
                                                                        <div class="teacher-avatar">
                                                                            {{ $docenteData['docente_info'] ? strtoupper(substr($docenteData['docente_info']->nombre, 0, 1)) : 'N/A' }}
                                                                        </div>
                                                                    @endif
                                                                    <div class="teacher-info">
                                                                        <h4>{{ $docenteData['docente_info']->nombre . ' ' . $docenteData['docente_info']->apellido_paterno ?? 'N/A' }}</h4>
                                                                        <div class="teacher-id">Doc. {{ $docenteData['docente_info']->numero_documento ?? 'N/A' }}</div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            @php $docentePrinted = true; @endphp
                                                        @endif

                                                        {{-- Columna MES --}}
                                                        @if(!$monthPrinted)
                                                            <td rowspan="{{ $monthData['rowspan'] }}" class="table-group-month align-middle text-center">
                                                                {{ strtoupper($monthData['month_name']) }}
                                                            </td>
                                                            @php $monthPrinted = true; @endphp
                                                        @endif

                                                        {{-- Columna SEMANA --}}
                                                        @if(!$weekPrinted)
                                                            <td rowspan="{{ $weekData['rowspan'] }}" class="table-group-week align-middle text-center">
                                                                SEMANA {{ $weekData['week_number'] }}
                                                            </td>
                                                            @php $weekPrinted = true; @endphp
                                                        @endif

                                                        <td>{{ \Carbon\Carbon::parse($detail['fecha'])->format('d/m/Y') }}</td>
                                                        <td>{{ $detail['curso'] }}</td>
                                                        <td>{{ $detail['tema_desarrollado'] }}</td>
                                                        <td>{{ $detail['aula'] }}</td>
                                                        <td>{{ $detail['turno'] }}</td>
                                                        <td>{{ $detail['hora_entrada'] }}</td>
                                                        <td>{{ $detail['hora_salida'] }}</td>
                                                        <td>{{ number_format($detail['horas_dictadas'], 2) }}</td>
                                                        {{-- Eliminado: <td>S/ {{ number_format($detail['pago'], 2, '.', ',') }}</td> --}}
                                                    </tr>
                                                @endforeach
                                                {{-- Fila de Total de Semana --}}
                                                <tr class="table-total-row">
                                                    <td colspan="8" class="text-end pe-4">TOTAL SEMANA {{ $weekData['week_number'] }}</td>
                                                    <td>{{ number_format($weekData['total_horas'], 2) }}</td>
                                                    {{-- Eliminado: <td>S/ {{ number_format($weekData['total_pagos'], 2, '.', ',') }}</td> --}}
                                                </tr>
                                            @endforeach
                                            {{-- Fila de Total de Mes --}}
                                            <tr class="table-total-row" style="background-color: #dbeee0 !important;">
                                                <td colspan="8" class="text-end pe-4">TOTAL MES {{ strtoupper($monthData['month_name']) }}</td>
                                                <td>{{ number_format($monthData['total_horas'], 2) }}</td>
                                                {{-- Eliminado: <td>S/ {{ number_format($monthData['total_pagos'], 2, '.', ',') }}</td> --}}
                                            </tr>
                                        @endforeach
                                        {{-- Fila de Total de Docente --}}
                                        <tr class="table-total-row" style="background-color: #c6e0b4 !important;">
                                            <td colspan="9" class="text-end pe-4">TOTAL {{ $docenteData['docente_info']->nombre . ' ' . $docenteData['docente_info']->apellido_paterno }}</td>
                                            <td>{{ number_format($docenteData['total_horas'], 2) }}</td>
                                            {{-- Eliminado: <td>S/ {{ number_format($docenteData['total_pagos'], 2, '.', ',') }}</td> --}}
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11"> {{-- Colspan ajustado --}}
                                                <div class="empty-state">
                                                    <div class="empty-icon">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </div>
                                                    <div class="empty-title">
                                                        No hay datos de asistencia
                                                    </div>
                                                    <div class="empty-message">
                                                        No se encontraron registros de asistencia docente para el periodo y/o docente seleccionado.
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    {{-- Fila de Total General --}}
                                    <tr class="table-grand-total-row">
                                        <td colspan="9" class="text-end pe-4">TOTAL GENERAL</td>
                                        <td>{{ number_format(collect($processedDetailedAsistencias)->sum('total_horas'), 2) }}</td>
                                        {{-- Eliminado: <td>S/ {{ number_format(collect($processedDetailedAsistencias)->sum('total_pagos'), 2, '.', ',') }}</td> --}}
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="empty-title">
                                No hay datos de asistencia
                            </div>
                            <div class="empty-message">
                                No se encontraron registros de asistencia docente para el periodo y/o docente seleccionado.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden div to store chart data --}}
<div id="chart-data-container" 
     data-fechas='@json(array_keys($asistenciaSemana))' 
     data-valores='@json(array_values($asistenciaSemana))'
     style="display: none;"></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> {{-- Asegurar Bootstrap JS --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips de Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Logic to disable month/year if start/end dates are used
        const fechaInicioInput = document.getElementById('fecha_inicio');
        const fechaFinInput = document.getElementById('fecha_fin');
        const mesSelect = document.getElementById('mes');
        const anioSelect = document.getElementById('anio');

        function toggleMonthYearDisable() {
            if (fechaInicioInput.value || fechaFinInput.value) {
                mesSelect.disabled = true;
                anioSelect.disabled = true;
            } else {
                mesSelect.disabled = false;
                anioSelect.disabled = false;
            }
        }

        fechaInicioInput.addEventListener('change', toggleMonthYearDisable);
        fechaFinInput.addEventListener('change', toggleMonthYearDisable);

        // Execute on page load for initial state
        toggleMonthYearDisable();


        // Attendance chart by day of the selected month / range
        const ctx = document.getElementById('graficoSemanal').getContext('2d');
        
        // Read data from the hidden div
        const chartDataContainer = document.getElementById('chart-data-container');
        const fechasData = JSON.parse(chartDataContainer.dataset.fechas);
        const valoresData = JSON.parse(chartDataContainer.dataset.valores);
        
        const labelsGrafico = fechasData.map(fecha => {
            const date = new Date(fecha + 'T00:00:00');
            return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        });

        new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: labelsGrafico,
                datasets: [{
                    label: 'Registros de Asistencia',
                    data: valoresData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgb(75, 192, 192)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                return fechasData[index]; 
                            },
                            label: function(context) {
                                return `Registros: ${context.parsed.y}`;
                            }
                        }
                    }
                }
            }
        });

        // Animaciones de entrada escalonadas para métricas
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
                // Manejar valores con formato (S/, h) para la animación
                let formattedValue = '';
                if (element.textContent.includes('S/')) {
                    formattedValue = 'S/ ' + current.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); // Mantener decimales para dinero
                } else if (element.textContent.includes('h')) {
                    formattedValue = current.toLocaleString('es-ES', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + 'h'; // Mantener decimales para horas
                } else {
                    formattedValue = Math.floor(current);
                }
                element.textContent = formattedValue;
            }, 30);
        };

        // Iniciar contadores cuando las tarjetas sean visibles
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const valueElement = entry.target.querySelector('.metric-value');
                    // Obtener el valor numérico, eliminando formatos no numéricos
                    const rawText = valueElement.textContent.replace('S/ ', '').replace('h', '').replace(',', ''); 
                    const targetValue = parseFloat(rawText); 
                    
                    if (!isNaN(targetValue) && targetValue > 0) { // Solo animar si es un número válido y mayor que 0
                        // Guardar el valor original para restaurar después de la animación si es necesario
                        const originalText = valueElement.textContent;
                        animateCounter(valueElement, targetValue);
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 }); // Detectar cuando 50% de la tarjeta es visible

        metricCards.forEach(card => observer.observe(card));
    });
</script>
@endpush
