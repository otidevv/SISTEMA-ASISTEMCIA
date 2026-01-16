// Dashboard Progressive Loading - TODO el contenido carga por AJAX
document.addEventListener('DOMContentLoaded', async function () {
    const apiToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        // Cargar TODOS los datos en paralelo
        const timestamp = new Date().getTime();
        const [generalData, adminData, anuncios] = await Promise.all([
            fetch(`/api/dashboard/datos-generales?_=${timestamp}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken, 'Cache-Control': 'no-cache' }
            }).then(r => r.json()),
            fetch(`/api/dashboard/admin?_=${timestamp}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken, 'Cache-Control': 'no-cache' }
            }).then(r => r.json()),
            fetch(`/api/dashboard/anuncios?_=${timestamp}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken, 'Cache-Control': 'no-cache' }
            }).then(r => r.json())
        ]);

        // Renderizar banner
        if (generalData.cicloActivo) {
            renderBanner(generalData.cicloActivo);
        }

        // Renderizar stats
        renderStats(generalData);

        // Renderizar postulaciones
        if (adminData.postulaciones) {
            renderPostulaciones(adminData.postulaciones);
        }

        // Renderizar carnets
        if (adminData.carnets) {
            renderCarnets(adminData.carnets);
        }

        // Renderizar alertas
        if (adminData.alertas && adminData.alertas.length > 0) {
            renderAlertas(adminData.alertas);
        } else {
            document.getElementById('alertas-content').innerHTML = '<div class="alert alert-success mb-0"><i class="mdi mdi-check-circle"></i> No hay alertas</div>';
        }

        // Renderizar anuncios
        if (anuncios && anuncios.length > 0) {
            renderAnuncios(anuncios);
        } else {
            document.getElementById('anuncios-content').innerHTML = '<p class="text-muted small text-center py-3">📢 No hay anuncios activos</p>';
        }

        // Cargar estadísticas de asistencia (lo más pesado)
        loadEstadisticasAsistencia(apiToken, timestamp);

    } catch (error) {
        console.error('Error loading dashboard:', error);
    }

    // Cargar estadísticas de asistencia por separado
    async function loadEstadisticasAsistencia(apiToken, timestamp) {
        const chartContainer = document.getElementById('asistencia-chart');
        if (!chartContainer) return;

        try {
            const response = await fetch(`/api/dashboard/admin/estadisticas-asistencia?_=${timestamp}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': apiToken,
                    'Cache-Control': 'no-cache'
                }
            });

            if (!response.ok) throw new Error('Error en la respuesta del servidor');

            const data = await response.json();

            if (data.estadisticas) {
                renderAsistenciaStats(data.estadisticas);
            } else {
                chartContainer.innerHTML = '<div class="alert alert-info">📊 No hay datos de asistencia</div>';
            }
        } catch (error) {
            console.error('Error loading attendance statistics:', error);
            chartContainer.innerHTML = '<div class="alert alert-warning">⚠️ Error al cargar estadísticas</div>';
        }
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
            { title: 'Cursos', subtitle: 'Programados', value: data.totalCursosActivos || 0, icon: 'mdi-book-open-page-variant', gradient: 'gradient-warning' }
        ];

        document.getElementById('stats-cards').innerHTML = stats.map(s => `
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card modern-card ${s.gradient} text-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="widget-icon-modern bg-white bg-opacity-25 text-white me-3">
                                <i class="mdi ${s.icon}"></i>
                            </div>
                            <div>
                                <small class="text-white text-uppercase" style="font-size: 10px">${s.title}</small>
                                <h2 class="stat-number mb-0 text-white">${s.value}</h2>
                                <small class="text-white">${s.subtitle}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderAsistenciaStats(stats) {
        const html = `
            <div class="mb-3 p-3 rounded" style="background: #11998e15">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-success"><i class="mdi mdi-circle"></i> Regulares</span>
                    <span class="badge bg-success">${stats.regulares} (${stats.porcentaje_regulares}%)</span>
                </div>
                <div class="progress" style="height: 25px">
                    <div class="progress-bar bg-success" style="width: ${stats.porcentaje_regulares}%">${stats.porcentaje_regulares}%</div>
                </div>
            </div>
            <div class="mb-3 p-3 rounded" style="background: #f093fb15">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-warning"><i class="mdi mdi-circle"></i> Amonestados</span>
                    <span class="badge bg-warning">${stats.amonestados} (${stats.porcentaje_amonestados}%)</span>
                </div>
                <div class="progress" style="height: 25px">
                    <div class="progress-bar bg-warning" style="width: ${stats.porcentaje_amonestados}%">${stats.porcentaje_amonestados}%</div>
                </div>
            </div>
            <div class="mb-3 p-3 rounded" style="background: #fa709a15">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold text-danger"><i class="mdi mdi-circle"></i> Inhabilitados</span>
                    <span class="badge bg-danger">${stats.inhabilitados} (${stats.porcentaje_inhabilitados}%)</span>
                </div>
                <div class="progress" style="height: 25px">
                    <div class="progress-bar bg-danger" style="width: ${stats.porcentaje_inhabilitados}%">${stats.porcentaje_inhabilitados}%</div>
                </div>
            </div>
            <div class="alert alert-light mb-0"><strong>Total:</strong> ${stats.total_estudiantes}</div>
        `;
        document.getElementById('asistencia-chart').innerHTML = html;
    }

    function renderPostulaciones(p) {
        const items = [
            { label: 'Aprobados', value: p.aprobados, color: 'success' },
            { label: 'Pendientes', value: p.pendientes, color: 'warning' },
            { label: 'Rechazados', value: p.rechazados, color: 'danger' }
        ];

        document.getElementById('postulaciones-stats').innerHTML = items.map(i => `
            <div class="d-flex justify-content-between mb-2">
                <span>${i.label}</span>
                <span class="badge bg-${i.color}">${i.value}</span>
            </div>
        `).join('') + `<hr><div class="text-center"><strong>Total: ${p.total}</strong></div>`;
    }

    function renderCarnets(c) {
        const items = [
            { label: 'Generados', value: c.generados, color: 'primary' },
            { label: 'Entregados', value: c.entregados, color: 'success' }
        ];

        document.getElementById('carnets-stats').innerHTML = items.map(i => `
            <div class="d-flex justify-content-between mb-2">
                <span>${i.label}</span>
                <span class="badge bg-${i.color}">${i.value}</span>
            </div>
        `).join('');
    }

    function renderAlertas(alertas) {
        document.getElementById('alertas-content').innerHTML = alertas.map(a => `
            <div class="alert alert-${a.tipo} mb-2">
                <i class="${a.icono} me-2"></i> ${a.mensaje}
                ${a.url !== '#' ? `<a href="${a.url}" class="alert-link ms-2">Ver más →</a>` : ''}
            </div>
        `).join('');
    }

    function renderAnuncios(anuncios) {
        document.getElementById('anuncios-content').innerHTML = anuncios.map(a => `
            <div class="mb-3 pb-3 border-bottom">
                <h6 class="mb-1">${a.titulo}</h6>
                <p class="small text-muted mb-1">${a.contenido.substring(0, 100)}...</p>
                <small class="text-muted"><i class="mdi mdi-calendar"></i> ${a.fecha_publicacion}</small>
            </div>
        `).join('');
    }
});
