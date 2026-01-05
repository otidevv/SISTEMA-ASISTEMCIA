@extends('layouts.app')

@section('title', 'Escanear Carnets')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <style>
        #reader {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border: 2px solid #4472C4;
            border-radius: 10px;
        }
        .scan-result {
            display: none;
        }
        .student-card {
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            background: #f8f9fa;
        }
        .student-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #4472C4;
        }
        .history-item {
            border-left: 3px solid #28a745;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .btn-scan {
            font-size: 1.2rem;
            padding: 15px 30px;
        }
    </style>
@endpush

@section('content')
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Escanear y Entregar Carnets</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('carnets.index') }}">Carnets</a></li>
                        <li class="breadcrumb-item active">Escanear</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Scanner Section -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">
                        <i class="uil uil-qrcode-scan me-1"></i> Escáner QR
                    </h4>
                    
                    <div class="text-center mb-3">
                        <button id="start-scan" class="btn btn-primary btn-scan">
                            <i class="uil uil-camera me-2"></i> Iniciar Escáner
                        </button>
                        <button id="stop-scan" class="btn btn-danger btn-scan" style="display: none;">
                            <i class="uil uil-stop-circle me-2"></i> Detener Escáner
                        </button>
                    </div>

                    <div id="reader"></div>

                    <!-- Resultado del escaneo -->
                    <div id="scan-result" class="scan-result mt-4">
                        <div class="student-card">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <img id="student-photo" src="" alt="Foto" class="student-photo mb-2">
                                    <h5 id="student-code" class="text-primary"></h5>
                                </div>
                                <div class="col-md-8">
                                    <h4 id="student-name" class="mb-3"></h4>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="30%">DNI:</th>
                                            <td id="student-dni"></td>
                                        </tr>
                                        <tr>
                                            <th>Carrera:</th>
                                            <td id="student-carrera"></td>
                                        </tr>
                                        <tr>
                                            <th>Turno:</th>
                                            <td id="student-turno"></td>
                                        </tr>
                                        <tr>
                                            <th>Aula:</th>
                                            <td id="student-aula"></td>
                                        </tr>
                                        <tr>
                                            <th>Ciclo:</th>
                                            <td id="student-ciclo"></td>
                                        </tr>
                                    </table>
                                    
                                    <div class="text-end mt-3">
                                        <button id="btn-cancel" class="btn btn-secondary">
                                            <i class="uil uil-times me-1"></i> Cancelar
                                        </button>
                                        <button id="btn-confirm-delivery" class="btn btn-success btn-lg">
                                            <i class="uil uil-check-circle me-1"></i> Confirmar Entrega
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Section -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">
                        <i class="uil uil-history me-1"></i> Entregas Recientes
                    </h4>
                    <div id="delivery-history">
                        <p class="text-muted text-center">No hay entregas en esta sesión</p>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">
                        <i class="uil uil-chart-line me-1"></i> Estadísticas de Hoy
                    </h4>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h3 id="today-deliveries" class="text-success">0</h3>
                            <p class="text-muted mb-0">Entregados Hoy</p>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 id="session-deliveries" class="text-primary">0</h3>
                            <p class="text-muted mb-0">En esta Sesión</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrcodeScanner = null;
        let currentCarnetId = null;
        let sessionDeliveries = 0;
        let deliveryHistory = [];

        // Configuración de Toastr
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 5000
        };

        // Iniciar escáner
        document.getElementById('start-scan').addEventListener('click', function() {
            startScanner();
        });

        // Detener escáner
        document.getElementById('stop-scan').addEventListener('click', function() {
            stopScanner();
        });

        // Cancelar entrega
        document.getElementById('btn-cancel').addEventListener('click', function() {
            resetScanResult();
            startScanner();
        });

        // Confirmar entrega
        document.getElementById('btn-confirm-delivery').addEventListener('click', function() {
            if (currentCarnetId) {
                registrarEntrega(currentCarnetId);
            }
        });

        function startScanner() {
            document.getElementById('start-scan').style.display = 'none';
            document.getElementById('stop-scan').style.display = 'inline-block';
            document.getElementById('scan-result').style.display = 'none';

            html5QrcodeScanner = new Html5Qrcode("reader");
            
            html5QrcodeScanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                onScanSuccess,
                onScanError
            ).catch(err => {
                console.error('Error al iniciar escáner:', err);
                toastr.error('No se pudo acceder a la cámara. Verifique los permisos.');
                document.getElementById('start-scan').style.display = 'inline-block';
                document.getElementById('stop-scan').style.display = 'none';
            });
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    document.getElementById('start-scan').style.display = 'inline-block';
                    document.getElementById('stop-scan').style.display = 'none';
                }).catch(err => {
                    console.error('Error al detener escáner:', err);
                });
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Detener escáner
            stopScanner();

            // Reproducir sonido de éxito (opcional)
            playBeep();

            // Procesar código escaneado
            procesarCodigoQR(decodedText);
        }

        function onScanError(errorMessage) {
            // Ignorar errores de escaneo continuo
        }

        function procesarCodigoQR(codigo) {
            // Mostrar loading
            toastr.info('Procesando código QR...');

            fetch('{{ route("carnets.escanear-qr") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    codigo_carnet: codigo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarDatosCarnet(data.carnet);
                } else {
                    toastr.error(data.message || 'Error al procesar el código QR');
                    setTimeout(() => startScanner(), 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error de conexión al procesar el código QR');
                setTimeout(() => startScanner(), 2000);
            });
        }

        function mostrarDatosCarnet(carnet) {
            currentCarnetId = carnet.id;

            // Mostrar datos
            document.getElementById('student-code').textContent = carnet.codigo;
            document.getElementById('student-name').textContent = carnet.estudiante;
            document.getElementById('student-dni').textContent = carnet.dni;
            document.getElementById('student-carrera').textContent = carnet.carrera;
            document.getElementById('student-turno').textContent = carnet.turno;
            document.getElementById('student-aula').textContent = carnet.aula;
            document.getElementById('student-ciclo').textContent = carnet.ciclo;

            // Mostrar foto
            if (carnet.foto_url) {
                document.getElementById('student-photo').src = carnet.foto_url;
            } else {
                document.getElementById('student-photo').src = '{{ asset("images/default-avatar.png") }}';
            }

            // Mostrar resultado
            document.getElementById('scan-result').style.display = 'block';

            toastr.success('Carnet escaneado correctamente');
        }

        function registrarEntrega(carnetId) {
            const btn = document.getElementById('btn-confirm-delivery');
            btn.disabled = true;
            btn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i> Registrando...';

            fetch('{{ route("carnets.registrar-entrega") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    carnet_id: carnetId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    
                    // Agregar al historial
                    agregarAlHistorial(data.carnet);
                    
                    // Actualizar estadísticas
                    sessionDeliveries++;
                    document.getElementById('session-deliveries').textContent = sessionDeliveries;
                    
                    // Resetear y reiniciar escáner
                    setTimeout(() => {
                        resetScanResult();
                        startScanner();
                    }, 2000);
                } else {
                    toastr.error(data.message || 'Error al registrar entrega');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="uil uil-check-circle me-1"></i> Confirmar Entrega';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error de conexión al registrar entrega');
                btn.disabled = false;
                btn.innerHTML = '<i class="uil uil-check-circle me-1"></i> Confirmar Entrega';
            });
        }

        function agregarAlHistorial(carnet) {
            deliveryHistory.unshift(carnet);
            
            const historyHtml = deliveryHistory.map(item => `
                <div class="history-item">
                    <strong>${item.codigo}</strong><br>
                    <small class="text-muted">${item.estudiante}</small><br>
                    <small class="text-success">${item.fecha_entrega}</small>
                </div>
            `).join('');

            document.getElementById('delivery-history').innerHTML = historyHtml;
        }

        function resetScanResult() {
            document.getElementById('scan-result').style.display = 'none';
            currentCarnetId = null;
            document.getElementById('btn-confirm-delivery').disabled = false;
            document.getElementById('btn-confirm-delivery').innerHTML = '<i class="uil uil-check-circle me-1"></i> Confirmar Entrega';
        }

        function playBeep() {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        }
    </script>
@endpush
