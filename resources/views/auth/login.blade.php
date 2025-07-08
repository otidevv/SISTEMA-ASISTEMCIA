@extends('layouts.auth')

@section('title', 'Acceso al Sistema - CEPRE UNAMAD')

@section('content')
    <div class="min-vh-100 position-relative d-flex align-items-center justify-content-center p-3">
        <!-- Imagen de fondo original con overlay oscuro -->
        <div class="position-absolute w-100 h-100 top-0 start-0 overflow-hidden">
            <img src="{{ asset('assets/images/login/login.jpg') }}" alt="CEPRE UNAMAD Background"
                 class="w-100 h-100 object-fit-cover position-absolute top-0 start-0">
            <div class="position-absolute w-100 h-100 top-0 start-0 bg-dark-overlay"></div>
        </div>

        <!-- Contenedor Principal con formulario -->
        <div class="container position-relative z-1">
            <div class="row justify-content-center">
                <div class="col-md-9 col-lg-7 col-xl-6">
                    <div class="card shadow-lg border-0 rounded-4 animated-card" style="background: rgba(255, 255, 255, 0.98);">
                        <div class="card-body p-4 p-sm-5">
                            <div class="text-center mb-5">
                                <a href="{{ route('home') }}" class="d-inline-block mb-4">
                                    <img src="{{ asset('assets/images/logocepre1.svg') }}" alt="CEPRE UNAMAD"
                                         height="90" class="img-fluid" />
                                </a>
                                <h2 class="fw-bold text-dark mb-2 fs-2">Bienvenido</h2>
                                <p class="text-muted mb-0">Inicia sesión en tu cuenta para continuar</p>
                            </div>

                            <!-- Alertas de Error de Laravel (si las hay) -->
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 rounded-3 mb-4 animate__animated animate__fadeInDown" role="alert">
                                    <div class="d-flex align-items-center">
                                        <svg class="me-2 flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        <div>
                                            <strong>Error de validación</strong>
                                            <ul class="mb-0 mt-1 small">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Formulario de Login -->
                            <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                                @csrf
                                
                                <!-- Campo Correo Electrónico -->
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-semibold text-dark">
                                        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                        Correo Electrónico
                                    </label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text bg-light border-end-0 rounded-start-pill">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="4"></circle>
                                                <path d="M16 8v5a3 3 0 0 0 6 0v-5a10 10 0 1 0-20 0v5a3 3 0 0 0 6 0v-5"></path>
                                            </svg>
                                        </span>
                                        <input type="email"
                                            class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                            name="email" id="email" 
                                            value="{{ old('email') }}"
                                            placeholder="ejemplo@unamad.edu.pe" 
                                            required autofocus
                                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                            title="Por favor, introduce un correo electrónico válido."
                                            style="border-radius: 0 8px 8px 0;">
                                        <div class="invalid-feedback">
                                            Por favor, introduce un correo electrónico válido.
                                        </div>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block mt-1">
                                            <small>
                                                <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                                </svg>
                                                {{ $message }}
                                            </small>
                                        </div>
                                    @enderror
                                </div>

                                <!-- Campo Contraseña -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="password" class="form-label fw-semibold text-dark mb-0">
                                            <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <circle cx="12" cy="16" r="1"></circle>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                            Contraseña
                                        </label>
                                        <a href="{{ route('password.request') }}"
                                            class="text-decoration-none text-primary small animate__animated animate__fadeInRight">
                                            <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                            </svg>
                                            ¿Olvidaste tu contraseña?
                                        </a>
                                    </div>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text bg-light border-end-0 rounded-start-pill">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                                            </svg>
                                        </span>
                                        <input type="password"
                                            class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror"
                                            name="password" id="password" 
                                            placeholder="Ingresa tu contraseña"
                                            required
                                            minlength="6"
                                            title="La contraseña debe tener al menos 6 caracteres."
                                            style="border-radius: 0 8px 8px 0;">
                                        <span class="input-group-text bg-light border-start-0 cursor-pointer rounded-end-pill" 
                                            onclick="togglePassword()" id="togglePasswordBtn">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="togglePasswordIcon">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </span>
                                        <div class="invalid-feedback">
                                            Por favor, introduce tu contraseña.
                                        </div>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block mt-1">
                                            <small>
                                                <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                                </svg>
                                                {{ $message }}
                                            </small>
                                        </div>
                                    @enderror
                                </div>

                                <!-- Recordar Sesión -->
                                <div class="mb-4 form-check animate__animated animate__fadeInUp">
                                    <input type="checkbox" class="form-check-input" name="remember"
                                        id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted" for="remember">
                                        Mantener sesión iniciada
                                    </label>
                                </div>

                                <!-- Botón de Envío -->
                                <div class="d-grid mb-4 animate__animated animate__zoomIn">
                                    <button class="btn btn-primary btn-lg fw-semibold" type="submit"
                                        style="border-radius: 10px; padding: 14px;">
                                        <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M15 3h6v6"></path>
                                            <path d="M10 14 21 3"></path>
                                            <path d="M21 3 11 13l-7-7"></path>
                                        </svg>
                                        Acceder al Sistema
                                    </button>
                                </div>

                                <!-- Información Adicional -->
                                <div class="text-center animate__animated animate__fadeInUp">
                                    <p class="text-muted small mb-0">
                                        <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 12l2 2 4-4"></path>
                                            <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                                            <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                                            <path d="M12 21c0-1 1-3 3-3s3 2 3 3-1 3-3 3-3-2-3-3"></path>
                                            <path d="M12 3c0 1-1 3-3 3s-3-2-3-3 1-3 3-3 3 2 3 3"></path>
                                        </svg>
                                        Conexión segura y encriptada
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Bienvenida -->
    <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg animate__animated animate__zoomIn" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title fw-bold" id="welcomeModalLabel">
                        <svg class="me-2" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4"></path>
                            <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                            <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                            <path d="M12 21c0-1 1-3 3-3s3 2 3 3-1 3-3 3-3-2-3-3"></path>
                            <path d="M12 3c0 1-1 3-3 3s-3-2-3-3 1-3 3-3 3 2 3 3"></path>
                        </svg>
                        Acceso Autorizado
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3 animate__animated animate__bounceIn">
                        <svg class="text-success" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20,6 9,17 4,12"></polyline>
                        </svg>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">¡Bienvenido al Sistema!</h4>
                    <p class="text-muted mb-3">
                        Has iniciado sesión exitosamente en la plataforma de gestión académica del CEPRE UNAMAD.
                    </p>
                    <div class="bg-light rounded-3 p-3 mb-3">
                        <p class="small mb-0 text-muted">
                            <svg class="me-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            Sesión iniciada: <span id="loginTime"></span>
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-primary px-4 animate__animated animate__pulse animate__infinite" data-bs-dismiss="modal">
                        <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h6v6"></path>
                            <path d="M10 14 21 3"></path>
                            <path d="M21 3 11 13l-7-7"></path>
                        </svg>
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Función para mostrar/ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }

        // Validación de formulario con Bootstrap
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.querySelectorAll('.needs-validation');
                Array.prototype.slice.call(forms)
                    .forEach(function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
            }, false);
        })();

        // Función para mostrar el modal de bienvenida (idealmente se activaría desde el backend)
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('login_success') && urlParams.get('login_success') === 'true') {
                showWelcomeModal();
            }
        });

        function showWelcomeModal() {
            // Actualizar tiempo de login
            const now = new Date();
            const timeString = now.toLocaleString('es-PE', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            document.getElementById('loginTime').textContent = timeString;
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('welcomeModal'));
            modal.show();
        }
    </script>

    <!-- Estilos CSS -->
    <style>
        /* Import Animate.css for subtle animations */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow: hidden; /* Evita barras de desplazamiento del fondo */
        }

        .min-vh-100 {
            min-height: 100vh;
        }

        .cursor-pointer {
            cursor: pointer;
        }
        
        .object-fit-cover {
            object-fit: cover;
        }
        
        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        /* Overlay oscuro para la imagen de fondo */
        .bg-dark-overlay {
            background-color: rgba(30, 40, 50, 0.8); /* Tono oscuro para profesionalismo */
        }
        
        /* Colores y estilos para elementos interactivos */
        .form-control:focus {
            border-color: #3F51B5; /* Azul índigo para el enfoque */
            box-shadow: 0 0 0 0.2rem rgba(63, 81, 181, 0.25); /* Sombra de enfoque */
        }
        
        .btn-primary {
            background: #3F51B5; /* Azul índigo sólido para el botón */
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #303F9F; /* Azul índigo más oscuro al pasar el ratón */
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(63, 81, 181, 0.4);
        }

        /* El texto con clase .text-primary también usará el nuevo color */
        .text-primary {
            color: #3F51B5 !important;
        }
        
        .input-group-text {
            border-radius: 8px 0 0 8px; 
            background-color: #f8fafc !important;
            border-color: #dee2e6;
        }

        .input-group-text.rounded-start-pill {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
        }

        .input-group-text.rounded-end-pill {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
        }
        
        .form-control {
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            transform: translateY(-1px);
        }
        
        .card {
            backdrop-filter: blur(5px);
            background: rgba(255, 255, 255, 0.98) !important;
        }
        
        .modal-content {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95) !important;
        }
        
        .alert {
            backdrop-filter: blur(10px);
            background: rgba(220, 53, 69, 0.9) !important;
            color: white;
        }
        .alert svg {
            color: white;
        }

        /* Animaciones */
        .animated-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .card-body {
                padding: 1.5rem !important;
            }
            .text-center .mb-4 img {
                height: 70px;
            }
            .fs-2 {
                font-size: 1.8rem !important;
            }
            .lead {
                font-size: 0.9rem;
            }
        }
    </style>
@endsection
