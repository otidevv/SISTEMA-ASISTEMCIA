@extends('layouts.app')

@section('title', 'Monitor de Asistencia en Tiempo Real')

@push('css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

        :root {
            /* Colores Institucionales Exactos */
            --cepre-magenta: #e2007a;
            --cepre-green: #93c01f;
            --cepre-cyan: #00aeef;
            --cepre-dark-blue: #0d47a1;

            /* Escala de Grises Premium (Slate) */
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;

            /* Variables Dinámicas */
            --bg-app: var(--slate-50);
            --bg-card: #ffffff;
            --border-card: var(--slate-200);
            --text-heading: var(--slate-900);
            --text-body: var(--slate-700);
            --text-muted: var(--slate-500);
            --shadow-card: 0 4px 20px -2px rgba(15, 23, 42, 0.05), 0 0 3px rgba(15, 23, 42, 0.02);
            --shadow-hover: 0 10px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.05);
            --bg-avatar: var(--slate-100);
            --bg-time-box: var(--slate-50);
        }

        [data-bs-theme="dark"] {
            --bg-app: var(--slate-900);
            --bg-card: var(--slate-800);
            --border-card: var(--slate-700);
            --text-heading: var(--slate-50);
            --text-body: var(--slate-200);
            --text-muted: var(--slate-400);
            --shadow-card: 0 4px 20px -2px rgba(0, 0, 0, 0.4), 0 0 3px rgba(0, 0, 0, 0.2);
            --shadow-hover: 0 10px 30px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            --bg-avatar: var(--slate-900);
            --bg-time-box: rgba(15, 23, 42, 0.5);
        }

        body {
            background-color: var(--bg-app) !important;
            font-family: 'Outfit', sans-serif !important;
            color: var(--text-body);
        }

        /* HEADER PREMIUM */
        .premium-header {
            margin-bottom: 2.5rem;
            border-bottom: 1px solid var(--border-card);
            padding-bottom: 1.5rem;
        }

        .header-badge {
            background: rgba(0, 174, 239, 0.1);
            color: var(--cepre-cyan);
            padding: 6px 14px;
            border-radius: 100px;
            font-weight: 800;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border: 1px solid rgba(0, 174, 239, 0.2);
        }

        .header-title {
            font-size: 2.25rem;
            font-weight: 900;
            color: var(--text-heading);
            letter-spacing: -1px;
            margin-top: 0.5rem;
            margin-bottom: 0.25rem;
        }

        /* Controles Header */
        .header-controls {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            padding: 8px 16px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-muted);
            box-shadow: var(--shadow-card);
        }

        .status-pill.online {
            color: var(--cepre-green);
            border-color: rgba(147, 192, 31, 0.3);
            background: rgba(147, 192, 31, 0.05);
        }

        .live-dot {
            width: 8px; height: 8px;
            background-color: currentColor;
            border-radius: 50%; margin-right: 8px;
            position: relative;
        }

        .status-pill.online .live-dot::after {
            content: ''; position: absolute;
            width: 100%; height: 100%;
            background-color: inherit; border-radius: inherit;
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        @keyframes ping {
            75%, 100% { transform: scale(3); opacity: 0; }
        }

        .btn-sound {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            color: var(--text-body);
            padding: 8px 20px;
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: var(--shadow-card);
            transition: all 0.2s ease;
        }

        .btn-sound.active {
            background: var(--cepre-cyan);
            color: white;
            border-color: var(--cepre-cyan);
        }

        /* GRID Y TARJETAS */
        .monitor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            padding-bottom: 2rem;
        }

        .premium-widget {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            box-shadow: var(--shadow-card);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-widget:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .premium-widget.new-entry {
            animation: slideInFade 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideInFade {
            0% { opacity: 0; transform: translateY(20px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Línea superior indicadora */
        .widget-top-line {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--border-card);
        }

        .widget-top-line.status-regular { background: var(--cepre-green); }
        .widget-top-line.status-amonestado { background: #f59e0b; }
        .widget-top-line.status-inhabilitado { background: #ef4444; } /* ROJO para Inhabilitados */

        .widget-body {
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Avatar Grande Premium */
        .widget-avatar-large {
            width: 130px;
            height: 130px;
            border-radius: 24px;
            background: var(--bg-avatar);
            border: 1px solid var(--border-card);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--cepre-dark-blue);
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), inset 0 2px 4px rgba(0,0,0,0.02);
            position: relative;
        }

        [data-bs-theme="dark"] .widget-avatar-large {
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5);
        }

        .widget-avatar-large img {
            width: 100%; height: 100%; object-fit: cover;
        }

        /* Badge Estado */
        .widget-badge {
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid transparent;
            box-shadow: var(--shadow-card);
        }

        .badge-regular { background: rgba(147, 192, 31, 0.1); color: var(--cepre-green); border-color: rgba(147, 192, 31, 0.2); }
        .badge-amonestado { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.2); }
        .badge-inhabilitado { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); } /* ROJO para Inhabilitados */

        /* Información Usuario */
        .widget-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 2px;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .widget-doc {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        /* Sección de Tiempo */
        .widget-time-section {
            background: var(--bg-time-box);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid var(--border-card);
        }

        .time-icon-box {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            display: flex; align-items: center; justify-content: center;
            color: var(--cepre-cyan);
            box-shadow: var(--shadow-card);
        }

        .time-label {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .time-value {
            font-size: 1.25rem;
            font-weight: 900;
            color: var(--text-heading);
            line-height: 1;
        }

        /* MODAL PREMIUM (Estilo Apple / Stripe) */
        .attendance-modal .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: none;
            border-radius: 0;
        }

        [data-bs-theme="dark"] .attendance-modal .modal-content {
            background: rgba(15, 23, 42, 0.95);
        }

        .modal-queue {
            position: absolute;
            top: 30px; left: 30px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            padding: 8px 20px;
            border-radius: 100px;
            font-weight: 800;
            color: var(--text-heading);
            box-shadow: var(--shadow-card);
            z-index: 10;
        }

        .modal-content-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            position: relative;
        }

        .modal-avatar-wrapper {
            width: 180px; height: 180px;
            border-radius: 30px;
            background: var(--bg-card);
            padding: 8px;
            box-shadow: var(--shadow-hover);
            margin-bottom: 2rem;
            position: relative;
            border: 1px solid var(--border-card);
        }

        .modal-avatar-inner {
            width: 100%; height: 100%;
            border-radius: 22px;
            background: var(--bg-avatar);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            font-size: 5rem; font-weight: 900; color: var(--cepre-cyan);
        }

        .modal-avatar-inner img { width: 100%; height: 100%; object-fit: cover; }

        .modal-verif-badge {
            position: absolute;
            bottom: -15px; right: -15px;
            width: 60px; height: 60px;
            background: var(--cepre-cyan);
            color: white;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
            border: 4px solid var(--bg-app);
            box-shadow: var(--shadow-card);
        }

        .modal-name {
            font-size: 3rem;
            font-weight: 900;
            color: var(--text-heading);
            letter-spacing: -1.5px;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .modal-doc {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
        }

        .modal-alert-box {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            padding: 1.5rem 3rem;
            border-radius: 20px;
            box-shadow: var(--shadow-card);
            text-align: center;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .modal-alert-status {
            font-size: 1.75rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .modal-time-display {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--text-heading);
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 2px solid var(--border-card);
            padding-left: 2rem;
        }
        .modal-time-display i { color: var(--cepre-cyan); font-size: 2rem; }

        .modal-stats {
            display: flex;
            gap: 1.5rem;
        }

        .stat-card {
            background: var(--bg-time-box);
            border: 1px solid var(--border-card);
            padding: 1rem 2rem;
            border-radius: 16px;
            text-align: center;
            min-width: 160px;
        }

        .stat-val { font-size: 2rem; font-weight: 900; color: var(--text-heading); line-height: 1; margin-bottom: 4px; }
        .stat-lbl { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

        .progress-line {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 8px;
            background: var(--border-card);
        }

        .progress-line-fill {
            height: 100%; width: 0%;
            background: var(--cepre-cyan);
            transition: width linear 0.1s;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid" style="padding-top: 1.5rem; max-width: 1600px;">
        
        <!-- HEADER -->
        <div class="premium-header d-flex justify-content-between align-items-end">
            <div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="header-badge">CEPRE UNAMAD</span>
                    <span class="text-muted fw-bold" style="font-size: 0.85rem;">
                        <i class="uil uil-shield-check text-success"></i> Biometría Activa
                    </span>
                </div>
                <h1 class="header-title">Monitor de Asistencia</h1>
            </div>
            <div class="header-controls">
                <div class="status-pill online" id="connection-status">
                    <div class="live-dot"></div> CONECTADO
                </div>
                <button class="btn-sound active" id="toggle-sound">
                    <i class="uil uil-volume"></i> SONIDO: ON
                </button>
            </div>
        </div>
        
        <!-- GRID DE MONITOREO -->
        <div id="registros-asistencia" class="monitor-grid">
            @if ($ultimosRegistros->isEmpty())
                <div class="col-12 text-center p-5" style="grid-column: 1 / -1;">
                    <div class="display-3 text-muted mb-3 opacity-25"><i class="uil uil-fingerprint"></i></div>
                    <h4 class="text-muted fw-bolder">Sistema en Espera</h4>
                    <p class="text-muted fw-medium">Las marcaciones aparecerán aquí en tiempo real.</p>
                </div>
            @else
                @foreach ($ultimosRegistros as $registro)
                    @php
                        $situ = $registro->estado_situacional;
                        $estado = $situ['estado'] ?? 'regular';
                        $iniciales = $registro->usuario ? strtoupper(substr($registro->usuario->nombre, 0, 1) . substr($registro->usuario->apellido_paterno, 0, 1)) : 'U';
                        $nombreCorto = $registro->usuario ? $registro->usuario->nombre . ' ' . $registro->usuario->apellido_paterno : 'Desconocido';
                        $doc = $registro->nro_documento;
                    @endphp
                    <div class="premium-widget" data-id="{{ $registro->id }}">
                        <div class="widget-top-line status-{{ $estado }}"></div>
                        
                        <!-- Badge flotante premium -->
                        <div class="position-absolute top-0 end-0 mt-3 me-3 z-3">
                            <span class="widget-badge badge-{{ $estado }}">
                                {{ $situ['detalle'] ?? 'REGULAR' }}
                            </span>
                        </div>
                        
                        <div class="widget-body text-center pt-5">
                            <div class="widget-avatar-large mx-auto mb-3">
                                @if ($registro->usuario && $registro->usuario->foto_perfil)
                                    <img src="{{ asset('storage/' . $registro->usuario->foto_perfil) }}" alt="Foto" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="display:none; width:100%; height:100%; align-items:center; justify-content:center;">{{ $iniciales }}</div>
                                @else
                                    <div>{{ $iniciales }}</div>
                                @endif
                            </div>
                            
                            <h4 class="widget-name" title="{{ $nombreCorto }}">{{ $nombreCorto }}</h4>
                            <div class="widget-doc mb-3">DNI: {{ $doc }}</div>
                            
                            <div class="widget-time-section justify-content-center mt-auto">
                                <div class="time-icon-box" style="width: 32px; height: 32px;">
                                    <i class="uil uil-clock fs-5"></i>
                                </div>
                                <div class="text-start">
                                    <div class="time-label">Hora de Marcación</div>
                                    <div class="time-value" style="font-size: 1.15rem;">{{ $registro->fecha_registro->format('H:i:s') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- MODAL PREMIUM -->
    <div class="modal fade attendance-modal" id="attendanceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                
                <div class="modal-queue" id="queue-indicator" style="display: none;">
                    <i class="uil uil-layer-group text-primary"></i> <span id="queue-text">1/1 EN COLA</span>
                </div>
                
                <div class="modal-content-wrapper">
                    
                    <div class="modal-avatar-wrapper animate__animated animate__zoomIn">
                        <div class="modal-avatar-inner" id="modal-photo">
                            <!-- Foto o iniciales -->
                        </div>
                        <div class="modal-verif-badge shadow-sm animate__animated animate__bounceIn animate__delay-1s">
                            <i id="modal-verif-icon" class="uil uil-fingerprint"></i>
                        </div>
                    </div>

                    <h1 class="modal-name animate__animated animate__fadeInUp" id="student-name-modal">CARGANDO...</h1>
                    <div class="modal-doc animate__animated animate__fadeInUp">DNI: <span id="student-doc-modal">00000000</span></div>
                    
                    <div class="modal-alert-box animate__animated animate__fadeInUp animate__delay-1s" id="modal-alert-box">
                        <div class="modal-alert-status text-success" id="modal-badge-title">ESTADO</div>
                        <div class="modal-time-display">
                            <i class="uil uil-clock"></i> <span id="modal-time">00:00:00</span>
                        </div>
                    </div>

                    <div id="modal-details" class="modal-stats animate__animated animate__fadeInUp animate__delay-2s">
                        <!-- Detalles estadísticos -->
                    </div>

                </div>

                <div class="progress-line">
                    <div id="modal-progress-bar" class="progress-line-fill"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('registros-asistencia');
            const modalElement = document.getElementById('attendanceModal');
            const modal = new bootstrap.Modal(modalElement);
            const progressBar = document.getElementById('modal-progress-bar');
            const queueIndicator = document.getElementById('queue-indicator');
            const queueText = document.getElementById('queue-text');
            const toggleSoundBtn = document.getElementById('toggle-sound');
            const connectionStatus = document.getElementById('connection-status');
            
            let attendanceQueue = [];
            let isModalActive = false;
            let progressTimer = null;
            let soundEnabled = true;
            let lastProcessedId = null;

            const notificationSound = new Audio('{{ asset("assets/sounds/notifipro.mp3") }}');

            toggleSoundBtn.addEventListener('click', function() {
                soundEnabled = !soundEnabled;
                this.classList.toggle('active', soundEnabled);
                this.innerHTML = soundEnabled ? '<i class="uil uil-volume"></i> SONIDO: ON' : '<i class="uil uil-volume-mute"></i> SONIDO: OFF';
                if (soundEnabled) {
                    notificationSound.volume = 0;
                    notificationSound.play().then(() => { notificationSound.volume = 1; }).catch(() => {});
                }
            });

            function updateQueueIndicator() {
                const qSize = attendanceQueue.length;
                queueText.innerHTML = qSize > 0 ? `1/${qSize + 1} EN COLA` : `1/1 EN COLA`;
                queueIndicator.style.display = qSize > 0 ? 'block' : 'none';
            }

            function showModal(data) {
                if (isModalActive) {
                    attendanceQueue.push(data);
                    updateQueueIndicator();
                    return;
                }

                isModalActive = true;
                updateQueueIndicator();
                const situ = data.estado_situacional;
                
                // Set Modal Data
                document.getElementById('student-name-modal').innerText = data.name;
                document.getElementById('student-doc-modal').innerText = data.doc;
                document.getElementById('modal-time').innerText = data.timeOnly;
                document.getElementById('modal-photo').innerHTML = data.modalPhotoHtml;
                
                // Status Box
                const alertBox = document.getElementById('modal-alert-box');
                const badgeTitle = document.getElementById('modal-badge-title');
                
                badgeTitle.innerHTML = situ.detalle;
                
                let themeColor = 'var(--cepre-green)';
                let themeIcon = 'uil-check-circle';
                if(situ.estado === 'amonestado') { themeColor = '#f59e0b'; themeIcon = 'uil-exclamation-triangle'; }
                else if(situ.estado === 'inhabilitado') { themeColor = 'var(--cepre-magenta)'; themeIcon = 'uil-times-circle'; }

                badgeTitle.style.color = themeColor;
                badgeTitle.innerHTML = `<i class="uil ${themeIcon}"></i> ${situ.detalle}`;
                
                // Verif Icon
                const verifIcons = { 0:'uil-fingerprint', 1:'uil-credit-card', 2:'uil-user-square', 3:'uil-qrcode-scan', 4:'uil-edit-alt' };
                document.getElementById('modal-verif-icon').className = `uil ${verifIcons[data.tipo_verificacion] || 'uil-check-circle'}`;
                
                // Stats
                const details = document.getElementById('modal-details');
                if (situ.estado !== 'regular') {
                    details.innerHTML = `
                        <div class="stat-card">
                            <div class="stat-val text-danger">${situ.faltas}</div>
                            <div class="stat-lbl">Faltas Registradas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-val text-primary" style="font-size: 1.5rem; margin-top: 8px;">${situ.examen}</div>
                            <div class="stat-lbl mt-2">Próximo Examen</div>
                        </div>
                        <div class="stat-card" style="background: rgba(226, 0, 122, 0.05); border-color: rgba(226, 0, 122, 0.2);">
                            <div class="stat-val text-danger">${situ.faltas_para_inhabilitacion}</div>
                            <div class="stat-lbl text-danger">Faltas para Inhabilitar</div>
                        </div>`;
                } else {
                    details.innerHTML = `
                        <div class="stat-card">
                            <div class="stat-val text-success">${situ.asistencias} <span class="text-muted fs-4">/ ${situ.dias_habiles_totales}</span></div>
                            <div class="stat-lbl">Asistencias Totales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-val text-warning">${situ.faltas_para_amonestacion}</div>
                            <div class="stat-lbl">Margen de Faltas</div>
                        </div>`;
                    if (typeof confetti === 'function') confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 }, colors: ['#93c01f', '#00aeef', '#e2007a'] });
                }

                // Progress bar
                progressBar.style.width = '0%';
                progressBar.style.background = themeColor;

                modal.show();
                if (soundEnabled) notificationSound.play().catch(() => {});

                // Timer
                let duration = 4000;
                if (attendanceQueue.length > 10) duration = 1200;
                else if (attendanceQueue.length > 3) duration = 2500;

                let progress = 0;
                const interval = 50;
                const increment = (interval / duration) * 100;

                if (progressTimer) clearInterval(progressTimer);
                progressTimer = setInterval(() => {
                    progress += increment;
                    progressBar.style.width = `${progress}%`;
                    if (progress >= 100) { clearInterval(progressTimer); modal.hide(); }
                }, interval);
            }

            modalElement.addEventListener('hidden.bs.modal', function () {
                isModalActive = false;
                if (attendanceQueue.length > 0) {
                    setTimeout(() => { showModal(attendanceQueue.shift()); }, 300);
                }
            });

            function addNewRecord(data) {
                const situ = data.estado_situacional;
                const card = document.createElement('div');
                card.className = `premium-widget new-entry`;
                card.dataset.id = data.id;
                
                card.innerHTML = `
                    <div class="widget-top-line status-${situ.estado}"></div>
                    
                    <div class="position-absolute top-0 end-0 mt-3 me-3 z-3">
                        <span class="widget-badge badge-${situ.estado}">
                            ${situ.detalle}
                        </span>
                    </div>
                    
                    <div class="widget-body text-center pt-5">
                        <div class="widget-avatar-large mx-auto mb-3">
                            ${data.cardPhotoHtml}
                        </div>
                        <h4 class="widget-name" title="${data.name}">${data.name}</h4>
                        <div class="widget-doc mb-3">DNI: ${data.doc}</div>
                        <div class="widget-time-section justify-content-center mt-auto">
                            <div class="time-icon-box" style="width: 32px; height: 32px;">
                                <i class="uil uil-clock fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="time-label">Hora Marcación</div>
                                <div class="time-value" style="font-size: 1.15rem;">${data.timeOnly}</div>
                            </div>
                        </div>
                    </div>`;

                const empty = container.querySelector('.col-12.text-center');
                if (empty) container.removeChild(empty);
                
                container.prepend(card);
                setTimeout(() => card.classList.remove('new-entry'), 600);

                const all = container.querySelectorAll('.premium-widget');
                if (all.length > 24) container.removeChild(all[all.length - 1]);

                showModal(data);
            }

            function generatePhotoHtml(reg) {
                const init = (reg.iniciales || 'U').toUpperCase();
                if (reg.foto_url) {
                    return `<img src="${reg.foto_url}" alt="Foto" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="display:none; width:100%; height:100%; align-items:center; justify-content:center;">${init}</div>`;
                }
                return `<div class="w-100 h-100 d-flex align-items-center justify-content-center">${init}</div>`;
            }

            if (typeof window.Echo !== 'undefined') {
                window.Echo.channel('asistencia-channel').listen('.App\\Events\\NuevoRegistroAsistencia', (data) => {
                    if (!data || !data.registro || data.registro.id === lastProcessedId) return;
                    lastProcessedId = data.registro.id;
                    
                    const reg = data.registro;
                    const fullTime = reg.fecha_registro_formateada || new Date(reg.fecha_registro).toLocaleTimeString('es-PE');
                    
                    addNewRecord({
                        id: reg.id,
                        name: reg.nombre_completo || 'Desconocido',
                        doc: reg.nro_documento,
                        timeOnly: fullTime.includes(' ') ? fullTime.split(' ')[1] : fullTime,
                        cardPhotoHtml: generatePhotoHtml(reg),
                        modalPhotoHtml: generatePhotoHtml(reg),
                        tipo_verificacion: reg.tipo_verificacion,
                        estado_situacional: reg.estado_situacional
                    });
                });

                const pusher = window.Echo.connector.pusher;
                if (pusher) {
                    pusher.connection.bind('connected', () => {
                        connectionStatus.className = 'status-pill online';
                        connectionStatus.innerHTML = '<div class="live-dot"></div> CONECTADO';
                    });
                    pusher.connection.bind('disconnected', () => {
                        connectionStatus.className = 'status-pill';
                        connectionStatus.innerHTML = '<div class="live-dot" style="animation:none;"></div> DESCONECTADO';
                    });
                }
            } else {
                console.error('❌ Laravel Echo no disponible');
            }
        });
    </script>
@endpush