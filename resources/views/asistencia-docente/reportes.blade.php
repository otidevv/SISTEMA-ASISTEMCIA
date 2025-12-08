@extends('layouts.app')

@section('title', 'Reportes de Asistencia Docente')

@push('css')
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
}
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
                                <label for="docente_id" class="form-label fw-semibold">Docente</label>
                                <select class="form-select" id="docente_id" name="docente_id">
                                    <option value="">Todos los Docentes</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ (string)$selectedDocenteId === (string)$docente->id ? 'selected' : '' }}>
                                            {{ $docente->nombre }} {{ $docente->apellido_paterno }}
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

                            <div class="col-lg-6 col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="mdi mdi-file-excel me-1"></i> Exportar Datos
                                </label>
                                <a href="{{ route('asistencia-docente.exportar', [
                                    'docente_id' => $selectedDocenteId,
                                    'mes' => $selectedMonth,
                                    'anio' => $selectedYear,
                                    'fecha_inicio' => $fechaInicio,
                                    'fecha_fin' => $fechaFin,
                                    'ciclo_academico' => $selectedCicloAcademico
                                ]) }}" class="btn btn-success w-100" id="exportBtn">
                                    <i class="mdi mdi-download me-1"></i> Descargar Excel con Filtros Actuales
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
                                        <td style="padding: 1rem 0.75rem; border: none;"></td>
                                    </tr>
                                </tbody>
                            </table>
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
                                                    <td>{{ $detail['tema_desarrollado'] }}</td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/**
 * REPORTES DE ASISTENCIA DOCENTE - JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endpush