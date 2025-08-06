@extends('layouts.app')

@section('title', 'Gestión de Horarios Docentes')

@push('css')
<style>
    .search-container {
        position: relative;
    }

    .search-input {
        padding-right: 40px;
    }

    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }

    .suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.25rem 0.25rem;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1050;
    }

    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f1f3f4;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-item:hover,
    .suggestion-item.active {
        background-color: #f8f9fa;
    }

    .suggestion-item .suggestion-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .suggestion-item .text-primary {
        font-weight: 600;
    }

    .no-results {
        padding: 15px;
        text-align: center;
        color: #6c757d;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .quick-action-btn {
        padding: 1.25rem;
        border: 2px dashed #d1d5db;
        border-radius: 0.75rem;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
    }

    .quick-action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .quick-action-btn:hover::before {
        left: 100%;
    }

    .quick-action-btn:hover {
        border-color: #4f46e5;
        background: #f8fafc;
        color: inherit;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
    }

    .quick-action-btn.active {
        border-color: #4f46e5;
        background: #eef2ff;
        color: #4f46e5;
    }

    .quick-action-icon {
        margin-bottom: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .day-filter-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.875rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin: 0.25rem;
        gap: 0.375rem;
    }

    .day-filter-badge.active {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .day-filter-badge:not(.active) {
        background-color: #f3f4f6;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    .day-filter-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .horario-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .horario-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .horario-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    }

    .stat-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
        position: relative;
    }

    .stat-icon::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border-radius: 50%;
        background: linear-gradient(135deg, transparent, rgba(255,255,255,0.3));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stat-card:hover .stat-icon::after {
        opacity: 1;
    }

    .stat-icon.primary {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1d4ed8;
    }

    .stat-icon.success {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #16a34a;
    }

    .stat-icon.warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
    }

    .stat-icon.info {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        color: #0284c7;
    }

    .stat-value {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
        line-height: 1;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
    }

    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
        background: #f8fafb;
        border-radius: 0.5rem;
        padding: 1rem;
    }

    .activity-item {
        padding: 1rem;
        background: white;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .activity-item.created {
        border-left-color: #10b981;
    }

    .activity-item.updated {
        border-left-color: #f59e0b;
    }

    .activity-item.deleted {
        border-left-color: #ef4444;
    }

    .activity-item:last-child {
        margin-bottom: 0;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .activity-icon.created {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .activity-icon.updated {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .activity-icon.deleted {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    /* Vista Calendario */
    .calendario-container {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .calendario-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .calendario-nav {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .nav-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .nav-btn:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }

    .calendario-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: #f8fafb;
    }

    .dia-header {
        padding: 1rem;
        text-align: center;
        font-weight: 600;
        color: #374151;
        background: #f3f4f6;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .dia-column {
        min-height: 150px;
        padding: 0.75rem;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        background: white;
    }

    .dia-column:last-child {
        border-right: none;
    }

    .horario-bloque {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 1px solid #93c5fd;
        border-radius: 0.375rem;
        padding: 0.5rem;
        margin-bottom: 0.375rem;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .horario-bloque::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #3b82f6;
    }

    .horario-bloque:hover {
        background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .horario-time {
        font-weight: 600;
        color: #1e40af;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .horario-details {
        margin-top: 0.25rem;
        color: #374151;
    }

    .empty-day {
        text-align: center;
        color: #9ca3af;
        font-style: italic;
        padding: 2rem 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .toggle-view-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .toggle-view-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .view-lista {
        display: block;
    }

    .view-calendario {
        display: none;
    }

    .docente-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        flex-shrink: 0;
    }

    .curso-badge {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        color: #0284c7;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    .aula-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .time-badge {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    @media (max-width: 768px) {
        .quick-actions {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .calendario-grid {
            grid-template-columns: 1fr;
        }
        
        .dia-column {
            min-height: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('horarios-docentes.index') }}">
                                <i class="fas fa-calendar-alt me-1"></i>Horarios
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            <i class="fas fa-cogs me-1"></i>Gestión
                        </li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-calendar-check me-2 text-primary"></i>
                    Gestión de Horarios Docentes
                </h4>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-value" id="totalHorarios">{{ $horarios->total() }}</div>
            <div class="stat-label">
                <i class="fas fa-chart-line me-1"></i>
                Total Horarios
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-value" id="docentesActivos">{{ $horarios->unique('docente_id')->count() }}</div>
            <div class="stat-label">
                <i class="fas fa-user-check me-1"></i>
                Docentes Activos
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-value" id="cursosProgram">{{ $horarios->unique('curso_id')->count() }}</div>
            <div class="stat-label">
                <i class="fas fa-graduation-cap me-1"></i>
                Cursos Programados
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="stat-value" id="aulasUso">{{ $horarios->unique('aula_id')->count() }}</div>
            <div class="stat-label">
                <i class="fas fa-building me-1"></i>
                Aulas en Uso
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-ul me-2"></i>
                        Horarios Programados
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Acciones Rápidas -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-bolt me-1"></i>
                            Acciones Rápidas
                        </h6>
                        <div class="quick-actions">
                            <a href="{{ route('horarios-docentes.create') }}" class="quick-action-btn">
                                <div class="quick-action-icon">
                                    <i class="fas fa-plus-circle text-primary fs-3"></i>
                                </div>
                                <div class="fw-bold mt-2">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    Nuevo Horario
                                </div>
                                <small class="text-muted">Programar nueva clase</small>
                            </a>
                            <div class="quick-action-btn" onclick="toggleCalendarioView()">
                                <div class="quick-action-icon">
                                    <i class="fas fa-calendar-week text-success fs-3"></i>
                                </div>
                                <div class="fw-bold mt-2">
                                    <i class="fas fa-eye me-1"></i>
                                    Vista Calendario
                                </div>
                                <small class="text-muted">Ver programación semanal</small>
                            </div>
                            <div class="quick-action-btn" onclick="exportarHorarios()">
                                <div class="quick-action-icon">
                                    <i class="fas fa-file-export text-info fs-3"></i>
                                </div>
                                <div class="fw-bold mt-2">
                                    <i class="fas fa-download me-1"></i>
                                    Exportar Excel
                                </div>
                                <small class="text-muted">Descargar horarios</small>
                            </div>
                            <div class="quick-action-btn" onclick="generarReporte()">
                                <div class="quick-action-icon">
                                    <i class="fas fa-chart-pie text-warning fs-3"></i>
                                </div>
                                <div class="fw-bold mt-2">
                                    <i class="fas fa-analytics me-1"></i>
                                    Reportes
                                </div>
                                <small class="text-muted">Estadísticas avanzadas</small>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle de Vista -->
                    <button class="toggle-view-btn" id="toggleViewBtn" onclick="toggleCalendarioView()">
                        <i class="fas fa-calendar-alt" id="toggleIcon"></i>
                        <span id="toggleText">Ver Calendario</span>
                    </button>

                    <!-- Búsqueda y Filtros -->
                    <div class="mb-4 view-lista" id="searchFilters">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="search-container">
                                    <input type="text" 
                                           class="form-control search-input" 
                                           id="horario_search" 
                                           placeholder="🔍 Buscar por docente, curso, aula..."
                                           autocomplete="off">
                                    <i class="fas fa-search search-icon"></i>
                                    <div class="suggestions-dropdown" id="suggestions"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="filtroTipo">
                                    <option value="">
                                        <i class="fas fa-filter"></i> Todos los tipos
                                    </option>
                                    <option value="teoria">📚 Teoría</option>
                                    <option value="practica">⚡ Práctica</option>
                                    <option value="laboratorio">🧪 Laboratorio</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros por día -->
                    <div class="mb-4 view-lista" id="dayFilters">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-calendar-day me-1"></i>
                            Filtrar por día:
                        </h6>
                        <div class="d-flex flex-wrap">
                            <span class="day-filter-badge active" data-day="todos">
                                <i class="fas fa-calendar"></i> Todos
                            </span>
                            <span class="day-filter-badge" data-day="lunes">
                                <i class="fas fa-calendar-day"></i> Lunes
                            </span>
                            <span class="day-filter-badge" data-day="martes">
                                <i class="fas fa-calendar-day"></i> Martes
                            </span>
                            <span class="day-filter-badge" data-day="miercoles">
                                <i class="fas fa-calendar-day"></i> Miércoles
                            </span>
                            <span class="day-filter-badge" data-day="jueves">
                                <i class="fas fa-calendar-day"></i> Jueves
                            </span>
                            <span class="day-filter-badge" data-day="viernes">
                                <i class="fas fa-calendar-day"></i> Viernes
                            </span>
                            <span class="day-filter-badge" data-day="sabado">
                                <i class="fas fa-calendar-day"></i> Sábado
                            </span>
                        </div>
                    </div>

                    <!-- Vista Lista -->
                    <div id="horariosList" class="view-lista">
                        @forelse($horarios as $horario)
                            <div class="horario-card" data-day="{{ strtolower($horario->dia_semana) }}" 
                                 data-docente="{{ $horario->docente->nombre_completo ?? '' }}" 
                                 data-curso="{{ $horario->curso->nombre ?? '' }}" 
                                 data-aula="{{ $horario->aula->nombre ?? '' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="docente-avatar">
                                                {{ substr($horario->docente->nombre_completo ?? 'N', 0, 1) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-1">
                                                    <i class="fas fa-user-tie me-1 text-primary"></i>
                                                    {{ $horario->docente->nombre_completo ?? 'Sin asignar' }}
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    {{ $horario->docente->email ?? 'Sin email' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="day-filter-badge" data-day="{{ strtolower($horario->dia_semana) }}">
                                            <i class="fas fa-calendar-day"></i>
                                            {{ ucfirst($horario->dia_semana) }}
                                        </span>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="time-badge">
                                            <i class="fas fa-clock"></i>
                                            {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="aula-info">
                                            <i class="fas fa-door-open text-info"></i>
                                            <strong>{{ $horario->aula->nombre ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="curso-badge">
                                            <i class="fas fa-book"></i>
                                            {{ Str::limit($horario->curso->nombre ?? 'N/A', 15) }}
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="verDetalles({{ $horario->id }})">
                                                    <i class="fas fa-eye me-2 text-info"></i>Ver Detalles
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('horarios-docentes.edit', $horario->id) }}">
                                                    <i class="fas fa-edit me-2 text-warning"></i>Editar
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="duplicarHorario({{ $horario->id }})">
                                                    <i class="fas fa-copy me-2 text-success"></i>Duplicar
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="eliminarHorario({{ $horario->id }})">
                                                    <i class="fas fa-trash me-2"></i>Eliminar
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5" id="emptyState">
                                <i class="fas fa-calendar-times fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No hay horarios programados
                                </h5>
                                <p class="text-muted">Comienza creando tu primer horario académico</p>
                                <a href="{{ route('horarios-docentes.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Crear Primer Horario
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Vista Calendario -->
                    <div id="calendarioView" class="view-calendario">
                        <div class="calendario-container">
                            <div class="calendario-header">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fas fa-calendar-week fs-4"></i>
                                    <h5 class="mb-0">Programación Semanal</h5>
                                </div>
                                <div class="calendario-nav">
                                    <button class="nav-btn" onclick="previousWeek()">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <span id="semanaActual" class="fw-bold">
                                        <i class="fas fa-calendar me-2"></i>
                                        Semana Actual
                                    </span>
                                    <button class="nav-btn" onclick="nextWeek()">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="calendario-grid">
                                @php
                                    $dias = [
                                        'lunes' => 'Lunes',
                                        'martes' => 'Martes', 
                                        'miercoles' => 'Miércoles',
                                        'jueves' => 'Jueves',
                                        'viernes' => 'Viernes',
                                        'sabado' => 'Sábado',
                                        'domingo' => 'Domingo'
                                    ];
                                    $iconosDias = [
                                        'lunes' => 'fa-moon',
                                        'martes' => 'fa-sun',
                                        'miercoles' => 'fa-cloud-sun',
                                        'jueves' => 'fa-star',
                                        'viernes' => 'fa-heart',
                                        'sabado' => 'fa-gem',
                                        'domingo' => 'fa-home'
                                    ];
                                @endphp
                                
                                @foreach($dias as $diaKey => $diaNombre)
                                    <div class="dia-header">
                                        <i class="fas {{ $iconosDias[$diaKey] }} me-2"></i>
                                        {{ $diaNombre }}
                                    </div>
                                @endforeach
                                
                                @foreach($dias as $diaKey => $diaNombre)
                                    <div class="dia-column">
                                        @php
                                            $horariosDia = $horarios->where('dia_semana', $diaKey)->sortBy('hora_inicio');
                                        @endphp
                                        @forelse($horariosDia as $horario)
                                            <div class="horario-bloque" 
                                                 title="🎓 {{ $horario->docente->nombre_completo ?? 'N/A' }} - 📚 {{ $horario->curso->nombre ?? 'N/A' }}"
                                                 onclick="verDetalles({{ $horario->id }})">
                                                <div class="horario-time">
                                                    <i class="fas fa-clock"></i>
                                                    {{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}
                                                </div>
                                                <div class="horario-details">
                                                    <div class="fw-bold">
                                                        <i class="fas fa-user-tie"></i>
                                                        {{ Str::limit($horario->docente->nombre_completo ?? 'N/A', 12) }}
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-book"></i>
                                                        {{ Str::limit($horario->curso->nombre ?? 'N/A', 10) }}
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-door-open"></i>
                                                        {{ $horario->aula->nombre ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="empty-day">
                                                <i class="fas fa-calendar-times fs-4 mb-2"></i>
                                                <small>Sin horarios programados</small>
                                            </div>
                                        @endforelse
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Paginación -->
                    @if($horarios->hasPages())
                        <div class="d-flex justify-content-center mt-4 view-lista">
                            {{ $horarios->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actividad Reciente -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-1 text-primary"></i>
                        Actividad Reciente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="recent-activity" id="recentActivity">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-clock fs-3"></i>
                            <p class="mb-0 mt-2">Cargando actividad...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen por Docente -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-user-graduate me-1 text-success"></i>
                        Top Docentes Activos
                    </h6>
                </div>
                <div class="card-body">
                    <div id="resumenDocentes">
                        @php
                            $docentesConHorarios = $horarios->groupBy('docente_id');
                        @endphp
                        @foreach($docentesConHorarios->take(5) as $docenteId => $horariosDocente)
                            @php
                                $docente = $horariosDocente->first()->docente;
                                $totalHoras = $horariosDocente->sum(function($h) {
                                    return \Carbon\Carbon::parse($h->hora_fin)->diffInHours(\Carbon\Carbon::parse($h->hora_inicio));
                                });
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="docente-avatar" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                        {{ substr($docente->nombre_completo ?? 'N', 0, 1) }}
                                    </div>
                                    <div>
                                        <strong class="d-block">{{ Str::limit($docente->nombre_completo ?? 'Sin nombre', 15) }}</strong>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $totalHoras }}h/semana
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary">{{ $horariosDocente->count() }}</span>
                                    <small class="text-muted d-block">horarios</small>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($docentesConHorarios->count() > 5)
                            <div class="text-center mt-3">
                                <button class="btn btn-sm btn-outline-primary" onclick="verTodosDocentes()">
                                    <i class="fas fa-users me-1"></i>
                                    Ver todos ({{ $docentesConHorarios->count() }} docentes)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Detalles del Horario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detallesContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Variables globales
    let vistaActual = 'lista';
    const horariosData = @json($horarios->toArray());
    
    const searchInput = document.getElementById('horario_search');
    const suggestionsContainer = document.getElementById('suggestions');
    const horarioCards = document.querySelectorAll('.horario-card');
    
    let currentFocus = -1;
    let filteredHorarios = [];

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        cargarActividadReciente();
        inicializarFiltros();
        animarContadores();
        inicializarBusqueda();
        actualizarSemanaActual();
    });

    // Sistema de búsqueda mejorado
    function inicializarBusqueda() {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            mostrarSugerencias(searchTerm);
            filtrarHorarios(searchTerm);
        });

        searchInput.addEventListener('keydown', function(e) {
            const items = suggestionsContainer.querySelectorAll('.suggestion-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocus++;
                if (currentFocus >= items.length) currentFocus = 0;
                setActive(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocus--;
                if (currentFocus < 0) currentFocus = items.length - 1;
                setActive(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentFocus > -1 && filteredHorarios[currentFocus]) {
                    seleccionarSugerencia(currentFocus);
                }
            } else if (e.key === 'Escape') {
                suggestionsContainer.style.display = 'none';
                currentFocus = -1;
            }
        });
    }

    function mostrarSugerencias(searchTerm) {
        if (!searchTerm) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        // Buscar en los datos reales
        filteredHorarios = [];
        
        if (horariosData.data) {
            filteredHorarios = horariosData.data.filter(horario => {
                const docenteNombre = horario.docente ? horario.docente.nombre_completo || 'Sin asignar' : 'Sin asignar';
                const cursoNombre = horario.curso ? horario.curso.nombre || 'Sin curso' : 'Sin curso';
                const aulaNombre = horario.aula ? horario.aula.nombre || 'Sin aula' : 'Sin aula';
                const diaName = horario.dia_semana || '';
                
                return docenteNombre.toLowerCase().includes(searchTerm) ||
                       cursoNombre.toLowerCase().includes(searchTerm) ||
                       aulaNombre.toLowerCase().includes(searchTerm) ||
                       diaName.toLowerCase().includes(searchTerm);
            });
        }

        if (filteredHorarios.length === 0) {
            suggestionsContainer.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search-minus me-2"></i>
                    No se encontraron resultados
                </div>`;
            suggestionsContainer.style.display = 'block';
            return;
        }

        let html = '';
        filteredHorarios.slice(0, 5).forEach((horario, index) => {
            const docenteNombre = horario.docente ? horario.docente.nombre_completo || 'Sin asignar' : 'Sin asignar';
            const cursoNombre = horario.curso ? horario.curso.nombre || 'Sin curso' : 'Sin curso';
            const aulaNombre = horario.aula ? horario.aula.nombre || 'Sin aula' : 'Sin aula';
            const diaName = horario.dia_semana || '';
            
            html += `
                <div class="suggestion-item" data-index="${index}">
                    <div class="suggestion-icon">
                        ${docenteNombre.charAt(0)}
                    </div>
                    <div class="flex-grow-1">
                        <div><span class="text-primary">${highlightMatch(docenteNombre, searchTerm)}</span> - ${highlightMatch(cursoNombre, searchTerm)}</div>
                        <div><small class="text-muted">
                            <i class="fas fa-calendar-day me-1"></i>${diaName} | 
                            <i class="fas fa-door-open me-1"></i>${aulaNombre} | 
                            <i class="fas fa-clock me-1"></i>${horario.hora_inicio} - ${horario.hora_fin}
                        </small></div>
                    </div>
                </div>
            `;
        });

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';

        // Agregar eventos click
        document.querySelectorAll('.suggestion-item').forEach((item, index) => {
            item.addEventListener('click', function() {
                seleccionarSugerencia(index);
            });
        });
    }

    function highlightMatch(text, searchTerm) {
        if (!searchTerm) return text;
        const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    function seleccionarSugerencia(index) {
        const horario = filteredHorarios[index];
        const docenteNombre = horario.docente ? horario.docente.nombre_completo || 'Sin asignar' : 'Sin asignar';
        const cursoNombre = horario.curso ? horario.curso.nombre || 'Sin curso' : 'Sin curso';
        searchInput.value = docenteNombre + ' - ' + cursoNombre;
        suggestionsContainer.style.display = 'none';
        filtrarHorarios(searchInput.value.toLowerCase());
    }

    function setActive(items) {
        items.forEach(item => item.classList.remove('active'));
        if (currentFocus >= 0 && currentFocus < items.length) {
            items[currentFocus].classList.add('active');
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }
    }

    function filtrarHorarios(searchTerm) {
        const diaActivo = document.querySelector('.day-filter-badge.active').dataset.day;
        let visibleCount = 0;
        
        horarioCards.forEach(card => {
            const docente = card.dataset.docente.toLowerCase();
            const curso = card.dataset.curso.toLowerCase();
            const aula = card.dataset.aula.toLowerCase();
            const dia = card.dataset.day;
            
            const matchesSearch = !searchTerm || 
                docente.includes(searchTerm) || 
                curso.includes(searchTerm) || 
                aula.includes(searchTerm);
            
            const matchesDay = diaActivo === 'todos' || dia === diaActivo;
            
            if (matchesSearch && matchesDay) {
                card.style.display = 'block';
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50 * visibleCount);
                
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        verificarEstadoVacio();
    }

    // Filtros por día
    function inicializarFiltros() {
        const dayFilters = document.querySelectorAll('.day-filter-badge');
        
        dayFilters.forEach(filter => {
            filter.addEventListener('click', function() {
                dayFilters.forEach(f => f.classList.remove('active'));
                this.classList.add('active');
                filtrarHorarios(searchInput.value.toLowerCase());
            });
        });
    }

    function verificarEstadoVacio() {
        const visibleCards = Array.from(horarioCards).filter(card => 
            card.style.display !== 'none'
        );
        
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            emptyState.style.display = visibleCards.length === 0 ? 'block' : 'none';
        }
    }

    // Toggle entre vista lista y calendario
    function toggleCalendarioView() {
        const listaView = document.querySelector('.view-lista');
        const calendarioView = document.querySelector('.view-calendario');
        const toggleBtn = document.getElementById('toggleViewBtn');
        const toggleIcon = document.getElementById('toggleIcon');
        const toggleText = document.getElementById('toggleText');
        const searchFilters = document.getElementById('searchFilters');
        const dayFilters = document.getElementById('dayFilters');

        if (vistaActual === 'lista') {
            // Cambiar a vista calendario
            listaView.style.display = 'none';
            calendarioView.style.display = 'block';
            searchFilters.style.display = 'none';
            dayFilters.style.display = 'none';
            
            toggleBtn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
            toggleIcon.className = 'fas fa-list';
            toggleText.textContent = 'Ver Lista';
            
            vistaActual = 'calendario';
            
            // Animación de entrada
            calendarioView.style.opacity = '0';
            calendarioView.style.transform = 'translateY(20px)';
            setTimeout(() => {
                calendarioView.style.transition = 'all 0.5s ease';
                calendarioView.style.opacity = '1';
                calendarioView.style.transform = 'translateY(0)';
            }, 100);
            
        } else {
            // Cambiar a vista lista
            listaView.style.display = 'block';
            calendarioView.style.display = 'none';
            searchFilters.style.display = 'block';
            dayFilters.style.display = 'block';
            
            toggleBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            toggleIcon.className = 'fas fa-calendar-alt';
            toggleText.textContent = 'Ver Calendario';
            
            vistaActual = 'lista';
        }
    }

    // Cargar actividad reciente con datos reales
    function cargarActividadReciente() {
        setTimeout(() => {
            const container = document.getElementById('recentActivity');
            
            // Generar actividad basada en horarios reales
            let actividadHTML = '';
            const tiposActividad = ['created', 'updated', 'deleted'];
            const iconos = {
                'created': 'fa-plus-circle',
                'updated': 'fa-edit',
                'deleted': 'fa-trash-alt'
            };
            const acciones = {
                'created': 'Horario creado',
                'updated': 'Horario actualizado', 
                'deleted': 'Horario eliminado'
            };

            // Simulación de actividad reciente
            for (let i = 0; i < 5; i++) {
                const tipo = tiposActividad[Math.floor(Math.random() * tiposActividad.length)];
                const horario = horariosData.data ? horariosData.data[Math.floor(Math.random() * horariosData.data.length)] : null;
                
                if (horario) {
                    const docenteNombre = horario.docente ? horario.docente.nombre_completo : 'Docente';
                    const cursoNombre = horario.curso ? horario.curso.nombre : 'Curso';
                    const minutos = Math.floor(Math.random() * 120) + 1;
                    
                    actividadHTML += `
                        <div class="activity-item ${tipo}">
                            <div class="activity-icon ${tipo}">
                                <i class="fas ${iconos[tipo]}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong>${acciones[tipo]}</strong>
                                <div class="text-muted small">
                                    <i class="fas fa-user me-1"></i>${docenteNombre} - 
                                    <i class="fas fa-book me-1"></i>${cursoNombre}
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Hace ${minutos} min
                                </small>
                            </div>
                        </div>
                    `;
                }
            }
            
            container.innerHTML = actividadHTML;
        }, 1000);
    }

    // Animar contadores
    function animarContadores() {
        const contadores = document.querySelectorAll('.stat-value');
        contadores.forEach((contador, index) => {
            const valor = parseInt(contador.textContent);
           let actual = 0;
            const incremento = valor / 50;
            
            setTimeout(() => {
                const timer = setInterval(() => {
                    actual += incremento;
                    if (actual >= valor) {
                        actual = valor;
                        clearInterval(timer);
                    }
                    contador.textContent = Math.floor(actual);
                }, 50);
            }, index * 200);
        });
    }

    // Navegación de calendario
    function previousWeek() {
        console.log('Semana anterior');
        // Implementar lógica para cargar semana anterior
    }

    function nextWeek() {
        console.log('Siguiente semana');
        // Implementar lógica para cargar siguiente semana
    }

    function actualizarSemanaActual() {
        const semanaElement = document.getElementById('semanaActual');
        const fecha = new Date();
        const opciones = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        semanaElement.innerHTML = `
            <i class="fas fa-calendar me-2"></i>
            Semana del ${fecha.toLocaleDateString('es-ES', opciones)}
        `;
    }

    // Funciones de acciones
    function exportarHorarios() {
        // Crear datos para exportar
        const datosExport = [];
        
        if (horariosData.data) {
            horariosData.data.forEach(horario => {
                datosExport.push({
                    'Docente': horario.docente ? horario.docente.nombre_completo : 'Sin asignar',
                    'Día': horario.dia_semana,
                    'Hora Inicio': horario.hora_inicio,
                    'Hora Fin': horario.hora_fin,
                    'Curso': horario.curso ? horario.curso.nombre : 'Sin curso',
                    'Aula': horario.aula ? horario.aula.nombre : 'Sin aula'
                });
            });
        }
        
        // Simular descarga
        console.log('Exportando horarios:', datosExport);
        
        // Mostrar notificación
        mostrarNotificacion('Exportación iniciada', 'Los horarios se están preparando para descargar', 'success');
    }

    function generarReporte() {
        mostrarNotificacion('Generando reporte', 'Se está preparando el reporte estadístico', 'info');
        
        setTimeout(() => {
            mostrarNotificacion('Reporte generado', 'El reporte estadístico está listo', 'success');
        }, 2000);
    }

    function verDetalles(horarioId) {
        const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
        const content = document.getElementById('detallesContent');
        
        content.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Cargando detalles del horario...</p>
            </div>
        `;
        
        modal.show();
        
        // Buscar horario específico
        let horarioDetalle = null;
        if (horariosData.data) {
            horarioDetalle = horariosData.data.find(h => h.id == horarioId);
        }
        
        setTimeout(() => {
            if (horarioDetalle) {
                const docenteNombre = horarioDetalle.docente ? horarioDetalle.docente.nombre_completo : 'Sin asignar';
                const cursoNombre = horarioDetalle.curso ? horarioDetalle.curso.nombre : 'Sin curso';
                const aulaNombre = horarioDetalle.aula ? horarioDetalle.aula.nombre : 'Sin aula';
                
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-info-circle me-2 text-primary"></i>Información General</h6>
                            <div class="mb-3">
                                <strong><i class="fas fa-hashtag me-1"></i>ID:</strong> ${horarioDetalle.id}
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-toggle-on me-1 text-success"></i>Estado:</strong> 
                                <span class="badge bg-success">Activo</span>
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-calendar-plus me-1"></i>Creado:</strong> 
                                ${new Date().toLocaleDateString('es-ES')}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-clock me-2 text-info"></i>Detalles del Horario</h6>
                            <div class="mb-3">
                                <strong><i class="fas fa-user-tie me-1"></i>Docente:</strong> ${docenteNombre}
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-book me-1"></i>Curso:</strong> ${cursoNombre}
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-door-open me-1"></i>Aula:</strong> ${aulaNombre}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6><i class="fas fa-chart-bar me-2 text-warning"></i>Estadísticas</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-card p-3">
                                        <div class="stat-icon primary" style="width: 2.5rem; height: 2.5rem; font-size: 1rem;">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="fw-bold">25</div>
                                        <small class="text-muted">Estudiantes</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-card p-3">
                                        <div class="stat-icon success" style="width: 2.5rem; height: 2.5rem; font-size: 1rem;">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="fw-bold">2.5</div>
                                        <small class="text-muted">Horas</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-card p-3">
                                        <div class="stat-icon warning" style="width: 2.5rem; height: 2.5rem; font-size: 1rem;">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                        <div class="fw-bold">95%</div>
                                        <small class="text-muted">Asistencia</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fs-1 text-warning mb-3"></i>
                        <h5>Error al cargar detalles</h5>
                        <p class="text-muted">No se pudo encontrar la información del horario</p>
                    </div>
                `;
            }
        }, 1500);
    }

    function duplicarHorario(horarioId) {
        mostrarNotificacion('Duplicando horario', 'Se está creando una copia del horario', 'info');
        
        setTimeout(() => {
            mostrarNotificacion('Horario duplicado', 'Se ha creado una copia exitosamente', 'success');
        }, 1500);
    }

    function eliminarHorario(horarioId) {
        if (confirm('⚠️ ¿Estás seguro de eliminar este horario?\n\nEsta acción no se puede deshacer y afectará la programación académica.')) {
            mostrarNotificacion('Eliminando horario', 'Procesando eliminación...', 'warning');
            
            // Simular eliminación
            setTimeout(() => {
                // Crear formulario dinámico para eliminación
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/horarios-docentes/${horarioId}`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }, 1000);
        }
    }

    function verTodosDocentes() {
        mostrarNotificacion('Cargando docentes', 'Preparando vista completa de docentes', 'info');
    }

    // Sistema de notificaciones
    function mostrarNotificacion(titulo, mensaje, tipo = 'info') {
        const iconos = {
            'success': 'fa-check-circle',
            'error': 'fa-times-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        
        const colores = {
            'success': '#10b981',
            'error': '#ef4444',
            'warning': '#f59e0b',
            'info': '#3b82f6'
        };
        
        const notif = document.createElement('div');
        notif.className = 'position-fixed';
        notif.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: white;
            border-left: 4px solid ${colores[tipo]};
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 1rem;
            max-width: 400px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        notif.innerHTML = `
            <div class="d-flex align-items-start gap-3">
                <i class="fas ${iconos[tipo]} fs-5" style="color: ${colores[tipo]};"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold">${titulo}</div>
                    <div class="text-muted small">${mensaje}</div>
                </div>
                <button class="btn-close btn-sm" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(notif);
        
        setTimeout(() => {
            notif.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notif.style.transform = 'translateX(100%)';
            setTimeout(() => notif.remove(), 300);
        }, 4000);
    }

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            suggestionsContainer.style.display = 'none';
        }
    });
</script>
@endpush