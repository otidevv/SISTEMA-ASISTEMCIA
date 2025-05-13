@extends('layouts.app')

@section('title', 'Monitor de Asistencia en Tiempo Real')

@section('css')
    <style>
        .monitor-container {
            height: calc(100vh - 250px);
            overflow-y: auto;
            position: relative;
        /* Estilo específico para una sola inicial */
        .attendance-modal .student-photo-initial.single-letter {
            font-size: 220px !important;
            letter-spacing: 0 !important;
        }
        
        /* Estilo específico para dos iniciales */
        .attendance-modal .student-photo-initial.double-letter {
            font-size: 150px !important;
            letter-spacing: -10px !important;
        }

        /* Contenedor de la foto - Limitado estrictamente */
        .attendance-modal .student-photo-container {
            position: relative;
            margin: 0 auto;
            width: 300px !important;
            height: 300px !important;
            max-width: 300px !important;
            max-height: 300px !important;
            overflow: hidden !important;
        }
        
        /* Estilos específicos para foto en el modal - Con límites estrictos */
        .attendance-modal .student-photo,
        .attendance-modal .student-photo img,
        .attendance-modal #student-photo img,
        .attendance-modal .student-photo-container img,
        .attendance-modal .student-photo-container > img,
        .attendance-modal .student-photo-container * {
            width: 300px !important;
            height: 300px !important;
            max-width: 300px !important;
            max-height: 300px !important;
            object-fit: cover !important;
            border-radius: 50% !important;
            border: 12px solid #fff !important;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2) !important;
            margin: 0 auto !important;
            display: block !important;
        }

        .attendance-modal .student-photo-initial,
        .attendance-modal #student-photo .student-photo-initial,
        .attendance-modal .student-photo-container .student-photo-initial {
            width: 300px !important;
            height: 300px !important;
            max-width: 300px !important;
            max-height: 300px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 180px !important;
            font-weight: 900 !important;
            color: white !important;
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            border: 12px solid #fff !important;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3) !important;
            margin: 0 auto !important;
            text-transform: uppercase !important;
            letter-spacing: -5px !important;
            user-select: none !important;
            line-height: 1 !important;
        }
        
        /* Asegurar que el contenedor div principal también tenga límites */
        .attendance-modal #student-photo {
            width: 100% !important;
            height: 100% !important;
            max-width: 300px !important;
            max-height: 300px !important;
            overflow: hidden !important;
            border-radius: 50% !important;
        }
        
        /* Fotos pequeñas en la lista de registros */
        .asistencia-card .student-photo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: none;
            box-shadow: none;
        }

        .asistencia-card .student-photo-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: white;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin: 0;
            text-transform: uppercase;
        }

        .asistencia-card {
            transition: all 0.5s ease;
        }

        .asistencia-card.new {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .attendance-modal .verification-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: #4caf50;
            color: white;
            border-radius: 40px;
            padding: 15px 30px;
            font-size: 1.5rem !important;
            font-weight: bold;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .attendance-modal .student-photo-container {
            position: relative;
            margin: 0 auto;
            width: 40vh;
            height: 40vh;
            max-width: 500px;
            max-height: 500px;
        }

        .attendance-modal .check-animation {
            font-size: 6rem !important;
            color: #28a745;
            margin-bottom: 2rem;
            animation: pulse 1.5s infinite;
        }
        
        /* Eliminar el check animation ya que no está en el HTML actualizado */
        .attendance-modal .check-animation {
            display: none;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .attendance-modal .progress-container {
            height: 12px;
            background-color: #f0f0f0;
            width: 60%;
            margin: 2rem auto 0;
            border-radius: 10px;
        }

        .attendance-modal .progress-bar-custom {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #1e88e5, #1565c0);
            transition: width linear 0.1s;
            border-radius: 10px;
        }
        
        /* Queue indicator badge */
        .attendance-modal .queue-badge {
            position: absolute;
            top: 2rem;
            left: 2rem;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            border-radius: 35px;
            padding: 12px 25px;
            font-size: 1.25rem;
            font-weight: bold;
            z-index: 3;
        }

        /* Modal Customization - Más ancho horizontalmente */
        .attendance-modal .modal-dialog.modal-custom {
            width: 98vw !important;
            max-width: 98vw !important;
            height: 85vh !important;
            margin: 7.5vh 1vw;
        }
        
        .attendance-modal .modal-content {
            height: 100%;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            background: white;
        }
        
        .attendance-modal .modal-header {
            background: transparent;
            color: white;
            border-bottom: none;
            padding: 2rem 2rem 0 0;
            height: auto;
            position: absolute;
            right: 0;
            top: 0;
            z-index: 10;
        }
        
        .attendance-modal .btn-close {
            font-size: 2rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .attendance-modal .modal-body {
            padding: 3rem;
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
        }
        
        /* Texto de asistencia registrada */
        .attendance-modal h1.text-success {
            font-size: clamp(3rem, 5vw, 6rem) !important;
            font-weight: 900 !important;
            color: #28a745 !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 3rem !important;
        }
        
        /* Estilos para el contenido del modal */
        #student-name {
            font-size: clamp(2.5rem, 4vw, 5rem) !important;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 1rem;
            margin-top: 2rem;
        }
        
        #student-info {
            font-size: clamp(1.5rem, 3vw, 3rem) !important;
            color: #666;
            font-weight: 500;
        }
        
        /* Ajuste de clases display de Bootstrap */
        .attendance-modal .display-2 {
            font-size: clamp(3rem, 5vw, 6rem) !important;
        }
        
        .attendance-modal .display-3 {
            font-size: clamp(2.5rem, 4vw, 5rem) !important;
        }
        
        .attendance-modal .display-5 {
            font-size: clamp(1.5rem, 3vw, 3rem) !important;
        }

        /* Queue indicator badge */
        .queue-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: rgba(0, 0, 0, 0.4);
            color: white;
            border-radius: 25px;
            padding: 5px 15px;
            font-size: 16px;
            font-weight: bold;
            z-index: 3;
        }
    </style>
@endsection

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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title">Visualizador de Asistencia en Tiempo Real</h4>
                            <div>
                                <span class="badge bg-success" id="connection-status">Conectado</span>
                                <button class="btn btn-sm btn-primary ms-2" id="toggle-sound">
                                    <i class="uil uil-volume"></i> Sonido: ON
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="uil uil-info-circle me-1"></i>
                            Los nuevos registros de asistencia aparecerán automáticamente en esta pantalla.
                        </div>

                        <div class="monitor-container" id="registros-container">
                            <div class="text-center p-5">
                                <h5>Esperando nuevos registros de asistencia...</h5>
                                <p class="text-muted">Los últimos registros aparecerán aquí.</p>
                            </div>

                            <!-- Lista de registros recientes -->
                            <h5 class="mt-4">Últimos registros:</h5>
                            @foreach ($ultimosRegistros as $registro)
                                <div class="card mb-2 asistencia-card" data-id="{{ $registro->id }}">
                                    <div class="card-body py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                @if ($registro->usuario && $registro->usuario->foto_perfil)
                                                    <img src="{{ asset('storage/' . $registro->usuario->foto_perfil) }}"
                                                        alt="Foto" width="40" height="40" class="rounded-circle">
                                                @else
                                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                                        style="width: 40px; height: 40px;">
                                                        @if ($registro->usuario)
                                                            {{ strtoupper(substr($registro->usuario->nombre, 0, 1)) }}
                                                        @else
                                                            <i class="uil uil-user"></i>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0">
                                                    @if ($registro->usuario)
                                                        {{ $registro->usuario->nombre }}
                                                        {{ $registro->usuario->apellido_paterno }}
                                                    @else
                                                        Documento: {{ $registro->nro_documento }}
                                                    @endif
                                                </h6>
                                                <p class="mb-0 text-muted small">
                                                    {{ $registro->fecha_registro->format('d/m/Y H:i:s') }}</p>
                                            </div>
                                            <div class="ms-auto">
                                                <span class="badge bg-info">{{ $registro->tipo_verificacion_texto }}</span>
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

    <!-- Modal de Asistencia Bootstrap -->
    <div class="modal fade attendance-modal" id="attendance-modal" tabindex="-1" aria-labelledby="attendance-modal-label"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-custom">
            <div class="modal-content">
                <div class="queue-badge" id="queue-indicator">1/1</div>
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white btn-lg ms-auto" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center d-flex flex-column justify-content-center align-items-center">
                    <h1 class="text-success mb-4 display-2 fw-bold">¡ASISTENCIA REGISTRADA!</h1>
                    
                    <div class="student-photo-container mb-4">
                        <div id="student-photo"></div>
                        <span class="verification-badge" id="verification-type"></span>
                    </div>

                    <h1 class="fw-bold display-3" id="student-name"></h1>
                    <p class="text-muted display-5" id="student-info"></p>

                    <div class="progress-container mt-5">
                        <div class="progress-bar-custom" id="progress-bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
                    <p class="text-muted display-3" id="student-info"></p>

                    <div class="progress-container mt-5">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables
            const notificationSound = new Audio('/assets/sounds/notification.mp3');
            const registrosContainer = document.getElementById('registros-container');
            const attendanceModal = new bootstrap.Modal(document.getElementById('attendance-modal'), {
                backdrop: 'static',
                keyboard: false
            });
            const modalElement = document.getElementById('attendance-modal');
            const studentPhoto = document.getElementById('student-photo');
            const studentName = document.getElementById('student-name');
            const studentInfo = document.getElementById('student-info');
            const verificationType = document.getElementById('verification-type');
            const toggleSoundBtn = document.getElementById('toggle-sound');
            const connectionStatus = document.getElementById('connection-status');
            const progressBar = document.getElementById('progress-bar');
            const queueIndicator = document.getElementById('queue-indicator');

            let isSoundEnabled = true;
            let attendanceQueue = []; // Cola para almacenar registros pendientes
            let isModalShowing = false;
            let progressTimer = null;

            // Toggle sound
            toggleSoundBtn.addEventListener('click', function() {
                isSoundEnabled = !isSoundEnabled;
                this.innerHTML = isSoundEnabled ?
                    '<i class="uil uil-volume"></i> Sonido: ON' :
                    '<i class="uil uil-volume-mute"></i> Sonido: OFF';
            });

            // Evento cuando el modal termina de cerrarse
            modalElement.addEventListener('hidden.bs.modal', function() {
                isModalShowing = false;
                if (progressTimer) {
                    clearInterval(progressTimer);
                    progressTimer = null;
                }
                // Procesar el siguiente en cola después de que se cierre completamente
                setTimeout(() => {
                    processNextInQueue();
                }, 300);
            });

            // Función para mostrar el siguiente registro en la cola
            function processNextInQueue() {
                if (attendanceQueue.length > 0 && !isModalShowing) {
                    const nextRecord = attendanceQueue.shift();
                    showAttendanceModal(nextRecord);
                    updateQueueIndicator();
                }
            }

            // Actualizar el indicador de cola
            function updateQueueIndicator() {
                const queueSize = attendanceQueue.length;
                queueIndicator.textContent = queueSize > 0 ? `1/${queueSize + 1}` : '1/1';
            }

            // Función para mostrar el modal de asistencia
            function showAttendanceModal(data) {
                isModalShowing = true;

                // Establecer contenido del modal
                studentPhoto.innerHTML = data.photoHtml;
                studentName.textContent = data.name;
                studentInfo.textContent = data.time;
                verificationType.textContent = data.type;
                progressBar.style.width = '0%';
                
                // Aplicar estilos adicionales a las imágenes después de cargarlas
                setTimeout(() => {
                    const images = studentPhoto.querySelectorAll('img');
                    images.forEach(img => {
                        img.style.width = '300px';
                        img.style.height = '300px';
                        img.style.maxWidth = '300px';
                        img.style.maxHeight = '300px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '50%';
                    });
                }, 10);

                // Mostrar modal usando Bootstrap API
                attendanceModal.show();

                // Reproducir sonido si está habilitado
                if (isSoundEnabled) {
                    notificationSound.play();
                }

                // Animación de la barra de progreso
                let progress = 0;
                const interval = 50; // Actualizar cada 50ms
                const duration = 5000; // Duración total: 5 segundos
                const increment = interval / duration * 100;

                if (progressTimer) {
                    clearInterval(progressTimer);
                }

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

            // Function to add new attendance record to the list and queue
            function addNewAttendanceRecord(data) {
                // Create new card element
                const newCard = document.createElement('div');
                newCard.className = 'card mb-2 asistencia-card new';
                newCard.dataset.id = data.id;
                newCard.innerHTML = `
                <div class="card-body py-2">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            ${data.smallPhotoHtml}
                        </div>
                        <div>
                            <h6 class="mb-0">${data.name}</h6>
                            <p class="mb-0 text-muted small">${data.time}</p>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-info">${data.type}</span>
                        </div>
                    </div>
                </div>
                `;

                // Check if "Esperando nuevos registros" message exists and remove it
                const emptyMessage = registrosContainer.querySelector('.text-center.p-5');
                if (emptyMessage) {
                    registrosContainer.removeChild(emptyMessage);
                }

                // Add to container at the top (after the heading)
                const heading = registrosContainer.querySelector('h5.mt-4');
                if (heading) {
                    heading.after(newCard);
                } else {
                    registrosContainer.prepend(newCard);
                }

                // Remove highlight after animation
                setTimeout(() => {
                    newCard.classList.remove('new');
                }, 3000);

                // Limit the number of records shown
                const allCards = registrosContainer.querySelectorAll('.asistencia-card');
                if (allCards.length > 20) {
                    registrosContainer.removeChild(allCards[allCards.length - 1]);
                }

                // Agregar a la cola de asistencia
                attendanceQueue.push(data);
                updateQueueIndicator();

                // Si el modal no está mostrándose, procesar el siguiente registro
                if (!isModalShowing) {
                    processNextInQueue();
                }
            }

            // Funciones auxiliares para crear HTML de fotos
            function createPhotoHtml(registro) {
                if (registro.foto_url) {
                    return `<img src="${registro.foto_url}" alt="Foto" class="student-photo" style="width: 300px !important; height: 300px !important; max-width: 300px !important; max-height: 300px !important; object-fit: cover !important; border-radius: 50% !important;">`;
                } else {
                    // Obtener las iniciales del nombre
                    let iniciales = 'U'; // Por defecto "U" de Usuario
                    
                    if (registro.iniciales) {
                        iniciales = registro.iniciales;
                    } else if (registro.nombre_completo) {
                        // Generar iniciales del nombre completo
                        const palabras = registro.nombre_completo.trim().split(' ');
                        if (palabras.length >= 2) {
                            iniciales = palabras[0].charAt(0) + palabras[1].charAt(0);
                        } else if (palabras.length === 1) {
                            iniciales = palabras[0].charAt(0);
                        }
                    } else if (registro.nro_documento) {
                        // Si solo hay documento, usar los primeros 2 dígitos
                        iniciales = registro.nro_documento.substring(0, 2);
                    }
                    
                    iniciales = iniciales.toUpperCase();
                    
                    // Determinar la clase CSS según el número de letras
                    const letterClass = iniciales.length === 1 ? 'single-letter' : 'double-letter';
                    
                    return `<div class="student-photo-initial ${letterClass}" style="width: 300px !important; height: 300px !important; max-width: 300px !important; max-height: 300px !important; font-size: 180px !important; line-height: 1 !important;">${iniciales}</div>`;
                }
            }

            function createSmallPhotoHtml(registro) {
                if (registro.foto_url) {
                    return `<img src="${registro.foto_url}" alt="Foto" width="40" height="40" class="rounded-circle" style="width: 40px !important; height: 40px !important;">`;
                } else {
                    // Misma lógica para las iniciales
                    let iniciales = 'U';
                    
                    if (registro.iniciales) {
                        iniciales = registro.iniciales;
                    } else if (registro.nombre_completo) {
                        const palabras = registro.nombre_completo.trim().split(' ');
                        if (palabras.length >= 2) {
                            iniciales = palabras[0].charAt(0) + palabras[1].charAt(0);
                        } else if (palabras.length === 1) {
                            iniciales = palabras[0].charAt(0);
                        }
                    } else if (registro.nro_documento) {
                        iniciales = registro.nro_documento.substring(0, 2);
                    }
                    
                    iniciales = iniciales.toUpperCase();
                    
                    return `<div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px !important; height: 40px !important; font-weight: bold;">${iniciales}</div>`;
                }
            }
            
            // Función para formatear la fecha
            function formatearFecha(fechaString) {
                if (!fechaString) return '';
                
                const fecha = new Date(fechaString);
                
                // Formatear fecha en formato dd/mm/yyyy HH:mm:ss
                const dia = fecha.getDate().toString().padStart(2, '0');
                const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
                const año = fecha.getFullYear();
                const horas = fecha.getHours().toString().padStart(2, '0');
                const minutos = fecha.getMinutes().toString().padStart(2, '0');
                const segundos = fecha.getSeconds().toString().padStart(2, '0');
                
                return `${dia}/${mes}/${año} ${horas}:${minutos}:${segundos}`;
            }

            // Configuración directa de Echo (sin función separada)
            // Reemplaza la sección de configuración de Echo con este código
            try {
                console.log('Inicializando WebSockets...');
                
                // Una sola configuración de Pusher
                window.pusher = new Pusher('iv9wx1kfwnwactpwfzwn', {
                    wsHost: window.location.hostname,
                    wsPort: 443,
                    wssPort: 443,
                    enabledTransports: ['ws', 'wss'],
                    forceTLS: true,
                    disableStats: true,
                    cluster: 'mt1'
                });
                
                // Logging de información de conexión
                console.log('📡 Configuración WebSocket:', {
                    host: window.location.hostname,
                    pusherKey: 'iv9wx1kfwnwactpwfzwn',
                    canal: 'asistencia-channel',
                    evento: 'App\\Events\\NuevoRegistroAsistencia'
                });
                
                // Una sola suscripción al canal
                const channel = window.pusher.subscribe('asistencia-channel');
                
                // Un solo binding al evento
                channel.bind('App\\Events\\NuevoRegistroAsistencia', function(data) {
                    console.log('✅ EVENTO RECIBIDO:', data);
                    
                    try {
                        if (!data || !data.registro) {
                            console.error('❌ Datos del evento inválidos:', data);
                            return;
                        }
                        
                        const registro = data.registro;
                        
                        console.log('📋 Procesando registro:', registro);
                        
                        // Usar fecha_registro formateada en lugar de fecha_hora_formateada
                        const fechaRegistroFormateada = registro.fecha_registro_formateada || 
                            formatearFecha(registro.fecha_registro) || 
                            'Fecha no disponible';
                        
                        const eventData = {
                            id: registro.id,
                            name: registro.nombre_completo || 
                                `Documento: ${registro.nro_documento}`,
                            time: fechaRegistroFormateada,
                            photoHtml: createPhotoHtml(registro),
                            smallPhotoHtml: createSmallPhotoHtml(registro),
                            type: registro.tipo_verificacion_texto
                        };
                        
                        console.log('🎯 Datos preparados para el modal:', eventData);
                        addNewAttendanceRecord(eventData);
                    } catch (error) {
                        console.error('❌ Error al procesar evento:', error);
                    }
                });
                
                // Eventos de conexión
                window.pusher.connection.bind('state_change', function(states) {
                    console.log(`Estado de WebSocket: ${states.previous} → ${states.current}`);
                });
                
                window.pusher.connection.bind('connected', function() {
                    console.log('✅ CONECTADO al servidor WebSocket');
                    connectionStatus.textContent = 'Conectado';
                    connectionStatus.className = 'badge bg-success';
                });
                
                window.pusher.connection.bind('connecting', function() {
                    connectionStatus.textContent = 'Conectando...';
                    connectionStatus.className = 'badge bg-warning';
                });
                
                window.pusher.connection.bind('disconnected', function() {
                    connectionStatus.textContent = 'Desconectado';
                    connectionStatus.className = 'badge bg-danger';
                });
                
                window.pusher.connection.bind('failed', function(error) {
                    console.error('❌ ERROR de conexión WebSocket:', error);
                    connectionStatus.textContent = 'Error de conexión';
                    connectionStatus.className = 'badge bg-danger';
                    
                    // Iniciar polling como respaldo si WebSockets fallan
                    checkForNewRecords();
                });
                
                // Función para tests manuales
                window.testModal = function() {
                    const testData = {
                        id: 999,
                        name: 'Usuario de Prueba',
                        time: formatearFecha(new Date()),
                        photoHtml: '<div class="student-photo-initial">TP</div>',
                        smallPhotoHtml: '<div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">TP</div>',
                        type: 'Prueba Manual'
                    };
                    
                    showAttendanceModal(testData);
                    addNewAttendanceRecord(testData);
                    return 'Test modal activado';
                };
                
            } catch (error) {
                console.error('❌ Error fatal al configurar WebSockets:', error);
                // Implementar polling como respaldo
                checkForNewRecords();
            }

            // Función de polling como respaldo si WebSockets no funcionan
            function checkForNewRecords() {
                let lastId = 0;

                // Obtener el último ID de los registros actuales
                const existingCards = document.querySelectorAll('.asistencia-card');
                if (existingCards.length > 0) {
                    lastId = Math.max(...Array.from(existingCards).map(card => parseInt(card.dataset.id) || 0));
                }

                setInterval(() => {
                    fetch(`/api/ultimos-registros?ultimo_id=${lastId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                console.log('Nuevos registros encontrados mediante polling:', data);

                                // Actualizar el último ID
                                lastId = Math.max(...data.map(item => item.id));

                                // Procesar cada registro
                                data.forEach(registro => {
                                    // Usar fecha_registro formateada
                                    const fechaRegistroFormateada = registro.fecha_registro_formateada || 
                                        formatearFecha(registro.fecha_registro) || 
                                        'Fecha no disponible';
                                    
                                    const eventData = {
                                        id: registro.id,
                                        name: registro.nombre_completo ||
                                            `Documento: ${registro.nro_documento}`,
                                        time: fechaRegistroFormateada,
                                        photoHtml: createPhotoHtml(registro),
                                        smallPhotoHtml: createSmallPhotoHtml(registro),
                                        type: registro.tipo_verificacion_texto
                                    };

                                    addNewAttendanceRecord(eventData);
                                });
                            }
                        })
                        .catch(error => console.error('Error al consultar nuevos registros:', error));
                }, 5000); // Consultar cada 5 segundos
            }
        });
    </script>
@endpush