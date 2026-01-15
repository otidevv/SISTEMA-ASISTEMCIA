@extends('layouts.app')

@section('title', 'Dashboard Administrativo')

@section('content')
<style>
    .gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
    .gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important; }
    .gradient-info { background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%) !important; }
    .gradient-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
    
    .modern-card {
        border-radius: 15px !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        transition: transform 0.3s !important;
        border: none !important;
    }
    .modern-card:hover { transform: translateY(-3px) !important; box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important; }
    
    .widget-icon-modern {
        width: 55px !important; height: 55px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 24px !important;
    }
    
    .stat-number { font-size: 2.2rem !important; font-weight: 700 !important; }
    .modern-progress { height: 28px !important; border-radius: 14px !important; background: rgba(255,255,255,0.2) !important; }
    .modern-progress .progress-bar { border-radius: 14px !important; font-weight: 600 !important; }
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
<div id="ciclo-banner" class="row mb-4"></div>

<!-- Stats Cards -->
<div id="stats-cards" class="row mb-4"></div>

<!-- Main Content -->
<div id="dashboard-content" style="display: none;">
    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="modern-card">
                <div class="card-body p-4">
                    <h5 class="mb-4"><i class="mdi mdi-chart-bar text-primary"></i> Estadísticas de Asistencia</h5>
                    <div id="asistencia-chart"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="modern-card mb-4">
                <div class="card-body p-4">
                    <h6 class="mb-3"><i class="mdi mdi-clipboard-text text-info"></i> Postulaciones</h6>
                    <div id="postulaciones-stats"></div>
                </div>
            </div>

            <div class="modern-card mb-4">
                <div class="card-body p-4">
                    <h6 class="mb-3"><i class="mdi mdi-card-account-details text-warning"></i> Carnets</h6>
                    <div id="carnets-stats"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 mb-4">
            <div class="modern-card">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="mdi mdi-bell-ring text-danger"></i> Alertas</h5>
                    <div id="alertas-content"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="modern-card">
                <div class="card-body p-4">
                    <h6 class="mb-3"><i class="mdi mdi-bullhorn text-success"></i> Anuncios</h6>
                    <div id="anuncios-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const apiToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    try {
        const [generalData, adminData, anuncios] = await Promise.all([
            fetch('/api/dashboard/datos-generales', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken }}).then(r => r.json()),
            fetch('/api/dashboard/admin', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken }}).then(r => r.json()),
            fetch('/api/dashboard/anuncios', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken }}).then(r => r.json())
        ]);

        if (generalData.cicloActivo) renderBanner(generalData.cicloActivo);
        renderStats(generalData);
        
        if (adminData.estadisticasAsistencia) {
            renderAsistencia(adminData.estadisticasAsistencia);
        } else {
            document.getElementById('asistencia-chart').innerHTML = '<div class="alert alert-info">📊 No hay datos de asistencia</div>';
        }
        
        if (adminData.postulaciones) {
            renderPostulaciones(adminData.postulaciones);
        } else {
            document.getElementById('postulaciones-stats').innerHTML = '<div class="alert alert-info">📋 Sin postulaciones</div>';
        }
        
        if (adminData.carnets) {
            renderCarnets(adminData.carnets);
        } else {
            document.getElementById('carnets-stats').innerHTML = '<div class="alert alert-info">🆔 Sin carnets</div>';
        }
        
        if (adminData.alertas && adminData.alertas.length > 0) {
            renderAlertas(adminData.alertas);
        } else {
            document.getElementById('alertas-content').innerHTML = '<div class="alert alert-success mb-0"><i class="mdi mdi-check-circle"></i> No hay alertas</div>';
        }
        
        if (anuncios && anuncios.length > 0) {
            renderAnuncios(anuncios);
        } else {
            document.getElementById('anuncios-content').innerHTML = '<p class="text-muted small text-center py-3">📢 No hay anuncios activos</p>';
        }
        
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
            { label: 'Regulares', count: stats.regulares, pct: stats.porcentaje_regulares, color: 'success', bg: '#11998e15' },
            { label: 'Amonestados', count: stats.amonestados, pct: stats.porcentaje_amonestados, color: 'warning', bg: '#f093fb15' },
            { label: 'Inhabilitados', count: stats.inhabilitados, pct: stats.porcentaje_inhabilitados, color: 'danger', bg: '#fa709a15' }
        ];

        document.getElementById('asistencia-chart').innerHTML = items.map(i => `
            <div class="mb-3 p-3 rounded" style="background: ${i.bg}">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-${i.color}"><i class="mdi mdi-circle"></i> ${i.label}</span>
                    <span class="badge bg-${i.color}">${i.count} (${i.pct}%)</span>
                </div>
                <div class="progress" style="height: 25px">
                    <div class="progress-bar bg-${i.color}" style="width: ${i.pct}%">${i.pct}%</div>
                </div>
            </div>
        `).join('') + `<div class="alert alert-light mb-0"><strong>Total:</strong> ${stats.total_estudiantes}</div>`;
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