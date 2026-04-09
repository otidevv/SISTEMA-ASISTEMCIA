@extends('layouts.app')

@section('title', 'Dashboard Operativo')

@section('content')
<style>
    /* Diseño Institucional Vanguardista */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    .gradient-hero { 
        background: var(--cepre-pink) !important; 
        border-radius: 20px !important;
        box-shadow: 0 15px 35px rgba(236, 0, 140, 0.25) !important;
        position: relative;
        overflow: hidden;
        transition: all 0.5s ease;
    }

    .gradient-hero::after {
        content: "";
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .modern-card {
        border-radius: 16px !important;
        border: 1px solid var(--glass-border) !important;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
        background: var(--glass-bg);
    }

    [data-layout-mode="dark"] .modern-card {
        background: rgba(43, 60, 75, 0.9);
    }

    .modern-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
    }

    .kpi-icon-container {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        background: rgba(255,255,255,0.25);
        backdrop-filter: blur(5px);
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 850;
        letter-spacing: -1px;
    }

    .progress-custom {
        height: 12px;
        border-radius: 20px;
        background: rgba(0,0,0,0.05);
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }

    .shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    .exam-badge {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 10px 15px;
    }

    /* Línea de Tiempo de Hitos */
    .timeline-milestones {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-top: 30px;
        padding-top: 20px;
    }

    .timeline-milestones::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
    }

    .milestone-point {
        position: relative;
        text-align: center;
        width: 100px;
    }

    .milestone-point::before {
        content: "";
        position: absolute;
        top: -24px;
        left: 50%;
        transform: translateX(-50%);
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: white;
        border: 3px solid var(--cepre-pink);
        z-index: 2;
        transition: all 0.3s ease;
    }

    .milestone-point.completed::before {
        background: var(--cepre-green);
        border-color: white;
    }

    .milestone-point.next::before {
        background: #fff;
        border-color: white;
        box-shadow: 0 0 10px rgba(255,255,255,0.8);
        width: 16px;
        height: 16px;
        top: -26px;
    }

    .milestone-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: rgba(255,255,255,0.9);
        display: block;
        text-transform: uppercase;
    }

    .milestone-date {
        font-size: 0.7rem;
        color: rgba(255,255,255,0.7);
    }

    /* Botones de Filtro de Ciclo */
    .cycle-btn {
        border: 1px solid #e0e0e0;
        background: white;
        color: #666;
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-right: 8px;
        margin-bottom: 8px;
    }

    .cycle-btn.active {
        background: var(--cepre-pink);
        color: white;
        border-color: var(--cepre-pink);
        box-shadow: 0 4px 12px rgba(236, 0, 140, 0.2);
    }
    
    .cycle-btn:hover:not(.active) {
        background: #f8f9fa;
        border-color: var(--cepre-pink);
        color: var(--cepre-pink);
    }
</style>

<div class="container-fluid">
    <!-- Título y Selector de Ciclos -->
    <div class="row align-items-center mb-4 mt-2">
        <div class="col-xl-6">
            <h3 class="fw-bold mb-1">
                <i class="mdi mdi-view-dashboard-variant-outline text-primary me-2"></i>
                Panel de Control <span class="text-primary">Operativo</span>
            </h3>
            <p class="text-muted mb-0">Selecciona un ciclo para filtrar los datos en tiempo real:</p>
        </div>
        <div class="col-xl-6 text-xl-end mt-3 mt-xl-0">
            <div id="cycle-selector" class="d-flex flex-wrap justify-content-xl-end">
                <button class="cycle-btn active" data-id="global">
                    <i class="mdi mdi-earth me-1"></i> Global Activos
                </button>
                @foreach($ciclosActivos as $ciclo)
                    <button class="cycle-btn" data-id="{{ $ciclo->id }}">
                        <i class="mdi mdi-school-outline me-1"></i> {{ $ciclo->nombre }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Ciclo Banner Hero -->
    <div id="ciclo-banner" class="row mb-4">
        <div class="col-12">
            <div class="card modern-card border-0 overflow-hidden shadow-none">
                <div class="card-body p-5 border-0 rounded-4 shimmer" style="height: 200px;"></div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div id="stats-cards" class="row">
        @for($i=0; $i<4; $i++)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card modern-card shimmer" style="height: 120px;"></div>
        </div>
        @endfor
    </div>

    <!-- Contenido Principal Dinámico -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card modern-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="mdi mdi-account-alert-outline text-danger me-2"></i>
                            Situación de Estudiantes (Riesgo Activo)
                        </h5>
                    </div>
                    <div id="asistencia-chart">
                        <div class="shimmer mb-3 rounded-4" style="height: 80px;"></div>
                        <div class="shimmer mb-3 rounded-4" style="height: 80px;"></div>
                        <div class="shimmer rounded-4" style="height: 80px;"></div>
                    </div>
                </div>
            </div>

            <div class="card modern-card">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="mdi mdi-bell-badge-outline text-warning me-2"></i>
                        Alertas del Sistema
                    </h5>
                    <div id="alertas-content">
                        <div class="shimmer mb-2 rounded-3" style="height: 60px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card modern-card mb-4 bg-primary bg-opacity-10 border-primary border-opacity-25">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-primary mb-3 text-uppercase">Logística de Ingreso</h6>
                    <div id="postulaciones-stats">
                        <div class="shimmer rounded-3" style="height: 100px;"></div>
                    </div>
                </div>
            </div>

            <div class="card modern-card mb-4 border-dashed">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3 text-uppercase">Gestión de Documentos</h6>
                    <div id="carnets-stats">
                        <div class="shimmer rounded-3" style="height: 120px;"></div>
                    </div>
                </div>
            </div>

            <div class="card modern-card">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-success mb-3 text-uppercase">Últimos Anuncios</h6>
                    <div id="anuncios-content">
                        <div class="shimmer mb-2 rounded-2" style="height: 80px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/dashboard-progressive-loading.js')
@endpush