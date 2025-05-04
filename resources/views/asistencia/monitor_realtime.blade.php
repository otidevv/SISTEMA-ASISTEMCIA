@extends('layouts.app')

@section('title', 'Monitor de Asistencia en Tiempo Real')

@section('css')
    <style>
        .monitor-container {
            height: calc(100vh - 250px);
            overflow-y: auto;
            position: relative;
        }

        .student-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        .student-photo-initial {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            background: linear-gradient(135deg, #4158d0, #c850c0);
            border: 5px solid #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            margin: 0 auto;
        }

        .asistencia-card {
            transition: all 0.5s ease;
        }

        .asistencia-card.new {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .verification-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: #4caf50;
            color: white;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            z-index: 2;
        }

        .student-photo-container {
            position: relative;
            margin: 0 auto 15px;
            width: 150px;
        }

        .check-animation {
            font-size: 40px;
            color: #28a745;
            margin-bottom: 10px;
            animation: pulse 1.5s infinite;
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

        .progress-container {
            height: 5px;
            background-color: #f0f0f0;
            width: 100%;
            margin-top: 15px;
        }

        .progress-bar-custom {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #1e88e5, #1565c0);
            transition: width linear 0.1s;
        }

        /* Modal Customization */
        .attendance-modal .modal-header {
            background: linear-gradient(135deg, #1e88e5, #1565c0);
            color: white;
            border-bottom: none;
        }

        .attendance-modal .modal-content {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        /* Queue indicator badge */
        .queue-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(0, 0, 0, 0.3);
            color: white;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 12px;
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
                                                    {{ $registro->fecha_hora->format('d/m/Y H:i:s') }}</p>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="queue-badge" id="queue-indicator">1/1</div>
                <div class="modal-header">
                    <h5 class="modal-title" id="attendance-modal-label">Registro de Asistencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="check-animation">
                        <i class="uil uil-check-circle"></i>
                    </div>
                    <h4 class="text-success mb-4">¡Asistencia Registrada!</h4>

                    <div class="student-photo-container">
                        <div id="student-photo"></div>
                        <span class="verification-badge" id="verification-type"></span>
                    </div>

                    <h2 class="mt-3 mb-1 fw-bold" id="student-name"></h2>
                    <p class="text-muted" id="student-info"></p>

                    <div class="progress-container">
                        <div class="progress-bar-custom" id="progress-bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
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

            // WebSocket connection setup
            function setupWebSocket() {
                // Aquí implementarías la conexión a Laravel Echo/Pusher
                /*
                window.Echo.channel('asistencia-channel')
                    .listen('NuevoRegistroAsistencia', (e) => {
                        // Process and display the new record
                        const data = {
                            id: e.registro.id,
                            name: e.registro.nombre_completo || `Documento: ${e.registro.nro_documento}`,
                            time: e.registro.fecha_hora_formateada,
                            photoHtml: createPhotoHtml(e.registro), // Función para crear el HTML de la foto
                            smallPhotoHtml: createSmallPhotoHtml(e.registro), // Función para la lista
                            type: e.registro.tipo_verificacion_texto
                        };

                        addNewAttendanceRecord(data);
                    });
                */

                // Funciones auxiliares para crear HTML de fotos
                function createPhotoHtml(registro) {
                    if (registro.usuario && registro.usuario.foto_perfil) {
                        return `<img src="${registro.usuario.foto_perfil}" alt="Foto" class="student-photo">`;
                    } else {
                        const inicial = registro.usuario ? registro.usuario.nombre.charAt(0).toUpperCase() : 'U';
                        return `<div class="student-photo-initial">${inicial}</div>`;
                    }
                }

                function createSmallPhotoHtml(registro) {
                    if (registro.usuario && registro.usuario.foto_perfil) {
                        return `<img src="${registro.usuario.foto_perfil}" alt="Foto" width="40" height="40" class="rounded-circle">`;
                    } else {
                        const inicial = registro.usuario ? registro.usuario.nombre.charAt(0).toUpperCase() : 'U';
                        return `<div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">${inicial}</div>`;
                    }
                }

                // Para pruebas: simulamos nuevos registros
                setInterval(() => {
                    const nombres = ['Carlos Mendoza', 'Ana López', 'Luis García', 'María Torres',
                        'Juan Pérez', 'Patricia Ramírez', 'Miguel Ángel', 'Sofía Castro'
                    ];
                    const tiposVerificacion = ['Huella digital', 'Tarjeta RFID', 'Facial', 'Código QR',
                        'Manual'
                    ];

                    const randomNombre = nombres[Math.floor(Math.random() * nombres.length)];
                    const randomTipo = tiposVerificacion[Math.floor(Math.random() * tiposVerificacion
                        .length)];
                    const randomInicial = randomNombre.charAt(0);

                    // Crear HTML para la foto (versión grande para el modal)
                    const photoHtml = `<div class="student-photo-initial">${randomInicial}</div>`;

                    // Versión pequeña para la lista
                    const smallPhotoHtml = `
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                             style="width: 40px; height: 40px;">
                            ${randomInicial}
                        </div>
                    `;

                    const testData = {
                        id: Math.floor(Math.random() * 1000),
                        name: randomNombre,
                        time: new Date().toLocaleString(),
                        photoHtml: photoHtml,
                        smallPhotoHtml: smallPhotoHtml,
                        type: randomTipo
                    };

                    addNewAttendanceRecord(testData);
                }, 8000); // Cada 8 segundos para pruebas
            }

            // Initialize
            setupWebSocket();
        });
    </script>
@endpush
