@extends('layouts.auth')

@section('title', 'Acceso al Sistema - CEPRE UNAMAD')

@section('content')
    <div class="min-vh-100 position-relative">
        <!-- Imagen de fondo -->
        <div class="position-absolute w-100 h-100">
            <img src="{{ asset('assets/images/login/login.jpg') }}" alt="CEPRE UNAMAD Background" 
                 class="w-100 h-100 object-fit-cover">
            <div class="position-absolute w-100 h-100 bg-dark opacity-60"></div>
            <div class="position-absolute w-100 h-100" 
                 style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.8) 0%, rgba(147, 51, 234, 0.8) 100%);"></div>
        </div>

        <!-- Contenido Principal -->
        <div class="position-relative d-flex align-items-center min-vh-100 py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-10 col-lg-12">
                        <div class="card shadow-2xl border-0 bg-white" style="border-radius: 20px; backdrop-filter: blur(10px);">
                            <div class="card-body p-0">
                                <div class="row g-0">
                                    <!-- Formulario de Login -->
                                    <div class="col-lg-6 p-5">
                                        <!-- Logo y Título -->
                                        <div class="text-center mb-4">
                                            <a href="{{ route('home') }}" class="d-inline-block mb-3">
                                                <img src="{{ asset('assets/images/logocepre1.svg') }}" alt="CEPRE UNAMAD"
                                                    height="90" class="img-fluid" />
                                            </a>
                                            <h2 class="fw-bold text-dark mb-2">Acceso al Sistema</h2>
                                            <p class="text-muted mb-1">
                                                Plataforma de Gestión Académica
                                            </p>
                                            <p class="text-primary fw-semibold small">
                                                CEPRE UNAMAD - Ciclo Ordinario 2025-1
                                            </p>
                                        </div>

                                        <!-- Alertas de Error -->
                                        @if ($errors->any())
                                            <div class="alert alert-danger border-0 rounded-3 mb-4" role="alert">
                                                <div class="d-flex align-items-center">
                                                    <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

                                        <!-- Formulario -->
                                        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                                            @csrf
                                            
                                            <!-- Campo Email -->
                                            <div class="mb-4">
                                                <label for="email" class="form-label fw-semibold text-dark">
                                                    <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                        <polyline points="22,6 12,13 2,6"></polyline>
                                                    </svg>
                                                    Correo Electrónico
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <circle cx="12" cy="12" r="4"></circle>
                                                            <path d="M16 8v5a3 3 0 0 0 6 0v-5a10 10 0 1 0-20 0v5a3 3 0 0 0 6 0v-5"></path>
                                                        </svg>
                                                    </span>
                                                    <input type="email"
                                                        class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                                        name="email" id="email" 
                                                        value="{{ old('email') }}"
                                                        placeholder="estudiante@unamad.edu.pe" 
                                                        required autofocus
                                                        style="border-radius: 0 8px 8px 0;">
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
                                                        class="text-decoration-none text-primary small">
                                                        <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                                        </svg>
                                                        ¿Olvidaste tu contraseña?
                                                    </a>
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                                                        </svg>
                                                    </span>
                                                    <input type="password"
                                                        class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror"
                                                        name="password" id="password" 
                                                        placeholder="Ingresa tu contraseña"
                                                        required>
                                                    <span class="input-group-text bg-light border-start-0 cursor-pointer" 
                                                        onclick="togglePassword()" id="togglePasswordBtn">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="togglePasswordIcon">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </span>
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
                                            <div class="mb-4">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="remember"
                                                        id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="form-check-label text-muted" for="remember">
                                                        Mantener sesión iniciada
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Botón de Envío -->
                                            <div class="d-grid mb-4">
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
                                            <div class="text-center">
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

                                    <!-- Panel Lateral de Información -->
                                    <div class="col-lg-6 d-none d-lg-block">
                                        <div class="h-100 position-relative overflow-hidden p-5 text-white d-flex align-items-center"
                                            style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.9) 0%, rgba(147, 51, 234, 0.9) 100%); border-radius: 0 20px 20px 0;">
                                            
                                            <!-- Contenido del Panel -->
                                            <div class="w-100 text-center">
                                                <div class="mb-4">
                                                    <svg class="mb-3" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                                        <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                                    </svg>
                                                </div>
                                                <h3 class="fw-bold mb-3">
                                                    Sistema de Gestión Académica
                                                </h3>
                                                <p class="lead mb-4 opacity-90">
                                                    Plataforma integral para el control de asistencia, calificaciones y gestión académica del Centro Preuniversitario
                                                </p>
                                                
                                                <div class="row text-center mb-4">
                                                    <div class="col-4">
                                                        <svg class="mb-2" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="9" cy="7" r="4"></circle>
                                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                        </svg>
                                                        <p class="small mb-0 opacity-80">Estudiantes</p>
                                                    </div>
                                                    <div class="col-4">
                                                        <svg class="mb-2" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                                            <line x1="8" y1="21" x2="16" y2="21"></line>
                                                            <line x1="12" y1="17" x2="12" y2="21"></line>
                                                        </svg>
                                                        <p class="small mb-0 opacity-80">Docentes</p>
                                                    </div>
                                                    <div class="col-4">
                                                        <svg class="mb-2" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <line x1="18" y1="20" x2="18" y2="10"></line>
                                                            <line x1="12" y1="20" x2="12" y2="4"></line>
                                                            <line x1="6" y1="20" x2="6" y2="14"></line>
                                                        </svg>
                                                        <p class="small mb-0 opacity-80">Reportes</p>
                                                    </div>
                                                </div>

                                                <!-- Información Institucional -->
                                                <div class="border-top pt-4 mt-4 opacity-75">
                                                    <p class="small mb-0">
                                                        <svg class="me-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                                        </svg>
                                                        <strong>Centro Preuniversitario</strong>
                                                    </p>
                                                    <p class="small mb-0 opacity-60">
                                                        Universidad Nacional Amazónica de Madre de Dios
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Bienvenida -->
    <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
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
                    <div class="mb-3">
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
                    <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">
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

        // Validación de formulario
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        } else {
                            // Mostrar modal de bienvenida si el formulario es válido
                            event.preventDefault();
                            showWelcomeModal();
                            // Enviar formulario después de 2 segundos
                            setTimeout(function() {
                                form.submit();
                            }, 2000);
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Mostrar modal de bienvenida
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
        .cursor-pointer {
            cursor: pointer;
        }
        
        .object-fit-cover {
            object-fit: cover;
        }
        
        .shadow-2xl {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #9333ea 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8fafc !important;
        }
        
        .form-control {
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            transform: translateY(-1px);
        }
        
        .card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95) !important;
        }
        
        .modal-content {
            backdrop-filter: blur(10px);
        }
        
        .alert {
            backdrop-filter: blur(10px);
        }
        
        /* Animaciones */
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
        
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .min-vh-100 {
                padding: 2rem 0;
            }
            .card-body {
                padding: 2rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .card-body {
                padding: 1.5rem !important;
            }
        }
    </style>
@endsection