@extends('layouts.app')

@section('title', 'Reportes de Asistencia Docente')

@push('css')
{{-- Select2 CSS for searchable dropdowns --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
/* ========================================
   REPORTES DE ASISTENCIA DOCENTE - CSS
   Tema: Shreyu Admin Template
   ======================================== */

/* Variables CSS - Tema Shreyu */
:root {
    --shreyu-primary: #6c757d;
    --shreyu-success: #10b759;
    --shreyu-info: #35b8e0;
    --shreyu-warning: #f9c851;
    --shreyu-danger: #f05050;
    --shreyu-dark: #313a46;
    --shreyu-light: #f1f3fa;
}

/* Tarjetas de métricas */
.metric-card-reports {
    transition: all 0.3s ease;
}

.metric-card-reports:hover {
    transform: translateY(-4px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
}

.metric-icon-wrapper {
    width: 3rem;
    height: 3rem;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.metric-icon-wrapper.primary {
    background-color: rgba(108, 117, 125, 0.1);
    color: var(--shreyu-primary);
}

.metric-icon-wrapper.success {
    background-color: rgba(16, 183, 89, 0.1);
    color: var(--shreyu-success);
}

.metric-icon-wrapper.warning {
    background-color: rgba(249, 200, 81, 0.1);
    color: var(--shreyu-warning);
}

.metric-icon-wrapper.info {
    background-color: rgba(53, 184, 224, 0.1);
    color: var(--shreyu-info);
}

.metric-value {
    font-size: 2rem;
    font-weight: 600;
    color: var(--shreyu-dark);
    margin-bottom: 0.25rem;
    line-height: 1;
}

.metric-label {
    color: var(--shreyu-primary);
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Tabla de datos mejorada */
.reports-data-table {
    border-collapse: separate;
    border-spacing: 0;
}

.reports-data-table thead th {
    background: linear-gradient(135deg, var(--shreyu-dark) 0%, #3f4853 100%);
    color: #ffffff;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    vertical-align: middle;
    padding: 1rem 0.75rem;
    border: none;
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.reports-data-table tbody td {
    vertical-align: middle;
    font-size: 0.8125rem;
    padding: 0.875rem 0.75rem;
    border-bottom: 1px solid #f1f3fa;
}

.reports-data-table tbody tr {
    transition: all 0.2s ease;
}

.reports-data-table tbody tr:hover {
    background-color: rgba(53, 184, 224, 0.03);
    transform: scale(1.001);
}

/* Agrupación de tabla mejorada */
.table-group-header-reports {
    background: linear-gradient(135deg, rgba(108, 117, 125, 0.12) 0%, rgba(108, 117, 125, 0.08) 100%);
    font-weight: 600;
    border-left: 4px solid var(--shreyu-primary);
}

.table-group-month-reports {
    background: linear-gradient(135deg, rgba(53, 184, 224, 0.12) 0%, rgba(53, 184, 224, 0.08) 100%);
    font-weight: 600;
    border-left: 3px solid var(--shreyu-info);
}

.table-group-week-reports {
    background: linear-gradient(135deg, rgba(241, 243, 250, 0.8) 0%, rgba(241, 243, 250, 0.5) 100%);
    font-weight: 600;
    color: var(--shreyu-primary);
    border-left: 2px solid #dee2e6;
}

.table-total-row-reports td {
    background: linear-gradient(135deg, rgba(16, 183, 89, 0.12) 0%, rgba(16, 183, 89, 0.08) 100%) !important;
    font-weight: 600;
    border-top: 2px solid var(--shreyu-success);
    border-bottom: 2px solid var(--shreyu-success);
}

.table-grand-total-row-reports td {
    background: linear-gradient(135deg, var(--shreyu-dark) 0%, #3f4853 100%) !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 0.875rem;
    padding: 1rem 0.75rem;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
}

/* Badges y chips para datos */
.badge-date {
    display: inline-block;
    padding: 0.35rem 0.65rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.25rem;
    font-weight: 500;
    font-size: 0.75rem;
    color: var(--shreyu-dark);
    border: 1px solid #dee2e6;
}

.badge-course {
    display: inline-block;
    padding: 0.35rem 0.65rem;
    background: linear-gradient(135deg, rgba(53, 184, 224, 0.15) 0%, rgba(53, 184, 224, 0.1) 100%);
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.75rem;
    color: var(--shreyu-info);
    border: 1px solid rgba(53, 184, 224, 0.3);
}

.badge-room {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.35rem 0.65rem;
    background: linear-gradient(135deg, rgba(249, 200, 81, 0.15) 0%, rgba(249, 200, 81, 0.1) 100%);
    border-radius: 0.25rem;
    font-weight: 500;
    font-size: 0.75rem;
    color: #d4a017;
    border: 1px solid rgba(249, 200, 81, 0.3);
}

.badge-shift {
    display: inline-block;
    padding: 0.35rem 0.65rem;
    background: linear-gradient(135deg, rgba(108, 117, 125, 0.15) 0%, rgba(108, 117, 125, 0.1) 100%);
    border-radius: 0.25rem;
    font-weight: 500;
    font-size: 0.75rem;
    color: var(--shreyu-primary);
    border: 1px solid rgba(108, 117, 125, 0.3);
}

.badge-time {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.35rem 0.65rem;
    background: linear-gradient(135deg, rgba(16, 183, 89, 0.15) 0%, rgba(16, 183, 89, 0.1) 100%);
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.75rem;
    color: var(--shreyu-success);
    border: 1px solid rgba(16, 183, 89, 0.3);
}

.badge-hours {
    display: inline-block;
    padding: 0.4rem 0.75rem;
    background: linear-gradient(135deg, var(--shreyu-success) 0%, #0d9448 100%);
    border-radius: 0.25rem;
    font-weight: 700;
    font-size: 0.8125rem;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(16, 183, 89, 0.2);
}

.text-topic {
    color: var(--shreyu-dark);
    font-size: 0.8125rem;
    line-height: 1.4;
    max-width: 250px;
}

/* Perfil de docente */
.teacher-profile-reports {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.teacher-avatar-reports {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--shreyu-primary) 0%, var(--shreyu-dark) 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    flex-shrink: 0;
}

.teacher-avatar-reports img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.teacher-info-reports h6 {
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0 0 0.125rem 0;
}

.teacher-info-reports .teacher-id {
    font-size: 0.75rem;
    color: var(--shreyu-primary);
}

/* Top docentes */
.top-teacher-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #dee2e6;
}

.top-teacher-item:last-child {
    border-bottom: none;
}

.top-teacher-rank {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.top-teacher-rank.rank-1 {
    background-color: var(--shreyu-warning);
    color: #fff;
}

.top-teacher-rank.rank-2 {
    background-color: var(--shreyu-primary);
    color: #fff;
}

.top-teacher-rank.rank-3 {
    background-color: var(--shreyu-dark);
    color: #fff;
}

.top-teacher-rank.rank-other {
    background-color: var(--shreyu-info);
    color: #fff;
}

.top-teacher-info {
    flex: 1;
}

.top-teacher-name {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0.125rem;
}

.top-teacher-stats {
    font-size: 0.75rem;
    color: #98a6ad;
}

/* Animaciones */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}

/* Responsive */
@media (max-width: 768px) {
    .teacher-profile-reports {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .reports-data-table {
        font-size: 0.8125rem;
    }

/* ═══════════════════════════════════════════════════════════════════════
   BARRA DE RENDIMIENTO Y CUMPLIMIENTO POR DOCENTE
   "Código de barras" visual multi-segmento
   ═══════════════════════════════════════════════════════════════════════ */

/* Contenedor de la celda de rendimiento */
.perf-cell {
    min-width: 220px;
    padding: 0.5rem 0 !important;
}

/* Barra multi-segmento apilada */
.perf-bar-track {
    height: 14px;
    border-radius: 7px;
    overflow: hidden;
    display: flex;
    background: #f1f3fa;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.08);
    position: relative;
    margin-bottom: 6px;
}

/* Segmento verde: COMPLETADA (asistió + tema) */
.perf-seg-completada {
    background: linear-gradient(90deg, #10b759, #0d9448);
    height: 100%;
    transition: width 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    position: relative;
}
.perf-seg-completada::after {
    content: '';
    position: absolute;
    top: 2px; left: 2px; right: 2px;
    height: 4px;
    border-radius: 3px;
    background: rgba(255,255,255,0.25);
}

/* Segmento naranja: SIN TEMA (asistió pero no registró tema) */
.perf-seg-sintema {
    background: linear-gradient(90deg, #f9c851, #e6a817);
    height: 100%;
    transition: width 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.1s;
}

/* Segmento morado: INCOMPLETA (solo entrada o solo salida) */
.perf-seg-incompleta {
    background: linear-gradient(90deg, #9b59b6, #7b1fa2);
    height: 100%;
    transition: width 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.2s;
}

/* Segmento rojo: FALTA (inasistencia total) */
.perf-seg-falta {
    background: linear-gradient(90deg, #f05050, #c0392b);
    height: 100%;
    transition: width 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.3s;
}

/* Área restante (sesiones futuras / no computables) */
.perf-seg-pendiente {
    background: #dee2e6;
    flex: 1;
    height: 100%;
}

/* Marcador de límite crítico 30% faltas */
.perf-bar-limit {
    position: absolute;
    right: 30%;
    top: 0;
    bottom: 0;
    width: 2px;
    background: rgba(240, 80, 80, 0.6);
    z-index: 5;
}
.perf-bar-limit::before {
    content: '30%';
    position: absolute;
    top: -16px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 9px;
    color: #f05050;
    font-weight: 700;
    white-space: nowrap;
}

/* Fila de píldoras de métricas debajo de la barra */
.perf-pills {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    margin-top: 2px;
}

.perf-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
}
.perf-pill-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.perf-pill-completada { background: rgba(16, 183, 89, 0.12); color: #0d9448; }
.perf-pill-completada .perf-pill-dot { background: #10b759; }

.perf-pill-sintema { background: rgba(249, 200, 81, 0.18); color: #9a6d00; }
.perf-pill-sintema .perf-pill-dot { background: #f9c851; }

.perf-pill-incompleta { background: rgba(155, 89, 182, 0.12); color: #7b1fa2; }
.perf-pill-incompleta .perf-pill-dot { background: #9b59b6; }

.perf-pill-falta { background: rgba(240, 80, 80, 0.12); color: #c0392b; }
.perf-pill-falta .perf-pill-dot { background: #f05050; }

/* Bloque de porcentajes grandes debajo de las píldoras */
.perf-pct-row {
    display: flex;
    gap: 8px;
    margin-top: 5px;
    flex-wrap: wrap;
}
.perf-pct-item {
    text-align: center;
    min-width: 55px;
}
.perf-pct-value {
    display: block;
    font-size: 1rem;
    font-weight: 800;
    line-height: 1;
}
.perf-pct-label {
    display: block;
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #98a6ad;
    margin-top: 1px;
}

.perf-pct-asistencia .perf-pct-value { color: #10b759; }
.perf-pct-temas      .perf-pct-value { color: #35b8e0; }
.perf-pct-faltas     .perf-pct-value { color: #f05050; }

/* Tooltip custom sobre la barra */
.perf-bar-track [data-bs-toggle="tooltip"] {
    cursor: default;
}

/* Leyenda de colores al pie de la tabla */
.perf-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    padding: 10px 14px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 6px 6px;
}
.perf-legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    color: #495057;
}
.perf-legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 3px;
    flex-shrink: 0;
}
.legend-dot-completada { background: linear-gradient(90deg, #10b759, #0d9448); }
.legend-dot-sintema    { background: linear-gradient(90deg, #f9c851, #e6a817); }
.legend-dot-incompleta { background: linear-gradient(90deg, #9b59b6, #7b1fa2); }
.legend-dot-falta      { background: linear-gradient(90deg, #f05050, #c0392b); }
.legend-dot-pendiente  { background: #dee2e6; }


</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Encabezado de página --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Centro Preuniversitario</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('asistencia-docente.index') }}">Asistencia Docente</a></li>
                        <li class="breadcrumb-item active">Reportes</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-chart-bar me-1"></i> Reportes de Asistencia Docente
                </h4>
            </div>
        </div>
    </div>

    {{-- Tarjetas de métricas --}}
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card metric-card-reports fade-in-up">
                <div class="card-body">
                    <div class="metric-icon-wrapper primary">
                        <i class="mdi mdi-calendar-check"></i>
                    </div>
                    <div class="metric-value">{{ $totalRegistrosPeriodo }}</div>
                    <div class="metric-label">
                        <i class="mdi mdi-chart-line me-1"></i>
                        Registros en el Periodo
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card metric-card-reports fade-in-up">
                <div class="card-body">
                    <div class="metric-icon-wrapper success">
                        <i class="mdi mdi-account-group"></i>
                    </div>
                    <div class="metric-value">{{ $asistenciaPorDocente->count() }}</div>
                    <div class="metric-label">
                        <i class="mdi mdi-account-check me-1"></i>
                        Docentes con Asistencia
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card metric-card-reports fade-in-up">
                <div class="card-body">
                    <div class="metric-icon-wrapper warning">
                        <i class="mdi mdi-clock-outline"></i>
                    </div>
                    <div class="metric-value">{{ number_format($asistenciaPorDocente->sum('total_horas'), 1) }}h</div>
                    <div class="metric-label">
                        <i class="mdi mdi-timer-sand me-1"></i>
                        Horas Totales Dictadas
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card metric-card-reports fade-in-up">
                <div class="card-body">
                    <div class="metric-icon-wrapper info">
                        <i class="mdi mdi-cash-multiple"></i>
                    </div>
                    <div class="metric-value">S/ {{ number_format($asistenciaPorDocente->sum('total_pagos'), 2) }}</div>
                    <div class="metric-label">
                        <i class="mdi mdi-currency-usd me-1"></i>
                        Monto Total Estimado
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerta de éxito --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            <strong>¡Operación exitosa!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Panel de Filtros --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="mdi mdi-filter-variant me-1"></i> Opciones de Filtrado
                    </h5>
                    <form method="GET" action="{{ route('asistencia-docente.reports') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label for="docente_id" class="form-label fw-semibold">
                                    Docente 
                                    <span class="badge bg-info ms-1">{{ count($docentes) }} con horario</span>
                                </label>
                                <select class="form-select" id="docente_id" name="docente_id">
                                    <option value="">Todos los Docentes ({{ count($docentes) }})</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ (string)$selectedDocenteId === (string)$docente->id ? 'selected' : '' }}>
                                            {{ $docente->nombre }} {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-lg-3 col-md-6">
                                <label for="ciclo_academico" class="form-label fw-semibold">Ciclo Académico</label>
                                <select class="form-select" id="ciclo_academico" name="ciclo_academico">
                                    <option value="">Todos los Ciclos</option>
                                    @foreach($ciclosAcademicos as $key => $ciclo)
                                        <option value="{{ $key }}" {{ (string)$selectedCicloAcademico === (string)$key ? 'selected' : '' }}>
                                            {{ $ciclo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-4">
                                <label for="mes" class="form-label fw-semibold">Mes</label>
                                <select class="form-select" id="mes" name="mes" {{ ($fechaInicio || $fechaFin) ? 'disabled' : '' }}>
                                    <option value="">Todos</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ (string)$selectedMonth === (string)$m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-4">
                                <label for="anio" class="form-label fw-semibold">Año</label>
                                <select class="form-select" id="anio" name="anio" {{ ($fechaInicio || $fechaFin) ? 'disabled' : '' }}>
                                    <option value="">Todos</option>
                                    @for ($y = Carbon\Carbon::now()->year; $y >= Carbon\Carbon::now()->year - 5; $y--)
                                        <option value="{{ $y }}" {{ (string)$selectedYear === (string)$y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-lg-2 col-md-4">
                                <label class="form-label fw-semibold">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="mdi mdi-magnify me-1"></i> Ver Reporte
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12">
                                <div class="alert alert-info py-2 mb-0" style="background-color: rgba(53, 184, 224, 0.1); border-color: rgba(53, 184, 224, 0.3);">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    <strong>Rango de fechas personalizado:</strong> Si deseas filtrar por fechas específicas, usa los campos a continuación (esto deshabilitará mes/año)
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-lg-3 col-md-6">
                                <label for="fecha_inicio" class="form-label fw-semibold">
                                    <i class="mdi mdi-calendar-start me-1"></i> Fecha Inicio
                                </label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}">
                            </div>
                            
                            <div class="col-lg-3 col-md-6">
                                <label for="fecha_fin" class="form-label fw-semibold">
                                    <i class="mdi mdi-calendar-end me-1"></i> Fecha Fin
                                </label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin }}">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="mdi mdi-file-excel me-1"></i> Exportar Excel
                                </label>
                                <a href="{{ route('asistencia-docente.exportar', [
                                    'docente_id' => $selectedDocenteId,
                                    'mes' => $selectedMonth,
                                    'anio' => $selectedYear,
                                    'fecha_inicio' => $fechaInicio,
                                    'fecha_fin' => $fechaFin,
                                    'ciclo_academico' => $selectedCicloAcademico
                                ]) }}" class="btn btn-success w-100" id="exportBtn">
                                    <i class="mdi mdi-download me-1"></i> Descargar Excel
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="mdi mdi-file-pdf me-1"></i> Exportar PDF
                                </label>
                                <a href="{{ route('asistencia-docente.exportar-planilla-pdf', [
                                    'docente_id' => $selectedDocenteId,
                                    'mes' => $selectedMonth,
                                    'anio' => $selectedYear,
                                    'fecha_inicio' => $fechaInicio,
                                    'fecha_fin' => $fechaFin,
                                    'ciclo_academico' => $selectedCicloAcademico
                                ]) }}" class="btn btn-danger w-100" id="exportPdfBtn">
                                    <i class="mdi mdi-file-pdf me-1"></i> Descargar PDF Planilla
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos y Resumen --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="mdi mdi-chart-line me-1"></i> Asistencia por Día
                    </h5>
                    <p class="text-muted mb-3">
                        @if($fechaInicio && $fechaFin)
                            Periodo: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                        @elseif(!empty($selectedMonth) && !empty($selectedYear))
                            {{ \Carbon\Carbon::create((int)$selectedYear, (int)$selectedMonth, 1)->locale('es')->monthName }} {{ $selectedYear }}
                        @else
                            Todo el historial
                        @endif
                        @if($selectedCicloAcademico)
                            (Ciclo: {{ $selectedCicloAcademico }})
                        @endif
                    </p>
                    <canvas id="graficoSemanal" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="mdi mdi-account-star me-1"></i> Top 5 Docentes
                    </h5>
                    @forelse($asistenciaPorDocente->sortByDesc('total_asistencias')->take(5) as $index => $docente)
                        <div class="top-teacher-item">
                            <div class="top-teacher-rank rank-{{ $index == 0 ? '1' : ($index == 1 ? '2' : ($index == 2 ? '3' : 'other')) }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="top-teacher-info">
                                <div class="top-teacher-name">
                                    {{ $docente->docente ? $docente->docente->nombre . ' ' . $docente->docente->apellido_paterno : 'N/A' }}
                                </div>
                                <div class="top-teacher-stats">
                                    {{ $docente->total_asistencias }} registros
                                    @if(isset($docente->total_horas))
                                        • {{ number_format($docente->total_horas, 1) }}h
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="mdi mdi-account-group" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mb-0 mt-2">No hay datos de docentes para el periodo seleccionado.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen por Docente --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-account-group me-1"></i> Resumen por Docente
                        </h5>
                        <span class="badge bg-primary">
                            {{ count($processedDetailedAsistencias) }} {{ count($processedDetailedAsistencias) == 1 ? 'Docente' : 'Docentes' }}
                        </span>
                    </div>
                    <p class="text-muted mb-3">
                        <i class="mdi mdi-information-outline me-1"></i>
                        Haz clic en <strong>"Ver Detalle"</strong> para ver todas las sesiones de un docente, o usa <strong>"Descargar Excel"</strong> para el reporte completo.
                    </p>
                    
                    @if(count($processedDetailedAsistencias) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0">
                                <thead style="background: linear-gradient(135deg, var(--shreyu-dark) 0%, #3f4853 100%);">
                                    <tr>
                                        <th style="color: #ffffff; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 1rem 0.75rem; border: none;">
                                            #
                                        </th>
                                        <th style="color: #ffffff; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 1rem 0.75rem; border: none;">
                                            DOCENTE
                                        </th>
                                        <th class="text-center" style="color: #ffffff; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 1rem 0.75rem; border: none;">
                                            TOTAL SESIONES
                                        </th>
                                        <th class="text-center" style="color: #ffffff; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 1rem 0.75rem; border: none;">
                                            HORAS DICTADAS
                                        </th>
                                        <th class="text-center" style="color: #ffffff; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 1rem 0.75rem; border: none;">
                                            MONTO ESTIMADO
                                        </th>
                                        <th style="color: #ffffff; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 1rem 0.75rem; border: none; min-width: 260px;">
                                            <i class="mdi mdi-chart-bar-stacked me-1"></i> RENDIMIENTO Y CUMPLIMIENTO
                                        </th>
                                        <th class="text-center" style="color: #ffffff; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; padding: 1rem 0.75rem; border: none;">
                                            ACCIONES
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $sortedDocentes = collect($processedDetailedAsistencias)->sortByDesc('total_horas');
                                        $totalSesiones = 0;
                                        $totalHoras = 0;
                                        $totalMonto = 0;
                                    @endphp
                                    @foreach($sortedDocentes as $docenteId => $docenteData)
                                        @php
                                            // Contar sesiones reales
                                            $sesionesDocente = 0;
                                            foreach($docenteData['months'] as $monthData) {
                                                foreach($monthData['weeks'] as $weekData) {
                                                    $sesionesDocente += count($weekData['details']);
                                                }
                                            }
                                            
                                            $totalSesiones += $sesionesDocente;
                                            $totalHoras += $docenteData['total_horas'];
                                            $totalMonto += $docenteData['total_pagos'] ?? 0;
                                        @endphp
                                        <tr style="transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='rgba(53, 184, 224, 0.03)'" onmouseout="this.style.backgroundColor=''">
                                            <td style="padding: 0.875rem 0.75rem; border-bottom: 1px solid #f1f3fa; vertical-align: middle;">
                                                <span class="badge" style="background: linear-gradient(135deg, var(--shreyu-primary) 0%, var(--shreyu-dark) 100%); color: #ffffff; font-weight: 600; font-size: 0.8125rem; padding: 0.4rem 0.65rem;">
                                                    {{ $loop->iteration }}
                                                </span>
                                            </td>
                                            <td style="padding: 0.875rem 0.75rem; border-bottom: 1px solid #f1f3fa; vertical-align: middle;">
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($docenteData['docente_info'] && $docenteData['docente_info']->foto_perfil)
                                                        <img src="{{ asset('storage/' . $docenteData['docente_info']->foto_perfil) }}" 
                                                             alt="Foto" 
                                                             class="rounded-circle" 
                                                             style="width: 2.5rem; height: 2.5rem; object-fit: cover;">
                                                    @else
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, var(--shreyu-primary) 0%, var(--shreyu-dark) 100%); color: white; font-weight: 600; font-size: 1rem;">
                                                            {{ $docenteData['docente_info'] ? strtoupper(substr($docenteData['docente_info']->nombre, 0, 1)) : 'N/A' }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div style="font-weight: 600; font-size: 0.875rem; color: var(--shreyu-dark);">
                                                            {{ $docenteData['docente_info'] ? $docenteData['docente_info']->nombre . ' ' . $docenteData['docente_info']->apellido_paterno : 'N/A' }}
                                                        </div>
                                                        <div style="font-size: 0.75rem; color: var(--shreyu-primary);">
                                                            Doc. {{ $docenteData['docente_info']->numero_documento ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center" style="padding: 0.875rem 0.75rem; border-bottom: 1px solid #f1f3fa; vertical-align: middle;">
                                                <span class="badge" style="background: linear-gradient(135deg, rgba(53, 184, 224, 0.15) 0%, rgba(53, 184, 224, 0.1) 100%); color: var(--shreyu-info); font-weight: 600; font-size: 0.8125rem; padding: 0.4rem 0.75rem; border: 1px solid rgba(53, 184, 224, 0.3);">
                                                    <i class="mdi mdi-calendar-check me-1"></i>
                                                    {{ $sesionesDocente }}
                                                </span>
                                            </td>
                                            <td class="text-center" style="padding: 0.875rem 0.75rem; border-bottom: 1px solid #f1f3fa; vertical-align: middle;">
                                                <span class="badge" style="background: linear-gradient(135deg, var(--shreyu-success) 0%, #0d9448 100%); color: #ffffff; font-weight: 700; font-size: 0.875rem; padding: 0.45rem 0.85rem; box-shadow: 0 2px 4px rgba(16, 183, 89, 0.2);">
                                                    <i class="mdi mdi-clock-outline me-1"></i>
                                                    {{ number_format($docenteData['total_horas'], 2) }}h
                                                </span>
                                            </td>
                                            <td class="text-center" style="padding: 0.875rem 0.75rem; border-bottom: 1px solid #f1f3fa; vertical-align: middle;">
                                                <span class="badge" style="background: linear-gradient(135deg, rgba(249, 200, 81, 0.15) 0%, rgba(249, 200, 81, 0.1) 100%); color: #d4a017; font-weight: 600; font-size: 0.8125rem; padding: 0.4rem 0.75rem; border: 1px solid rgba(249, 200, 81, 0.3);">
                                                    <i class="mdi mdi-cash me-1"></i>
                                                    S/ {{ number_format($docenteData['total_pagos'] ?? 0, 2) }}
                                                </span>
                                            </td>
                                            {{-- ══ CELDA: BARRA DE RENDIMIENTO Y CUMPLIMIENTO ══ --}}
                                            <td class="perf-cell" style="padding: 0.75rem; border-bottom: 1px solid #f1f3fa; vertical-align: middle;">
                                                @php
                                                    $wC  = $docenteData['w_completada'] ?? 0;
                                                    $wST = $docenteData['w_sin_tema']   ?? 0;
                                                    $wI  = $docenteData['w_incompleta'] ?? 0;
                                                    $wF  = $docenteData['w_falta']      ?? 0;
                                                    $cC  = $docenteData['cont_completada']  ?? 0;
                                                    $cST = $docenteData['cont_sin_tema']    ?? 0;
                                                    $cI  = $docenteData['cont_incompleta']  ?? 0;
                                                    $cF  = $docenteData['cont_falta']       ?? 0;
                                                    $cP  = $docenteData['cont_programada']  ?? 0;
                                                    $cT  = $docenteData['cont_tardanza']    ?? 0;
                                                    $tMinT = $docenteData['total_min_tardanza'] ?? 0;
                                                    $pTa = $docenteData['pct_tardanza']     ?? 0;
                                                    $promMinT = $docenteData['promedio_min_tardanza'] ?? 0;
                                                    $tot = $docenteData['total_transcurridas'] ?? 0;
                                                    $pA  = $docenteData['pct_asistencia'] ?? 0;
                                                    $pT  = $docenteData['pct_temas']      ?? 0;
                                                    $pF  = $docenteData['pct_faltas']     ?? 0;
                                                    // Alerta si % faltas >= 30%
                                                    $alertaFaltas = $pF >= 30;
                                                    $alertaNoTemas = ($pT < 70 && $tot > 0);
                                                @endphp

                                                @if($tot === 0 && $cP === 0)
                                                    {{-- Sin datos aún --}}
                                                    <div class="text-muted" style="font-size:0.75rem; text-align:center; padding:4px 0;">
                                                        <i class="mdi mdi-clock-outline me-1"></i> Sin sesiones registradas
                                                    </div>
                                                @else
                                                    {{-- Barra multi-segmento --}}
                                                    <div class="perf-bar-track"
                                                         data-bs-toggle="tooltip"
                                                         data-bs-html="true"
                                                         data-bs-placement="top"
                                                         title="<strong>Periodo analizado:</strong> {{ $tot }} sesión(es) transcurrida(s)<br>
                                                                <span style='color:#10b759'>●</span> Completadas (con tema): {{ $cC }}<br>
                                                                <span style='color:#f9c851'>●</span> Sin tema registrado: {{ $cST }}<br>
                                                                <span style='color:#9b59b6'>●</span> Marcado incompleto: {{ $cI }}<br>
                                                                <span style='color:#f05050'>●</span> Inasistencias (faltas): {{ $cF }}<br>
                                                                <span style='color:#e67e22'>●</span> Tardanzas: {{ $cT }} ses. ({{ $tMinT }} min totales)<br>
                                                                <span style='color:#dee2e6'>●</span> Programadas/En curso: {{ $cP }}">
                                                        {{-- Segmento: Completadas --}}
                                                        @if($wC > 0)
                                                            <div class="perf-seg-completada" style="width:{{ $wC }}%;"
                                                                 data-bs-toggle="tooltip"
                                                                 data-bs-placement="top"
                                                                 title="✅ Completadas: {{ $cC }} ses. ({{ $wC }}%)"></div>
                                                        @endif
                                                        {{-- Segmento: Sin Tema --}}
                                                        @if($wST > 0)
                                                            <div class="perf-seg-sintema" style="width:{{ $wST }}%;"
                                                                 data-bs-toggle="tooltip"
                                                                 data-bs-placement="top"
                                                                 title="⚠️ Sin tema: {{ $cST }} ses. ({{ $wST }}%)"></div>
                                                        @endif
                                                        {{-- Segmento: Incompleta --}}
                                                        @if($wI > 0)
                                                            <div class="perf-seg-incompleta" style="width:{{ $wI }}%;"
                                                                 data-bs-toggle="tooltip"
                                                                 data-bs-placement="top"
                                                                 title="⚡ Marcado incompleto: {{ $cI }} ses. ({{ $wI }}%)"></div>
                                                        @endif
                                                        {{-- Segmento: Falta --}}
                                                        @if($wF > 0)
                                                            <div class="perf-seg-falta" style="width:{{ $wF }}%;"
                                                                 data-bs-toggle="tooltip"
                                                                 data-bs-placement="top"
                                                                 title="❌ Faltas: {{ $cF }} ses. ({{ $wF }}%)"></div>
                                                        @endif
                                                        {{-- Área restante (programadas/en curso) --}}
                                                        <div class="perf-seg-pendiente"
                                                             data-bs-toggle="tooltip"
                                                             data-bs-placement="top"
                                                             title="📅 Programadas/En curso: {{ $cP }} ses."></div>
                                                    </div>

                                                    {{-- Píldoras de conteo --}}
                                                    <div class="perf-pills">
                                                        @if($cC > 0)
                                                        <span class="perf-pill perf-pill-completada">
                                                            <span class="perf-pill-dot"></span> {{ $cC }} compl.
                                                        </span>
                                                        @endif
                                                        @if($cST > 0)
                                                        <span class="perf-pill perf-pill-sintema">
                                                            <span class="perf-pill-dot"></span> {{ $cST }} sin tema
                                                        </span>
                                                        @endif
                                                        @if($cI > 0)
                                                        <span class="perf-pill perf-pill-incompleta">
                                                            <span class="perf-pill-dot"></span> {{ $cI }} incompl.
                                                        </span>
                                                        @endif
                                                        @if($cF > 0)
                                                        <span class="perf-pill perf-pill-falta">
                                                            <span class="perf-pill-dot"></span> {{ $cF }} falta(s)
                                                        </span>
                                                        @endif
                                                        @if($cT > 0)
                                                        <span class="perf-pill" style="background: rgba(230, 126, 34, 0.1); color: #d35400; border: 1px solid rgba(230, 126, 34, 0.25);">
                                                            <span class="perf-pill-dot" style="background:#e67e22"></span> {{ $cT }} tard. ({{ $tMinT }}m)
                                                        </span>
                                                        @endif
                                                    </div>

                                                    {{-- Porcentajes clave --}}
                                                    <div class="perf-pct-row">
                                                        <div class="perf-pct-item perf-pct-asistencia"
                                                             data-bs-toggle="tooltip"
                                                             data-bs-placement="bottom"
                                                             title="Porcentaje de sesiones donde el docente asistió (completa o sin tema), sobre el total de sesiones transcurridas.">
                                                            <span class="perf-pct-value">{{ $pA }}%</span>
                                                            <span class="perf-pct-label">Asistencia</span>
                                                        </div>
                                                        <div style="color:#dee2e6; font-weight:300; font-size:1.2rem; align-self:center;">|</div>
                                                        <div class="perf-pct-item perf-pct-temas"
                                                             data-bs-toggle="tooltip"
                                                             data-bs-placement="bottom"
                                                             title="Porcentaje de sesiones asistidas donde el docente registró el tema desarrollado.">
                                                            <span class="perf-pct-value">{{ $pT }}%</span>
                                                            <span class="perf-pct-label">Temas OK</span>
                                                        </div>
                                                        <div style="color:#dee2e6; font-weight:300; font-size:1.2rem; align-self:center;">|</div>
                                                        <div class="perf-pct-item perf-pct-tardanzas"
                                                             data-bs-toggle="tooltip"
                                                             data-bs-placement="bottom"
                                                             title="Porcentaje de tardanzas sobre las sesiones con asistencia. Promedio: {{ $promMinT }} min.">
                                                            <span class="perf-pct-value" style="color: #e67e22;">{{ $pTa }}%</span>
                                                            <span class="perf-pct-label">Tardanza</span>
                                                        </div>
                                                        <div style="color:#dee2e6; font-weight:300; font-size:1.2rem; align-self:center;">|</div>
                                                        <div class="perf-pct-item perf-pct-faltas"
                                                             data-bs-toggle="tooltip"
                                                             data-bs-placement="bottom"
                                                             title="Porcentaje de faltas absolutas (inasistencias) sobre el total de sesiones transcurridas.">
                                                            <span class="perf-pct-value">{{ $pF }}%</span>
                                                            <span class="perf-pct-label">Faltas</span>
                                                        </div>
                                                        @if($alertaFaltas)
                                                            <div class="align-self-center ms-1">
                                                                <span class="badge bg-danger" style="font-size:0.6rem; animation: blink 1s step-start infinite;"
                                                                      data-bs-toggle="tooltip" title="¡Supera el 30% de faltas reglamentarias!">
                                                                    <i class="mdi mdi-alert"></i> +30% faltas
                                                                </span>
                                                            </div>
                                                        @endif
                                                        @if($alertaNoTemas)
                                                            <div class="align-self-center ms-1">
                                                                <span class="badge bg-warning text-dark" style="font-size:0.6rem;"
                                                                      data-bs-toggle="tooltip" title="Bajo registro de temas (< 70% de sesiones asistidas tienen tema registrado).">
                                                                    <i class="mdi mdi-pencil-off"></i> Temas pendientes
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            {{-- ══ FIN CELDA RENDIMIENTO ══ --}}

                                            <td class="text-center" style="padding: 0.875rem 0.75rem; border-bottom: 1px solid #f1f3fa; vertical-align: middle;">
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detalleModal{{ $docenteId }}">
                                                    <i class="mdi mdi-eye me-1"></i> Ver Detalle
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    {{-- Fila de totales --}}
                                    <tr style="background: linear-gradient(135deg, var(--shreyu-dark) 0%, #3f4853 100%); color: #ffffff; font-weight: 700;">
                                        <td colspan="2" class="text-end" style="padding: 1rem 0.75rem; border: none; font-size: 0.875rem;">
                                            <i class="mdi mdi-sigma me-2"></i>
                                            <strong>TOTAL GENERAL</strong>
                                        </td>
                                        <td class="text-center" style="padding: 1rem 0.75rem; border: none;">
                                            <span class="badge" style="background: #ffffff; color: var(--shreyu-dark); font-weight: 700; font-size: 0.875rem; padding: 0.45rem 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                                {{ $totalSesiones }}
                                            </span>
                                        </td>
                                        <td class="text-center" style="padding: 1rem 0.75rem; border: none;">
                                            <span class="badge" style="background: #ffffff; color: var(--shreyu-dark); font-weight: 700; font-size: 0.875rem; padding: 0.45rem 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                                {{ number_format($totalHoras, 2) }}h
                                            </span>
                                        </td>
                                        <td class="text-center" style="padding: 1rem 0.75rem; border: none;">
                                            <span class="badge" style="background: #ffffff; color: var(--shreyu-dark); font-weight: 700; font-size: 0.875rem; padding: 0.45rem 0.75rem; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                                S/ {{ number_format($totalMonto, 2) }}
                                            </span>
                                        </td>
                                        {{-- Celda vacía para columna rendimiento en totales --}}
                                        <td style="padding: 1rem 0.75rem; border: none; font-size: 0.7rem; color: rgba(255,255,255,0.6); text-align: center;">
                                            Ver barra por docente
                                        </td>
                                        <td style="padding: 1rem 0.75rem; border: none;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        {{-- Leyenda de colores de la barra de rendimiento --}}
                        <div class="perf-legend mt-0">
                            <span style="font-size:0.75rem; font-weight:600; color:#495057; margin-right:4px;">
                                <i class="mdi mdi-information-outline me-1"></i>Leyenda:
                            </span>
                            <div class="perf-legend-item">
                                <div class="perf-legend-dot legend-dot-completada"></div>
                                Completada (asistió + tema registrado)
                            </div>
                            <div class="perf-legend-item">
                                <div class="perf-legend-dot legend-dot-sintema"></div>
                                Sin tema (asistió pero no registró tema)
                            </div>
                            <div class="perf-legend-item">
                                <div class="perf-legend-dot legend-dot-incompleta"></div>
                                Incompleta (solo entrada o salida)
                            </div>
                            <div class="perf-legend-item">
                                <div class="perf-legend-dot legend-dot-falta"></div>
                                Falta (inasistencia total)
                            </div>
                            <div class="perf-legend-item">
                                <div class="perf-legend-dot legend-dot-pendiente"></div>
                                Programadas / En curso
                            </div>
                        </div>
                    @else

                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-account-group" style="font-size: 4rem; opacity: 0.3;"></i>
                            <h5 class="mt-3">No hay datos de asistencia</h5>
                            <p>No se encontraron registros de asistencia docente para el periodo y/o docente seleccionado.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         SECCIÓN DE GRÁFICOS: Rendimiento por Docente
         ═══════════════════════════════════════════════════════════════ --}}
    @if(count($processedDetailedAsistencias) > 0)
    <div class="row mt-4" id="seccion-graficos">
        {{-- Encabezado de sección de gráficos --}}
        <div class="col-12 mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="mdi mdi-chart-areaspline" style="font-size:1.5rem; color:var(--shreyu-primary);"></i>
                <h5 class="mb-0 fw-bold">Análisis Gráfico de Rendimiento Docente</h5>
                <span class="badge bg-primary ms-2">{{ count($processedDetailedAsistencias) }} Docentes</span>
            </div>
            <p class="text-muted mt-1 mb-0" style="font-size:0.82rem;">
                Distribución visual de asistencias, faltas y registro de temas por docente durante el periodo consultado.
            </p>
        </div>

        {{-- Pre-calcular arrays para el gráfico comparativo --}}
        @php
            $grafLabels     = [];
            $grafAsistencia = [];
            $grafTemas      = [];
            $grafFaltas     = [];
            $grafTardanzas  = [];
            foreach ($processedDetailedAsistencias as $gd) {
                $gi = $gd['docente_info'] ?? null;
                $grafLabels[]     = $gi ? ($gi->nombre . ' ' . $gi->apellido_paterno) : 'N/A';
                $grafAsistencia[] = $gd['pct_asistencia'] ?? 0;
                $grafTemas[]      = $gd['pct_temas']      ?? 0;
                $grafFaltas[]     = $gd['pct_faltas']     ?? 0;
                $grafTardanzas[]  = $gd['pct_tardanza']   ?? 0;
            }
        @endphp

        {{-- ── GRÁFICO 1: Barras comparativas de % por todos los docentes ── --}}
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex align-items-center gap-2 py-3"
                     style="background: linear-gradient(135deg, var(--shreyu-dark) 0%, #3f4853 100%); border-radius: 6px 6px 0 0;">
                    <i class="mdi mdi-chart-bar text-white" style="font-size:1.1rem;"></i>
                    <span class="text-white fw-semibold" style="font-size:0.9rem;">
                        Comparativo General — % Asistencia · % Temas Registrados · % Tardanzas · % Faltas
                    </span>
                </div>
                <div class="card-body" style="padding: 1.25rem;">
                    <div style="height: 320px; position: relative;">
                        <canvas id="graficoBarsComparativo"></canvas>
                    </div>
                    {{-- Inyectar datos del gráfico comparativo como variables JS --}}
                    <script id="chart-compare-data">
                        window.grafChartData = {
                            labels:     {!! json_encode(array_values($grafLabels), JSON_UNESCAPED_UNICODE) !!},
                            asistencia: {!! json_encode(array_values($grafAsistencia)) !!},
                            temas:      {!! json_encode(array_values($grafTemas)) !!},
                            faltas:     {!! json_encode(array_values($grafFaltas)) !!},
                            tardanzas:  {!! json_encode(array_values($grafTardanzas)) !!}
                        };
                    </script>
                </div>
            </div>
        </div>


        {{-- ── GRÁFICO 2: Donas individuales por docente ── --}}
        <div class="col-12 mb-2">
            <h6 class="fw-semibold text-muted mb-3">
                <i class="mdi mdi-chart-donut me-1"></i>
                Distribución de Sesiones por Docente
            </h6>
        </div>

        @php $sortedGrafDoc = collect($processedDetailedAsistencias)->sortByDesc('total_horas'); @endphp
        @foreach($sortedGrafDoc as $dId => $dData)
            @php
                $dInfo = $dData['docente_info'];
                $dNombre = $dInfo ? $dInfo->nombre . ' ' . $dInfo->apellido_paterno : 'N/A';
                $dIniciales = $dInfo ? strtoupper(substr($dInfo->nombre, 0, 1) . substr($dInfo->apellido_paterno, 0, 1)) : 'ND';
                $dCc  = $dData['cont_completada']  ?? 0;
                $dCst = $dData['cont_sin_tema']    ?? 0;
                $dCi  = $dData['cont_incompleta']  ?? 0;
                $dCf  = $dData['cont_falta']       ?? 0;
                $dCp  = $dData['cont_programada']  ?? 0;
                $dTard = $dData['cont_tardanza']   ?? 0;
                $dMinT = $dData['total_min_tardanza'] ?? 0;
                $dPtard = $dData['pct_tardanza']   ?? 0;
                $dTot = $dData['total_transcurridas'] ?? 0;
                $dPa  = $dData['pct_asistencia']   ?? 0;
                $dPt  = $dData['pct_temas']        ?? 0;
                $dPf  = $dData['pct_faltas']       ?? 0;
                $dTotalSes = $dCc + $dCst + $dCi + $dCf + $dCp;
                // Color del anillo exterior por nivel de asistencia
                $ringColor = $dPa >= 90 ? '#10b759' : ($dPa >= 70 ? '#f9c851' : '#f05050');
            @endphp
            <div class="col-12 col-md-6 col-xl-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="border-top: 3px solid {{ $ringColor }} !important;">
                    {{-- Header tarjeta docente --}}
                    <div class="card-header d-flex align-items-center gap-2 py-2 bg-transparent border-bottom" style="border-color: #f1f3fa !important;">
                        @if($dInfo && $dInfo->foto_perfil)
                            <img src="{{ asset('storage/' . $dInfo->foto_perfil) }}"
                                 class="rounded-circle" style="width:2rem; height:2rem; object-fit:cover;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                 style="width:2rem; height:2rem; background: linear-gradient(135deg, var(--shreyu-primary) 0%, var(--shreyu-dark) 100%); font-size:0.75rem; flex-shrink:0;">
                                {{ $dIniciales }}
                            </div>
                        @endif
                        <div class="flex-fill overflow-hidden">
                            <div class="fw-semibold text-truncate" style="font-size:0.82rem; color:var(--shreyu-dark);">
                                {{ $dNombre }}
                            </div>
                            <div style="font-size:0.68rem; color:#98a6ad;">
                                {{ $dTotalSes }} sesión(es) en total
                            </div>
                        </div>
                        {{-- Badge nivel asistencia --}}
                        @if($dPa >= 90)
                            <span class="badge" style="background:rgba(16,183,89,0.15); color:#0d9448; font-size:0.65rem; border:1px solid rgba(16,183,89,0.3);">
                                <i class="mdi mdi-check-circle"></i> Excelente
                            </span>
                        @elseif($dPa >= 70)
                            <span class="badge" style="background:rgba(249,200,81,0.15); color:#9a6d00; font-size:0.65rem; border:1px solid rgba(249,200,81,0.3);">
                                <i class="mdi mdi-alert-circle"></i> Regular
                            </span>
                        @else
                            <span class="badge" style="background:rgba(240,80,80,0.15); color:#c0392b; font-size:0.65rem; border:1px solid rgba(240,80,80,0.3);">
                                <i class="mdi mdi-close-circle"></i> Crítico
                            </span>
                        @endif
                    </div>

                    <div class="card-body p-3">
                        @if($dTotalSes === 0)
                            <div class="text-center py-4 text-muted">
                                <i class="mdi mdi-calendar-blank" style="font-size:2.5rem; opacity:0.3;"></i>
                                <p class="mb-0 mt-2" style="font-size:0.8rem;">Sin sesiones registradas</p>
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-3">
                                {{-- Dona --}}
                                <div style="position:relative; width:130px; height:130px; flex-shrink:0;">
                                    <canvas id="donaDocente{{ $dId }}"
                                            data-completada="{{ $dCc }}"
                                            data-sintema="{{ $dCst }}"
                                            data-incompleta="{{ $dCi }}"
                                            data-falta="{{ $dCf }}"
                                            data-programada="{{ $dCp }}"
                                            width="130" height="130"></canvas>
                                    {{-- Centro de la dona: % asistencia --}}
                                    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; pointer-events:none;">
                                        <div style="font-size:1.3rem; font-weight:800; line-height:1; color:{{ $ringColor }};">
                                            {{ $dPa }}%
                                        </div>
                                        <div style="font-size:0.55rem; text-transform:uppercase; letter-spacing:0.05em; color:#98a6ad; margin-top:2px;">
                                            Asistencia
                                        </div>
                                    </div>
                                </div>

                                {{-- Stats a la derecha de la dona --}}
                                <div class="flex-fill">
                                    {{-- Barra mini de completadas --}}
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#0d9448; font-weight:600;">
                                                <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:#10b759; margin-right:3px;"></span>
                                                Completadas
                                            </span>
                                            <span style="font-size:0.7rem; font-weight:700; color:#0d9448;">{{ $dCc }}</span>
                                        </div>
                                        <div style="height:5px; border-radius:3px; background:#f1f3fa; overflow:hidden;">
                                            <div style="height:100%; border-radius:3px; background:linear-gradient(90deg,#10b759,#0d9448); width:{{ $dTotalSes > 0 ? round($dCc/$dTotalSes*100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                    {{-- Sin tema --}}
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#9a6d00; font-weight:600;">
                                                <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:#f9c851; margin-right:3px;"></span>
                                                Sin tema
                                            </span>
                                            <span style="font-size:0.7rem; font-weight:700; color:#9a6d00;">{{ $dCst }}</span>
                                        </div>
                                        <div style="height:5px; border-radius:3px; background:#f1f3fa; overflow:hidden;">
                                            <div style="height:100%; border-radius:3px; background:linear-gradient(90deg,#f9c851,#e6a817); width:{{ $dTotalSes > 0 ? round($dCst/$dTotalSes*100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                    {{-- Incompleta --}}
                                    @if($dCi > 0)
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#7b1fa2; font-weight:600;">
                                                <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:#9b59b6; margin-right:3px;"></span>
                                                Incompl.
                                            </span>
                                            <span style="font-size:0.7rem; font-weight:700; color:#7b1fa2;">{{ $dCi }}</span>
                                        </div>
                                        <div style="height:5px; border-radius:3px; background:#f1f3fa; overflow:hidden;">
                                            <div style="height:100%; border-radius:3px; background:linear-gradient(90deg,#9b59b6,#7b1fa2); width:{{ $dTotalSes > 0 ? round($dCi/$dTotalSes*100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                    @endif
                                    {{-- Tardanzas --}}
                                    @if($dTard > 0)
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#e67e22; font-weight:600;">
                                                <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:#e67e22; margin-right:3px;"></span>
                                                Tardanzas
                                            </span>
                                            <span style="font-size:0.7rem; font-weight:700; color:#e67e22;">{{ $dTard }} ({{ $dMinT }}m)</span>
                                        </div>
                                        <div style="height:5px; border-radius:3px; background:#f1f3fa; overflow:hidden;">
                                            <div style="height:100%; border-radius:3px; background:linear-gradient(90deg,#e67e22,#d35400); width:{{ $dTotalSes > 0 ? round($dTard/$dTotalSes*100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                    @endif
                                    {{-- Faltas --}}
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#c0392b; font-weight:600;">
                                                <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:#f05050; margin-right:3px;"></span>
                                                Faltas
                                            </span>
                                            <span style="font-size:0.7rem; font-weight:700; color:#c0392b;">{{ $dCf }}</span>
                                        </div>
                                        <div style="height:5px; border-radius:3px; background:#f1f3fa; overflow:hidden;">
                                            <div style="height:100%; border-radius:3px; background:linear-gradient(90deg,#f05050,#c0392b); width:{{ $dTotalSes > 0 ? round($dCf/$dTotalSes*100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                    {{-- Prog. --}}
                                    @if($dCp > 0)
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span style="font-size:0.7rem; color:#98a6ad; font-weight:600;">
                                                <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:#dee2e6; margin-right:3px;"></span>
                                                Programadas
                                            </span>
                                            <span style="font-size:0.7rem; font-weight:700; color:#98a6ad;">{{ $dCp }}</span>
                                        </div>
                                        <div style="height:5px; border-radius:3px; background:#f1f3fa; overflow:hidden;">
                                            <div style="height:100%; border-radius:3px; background:#dee2e6; width:{{ $dTotalSes > 0 ? round($dCp/$dTotalSes*100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Footer: 4 porcentajes clave --}}
                            <div class="d-flex justify-content-around mt-3 pt-2" style="border-top: 1px solid #f1f3fa;">
                                <div class="text-center">
                                    <div style="font-size:1.0rem; font-weight:800; color:#10b759;">{{ $dPa }}%</div>
                                    <div style="font-size:0.55rem; text-transform:uppercase; color:#98a6ad;">Asistencia</div>
                                </div>
                                <div style="width:1px; background:#dee2e6;"></div>
                                <div class="text-center">
                                    <div style="font-size:1.0rem; font-weight:800; color:#35b8e0;">{{ $dPt }}%</div>
                                    <div style="font-size:0.55rem; text-transform:uppercase; color:#98a6ad;">Temas OK</div>
                                </div>
                                <div style="width:1px; background:#dee2e6;"></div>
                                <div class="text-center">
                                    <div style="font-size:1.0rem; font-weight:800; color:#e67e22;">{{ $dPtard }}%</div>
                                    <div style="font-size:0.55rem; text-transform:uppercase; color:#98a6ad;">Tardanza</div>
                                </div>
                                <div style="width:1px; background:#dee2e6;"></div>
                                <div class="text-center">
                                    <div style="font-size:1.0rem; font-weight:800; color:#f05050;">{{ $dPf }}%</div>
                                    <div style="font-size:0.55rem; text-transform:uppercase; color:#98a6ad;">Faltas</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>{{-- fin #seccion-graficos --}}
    @endif

    {{-- Modales de Detalle por Docente --}}
    @foreach($processedDetailedAsistencias as $docenteId => $docenteData)

        <div class="modal fade" id="detalleModal{{ $docenteId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, var(--shreyu-dark) 0%, #3f4853 100%); color: #ffffff;">
                        <h5 class="modal-title">
                            <i class="mdi mdi-account-details me-2"></i>
                            Detalle de Asistencia: {{ $docenteData['docente_info']->nombre }} {{ $docenteData['docente_info']->apellido_paterno }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered mb-0">
                                <thead style="background-color: #f1f3fa;">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Curso</th>
                                        <th>Tema</th>
                                        <th>Aula</th>
                                        <th>Turno</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Horas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($docenteData['months'] as $monthData)
                                        @foreach($monthData['weeks'] as $weekData)
                                            @foreach($weekData['details'] as $detail)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($detail['fecha'])->format('d/m/Y') }}</td>
                                                    <td>{{ $detail['curso'] }}</td>
                                                    <td>{{ \App\Models\AsistenciaDocente::getPlainTema($detail['tema_desarrollado']) }}</td>
                                                    <td>{{ $detail['aula'] }}</td>
                                                    <td>{{ $detail['turno'] }}</td>
                                                    <td>{{ $detail['hora_entrada'] }}</td>
                                                    <td>{{ $detail['hora_salida'] }}</td>
                                                    <td>{{ number_format($detail['horas_dictadas'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </tbody>
                                <tfoot style="background-color: var(--shreyu-success); color: #ffffff; font-weight: 600;">
                                    <tr>
                                        <td colspan="7" class="text-end">TOTAL:</td>
                                        <td>{{ number_format($docenteData['total_horas'], 2) }}h</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="mdi mdi-close me-1"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Hidden div to store chart data --}}
<div id="chart-data-container" 
     data-fechas='@json(array_keys($asistenciaSemana))' 
     data-valores='@json(array_values($asistenciaSemana))'
     style="display: none;"></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/**
 * REPORTES DE ASISTENCIA DOCENTE - JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {

    // ── Inicializar todos los tooltips de Bootstrap (incluye la barra de rendimiento) ──
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(el =>
        new bootstrap.Tooltip(el, { html: true, trigger: 'hover', delay: { show: 200, hide: 100 } })
    );

    // ── Animación de parpadeo para la alerta de +30% faltas ──────────────────
    const style = document.createElement('style');
    style.textContent = `
        @keyframes blink {
            0%  { opacity: 1; }
            50% { opacity: 0.3; }
            100%{ opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    // ─────────────────────────────────────────────────────────────────────────


    // 0. Inicializar Select2 para búsqueda de docentes
    $('#docente_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar docente...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return 'No se encontraron docentes';
            },
            searching: function() {
                return 'Buscando...';
            }
        }
    });

    // 1. Lógica de filtros
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
    toggleMonthYearDisable();

    // 2. Gráfico de asistencia
    const ctx = document.getElementById('graficoSemanal');
    if (ctx) {
        const chartDataContainer = document.getElementById('chart-data-container');
        const fechasData = JSON.parse(chartDataContainer.dataset.fechas || '[]');
        const valoresData = JSON.parse(chartDataContainer.dataset.valores || '[]');

        const labelsGrafico = fechasData.map(fecha => {
            const date = new Date(fecha + 'T00:00:00');
            return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
        });

        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsGrafico,
                datasets: [{
                    label: 'Registros de Asistencia',
                    data: valoresData,
                    backgroundColor: 'rgba(16, 183, 89, 0.6)',
                    borderColor: 'rgb(16, 183, 89)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6c757d'
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#313a46',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#dee2e6',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
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
    }

    // 3. Animaciones de métricas
    const metricCards = document.querySelectorAll('.metric-card-reports');
    metricCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // 4. Contador animado para valores
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const valueElement = entry.target.querySelector('.metric-value');
                if (valueElement) {
                    animateCounter(valueElement);
                }
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    metricCards.forEach(card => observer.observe(card));

    function animateCounter(element) {
        const rawText = element.textContent.replace('S/ ', '').replace('h', '').replace(',', '');
        const targetValue = parseFloat(rawText);
        
        if (isNaN(targetValue) || targetValue <= 0) return;

        let current = 0;
        const increment = targetValue / 50;
        const hasMoneyFormat = element.textContent.includes('S/');
        const hasHoursFormat = element.textContent.includes('h');
        
        const timer = setInterval(() => {
            current += increment;
            
            if (current >= targetValue) {
                current = targetValue;
                clearInterval(timer);
            }
            
            let formattedValue = '';
            if (hasMoneyFormat) {
                formattedValue = 'S/ ' + current.toLocaleString('es-ES', { 
                    minimumFractionDigits: 2, 
                    maximumFractionDigits: 2 
                });
            } else if (hasHoursFormat) {
                formattedValue = current.toLocaleString('es-ES', { 
                    minimumFractionDigits: 1, 
                    maximumFractionDigits: 1 
                }) + 'h';
            } else {
                formattedValue = Math.floor(current);
            }
            
            element.textContent = formattedValue;
        }, 30);
    }

    // ═══════════════════════════════════════════════════════════════
    // GRÁFICOS DE RENDIMIENTO DOCENTE
    // ═══════════════════════════════════════════════════════════════

    // ── Configuración global de Chart.js ──────────────────────────
    Chart.defaults.font.family = "'Nunito', 'Inter', sans-serif";
    Chart.defaults.color = '#6c757d';

    // ── 1. Gráfico de Barras Comparativo (todos los docentes) ─────
    const compareEl = document.getElementById('graficoBarsComparativo');
    if (compareEl && window.grafChartData) {
        const labelsComp    = window.grafChartData.labels     || [];
        const asistenciaVal = window.grafChartData.asistencia || [];
        const temasVal      = window.grafChartData.temas      || [];
        const faltasVal     = window.grafChartData.faltas     || [];
        const tardanzasVal  = window.grafChartData.tardanzas  || [];

        // Acortar nombres largos eliminando títulos profesionales primero
        const labelsCortos = labelsComp.map(name => {
            // Eliminar prefijos de títulos comunes (incluyendo Blgo., Blga., Psic., Mtr.)
            const cleaned = name.replace(/^(Lic\.|Mgt\.|Dr\.|Dra\.|Ing\.|Abog\.|MSc\.|Bigo\.|Blgo\.|Blga\.|Psic\.|Psg\.|Mtr\.|Lic|Mgt|Dr|Dra|Ing|Abog|MSc|Bigo|Blgo|Blga|Psic|Psg|Mtr)\s+/i, '');
            const parts = cleaned.split(' ');
            return parts.length >= 2 ? parts[0] + ' ' + parts[1].charAt(0) + '.' : cleaned;
        });

        new Chart(compareEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labelsCortos,
                datasets: [
                    {
                        label: '% Asistencia',
                        data: asistenciaVal,
                        backgroundColor: 'rgba(16, 183, 89, 0.75)',
                        borderColor: 'rgba(16, 183, 89, 1)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: '% Temas OK',
                        data: temasVal,
                        backgroundColor: 'rgba(53, 184, 224, 0.75)',
                        borderColor: 'rgba(53, 184, 224, 1)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: '% Tardanzas',
                        data: tardanzasVal,
                        backgroundColor: 'rgba(230, 126, 34, 0.75)',
                        borderColor: 'rgba(230, 126, 34, 1)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: '% Faltas',
                        data: faltasVal,
                        backgroundColor: 'rgba(240, 80, 80, 0.75)',
                        borderColor: 'rgba(240, 80, 80, 1)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            padding: 20,
                            font: { size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#313a46',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#dee2e6',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            title: function(context) {
                                // Mostrar nombre completo en el tooltip
                                return labelsComp[context[0].dataIndex] || context[0].label;
                            },
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.parsed.y}%`;
                            }
                        }
                    },
                    // Línea de referencia al 30% de faltas
                    annotation: undefined
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: val => val + '%',
                            stepSize: 10,
                            color: '#98a6ad'
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: { color: '#6c757d', maxRotation: 35, minRotation: 0 },
                        grid: { display: false }
                    }
                },
                animation: {
                    duration: 900,
                    easing: 'easeOutQuart'
                }
            }
        });
    }

    // ── 2. Donas individuales por docente ──────────────────────────
    const coloresDonas = {
        completada : { bg: 'rgba(16,183,89,0.85)',  border: '#0d9448' },
        sintema    : { bg: 'rgba(249,200,81,0.85)', border: '#e6a817' },
        incompleta : { bg: 'rgba(155,89,182,0.85)', border: '#7b1fa2' },
        falta      : { bg: 'rgba(240,80,80,0.85)',  border: '#c0392b' },
        programada : { bg: 'rgba(222,226,230,0.85)',border: '#adb5bd' },
    };

    document.querySelectorAll('canvas[id^="donaDocente"]').forEach(canvas => {
        const cc  = parseInt(canvas.dataset.completada  || '0');
        const cst = parseInt(canvas.dataset.sintema     || '0');
        const ci  = parseInt(canvas.dataset.incompleta  || '0');
        const cf  = parseInt(canvas.dataset.falta       || '0');
        const cp  = parseInt(canvas.dataset.programada  || '0');

        const totalSes = cc + cst + ci + cf + cp;
        if (totalSes === 0) return;

        // Construir datasets solo con segmentos no cero
        const segs = [];
        if (cc  > 0) segs.push({ label: 'Completadas',  value: cc,  ...coloresDonas.completada  });
        if (cst > 0) segs.push({ label: 'Sin tema',      value: cst, ...coloresDonas.sintema     });
        if (ci  > 0) segs.push({ label: 'Incompleta',    value: ci,  ...coloresDonas.incompleta  });
        if (cf  > 0) segs.push({ label: 'Faltas',        value: cf,  ...coloresDonas.falta       });
        if (cp  > 0) segs.push({ label: 'Programadas',   value: cp,  ...coloresDonas.programada  });

        new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: segs.map(s => s.label),
                datasets: [{
                    data: segs.map(s => s.value),
                    backgroundColor: segs.map(s => s.bg),
                    borderColor: segs.map(s => s.border),
                    borderWidth: 1.5,
                    hoverOffset: 6,
                    spacing: 1,
                }]
            },
            options: {
                responsive: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#313a46',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 10,
                        borderColor: '#dee2e6',
                        borderWidth: 1,
                        callbacks: {
                            label: function(ctx) {
                                const pct = totalSes > 0 ? ((ctx.parsed / totalSes) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.parsed} ses. (${pct}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: false,
                    duration: 800,
                    easing: 'easeOutQuart'
                }
            }
        });
    });

});
</script>
@endpush