@extends('layouts.app')

@section('title', 'Dashboard Administrativo')

@section('content')
<style>
    /* Colores Institucionales Premium */
    .gradient-primary { background: linear-gradient(135deg, var(--cepre-pink) 0%, #ff4fb1 100%) !important; color: white !important; }
    .gradient-success { background: linear-gradient(135deg, var(--cepre-green) 0%, #6da12c 100%) !important; color: white !important; }
    .gradient-info { background: linear-gradient(135deg, var(--cepre-blue) 0%, #0081b1 100%) !important; color: white !important; }
    .gradient-warning { background: linear-gradient(135deg, #f72585 0%, #ff4d6d 100%) !important; color: white !important; }
    .gradient-dark { background: linear-gradient(135deg, var(--cepre-dark-blue) 0%, #1a3a49 100%) !important; color: white !important; }
    
    .modern-card {
        border-radius: 12px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border: none !important;
        overflow: hidden;
    }

    /* Sombras según el modo */
    html[data-bs-theme="light"] .modern-card, body:not([data-layout-mode="dark"]) .modern-card {
        box-shadow: 0 10px 30px -12px rgba(0,0,0,0.1) !important;
    }
    
    .modern-card:hover { 
        transform: translateY(-8px) !important; 
    }

    html[data-bs-theme="light"] .modern-card:hover, body:not([data-layout-mode="dark"]) .modern-card:hover {
        box-shadow: 0 20px 40px -15px rgba(236, 0, 140, 0.15) !important; 
    }
    
    .widget-icon-modern {
        width: 52px !important; height: 52px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 24px !important;
        box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important;
    }
    
    .stat-number { font-size: 2rem !important; font-weight: 800 !important; }
    
    html[data-bs-theme="light"] .stat-number, body:not([data-layout-mode="dark"]) .stat-number {
        color: var(--cepre-dark-blue);
    }

    .modern-progress { height: 10px !important; border-radius: 10px !important; background: rgba(0,0,0,0.05) !important; overflow: hidden; }
    .modern-progress .progress-bar { border-radius: 10px !important; transition: width 1s ease-in-out; }

    /* Estados de asistencia con colores de marca */
    .status-badge-vibrant { padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: white !important; }
    .bg-vibrant-success { background: var(--cepre-green) !important; }
    .bg-vibrant-warning { background: #fd7e14 !important; }
    .bg-vibrant-danger { background: var(--cepre-pink) !important; }
    .bg-vibrant-dark { background: var(--cepre-dark-blue) !important; }
    .text-vibrant-success { color: var(--cepre-green) !important; }
    .text-vibrant-warning { color: #fd7e14 !important; }
    .text-vibrant-danger { color: var(--cepre-pink) !important; }
    .text-vibrant-dark { color: var(--cepre-dark-blue) !important; }
</style>

<!-- Page Title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title"><i class="mdi mdi-view-dashboard-outline"></i> Dashboard Administrativo</h4>
        </div>
    </div>
</div>

<!-- Ciclo Banner -->
<div id="ciclo-banner" class="row mb-4">
    <div class="col-12">
        <div class="skeleton" style="height: 150px;"></div>
    </div>
</div>

<!-- Stats Cards -->
<div id="stats-cards" class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3"><div class="skeleton" style="height: 120px;"></div></div>
    <div class="col-xl-3 col-md-6 mb-3"><div class="skeleton" style="height: 120px;"></div></div>
    <div class="col-xl-3 col-md-6 mb-3"><div class="skeleton" style="height: 120px;"></div></div>
    <div class="col-xl-3 col-md-6 mb-3"><div class="skeleton" style="height: 120px;"></div></div>
</div>

<!-- Main Content -->
<div id="dashboard-content">
    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="modern-card">
                <div class="card-body p-4">
                    <h5 class="mb-4"><i class="mdi mdi-chart-bar text-primary"></i> Estadísticas de Asistencia</h5>
                    <div id="asistencia-chart">
                        <!-- Skeleton loaders -->
                        <div class="skeleton" style="height: 80px; margin-bottom: 15px;"></div>
                        <div class="skeleton" style="height: 80px; margin-bottom: 15px;"></div>
                        <div class="skeleton" style="height: 80px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="modern-card mb-4">
                <div class="card-body p-4">
                    <h6 class="mb-3"><i class="mdi mdi-clipboard-text text-info"></i> Postulaciones</h6>
                    <div id="postulaciones-stats">
                        <div class="skeleton" style="height: 30px; margin-bottom: 10px;"></div>
                        <div class="skeleton" style="height: 30px; margin-bottom: 10px;"></div>
                        <div class="skeleton" style="height: 30px;"></div>
                    </div>
                </div>
            </div>

            <div class="modern-card mb-4">
                <div class="card-body p-4">
                    <h6 class="mb-3"><i class="mdi mdi-card-account-details text-warning"></i> Carnets</h6>
                    <div id="carnets-stats">
                        <div class="skeleton" style="height: 30px; margin-bottom: 10px;"></div>
                        <div class="skeleton" style="height: 30px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="modern-card">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="mdi mdi-bell-ring text-danger"></i> Alertas</h5>
                    <div id="alertas-content">
                        <div class="skeleton" style="height: 60px; margin-bottom: 10px;"></div>
                        <div class="skeleton" style="height: 60px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="modern-card">
                <div class="card-body p-4">
                    <h6 class="mb-3"><i class="mdi mdi-bullhorn text-success"></i> Anuncios</h6>
                    <div id="anuncios-content">
                        <div class="skeleton" style="height: 80px; margin-bottom: 10px;"></div>
                        <div class="skeleton" style="height: 80px;"></div>
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