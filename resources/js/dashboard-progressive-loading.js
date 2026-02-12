// Dashboard Progressive Loading - Carga optimizada por AJAX
document.addEventListener('DOMContentLoaded', async function () {
    const apiToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!apiToken) {
        console.error('❌ CSRF token no encontrado');
        showGlobalError('Error de configuración. Por favor, recarga la página.');
        return;
    }

    try {
        console.log('📊 Iniciando carga del dashboard...');

        // Cargar TODOS los datos en paralelo
        const timestamp = new Date().getTime();
        const [generalData, adminData, anuncios] = await Promise.all([
            fetch(`/api/dashboard/datos-generales?_=${timestamp}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': apiToken,
                    'Cache-Control': 'no-cache',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(async r => {
                if (!r.ok) {
                    const error = await r.text();
                    console.error('Error datos generales:', error);
                    throw new Error(`HTTP ${r.status}`);
                }
                return r.json();
            }),

            fetch(`/api/dashboard/admin?_=${timestamp}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': apiToken,
                    'Cache-Control': 'no-cache',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(async r => {
                if (!r.ok) {
                    const error = await r.text();
                    console.error('Error datos admin:', error);
                    throw new Error(`HTTP ${r.status}`);
                }
                return r.json();
            }),

            fetch(`/api/dashboard/anuncios?_=${timestamp}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': apiToken,
                    'Cache-Control': 'no-cache',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(async r => {
                if (!r.ok) {
                    const error = await r.text();
                    console.error('Error anuncios:', error);
                    return []; // Anuncios no es crítico
                }
                return r.json();
            })
        ]);

        console.log('✅ Datos cargados exitosamente');

        // Renderizar banner
        if (generalData && generalData.cicloActivo) {
            renderBanner(generalData.cicloActivo);
        } else {
            renderNoCiclo();
        }

        // Renderizar stats
        if (generalData) {
            renderStats(generalData);
        }

        // Renderizar datos administrativos
        if (adminData) {
            // Estadísticas de asistencia (ya vienen en adminData)
            if (adminData.estadisticasAsistencia) {
                renderAsistenciaStats(adminData.estadisticasAsistencia);
            } else {
                document.getElementById('asistencia-chart').innerHTML =
                    '<div class="alert alert-info mb-0"><i class="mdi mdi-information"></i> No hay datos de asistencia disponibles</div>';
            }

            // Postulaciones
            if (adminData.postulaciones) {
                renderPostulaciones(adminData.postulaciones);
            }

            // Carnets
            if (adminData.carnets) {
                renderCarnets(adminData.carnets);
            }

            // Alertas
            if (adminData.alertas && adminData.alertas.length > 0) {
                renderAlertas(adminData.alertas);
            } else {
                document.getElementById('alertas-content').innerHTML =
                    '<div class="alert alert-success mb-0"><i class="mdi mdi-check-circle"></i> No hay alertas pendientes</div>';
            }
        }

        // Renderizar anuncios
        if (anuncios && anuncios.length > 0) {
            renderAnuncios(anuncios);
        } else {
            document.getElementById('anuncios-content').innerHTML =
                '<p class="text-muted small text-center py-3"><i class="mdi mdi-bullhorn-outline"></i> No hay anuncios activos</p>';
        }

        // Mostrar dashboard
        const dashboardContent = document.getElementById('dashboard-content');
        if (dashboardContent) {
            dashboardContent.style.display = 'block';
        }

        console.log('✅ Dashboard renderizado completamente');

    } catch (error) {
        console.error('💥 Error crítico al cargar dashboard:', error);
        showGlobalError('Error al cargar el dashboard. Por favor, recarga la página.');
    }

    // ==================== FUNCIONES DE RENDERIZADO ====================

    function showGlobalError(message) {
        const cicloBanner = document.getElementById('ciclo-banner');
        if (cicloBanner) {
            cicloBanner.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="mdi mdi-alert-circle me-2"></i>
                        <strong>Error:</strong> ${message}
                    </div>
                </div>
            `;
        }
    }

    function renderNoCiclo() {
        document.getElementById('ciclo-banner').innerHTML = `
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="mdi mdi-information me-2"></i>
                    <strong>Atención:</strong> No hay un ciclo activo en este momento.
                </div>
            </div>
        `;
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
                                <h3 class="text-white mb-3">
                                    <i class="mdi mdi-school"></i> ${ciclo.nombre}
                                </h3>
                                <div class="progress modern-progress mb-3">
                                    <div class="progress-bar bg-success" style="width: ${progreso}%">
                                        ${progreso}%
                                    </div>
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
                                    <p class="text-white mb-2">
                                        <i class="mdi mdi-calendar"></i> ${exam.fecha}
                                    </p>
                                    <span class="badge bg-warning text-dark" style="font-size: 0.9rem">
                                        En ${Math.abs(Math.round(exam.dias_faltantes))} días
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
            {
                title: 'Estudiantes',
                subtitle: 'Inscritos Activos',
                value: data.totalInscritosActivos || 0,
                icon: 'mdi-account-group',
                gradient: 'gradient-primary'
            },
            {
                title: 'Asistencia Hoy',
                subtitle: `${data.asistenciaHoy?.estudiantes_unicos || 0} presentes`,
                value: `${Math.round(data.asistenciaHoy?.porcentaje_asistencia || 0)}%`,
                icon: 'mdi-check-circle',
                gradient: 'gradient-success'
            },
            {
                title: 'Docentes',
                subtitle: 'Activos en el Ciclo',
                value: data.totalDocentesActivos || 0,
                icon: 'mdi-account-tie',
                gradient: 'gradient-info'
            },
            {
                title: 'Aulas',
                subtitle: 'Asignadas',
                value: data.totalAulasAsignadas || 0,
                icon: 'mdi-door',
                gradient: 'gradient-warning'
            }
        ];

        document.getElementById('stats-cards').innerHTML = stats.map(s => `
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card modern-card ${s.gradient} text-white border-0">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="widget-icon-modern bg-white bg-opacity-25 text-white me-3">
                                <i class="mdi ${s.icon}"></i>
                            </div>
                            <div>
                                <small class="text-white text-uppercase" style="font-size: 10px; opacity: 0.9">
                                    ${s.title}
                                </small>
                                <h2 class="stat-number mb-0 text-white">${s.value}</h2>
                                <small class="text-white" style="opacity: 0.9">${s.subtitle}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderAsistenciaStats(stats) {
        const items = [
            {
                label: 'Regulares',
                count: stats.regulares,
                pct: stats.porcentaje_regulares,
                color: 'success',
                bg: '#11998e15'
            },
            {
                label: 'Amonestados',
                count: stats.amonestados,
                pct: stats.porcentaje_amonestados,
                color: 'warning',
                bg: '#f093fb15'
            },
            {
                label: 'Inhabilitados',
                count: stats.inhabilitados,
                pct: stats.porcentaje_inhabilitados,
                color: 'danger',
                bg: '#fa709a15'
            }
        ];

        const html = items.map(i => `
            <div class="mb-3 p-3 rounded" style="background: ${i.bg}">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-${i.color}">
                        <i class="mdi mdi-circle"></i> ${i.label}
                    </span>
                    <span class="badge bg-${i.color}">${i.count} (${i.pct}%)</span>
                </div>
                <div class="progress" style="height: 25px">
                    <div class="progress-bar bg-${i.color}" style="width: ${i.pct}%">
                        ${i.pct}%
                    </div>
                </div>
            </div>
        `).join('') + `
            <div class="alert alert-light mb-0">
                <strong>Total Estudiantes:</strong> ${stats.total_estudiantes}
            </div>
        `;

        document.getElementById('asistencia-chart').innerHTML = html;
    }

    function renderPostulaciones(p) {
        const items = [
            { label: 'Total', value: p.total, color: 'primary' },
            { label: 'Pendientes', value: p.pendientes, color: 'warning' },
            { label: 'Aprobadas', value: p.aprobadas, color: 'success' },
            { label: 'Rechazadas', value: p.rechazadas, color: 'danger' }
        ];

        document.getElementById('postulaciones-stats').innerHTML = '<div class="row g-2">' +
            items.map(i => `
                <div class="col-6">
                    <div class="text-center p-2 rounded bg-${i.color} bg-opacity-10">
                        <h4 class="mb-0 text-${i.color}">${i.value}</h4>
                        <small class="text-muted">${i.label}</small>
                    </div>
                </div>
            `).join('') +
            '</div>';
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
                <span>
                    <i class="mdi ${i.icon} text-${i.color}"></i> ${i.label}
                </span>
                <span class="badge bg-${i.color}">${i.value}</span>
            </div>
        `).join('');
    }

    function renderAlertas(alertas) {
        document.getElementById('alertas-content').innerHTML = alertas.map(a => `
            <div class="alert alert-${a.tipo} mb-2">
                <i class="${a.icono} me-2"></i> ${a.mensaje}
                ${a.url && a.url !== '#' ? `<a href="${a.url}" class="alert-link ms-2">Ver más →</a>` : ''}
            </div>
        `).join('');
    }

    function renderAnuncios(anuncios) {
        document.getElementById('anuncios-content').innerHTML = anuncios.slice(0, 3).map(a => {
            const fecha = new Date(a.fecha_publicacion).toLocaleDateString('es-PE', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            return `
                <div class="border-bottom pb-2 mb-2">
                    <h6 class="mb-1">
                        <i class="mdi mdi-bullhorn-outline text-success"></i> ${a.titulo}
                    </h6>
                    <small class="text-muted d-block mb-1">
                        <i class="mdi mdi-calendar"></i> ${fecha}
                    </small>
                    <p class="mb-0 small mt-1 text-muted">
                        ${a.contenido.substring(0, 100)}${a.contenido.length > 100 ? '...' : ''}
                    </p>
                </div>
            `;
        }).join('');
    }
});