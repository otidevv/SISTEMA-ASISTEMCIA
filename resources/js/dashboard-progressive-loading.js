// Dashboard Progressive Loading - Motor de Diseño Elite V27 (Identidad Cromática Exacta)
document.addEventListener('DOMContentLoaded', function () {
    const apiToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Paleta Oficial CEPRE UNAMAD
    const COLORS = {
        MAGENTA: '#e2007a',
        DARK_MAGENTA: '#9b0058',
        GREEN: '#93c01f',
        CYAN: '#00aeef',
        NAVY: '#1a237e',
        GOLD: '#ffd700'
    };

    const getUsername = () => {
        const metadataEl = document.getElementById('dashboard-metadata');
        if (metadataEl && metadataEl.dataset.userName) return metadataEl.dataset.userName;
        const hiddenInput = document.getElementById('auth-user-name');
        if (hiddenInput && hiddenInput.value) return hiddenInput.value;
        return 'Administrador';
    };

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
                renderBanner(adminData.cicloActivo, getUsername());
                startCountdown(adminData.cicloActivo.proximo_hito);
                renderStats(adminData);
                renderAsistenciaStats(adminData.estadisticasAsistencia);
                renderPostulaciones(adminData.postulaciones);
                renderCarnets(adminData.carnets);
                renderAlertas(adminData.alertas);
            }
            renderAnuncios(anuncios);
        } catch (error) { console.error('Error dashboard data V27:', error); }
    }

    function startCountdown(hito) {
        if (countdownInterval) clearInterval(countdownInterval);
        const countdownEl = document.getElementById('live-countdown');
        if (!countdownEl) return;

        if (!hito) {
            countdownEl.innerHTML = `<span class="badge bg-soft-success text-success px-3 border border-success" style="color: ${COLORS.GREEN} !important;">OBJETIVOS CUMPLIDOS</span>`;
            return;
        }

        const targetDate = new Date(hito.fecha).getTime();
        const updateTimer = () => {
            const now = new Date().getTime();
            const distance = targetDate - now;
            if (distance < 0) {
                countdownEl.innerHTML = '<span class="badge bg-success shadow-sm px-3 border border-white">HITO ALCANZADO</span>';
                clearInterval(countdownInterval);
                return;
            }
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownEl.innerHTML = `
                <div class="d-flex gap-2 animate__animated animate__fadeIn">
                    <div class="text-center rounded p-1 shadow-sm" style="min-width: 44px; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255,255,255,0.2) !important; backdrop-filter: blur(10px);">
                        <span class="fw-bold fs-6 d-block text-white">${days}</span>
                        <small class="text-white-50 fw-bold uppercase" style="font-size: 0.5rem">DÍAS</small>
                    </div>
                    <div class="text-center rounded p-1 shadow-sm" style="min-width: 44px; background: rgba(26, 35, 126, 0.5); border: 1px solid rgba(255,255,255,0.1) !important; backdrop-filter: blur(5px);">
                        <span class="fw-bold fs-6 d-block text-white">${hours}</span>
                        <small class="text-white-50" style="font-size: 0.5rem">HRS</small>
                    </div>
                    <div class="text-center rounded p-1 shadow-sm" style="min-width: 44px; background: rgba(26, 35, 126, 0.5); border: 1px solid rgba(255,255,255,0.1) !important; backdrop-filter: blur(5px);">
                        <span class="fw-bold fs-6 d-block text-white">${minutes}</span>
                        <small class="text-white-50" style="font-size: 0.5rem">MIN</small>
                    </div>
                    <div class="text-center rounded p-1 shadow-sm" style="min-width: 44px; background: ${COLORS.MAGENTA}; border: 1px solid rgba(255,255,255,0.3) !important; backdrop-filter: blur(5px); box-shadow: 0 0 15px rgba(226, 0, 122, 0.4);">
                        <span class="fw-bold fs-6 d-block text-white fw-extra-bold h-flash">${seconds}</span>
                        <small class="text-white-100" style="font-size: 0.5rem; opacity: 0.8">SEG</small>
                    </div>
                </div>
            `;
        };
        updateTimer();
        countdownInterval = setInterval(updateTimer, 1000);
    }

    function renderBanner(ciclo, name) {
        const container = document.getElementById('ciclo-banner');
        if (!container) return;

        const isGlobal = ciclo.es_global || false;
        const bannerTitle = isGlobal ? "INTELIGENCIA ESTRATÉGICA" : ciclo.nombre;
        const proximoNombre = (ciclo.proximo_hito ? ciclo.proximo_hito.nombre : 'FINALIZADO').toUpperCase();

        // Aplicación de Colores Oficiales
        const bannerBg = isGlobal 
            ? `linear-gradient(135deg, ${COLORS.NAVY} 0%, ${COLORS.DARK_BLUE} 50%, ${COLORS.MAGENTA} 100%)` 
            : `linear-gradient(135deg, ${COLORS.MAGENTA} 0%, ${COLORS.DARK_MAGENTA} 100%)`;

        let middleContent = '';
        if (isGlobal) {
            middleContent = `
                <div class="p-4 rounded-4 shadow-lg mb-4 position-relative overflow-hidden" 
                     style="background: rgba(0,0,0,0.25); backdrop-filter: blur(30px); border: 1px solid rgba(255,255,255,0.15) !important;">
                    <h6 class="text-white mb-3 fw-bold text-uppercase small" style="letter-spacing: 1px">Resumen Máster de Operaciones</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 text-center shadow-sm">
                                <i class="mdi mdi-identifier text-info fs-4 d-block mb-1"></i>
                                <span class="d-block fw-bold display-6 text-white">${ciclo.total_ciclos}</span>
                                <small class="text-white-50 fw-bold uppercase" style="font-size: 0.6rem">CICLOS ACTIVOS</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10 text-center shadow-sm">
                                <i class="mdi mdi-calendar-check text-warning fs-4 d-block mb-1"></i>
                                <span class="d-block fw-bold h5 text-white mt-2">${ciclo.fecha_inicio}</span>
                                <small class="text-white-50 fw-bold uppercase" style="font-size: 0.6rem">ARRANQUE GENERAL</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            const hitos = [
                { id: 'inicio', label: 'INICIO', date: ciclo.fecha_inicio },
                { id: 'examen1', label: '1er EX.', date: ciclo.fecha_examen_1 },
                { id: 'examen2', label: '2do EX.', date: ciclo.fecha_examen_2 },
                { id: 'examen3', label: '3er EX.', date: ciclo.fecha_examen_3 },
                { id: 'fin', label: 'FIN', date: ciclo.fecha_fin }
            ].filter(h => h.date && h.date !== '00/00/0000' && h.date !== '-');

            middleContent = `
                <div class="p-4 rounded-4 shadow-lg mb-4" 
                     style="background: rgba(0,0,0,0.22); backdrop-filter: blur(40px); border: 1px solid rgba(255,255,255,0.1) !important;">
                    <h6 class="text-white mb-3 fw-bold text-uppercase small" style="letter-spacing: 1px">Métricas de Tiempo</h6>
                    <div class="row g-2">
                        <div class="col-sm-6"><div class="p-2 px-3 rounded-4 bg-white shadow-sm border-start border-4 border-info"><small class="text-muted fw-bold d-block small" style="font-size: 0.55rem">INICIO OFICIAL</small><div class="text-dark fw-bold">${ciclo.fecha_inicio}</div></div></div>
                        <div class="col-sm-6"><div class="p-2 px-3 rounded-4 bg-white shadow-sm border-start border-4 border-danger"><small class="text-muted fw-bold d-block small" style="font-size: 0.55rem">CLAUSURA ACADÉMICA</small><div class="text-dark fw-bold">${ciclo.fecha_fin}</div></div></div>
                    </div>
                </div>

                <div class="timeline-milestones d-none d-xl-flex mt-2">
                    ${hitos.map((h, i) => {
                        const now = new Date().getTime();
                        const hitoDateMatch = h.date.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                        const hitoTime = hitoDateMatch ? new Date(`${hitoDateMatch[3]}-${hitoDateMatch[2]}-${hitoDateMatch[1]}`).getTime() : 0;
                        const isCompleted = now > hitoTime;
                        const isNext = ciclo.proximo_hito && (ciclo.proximo_hito.nombre.includes(h.label.replace(' EX.', '')) || (h.id === 'inicio' && ciclo.proximo_hito.nombre === 'Inicio de Clases') || (h.id === 'fin' && ciclo.proximo_hito.nombre === 'Fin del Ciclo'));
                        return `<div class="milestone-point ${isCompleted ? 'completed' : ''} ${isNext ? 'next' : ''}"><span class="milestone-label font-bold">${h.label}</span><span class="milestone-date text-white-50" style="font-size: 0.5rem">${h.date}</span></div>`;
                    }).join('')}
                </div>
            `;
        }

        container.innerHTML = `
            <div class="col-12 animate__animated animate__fadeIn">
                <div class="card gradient-hero border-0 text-white overflow-hidden shadow-lg position-relative" style="min-height: 420px; background: ${bannerBg} !important;">
                    
                    <!-- CAPAS DE DECORACIÓN -->
                    <div class="position-absolute" style="top: -100px; left: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%); blur: 60px; z-index: 1;"></div>
                    <div class="position-absolute" style="top: 20%; right: 10%; width: 300px; height: 300px; background: radial-gradient(circle, ${COLORS.CYAN}20 0%, transparent 70%); blur: 50px; z-index: 1;"></div>
                    
                    <div class="card-body p-4 p-md-5 position-relative" style="z-index: 20">
                        <div class="row g-4 align-items-center">
                            
                            <!-- Columna 1: Bienvenida -->
                            <div class="col-xl-4 col-lg-5 order-1">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="rounded-pill p-2 px-3 fw-bold small shadow-lg d-flex align-items-center" 
                                         style="letter-spacing: 1.5px; font-size: 0.65rem; background: rgba(26, 35, 126, 0.75); border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(20px); color: white;">
                                        <i class="mdi mdi-crown text-warning me-2"></i> ¡BIENVENIDO, ${name.toUpperCase()}!
                                    </div>
                                </div>
                                <h1 class="text-white fw-bold mb-4 display-5" style="text-shadow: 0 10px 30px rgba(0,0,0,0.3);">${bannerTitle}</h1>
                                
                                <div class="mb-4 d-inline-block">
                                    <p class="mb-2 fw-bold text-uppercase" style="font-size: 0.55rem; letter-spacing: 2px; color: ${COLORS.GOLD}">PRÓXIMA META ACADÉMICA: <span class="text-warning fw-extra-bold font-italic">${proximoNombre}</span></p>
                                    <div id="live-countdown" class="d-flex align-items-center"></div>
                                </div>

                                <div class="mt-4" style="max-width: 300px">
                                    <div class="d-flex justify-content-between mb-1 text-uppercase fw-bold" style="font-size: 0.5rem; letter-spacing: 1.5px">
                                        <span class="text-white-50">AVANCE DEL PROCESO</span>
                                        <span style="color: ${COLORS.GOLD}">${ciclo.progreso_porcentaje}%</span>
                                    </div>
                                    <div class="progress bg-white bg-opacity-20 shadow-sm" style="height: 6px; border-radius: 10px">
                                        <div class="progress-bar shadow-lg" style="width: ${ciclo.progreso_porcentaje}%; background-color: ${COLORS.GOLD}; transition: width 2s ease;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna 2: Central -->
                            <div class="col-xl-5 col-lg-7 order-2">
                                ${middleContent}
                            </div>

                            <!-- Columna 3: Jaguar -->
                            <div class="col-xl-3 d-none d-xl-block order-3 position-relative" style="height: 380px;">
                                <div class="position-absolute" style="bottom: -130px; right: -60px; z-index: 10;">
                                    <img src="/assets/img/mascotadashboard.png" 
                                         style="height: 540px; width: auto; filter: drop-shadow(0 40px 100px rgba(0,0,0,0.85));" 
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
            { label: 'ESTUDIANTES', val: data.totalInscripciones, icon: 'mdi mdi-account-group', grad: COLORS.MAGENTA, sub: 'Inscritos Consolidados' },
            { label: 'ASISTENCIA HOY', val: data.asistenciaHoy.porcentaje + '%', icon: 'mdi mdi-lightning-bolt', grad: COLORS.GREEN, sub: `${data.asistenciaHoy.estudiantes_unicos} presentes` },
            { label: 'DOCENTES', val: data.totalDocentesActivos, icon: 'mdi mdi-account-tie-outline', grad: COLORS.CYAN, sub: 'Activos en sistema' },
            { label: 'AULAS', val: data.totalAulas, icon: 'mdi mdi-door-open', grad: COLORS.DARK_MAGENTA, sub: 'Ocupadas totales' }
        ];
        container.innerHTML = kpis.map(k => `
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card modern-card border-0 shadow-sm h-100 position-relative">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="kpi-icon-container me-3 shadow-sm" style="background: ${k.grad}15; color: ${k.grad}">
                                <i class="${k.icon}"></i>
                            </div>
                            <span class="fw-bold text-muted small text-uppercase" style="letter-spacing: 1px">${k.label}</span>
                        </div>
                        <h2 class="stat-value mb-1" style="color: ${k.grad}">${k.val}</h2>
                        <p class="text-muted mb-0 small fw-medium text-truncate"><i class="mdi mdi-check-circle-outline"></i> ${k.sub}</p>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderAsistenciaStats(stats) {
        const container = document.getElementById('asistencia-chart');
        const items = [
            { lab: 'Regulares', val: stats.regulares, col: COLORS.GREEN, pct: Math.round((stats.regulares / stats.total_estudiantes) * 100) || 0, icon: 'mdi mdi-account-check-outline' },
            { lab: 'Amonestados', val: stats.amonestados, col: COLORS.GOLD, pct: Math.round((stats.amonestados / stats.total_estudiantes) * 100) || 0, icon: 'mdi mdi-account-alert-outline' },
            { lab: 'Inhabilitados', val: stats.MAGENTA, col: COLORS.MAGENTA, pct: Math.round((stats.inhabilitados / stats.total_estudiantes) * 100) || 0, icon: 'mdi mdi-account-remove-outline' }
        ];
        container.innerHTML = items.map(i => `<div class="mb-4"><div class="d-flex justify-content-between align-items-center mb-2"><span class="fw-bold fs-6" style="color: ${i.col}"><i class="${i.icon} me-1"></i> ${i.lab}</span><span class="text-muted fw-bold">${i.val} alumnos (${i.pct}%)</span></div><div class="progress progress-custom overflow-hidden shadow-sm" style="height: 14px"><div class="progress-bar shadow-sm" style="width: ${i.pct}%; background: ${i.col}"></div></div></div>`).join('') + `<div class="p-3 rounded-4 bg-light text-center border mt-4 shadow-sm"><small class="text-muted text-uppercase fw-bold">Población Activa Total</small><h4 class="mb-0 fw-bold text-dark">${stats.total_estudiantes} Estudiantes</h4></div>`;
    }

    function renderPostulaciones(p) {
        document.getElementById('postulaciones-stats').innerHTML = `
            <div class="row g-2 text-center">
                <div class="col-12 mb-2"><div class="p-2 bg-light rounded-4 border"><small class="text-muted uppercase fw-bold" style="font-size:0.6rem">Total Global</small><h4 class="mb-0 fw-bold" style="color: ${COLORS.NAVY}">${p.total}</h4></div></div>
                <div class="col-6"><div class="p-2 bg-white rounded-4 border shadow-sm"><small class="text-warning fw-bold" style="font-size:0.6rem">PEND.</small><h5 class="mb-0 fw-bold">${p.pendientes}</h5></div></div>
                <div class="col-6"><div class="p-2 bg-white rounded-4 border shadow-sm"><small class="text-success fw-bold" style="font-size:0.6rem">APROB.</small><h5 class="mb-0 fw-bold">${p.aprobadas}</h5></div></div>
            </div>`;
    }

    function renderCarnets(c) {
        document.getElementById('carnets-stats').innerHTML = `
            <div class="p-3 rounded-4 bg-white border shadow-sm mb-2 d-flex justify-content-between align-items-center"><span class="text-muted small fw-bold">Total Global</span><span class="badge rounded-pill px-3" style="background-color: ${COLORS.NAVY}">${c.total}</span></div>
            <div class="p-3 rounded-4 bg-white border shadow-sm d-flex justify-content-between align-items-center"><span class="text-muted small fw-bold">Pendientes</span><span class="badge text-dark rounded-pill px-3" style="background-color: ${COLORS.GOLD}">${c.pendientes_impresion}</span></div>`;
    }

    function renderAlertas(alertas) {
        const container = document.getElementById('alertas-content');
        if (!alertas || alertas.length === 0) { container.innerHTML = '<p class="text-muted text-center py-4 small">No se detectan alertas críticas.</p>'; return; }
        container.innerHTML = alertas.map(a => `
            <div class="alert border-0 border-start border-4 border-${a.tipo} bg-white shadow-sm rounded-4 mb-2 p-3 d-flex align-items-center">
                <i class="${a.icono} text-${a.tipo} fs-4 me-3"></i><div class="fw-bold text-dark small text-uppercase" style="font-size:0.65rem">${a.mensaje}</div>
            </div>`).join('');
    }

    function renderAnuncios(anuncios) {
        const container = document.getElementById('anuncios-content');
        if (!anuncios || anuncios.length === 0) { container.innerHTML = '<p class="text-muted text-center py-2 small">Sin anuncios recientes.</p>'; return; }
        container.innerHTML = anuncios.slice(0, 2).map(a => `
            <div class="p-3 rounded-4 bg-light mb-2 border"><small class="fw-bold text-uppercase" style="font-size: 0.55rem; color: ${COLORS.GREEN}">Comunicado Institucional</small><h6 class="mb-1 fw-bold small">${a.titulo}</h6><p class="small text-muted mb-0" style="font-size: 0.7rem">${a.contenido.substring(0, 75)}...</p></div>`).join('');
    }
});