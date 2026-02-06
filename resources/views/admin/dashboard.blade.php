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
<script src="{{ asset('js/dashboard-progressive-loading.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const apiToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    try {
        const [generalData, adminData, anuncios] = await Promise.all([
            fetch('/api/dashboard/datos-generales', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken }}).then(r => r.json()),
            fetch('/api/dashboard/admin', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken }}).then(r => r.json()),
            fetch('/api/dashboard/anuncios', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken }}).then(r => r.json())
        ]);

        if (generalData.cicloActivo) renderBanner(generalData.cicloActivo);
        renderStats(generalData);
        
        if (adminData.estadisticasAsistencia) renderAsistencia(adminData.estadisticasAsistencia);
        if (adminData.postulaciones) renderPostulaciones(adminData.postulaciones);
        if (adminData.carnets) renderCarnets(adminData.carnets);
        if (adminData.alertas) renderAlertas(adminData.alertas);
        if (anuncios) renderAnuncios(anuncios);
        
        document.getElementById('dashboard-content').style.display = 'block';
    } catch (error) {
        console.error('Error:', error);
    }

    function renderBanner(ciclo) {
        const progreso = Math.round(ciclo.progreso_porcentaje || 0);
        const exam = ciclo.proximo_examen;
        
        document.getElementById('ciclo-banner').innerHTML = `
            <div class="col-12">
                <div class="card modern-card gradient-primary text-white border-0">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-${exam ? '8' : '12'}">
                                <h3 class="text-white mb-3"><i class="mdi mdi-school"></i> ${ciclo.nombre}</h3>
                                <div class="progress modern-progress mb-3">
                                    <div class="progress-bar bg-success" style="width: ${progreso}%">${progreso}%</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.15)">
                                            <small class="d-block text-white" style="opacity: 0.8">INICIO</small>
                                            <strong class="text-white">${ciclo.fecha_inicio}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.15)">
                                            <small class="d-block text-white" style="opacity: 0.8">FIN</small>
                                            <strong class="text-white">${ciclo.fecha_fin}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 rounded" style="background: rgba(255,255,255,0.15)">
                                            <small class="d-block text-white" style="opacity: 0.8">DÍAS RESTANTES</small>
                                            <strong class="text-white fs-5">${ciclo.dias_restantes}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ${exam ? `
                            <div class="col-md-4 mt-3 mt-md-0">
                                <div class="text-center p-3 rounded" style="background: rgba(255,255,255,0.2)">
                                    <i class="mdi mdi-calendar-alert text-white" style="font-size: 2.5rem"></i>
                                    <h5 class="text-white mt-2">Próximo Examen</h5>
                                    <h4 class="text-white">${exam.nombre}</h4>
                                    <p class="text-white mb-2"><i class="mdi mdi-calendar"></i> ${exam.fecha}</p>
                                    <span class="badge bg-warning text-dark" style="font-size: 0.9rem">
                                        En ${Math.round(exam.dias_faltantes)} días
                                    </span>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderStats(data) {
        const stats = [
            { title: 'Estudiantes', subtitle: 'Inscritos Activos', value: data.totalInscritosActivos || 0, icon: 'mdi-account-group', gradient: 'gradient-primary' },
            { title: 'Asistencia Hoy', subtitle: `${data.asistenciaHoy?.estudiantes_unicos || 0} presentes`, value: `${Math.round(data.asistenciaHoy?.porcentaje_asistencia || 0)}%`, icon: 'mdi-check-circle', gradient: 'gradient-success' },
            { title: 'Docentes', subtitle: 'Activos en el Ciclo', value: data.totalDocentesActivos || 0, icon: 'mdi-account-tie', gradient: 'gradient-info' },
            { title: 'Aulas', subtitle: 'En Uso', value: data.totalAulasAsignadas || 0, icon: 'mdi-door', gradient: 'gradient-warning' }
        ];

        document.getElementById('stats-cards').innerHTML = stats.map(s => `
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card modern-card border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="widget-icon-modern ${s.gradient} text-white me-3">
                                <i class="mdi ${s.icon}"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase" style="font-size: 10px">${s.title}</small>
                                <h2 class="stat-number mb-0">${s.value}</h2>
                                <small class="text-muted">${s.subtitle}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderAsistencia(stats) {
        const items = [
            { label: 'Regulares', count: stats.regulares, pct: stats.porcentaje_regulares, color: 'vibrant-success', bg: '#2ec4b615' },
            { label: 'Amonestados', count: stats.amonestados, pct: stats.porcentaje_amonestados, color: 'vibrant-warning', bg: '#ff9f1c15' },
            { label: 'Inhabilitados', count: stats.inhabilitados, pct: stats.porcentaje_inhabilitados, color: 'vibrant-danger', bg: '#f7258515' },
            { label: 'Sin Asistencia', count: stats.sin_asistencia, pct: stats.porcentaje_sin_asistencia, color: 'vibrant-dark', bg: '#2b2d4215' }
        ];

        document.getElementById('asistencia-chart').innerHTML = items.map(i => `
            <div class="mb-3 p-3 rounded modern-card" style="background: ${i.bg}; border: none !important;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="status-badge-vibrant bg-${i.color} shadow-sm">
                        <i class="mdi mdi-account-check me-1"></i> ${i.label}
                    </span>
                    <span class="fw-extrabold text-${i.color}" style="font-size: 1.1rem;">
                        ${i.count} <small class="text-muted fw-normal">(${i.pct}%)</small>
                    </span>
                </div>
                <div class="progress modern-progress shadow-inner">
                    <div class="progress-bar bg-${i.color} progress-bar-striped progress-bar-animated" 
                         style="width: ${i.pct}%">
                    </div>
                </div>
            </div>
        `).join('') + `
            <div class="d-flex justify-content-between align-items-center mt-4 p-3 rounded bg-light border">
                <span class="text-muted fw-bold">TOTAL INSCRITOS ACTUAMENTE:</span>
                <span class="h4 mb-0 fw-black text-primary">${stats.total_estudiantes}</span>
            </div>`;
    }

    function renderPostulaciones(p) {
        const items = [
            { label: 'Total', value: p.total, color: 'primary' },
            { label: 'Pendientes', value: p.pendientes, color: 'warning' },
            { label: 'Aprobadas', value: p.aprobadas, color: 'success' },
            { label: 'Rechazadas', value: p.rechazadas, color: 'danger' }
        ];

        document.getElementById('postulaciones-stats').innerHTML = '<div class="row g-2">' + items.map(i => `
            <div class="col-6">
                <div class="text-center p-2 rounded bg-${i.color} bg-opacity-10">
                    <h4 class="mb-0 text-${i.color}">${i.value}</h4>
                    <small class="text-muted">${i.label}</small>
                </div>
            </div>
        `).join('') + '</div>';
    }

    function renderCarnets(c) {
        const items = [
            { label: 'Generados', value: c.total, icon: 'mdi-card', color: 'primary' },
            { label: 'Pend. Impresión', value: c.pendientes_impresion, icon: 'mdi-printer', color: 'warning' },
            { label: 'Pend. Entrega', value: c.pendientes_entrega, icon: 'mdi-package', color: 'info' },
            { label: 'Entregados', value: c.entregados, icon: 'mdi-check', color: 'success' }
        ];

        document.getElementById('carnets-stats').innerHTML = items.map(i => `
            <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded bg-${i.color} bg-opacity-10">
                <span><i class="mdi ${i.icon} text-${i.color}"></i> ${i.label}</span>
                <span class="badge bg-${i.color}">${i.value}</span>
            </div>
        `).join('');
    }

    function renderAlertas(alertas) {
        if (!alertas || alertas.length === 0) {
            document.getElementById('alertas-content').innerHTML = '<div class="alert alert-success mb-0"><i class="mdi mdi-check-circle"></i> No hay alertas</div>';
            return;
        }

        document.getElementById('alertas-content').innerHTML = alertas.map(a => `
            <div class="alert alert-${a.tipo} mb-2">
                <i class="${a.icono} me-2"></i> ${a.mensaje}
                ${a.url !== '#' ? `<a href="${a.url}" class="alert-link ms-2">Ver más →</a>` : ''}
            </div>
        `).join('');
    }

    function renderAnuncios(anuncios) {
        if (!anuncios || anuncios.length === 0) {
            document.getElementById('anuncios-content').innerHTML = '<p class="text-muted small">No hay anuncios</p>';
            return;
        }

        document.getElementById('anuncios-content').innerHTML = anuncios.slice(0, 3).map(a => {
            const fecha = new Date(a.fecha_publicacion).toLocaleDateString('es-ES');
            return `
                <div class="border-bottom pb-2 mb-2">
                    <h6 class="mb-1"><i class="mdi mdi-bullhorn-outline text-success"></i> ${a.titulo}</h6>
                    <small class="text-muted">${fecha}</small>
                    <p class="mb-0 small mt-1">${a.contenido.substring(0, 80)}...</p>
                </div>
            `;
        }).join('');
    }
});
</script>
@endpush