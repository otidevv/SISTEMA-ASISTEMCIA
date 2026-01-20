@extends('layouts.app')

@section('title', 'Monitor de Asistencia en Tiempo Real')

@push('css')
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.98);
            --glass-border: rgba(255, 255, 255, 0.5);
            --primary-gradient: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            --success-gradient: linear-gradient(135deg, #059669 0%, #10b981 100%);
            --warning-gradient: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            --danger-gradient: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            --live-red: #ff0000;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
        }

        /* Indicador LIVE mejorado */
        .live-indicator {
            display: flex;
            align-items: center;
            background: white;
            padding: 8px 16px;
            border-radius: 50px;
            color: var(--live-red);
            font-weight: 900;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 4px 12px rgba(255, 51, 51, 0.15);
            border: 1px solid rgba(255, 51, 51, 0.1);
        }

        .live-dot {
            width: 12px;
            height: 12px;
            background-color: var(--live-red);
            border-radius: 50%;
            margin-right: 10px;
            box-shadow: 0 0 10px var(--live-red);
            animation: pulse-live 1.2s infinite;
        }

        @keyframes pulse-live {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 51, 51, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 12px rgba(255, 51, 51, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(255, 51, 51, 0); }
        }

        .monitor-container {
            height: calc(100vh - 200px);
            overflow-y: auto;
            padding: 2rem;
            background: linear-gradient(180deg, rgba(255,255,255,0.8) 0%, rgba(241,245,249,0.5) 100%);
            backdrop-filter: blur(30px);
            border-radius: 32px;
            border: 2px solid #fff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }

        /* Tarjetas con MUCHA vida y contraste */
        .asistencia-card {
            border: 1px solid rgba(255,255,255,0.8) !important;
            background: white !important;
            border-radius: 22px !important;
            margin-bottom: 1.5rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05);
            border-left: 8px solid transparent !important;
        }

        .asistencia-card:hover {
            transform: scale(1.03) translateX(10px);
            box-shadow: 0 25px 40px -10px rgba(0,0,0,0.12);
        }

        /* Clases de Estado ULTRA VIBRANTES con GLOW */
        .status-badge {
            font-size: 0.95rem;
            padding: 0.8rem 2rem;
            border-radius: 100px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .status-regular { 
            background: var(--success-gradient) !important; 
            color: white !important; 
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5) !important;
        }
        .status-amonestado { 
            background: var(--warning-gradient) !important; 
            color: white !important; 
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.5) !important;
        }
        .status-inhabilitado { 
            background: var(--danger-gradient) !important; 
            color: white !important; 
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.5) !important;
        }

        /* Inyectar colores de borde a las tarjetas según estado */
        .asistencia-card[data-estado="regular"] { border-left-color: #10b981 !important; }
        .asistencia-card[data-estado="amonestado"] { border-left-color: #f59e0b !important; }
        .asistencia-card[data-estado="inhabilitado"] { border-left-color: #ef4444 !important; }

        .asistencia-card:hover[data-estado="regular"] { box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15); }
        .asistencia-card:hover[data-estado="amonestado"] { box-shadow: 0 20px 40px rgba(245, 158, 11, 0.15); }
        .asistencia-card:hover[data-estado="inhabilitado"] { box-shadow: 0 20px 40px rgba(239, 68, 68, 0.15); }

        /* Modal Ultra Premium */
        .attendance-modal .modal-content {
            border: none;
            background: #fff;
            border-radius: 50px;
            box-shadow: 0 0 100px rgba(0,0,0,0.3);
        }

        .big-status-text {
            font-size: 6rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            letter-spacing: -3px;
            text-transform: uppercase;
        }

        .text-status-regular { color: #059669; text-shadow: 0 10px 20px rgba(5, 150, 105, 0.2); }
        .text-status-amonestado { color: #d97706; text-shadow: 0 10px 20px rgba(217, 119, 6, 0.2); }
        .text-status-inhabilitado { color: #dc2626; text-shadow: 0 10px 20px rgba(220, 38, 38, 0.2); }

        .student-photo-container {
            width: 420px;
            height: 420px;
            position: relative;
        }

        .student-photo-wrapper {
            width: 100%;
            height: 100%;
            padding: 15px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 30px 60px -12px rgba(0,0,0,0.25);
            border: 2px solid rgba(0,0,0,0.05);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .student-photo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Fondos Dinámicos para el Modal */
        .modal-body.bg-regular { background: radial-gradient(circle at center, #ffffff 0%, #e8f5e9 100%); }
        .modal-body.bg-amonestado { background: radial-gradient(circle at center, #ffffff 0%, #fff3e0 100%); }
        .modal-body.bg-inhabilitado { background: radial-gradient(circle at center, #ffffff 0%, #ffebee 100%); }

        .verification-type-icon {
            width: 110px;
            height: 110px;
            font-size: 3.5rem;
            background: var(--primary-gradient);
            color: white;
            border: 10px solid #fff;
            position: absolute;
            bottom: 10px;
            right: 10px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .situational-box {
            padding: 1.5rem 6rem;
            border-radius: 100px;
            font-size: 4rem;
            font-weight: 900;
            color: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            margin-bottom: 2rem;
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0% { transform: scale(1); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
            50% { transform: scale(1.05); box-shadow: 0 30px 60px rgba(0,0,0,0.3); }
            100% { transform: scale(1); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        }

        .situational-box.status-regular { background: var(--success-gradient) !important; }
        .situational-box.status-amonestado { background: var(--warning-gradient) !important; }
        .situational-box.status-inhabilitado { background: var(--danger-gradient) !important; }

        #student-name {
            font-size: 5rem;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 10px;
            letter-spacing: -2px;
            line-height: 1;
        }

        #student-info {
            font-size: 2.5rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 2rem;
        }

        .progress-bar-custom {
            height: 100%;
            width: 0;
            background: var(--primary-gradient);
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.8);
            transition: width linear 0.1s;
        }

        /* Scrollbar Styling */
        .monitor-container::-webkit-scrollbar { width: 10px; }
        .monitor-container::-webkit-scrollbar-track { background: rgba(0,0,0,0.02); }
        .monitor-container::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
@endpush

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('asistencia.index') }}">Asistencia</a></li>
                            <li class="breadcrumb-item active">Monitor en Tiempo Real</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Monitor de Asistencia en Tiempo Real</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card bg-transparent border-0 shadow-none">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="header-title mb-0">Visualizador en Tiempo Real</h4>
                                <p class="text-muted mb-0">Sistema de Control de Procesos de Inscripción</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="live-indicator me-3" id="connection-status">
                                    <div class="live-dot"></div>
                                    EN VIVO
                                </div>
                                <button class="btn btn-white btn-sm shadow-sm border-0 px-3" id="toggle-sound">
                                    <i class="uil uil-volume"></i> ON
                                </button>
                            </div>
                        </div>

                        <div class="monitor-container" id="registros-container">
                            <!-- Lista de registros recientes -->
                            @if($ultimosRegistros->isEmpty())
                                <div class="text-center p-5">
                                    <div class="mb-3">
                                        <i class="uil uil-clock-three display-4 text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">Esperando nuevos registros...</h5>
                                </div>
                            @endif

                            @foreach ($ultimosRegistros as $registro)
                                @php
                                    $estado = $registro->estado_situacional['estado'] ?? 'regular';
                                    $detalle = $registro->estado_situacional['detalle'] ?? 'REGULAR';
                                    $statusClass = "status-{$estado}";
                                @endphp
                                <div class="card asistencia-card" data-id="{{ $registro->id }}" data-estado="{{ $estado }}">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                @if ($registro->usuario && $registro->usuario->foto_perfil)
                                                    <img src="{{ asset('storage/' . $registro->usuario->foto_perfil) }}"
                                                        alt="Foto" width="65" height="65" class="rounded-circle shadow-sm border border-2 border-white"
                                                        onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\"rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm\" style=\"width: 65px; height: 65px; background: var(--primary-gradient); font-weight: 900; font-size: 1.8rem;\">{{ strtoupper(substr($registro->usuario->nombre, 0, 1)) }}</div>';">
                                                @else
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm"
                                                        style="width: 65px; height: 65px; background: var(--primary-gradient); font-weight: 900; font-size: 1.8rem;">
                                                        @if ($registro->usuario)
                                                            {{ strtoupper(substr($registro->usuario->nombre, 0, 1)) }}
                                                        @else
                                                            <i class="uil uil-user"></i>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1 fw-bold">
                                                    @if ($registro->usuario)
                                                        {{ $registro->usuario->nombre }}
                                                        {{ $registro->usuario->apellido_paterno }}
                                                    @else
                                                        Documento: {{ $registro->nro_documento }}
                                                    @endif
                                                </h5>
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span class="me-3"><i class="uil uil-clock me-1"></i> {{ $registro->fecha_registro->format('H:i:s') }}</span>
                                                    <span><i class="uil uil-shield-check me-1"></i> {{ $registro->tipo_verificacion_texto }}</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="status-badge {{ $statusClass }}">{{ $detalle }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Asistencia Rediseñado -->
    <div class="modal fade attendance-modal" id="attendance-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="queue-counter" id="queue-indicator">1/1 EN COLA</div>
                
                <div class="modal-body text-center d-flex flex-column justify-content-center align-items-center" id="modal-body-content">
                    
                    <h1 class="big-status-text" id="modal-title">¡ASISTENCIA REGISTRADA!</h1>
                    
                    <div class="student-photo-container">
                        <div class="student-photo-wrapper" id="student-photo">
                            <!-- Aquí va la foto -->
                        </div>
                        <div class="verification-type-icon" id="verification-icon">
                            <i class="uil uil-fingerprint"></i>
                        </div>
                    </div>

                    <h1 id="student-name">CARGANDO...</h1>
                    <p id="student-info">CARGANDO...</p>

                    <div class="situational-box" id="situational-status">
                        HABILITADO
                    </div>

                    <div id="status-details" class="text-muted h4 mb-0">
                        <!-- Detalles extras como faltas -->
                    </div>

                    <div class="progress-container">
                        <div class="progress-bar-custom" id="progress-bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables
            const notificationSound = new Audio('/assets/sounds/notifipro.mp3');
            const registrosContainer = document.getElementById('registros-container');
            const attendanceModal = new bootstrap.Modal(document.getElementById('attendance-modal'), {
                backdrop: 'static',
                keyboard: false
            });
            const modalElement = document.getElementById('attendance-modal');
            const modalBody = document.getElementById('modal-body-content');
            const modalTitle = document.getElementById('modal-title');
            const studentPhoto = document.getElementById('student-photo');
            const studentName = document.getElementById('student-name');
            const studentInfo = document.getElementById('student-info');
            const situationalStatus = document.getElementById('situational-status');
            const statusDetails = document.getElementById('status-details');
            const verificationIcon = document.getElementById('verification-icon');
            
            const toggleSoundBtn = document.getElementById('toggle-sound');
            const connectionStatus = document.getElementById('connection-status');
            const progressBar = document.getElementById('progress-bar');
            const queueIndicator = document.getElementById('queue-indicator');

            let isSoundEnabled = true;
            let attendanceQueue = [];
            let isModalShowing = false;
            let progressTimer = null;

            // Toggle sound
            toggleSoundBtn.addEventListener('click', function() {
                isSoundEnabled = !isSoundEnabled;
                this.innerHTML = isSoundEnabled ? '<i class="uil uil-volume"></i> ON' : '<i class="uil uil-volume-mute"></i> OFF';
                this.classList.toggle('btn-white');
                this.classList.toggle('btn-dark');
            });

            modalElement.addEventListener('hidden.bs.modal', function() {
                isModalShowing = false;
                if (progressTimer) { clearInterval(progressTimer); progressTimer = null; }
                setTimeout(() => { processNextInQueue(); }, 300);
            });

            function processNextInQueue() {
                if (attendanceQueue.length > 0 && !isModalShowing) {
                    const nextRecord = attendanceQueue.shift();
                    showAttendanceModal(nextRecord);
                    updateQueueIndicator();
                }
            }

            function updateQueueIndicator() {
                const queueSize = attendanceQueue.length;
                queueIndicator.textContent = queueSize > 0 ? `1/${queueSize + 1} EN COLA` : '1/1 EN COLA';
                queueIndicator.style.display = queueSize > 0 ? 'block' : 'none';
            }

            const verificationIcons = {
                0: 'uil-fingerprint',
                1: 'uil-credit-card',
                2: 'uil-user-square',
                3: 'uil-qrcode-scan',
                4: 'uil-edit-alt'
            };

            function showAttendanceModal(data) {
                isModalShowing = true;
                const situ = data.estado_situacional;

                // Reset modal classes
                modalBody.className = 'modal-body text-center d-flex flex-column justify-content-center align-items-center bg-' + situ.estado;
                modalTitle.className = 'big-status-text text-status-' + situ.estado;
                
                // Set content
                studentPhoto.innerHTML = data.photoHtml;
                studentName.textContent = data.name;
                studentInfo.innerHTML = `<i class="uil uil-clock me-1"></i> ${data.time}`;
                situationalStatus.className = 'situational-box status-' + situ.estado;
                situationalStatus.textContent = situ.detalle;
                
                verificationIcon.innerHTML = `<i class="uil ${verificationIcons[data.tipo_verificacion] || 'uil-check-circle'}"></i>`;
                
                // Animate photo pop
                studentPhoto.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    studentPhoto.style.transform = 'scale(1)';
                }, 100);
                if (situ.estado !== 'regular') {
                    statusDetails.innerHTML = `
                        <div class="d-flex flex-column align-items-center gap-2 mt-2">
                            <div class="d-flex justify-content-center gap-4">
                                <span class="badge bg-soft-danger text-danger p-2"><i class="uil uil-times-circle"></i> Faltas: ${situ.faltas}</span>
                                <span class="badge bg-soft-primary text-primary p-2"><i class="uil uil-calendar-alt"></i> ${situ.examen}</span>
                            </div>
                            <div class="h5 text-muted mt-2">
                                <i class="uil uil-info-circle"></i> Faltan <strong>${situ.faltas_para_inhabilitacion}</strong> faltas para Inhabilitación
                            </div>
                        </div>
                    `;
                } else {
                    statusDetails.innerHTML = `
                        <div class="d-flex flex-column align-items-center gap-2">
                            <span class="badge bg-soft-success text-success p-2 h4"><i class="uil uil-check-circle"></i> Asistencias: ${situ.asistencias} / ${situ.dias_habiles_totales}</span>
                            <div class="h5 text-muted">
                                <i class="uil uil-smile"></i> ¡Vas por buen camino! Te quedan <strong>${situ.faltas_para_amonestacion}</strong> faltas de margen.
                            </div>
                        </div>
                    `;
                    // Tirar confeti si es regular
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: 150,
                            spread: 70,
                            origin: { y: 0.6 },
                            colors: ['#22c55e', '#10b981', '#ffffff']
                        });
                    }
                }

                progressBar.style.width = '0%';
                attendanceModal.show();

                if (isSoundEnabled) notificationSound.play();

                let progress = 0;
                const interval = 50;
                const duration = 5000;
                const increment = interval / duration * 100;

                if (progressTimer) clearInterval(progressTimer);

                progressTimer = setInterval(() => {
                    progress += increment;
                    progressBar.style.width = `${progress}%`;
                    if (progress >= 100) {
                        clearInterval(progressTimer);
                        progressTimer = null;
                        attendanceModal.hide();
                    }
                }, interval);
            }

            function addNewAttendanceRecord(data) {
                const situ = data.estado_situacional;
                const statusClass = `status-${situ.estado}`;
                
                const newCard = document.createElement('div');
                newCard.className = 'card asistencia-card new';
                newCard.dataset.id = data.id;
                newCard.dataset.estado = situ.estado;
                newCard.innerHTML = `
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                ${data.smallPhotoHtml}
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold">${data.name}</h5>
                                <div class="d-flex align-items-center text-muted small">
                                    <span class="me-3"><i class="uil uil-clock me-1"></i> ${data.timeOnly}</span>
                                    <span><i class="uil uil-shield-check me-1"></i> ${data.type}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="status-badge ${statusClass}">${situ.detalle}</span>
                            </div>
                        </div>
                    </div>
                `;

                const emptyMessage = registrosContainer.querySelector('.text-center.p-5');
                if (emptyMessage) registrosContainer.removeChild(emptyMessage);

                registrosContainer.prepend(newCard);

                setTimeout(() => { newCard.classList.remove('new'); }, 3000);

                const allCards = registrosContainer.querySelectorAll('.asistencia-card');
                if (allCards.length > 20) registrosContainer.removeChild(allCards[allCards.length - 1]);

                attendanceQueue.push(data);
                updateQueueIndicator();
                if (!isModalShowing) processNextInQueue();
            }

            function createPhotoHtml(registro) {
                if (registro.foto_url) {
                    return `<img src="${registro.foto_url}" alt="Foto">`;
                } else {
                    const iniciales = (registro.iniciales || 'U').toUpperCase();
                    return `<div class="student-photo-initial" style="background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; height: 100%; width: 100%; color: white; border-radius: 50%;">${iniciales}</div>`;
                }
            }

            function createSmallPhotoHtml(registro) {
                if (registro.foto_url) {
                    return `<img src="${registro.foto_url}" alt="Foto" width="65" height="65" class="rounded-circle shadow-sm border border-2 border-white" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\&quot;rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm\&quot; style=\&quot;width: 65px; height: 65px; background: var(--primary-gradient); font-weight: 900; font-size: 1.8rem;\&quot;>${(registro.iniciales || 'U').toUpperCase()}</div>';">`;
                } else {
                    const iniciales = (registro.iniciales || 'U').toUpperCase();
                    return `<div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm"
                                style="width: 65px; height: 65px; background: var(--primary-gradient); font-weight: 900; font-size: 1.8rem;">${iniciales}</div>`;
                }
            }
            
            function formatearFecha(fechaString) {
                if (!fechaString) return '';
                const fecha = new Date(fechaString);
                return fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }

            try {
                window.pusher = new Pusher('iv9wx1kfwnwactpwfzwn', {
                    wsHost: window.location.hostname,
                    wsPort: 443,
                    wssPort: 443,
                    enabledTransports: ['ws', 'wss'],
                    forceTLS: true,
                    disableStats: true,
                    cluster: 'mt1'
                });
                
                const channel = window.pusher.subscribe('asistencia-channel');
                channel.bind('App\\Events\\NuevoRegistroAsistencia', function(data) {
                    console.log('✅ EVENTO:', data);
                    if (!data || !data.registro) return;
                    
                    const registro = data.registro;
                    const eventData = {
                        id: registro.id,
                        name: registro.nombre_completo || `Doc: ${registro.nro_documento}`,
                        time: registro.fecha_registro_formateada || formatearFecha(registro.fecha_registro),
                        timeOnly: (registro.fecha_registro_formateada || formatearFecha(registro.fecha_registro)).split(' ')[1] || registro.fecha_registro_formateada,
                        photoHtml: createPhotoHtml(registro),
                        smallPhotoHtml: createSmallPhotoHtml(registro),
                        type: registro.tipo_verificacion_texto,
                        tipo_verificacion: registro.tipo_verificacion,
                        estado_situacional: registro.estado_situacional
                    };
                    addNewAttendanceRecord(eventData);
                });
                
                window.pusher.connection.bind('connected', () => {
                    connectionStatus.innerHTML = '<i class="uil uil-signal me-1"></i> Conectado';
                    connectionStatus.className = 'badge bg-success-lighten text-success px-3 py-2 rounded-pill';
                });
                
                window.pusher.connection.bind('disconnected', () => {
                    connectionStatus.innerHTML = '<i class="uil uil-signal-alt-3 me-1"></i> Desconectado';
                    connectionStatus.className = 'badge bg-danger-lighten text-danger px-3 py-2 rounded-pill';
                });
                
            } catch (error) {
                console.error('❌ WebSocket Error:', error);
            }
        });
    </script>
@endpush