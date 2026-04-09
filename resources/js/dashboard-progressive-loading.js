// Dashboard Progressive Loading - Motor de Diseño Elite V19 (RECONSTRUCCIÓN TOTAL - ESTABILIDAD)
document.addEventListener('DOMContentLoaded', function () {
    const apiToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let currentCicloId = 'global';
    let countdownInterval = null;

    if (!apiToken) return;

    loadDashboardData(currentCicloId);

    document.querySelectorAll('.cycle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const newId = this.dataset.id;
            if (newId === currentCicloId) return;
            document.querySelectorAll('.cycle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCicloId = newId;
            showSkeletons();
            loadDashboardData(currentCicloId);
        });
    });

    async function loadDashboardData(cicloId) {
        try {
            const timestamp = new Date().getTime();
            const baseUrl = `/api/dashboard/admin?ciclo_id=${cicloId}&_=${timestamp}`;
            const [adminData, anuncios] = await Promise.all([
                fetch(baseUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken } }).then(r => r.json()),
                fetch(`/api/dashboard/anuncios?_=${timestamp}`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': apiToken } }).then(r => r.json())
            ]);

            if (adminData && adminData.cicloActivo) {
                renderBanner(adminData.cicloActivo);
                startCountdown(adminData.cicloActivo.proximo_hito);
                renderStats(adminData);
                renderAsistenciaStats(adminData.estadisticasAsistencia);
                renderPostulaciones(adminData.postulaciones);
                renderCarnets(adminData.carnets);
                renderAlertas(adminData.alertas);
            }
            renderAnuncios(anuncios);
        } catch (error) { console.error('Error dashboard data V19:', error); }
    }

    function startCountdown(hito) {
        if (countdownInterval) clearInterval(countdownInterval);
        const countdownEl = document.getElementById('live-countdown');
        if (!countdownEl || !hito) return;

        const targetDate = new Date(hito.fecha).getTime();
        const updateTimer = () => {
            const now = new Date().getTime();
            const distance = targetDate - now;
            if (distance < 0) {
                countdownEl.innerHTML = '<span class="badge bg-success shadow-sm px-3">HITO ALCANZADO</span>';
                clearInterval(countdownInterval);
                return;
            }
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownEl.innerHTML = `
                <div class="d-flex gap-2">
                    <div class="text-center bg-white rounded p-1 shadow-sm border" style="min-width: 44px"><span class="fw-bold fs-6 d-block text-dark">${days}</span><small class="text-muted" style="font-size: 0.5rem">DÍAS</small></div>
                    <div class="text-center bg-white rounded p-1 shadow-sm border" style="min-width: 44px"><span class="fw-bold fs-6 d-block text-dark">${hours}</span><small class="text-muted" style="font-size: 0.5rem">HORA</small></div>
                    <div class="text-center bg-white rounded p-1 shadow-sm border" style="min-width: 44px"><span class="fw-bold fs-6 d-block text-dark">${minutes}</span><small class="text-muted" style="font-size: 0.5rem">MIN</small></div>
                    <div class="text-center bg-white rounded p-1 shadow-sm border" style="min-width: 44px"><span class="fw-bold fs-6 d-block text-magenta">${seconds}</span><small class="text-muted" style="font-size: 0.5rem">SEG</small></div>
                </div>
            `;
        };
        updateTimer();
        countdownInterval = setInterval(updateTimer, 1000);
    }

    function renderBanner(ciclo) {
        const container = document.getElementById('ciclo-banner');
        if (!container) return;

        const hitos = [
            { id: 'inicio', label: 'INICIO', date: ciclo.fecha_inicio },
            { id: 'examen1', label: '1er EX.', date: ciclo.fecha_examen_1 },
            { id: 'examen2', label: '2do EX.', date: ciclo.fecha_examen_2 },
            { id: 'examen3', label: '3er EX.', date: ciclo.fecha_examen_3 },
            { id: 'fin', label: 'FIN', date: ciclo.fecha_fin }
        ].filter(h => h.date && h.date !== '00/00/0000' && h.date !== '-');

        const proximoNombre = (ciclo.proximo_hito ? ciclo.proximo_hito.nombre : 'FINALIZADO').toUpperCase();

        container.innerHTML = `
            <div class="col-12">
                <div class="card gradient-hero border-0 text-white overflow-hidden shadow-lg position-relative" style="min-height: 420px">
                    <div class="card-body p-4 p-md-5 position-relative" style="z-index: 20">
                        <div class="row g-4 align-items-center">
                            
                            <!-- Columna 1: Título y Avance -->
                            <div class="col-xl-4 col-lg-5 order-1">
                                <span class="badge bg-white bg-opacity-10 text-white mb-3 p-2 px-3 rounded-pill text-uppercase fw-bold shadow-sm" style="letter-spacing: 2px; font-size: 0.7rem">
                                    <i class="mdi mdi-pulse me-1 text-warning"></i> MONITOREO OPERATIVO
                                </span>
                                <h1 class="text-white fw-bold mb-4 display-5" style="text-shadow: 0 5px 25px rgba(0,0,0,0.4); line-height: 1.1;">${ciclo.nombre}</h1>
                                
                                <div class="p-3 mb-4 rounded-4 bg-white shadow-lg border border-white border-opacity-10 d-inline-block">
                                    <p class="mb-1 fw-bold text-muted text-uppercase" style="font-size: 0.6rem">Siguiente Meta: <span class="text-magenta">${proximoNombre}</span></p>
                                    <div id="live-countdown"></div>
                                </div>

                                <div class="mt-2" style="max-width: 300px">
                                    <p class="mb-1 small fw-bold text-white-50">CUMPLIMIENTO: ${ciclo.progreso_porcentaje}%</p>
                                    <div class="progress bg-white bg-opacity-20" style="height: 6px;">
                                        <div class="progress-bar bg-white shadow-sm" style="width: ${ciclo.progreso_porcentaje}%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna 2: Hitos Centrales -->
                            <div class="col-xl-5 col-lg-7 order-2">
                                <div class="p-4 rounded-4 shadow-lg mb-4" 
                                     style="background: rgba(0,0,0,0.3); backdrop-filter: blur(40px); border: 1px solid rgba(255,255,255,0.1) !important;">
                                    <h6 class="text-white mb-3 fw-bold text-uppercase small" style="letter-spacing: 1px">Cronograma Académico</h6>
                                    <div class="row g-2">
                                        <div class="col-sm-6"><div class="p-2 px-3 rounded-4 bg-white shadow-sm border-start border-4 border-info"><small class="text-muted fw-bold d-block small">INICIO</small><div class="text-dark fw-bold">${ciclo.fecha_inicio}</div></div></div>
                                        <div class="col-sm-6"><div class="p-2 px-3 rounded-4 bg-white shadow-sm border-start border-4 border-danger"><small class="text-muted fw-bold d-block small">CLAUSURA</small><div class="text-dark fw-bold">${ciclo.fecha_fin}</div></div></div>
                                    </div>
                                </div>

                                <div class="timeline-milestones d-none d-xl-flex mt-2">
                                    ${hitos.map((h, i) => {
                                        const now = new Date().getTime();
                                        const hitoDateMatch = h.date.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                                        const hitoTime = hitoDateMatch ? new Date(`${hitoDateMatch[3]}-${hitoDateMatch[2]}-${hitoDateMatch[1]}`).getTime() : 0;
                                        const isCompleted = now > hitoTime;
                                        const isNext = ciclo.proximo_hito && (ciclo.proximo_hito.nombre.includes(h.label.replace(' EX.', '')) || (h.id === 'inicio' && ciclo.proximo_hito.nombre === 'Inicio del Ciclo') || (h.id === 'fin' && ciclo.proximo_hito.nombre === 'Fin del Ciclo'));
                                        return `<div class="milestone-point ${isCompleted ? 'completed' : ''} ${isNext ? 'next' : ''}"><span class="milestone-label font-bold">${h.label}</span><span class="milestone-date text-white-50" style="font-size: 0.5rem">${h.date}</span></div>`;
                                    }).join('')}
                                </div>
                            </div>

                            <!-- Columna 3: Jaguar Guardián -->
                            <div class="col-xl-3 d-none d-xl-block order-3 position-relative" style="height: 380px;">
                                <div class="position-absolute" style="bottom: -120px; right: -50px; z-index: 10;">
                                    <img src="/assets/img/mascotadashboard.png" 
                                         style="height: 520px; width: auto; filter: drop-shadow(0 40px 100px rgba(0,0,0,0.8));" 
                                         alt="Jaguar CEPRE">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function showSkeletons() {
        document.getElementById('stats-cards').innerHTML = Array(4).fill(`<div class="col-xl-3 col-md-6 mb-4"><div class="card modern-card shimmer" style="height: 120px;"></div></div>`).join('');
    }

    function renderStats(data) {
        const container = document.getElementById('stats-cards');
        const kpis = [
            { label: 'ESTUDIANTES', val: data.totalInscripciones, icon: 'mdi mdi-account-group', grad: 'var(--cepre-pink)', sub: 'Inscritos Reales' },
            { label: 'ASISTENCIA HOY', val: data.asistenciaHoy.porcentaje + '%', icon: 'mdi mdi-flash-outline', grad: 'var(--cepre-green)', sub: `${data.asistenciaHoy.estudiantes_unicos} presentes` },
            { label: 'DOCENTES', val: data.totalDocentesActivos, icon: 'mdi mdi-account-tie-outline', grad: '#0077b6', sub: 'Activos hoy' },
            { label: 'AULAS', val: data.totalAulas, icon: 'mdi mdi-door-open', grad: '#ff9f1c', sub: 'Ocupadas' }
        ];
        container.innerHTML = kpis.map(k => `
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card modern-card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-container me-3 shadow-sm" style="background: ${k.grad}18; color: ${k.grad}">
                                <i class="${k.icon}"></i>
                            </div>
                            <span class="fw-bold text-muted small text-uppercase" style="letter-spacing: 1px">${k.label}</span>
                        </div>
                        <h2 class="stat-value mb-1" style="color: ${k.grad}">${k.val}</h2>
                        <p class="text-muted mb-0 small fw-medium text-truncate"><i class="mdi mdi-arrow-right-circle-outline"></i> ${k.sub}</p>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderAsistenciaStats(stats) {
        const container = document.getElementById('asistencia-chart');
        const items = [
            { lab: 'Regulares', val: stats.regulares, col: 'var(--cepre-green)', pct: Math.round((stats.regulares / stats.total_estudiantes) * 100) || 0, icon: 'mdi mdi-account-check-outline' },
            { lab: 'Amonestados', val: stats.amonestados, col: '#ff9f1c', pct: Math.round((stats.amonestados / stats.total_estudiantes) * 100) || 0, icon: 'mdi mdi-account-alert-outline' },
            { lab: 'Inhabilitados', val: stats.inhabilitados, col: 'var(--cepre-pink)', pct: Math.round((stats.inhabilitados / stats.total_estudiantes) * 100) || 0, icon: 'mdi mdi-account-remove-outline' }
        ];
        container.innerHTML = items.map(i => `<div class="mb-4"><div class="d-flex justify-content-between align-items-center mb-2"><span class="fw-bold fs-6" style="color: ${i.col}"><i class="${i.icon} me-1"></i> ${i.lab}</span><span class="text-muted fw-bold">${i.val} alumnos (${i.pct}%)</span></div><div class="progress progress-custom overflow-hidden shadow-sm" style="height: 14px"><div class="progress-bar shadow-sm" style="width: ${i.pct}%; background: ${i.col}"></div></div></div>`).join('') + `<div class="p-3 rounded-4 bg-light text-center border mt-4 shadow-sm"><small class="text-muted text-uppercase fw-bold">Población Activa</small><h4 class="mb-0 fw-bold text-dark">${stats.total_estudiantes} Estudiantes</h4></div>`;
    }

    function renderPostulaciones(p) {
        document.getElementById('postulaciones-stats').innerHTML = `
            <div class="row g-2 text-center">
                <div class="col-12 mb-2"><div class="p-2 bg-light rounded-4 border"><small class="text-muted">Total</small><h4 class="mb-0 fw-bold text-primary">${p.total}</h4></div></div>
                <div class="col-6"><div class="p-2 bg-white rounded-4 border shadow-sm"><small class="text-warning">Pen.</small><h5 class="mb-0 fw-bold">${p.pendientes}</h5></div></div>
                <div class="col-6"><div class="p-2 bg-white rounded-4 border shadow-sm"><small class="text-success">Apr.</small><h5 class="mb-0 fw-bold">${p.aprobadas}</h5></div></div>
            </div>`;
    }

    function renderCarnets(c) {
        document.getElementById('carnets-stats').innerHTML = `
            <div class="p-3 rounded-4 bg-white border shadow-sm mb-2 d-flex justify-content-between align-items-center"><span class="text-muted small">Total</span><span class="badge bg-primary rounded-pill px-3">${c.total}</span></div>
            <div class="p-3 rounded-4 bg-white border shadow-sm d-flex justify-content-between align-items-center"><span class="text-muted small">Pendientes</span><span class="badge bg-warning text-dark rounded-pill px-3">${c.pendientes_impresion}</span></div>`;
    }

    function renderAlertas(alertas) {
        const container = document.getElementById('alertas-content');
        if (!alertas || alertas.length === 0) { container.innerHTML = '<p class="text-muted text-center py-4">Sin alertas.</p>'; return; }
        container.innerHTML = alertas.map(a => `
            <div class="alert border-0 border-start border-4 border-${a.tipo} bg-white shadow-sm rounded-4 mb-2 p-3 d-flex align-items-center">
                <i class="${a.icono} text-${a.tipo} fs-4 me-3"></i><div class="fw-bold text-dark small text-uppercase">${a.mensaje}</div>
            </div>`).join('');
    }

    function renderAnuncios(anuncios) {
        const container = document.getElementById('anuncios-content');
        if (!anuncios || anuncios.length === 0) { container.innerHTML = '<p class="text-muted text-center py-2">Sin anuncios.</p>'; return; }
        container.innerHTML = anuncios.slice(0, 2).map(a => `
            <div class="p-3 rounded-4 bg-light mb-2"><small class="text-success fw-bold text-uppercase">Comunicado</small><h6 class="mb-1 fw-bold">${a.titulo}</h6><p class="small text-muted mb-0">${a.contenido.substring(0, 70)}...</p></div>`).join('');
    }
});